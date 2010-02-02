<?php

require_once('../mobilAP.php');

$data = mobilAP::getSessions();

header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>