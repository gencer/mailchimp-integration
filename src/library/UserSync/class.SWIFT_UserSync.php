<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2015, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */


/**
 * The MailChimp Library
 *
 * @author Ruchi Kothari
 */
class SWIFT_UserSync extends SWIFT_Library
{
	private $MCAPI;
	private $_lists = array();

	const UNSUBSCRIBED = 'unsubscribed';
	const SUBSCRIBED = 'subscribed';
	const PENDING = 'pending';
	const MEMBERINFO_COUNT = 50;

	/**
	 * Constructor
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __construct()
	{
		parent::__construct();

		require_once('./' . SWIFT_APPSDIRECTORY . '/' . '/mailchimp/' . SWIFT_THIRDPARTYDIRECTORY . '/MailChimp/MCAPI.class.php');

		$_apiKey = $this->Settings->Get('mc_apikey');

		if (trim($_apiKey) == "") {
			return false;
		}

		$this->MCAPI = new MCAPI($_apiKey);
		$this->SetLists();

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
	 * Check API Key is set
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function IssetAPIKey()
	{
		if (isset($this->MCAPI)) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve all of the lists defined for a particular user account and set them to class variable
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" On success, "false" otherwise
	 * @throws SWIFT_Exception If error occured while fetching MalChimp lists
	 */
	public function SetLists()
	{
		$_mailChimpListContainer = $this->MCAPI->lists();

		if ($this->MCAPI->errorCode) {
			throw new SWIFT_Exception("Unable to load MailChimps Lists.\n Error Code=" . $this->MCAPI->errorCode . " \n Error Message=" . $this->MCAPI->errorMessage);

			return false;
		}

		foreach ($_mailChimpListContainer['data'] as $_list) {
			$this->_lists[$_list['id']] = $_list;
		}

		return true;
	}

	/**
	 * Get MailChimp lists
	 *
	 * @author Ruchi Kothari
	 * @return array All of the lists defined for a particular user account
	 */
	public function GetLists()
	{
		return $this->_lists;
	}

	/**
	 * Subscribe Users(General function)
	 *
	 * @author Ruchi Kothari
	 *
	 * @param array $_membersList                  list of users grouprd by list id
	 * @param array $_mailChimpUserObjectContainer Array of helpdesk users to subscribe
	 *
	 * @return array $_subscriptionResult Subscription result from mailchimp
	 * @throws SWIFT_User_Exception If the Class is not Loaded or if object is not the instance of class
	 * @throws SWIFT_Exception If error occured while Batch Subscribe
	 */
	public function Subscribe($_membersList, $_mailChimpUserObjectContainer)
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		$_subscriptionResult = array();

		if (!_is_array($_membersList)) {
			return false;
		}

		foreach ($_membersList as $_listID => $_list) {
			$_filteredMailChimpUserObjectContainer = array();

			foreach ($_list as $_email) {
				$_filteredMailChimpUserObjectContainer[] = $_mailChimpUserObjectContainer[$_email];
			}

			if (count($_list) > 1) {
				$_subscriptionResult[$_listID]         = $this->ListBatchSubscribe($_listID, $_filteredMailChimpUserObjectContainer);
				$_subscriptionResult[$_listID]['name'] = $this->_lists[$_listID]['name'];
			} else {
				$_subscriptionResult[$_listID][]       = $this->ListSubscribe($_listID, $_filteredMailChimpUserObjectContainer[0]);
				$_subscriptionResult[$_listID]['name'] = $this->_lists[$_listID]['name'];
			}
		}

