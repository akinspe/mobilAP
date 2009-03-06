<?php

//require('inc/model_classes.php');

require_once('inc/app_classes.php');

$App = new Application();

$PAGE_TITLE = 'Attendee Directory';
$PAGE = 'attendee_directory';

if (!$attendees = mobilAP::getCache('mobilAP_attendees')) {
	$attendees = mobilAP_attendee::getAttendees(array('only_active'=>getConfig('show_only_active_attendees')));
}


$usedLetters = array();
$letters = utils::getLetters();

foreach ($attendees as $attendee) {
	$letter = strtoupper($attendee->LastName[0]);
	if (!in_array($letter, $usedLetters)) {
		$usedLetters[] = $letter;
	}
}

$attendee = isset($_GET['view_attendee']) ? mobilAP_attendee::getAttendeeById($_GET['view_attendee']) : false;


include('templates/header.tpl');
include('templates/attendee_directory.tpl');
include('templates/footer.tpl');

?>