<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('mobilAP_user.php');

class mobilAP_session
{
	const ERROR_NO_USER=-1;
	const ERROR_USER_ALREADY_SUBMITTED=-2;

	const POLL_QUESTIONS_TABLE='poll_questions';
	const POLL_RESPONSES_TABLE='poll_responses';
	const POLL_ANSWERS_TABLE='poll_answers';
	const SESSION_TABLE='sessions';
	const SESSION_LINK_TABLE='session_links';
	const SESSION_PRESENTER_TABLE='session_presenters';
	const SESSION_DISCUSSION_TABLE='session_discussion';
	const SESSION_EVALUATION_TABLE='session_evaluations';
	const SESSION_EVALUATION_ANSWERS_TABLE='session_evaluation_answers';
	const SESSION_FLAGS_DEFAULT=15;
	const SESSION_FLAGS_LINKS=1;
	const SESSION_FLAGS_USER_LINKS=2;
	const SESSION_FLAGS_DISCUSSION=4;
	const SESSION_FLAGS_EVALUATION=8;
    const SESSION_SINGLE_ID=1;
	public $session_id;
    public $session_title;
    public $session_description;
	public $session_flags=mobilAP_session::SESSION_FLAGS_DEFAULT;
    public $session_flags_links = 0;
    public $session_flags_user_links = 0;
    public $session_flags_discussion = 0;
    public $session_questions = array();
    public $session_links = array();
    public $session_presenters = array();
    public $session_discussion = array();
    public $session_flags_evaluation = 0;

	public function updateSerial()
	{
		mobilAP::setSerialValue(sprintf('session_%s',$this->session_id));
	}
    	
    /**
     * sets the session flags, updating the convinence variables
     * @param int $session_flags bitfield integer of the new flags
     */
	public function setSessionFlags($session_flags)
	{
		$this->session_flags = intval($session_flags);
		$this->session_flags_links = $this->session_flags & mobilAP_Session::SESSION_FLAGS_LINKS;
		$this->session_flags_user_links = $this->session_flags & mobilAP_Session::SESSION_FLAGS_USER_LINKS;
		$this->session_flags_discussion = $this->session_flags & mobilAP_Session::SESSION_FLAGS_DISCUSSION;
		$this->session_flags_evaluation = $this->session_flags & mobilAP_Session::SESSION_FLAGS_EVALUATION;
		return true;
	}
	
    /**
     * sets the variables from an array (probably from the database
     * @param array $arr an associative array of key/values 
     * @return object a session object
     */
	private function loadSessionFromArray($arr)
	{
		$session = new mobilAP_session();
		$session->session_id = intval($arr['session_id']);
		$session->session_title = $arr['session_title'];
		$session->session_description = $arr['session_description'];
		$session->setSessionFlags($arr['session_flags']);
		return $session;
	}
	
