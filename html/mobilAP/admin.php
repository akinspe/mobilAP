<?php

require_once('../mobilAP.php');
$content_type = 'application/json';
$user_session = new mobilAP_UserSession();
$_user = new mobilAP_user(true);
$data = new mobilAP_Error("Invalid request");

if (!$_user->isSiteAdmin()) {
	unset($_POST['post']);
	$data = new mobilAP_Error("Unauthorized");
}

if (isset($_POST['post'])) {
    $post_action = $_POST['post'];
    switch ($post_action)
    {
        case 'updateIcon':
			$file = isset($_FILES['adminContentWebclipIcon']) ? $_FILES['adminContentWebclipIcon'] : array();
			$data = mobilAP::uploadWebClipIcon($file);

            $content_type = 'text/html';
            break;
	}


}

header("Content-type: $content_type; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>