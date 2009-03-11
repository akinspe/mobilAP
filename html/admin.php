<?php

/*

* Copyright (c) 2008, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('inc/app_classes.php');

$PAGE_TITLE = 'Administration';
$PAGE = 'admin';
$App = new Application();

if (!$App->is_LoggedIn()) {

	include("templates/header.tpl");
	include("templates/nav.tpl");
	include("templates/not_logged_in.tpl");
	include("templates/footer.tpl");
	exit();
}

$user = $App->getUser();
$mobilAP_admin = $user->isAdmin();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'main';

if (isset($_POST['cancel_item'])) {
	$action='edit_schedule';
}

if (isset($_POST['cancel'])) {
	$action='main';
}

if (isset($_POST['cancel_session'])) {
	$action='edit_session';
}

if (isset($_POST['cancel_question'])) {
	$action='edit_question';
}

if (isset($_POST['cancel_announcement'])) {
	$action='announcements';
}

if (isset($_POST['cancel_session_group'])) {
	$action='session_groups';
}

if (isset($_POST['cancel_evaluation_question'])) {
	$action = "evaluation_questions";
}

switch ($action)
{
	case 'export_data':
		$sql = mobilAP::export_data_sql();
		header('Content-type: text/plain');
		print $sql;
		exit();

	case 'add_announcement':
		$announcement = new mobilAP_announcement();
		$template_file = 'announcement_add.tpl';
		if (isset($_POST['add_announcement'])) {
			$announcement_title = isset($_POST['announcement_title']) ? $_POST['announcement_title'] : '';
			$announcement_text = isset($_POST['announcement_text']) ? $_POST['announcement_text'] : '';

			$ok = true;

			if (!$announcement->setTitle($announcement_title)) {
				$App->addErrorMessage("Please include a title");
				$ok = false;

			}
			if (!$announcement->setText($announcement_text)) {
				$App->addErrorMessage("Please include some text");
				$ok = false;
			}

			if ($ok) {
				$announcement->postAnnouncement($App->getUserID());
			} else {
				break;
			}
		} else {
			break;
		}
		
	case 'edit_announcement':
		$announcement_id = isset($_REQUEST['announcement_id']) ? $_REQUEST['announcement_id'] : '';
		if ($announcement = mobilAP_announcement::getAnnouncementById($announcement_id)) {
			$template_file = 'announcement_edit.tpl';

			if (isset($_POST['update_announcement'])) {
				$announcement_title = isset($_POST['announcement_title']) ? $_POST['announcement_title'] : '';
				$announcement_text = isset($_POST['announcement_text']) ? $_POST['announcement_text'] : '';
	
				$ok = true;
	
				if (!$announcement->setTitle($announcement_title)) {
					$App->addErrorMessage("Please include a title");
					$ok = false;
	
				}
				if (!$announcement->setText($announcement_text)) {
					$App->addErrorMessage("Please include some text");
					$ok = false;
				}
	
				if ($ok) {
					$announcement->updateAnnouncement($App->getUserID());
				} else {
					break;
				}
			} elseif (isset($_POST['delete_announcement'])) {
				$announcement->deleteAnnouncement($App->getUserID());
				$action='announcements';
			} else {
				break;
			}
		} 

	case 'announcements':
		$announcements = mobilAP_announcement::getAnnouncements();
		$template_file = 'announcements_admin.tpl';
		
		
		break;

	case 'add_schedule_item':
	
		$schedule_item = new mobilAP_schedule_item();
		
		if (isset($_POST['add_item'])) {
			$date = isset($_POST['date'])  ? $_POST['date'] : array();
			$start_time = isset($_POST['start_time']) ? Utils::createTimeStampFromArray(array_merge($date, $_POST['start_time'])) : null;
			$end_time = isset($_POST['end_time']) ? Utils::createTimeStampFromArray(array_merge($date, $_POST['end_time'])) : null;
			$title = isset($_POST['title']) ? $_POST['title'] : '';
			$detail = isset($_POST['detail']) ? $_POST['detail'] : '';
			$room = isset($_POST['room']) ? $_POST['room'] : '';
			$session_id = isset($_POST['session_id']) ? $_POST['session_id'] : '';
			$session_group_id = isset($_POST['session_group_id']) ? $_POST['session_group_id'] : '';
			$schedule_item->setStartTime($start_time);
			$schedule_item->setEndTime($end_time);
			$schedule_item->setTitle($title);
			$schedule_item->setDetail($detail);
			$schedule_item->setRoom($room);
			$schedule_item->setSession($session_id);
			$schedule_item->setSessionGroup($session_group_id);
			$result = $schedule_item->createItem();
			if (mobilAP_Error::isError($result)) {
				$App->addErrorMessage("Error creating item: " . $result->getMessage());
			} else {
				$day_schedule = mobilAP::getScheduleForDate($schedule_item->date);	
				$action='edit_schedule';
				break;			
			}
		} else {
			$date = isset($_REQUEST['date'])  ? $_REQUEST['date'] : time();
			$schedule_item->setStartTime(utils::make_timestamp($date));
			$schedule_item->setEndTime(utils::make_timestamp($date));
		}
		
		
		$template_file = 'add_schedule_item.tpl';
		break;
		
	case 'edit_schedule_item':
		$schedule_id = isset($_REQUEST['schedule_id']) ? $_REQUEST['schedule_id'] : '';
		if (!$schedule_item = mobilAP_schedule_item::getScheduleItem($schedule_id)) {
			$App->addErrorMessage("Error finding schedule item $schedule_id");
			$action='main';
			break;
		}

		if (!$mobilAP_admin) {
			$action='main';
			break;
		}
				
		if (isset($_POST['update_item'])) {
			$date = isset($_POST['date'])  ? $_POST['date'] : array();
			$start_time = isset($_POST['start_time']) ? Utils::createTimeStampFromArray(array_merge($date, $_POST['start_time'])) : null;
			$end_time = isset($_POST['end_time']) ? Utils::createTimeStampFromArray(array_merge($date, $_POST['end_time'])) : null;
			$title = isset($_POST['title']) ? $_POST['title'] : '';
			$detail = isset($_POST['detail']) ? $_POST['detail'] : '';
			$room = isset($_POST['room']) ? $_POST['room'] : '';
			$session_id = isset($_POST['session_id']) ? $_POST['session_id'] : '';
			$session_group_id = isset($_POST['session_group_id']) ? $_POST['session_group_id'] : '';
						
			$schedule_item->setStartTime($start_time);
			$schedule_item->setEndTime($end_time);
			$schedule_item->setTitle($title);
			$schedule_item->setDetail($detail);
			$schedule_item->setRoom($room);
			$schedule_item->setSession($session_id);
			$schedule_item->setSessionGroup($session_group_id);
			$schedule_item->updateItem();
			$date = $schedule_item->date;
			$action='edit_schedule';
		} elseif (isset($_POST['delete_item'])) {
			$schedule_item->deleteItem();
			$date = $schedule_item->date;
			$action='edit_schedule';
		} else {
			$template_file = 'edit_schedule_item.tpl';
			break;
		}

	case 'edit_schedule':
		$date = isset($date) ? $date : (isset($_REQUEST['date']) ? $_REQUEST['date'] : '');
		if (!$mobilAP_admin || !$day_schedule = mobilAP::getScheduleForDate($date)) {
			$action='main';
			break;
		}

		$template_file = 'edit_schedule.tpl';
		break;

	case 'add_session_group':
		$template_file = 'add_session_group.tpl';
		if (isset($_POST['add_session_group'])) {
			$session_group = new mobilap_session_group();
			$session_group_title = isset($_POST['session_group_title']) ? $_POST['session_group_title'] : '';
			$session_group_detail = isset($_POST['session_group_detail']) ? $_POST['session_group_detail'] : '';
			$session_group->setTitle($session_group_title);
			$session_group->setDetail($session_group_detail);
			$result = $session_group->createGroup();
			if (mobilAP_Error::isError($result)) {
				$App->addErrorMessage("Error creating session group: " . $result->getMessage());
				break;
			} else {
				$App->addMessage("Session group created");
			}
			
		} else {
			break;
		}

	case 'edit_session_group':
		$session_group_id = isset($_REQUEST['session_group_id']) ? $_REQUEST['session_group_id'] : '';
		if ($session_group = mobilAP_session_group::getSessionGroupByID($session_group_id)) {
			if (isset($_POST['update_session_group'])) {
				$session_group_title = isset($_POST['session_group_title']) ? $_POST['session_group_title'] : '';
				$session_group_detail = isset($_POST['session_group_detail']) ? $_POST['session_group_detail'] : '';
				$session_group->setTitle($session_group_title);
				$session_group->setDetail($session_group_detail);
				$session_group->updateGroup();
			} elseif (isset($_POST['delete_session_group'])) {
				$session_group->deleteGroup();
				$App->addMessage("Session group deleted");
			} else {
				$template_file = 'edit_session_group.tpl';
				break;			
			}
		}

	case 'session_groups':
		$action='session_groups';
		$session_groups = mobilAP_session_group::getSessionGroups();
		$template_file = 'session_groups.tpl';
		break;
		
	case 'view_evaluations':
		$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
		if ($session = mobilAP_session::getSessionByID($session_id)) {
			if (!$session->isPresenter($App->getUserID()) && !$mobilAP_admin) {
				include("templates/header.tpl");
				include("templates/not_authorized.tpl");
				include("templates/footer.tpl");
				exit();
			}

			$template_file = 'view_evaluations.tpl';
			$evaluations = $session->getEvaluations();
			$evaluation_questions = mobilAP::getEvaluationQuestions();
			$eval_summary = $session->getEvaluationSummary();
		}
		
		break;
		
	case 'add_link':
		$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
		if ($session = mobilAP_session::getSessionByID($session_id)) {
			if (!$session->isPresenter($App->getUserID()) && !$mobilAP_admin) {
				include("templates/header.tpl");
				include("templates/not_authorized.tpl");
				include("templates/footer.tpl");
				exit();
			}
			
			$template_file = 'add_link.tpl';
			if (isset($_POST['add_link'])) {
				$link_url = isset($_POST['link_url']) ? $_POST['link_url'] : '';
				$link_text = isset($_POST['link_text']) ? $_POST['link_text'] : '';
				$result = $session->addLink($link_url, $link_text, $App->getUserID(), 'A');
				if (mobilAP_Error::isError($result)) {
					$App->addErrorMessage("Error adding link: " . $result->getMessage());
				} else {
					$action='edit_session';
				}
			}
		} else {
			$App->addErrorMessage("Invalid session");
			$action='main';
		}
		
		break;
		
	case 'edit_link':
		$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
		$link_id = isset($link_id) ? $link_id : (isset($_REQUEST['link_id']) ? $_REQUEST['link_id'] : '');
		if ($session = mobilAP_session::getSessionByID($session_id)) {
			if (!$session->isPresenter($App->getUserID()) && !$mobilAP_admin) {
				include("templates/header.tpl");
				include("templates/not_authorized.tpl");
				include("templates/footer.tpl");
				exit();
			}

			if ($link = $session->getLinkById($link_id)) {
				$template_file = 'edit_link.tpl';
				
				if (isset($_POST['update_link'])) {
					$link_url = isset($_POST['link_url']) ? $_POST['link_url'] : '';
					$link_text = isset($_POST['link_text']) ? $_POST['link_text'] : '';
					$link->setURL($link_url);
					$link->setText($link_text);
					$link->updateLink();
					$action='edit_session';
					break;
				}
				
				if (isset($_POST['delete_link'])) {
					$result = $link->deleteLink();
					if (mobilAP_Error::isError($result)) {
						$App->addErrorMessage('Error deleting link: ' . $result->getMessage());
					} else {
						$action='edit_session';
						break;
					}
				
				}
				
			} else {
				$App->addErrorMessage("Invalid link");
				$action='edit_session';
			}
		
		} else {
			$App->addErrorMessage("Invalid session");
			$action='main';
			break;
		}
		break;
		
	case 'edit_response':
		$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
		$question_id = isset($question_id) ? $question_id : (isset($_REQUEST['question_id']) ? $_REQUEST['question_id'] : '');
		
		if ($session = mobilAP_session::getSessionByID($session_id)) {
			if (!$session->isPresenter($App->getUserID()) && !$mobilAP_admin) {
				include("templates/header.tpl");
				include("templates/not_authorized.tpl");
				include("templates/footer.tpl");
				exit();
			}

			if ($question = $session->getQuestionById($question_id)) {
			
				$response_value = isset($_REQUEST['response_value']) ? $_REQUEST['response_value'] : '';
			
				if ($response = $question->getResponseByValue($response_value)) {
				
					if (isset($_POST['update_response'])) {
						$response_text = isset($_POST['response_text']) ? $_POST['response_text'] : '';
						if (!$response->setResponseText($response_text)) {
							$App->addErrorMessage("Invalid response value");
						} else {
							$result = $response->updateResponse();
							if (mobilAP_Error::isError($result)) {
								$App->addErrorMessage("Error updating response: " . $result->getMessage());
							} else {
								$App->addMessage("Response updated");
								$action='edit_question';
								break;
							}
						}
					}

					if (isset($_POST['remove_response'])) {
						$result = $question->removeResponse($response->response_value);
						if (mobilAP_Error::isError($result)) {
							$App->addErrorMessage('Error deleting response: ' . $result->getMessage());
						} else {
							$action='edit_question';
						}
					}
				
					
				} else {
					$App->addErrorMessage("Invalid response");
					$action='edit_question';
				}
			
			} else {
				$App->addErrorMessage("Invalid question");
				$action='edit_session';
			}
		
		} else {
			$App->addErrorMessage("Invalid session");
			$action='main';
		}
		break;
		
	case 'add_question':
		$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
		if ($session = mobilAP_session::getSessionByID($session_id)) {
			if (!$session->isPresenter($App->getUserID()) && !$mobilAP_admin) {
				include("templates/header.tpl");
				include("templates/not_authorized.tpl");
				include("templates/footer.tpl");
				exit();
			}

			$template_file = 'add_question.tpl';
			if (isset($_POST['add_question'])) {
				$question_text = isset($_POST['question_text']) ? $_POST['question_text'] : '';
				$question_list_text = isset($_POST['question_list_text']) ? $_POST['question_list_text'] : '';
				$question_minchoices = isset($_POST['question_minchoices']) ? $_POST['question_minchoices'] : 0;
				$question_maxchoices = isset($_POST['question_maxchoices']) ? $_POST['question_maxchoices'] : 0;
				$question_active = isset($_POST['question_active']) ? $_POST['question_active'] : DB_FALSE;
				$chart_type = isset($_POST['chart_type']) ? $_POST['chart_type'] : $question->chart_type;
				$result = $session->addQuestion($question_text);
				if (mobilAP_Error::isError($result)) {
					$App->addErrorMessage("Error adding question: " . $result->getMessage());
					break;
				} else {
					$action='edit_question';
					$question =& $result;
					$question_id = $question->question_id;
					$question->setQuestionListText($question_list_text);
					$question->setMinChoices($question_minchoices);
					$question->setMaxChoices($question_maxchoices);
					$question->setChartType($chart_type);
					$question->setQuestionActive($question_active);
					$question->updateQuestion();
				}
			} else {
				$question = new mobilAP_poll_question($session_id);
				break;
			}
		} else {
			$App->addErrorMessage("Invalid session");
			$action='main';
			break;
		}
		
	case 'edit_question':
		$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
		$question_id = isset($question_id) ? $question_id : (isset($_REQUEST['question_id']) ? $_REQUEST['question_id'] : '');
		if ($session = mobilAP_session::getSessionByID($session_id)) {
			if (!$session->isPresenter($App->getUserID()) && !$mobilAP_admin) {
				include("templates/header.tpl");
				include("templates/not_authorized.tpl");
				include("templates/footer.tpl");
				exit();
			}
			
			if ($question = $session->getQuestionById($question_id)) {
				$template_file = 'edit_question.tpl';

				if (isset($_POST['update_question'])) {
					$question_text = isset($_POST['question_text']) ? $_POST['question_text'] : '';
					$question_list_text = isset($_POST['question_list_text']) ? $_POST['question_list_text'] : '';
					$question_minchoices = isset($_POST['question_minchoices']) ? $_POST['question_minchoices'] : 0;
					$question_maxchoices = isset($_POST['question_maxchoices']) ? $_POST['question_maxchoices'] : 0;
					$question_active = isset($_POST['question_active']) ? $_POST['question_active'] : DB_FALSE;
					$chart_type = isset($_POST['chart_type']) ? $_POST['chart_type'] : $question->chart_type;
					
					$question->setQuestion($question_text);
					$question->setQuestionListText($question_list_text);
					$question->setMinChoices($question_minchoices);
					$question->setMaxChoices($question_maxchoices);
					$question->setChartType($chart_type);
					$question->setQuestionActive($question_active);
					$question->updateQuestion();
					$action='edit_session';
					break;
				}
				
				if (isset($_POST['clear_answers'])) {
					$result = $question->clearAnswers();
				}

				if (isset($_POST['add_response'])) {
				
					$add_response_text = isset($_POST['add_response_text']) ? $_POST['add_response_text'] : '';
					$result = $question->addResponse($add_response_text);
					if (mobilAP_Error::isError($result)) {
						$App->addErrorMessage('Error adding response: ' . $result->getMessage());
					}
				}               
	
				if (isset($_POST['remove_response'])) {
					$response_value = @key($_POST['remove_response']);
					$result = $question->removeResponse($response_value);
					if (mobilAP_Error::isError($result)) {
						$App->addErrorMessage('Error deleting response: ' . $result->getMessage());
					}
				}
				
				if (isset($_POST['delete_question'])) {
					$result = $question->deleteQuestion();
					if (mobilAP_Error::isError($result)) {
						$App->addErrorMessage('Error deleting question: ' . $result->getMessage());
					} else {
						$action='edit_session';
						break;
					}
				}


			} else {
				$App->addErrorMessage("Invalid question");
				$action='edit_session';
			}
		
		} else {
			$App->addErrorMessage("Invalid session");
			$action='main';
		}
		break;
		

	case 'delete_discussion':
		$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
		if ($session = mobilAP_session::getSessionByID($session_id)) {
			$post_id = isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : '';
			$session->delete_chat($post_id);
		} 

	case 'view_discussion':
		$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
		if ($session = mobilAP_session::getSessionByID($session_id)) {
			$template_file = 'view_discussion.tpl';
		} 
		break;

	case 'view_responses':
		$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
		$question_id = isset($question_id) ? $question_id : (isset($_REQUEST['question_id']) ? $_REQUEST['question_id'] : '');
		if ($session = mobilAP_session::getSessionByID($session_id)) {
			if (!$session->isPresenter($App->getUserID()) && !$mobilAP_admin) {
				include("templates/header.tpl");
				include("templates/not_authorized.tpl");
				include("templates/footer.tpl");
				exit();
			}
			
			if ($question = $session->getQuestionById($question_id)) {
				$template_file = 'view_question_responses.tpl';
			} else {
				$App->addErrorMessage("Invalid question");
				$action='edit_session';
			}
				
		}
		break;
		
	case 'add_session':
		$session = new mobilAP_session();
		$template_file = 'add_session.tpl';

		if (isset($_POST['add_session'])) {
			$session_id = isset($_POST['session_id']) ? $_POST['session_id'] : '';
			$session_title = isset($_POST['session_title'])? $_POST['session_title'] : '';
			$session_abstract = isset($_POST['session_abstract'])? $_POST['session_abstract'] : '';
			
			$result = mobilAP_session::createSession($session_id, $session_title);
			
			if (mobilAP_Error::isError($result)) {
				$App->addErrorMessage("Error creating session: " . $result->getMessage());
				break;
			} elseif (!$session = mobilAP_session::getSessionByID($session_id)) {
				$App->addErrorMessage("Error creating session");
				break;
			}
						
			$session->setSessionTitle($session_title);
			$session->setSessionAbstract($session_abstract);
			$session->updateSession();
			$action='edit_session';
		} else {
			break;
		}
		
	case 'edit_session':
		$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : '';
		if ($session = mobilAP_session::getSessionByID($session_id)) {
			if (!$session->isPresenter($App->getUserID()) && !$mobilAP_admin) {
				include("templates/header.tpl");
				include("templates/not_authorized.tpl");
				include("templates/footer.tpl");
				exit();
			}
						
			if ($mobilAP_admin) {
				if (isset($_POST['delete_session'])) {
					$result = $session->deleteSession();
					$action = 'main';
					break;
				}
			}
		
			if (isset($_POST['update_session'])) {
				$ok = true;
				$session_title = isset($_POST['session_title'])? $_POST['session_title'] : '';
				$session_abstract = isset($_POST['session_abstract'])? $_POST['session_abstract'] : '';
				$edit_session_id = isset($_POST['edit_session_id'])? $_POST['edit_session_id'] : $session_id;
								
				$session->setSessionTitle($session_title);
				$session->setSessionAbstract($session_abstract);
				
				$result = $session->setSessionID($edit_session_id);
				if (mobilAP_Error::isError($result)) {
					$ok = false;
					$App->addErrorMessage("Error changing session id: " . $result->getMessage());
				}
				
				if ($ok) {
					$session->updateSession();
					$App->addMessage("Session $session_id updated");
					$action='main';
					break;
				}
			}
			
			if (isset($_POST['clear_evaluations'])) {
				$result = $session->clearEvaluations();
			}

			if (isset($_POST['clear_discussion'])) {
				$result = $session->clearChat();
			}
			
			if (isset($_POST['add_presenter'])) {
				$add_presenter_id = isset($_POST['add_presenter_id']) ? $_POST['add_presenter_id'] : '';
				$result = $session->addPresenter($add_presenter_id);
				if (mobilAP_Error::isError($result)) {
					$App->addErrorMessage("Error adding presenter: " . $result->getMessage());
				} else {
					$App->addMessage("Presenter $add_presenter_id added to this session");
				}
			}

			if (isset($_POST['remove_presenter'])) {
				$presenter_index = key($_POST['remove_presenter']);
				$result = $session->removePresenter($presenter_index);
				if (mobilAP_Error::isError($result)) {
					$App->addErrorMessage("Error removing presenter: " . $result->getMessage());
				} else {
					$App->addMessage("Presenter removed from this session");
				}
			}
		
		}
		break;
	case 'edit_evaluation_response':
		die('edit_evaluation_response not written');
		break;
	case 'edit_evaluation_question':
		$question_index = isset($_REQUEST['question_index']) ? $_REQUEST['question_index'] : '';
		
		if ($question = mobilAP_evaluation_question::getQuestionByIndex($question_index)) {
			$template_file = 'evaluation_edit_question.tpl';
			if (isset($_POST['remove_response'])) {
				$response_index = key($_POST['remove_response']);
				$question->removeResponse($response_index);
			}

			if (isset($_POST['add_response'])) {
				$add_response_text = isset($_POST['add_response_text']) ? $_POST['add_response_text'] : '';
				$result = $question->addResponse($add_response_text);
				if (mobilAP_Error::isError($result)) {
					$App->addErrorMessage("Error adding response: " . $result->getMessage());
					break;
				} 
			}
			
			if (isset($_POST['delete_question'])) {
				$App->addMessage("Question deleted");
				$question->deleteQuestion();
				$action='evaluation_questions';
				$evaluation_questions = mobilAP::getEvaluationQuestions();
				$template_file = 'evaluation_questions.tpl';
			}
		} else {
			$evaluation_questions = mobilAP::getEvaluationQuestions();
			$template_file = 'evaluation_questions.tpl';
		}
	case 'add_evaluation_question':
		if ($action=='add_evaluation_question') {
			$template_file = 'evaluation_add_question.tpl';
			$question = new mobilAP_evaluation_question();
		}
		if (isset($_POST['add_question']) || isset($_POST['update_question'])) {
			$question_text = isset($_POST['question_text']) ? $_POST['question_text'] : '';
			$question_response_type = isset($_POST['question_response_type']) ? $_POST['question_response_type'] : $question->question_response_type;
			$ok = true;
			if (!$question->setQuestionText($question_text)) {
				$ok = false;
				$App->addErrorMessage("Invalid question text");
			}
			if (!$question->setQuestionResponseType($question_response_type)) {
				$ok = false;
				$App->addErrorMessage("Invalid question response type");
			}
			
			if ($ok) {
				if ($action=='add_evaluation_question')  {
					$result = $question->addQuestion();
					if (mobilAP_Error::isError($result)) {
						$App->addErrorMessage("Error adding question: " . $result->getMessage());
						break;
					} 
				} else {
					$result = $question->updateQuestion();
					if (mobilAP_Error::isError($result)) {
						$App->addErrorMessage("Error updating question: " . $result->getMessage());
						break;
					}					
				}
			} else {
				break;
			}
		} else {
			break;
		}
		
	case 'evaluation_questions':
		$evaluation_questions = mobilAP::getEvaluationQuestions();
		$template_file = 'evaluation_questions.tpl';
		break;

	case 'settings':
		if (isset($_POST['reset_settings'])) {
			mobilAP::resetConfigs();
		}
		
		if (isset($_POST['update_settings'])) {
			$setting = isset($_POST['setting']) ? $_POST['setting'] : array();
			foreach ($setting as $var=>$value) {
				mobilAP::setConfig($var, $value);
			}

			$App->addMessage("Settings updated");
		}
		
		$template_file = 'admin_settings.tpl';
		break;

	case 'main':
	default:
		$action='main';		
		break;
}

switch ($action)
{
	case 'main':

		$sessions = mobilAP_session::getSessionsForUser($App->getUserID());
		if ($mobilAP_admin) {
			$mobilAP_days = mobilAP::getDays();
			$mobilAP_schedule = mobilAP::getSchedule();
		}
		$template_file = 'mobilAP_admin.tpl';
		break;
		
	case 'edit_schedule':
		$session_groups = mobilAP_session_group::getSessionGroups();
		$template_file = 'edit_schedule.tpl';
		break;
		
	case 'edit_session':
		$PAGE_TITLE = "Editing Session $session->session_id";
		$template_file = 'edit_session.tpl';
		break;


	case 'edit_response':
		$PAGE_TITLE = "Editing Session $session->session_id";
		$template_file = 'edit_response.tpl';
		break;
		
	case 'edit_question':
		$template_file = 'edit_question.tpl';

	case 'add_question':
		$PAGE_TITLE = "Editing Session $session->session_id";
		$question_minchoices_options = utils::krange(0,10);
		$question_maxchoices_options = utils::krange(1,10);
		$chart_types = mobilAP_poll_question::getChartTypes();

		break;

	case 'add_link':
	case 'edit_link':
		$PAGE_TITLE = "Editing Session $session->session_id";
		break;
}

include('templates/header.tpl');
include("templates/nav.tpl");
include("templates/admin/$template_file");
include('templates/footer.tpl');

?>