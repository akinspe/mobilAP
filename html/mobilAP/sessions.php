<?php

require_once('../mobilAP.php');

$data = mobilAP::getSessions();

header('Content-type: application/json');
echo json_encode($data);

?>