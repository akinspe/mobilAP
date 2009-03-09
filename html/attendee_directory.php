<?php

require_once('inc/app_classes.php');

if (!getConfig('SHOW_ATTENDEE_DIRECTORY')) {
	header("Location: " . getConfig('DEFAULT_LOGIN_URL'));
	exit();
}

$App = new Application();

$PAGE_TITLE = 'Attendee Directory';
$PAGE = 'attendee_directory';

if (!$attendees = mobilAP::getCache('mobilAP_attendees')) {
	$attendees = mobilAP_attendee::getAttendees(array('only_active'=>getConfig('show_only_active_attendees')));
}

$attendee = isset($_GET['view_attendee']) ? mobilAP_attendee::getAttendeeById($_GET['view_attendee']) : false;
$template_file = $attendee ? 'attendee_detail.tpl' : 'attendee_directory.tpl';

if ($attendee) {
} else {
	$usedLetters = array();
	$attendee_total = 0;
	$checked_in = 0;
	$letters = utils::getLetters();
	
	foreach ($attendees as $attendee) {
		$attendee_total++;
		if ($attendee->checked_in) {
			$checked_in++;
		}
		$letter = strtoupper($attendee->LastName[0]);
		if (!in_array($letter, $usedLetters)) {
			$usedLetters[] = $letter;
		}
	}
}


include('templates/header.tpl');
include("templates/$template_file");
include('templates/footer.tpl');

?>