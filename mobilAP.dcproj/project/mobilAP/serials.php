<?php

require_once('../mobilAP.php');

if (isset($_GET['key'])) {
    $data = array($_GET['key']=>mobilAP::getSerialValue($_GET['key']));
} else {
    $data = mobilAP::getSerials();
}

header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>