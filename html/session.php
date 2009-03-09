<?php

require_once('inc/app_classes.php');

$App = new Application();
$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
if (!$session = mobilAP_session::getSessionByID($session_id)) {
	include('sessions.php');
	exit();
}

$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'info';
$session->session_questions = $session->getQuestions();
$session->session_userdata = $session->getUserSubmissions($App->getUserToken());


switch ($view)
{
	case 'info':
		$session->session_presenters = $session->getPresentersDirectory();
		break;
	case 'links':
		if (isset($_REQUEST['add_link'])) {
			$link_url = isset($_REQUEST['link_url']) ? $_REQUEST['link_url'] : '';
			$link_text = isset($_REQUEST['link_text']) ? $_REQUEST['link_text'] : '';
			$result = $session->addLink($link_url, $link_text, $App->getUserToken());
			if (mobilAP_Error::isError($result)) {
				$App->addErrorMessage($result->getMessage());
			}
		} 

		$session->session_links = $session->getLinks();
		break;

	case 'question_results':
		$question_id = isset($_REQUEST['question_id']) ? $_REQUEST['question_id'] : '';
		$view = 'questions';
		$view_template = 'question_results';
		if ($question = $session->getQuestionById($question_id)) {
			break;
		} else {
		}
	case 'question':
		$view = 'questions';
		$view_template = 'question';
		
		$question_id = isset($_REQUEST['question_id']) ? $_REQUEST['question_id'] : '';
		if ($question = $session->getQuestionById($question_id)) {
			if (isset($_POST['submit_response'])) {
				$response = isset($_REQUEST['response']) ? $_REQUEST['response'] : array();
				$result = $question->submitAnswer($response, $App->getUserToken());
				if (mobilAP_Error::isError($result) && $result->getCode() != mobilAP_session::ERROR_USER_ALREADY_SUBMITTED) {
					$App->addErrorMessage($result->getMessage());
				} else {
					$view_template = 'question_results';
				}
			} elseif (isset($session->session_userdata['questions'][$question_id]) || isset($_POST['view_results'])) {
				$view_template = 'question_results';
			}
			break;
		} else {
		}
	case 'questions':
		$view = count($session->session_questions)>0 ? 'questions' : 'info';
		break;
	case 'discussion':
		if (isset($_REQUEST['add_discussion'])) {
			$post_text = isset($_POST['post_text']) ? $_POST['post_text'] : '';
			$result = $session->post_chat($post_text, $App->getUserToken());
			if (mobilAP_Error::isError($result)) {
				$App->addErrorMessage($result->getMessage());
			}
		}
		$session->session_chat = $session->get_chat();
		break;
	case 'evaluation':
		if (isset($_POST['submit_evaluation'])) {
			$responses = isset($_REQUEST['responses']) ? $_REQUEST['responses'] : array();
			$result = $session->addEvaluation($App->getUserToken(), $responses);
			if (mobilAP_Error::isError($result) && $result->getCode() != mobilAP_session::ERROR_USER_ALREADY_SUBMITTED) {
				$App->addErrorMessage($result->getMessage());
			} else {
				$view = 'evaluation_thanks';
			}
		} elseif ($session->session_userdata['evaluation']) {
			$view = 'evaluation_thanks';
		}
		
		break;
	default:
		$view = 'info';
		$session->session_presenters = $session->getPresentersDirectory();
		break;
}

$PAGE_TITLE = $session->session_title;
$PAGE = 'sessions';
$view_template = isset($view_template) ? $view_template : $view;

include('templates/header.tpl');
include('templates/session/session.tpl');
include('templates/footer.tpl');

?>