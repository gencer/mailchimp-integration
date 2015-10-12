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
 * The Main Installer for MailChimp
 *
 * @author Ruchi Kothari
 */
class SWIFT_SetupDatabase_mailchimp extends SWIFT_SetupDatabase
{
	// Time interval for batch subscription to MailChimp Lists
	const TIME_INTERVAL = 10;


	/**
	 * Constructor
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __construct()
	{
		parent::__construct('mailchimp');

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
	 * Install the app
	 *
	 * @author Ruchi Kothari
	 *
	 * @param int $_pageIndex The Page Index
	 *
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function Install($_pageIndex)
	{
		parent::Install($_pageIndex);

		$this->ImportSettings();

		// ======= Pseudo Cron =======
		SWIFT_Cron::Create('SyncMailChimpSubscription', 'MailChimp', 'BatchSubscription', 'Subscribe', '0', self::TIME_INTERVAL, '0', true);

		return true;
	}

	/**
	 * Upgrade the Module
	 *
	 * @author Ruchi Kothari
	 *
	 * @param bool $_isForced
	 *
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function Upgrade($_isForced = false)
	{
		$this->ImportSettings();

		return parent::Upgrade($_isForced);
	}

	/**
	 * Uninstalls the app
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function Uninstall()
	{
		parent::Uninstall();

		SWIFT_Cron::DeleteOnName(array('SyncMailChimpSubscription'));

		return true;
	}

	/**
	 * Import the MailChimp Integration Settings
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" on Success, "false" otherwise
	 */
	private function ImportSettings()
	{
		$_SWIFT = SWIFT::GetInstance();

		$this->Load->Library('Language:LanguageManager');

		$_languageIDList = SWIFT_Language::GetMasterLanguageIDList();

		try {
			$this->SettingsManager->Import('./' . SWIFT_APPSDIRECTORY . '/mailchimp/config/settings.xml');
		} catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
		}

		// Adding merge language to first found master language
		if (_is_array($_languageIDList)) {
			$this->LanguageManager->Merge($_languageIDList[0], './' . SWIFT_APPSDIRECTORY . '/mailchimp/config/mailchimp.language.xml');
		}

		return true;
	}
}

?>