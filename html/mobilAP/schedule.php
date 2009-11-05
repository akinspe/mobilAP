<?php

require_once('../mobilAP.php');
require_once('classes/mobilAP_schedule.php');

if (isset($_POST['post'])) {
    $post_action = $_POST['post'];
    switch ($post_action)
    {
        case 'delete':
            $schedule_id = isset($_POST['schedule_id']) ? $_POST['schedule_id'] : '';
            if ($schedule_item = mobilAP_schedule_item::getScheduleItem($schedule_id)) {
                $data = $schedule_item->deleteItem();
            } else {
                $data = mobilAP_Error::throwError("Unable to load schedule item for id " . $schedule_id,-2, $schedule_id);
            } 
            break;
        case 'update':
            $schedule_id = isset($_POST['schedule_id']) ? $_POST['schedule_id'] : '';
            if (!$schedule_item = mobilAP_schedule_item::getScheduleItem($schedule_id)) {
                $data = mobilAP_Error::throwError("Unable to load schedule item for id " . $schedule_id,-2, $schedule_id);
                break;
            }
        case 'add':
            if ($post_action=='add') {
                $schedule_item = new mobilAP_schedule_item();
            }
            
			$start_time = isset($_POST['start_time']) ? $_POST['start_time'] : null;
			$end_time = isset($_POST['end_time']) ? $_POST['end_time'] : null;
			$detail = isset($_POST['detail']) ? $_POST['detail'] : '';
			$room = isset($_POST['room']) ? $_POST['room'] : '';
			$session_id = isset($_POST['session_id']) ? $_POST['session_id'] : '';
			$schedule_item->setStartTime($start_time);
			$schedule_item->setEndTime($end_time);
			$schedule_item->setDetail($detail);
			$schedule_item->setRoom($room);
			$schedule_item->setSession($session_id);
            
            if ($post_action=='add') {
                $data = $schedule_item->createItem();
            } else {
                $data = $schedule_item->updateItem();
            }
            break;
        default:
            $data = mobilAP_Error::throwError("Invalid request",-1, $_POST['post']);
            break;
    }
} elseif (!$data = mobilAP_Cache::getCache('mobilAP_schedule')) {
    $data = mobilAP::getSchedule();
    mobilAP_Cache::setCache('mobilAP_schedule', $data, 600);
}

header('Content-type: application/json');
echo json_encode($data);

?>