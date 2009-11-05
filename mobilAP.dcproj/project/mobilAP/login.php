<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('../mobilAP.php');

$login_userID = isset($_POST['login_userID']) ? $_POST['login_userID'] : null;
$login_pword = isset($_POST['login_pword']) ? $_POST['login_pword'] : '';

$user_session = new mobilAP_UserSession();

// initialize result
$login_result = mobilAP_Error::throwError("Invalid request");

if (!empty($login_userID)) {

	$login_result = $user_session->login($login_userID, $login_pword);
	
	if (mobilAP_Error::isError($login_result)) {
		switch($login_result->getCode())
		{
			case mobilAP_UserSession::USER_ALREADY_LOGGED_IN:
				$message = 'You are already logged in.';
                break;
	
			case mobilAP_UserSession::USER_ADMIN_LOGIN_FAILURE:
			case mobilAP_UserSession::USER_LOGIN_FAILURE:
				$message = 'Login Failed. Please ensure your email address and password are correct.';
				break;

			case mobilAP_UserSession::USER_REQUIRES_PASSWORD:
				$message = 'This account requires a password';
				break;
	
			case mobilAP_UserSession::USER_NOT_FOUND:
				$message = "Login Failed. An account for $login_userID could not be found.";
				$login_userID = '';
				break;

			case mobilAP_UserSession::USER_CREATE_NEW_USER:
				$message = "An account for $login_userID could not be found. Would you like to create a new account?";
				break;
	
			default:
				$message = "There was an unknown error during login. ($login_result) Please contact the site administrator.";
				break;
		}
		
		if (isset($message)) {
			$login_result->setMessage($message);
            $login_result->setUserInfo($user_session);
		}
		
	} else {
        $login_result = $user_session;
	}
}
header('Content-type: application/json');
echo json_encode($login_result);

?>