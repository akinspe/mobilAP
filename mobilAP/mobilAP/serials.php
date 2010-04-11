<?php

require_once('../mobilAP.php');
$user_session = new mobilAP_UserSession();

if (isset($_GET['reset'])) {
	$user = $user_session->getUser();
	if ($user->isSiteAdmin()) {
		mobilAP::resetSerials();
	}
}

if (isset($_GET['key'])) {
    $data = array($_GET['key']=>mobilAP::getSerialValue($_GET['key']));
} else {
    $data = mobilAP::getSerials(mobilAP::SERIAL_TYPE_DATA);
	$data['user'] = $user_session->loggedIn() ? mobilAP::getSerialValue($user_session->getUserID()) : 0;
}

if (mobilAP::getConfig('CONTENT_PRIVATE') && !$user_session->loggedIn()) {
	foreach ($data as $key=>$value) {
		if ($key != 'config') {
			$data[$key] = 0;
		}
	}
}

header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>