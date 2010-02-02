<?php

require_once('../mobilAP.php');

if (isset($_REQUEST['letter'])) {
    $data = mobilAP_User::getUsers(array('letter'=>$_REQUEST['letter']));
} elseif (isset($_REQUEST['q'])) {
    $data = mobilAP_User::getUsers(array('search'=>$_REQUEST['q']));
} elseif (!$data = mobilAP_Cache::getCache('mobilAP_users')) {
    $data = mobilAP_User::getUsers();
}

header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>