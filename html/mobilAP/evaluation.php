<?php

require_once('../mobilAP.php');
require_once('classes/mobilAP_evaluation.php');

if (isset($_POST['post'])) {
    $post_action = $_POST['post'];
    $user = new mobilAP_user(true);

    switch ($post_action)
    {
            case 'deleteQuestion':
                $question_index = isset($_POST['question_index']) ? $_POST['question_index'] : '';
                if ($question = mobilAP_evaluation_question::getQuestionByIndex($question_index)) {
                    $data = $question->deleteQuestion($user->getUserID());
                } else {
                    $data = mobilAP_Error::throwError("Invalid question");
                }
                break;
            case 'updateQuestion':
            	$question_index = isset($_POST['question_index']) ? $_POST['question_index'] : '';
            	if (!$question = mobilAP_evaluation_question::getQuestionByIndex($question_index)) {
                    $data = mobilAP_Error::throwError("Invalid question");
                    break;
            	}
            case 'addQuestion':
            	if ($post_action=='addQuestion') {
            		$question = new mobilAP_evaluation_question();
            	}

				$question_text = isset($_POST['question_text']) ? $_POST['question_text'] : $question->question_text;
				$response_type = isset($_POST['response_type']) ? $_POST['response_type'] : $question->response_type;
				
				$question->setQuestionText($question_text);
				$question->setQuestionResponseType($response_type);
				
				if ($post_action =='updateQuestion') {
					$data = $question->updateQuestion($user->getUserID());
				} else {
					$data = $question->addQuestion($user->getUserID());
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
                                $question->deleteQuestion();
                            }
                            $data = $result;
                            break;
                        }
                    }
                }
            	
            	break;
        default:
            $data = mobilAP_Error::throwError("Invalid request",-1, $_POST['post']);
            break;
    }
} else {
    $data = mobilAP::getEvaluationQuestions();
}

header('Content-type: application/json');
echo json_encode($data);

?>