		return $_subscriptionResult;
	}

	/**
	 * Subscribe a batch of Users to a MailChimp lists.
	 *
	 * @author Ruchi Kothari
	 *
	 * @param string $_listID                       List ID for subscription
	 * @param array  $_mailChimpUserObjectContainer Array of helpdesk users to subscribe
	 *
	 * @return array $_subscriptionResult Subscription result from mailchimp
	 * @throws SWIFT_User_Exception If the Class is not Loaded or if object is not the instance of class
	 * @throws SWIFT_Exception If error occured while Batch Subscribe
	 */

	public function ListBatchSubscribe($_listID, $_mailChimpUserObjectContainer = array())
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		$_optIN           = $this->Settings->Get('mc_optin');
		$_updateExist     = false;
		$_replacInterests = true;
		$_userContainer   = array();

		foreach ($_mailChimpUserObjectContainer as $_SWIFT_MailChimpUserObject) {
			if (!$_SWIFT_MailChimpUserObject instanceof SWIFT_MailChimpUser || !$_SWIFT_MailChimpUserObject->GetIsClassLoaded()) {
				throw new SWIFT_User_Exception(SWIFT_INVALIDDATA);

				return false;
			}

			$_userContainer[] = array(
				'EMAIL' => $_SWIFT_MailChimpUserObject->GetEmail(), 'FNAME' => $_SWIFT_MailChimpUserObject->GetFirstName(), 'LNAME' => $_SWIFT_MailChimpUserObject->GetLastName()
			);
		}

		$_subscriptionResult = $this->MCAPI->listBatchSubscribe($_listID, $_userContainer, $_optIN, $_updateExist, $_replacInterests);

		$_error = array();
		if ($this->MCAPI->errorCode) {
			$_error['code']    = $this->MCAPI->errorCode;
			$_error['message'] = 'Batch Subscription failed to ' . $this->_lists[$_listID]['name'] . '.  Error Message=' . $this->MCAPI->errorMessage;

			$_subscriptionResult['errors'][] = $_error;
		}

		return $_subscriptionResult;
	}

	/**
	 * Subscribe a User to MailChimp lists.
	 *
	 * @author Ruchi Kothari
	 *
	 * @param string $_listID              List ID for subscription
	 * @param array  $_mailChimpUserObject MailChimp user object for subscription
	 *
	 * @return array $_subscriptionResult Subscription result from mailchimp
	 * @throws SWIFT_User_Exception If the Class is not Loaded or if object is not the instance of class
	 * @throws SWIFT_Exception If class not loaded
	 */
	public function ListSubscribe($_listID, $_MailChimpUserObject)
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		$_optIN            = $this->Settings->Get('mc_optin');
		$_updateExist      = false;
		$_replaceInterests = true;
		$_sendWelcome      = $this->Settings->Get('mc_send_welcome');
		$_emailTypes       = $this->Settings->Get('mc_emailtype');

		if (!$_MailChimpUserObject instanceof SWIFT_MailChimpUser || !$_MailChimpUserObject->GetIsClassLoaded()) {
			throw new SWIFT_User_Exception(SWIFT_INVALIDDATA);

			return false;
		}

		$_mergerVariables = array('FNAME' => $_MailChimpUserObject->GetFirstName(), 'LNAME' => $_MailChimpUserObject->GetLastName());

		$_subscriptionResult = $this->MCAPI->listSubscribe($_listID, $_MailChimpUserObject->GetEmail(), $_mergerVariables, $_emailTypes, $_optIN, $_updateExist, $_replaceInterests, $_sendWelcome);

		$_error = array();
		if ($this->MCAPI->errorCode) {
			$_error['code']    = $this->MCAPI->errorCode;
			$_error['message'] = 'Subscription failed for ' . $this->_lists[$_listID]['name'] . '.  Error Message=' . $this->MCAPI->errorMessage;

			$_subscriptionResult['errors'][] = $_error;
		}

		return $_subscriptionResult;
	}

	/**
	 * Unsubscribe Users(General function)
	 *
	 * @author Ruchi Kothari
	 *
	 * @param array $_membersList  list of users grouprd by list id
	 * @param bool  $_deleteMember Delete a member on unsubscription or not
	 *
	 * @return array $_subscriptionResult Subscription result from mailchimp
	 * @throws SWIFT_User_Exception If the Class is not Loaded or if object is not the instance of class
	 * @throws SWIFT_Exception If error occured while Batch Subscribe
	 */
	public function Unsubscribe($_membersList, $_deleteMember)
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		if (!_is_array($_membersList)) {
			return false;
		}

		$_subscriptionResult = array();

		foreach ($_membersList as $_listID => $_list) {
			if (count($_list) > 1) {
				$_subscriptionResult[$_listID]         = $this->ListBatchUnubscribe($_listID, $_list, $_deleteMember);
				$_subscriptionResult[$_listID]['name'] = $this->_lists[$_listID]['name'];
			} else {
				$_subscriptionResult[$_listID][]       = $this->ListUnsubscribe($_listID, current($_list), $_deleteMember);
				$_subscriptionResult[$_listID]['name'] = $this->_lists[$_listID]['name'];
			}
		}

		return $_subscriptionResult;
	}

	/**
	 * Unsubscribe a User from MailChimp lists.
	 *
	 * @author Ruchi Kothari
	 *
	 * @param string $_listID       List ID to which unsubscribe user
	 * @param string $_email        user email
	 * @param bool   $_deleteMember Delete a member on unsubscription or not
	 *
	 * @return array $_subscriptionResult Result from MailChimp on unsubscription
	 * @throws SWIFT_Exception If the Class is not Loaded
	 */
	public function ListUnsubscribe($_listID, $_email, $_deleteMember = false)
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		$_sendbye = $this->Settings->Get('mc_send_goodbye');
		$_notify  = $this->Settings->Get('mc_send_notify');

		$_subscriptionResult = array();
		$_subscriptionResult = $this->MCAPI->listUnsubscribe($_listID, $_email, $_deleteMember, $_sendbye, $_notify);

		$_error = array();
		if ($this->MCAPI->errorCode) {
			$_error['code']    = $this->MCAPI->errorCode;
			$_error['message'] = 'Failed to unsubscribe ' . $_email . " from " . $this->_lists[$_listID]['name'] . '.  Error Message=' . $this->MCAPI->errorMessage;

			$_subscriptionResult['errors'][] = $_error;
		}

		return $_subscriptionResult;
	}

	/**
	 * Unsubscribe Batch of User from MailChimp lists.
	 *
	 * @author Ruchi Kothari
	 *
	 * @param string $_listID       List ID to which unsubscribe users
	 * @param array  $_emails       Array of user emails
	 * @param bool   $_deleteMember Delete a member on unsubscription or not
	 *
	 * @return array $_subscriptionResult Result from MailChimp on unsubscription
	 * @throws SWIFT_Exception If the Class is not Loaded
	 */
	public function ListBatchUnsubscribe($_listID, $_emails, $_deleteMember = false)
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		$_sendbye = IIF($this->Settings->Get('mc_send_goodbye'), true, false);
		$_notify  = IIF($this->Settings->Get('mc_send_notify'), true, false);

		$_subscriptionResult         = array();
		$_subscriptionResult         = $this->MCAPI->listBatchUnsubscribe($_listID, $_emails, $_deleteMember, $_sendbye, $_notify);
		$_subscriptionResult['name'] = $this->_lists[$_listID]['name'];

		$_error = array();
		if ($this->MCAPI->errorCode) {
			$_error['code']    = $this->MCAPI->errorCode;
			$_error['message'] = 'Failed to batch unsubscribe from ' . $this->_lists[$_listID]['name'] . '.  Error Message=' . $this->MCAPI->errorMessage;

			$_subscriptionResult[$_listID]['errors'][] = $_error;
		}

		return $_subscriptionResult;
	}

	/**
	 * Get MailChimp member information
	 *
	 * @author Ruchi Kothari
	 *
	 * @param array $_emails Array of emails
	 *
	 * @return array $_listInfoResult Subscription result from mailchimp
	 * @throws SWIFT_Exception If the class is not loaded
	 */

	public function ListMemberInformation($_emails = array())
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		$_listIDs = $this->Settings->Get('mc_lists');

		if (empty($_emails) || empty($_listIDs)) {
			return false;
		}

		$_emailChunks = array_chunk($_emails, self::MEMBERINFO_COUNT);

		$_listInfoResult = array();

		foreach ($_listIDs as $_listID) {
			$_listInfoResult[$_listID][self::UNSUBSCRIBED] = array();
			$_listInfoResult[$_listID][self::SUBSCRIBED]   = array();
			$_listInfoResult[$_listID][self::PENDING]      = array();
			$_listInfoResult[$_listID]['nostatus']         = array();

			foreach ($_emailChunks as $_emailsChunk) {
				$_result = $this->MCAPI->listMemberInfo($_listID, $_emailsChunk);

				$_error = array();
				if ($this->MCAPI->errorCode) {
					$_error['code']    = $this->MCAPI->errorCode;
					$_error['message'] = 'Unable to retrieve Member Information.  Error Message=' . $this->MCAPI->errorMessage;

					$_listInfoResult[$_listID]['errors'][] = $_error;
					$_listInfoResult[$_listID]['name']     = $this->_lists[$_listID]['name'];
				} else {
					foreach ($_result['data'] as $_data) {
						if (isset($_data['status'])) {
							if ($_data['status'] == self::UNSUBSCRIBED) {
								$_listInfoResult[$_listID][self::UNSUBSCRIBED][] = $_data['email'];
							} else if ($_data['status'] == self::SUBSCRIBED) {
								$_listInfoResult[$_listID][self::SUBSCRIBED][] = $_data['email'];
							} else if ($_data['status'] == self::PENDING) {
								$_listInfoResult[$_listID][self::PENDING][] = $_data['email'];
							} else {
								$_listInfoResult[$_listID]['nostatus'][] = $_data['email'];
							}
						}
					}
				}
			}
		}

		return $_listInfoResult;
	}
}

?>
