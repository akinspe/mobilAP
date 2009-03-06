<?php

require('inc/app_classes.php');

$App = new Application();

$PAGE_TITLE = "mobilAP: Announcements";
$PAGE = 'announcements';

$announcements = mobilAP_announcement::getAnnouncements();

include('templates/header.tpl');
include('templates/announcements.tpl');
include('templates/footer.tpl');

?>