<?php

/*

* Copyright (c) 2008, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/


require_once('inc/app_classes.php');

if (getConfig('SETUP_COMPLETE')) {
	header("Location: index.php");
	exit();
}

$sql = "SHOW TABLES";
$result = mobilAP::query($sql);
ini_set('display_errors','on');

$create_tables = array(
	"CREATE TABLE IF NOT EXISTS `announcements` (`announcement_id` int(11) NOT NULL auto_increment, `announcement_title` varchar(50) default NULL, `announcement_timestamp` int(11) default NULL, `attendee_id` char(32) default NULL, `announcement_text` text, PRIMARY KEY  (`announcement_id`))",
	"CREATE TABLE IF NOT EXISTS `announcements_read` ( `announcement_id` int(11) unsigned NOT NULL default '0', `attendee_id` char(32) NOT NULL default '', `read_timestamp` int(11) default NULL,  PRIMARY KEY  (`attendee_id`,`announcement_id`))",
	"CREATE TABLE IF NOT EXISTS `attendees` (`attendee_id` char(32) NOT NULL default '', `salutation` char(4) default NULL, `FirstName` varchar(50) default NULL, `LastName` varchar(50) default NULL, `organization` varchar(50) default NULL, `title` varchar(50) default NULL, `dept` varchar(50) default NULL, `city` varchar(50) default NULL, `state` varchar(2) default NULL, `country` varchar(2) default NULL, `email` varchar(50) default NULL, `phone` varchar(15) default NULL, `md5` char(32) default NULL, `login_last` int(11) default NULL, `login_now` int(11) default NULL, `checked_in` int(11) default NULL, `directory_active` tinyint(1) NOT NULL default '0', `admin` tinyint(1) NOT NULL default '0', `bio` text, PRIMARY KEY (`attendee_id`), UNIQUE KEY `email` (`email`), KEY `directory_active` (`directory_active`))",
	"CREATE TABLE IF NOT EXISTS `attendees_import` (`import_id` int(11) NOT NULL auto_increment, `salutation` char(4) default NULL, `LastName` varchar(50) default NULL, `FirstName` varchar(50) default NULL, `organization` varchar(50) default NULL, `title` varchar(50) default NULL, `dept` varchar(50) default NULL, `city` varchar(50) default NULL, `state` varchar(2) default NULL, `country` varchar(2) default NULL, `email` varchar(50) default NULL, `phone` varchar(15) default NULL, `password` varchar(32) default NULL, PRIMARY KEY (`import_id`))",
	"CREATE TABLE IF NOT EXISTS `config` (`config_var` varchar(32) NOT NULL default '', `config_value` varchar(100) default NULL, PRIMARY KEY  (`config_var`))",
	"CREATE TABLE IF NOT EXISTS `evaluation_question_responses` (`question_index` tinyint(4) unsigned NOT NULL default '0', `response_index` tinyint(4) unsigned NOT NULL default '0', `response_text` varchar(50) default NULL, `response_value` smallint(6) default NULL,  PRIMARY KEY  (`question_index`,`response_index`))",
	"CREATE TABLE IF NOT EXISTS `evaluation_questions` (`question_index` tinyint(4) unsigned NOT NULL default '0',`question_text` varchar(100) default NULL,  `question_response_type` char(1) default NULL,  PRIMARY KEY  (`question_index`))",
	"CREATE TABLE IF NOT EXISTS `poll_answers` (`answer_id` int(11) NOT NULL auto_increment, `question_id` int(11) unsigned default NULL, `response_value` smallint(5) unsigned default NULL, `response_timestamp` int(11) default NULL, `response_userID` char(32) default NULL, PRIMARY KEY (`answer_id`), KEY `question_id` (`question_id`))",
	"CREATE TABLE IF NOT EXISTS `poll_questions` ( `question_id` int(10) unsigned NOT NULL auto_increment, `session_id` char(3) default NULL, `index` smallint(6) unsigned NOT NULL default '0', `question_text` varchar(200) default NULL, `question_minchoices` tinyint(4) unsigned NOT NULL default '0', `question_maxchoices` tinyint(4) unsigned NOT NULL default '0', `response_type` char(1) default NULL, `chart_type` char(3) default NULL, `question_active` tinyint(1) NOT NULL default '-1', `question_list_text` varchar(50) default NULL, PRIMARY KEY (`question_id`), UNIQUE KEY `poll_id` (`session_id`,`index`))",
	"CREATE TABLE IF NOT EXISTS `poll_responses` (`question_id` int(11) NOT NULL default '0', `index` smallint(6) unsigned NOT NULL default '0', `response_value` smallint(6) unsigned NOT NULL default '0', `response_text` varchar(200) default NULL, PRIMARY KEY (`question_id`,`index`), UNIQUE KEY `question_id` (`question_id`,`response_value`), UNIQUE KEY `question_id_2` (`question_id`,`response_text`))",
	"CREATE TABLE IF NOT EXISTS `schedule` (`schedule_id` int(11) NOT NULL auto_increment, `start_date` datetime default NULL, `start_ts` int(11) default NULL, `end_date` datetime default NULL, `end_ts` int(11) default NULL, `title` varchar(100) default NULL, `detail` varchar(100) default NULL, `session_id` char(3) default NULL, `room` char(32) default NULL, `session_group_id` int(11) default NULL, PRIMARY KEY (`schedule_id`), KEY `session_id` (`session_id`))",
	"CREATE TABLE IF NOT EXISTS `session_chat` (`post_id` int(10) unsigned NOT NULL auto_increment, `session_id` char(3) default NULL, `post_timestamp` int(11) default NULL, `post_user` char(32) default NULL, `post_text` text, PRIMARY KEY (`post_id`), KEY `session_id` (`session_id`))",
	"CREATE TABLE IF NOT EXISTS `session_evaluations` (`evaluation_id` int(11) NOT NULL auto_increment, `session_id` char(3) default NULL, `post_user` char(32) default NULL, `post_timestamp` int(11) default NULL, PRIMARY KEY (`evaluation_id`), KEY `session_id` (`session_id`))",
	"CREATE TABLE IF NOT EXISTS `session_groups` (`session_group_id` int(11) NOT NULL auto_increment, `session_group_title` varchar(100) default NULL, `session_group_detail` varchar(100) default NULL, PRIMARY KEY  (`session_group_id`))",
	"CREATE TABLE IF NOT EXISTS `session_links` (`link_id` int(11) NOT NULL auto_increment, `session_id` char(3) default NULL, `link_url` varchar(200) default NULL, `link_text` varchar(150) default NULL, `post_user` char(32) default NULL, `link_type` char(1) default NULL, `post_timestamp` int(11) default NULL, PRIMARY KEY (`link_id`), KEY `session_id` (`session_id`))",
	"CREATE TABLE IF NOT EXISTS `session_presenters` (`session_id` char(3) NOT NULL default '', `presenter_id` char(32) NOT NULL default '', `index` tinyint(4) unsigned default NULL, PRIMARY KEY (`session_id`,`presenter_id`), UNIQUE KEY `session_id` (`session_id`,`index`))",
	"CREATE TABLE IF NOT EXISTS `sessions` (`session_id` char(3) NOT NULL default '', `session_title` varchar(100) default NULL, `session_abstract` text, `session_flags` int(10) unsigned NOT NULL default 15, PRIMARY KEY (`session_id`))"
);

foreach ($create_tables as $sql) {
	$result = mobilAP::query($sql);
}

$App = new Application();
$template_file = 'setup.tpl';

$email = isset($_POST['email']) ? $_POST['email'] : '';
$FirstName = isset($_POST['FirstName']) ? $_POST['FirstName'] : '';
$LastName = isset($_POST['LastName']) ? $_POST['LastName'] : '';
if (isset($_POST['submit_setup'])) {
	$password = isset($_POST['password']) ? $_POST['password'] : getConfig('default_password');
	$password_verify = isset($_POST['password_verify']) ? $_POST['password_verify'] : getConfig('default_password');
	$attendee = new mobilAP_attendee();
	$ok = true;
	
	if (!$attendee->setName($FirstName, $LastName)) {
		$ok = false;
		$App->addErrorMessage("Please include your full name");
	}

	if (!$attendee->setEmail($email)) {
		$ok = false;
		$App->addErrorMessage("Please include a valid email");
	}

	if ($password == '') {
		$ok = false;
		$App->addErrorMessage("Please include a valid password");
	}

	if ($password != $password_verify) {
		$ok = false;
		$App->addErrorMessage("You did not verify your password correctly.");
	}
	
	$attendee->setAdmin(true);
	
	if ($ok) {
		$result = $attendee->createAttendeeFromObj();
		
		if (mobilAP_Error::isError($result)) {
			$App->addErrorMessage("Error creating user: " . $result->getMessage());
		} else {
			$attendee->setPassword($password);
			$attendee->updateAttendee();
			$template_file="setup_success.tpl";

			mobilAP::setConfig('SETUP_COMPLETE', -1);
		}
	} else {
	}
}

include('templates/header.tpl');
include("templates/setup/$template_file");
include('templates/footer.tpl');
