<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('../mobilAP.php');

$user_session = new mobilAP_usersession();
$user = new mobilAP_user(true);

$data = array();

if (mobilAP::getConfig('HOME_SHOW_WELCOME')) {
	$data[] = array('title'=>mobilAP::getConfig('HOME_WELCOME'),'id'=>'welcome');
}

if (mobilAP::getConfig('HOME_SHOW_SCHEDULE')) {

	if (mobilAP::getConfig('SINGLE_SESSION_MODE')) {
		require_once('classes/mobilAP_session.php');
		if ($session = mobilAP_session::getSessionById(mobilAP_session::SESSION_SINGLE_ID)) {
			$data[] = array('title'=>$session->session_title,'id'=>'session');
		}
	} else {
		$data[] = array('title'=>mobilAP::getConfig('HOME_SCHEDULE'),'id'=>'schedule');
	}
}

if (mobilAP::getConfig('HOME_SHOW_DIRECTORY')) {
	$data[] = array('title'=>mobilAP::getConfig('HOME_DIRECTORY'),'id'=>'directory');
}

if (mobilAP::getConfig('HOME_SHOW_ANNOUNCEMENTS')) {
	$data[] = array('title'=>mobilAP::getConfig('HOME_ANNOUNCEMENTS'),'id'=>'announcements');
}

if ($user_session->loggedIn()) {
    $data[] = array('title'=>'Profile','id'=>'profile');
    if ($user->isSiteAdmin()) {
        $data[] = array('title'=>'Admin','id'=>'admin');
    }
}

$data[] = array('title'=>$user_session->loggedIn() ? 'Logout' : 'Login','id'=>$user_session->loggedIn() ? 'logout': 'login');

if (!mobilAP::isSetup()) {
    $data = array(
        array('title'=>'Setup', 'id'=>'setup')
    );
}

header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>