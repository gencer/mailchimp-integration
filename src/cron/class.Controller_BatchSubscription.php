<?php
/**
 * @copyright      2001-2015 Kayako
 * @license        https://www.freebsd.org/copyright/freebsd-license.html
 * @link           https://github.com/kayako/mailchimp-integration
 */

/**
 * Controller for Batch Subscription
 *
 * @author Ruchi Kothari
 */
class Controller_BatchSubscription extends Controller_cron
{
	/**
	 * Constructor
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __construct()
	{
		parent::__construct();

		$this->Load->Library('UserSync:UserSyncManager', array(), true, APP_MAILCHIMP);
		$this->Load->Library('UserSync:UserSync', array(), true, APP_MAILCHIMP);

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
	 * Batch Subscription to MailChimp Lists
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" on Success, "false" otherwise
	 * @throws SWIFT_Exception If the Class is not Loaded
	 */
	public function Subscribe()
	{
		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		$_SWIFT_UserSyncManagerObject = new SWIFT_UserSyncManager();
		$_subscriptionResult          = $_SWIFT_UserSyncManagerObject->BatchSubscribe();

		$_errorString = "";

		if (isset($_subscriptionResult['error'])) {
			$_errorString .= $_subscriptionResult['error'];
		} else if (isset($_subscriptionResult['lists'])) {
			foreach ($_subscriptionResult['lists'] as $_list) {
				foreach ($_list['errors'] as $_error) {
					$_errorString .= $_error['email'] . ' failed';
					$_errorString .= ' Code: ' . $_error['code'];
					$_errorString .= ' Message: ' . $_error['message'];
				}
			}
		}

		if (!empty($_errorString)) {

			$_errorString = $this->Language->Get('mc_error') . $_errorString;

			SWIFT_Loader::LoadLibrary('ErrorLog:ErrorLog');

			SWIFT_ErrorLog::Create(SWIFT_ErrorLog::TYPE_GENERAL, $_errorString);
		}

		return true;
	}
}

?>
