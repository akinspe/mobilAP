<?php

require_once('../mobilAP.php');

$user_session = new mobilAP_UserSession();
$user = new mobilAP_user(true);
$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
if ($session = mobilAP::getSessionByID($session_id)) {

    if (isset($_POST['post'])) {
    	$post_action = $_POST['post'];
        switch ($post_action)
        {
            case 'deleteLink':
				$link_id = isset($_POST['link_id']) ? $_POST['link_id'] : '';
                if ($link = $session->getLinkById($link_id)) {
                    $data = $link->deleteLink($user->getUserID());
                } else {
                    $data = mobilAP_Error::throwError("Invalid link");
                }
                break;
            case 'addLink':
				$link_url = isset($_POST['link_url']) ? $_POST['link_url'] : '';
				$link_title = isset($_POST['link_title']) ? $_POST['link_title'] : '';
				$data = $session->addLink($link_url, $link_title, $user->getUserID());
                break;        

            case 'clearDiscussion':
				$data = $session->clearDiscussion($user->getUserID());
                break;        
            case 'evaluation':
                $responses = isset($_POST['responses']) ? $_POST['responses'] : array();
                $data = $session->addEvaluation($user->getUserID(), $responses);
                break;
			                
            case 'discussion':
				$post_text = isset($_POST['post_text']) ? $_POST['post_text'] : '';
				$data = $session->post_discussion($post_text, $user->getUserID());
                break;        
                
            case 'clearAnswers':
                $question_id = isset($_POST['question_id']) ? $_POST['question_id'] : '';
                if ($question = $session->getQuestionById($question_id)) {
                    $data = $question->clearAnswers($user->getUserID());
                } else {
                    $data = mobilAP_Error::throwError("Invalid question");
                }
                break;
            case 'question':
                $question_id = isset($_POST['question_id']) ? $_POST['question_id'] : '';
                if ($question = $session->getQuestionById($question_id)) {
                    $response = isset($_POST['response']) ? $_POST['response'] : array();
                    $data = $question->submitAnswer($response, $user->getUserID());
                } else {
                    $data = mobilAP_Error::throwError("Invalid question");
                }
                break;
            case 'deleteQuestion':
                $question_id = isset($_POST['question_id']) ? $_POST['question_id'] : '';
                if ($question = $session->getQuestionById($question_id)) {
                    $data = $question->deleteQuestion($user->getUserID());
                } else {
                    $data = mobilAP_Error::throwError("Invalid question");
                }
                break;
            case 'addPresenter':
            	$userID = isset($_POST['userID']) ? $_POST['userID'] : '';
                $data = $session->addPresenter($userID, $user->getUserID());
                break;
            case 'removePresenter':
            	$userID = isset($_POST['userID']) ? $_POST['userID'] : '';
                $data = $session->removePresenter($userID, $user->getUserID());
                break;
            case 'updateQuestion':
            	$question_id = isset($_POST['question_id']) ? $_POST['question_id'] : '';
            	if (!$question = $session->getQuestionById($question_id)) {
                    $data = mobilAP_Error::throwError("Invalid question");
                    break;
            	}
            case 'addQuestion':
            	if ($post_action=='addQuestion') {
            		$question = new mobilAP_session_question($session->session_id);
            	}

				$question_text = isset($_POST['question_text']) ? $_POST['question_text'] : $question->question_text;
				$question_list_text = isset($_POST['question_list_text']) ? $_POST['question_list_text'] : $question->question_list_text;
				$question_minchoices = isset($_POST['question_minchoices']) ? $_POST['question_minchoices'] : $question->question_minchoices;
				$question_maxchoices = isset($_POST['question_maxchoices']) ? $_POST['question_maxchoices'] : $question->question_maxchoices;
				$question_active = isset($_POST['question_active']) ? $_POST['question_active'] : $question->question_active;
				$chart_type = isset($_POST['chart_type']) ? $_POST['chart_type'] : $question->chart_type;
				
				$question->setQuestion($question_text);
				$question->setQuestionListText($question_list_text);
				$question->setMinChoices($question_minchoices);
				$question->setMaxChoices($question_maxchoices);
				$question->setChartType($chart_type);
				$question->setQuestionActive($question_active);
				
				if ($post_action =='updateQuestion') {
					$data = $question->updateQuestion($user->getUserID());
				} else {
					$data = $session->addQuestion($question, $user->getUserID());
                }
                
                if (!mobilAP_Error::isError($data)) {
                    $deletedResponses = isset($_POST['deletedResponses']) ? $_POST['deletedResponses'] : array();
                    $addedResponses = isset($_POST['addedResponses']) ? $_POST['addedResponses'] : array();
                    foreach ($deletedResponses as $response_value) {
                        $result = $question->deleteResponse($response_value);
                        if (mobilAP_Error::isError($result)) {
                            $data = $result;
                            break;
                        }
                    }

                    foreach ($addedResponses as $response_text) {
                        $result = $question->addResponse($response_text);
                        if (mobilAP_Error::isError($result)) {
                            if ($post_action=='addQuestion') {
                                $question->deleteQuestion($user->getUserID());
                            }
                            $data = $result;
                            break;
                        }
                    }
                }
            	
            	break;
            case 'deleteSession':
            	$data = $session->deleteSession($user->getUserID());
				break;
            case 'updateSession':
                $session_title = isset($_POST['session_title']) ? $_POST['session_title'] : $session->session_title;
                $session_description = isset($_POST['session_description']) ? $_POST['session_description'] : $session->session_description;
                $session_flags = isset($_POST['session_flags']) ? $_POST['session_flags'] : $session->session_flags;
                $session->setSessionTitle($session_title);
                $session->setSessionDescription($session_description);
                $session->setSessionFlags($session_flags);
                $data = $session->updateSession($user->getUserID());
                $data = $_POST;
                break;
            default:
                $data = mobilAP_Error::throwError("Invalid request",-1, $_POST['post']);
                break;
        }
        
    } else {

		$session->serial = mobilAP::getSerialValue('session_' . $session->session_id);
        $session->session_links = $session->getLinks();
        $session->session_questions = $session->getQuestions();				
        $session->session_presenters = $session->getPresenters();
        $session->session_discussion = $session->get_discussion();
        $data =& $session;
    }
} else {
    $data = new mobilAP_session();
    if (isset($_POST['post'])) {
        $session = $data;
        switch ($_POST['post'])
        {
            case 'add':
                $session_title = isset($_POST['session_title']) ? $_POST['session_title'] : '';
                $session_description = isset($_POST['session_description']) ? $_POST['session_description'] : '';
                $session_flags = isset($_POST['session_flags']) ? $_POST['session_flags'] : 0;
                $session->setSessionTitle($session_title);
                $session->setSessionDescription($session_description);
                $session->setSessionFlags($session_flags);
                $data = $session->createSession($user->getUserID());
                $data = $_POST;
                break;
        }
    }
}

header("Content-type: application/json; charset=" . MOBILAP_CHARSET);
echo json_encode($data);			

?>