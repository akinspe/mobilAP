<?php

/*

* Copyright (c) 2008, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

ini_set('display_errors', 'off');
require('inc/model_classes.php');

$data = false;

//process some data
if (isset($_REQUEST['get'])) {
	switch ($_REQUEST['get'])
	{
		case 'schedule':
			if (!$data = mobilAP::getCache('mobilAP_schedule')) {
				$data = mobilAP::getSchedule();
				mobilAP::setCache('mobilAP_schedule', $data, 600);
			}
			break;
			
		case 'session':
			$user = new mobilAP_webuser();
			$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
			if ($data = mobilAP_session::getSessionByID($session_id)) {
				$data->session_links = $data->getLinks();
				$data->session_questions = $data->getQuestions();				
				$data->session_presenters = $data->getPresentersDirectory();
				$data->session_chat = $data->get_chat();
				$data->session_userdata = $data->getUserSubmissions($user->getUserToken());
			}
			
			break;
			
		case 'announcements':
			$user = new mobilAP_webuser();
			$data = mobilAP_announcement::getAnnouncements($user->getUserToken());
			foreach ($data as $idx=>$announcement) {
				$data[$idx]->read = $announcement->hasRead($user->getUserToken());
			}
			break;
		
		case 'evaluation_questions':
			$data = mobilAP::getEvaluationQuestions();
			break;
		
		case 'session_group':
			$session_group_id = isset($_REQUEST['session_group_id']) ? $_REQUEST['session_group_id'] : '';
			$data = mobilAP_session_group::getSessionGroupByID($session_group_id);
			break;
			
		case 'session_groups':
			$data = array_values(mobilAP_session_group::getSessionGroups());
			break;
			
		case 'question':
			$question_id = isset($_REQUEST['question_id']) ? $_REQUEST['question_id'] : '';
			if (!$data = mobilAP_poll_question::getQuestionById($question_id)) {
				$data = mobilAP_Error::throwError("Invalid question $question_id");
			}
			break;

		case 'user':
			$data = new mobilAP_webuser();
			break;
		
		case 'chat':
			$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
			$last_post = isset($_REQUEST['last_post']) ? $_REQUEST['last_post'] : 0;
			if ($session = mobilAP_session::getSessionByID($session_id)) {
				$data = $session->get_chat($last_post);
			}
			break;			
	
		case 'attendee_summary':
			if (!$data = mobilAP::getCache('mobilAP_attendee_summary')) {
				$data = mobilAP_attendee::getAttendeeSummary();
				mobilAP::setCache('mobilAP_attendee_summary', $data, 600);
			}
			break;	
	
		case 'attendee':
			$attendee_id = isset($_REQUEST['attendee_id']) ? $_REQUEST['attendee_id'] : '';
			if ($attendee = mobilAP_attendee::getAttendeeById($attendee_id)) {
				$data = $attendee;
			}
			break;
		case 'attendees':
			if (!$data = mobilAP::getCache('mobilAP_attendees')) {
				$data = mobilAP_attendee::getAttendees(array('only_active'=>getConfig('show_only_active_attendees')));
			}
			break;
			
		default:
			if (is_file('templates/' . $_REQUEST['get'] . '.tpl')) {
				$data = file_get_contents('templates/' . $_REQUEST['get'] . '.tpl');
			}
			break;

	}
} elseif (isset($_REQUEST['post'])) {
	$user = new mobilAP_webuser();
	switch ($_REQUEST['post'])
	{
		case 'chat':
			$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
			if ($session = mobilAP_session::getSessionByID($session_id)) {
				$post_text = isset($_POST['post_text']) ? $_POST['post_text'] : '';
				$data = $session->post_chat($post_text, $user->getUserToken());
				if (!mobilAP_Error::isError($data)) {
					$data = $session->get_chat();
				}
			} 
			break;
			
		case 'question':
			$question_id = isset($_REQUEST['question_id']) ? $_REQUEST['question_id'] : '';
			if ($question = mobilAP_poll_question::getQuestionById($question_id)) {
				
				$response = isset($_REQUEST['response']) ? $_REQUEST['response'] : array();
				$data = $question->submitAnswer($response, $user->getUserToken());
				if (!mobilAP_Error::isError($data)) {
					$data = $question;
				}
			} 
			
			break;
		case 'link':
			$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
			if ($session = mobilAP_session::getSessionByID($session_id)) {
				$link_url = isset($_REQUEST['link_url']) ? $_REQUEST['link_url'] : '';
				$link_text = isset($_REQUEST['link_text']) ? $_REQUEST['link_text'] : '';
				$result = $session->addLink($link_url, $link_text, $user->getUserToken());
				$data = $session->getLinks();
			} 
			
			break;

		case 'evaluation':
			$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
			if ($session = mobilAP_session::getSessionByID($session_id)) {
				$responses = isset($_REQUEST['responses']) ? $_REQUEST['responses'] : array();
				$data = $session->addEvaluation($user->getUserToken(), $responses);
			} 
			
			break;
		
		case 'readAnnouncement':
			$announcement_id = isset($_REQUEST['announcement_id']) ? $_REQUEST['announcement_id'] : '';
			if ($announcement = mobilAP_announcement::getAnnouncementByID($announcement_id)) {
				$data = $announcement->readAnnouncement($user->getUserToken());
			} else {
				$data = mobilAP_Error::throwError("Error getting announcement $announcement_id");
			}
			break;
		

	}
}

echo json_encode($data);

?>