<?php

require_once('../mobilAP.php');

$user_session = new mobilAP_UserSession();

if (mobilAP::getConfig('CONTENT_PRIVATE') && !$user_session->loggedIn()) {
	$data = array();
} else {
	$data = mobilAP::getSessions();
}


header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>