<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('../mobilAP.php');

$user_session = new mobilAP_usersession();

if ($user_session->loggedIn()) {
    $user_session->logout();
    $data = $user_session;
} else{
    $data = mobilAP_error::throwError("Not logged in");
}

header('Content-type: application/json');
echo json_encode($data);

?>