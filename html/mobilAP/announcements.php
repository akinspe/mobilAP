<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('../mobilAP.php');
require_once('classes/mobilAP_announcement.php');

$user = new mobilAP_user(true);

if (isset($_POST['post'])) {
    $post_action = $_POST['post'];
    switch ($post_action)
    {
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

    $data = mobilAP::getAnnouncements();
    foreach ($data as $idx=>$announcement) {
        $data[$idx]->read = $announcement->hasRead($user->getUserID());
        $data[$idx]->user = mobilAP_user::getUserById($announcement->userID);
    }
}

header('Content-type: application/json');
echo json_encode($data);

?>