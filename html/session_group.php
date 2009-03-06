<?php

require_once('inc/app_classes.php');

$App = new Application();
$session_group_id = isset($_REQUEST['session_group_id']) ? $_REQUEST['session_group_id'] : '';
if (!$session_group = mobilAP_session_group::getSessionGroupByID($session_group_id)) {
	include('sessions.php');
	exit();
}

$PAGE_TITLE = $session_group->session_group_title;
$PAGE = 'sessions';

include('templates/header.tpl');
include('templates/session/session_group.tpl');
include('templates/footer.tpl');

?>