    /**
     * deletes the session
     * @param $userID, the user who is deleting the session
     * @return true or an error object
     */
	public function deleteSession($userID)
	{
		require_once('mobilAP_schedule.php');
		// check privilages
		if (!$user = mobilAP_user::getUserById($userID)) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		} elseif (!$user->isSiteAdmin()) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		}
		
		// remove questions
		$questions = $this->getQuestions();
		foreach ($questions as $question) {
			$question->deleteQuestion($userID);
		}
		
		// clear out relevant tables
		$tables = array(
			mobilAP_session::SESSION_LINK_TABLE, 
			mobilAP_session::SESSION_PRESENTER_TABLE, 
			mobilAP_session::SESSION_EVALUATION_TABLE, 
			mobilAP_session::SESSION_DISCUSSION_TABLE,
			mobilAP_schedule::SCHEDULE_TABLE,
			mobilAP_session::SESSION_TABLE
		);
		$params = array($this->session_id);
		foreach ($tables as $table) {
			$sql = sprintf("DELETE FROM %s WHERE session_id=?", $table);
			$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
		}

		mobilAP::purgeSerialValue('session_' . $this->session_id);
		mobilAP::setSerialValue('sessions');
		mobilAP::setSerialValue('schedule');
		return true;
	}

    /**
     * creates a new session based on the object contents
     * @param $userID the user creating the session
     * @return true or an error object
     */
	public function createSession($userID)
	{
		// check privilages
		if (!$user = mobilAP_user::getUserById($userID)) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		} elseif (!$user->isSiteAdmin()) {
			return mobilAP_Error::throwError("Unauthoirzed", mobilAP_UserSession::USER_UNAUTHORIZED);
		}
		
		//check required fields
		if (empty($this->session_title)) {
			return mobilAP_Error::throwError("Session title cannot be blank");
		} 
		
		$sql = sprintf("INSERT INTO %s (session_title, session_description, session_flags) 
        VALUES (?,?,?)", mobilAP_session::SESSION_TABLE);
        
        $params = array(
        	$this->session_title, $this->session_description, $this->session_flags);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		$this->session_id = $result->get_last_insert_id();
		mobilAP::setSerialValue('sessions');
		$this->updateSerial();
		return true;
	}

    /**
     * instantiates a session object 
     * @param $session_id the session_id of the session to load
     * @return a session object or false if it could not be found
     */
	public static function getSessionByID($session_id)
	{
		$sql = sprintf("SELECT * FROM %s WHERE session_id=?",
				mobilAP_session::SESSION_TABLE);
		$params = array($session_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return false;
		}
		$session = false;
		
		if ($row = $result->fetchRow()) {
			$session = mobilAP_session::loadSessionFromArray($row);
		}
		return $session;
	}
	
    /**
     * returns a list of sessions
     * @return array of session objects
     */
	public static function getSessions()
	{
		$sql = sprintf("SELECT * FROM %s ORDER BY session_id", mobilAP_session::SESSION_TABLE);
		$result = mobilAP::query($sql);
		$sessions = array();
		if (mobilAP_Error::isError($result)) {
			return $sessions;
		}
		while ($row = $result->fetchRow()) {
			$session = mobilAP_session::loadSessionFromArray($row);
			/*
			$session->session_links = $session->getLinks();
			$session->session_questions = $session->getQuestions();				
			$session->session_presenters = $session->getPresenters();
			$session->session_discussion = $session->get_discussion();
			$session->session_evaluations = $session->getEvaluations();
			*/
			$sessions[] = $session;
		}
		
		return $sessions;
	}
	
    /**
     * returns a list of sessions that a user can administer
     * @param $userID the userID of the user 
     * @return array of session objects
     */
	static function getSessionsForUser($userID)
	{
		$sessions = array();
		if ($user = mobilAP_user::getUserByID($userID)) {
			/* if the user is a site admin return ALL sessions */
			if ($user->isSiteAdmin()) {
				return mobilAP_session::getSessions();
			}
			
			$sql = sprintf("SELECT * FROM %s 	
					WHERE session_id IN (SELECT session_id FROM %s WHERE presenter_id=?)
					ORDER BY session_id",
					mobilAP_session::SESSION_TABLE,
					mobilAP_session::SESSION_PRESENTER_TABLE);
			$params = array($user->getUserID());		
			$result = mobilAP::query($sql,$params);
			$sessions = array();
			if (mobilAP_Error::isError($result)) {
				return $sessions;
			}
			
			while ($row = $result->fetchRow()) {
				$session = mobilAP_session::loadSessionFromArray($row);
				$sessions[] = $session;
			}
		}
		
		return $sessions;
	}

    /**
     * returns an array of evaluation summary data
     * @return structured array of data based on evaluation responses
     * @TODO document the structure
     */
	public function getEvaluationSummary()
	{
		$evaluation_questions = mobilAP::getEvaluationQuestions();
		$avg_fields = array();
		$text_fields = array();
		$count_fields = array();
		$data = array();
		foreach ($evaluation_questions as $question) {
			if ($question->question_response_type == mobilAP_evaluation_question::RESPONSE_TYPE_CHOICES) {
				$data['q' . $question->question_index] = array('avg'=>0, 'count'=>array());
				foreach ($question->responses as $response) {
					$data['q' . $question->question_index]['count'][$response['response_value']] = 0;
				}
				
				$avg_fields[] = sprintf("avg(q%d) q%d", $question->question_index, $question->question_index);
				$count_fields[] = sprintf("q%d", $question->question_index);
			} else {
				$data['q' . $question->question_index] = array();
				$text_fields[] = sprintf("q%d", $question->question_index);
			}
		}
		
		if (count($avg_fields)) {
	
			$sql = sprintf("SELECT %s FROM %s
					WHERE session_id=?",
					implode(',', $avg_fields), mobilAP_session::SESSION_EVALUATION_TABLE);
			$params = array($this->session_id);
			$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return $data;
			}
			if ($row = $result->fetchRow()) {
				foreach ($row as $idx=>$avg) {
					if ($avg) {
						$data[$idx]['avg'] = $avg;
					}
				}
			}
		}
		
		if (count($count_fields)) {

			$sql = sprintf("SELECT %s FROM %s
					WHERE session_id=?",
					implode(',', $count_fields), mobilAP_session::SESSION_EVALUATION_TABLE);
	
			$params = array($this->session_id);
			$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return $data;
			}
			while ($row = $result->fetchRow()) {
				foreach ($row as $idx=>$value) {
					$data[$idx]['count'][$value]++;
				}
			}
		}
		
		if (count($text_fields)) {

			$sql = sprintf("SELECT %s FROM %s WHERE session_id=?",
				implode(',', $text_fields),mobilAP_session::SESSION_EVALUATION_TABLE);
	
			$params = array($this->session_id);
			$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return $data;
			}
			while ($row = $result->fetchRow()) {
				foreach ($row as $idx=>$text) {
					if ($text) {
						$data[$idx][] = $text;
					}
				}
			}
		}

		return $data;		
	}
	
    /**
     * returns a list of completed evaluations for this session
     * @return array of evaluation data arrays
     */
	public function getEvaluations()
	{
		$sql = sprintf("SELECT * FROM %s WHERE session_id=?", 
				mobilAP_session::SESSION_EVALUATION_TABLE);
			$params = array($this->session_id);
		$result = mobilAP::query($sql,$params);
		$evaluations = array();
		if (mobilAP_Error::isError($result)) {
			return $evaluations;
		}
		while ($row = $result->fetchRow()) {
			$evaluations[] = $row;
		}
		
		return $evaluations;
	}
	
    /**
     * returns a list of links for this session
     * @return array of link objects
     */
	public function getLinks()
	{
		$sql = sprintf("SELECT * FROM %s
				WHERE session_id=?
				ORDER BY post_timestamp DESC",
				mobilAP_session::SESSION_LINK_TABLE);
		$params = array($this->session_id);
		$result = mobilAP::query($sql,$params);
		$links = array();
		if (mobilAP_Error::isError($result)) {
			return $links;
		}

		while ($row = $result->fetchRow()) {
			$links[] = mobilAP_session_link::loadLinkFromArray($row);
		}
		
		return $links;				
	}

    /**
     * returns a list of questions for this session
     * @param boolean $show_all, if false inactive questions will not be returned
     * @return array of question objects
     */
	public function getQuestions($show_all=false)
	{
		$where = array(
			"session_id=?"
		);
		
		if (!$show_all) {
			$where[] = 'question_active';
		}
		
		$sql = sprintf("SELECT * FROM %s
				WHERE %s
				ORDER BY question_index",
				mobilAP_session::POLL_QUESTIONS_TABLE, implode(" AND ", $where)); 
		$params = array($this->session_id);
		$result = mobilAP::query($sql,$params);
		$questions = array();
		if (mobilAP_Error::isError($result)) {
			return $questions;
		}
		
		while ($row = $result->fetchRow()) {
			$questions[] = mobilAP_session_question::loadQuestionFromArray($row);
		}
		
		return $questions;				
	}

    /**
     * returns the next available question index for this session
     * @return int
     */
	private function getNextQuestionIndex()
	{
		$sql = sprintf("SELECT count(*) FROM %s WHERE session_id=?", 
						mobilAP_session::POLL_QUESTIONS_TABLE);
		$params = array($this->session_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		return $result->fetchColumn();
	}

    /**
     * retrieves a question by its question id. Ensures that the question is associted with this session
     * @param int $question_id, the question id to return
     * @return question object or false if not found
     */
	public function getQuestionById($question_id)
	{
		$sql = sprintf("SELECT * FROM %s WHERE
				question_id=? AND session_id=?", 
				mobilAP_session::POLL_QUESTIONS_TABLE);
		$params = array($question_id, $this->session_id);
		$result = mobilAP::query($sql,$params);
		$question = false;
		if (mobilAP_Error::isError($result)) {
			return $question;
		}
		if ($row = $result->fetchRow()) {
			$question = mobilAP_session_question::loadQuestionFromArray($row);
		}
		return $question;		
	}

    /**
     * retrieves a link by its id. Ensures that the link is associted with this session
     * @param int $link_id, the link id to return
     * @return link object or false if not found
     */
	public function getLinkById($link_id)
	{
		$sql = sprintf("SELECT * FROM %s WHERE
				link_id=? AND session_id=?",
				mobilAP_session::SESSION_LINK_TABLE);
		$params = array($link_id, $this->session_id);
		$result = mobilAP::query($sql,$params);
		$link = false;
		if (mobilAP_Error::isError($result)) {
			return $link;
		}
		if ($row = $result->fetchRow()) {
			$link = mobilAP_session_link::loadLinkFromArray($row);
		}
		return $link;
	}
	
    /**
     * returns whether a user can administer this session
     * @param string $userID the user to check
     * @return boolean true if the user can administer this session
     */
    public function isAdmin($userID)
    {
        if (!$this->isPresenter($userID)) {
            if ($user = mobilAP_user::getUserByID($userID)) {
                return $user->isSiteAdmin();
            } else {
                return false;
            }
        } else {
            return true;
        }
        
    }
    
    /**
     * returns whether a user is a presenter for this session
     * @param string $userID the user to check
     * @return boolean true if the user is a presenter
     */
	public function isPresenter($userID)
	{
		$user = mobilAP_User::getUserById($userID);
		
		if ($user) {
			$sql = sprintf("SELECT presenter_id FROM %s WHERE
					session_id=?
					AND presenter_id=?", 
					mobilAP_session::SESSION_PRESENTER_TABLE);
            $params = array($this->session_id,$user->getUserID());
					
			$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return false;
			}
			return ($result->fetchColumn()) ? true : false;
		}
		return false;
	}

    /**
     * returns a list of presenters for this session
     * @return an array of user objects
     */
	public function getPresenters()
	{
		$sql = sprintf("SELECT presenter_id FROM %s 
				WHERE session_id=? ORDER BY presenter_index",
				mobilAP_session::SESSION_PRESENTER_TABLE);
		$params = array($this->session_id);
		$result = mobilAP::query($sql,$params);
		$presenters = array();
		if (mobilAP_Error::isError($result)) {
			return $presenters;
		}
		
		while ($row = $result->fetchRow()) {
			$presenters[] = mobilAP_user::getUserById($row['presenter_id']);
		}
		
		return $presenters;
	}

    /**
     * returns the next index to use when adding a presenter
     * @return int
     */
	private function getNextPresenterIndex()
	{
		$sql = sprintf("SELECT count(*) FROM %s
				WHERE session_id=?", mobilAP_session::SESSION_PRESENTER_TABLE);
		$params = array($this->session_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		
		return $result->fetchColumn();
	}
	
    /**
     * adds a presenter to this session
     * @param $presenter_userID the userID to add
     * @return true if successful or error object
     */
	public function addPresenter($presenter_userID, $admin_userID)
	{
		// check privilages
		if (!$this->isAdmin($admin_userID)) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		}
		
		if ($user = mobilAP_user::getUserByID($presenter_userID)) {
            if ($this->isPresenter($user->getUserID())) {
                return true;
            }
        
			//index field is user to keep them in order
			$index = $this->getNextPresenterIndex();
			$sql = sprintf("INSERT INTO %s (session_id, presenter_index, presenter_id)
			VALUES (?,?,?)",
			mobilAP_session::SESSION_PRESENTER_TABLE);
			$params = array($this->session_id, $index, $user->getUserID());
			$result = mobilAP::query($sql,$params);
			//be nice if the presenter is already there
			if (mobilAP_Error::isError($result)) {
				return $result;
			}

			mobilAP::setSerialValue($user->getUserID(),mobilAP::SERIAL_TYPE_USER);
			$this->updateSerial();
			return true;
		} else {
			return mobilAP_Error::throwError("User $presenter_userID not found");
		}
	}
	
    /**
     * removed a presenter from this session
     * @param $presenter_userID the userID to remove
     * @return true if successful or error object
     */
	public function removePresenter($presenter_userID, $admin_userID)
	{
		// check privilages
		if (!$this->isAdmin($admin_userID)) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		}

		$sql = sprintf("SELECT presenter_index FROM %s WHERE presenter_id=? AND session_id=?", 
						mobilAP_session::SESSION_PRESENTER_TABLE);
		$params = array($presenter_userID, $this->session_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}

		if ($row = $result->fetchRow()) {
			$index = intval($row['presenter_index']);
		} else {
			return mobilAP_Error::throwError("User $presenter_userID is not a presenter for this session");
		}
		
		$sql = sprintf("DELETE FROM %s WHERE session_id=? AND presenter_index=?",
				mobilAP_session::SESSION_PRESENTER_TABLE);
		$params = array($this->session_id, $index);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		
		mobilAP::setSerialValue($presenter_userID,mobilAP::SERIAL_TYPE_USER);

		$sql = sprintf("UPDATE %s 
						SET presenter_index=presenter_index-1 
			    		WHERE session_id=? AND presenter_index>?",
					mobilAP_session::SESSION_PRESENTER_TABLE);
		$params = array($this->session_id, $index);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}

		$this->updateSerial();
		return true;
	}
	
    /**
     * adds a question to this session
     * @param $question a question object
     * @return true if successful or error object
     */
	public function addQuestion($question, $admin_userID)
	{
		// check privilages
		if (!$this->isAdmin($admin_userID)) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		}

		if (!is_a($question, 'mobilAP_session_question')) {
			return mobilAP_Error::throwError("Invalid question object");
		}
		
		if (empty($question->question_text)) {
			return mobilAP_Error::throwError("Question cannot be empty");
		}

		if ($question->question_minchoices > $question->question_maxchoices) {
			return mobilAP_Error::throwError("Minimum choices should not be greater than maximum choices");
		}
		
		$question->session_id = $this->session_id;
		$question->question_index = $this->getNextQuestionIndex();
			
		$sql = sprintf("INSERT INTO %s 
		(session_id, question_index, question_active, question_text, question_minchoices, question_maxchoices, chart_type)
		VALUES
		(?,?,?,?,?,?,?)", mobilAP_session::POLL_QUESTIONS_TABLE);
		$params = array(
		$question->session_id,
		$question->question_index,
		$question->question_active,
		$question->question_text,
        $question->question_minchoices,
        $question->question_maxchoices,
        $question->chart_type);
		
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		$question->question_id = $result->get_last_insert_id();
		$this->updateSerial();
		return true;
	}
	
    /**
     * adds a link to this session
     * @param string $link_url 
     * @param string $link_text 
     * @param string $post_userID userID of user posting the link
     * @return true if successful or error object
     */
	public function addLink($link_url, $link_text, $post_userID)
	{
		if (!MobilAP_Utils::is_validURL($link_url)) {
			return mobilAP_Error::throwError("Invalid url");
		} elseif (empty($link_text)) {
			return mobilAP_Error::throwError("Link text cannot be blank");
		} elseif (!$user = mobilAP_User::getUserById($post_userID)) {
			return mobilAP_Error::throwError("You must login to post a link", mobilAP_session::ERROR_NO_USER);
		} elseif (!($this->session_flags & mobilAP_session::SESSION_FLAGS_USER_LINKS) && !$this->isPresenter($post_user)) {
			return mobilAP_Error::throwError("Posting of links to this session has been disabled");
		}

		$ts = time();
		$link_type = $this->isPresenter($user->getUserID()) ? 'A' : 'U';
		
		$sql = sprintf("INSERT INTO %s
				(session_id, link_url, link_text, link_type, post_user, post_timestamp)
				VALUES
				(?,?,?,?,?,?)",
				mobilAP_session::SESSION_LINK_TABLE);
        $params=array($this->session_id, $link_url, $link_text, $link_type, $user->getUserID(), $ts);
				
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		$link_id = $result->get_last_insert_id();
		$this->updateSerial();
		return true;
	}
	
    /**
     * submits an evaluation for this session
     * @param string $post_userID the user who completed the evaluation
     * @param array $respones a data structure with their responses
     * @return true if successful or error object
     */
	public function addEvaluation($post_userID, $responses)
	{
		if (empty($post_userID)) {
			return mobilAP_Error::throwError("You must login to post an evaluation", mobilAP_session::ERROR_NO_USER);
		}

		if (!$user = mobilAP_user::getUserByID($post_userID)) {
			return mobilAP_Error::throwError("User not found");
		}

		if (!is_array($responses)) {
			return mobilAP_Error::throwError("Invalid responses");
		}
		
		$evaluation_questions = mobilAP::getEvaluationQuestions();
		
		for($i=0; $i<count($evaluation_questions); $i++) {
			if (!isset($responses[$i])) {
				$responses[$i] = null;
			}
		}
		
		ksort($responses);
		
		$ts = time();
		
		$sql = sprintf("SELECT evaluation_id FROM %s 
				WHERE session_id=? AND post_user=?",
				mobilAP_session::SESSION_EVALUATION_TABLE);
		$params = array($this->session_id, $user->getUserID());
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}

		if ($result->fetchRow()) {
			return mobilAP_Error::throwError("You have already evaluated this session", mobilAP_session::ERROR_USER_ALREADY_SUBMITTED);
		}
		
		$sql = sprintf("INSERT INTO %s
				(session_id, post_user, post_timestamp)
				VALUES
				(?, ?, ?)",
				mobilAP_session::SESSION_EVALUATION_TABLE);
                
		$result = mobilAP::query($sql, array($this->session_id, $user->getUserID(), time()));
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
        
        $evaluation_id = $result->get_last_insert_id();
        
        foreach ($responses as $index=>$value) {
            $sql = sprintf("INSERT INTO %s
                    (evaluation_id, question_index, question_answer)
                    VALUES
                    (?, ?, ?)",
                    mobilAP_session::SESSION_EVALUATION_ANSWERS_TABLE);
                    
            $result = mobilAP::query($sql, array($evaluation_id, $index, $value));
        }
        
		mobilAP::setSerialValue($user->getUserID(),mobilAP::SERIAL_TYPE_USER);
		$this->updateSerial();
		return true;
	}

    /**
     * clears the evaluation data for this session
     */
	public function clearEvaluations($admin_userID)
	{
        if (!$this->isAdmin($admin_userID)) {
            return new mobilAP_Error('Unauthorized', mobilAP_UserSession::USER_UNAUTHORIZED);
        }
        
        //get users who have answered the evaluation so we can update their serials
        $sql = sprintf("SELECT post_user FROM %s WHERE session_id=?", mobilAP_session::SESSION_EVALUATION_TABLE);
        $params = array($this->session_id);
		$result = mobilAP::query($sql, $params);
		$users = array();
		while ($row = $result->fetchRow()) {
			$users[] = $row['post_user'];
		}
        
		$sql = sprintf("DELETE FROM %s WHERE session_id=?",
				mobilAP_session::SESSION_EVALUATION_TABLE);
		$result = mobilAP::query($sql, $params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		
		foreach ($users as $userID) {
			mobilAP::setSerialValue($userID,mobilAP::SERIAL_TYPE_USER);
		}

		$this->updateSerial();
		return true;
	}

    /**
     * clears the discussion for this session
     */
	public function clearDiscussion($admin_userID)
	{
        if (!$this->isAdmin($admin_userID)) {
            return new mobilAP_Error('Unauthorized', mobilAP_UserSession::USER_UNAUTHORIZED);
        }

		$sql = sprintf("DELETE FROM %s WHERE session_id=?",
				mobilAP_session::SESSION_DISCUSSION_TABLE);
		$params = array($this->session_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}

		$this->updateSerial();
		return true;
	}
	
    /**
     * posts a discussion to this session
     * @param string $post_text
     * @param string $post_userID userID of user posting the link
     * @return true if successful or error object
     */
	public function post_discussion($post_text, $post_userID)
	{
		if (empty($post_text)) {
			return mobilAP_Error::throwError("Text cannot be empty");
		} elseif (empty($post_userID)) {
			return mobilAP_Error::throwError("Please login to participate in the discussion", mobilAP_session::ERROR_NO_USER);
		} elseif (!$user = mobilAP_user::getUserById($post_userID)) {
			return mobilAP_Error::throwError("Invalid user $post_userID");
		} elseif (!($this->session_flags & mobilAP_session::SESSION_FLAGS_DISCUSSION)) {
			return mobilAP_Error::throwError("Discussion to this session has been disabled");
		}
		
		$ts = time();
		$sql = sprintf("INSERT INTO %s
				(session_id, post_user, post_timestamp, post_text)
				VALUES
				(?,?,?,?)",
				mobilAP_session::SESSION_DISCUSSION_TABLE);
		$params = array($this->session_id, $user->getUserID(), $ts, $post_text);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}

		$this->updateSerial();
		return true;
	}
	
    /**
     * removes a discussion post from this session
     * @param int $post_id
     * @return true if successful or error object
     */
	public function delete_discussion_post($post_id,$admin_userID)
	{
		// check privilages
		if (!$this->isAdmin($admin_userID)) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		}

		$sql = sprintf("DELETE FROM %s 
				WHERE session_id=? AND post_id=?",
				 mobilAP_session::SESSION_DISCUSSION_TABLE);
		$params = array($this->session_id, $post_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}

		$this->updateSerial();
		return true;
	}

	public function get_discussion($last_post=0)
	{
		$sql = sprintf("SELECT post_id,post_timestamp, post_user, post_text, email FROM %s c 
				LEFT JOIN %s u ON c.post_user=u.userID
				WHERE session_id=? AND post_id>?
				ORDER BY post_timestamp ASC",
        mobilAP_session::SESSION_DISCUSSION_TABLE,
         mobilAP_user::USER_TABLE);
        $params = array($this->session_id,$last_post);
         
		$result = mobilAP::query($sql,$params);
		$discussion = array();

		if (mobilAP_Error::isError($result)) {
			return $discussion;
		}
		while ($row = $result->fetchRow()) {
			$row['post_id'] = intval($row['post_id']);
			$row['date'] = strftime('%b %d, %Y %H:%M:%S', $row['post_timestamp']);
			$discussion[] = $row;
		}
			
		return $discussion;
	}

    /**
     * sets the session title
     * @param string $session_title
     * @return boolean true if successful or false
     */
	public function setSessionTitle($session_title)
	{
		if (!empty($session_title)) {
			$this->session_title = trim($session_title);
			return true;
		} else {
			return false;
		}
	}

    /**
     * sets the session description/abstract
     * @param string $session_description
     * @return true if successful or false
     */
	public function setSessionDescription($session_description)
	{
		$this->session_description = trim($session_description);
		return true;
	}
	
    /**
     * commits the session data to the database
     * @param string $userID user who is updating the data
     * @return true if successful or error object
     */
	public function updateSession($admin_userID)
	{
        if (!$this->isAdmin($admin_userID)) {
            return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
        }
        
		$sql = sprintf("UPDATE %s SET
		session_title=?, session_description=?, session_flags=?
		WHERE session_id=?",
		mobilAP_session::SESSION_TABLE);
		$params = array($this->session_title, $this->session_description, $this->session_flags,
		$this->session_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		mobilAP_Cache::flushCache('mobilAP_schedule');				
		mobilAP::setSerialValue('sessions');
		mobilAP::setSerialValue('schedule');
		$this->updateSerial();
        return true;
	}
}

