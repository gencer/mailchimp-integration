<?php
/**
 * @copyright      2001-2015 Kayako
 * @license        https://www.freebsd.org/copyright/freebsd-license.html
 * @link           https://github.com/kayako/mailchimp-integration
 */

/**
 * The MailChimp Settings Manager Class
 *
 * @author Ruchi Kothari
 */
class Controller_SettingsManager extends Controller_admin
{
	// Core Constants
	const MENU_ID = 100;
	const NAVIGATION_ID = 1;

	/**
	 * Constructor
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __construct()
	{
		parent::__construct();

		$this->Language->Load('mailchimp');
		$this->Language->Load('settings');

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
	 * Render the MailChimp Settings
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" on Success, "false" otherwise
	 * @throws SWIFT_Exception If the Class is not Loaded
	 */
	public function Index()
	{
		$_SWIFT = SWIFT::GetInstance();

		if (!$this->GetIsClassLoaded()) {
			throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

			return false;
		}

		$this->UserInterface->Header($this->Language->Get('mc_name') . ' > ' . $this->Language->Get('mc_settingname'), self::MENU_ID, self::NAVIGATION_ID);

		if ($_SWIFT->Staff->GetPermission('admin_canupdatesettings') == '0') {
			$this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
		} else {
			$this->UserInterface->Start(get_class($this), '/MailChimp/SettingsManager/Index', SWIFT_UserInterface::MODE_INSERT, false);
			$this->SettingsManager->Render($this->UserInterface, SWIFT_SettingsManager::FILTER_NAME, array('settings_mc'));
			$this->UserInterface->End();
		}

		$this->UserInterface->Footer();

		return true;
	}
}
?>