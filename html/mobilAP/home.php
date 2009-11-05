<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('../mobilAP.php');

$user_session = new mobilAP_usersession();
$user = new mobilAP_user(true);

$data = array(
    array('title'=>'Welcome','id'=>'welcome'),
);

if (mobilAP::getConfig('SINGLE_SESSION_MODE')) {
    require_once('classes/mobilAP_session.php');
    if ($session = mobilAP_session::getSessionById(mobilAP_session::SESSION_SINGLE_ID)) {
        $data[] = array('title'=>$session->session_title,'id'=>'session');
    }
} else {
    $data[] = array('title'=>'Schedule','id'=>'schedule');
}
    
$data[] = array('title'=>'Directory','id'=>'directory');
$data[] = array('title'=>'Announcements','id'=>'announcements');

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

header('Content-type: application/json');
echo json_encode($data);

?>