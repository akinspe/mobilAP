<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('../mobilAP.php');
require_once('classes/mobilAP_announcement.php');

$user_session = new mobilAP_UserSession();
$user = new mobilAP_user(true);

if (isset($_POST['post'])) {
    $post_action = $_POST['post'];
    switch ($post_action)
    {
        case 'readAnnouncement':
            $announcement_id = isset($_REQUEST['announcement_id']) ? $_REQUEST['announcement_id'] : '';
            if ($announcement = mobilAP_announcement::getAnnouncementById($announcement_id)) {
				$data = $announcement->readAnnouncement($user->getUserID(),true);
            } else {
                $data = mobilAP_Error::throwError("Invalid announcement");
            }
            break;
        case 'unreadAnnouncement':
            $announcement_id = isset($_REQUEST['announcement_id']) ? $_REQUEST['announcement_id'] : '';
            if ($announcement = mobilAP_announcement::getAnnouncementById($announcement_id)) {
				$data = $announcement->readAnnouncement($user->getUserID(),false);
            } else {
                $data = mobilAP_Error::throwError("Invalid announcement");
            }
            break;
        case 'deleteAnnouncement':
            $announcement_id = isset($_POST['announcement_id']) ? $_POST['announcement_id'] : '';
            if ($announcement = mobilAP_announcement::getAnnouncementById($announcement_id)) {
                $data = $announcement->deleteAnnouncement($user->getUserID());
            } else {
                $data = mobilAP_Error::throwError("Invalid announcement");
            }
            break;
        case 'updateAnnouncement':
            $announcement_id = isset($_POST['announcement_id'   ]) ? $_POST['announcement_id'] : '';
            if (!$announcement = mobilAP_announcement::getAnnouncementById($announcement_id)) {
                $data = mobilAP_Error::throwError("Invalid announcement");
                break;
            }
        case 'addAnnouncement':
            $announcement_title = isset($_POST['announcement_title']) ? $_POST['announcement_title'] : '';
            $announcement_text = isset($_POST['announcement_text']) ? $_POST['announcement_text'] : '';
            if ($post_action=='addAnnouncement') {
                $announcement = new mobilAP_announcement();
            }

			$announcement->setTitle($announcement_title);
			$announcement->setText($announcement_text);
            
            if ($post_action == 'addAnnouncement') {
                $data = $announcement->postAnnouncement($user->getUserID());
            } else {
                $data = $announcement->updateAnnouncement($user->getUserID());
            }
            break;
        default:
            $data = mobilAP_Error::throwError("Invalid request",-1, $_POST['post']);
            break;
    }
} else {
	if (mobilAP::getConfig('CONTENT_PRIVATE') && !$user_session->loggedIn()) {
		$data = array();
	} else {
		$data = mobilAP::getAnnouncements();
	}
}

header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>