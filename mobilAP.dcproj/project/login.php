<?php

/*

* Copyright (c) 2008, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/


require_once('inc/app_classes.php');
$App = new Application();

$login_userID = isset($_POST['login_userID']) ? $_POST['login_userID'] : null;
$login_pword = isset($_POST['login_pword']) ? $_POST['login_pword'] : getConfig('default_password');
$action = isset($_GET['action']) ? $_GET['action'] : null;
$referrer = isset($_REQUEST['referrer']) ? urldecode($_REQUEST['referrer']) : '';
$js = isset($_REQUEST['js']) ? true : false;


$PAGE_TITLE ='mobilAP Login';

$login_file='login.tpl';
$login_result = $_POST;
$user = new mobilAP_webuser();

if ( (isset($_POST['login_submit_x']) || isset($_POST['login_submit'])) && !empty($login_userID) && !empty($login_pword) ) {

	$login_result = $user->login($login_userID, $login_pword, null);
	
	if (mobilAP_Error::isError($login_result)) {
		switch($login_result->getCode())
		{
			case mobilAP_webuser::USER_ALREADY_LOGGED_IN:
				$message = 'You are already logged in.';
				break;
	
			case mobilAP_webuser::USER_LOGIN_FAILURE:
				$message = 'Login Failed. Please ensure your email address and password are correct.';
				break;
	
			case mobilAP_webuser::USER_NOT_FOUND:
				$message = 'Login Failed. An account for this email address could not be found.';
				break;
	
			default:
				$message = "There was an unknown error during login. ($login_result) Please contact the site administrator.";
				break;
		}
		
		if (isset($message)) {
			$login_result->setMessage($message);
			$App->addErrorMessage($message);
		}
		
	} else {
		$login_result = $_POST;
		if (!$js) {
			
			$url = (!empty($referrer)) ? $referrer : getConfig('DEFAULT_LOGIN_URL');
			header("Location: $url");
			exit();		
		} else {
			$login_result = $user;
		}
	}
} elseif ($action == 'logout') { 
    if ($user->is_LoggedIn()) {
        $user->logout();
		if (!$js) {
			$url = !empty($referrer) ? $referrer : getConfig('DEFAULT_LOGOUT_URL');
			header("Location: $url");
			exit();		
		} else {
			$login_result = $user;
		}
    }
} else {
    if ($user->is_loggedIn()) {
    	$login_result = mobilAP_Error::throwError('You are already logged in.');
        $App->addErrorMessage($login_result->getMessage());
    } else {
        $login_file = 'login.tpl';
    }
}

if ($js) {
	echo json_encode($login_result);
} else {

	include('templates/header.tpl');
	include("templates/login/$login_file");
	include('templates/footer.tpl');
	
}

?>