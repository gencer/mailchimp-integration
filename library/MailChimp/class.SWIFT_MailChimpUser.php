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
 * Library to manage User Information
 *
 * @author Ruchi Kothari
 */
class SWIFT_MailChimpUser extends SWIFT_Library
{
	private $_email;
	private $_firstName;
	private $_lastName;


	/**
	 * Constructor
	 *
	 * @author Ruchi Kothari
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function __construct($_email, $_firstName, $_lastName)
	{
		parent::__construct();

		$this->SetEmail($_email);
		$this->SetFirstName($_firstName);
		$this->SetLastName($_lastName);

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
	 * Email Setter function
	 *
	 * @author Ruchi Kothari
	 *
	 * @param string $_email email address of user
	 *
	 * @return bool "true" on Success, "false" otherwise
	 * @throws SWIFT_User_Exception If email address is invalid
	 */
	public function SetEmail($_email)
	{
		if (trim($_email) == '' || !IsEmailValid($_email)) {
			throw new SWIFT_User_Exception("Invalid Email Address: " . SWIFT_INVALIDDATA);

			return false;
		}

		$this->_email = trim($_email);

		return true;
	}

	/**
	 * First Name Setter function
	 *
	 * @author Ruchi Kothari
	 *
	 * @param string $_firstName user's first name
	 *
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function SetFirstName($_firstName)
	{
		if (trim($_firstName) == '') {
			return false;
		}

		$this->_firstName = strip_tags($_firstName);

		return true;
	}

	/**
	 * Last Name Setter function
	 *
	 * @author Ruchi Kothari
	 *
	 * @param string $_lastName user's last name
	 *
	 * @return bool "true" on Success, "false" otherwise
	 */
	public function SetLastName($_lastName)
	{
		if (trim($_lastName) == '') {
			return false;
		}

		$this->_lastName = strip_tags($_lastName);

		return true;
	}

	/**
	 * Email Getter function
	 *
	 * @author Ruchi Kothari
	 * @return string email address of user
	 */
	public function GetEmail()
	{
		return $this->_email;
	}

	/**
	 * First Name Getter function
	 *
	 * @author Ruchi Kothari
	 * @return string first name of user
	 */
	public function GetFirstName()
	{
		return $this->_firstName;
	}

	/**
	 * Last Name Getter function
	 *
	 * @author Ruchi Kothari
	 * @return string last name of user
	 */
	public function GetLastName()
	{
		return $this->_lastName;
	}
}

?>
