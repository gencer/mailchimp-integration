<?php
/**
 * @copyright      2001-2015 Kayako
 * @license        https://www.freebsd.org/copyright/freebsd-license.html
 * @link           https://github.com/kayako/mailchimp-integration
 */

/**
 * The Synchronization Library for MailChimp
 *
 * @author Ruchi Kothari
 */
class SWIFT_UserSyncManager extends SWIFT_Library
{
	// Number of users to subscribe in a single batch
	const USER_COUNT = 500;

	// Last user's id who is synchronized
	private $_lastID;

	/**
	 * Constructor
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __construct()
	{
		parent::__construct();

		return true;
	}

	/**
	 * Destructor
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __destruct()
	{
		parent::__destruct();

		return true;
	}

	/**
	 * Retrieve last synchronized user's ID
	 *
	 * @author Ruchi Kothari
	 * @return mixed Last synchronized user's ID on Success, "false" otherwise
	 * @throws SWIFT_Exception If class is not loaded
	 */
	private function RetrieveLastSyncID()
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		if (!$this->Settings->GetKey('mailchimp', 'lastuserid')) {
			return $this->UpdateLastSyncID();
		}

		return intval($this->Settings->GetKey('mailchimp', 'lastuserid'));
	}

	/**
	 * Update last synchronized user's id
	 *
	 * @author Ruchi Kothari
	 *
	 * @param int $_lastSyncID
	 *
	 * @return mixed $_lastUserID on Success, "false" otherwise
	 * @throws SWIFT_Exception If class is not loaded
	 */
	private function UpdateLastSyncID($_lastSyncID = -1)
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		if ($this->Settings->UpdateKey('mailchimp', 'lastuserid', $_lastSyncID)) {
			return $_lastSyncID;
		}

		return false;
	}

	/**
	 * Batch Subscription
	 *
	 * @author Ruchi Kothari
	 * @return "true" on success, "false" otherwise
	 * @throws SWIFT_Exception If class is not loaded
	 */
	public function BatchSubscribe()
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

