<?php

require_once('../mobilAP.php');

$userID = isset($_GET['userID']) ? $_GET['userID'] : '';
if (!$data = mobilAP_User::getUserByID($userID)) {
    $data = new mobilAP_User();
}

header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>