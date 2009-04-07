<?php

require_once('inc/app_classes.php');

$App = new Application();

$PAGE_TITLE = getConfig('SITE_TITLE') . ': Sessions';
$PAGE = 'sessions';

if (getConfig('CONTENT_PRIVATE') && !$App->is_LoggedIn()) {

	include("templates/header.tpl");
	include("templates/nav.tpl");
	include("templates/not_logged_in.tpl");
	include("templates/footer.tpl");
	exit();
}

if (!$schedule = mobilAP::getCache(SITE_PREFIX . '_mobilAP_schedule')) {
	$schedule = mobilAP::getSchedule();
	mobilAP::setCache(SITE_PREFIX . '_mobilAP_schedule', $schedule, 600);
}

$day_index = isset($_GET['day_index']) ? $_GET['day_index'] : 0;
$day_data = isset($schedule[$day_index]) ? $schedule[$day_index] : current($schedule);
$day_schedule = isset($day_data['schedule']) ? $day_data['schedule'] : array();


include('templates/header.tpl');
include('templates/session/sessions.tpl');
include('templates/footer.tpl');

?>