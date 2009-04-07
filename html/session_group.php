<?php

require_once('inc/app_classes.php');

$App = new Application();

$PAGE = 'sessions';

if (getConfig('CONTENT_PRIVATE') && !$App->is_LoggedIn()) {

	$PAGE_TITLE = "Unauthorized";
	include("templates/header.tpl");
	include("templates/nav.tpl");
	include("templates/not_logged_in.tpl");
	include("templates/footer.tpl");
	exit();
}

$session_group_id = isset($_REQUEST['session_group_id']) ? $_REQUEST['session_group_id'] : '';
if (!$session_group = mobilAP_session_group::getSessionGroupByID($session_group_id)) {
	include('sessions.php');
	exit();
}

$PAGE_TITLE = $session_group->session_group_title;

include('templates/header.tpl');
include('templates/session/session_group.tpl');
include('templates/footer.tpl');

?>