//		SWIFT_Loader::LoadLibrary('User:UserEmail');

		$_lastSyncID = $this->RetrieveLastSyncID();

		if (!$_lastSyncID) {
			throw new SWIFT_Exception("Last synchronized user is not available.");
		}

		$this->Database->QueryLimit('SELECT users.userid, users.fullname FROM ' . TABLE_PREFIX . 'users AS users
			LEFT JOIN ' . TABLE_PREFIX . 'userproperties AS userproperties ON users.userid = userproperties.userid
			WHERE users.userid > ' . intval($_lastSyncID) . ' AND userproperties.keyname IS NULL
			ORDER BY users.userid ASC', self::USER_COUNT);

		$_userContainer                = array();
		$_userIDList                   = array();
		$_mailChimpUserObjectContainer = array();

		while ($this->Database->NextRecord()) {
			$_userContainer[$this->Database->Record['userid']]['fullname'] = $this->Database->Record['fullname'];
			$_userIDList[]                                                 = $this->Database->Record['userid'];
		}

		if (empty($_userIDList)) {
			return false;
		}

		$this->_lastID = $_userIDList[count($_userIDList) - 1];

		$this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "useremails
			WHERE linktype = '" . SWIFT_UserEmail::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ")");

		$_userContainerByEmail = array();
		$_userEmails           = array();

		// User container with email id as key, so that user information can be traced after filteration of emails
		while ($this->Database->NextRecord()) {
			$_userContainerByEmail[$this->Database->Record['email']] = $_userContainer[$this->Database->Record['linktypeid']];
			$_userEmails[]                                           = $this->Database->Record['email'];
		}

		if (!empty($_userEmails)) {
			$_SWIFT_UserSyncObject = new SWIFT_UserSync();

			// Retrieve mailchimp members information
			$_memberInfoList = $_SWIFT_UserSyncObject->ListMemberInformation($_userEmails);

			// Filter users emails depending upon subscription status
			$_filteredMembersList = $this->FilterList(SWIFT_UserSync::SUBSCRIBED, $_userEmails, $_memberInfoList);

			if (!empty($_filteredMembersList)) {
				foreach ($_filteredMembersList as $_list) {
					foreach ($_list as $_email) {
						if (!isset($_mailChimpUserObjectContainer[$_email])) {
							$_firstName = strstr($_userContainerByEmail[$_email]['fullname'], ' ', true);
							$_lastName  = strstr($_userContainerByEmail[$_email]['fullname'], ' ');

							$_mailChimpUserObjectContainer[$_email] = new SWIFT_MailChimpUser($_email, $_firstName, $_lastName);
						}
					}
				}
			}

			$_subscriptionResult = $_SWIFT_UserSyncObject->Subscribe($_filteredMembersList, $_mailChimpUserObjectContainer);

			$this->UpdateLastSyncID($this->_lastID);

			$this->LogError($_subscriptionResult);
		}

		return true;
	}

	/**
	 * Subscribe a user to MailChimp List
	 *
	 * @author Ruchi Kothari
	 *
	 * @param array $_userDetails User details for subscription
	 *
	 * @return bool "true" on Success, "false" otherwise
	 * @throws SWIFT_Exception If class is not loaded
	 */

	public function MemberSubscribe($_userDetails)
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		if (!empty($_userDetails['emails'])) {
			$_firstName = strstr($_userDetails['fullname'], " ", true);
			$_lastName  = strstr($_userDetails['fullname'], " ");

			$_SWIFT_UserSyncObject = new SWIFT_UserSync();

			// Retrieve mailchimp members information
			$_memberInfoList = $_SWIFT_UserSyncObject->ListMemberInformation($_userDetails['emails']);

			// Filter users emails depending upon subscription status
			$_filteredMembersList          = $this->FilterList(SWIFT_UserSync::SUBSCRIBED, $_userDetails['emails'], $_memberInfoList);
			$_mailChimpUserObjectContainer = array();

			if (!empty($_filteredMembersList)) {
				foreach ($_filteredMembersList as $_list) {
					foreach ($_list as $_email) {
						if (!isset($_mailChimpUserObjectContainer[$_email])) {
							$_mailChimpUserObjectContainer[$_email] = new SWIFT_MailChimpUser($_email, $_firstName, $_lastName);
						}
					}
				}
			}

			$_subscriptionResult = $_SWIFT_UserSyncObject->Subscribe($_filteredMembersList, $_mailChimpUserObjectContainer);

			$this->LogError($_subscriptionResult);
		}

		return true;
	}

	/**
	 * Filter users list for subscription or unsubscription
	 *
	 * @author Ruchi Kothari
	 *
	 * @param string $_type           Type of filteration, whether subscribed or unsubscribed
	 * @param array  $_fullList       Complete list of users from DB
	 * @param array  $_memberInfoList Mailchimp member information list with subscribed or unsubscribed status
	 *
	 * @return mixed Filtered list on Success, "false" otherwise
	 * @throws SWIFT_Exception If class is not loaded
	 */
	private function FilterList($_type, $_fullList, $_memberInfoList)
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		$_filteredList = array();

		if (!_is_array($_memberInfoList)) {
			return false;
		}

		foreach ($_memberInfoList as $_listID => $_list) {
			$_remove = array();
			if ($_type == SWIFT_UserSync::UNSUBSCRIBED) {
				$_remove = array_merge($_list[$_type], $_list[SWIFT_UserSync::PENDING], $_list['nostatus']);
			} else {
				$_remove = $_list[$_type];
			}

			$_filtered = array_diff($_fullList, $_remove);

			if (!empty($_filtered)) {
				$_filteredList[$_listID] = $_filtered;
			}
		}

		return $_filteredList;
	}

	/**
	 * Unsubscribe a member from Mailchimp List
	 *
	 * @author Ruchi Kothari
	 *
	 * @param array $_emails       Emails of a user to unsubscribe
	 * @param bool  $_deleteMember Delete a member on unsubscription or not
	 *
	 * @return bool "true" on Success, "false" otherwise
	 * @throws SWIFT_Exception If class is not loaded
	 */

	public function MemberUnsubscribe($_emails, $_deleteMember = false)
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		if (!empty($_emails)) {
			$_SWIFT_UserSyncObject = new SWIFT_UserSync();

			// Retrieve mailchimp members information
			$_memberInfoList = $_SWIFT_UserSyncObject->ListMemberInformation($_emails);

			// Filter users emails depending upon subscription status
			$_filteredMembersList = $this->FilterList(SWIFT_UserSync::UNSUBSCRIBED, $_emails, $_memberInfoList);

			$_subscriptionResult = $_SWIFT_UserSyncObject->Unsubscribe($_filteredMembersList, $_deleteMember);

			$this->LogError($_subscriptionResult);
		}

		return true;
	}

	/**
	 * Perform action(Subscription or Unsubscription) on a user
	 *
	 * @author Ruchi Kothari
	 *
	 * @param object $_SWIFT_UserObject      SWIFT_User object
	 * @param int    $_unsubscribeNewsletter flag for subscription
	 * @param array  $_userDetails           User details
	 *
	 * @return bool "true" on Success, "false" otherwise
	 * @throws SWIFT_Exception If class is not loaded
	 */
	public function PerformAction($_SWIFT_UserObject, $_unsubscribeNewsletter, $_userDetails)
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

