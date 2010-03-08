<?php

require_once('../mobilAP.php');

$user_session = new mobilAP_UserSession();

if (mobilAP::getConfig('CONTENT_PRIVATE') && !$user_session->loggedIn()) {
	$data = array();
} elseif (isset($_REQUEST['letter'])) {
    $data = mobilAP_User::getUsers(array('letter'=>$_REQUEST['letter']));
} elseif (isset($_REQUEST['q'])) {
    $data = mobilAP_User::getUsers(array('search'=>$_REQUEST['q']));
} elseif (!$data = mobilAP_Cache::getCache('mobilAP_users')) {
    $data = mobilAP_User::getUsers();
}

header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>