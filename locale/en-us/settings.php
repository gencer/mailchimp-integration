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

$__LANG = array(
	// ======= General =======
	'settings_mc'        => 'MailChimp',
	'mc_general'         => 'MailChimp Settings',
	'mc_apikey'          => 'MailChimp API Key',
	'd_mc_apikey'        => 'You can create an API key in your <u><a href="http://admin.mailchimp.com/account/api">MailChimp API dashboard</a></u>. Enter it here, press update and you can then proceed to configure your MailChimp integration.',

	'mc_optin'           => 'Send Users an Email to Confirm Subscription',
	'd_mc_optin'         => 'If enabled, a confirmation email will be sent to users who are subscribed to your MailChimp lists, asking them to click on a link to confirm their subscription.',

	'mc_delete_member'   => 'Remove User from MailChimp List When Unsubscribing',
	'd_mc_delete_member' => 'If enabled, users in your MailChimp lists will be deleted when they are unsubscribed. If disabled, users will simply be unsubscribed and will remain in your MailChimp lists.',

	'mc_send_goodbye'    => '\'Good Bye\' Emails',
	'd_mc_send_goodbye'  => 'If enabled, MailChimp good bye emails will be sent to users who are unsubscribed.',

	'mc_send_notify'     => 'Send Unsubscribe Notifications',
	'd_mc_send_notify'   => 'Each MailChimp list can have an email address to which notifications will be sent when a user unsubscribes. If enabled, notifications will be sent.',

	'mc_send_welcome'    => 'Send a Welcome Email',
	'd_mc_send_welcome'  => 'If enabled, new users to the MailChimp lists will receive a welcome email. If \'Send Users an Email to Confirm Subscription\' is enabled, this setting has no effect.',

	'mc_emailtype'       => 'Email Content Type',
	'd_mc_emailtype'     => 'Each new user that gets added to your MailChimp lists will receive this email content type priority.',

	'mc_lists'           => 'MailChimp Lists',
	'd_mc_lists'         => 'Select the lists to which your helpdesk users will automatically be subscribed to.',

	'mc_mailtype_html'   => 'HTML',
	'mc_mailtype_text'   => 'Text',
	'mc_mailtype_mobile' => 'Mobile',

	'mc_nolistavailable' => '--- No MailChimp Lists Available ---',
	'mc_error'           => '--- MailChimp Error Encountered ---',
);
?>