//		SWIFT_Loader::LoadLibrary('User:UserProperty');

		$_subscriptionResult = true;

		// Retrieve user unsubscribe property
		$_unsubscribeStatus = SWIFT_UserProperty::RetrieveOnUser($_SWIFT_UserObject, 'mc_unsubscribe');

		if ($_unsubscribeNewsletter == 1 && $_unsubscribeStatus == '') {
			// Create user property if user doesn't want to subscribe
			SWIFT_UserProperty::CreateOrUpdate($_SWIFT_UserObject, 'mc_unsubscribe', 1);

			$_SWIFT_UserSyncManagerObject = new SWIFT_UserSyncManager();
			$_subscriptionResult          = $_SWIFT_UserSyncManagerObject->MemberUnsubscribe($_userDetails['emails']['existing']);
		} else if ($_unsubscribeNewsletter == 0) {
			$_userSubscribeDetails           = array();
			$_userSubscribeDetails['emails'] = array();

			if ($_unsubscribeStatus == 1) {
				// Delete user property if user want to subscribe
				SWIFT_UserProperty::DeleteOnName($_SWIFT_UserObject, 'mc_unsubscribe');

				$_userSubscribeDetails['emails'] = $_userDetails['emails']['existing'];
			}

			if (!empty($_userDetails['emails']['new'])) {
				$_userSubscribeDetails['emails'] = array_merge($_userSubscribeDetails['emails'], $_userDetails['emails']['new']);
			}

			if (!empty($_userSubscribeDetails['emails'])) {
				$_userSubscribeDetails['fullname'] = $_userDetails['fullname'];

				$_SWIFT_UserSyncManagerObject = new SWIFT_UserSyncManager();
				$_subscriptionResult          = $_SWIFT_UserSyncManagerObject->MemberSubscribe($_userSubscribeDetails);
			}
		}

		if (!empty($_userDetails['emails']['deleted'])) {
			$_SWIFT_UserSyncManagerObject = new SWIFT_UserSyncManager();
			$_subscriptionResult          = $_SWIFT_UserSyncManagerObject->MemberUnsubscribe($_userDetails['emails']['deleted'], true);
		}

		return $_subscriptionResult;
	}

	/**
	 * Log errors from mailchimp results
	 *
	 * @author Ruchi Kothari
	 *
	 * @param array $_mailchimpResult Result recieved from MailChimp after performing various operations
	 *
	 * @return bool "true" on Success, "false" otherwise
	 * @throws SWIFT_Exception If class is not loaded
	 */
	private function LogError($_mailchimpResult)
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		if (!_is_array($_mailchimpResult)) {
			return true;
		}

		$_errorString = '';

		foreach ($_mailchimpResult as $_list) {
			if (!empty($_list['errors'])) {
				$_errorString .= "*****" . $_list['name'] . "  Error***** :";
				foreach ($_list['errors'] as $_error) {
					$_errorString .= ' Message: ' . $_error['message'] . SWIFT_CRLF;
					$_errorString .= ' Code: ' . $_error['code'];
				}
			}
		}

		if (!empty($_errorString)) {
			$_errorString = $this->Language->Get('mc_error') . $_errorString;

//			SWIFT_Loader::LoadLibrary('ErrorLog:ErrorLog');

			SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_GENERAL, $_errorString);
		}

		return true;
	}
}

?>
