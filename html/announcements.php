<?php

require('inc/app_classes.php');

$App = new Application();

$PAGE_TITLE = "mobilAP: Announcements";
$PAGE = 'announcements';

if (getConfig('CONTENT_PRIVATE') && !$App->is_LoggedIn()) {

	include("templates/header.tpl");
	include("templates/nav.tpl");
	include("templates/not_logged_in.tpl");
	include("templates/footer.tpl");
	exit();
}

$announcements = mobilAP_announcement::getAnnouncements();

include('templates/header.tpl');
include('templates/announcements.tpl');
include('templates/footer.tpl');

?>