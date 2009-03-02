<?php

require_once('inc/app_classes.php');

$App = new Application();
$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
if (!$session = mobilAP_session::getSessionByID($session_id)) {
	include('sessions.php');
	exit();
}

$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : '';
$session->session_questions = $session->getQuestions();
$session->session_userdata = $session->getUserSubmissions($App->getUserToken());


switch ($view)
{
	case 'info':
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
		if ($question = $session->getQuestionById($question_id)) {
			break;
		} else {
		}
	case 'question':
		$question_id = isset($_REQUEST['question_id']) ? $_REQUEST['question_id'] : '';
		if ($question = $session->getQuestionById($question_id)) {
			if (isset($_POST['submit_response'])) {
				$response = isset($_REQUEST['response']) ? $_REQUEST['response'] : array();
				$result = $question->submitAnswer($response, $App->getUserToken());
				if (mobilAP_Error::isError($result) && $result->getCode() != mobilAP_session::ERROR_USER_ALREADY_SUBMITTED) {
					$App->addErrorMessage($result->getMessage());
				} else {
					$view = 'question_results';
				}
			} elseif (isset($session->session_userdata['questions'][$question_id])) {
				$view = 'question_results';
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
		break;
}

$PAGE_TITLE = $session->session_title;
$PAGE = 'sessions';
$view_template = isset($view_template) ? $view_template : $view;

include('templates/header.tpl');
include('templates/nav.tpl');

?>
<h2><?= $session->session_id . ' ' . $session->session_title ?></h2>
<ul id="session_views">
<?php foreach (array('info'=>'Info', 'links'=>'Links', 'questions'=>'Questions', 'discussion'=>'Discussion') as $_view=>$_view_title) { ?>
	<?php if ($view==$_view) { ?>
	<li class="active"><?= $_view_title ?>
		<?php } elseif ($_view != 'questions' || count($session->session_questions)>0) { ?>
	<li><a href="session.php?session_id=<?= $session->session_id ?>&view=<?= $_view ?>"><?= $_view_title ?></a>
		<?php } else { ?>
	<li class="disabled"><?= $_view_title ?>
		<?php } ?>
	</li>
<?php } ?>	
</ul>
<div class="clearbox"></div>
<?= $App->getMessages() ?>

<?php

include("templates/session/{$view_template}.tpl");
include('templates/footer.tpl');

?>