<?php

require_once('inc/app_classes.php');

$App = new Application();

if (!$schedule = mobilAP::getCache('mobilAP_schedule')) {
	$schedule = mobilAP::getSchedule();
	mobilAP::setCache('mobilAP_schedule', $schedule, 600);
}

$day_index = isset($_GET['day_index']) ? $_GET['day_index'] : 0;
$day_data = isset($schedule[$day_index]) ? $schedule[$day_index] : current($schedule);
$day_schedule = isset($day_data['schedule']) ? $day_data['schedule'] : array();

$PAGE_TITLE = 'mobilAP: Sessions';
$PAGE = 'sessions';

include('templates/header.tpl');
include('templates/session/sessions.tpl');
include('templates/footer.tpl');

?>