class mobilAP_session_group
{
	var $session_group_id;
	var $session_group_title;
	var $session_group_detail;
	var $schedule_items=array();
	const SESSION_GROUP_TABLE='session_groups';

	function getSessionGroupByID($session_group_id) {	
		$sql = sprintf("SELECT * FROM %s WHERE session_group_id=%d", mobilAP_session_group::SESSION_GROUP_TABLE, $session_group_id);
		$result = mobilAP::query($sql);
		
		$session_group = null;
		if (mobilAP_Error::isError($result)) {
			return $session_group;
		}
		if ($row = $result->fetchRow()) {
			$session_group = new mobilAP_session_group();
			$session_group->session_group_title = $row['session_group_title'];
			$session_group->session_group_id = $row['session_group_id'];
			$session_group->session_group_detail = $row['session_group_detail'];
			$session_group->schedule_items = $session_group->getScheduleItems();
		}
		
		return $session_group;
	}
	
	function setTitle($session_group_title)
	{
		if (!empty($session_group_title)) {
			$this->session_group_title = $session_group_title;
			return true;
		} else {
			return false;
		}
	}

	function setDetail($session_group_detail)
	{
		$this->session_group_detail = $session_group_detail;
	}
	
	function updateGroup()
	{
		$sql = sprintf("UPDATE %s SET 
						session_group_title=?,
						session_group_detail=?
						WHERE session_group_id=?", mobilAP_session_group::SESSION_GROUP_TABLE);
		$params = array($this->session_group_title, $this->session_group_detail, $this->session_group_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		
		return true;
	}

	function createGroup()
	{
		if (empty($this->session_group_title)) {
			return mobilAP_Error::throwError("Session group title cannot be blank");
		}

		$sql = sprintf("INSERT INTO %s (session_group_title, session_group_detail) VALUES (?, ?)",
						mobilAP_session_group::SESSION_GROUP_TABLE);
		$params = array($this->session_group_title, $this->session_group_detail);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		
		$this->session_group_id = $result->get_last_insert_id();
		$this->updateGroup();
		return true;
	}
	
	function deleteGroup()
	{
		$tables = array(mobilAP_session_group::SESSION_GROUP_TABLE);
		foreach ($tables as $table) {
			$sql = sprintf("DELETE FROM %s WHERE session_group_id=%d" , $table,  $this->session_group_id);
			$result = mobilAP::query($sql);

			if (mobilAP_Error::isError($result)) {
				return $result;
			}
		}

		$sql = sprintf("UPDATE %s SET session_group_id=NULL WHERE session_group_id=%d" , mobilAP_schedule::SCHEDULE_TABLE,  $this->session_group_id);
		$result = mobilAP::query($sql);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		
		return true;
	}
	
	function getScheduleItems()
	{
		$schedule_items = array();
		$sql = sprintf("SELECT * FROM %s WHERE session_group_id=%d ORDER BY start_time asc,session_id", mobilAP_schedule::SCHEDULE_TABLE, $this->session_group_id);
		$result = mobilAP::query($sql);

		if (mobilAP_Error::isError($result)) {
			return $schedule_items;
		}

		while ($row = $result->fetchRow()) {
			$schedule_item = new mobilAP_schedule_item();
			$schedule_item->schedule_id = $row['schedule_id'];
			$schedule_item->start_date = strftime('%b %d, %Y %H:%M:%S', $row['start_ts']);
			$schedule_item->start_ts = $row['start_ts'];
			$schedule_item->date = strftime('%Y-%m-%d', $schedule_item->start_ts);
			$schedule_item->end_date = strftime('%b %d, %Y %H:%M:%S', $row['end_ts']);
			$schedule_item->end_ts = $row['end_ts'];
			$schedule_item->title = $row['title'];
			$schedule_item->detail = $row['detail'];
			$schedule_item->room = $row['room'];
			$schedule_item->session_id = $row['session_id'];
			$schedule_item->session_group_id = $row['session_group_id'];
			$schedule_items[] = $schedule_item;
		}
		return $schedule_items;
	}
	
	function getSessionGroups()
	{
		$sql = "SELECT * FROM " . mobilAP_session_group::SESSION_GROUP_TABLE ;
		$result = mobilAP::query($sql);
		$session_groups = array();

		if (mobilAP_Error::isError($result)) {
			return $session_groups;
		}
		
		while ($row = $result->fetchRow()) {
			$session_group = new mobilAP_session_group();
			$session_group->session_group_id = $row['session_group_id'];
			$session_group->session_group_title = $row['session_group_title'];
			$session_group->session_group_detail = $row['session_group_detail'];
			$session_group->schedule_items = $session_group->getScheduleItems();
			$session_groups[$row['session_group_id']] = $session_group;
		}
		
		return $session_groups;
	}
}

class mobilAP_session_link
{
	var $link_id;
	var $session_id;
	var $link_url;
	var $link_text;
	var $post_user;
	var $link_type;
	var $post_timestamp;
	
	private function updateSerial()
	{
		$session = $this->getSession();
		$session->updateSerial();
	}
	
	function getSession()
	{
		return mobilAP_session::getSessionByID($this->session_id);
	}
	
	function setURL($url)
	{
		if (preg_match('@http[s]?://(.+)@', $url)) {
			$this->link_url = $url;
			return true;
		} else {
			trigger_error("Invalid url $url found when setting link");
			return false;
		}
	}

	function setText($text)
	{
		if (!empty($text)) {
			$this->link_text = $text;
			return true;
		} else {
			return false;
		}
	}
	
	function updateLink()
	{
		$sql = sprintf("UPDATE %s SET
				link_url=?,
				link_text=?
				WHERE link_id=?",
			mobilAP_session::SESSION_LINK_TABLE
		);
		$params = array($this->link_url, $this->link_text, $this->link_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}

		$this->updateSerial();
		return true;
	}

	function deleteLink()
	{
		$tables = array(mobilAP_session::SESSION_LINK_TABLE);
		$params = array($this->link_id);
		foreach ($tables as $table)
		{
			$sql = sprintf("DELETE FROM %s WHERE link_id=?", $table);
			$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
		}
		
		$this->updateSerial();
		return true;
	}
	
	function loadLinkFromArray($arr)
	{
		$link = new mobilAP_session_link();
		$link->link_id = $arr['link_id'];
		$link->session_id = $arr['session_id'];
		$link->link_url = $arr['link_url'];
		$link->link_text = $arr['link_text'];
		$link->link_type = $arr['link_type'];
		$link->post_user = $arr['post_user'];
		$link->post_timestamp = $arr['post_timestamp'];
		return $link;
	}

}

class mobilAP_session_question
{
	const CHART_TYPE_PIE='p';
	const CHART_TYPE_BAR='b';
	const RESPONSE_TYPE_CHOICE='C';
	const RESPONSE_TYPE_ZIP='Z';
	const RESPONSE_TYPE_='Z';
	var $question_id;
	var $session_id;
	var $question_index=0;
	var $question_text;
	var $question_list_text;
	var $question_minchoices=0;
	var $question_maxchoices=1;
	var $question_active=-1;
	var $response_type=mobilAP_session_question::RESPONSE_TYPE_CHOICE;
	var $chart_type=mobilAP_session_question::CHART_TYPE_PIE;
	var $responses=array();
	var $answers=array();
	
	function __toString()
	{
		return $this->question_text;
	}
	
	function __construct($session_id)
	{
		$this->session_id = $session_id;
	}
	
	function getChartTypes()
	{
		return array(
			'p'=>'Pie Chart',
			'bhs'=>'Bar Chart'
		);
			
	}

	private function updateSerial()
	{
		$session = $this->getSession();
		$session->updateSerial();
	}
	
	function getSession()
	{
		return mobilAP_session::getSessionByID($this->session_id);
	}
	
	function isAdmin($userID)
	{
		if ($session = $this->getSession()) {
			return $session->isAdmin($userID);
		}
		
		return false;
	}
	
	function setQuestionActive($active)
	{
		$this->question_active = $active ? -1 : 0;
	}
	
	function setChartType($chart_type)
	{
		$chart_types = mobilAP_session_question::getChartTypes();
		if (isset($chart_types[$chart_type])) {
			$this->chart_type = $chart_type;
			return true;
		} else {
			return false;
		}
	}
	
	function setText($question_text)
	{
		if (is_string($question_text) && !empty($question_text)) {
			$this->question_text = $question_text;
			return true;
		} else {
			return false;
		}
	}
	
	function setMaxChoices($max_choices)
	{
		if (intval($max_choices)) {
			$this->question_maxchoices = intval($max_choices);
			return true;
		} else {
			return false;
		}
	}

	function setMinChoices($min_choices)
	{
		if (intval($min_choices) || $min_choices==0) {
			$this->question_minchoices = intval($min_choices);
			return true;
		} else {
			return false;
		}
	}

    function getChartURL()
    {
        
        $max_label_length=15;
        
        //go through the responses, for pie charts, don't include responses with zero answers
        //max_data value represents the highest value and is used to scale the bar charts
        
        $max_data = 0;
        $data = array();
        $labels = array();
        
        foreach ($this->responses as $i=>$response) {
        	if ($this->answers[$response->response_value]>0 || $this->chart_type != 'p') {
        		$data[] = $this->answers[$response->response_value];

                if ($this->answers[$response->response_value] > $max_data) {
                    $max_data = $this->answers[$response->response_value];
                }
        		
				$labels[] = strlen($response->response_text)>$max_label_length ? $i+1 : urlencode($response->response_text);
        	}
        }

		// base url with type, size and background
        $src = 'http://chart.apis.google.com/chart?cht=' . $this->chart_type . '&chf=bg,s,00000000';

		// add the data using text encoding
		$src .='&chd=t:' . implode(",", $data);
        
        //make no more than 10 x-axis legends, use the next whole factor for the max
        $step = 0;
        do {
            $step +=2;
            $even_max = $max_data % $step ? ($max_data+($max_data % $step)) : $max_data;
            
        } while ($even_max / $step > 10);
        
        switch ($this->chart_type)
        {
            case 'p':
                $src .='&chs=280x140';
                $src .='&chl=' . implode('|', $labels);
                break;
            
            case 'bhs':
                $src .='&chs=280x' . ((count($this->responses)*33)+20);
                $src .='&chxt=x,y';
                $src .='&chds=0,' . $even_max;
                $range = array();
                for ($i=0; $i<=$even_max; $i+=$step) {
                    $range[] = $i;
                }               
                                                
                $src .='&chxl=0:|' . implode('|', $range) . '|1:|' . implode('|', array_reverse($labels));
                break;
        }
        
        return $src;
    }

	function submitAnswer($responses, $user_token)
	{
		$user_token = $user_token ? $user_token : null;
		$responses = is_array($responses) ? $responses : array();
		$ts = time();
		$_responses=array();
								
		if (count($responses) < $this->question_minchoices || count($responses) > $this->question_maxchoices) {
			$message = "Question should have ";
			if ($this->question_maxchoices==1) {
				$message .= "1 choice";
			} elseif ($this->question_minchoices==$this->question_maxchoices) {
				$message .= sprintf("%d choices", $this->question_maxchoices);
			} else { 
				$message .= sprintf(" between %d and %d choices", $this->question_minchoices, $this->question_maxchoices);
			}					
			
			return mobilAP_Error::throwError($message);
		} 

		if (!$user = mobilAP_User::getUserById($user_token)) {
			return mobilAP_Error::throwError("Please login to answer this question", mobilAP_session::ERROR_NO_USER);
		}
		
		if ($user_token) {		
			$sql = sprintf("SELECT answer_id FROM %s
				    WHERE question_id=? AND response_userID=?", mobilAP_session::POLL_ANSWERS_TABLE);
			$params = array($this->question_id, $user_token);
			$result = mobilAP::query($sql, $params);
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
			if ($result->fetchRow()) {
				return mobilAP_Error::throwError("You have already answered this question", mobilAP_session::ERROR_USER_ALREADY_SUBMITTED);
			}
		}
		
		foreach ($responses as $response_value) {
			if ($this->is_response($response_value)) {
				$sql = sprintf("INSERT INTO %s (question_id, response_value, response_timestamp, response_userID)
				VALUES (?,?,?,?)", mobilAP_session::POLL_ANSWERS_TABLE);
				$params = array($this->question_id, $response_value, $ts, $user_token);
				$result = mobilAP::query($sql, $params);

				if (mobilAP_Error::isError($result)) {
					return $result;
				}
			}
		}
		$this->answers = $this->getAnswers();
		if ($user_token) {
			mobilAP::setSerialValue($user_token,mobilAP::SERIAL_TYPE_USER);
		}
		$this->updateSerial();
        return true;
	}
	
	function is_response($response_value) 
	{
		foreach ($this->responses as $response) {
			if ($response->response_value ==$response_value) 
				return true;
		}
		
		return false;
	}
	
	function loadQuestionFromArray($arr)
	{
		$question = new mobilAP_session_question($arr['session_id']);
		$question->question_id = intval($arr['question_id']);
		$question->question_index = intval($arr['question_index']);
		$question->question_text = $arr['question_text'];
		$question->question_list_text = $arr['question_list_text'];
		$question->question_minchoices =  intval($arr['question_minchoices']);
		$question->question_maxchoices =  intval($arr['question_maxchoices']);
		$question->question_active =  intval($arr['question_active']);
		$question->response_type =  $arr['response_type'];
		$question->chart_type =  $arr['chart_type'];
		$question->responses = $question->getResponses();
		$question->answers = $question->getAnswers();
		return $question;
		
	}
	
	function setQuestion($question_text)
	{
		if (is_string($question_text) && !empty($question_text)) {
			$this->question_text = $question_text;
			return true;
		} else {
			return false;
		}
	}

	function setQuestionListText($question_list_text)
	{
		$this->question_list_text = $question_list_text;
		return true;
	}
	
	function deleteQuestion($userID)
	{
		//find out who answered the question so we can update their serial
		$sql = sprintf("SELECT response_userID FROM %s WHERE question_id=?", mobilAP_session::POLL_ANSWERS_TABLE);
		$params = array($this->question_id);
		$result = mobilAP::query($sql,$params);
		$users = array();
		while ($row = $result->fetchRow()) {
			$users[] = $row['response_userID'];
		}
	
		$tables = array(mobilAP_session::POLL_QUESTIONS_TABLE, mobilAP_session::POLL_RESPONSES_TABLE, mobilAP_session::POLL_ANSWERS_TABLE);
		foreach ($tables as $table)
		{
			$sql = sprintf("DELETE FROM %s WHERE question_id=?", $table);
			$params = array($this->question_id);
			$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
		}
				
		//reindex questions
		$sql = sprintf("UPDATE %s SET
						question_index=question_index-1 
						WHERE session_id=? AND question_index>?",
						mobilAP_session::POLL_QUESTIONS_TABLE);
		$params = array($this->session_id,$this->question_index);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}

		foreach ($users as $userID) {
			mobilAP::setSerialValue($userID, mobilAP::SERIAL_TYPE_USER);
		}
		$this->updateSerial();
		return true;
	}
	
	function clearAnswers($admin_userID)
	{
        if (!$this->isAdmin($admin_userID)) {
            return new mobilAP_Error('Unauthorized', mobilAP_UserSession::USER_UNAUTHORIZED);
        }

		//find out who answered the question so we can update their serial
		$sql = sprintf("SELECT response_userID FROM %s WHERE question_id=?",mobilAP_session::POLL_ANSWERS_TABLE);
		$params = array($this->question_id);
		$result = mobilAP::query($sql,$params);
		$users = array();
		while ($row = $result->fetchRow()) {
			$users[] = $row['response_userID'];
		}

		$sql = sprintf("DELETE FROM %s WHERE question_id=?", mobilAP_session::POLL_ANSWERS_TABLE);
		$params = array($this->question_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		$this->answers = $this->getAnswers();

		foreach ($users as $userID) {
			mobilAP::setSerialValue($userID, mobilAP::SERIAL_TYPE_USER);
		}
		
		$this->updateSerial();
        return true;
	}
	
	function getNextResponseIndex()
	{
		return count($this->responses);
	}

	function getNextResponseValue()
	{
		$sql = sprintf("SELECT MAX(response_value) response_value FROM %s
				WHERE question_id=?",
				mobilAP_session::POLL_RESPONSES_TABLE);
		$params = array($this->question_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		
		$row = $result->fetchRow();
		if ($row['response_value']) {
			$next = intval($row['response_value'])+1;
		} else {
			$next = 1;
		}
		
		return $next;
	}

	function getQuestionById($question_id)
	{
		$sql = sprintf("SELECT * FROM %s
				WHERE question_id=?",
				mobilAP_session::POLL_QUESTIONS_TABLE);
		$params = array($question_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		$question = false;
		if ($row = $result->fetchRow()) {
			$question = mobilAP_session_question::loadQuestionFromArray($row);
		}
		return $question;		
	}

	function deleteResponse($response_value)
	{
		if ($response = $this->getResponseByValue($response_value)) {

			$index = $response->response_index;
			$result = $response->deleteResponse();
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
			
            $sql = sprintf("SELECT response_index FROM %s 
            				WHERE question_id=? AND response_index>? 
            				ORDER BY response_index ASC",
            				mobilAP_session::POLL_RESPONSES_TABLE);
			$params = array($this->question_id,$index);            
            $result = mobilAP::query($sql,$params);
            while ($row = $result->fetchRow()) {
            
                $sql = sprintf("UPDATE %s SET 
                    response_index=response_index-1 
                    WHERE question_id=? AND response_index=?",
                    mobilAP_session::POLL_RESPONSES_TABLE);
                $params = array($this->question_id, $row['response_index']);
                $_result = mobilAP::query($sql,$params);
                if (mobilAP_Error::isError($_result)) {
                    return $_result;
                }
            }
			$this->responses = $this->getResponses();
			$this->answers = $this->getAnswers();
			$this->updateSerial();
			return true;
		} else {
			return mobilAP_Error::throwError("There is no response $response_value for this question");
		}
	}
	
	function addResponse($response_text)
	{
		if (empty($response_text)) {
			return mobilAP_Error::throwError("Invalid response");
		}
		
		$index = $this->getNextResponseIndex();
		$response_value = $this->getNextResponseValue();
		
		
		$sql = sprintf("INSERT INTO %s (question_id, response_index, response_value, response_text)
				VALUES (?,?,?,?)", mobilAP_session::POLL_RESPONSES_TABLE);
		$params = array($this->question_id,$index,$response_value,$response_text);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		$this->responses = $this->getResponses();
		$this->answers = $this->getAnswers();

		if (mobilAP_Error::isError($result)) {
			if ($result->getCode()==DB_ERROR_ALREADY_EXISTS) {
				$result = mobilAP_Error::throwError("$response_text already exists", DB_ERROR_ALREADY_EXISTS);
			} else {
			}
			return $result;
		}
		$this->updateSerial();
		return true;
	}
	
	function &getResponseByValue($response_value)
	{
		$return = false;
		foreach ($this->responses as $index=>$response) {
			if ($response->response_value==$response_value) {
				return $response;
			}
		}
		return $return;
	}
	
	function getResponses()
	{
		$sql = sprintf("SELECT * FROM %s 
						WHERE question_id=? ORDER BY response_index",
						mobilAP_session::POLL_RESPONSES_TABLE);
		$params = array($this->question_id);
		$result = mobilAP::query($sql,$params);
		$responses = array();
		if (mobilAP_Error::isError($result)) {
			return $responses;
		}
		while ($row = $result->fetchRow()) {
			$row['session_id'] = $this->session_id;
			$responses[] = mobilAP_session_question_response::loadResponseFromArray($row);
		}
		
		return $responses;
	}
	
	function getAllAnswers()
	{
		$sql = sprintf("SELECT pa.*, a.FirstName, a.LastName, a.email FROM %s pa
				LEFT JOIN %s a ON pa.response_userID=a.attendee_id
				WHERE question_id=?
				ORDER BY response_value, LastName, FirstName",
				mobilAP_session::POLL_ANSWERS_TABLE, mobilAP_attendee::ATTENDEE_TABLE);
		$params = array($this->question_id);
		$result = mobilAP::query($sql,$params);
		$answers = array();
		if (mobilAP_Error::isError($result)) {
			return $answers;
		}
		while ($row = $result->fetchRow()) {
			$answers[$row['response_value']][] = $row;
		}
		
		return $answers;
	}

	function getAnswers()
	{
		$answers = array('total'=>0,'users'=>0);
		foreach ($this->responses as $index=>$response) {
			$answers[$response->response_value] = 0;
		}
		
		if (count($this->responses)>0) {
			$sql = sprintf("SELECT count(*) count, response_value FROM %s
			WHERE question_id=?
			GROUP BY response_value",
			mobilAP_session::POLL_ANSWERS_TABLE);
			$params = array($this->question_id);
			$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return $answers;
			}
			while ($row = $result->fetchRow()) {
				if ($row['response_value']) {
					$answers['total']+=$row['count'];
					$answers[$row['response_value']]+=$row['count'];
				}
			}
			
			$sql = sprintf("SELECT DISTINCT response_userID FROM %s
							WHERE question_id=? AND response_value",
							mobilAP_session::POLL_ANSWERS_TABLE);
			$params = array($this->question_id);
			$result = mobilAP::query($sql,$params);
			$users = array();
			if (mobilAP_Error::isError($result)) {
				return $answers;
			}

			while ($row = $result->fetchRow()) {
				$users[] = $row['response_userID'];
			}
			$answers['users'] = count($users);
		}
		
		return $answers;
	}
	
	function updateQuestion($userID)
	{
		$sql = sprintf("UPDATE %s SET
				question_text=?,
				question_list_text=?,
				question_minchoices=?,
				question_maxchoices=?,
				question_active=?,
				chart_type=?,
				response_type=?
				WHERE question_id=?",
				mobilAP_session::POLL_QUESTIONS_TABLE);
		$params = array($this->question_text, $this->question_list_text, 
		$this->question_minchoices, $this->question_maxchoices, $this->question_active,$this->chart_type,$this->response_type, 
		$this->question_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		$this->updateSerial();
		return true;
	}

	
}

class mobilAP_session_question_response
{
	var $session_id;
	var $question_id;
	var $response_index;
	var $response_value;
	var $response_text;

	function __toString()
	{
		return $this->response_text;
	}

	private function updateSerial()
	{
		$session = $this->getSession();
		$session->updateSerial();
	}
	
	function getSession()
	{
		return mobilAP_session::getSessionByID($this->session_id);
	}
	
	function deleteResponse()
	{
		$sql = sprintf("SELECT response_userID FROM %s WHERE question_id=? AND response_value=?", 
						mobilAP_session::POLL_ANSWERS_TABLE);
		$params = array($this->question_id, $this->response_value);
		$result = mobilAP::query($sql,$params);
		$users = array();
		while ($row = $result->fetchRow()) {
			$users[] = $row['response_userID'];
		}
		
		$tables = array(mobilAP_session::POLL_RESPONSES_TABLE, mobilAP_session::POLL_ANSWERS_TABLE);
		foreach ($tables as $table)
		{
			$sql = sprintf("DELETE FROM %s WHERE question_id=? AND response_value=?", $table);
			$params = array($this->question_id, $this->response_value);
			$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
		}
		
		foreach ($users as $userID) {
			mobilAP::setSerialValue($userID, mobilAP::SERIAL_TYPE_USER);
		}
		$this->updateSerial();
		return true;
	}
	
	function setResponseText($response_text)
	{
		if (empty($response_text)) {
			return mobilAP_Error::throwError("Invalid response");
		}
		$this->response_text = $response_text;
		return true;
	}

	function updateResponse()
	{
		$sql = sprintf("UPDATE %s SET
			response_text=?
			WHERE question_id=? AND response_value=?",
			mobilAP_session::POLL_RESPONSES_TABLE);
		$params = array($this->response_text, $this->question_id, $this->response_value);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		$this->updateSerial();
		return true;
	}

	function loadResponseFromArray($arr)
	{
		$response = new mobilAP_session_question_response();
		$response->session_id = $arr['session_id'];
		$response->question_id = intval($arr['question_id']);
		$response->response_index = intval($arr['response_index']);
		$response->response_value = intval($arr['response_value']);
		$response->response_text = $arr['response_text'];
		return $response;
	}
	
}

?>