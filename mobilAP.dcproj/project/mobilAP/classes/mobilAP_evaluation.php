<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('mobilAP_session.php');

class mobilAP_evaluation_question
{
	var $question_index;
	var $question_text;
	var $question_response_type='M';
	const RESPONSE_TYPE_TEXT='T';
	const RESPONSE_TYPE_CHOICES='M';
	const EVALUATION_QUESTION_TABLE='evaluation_questions';	
	const EVALUATION_QUESTION_RESPONSE_TABLE='evaluation_question_responses';
	
	function updateSerial()
	{
		mobilAP::setSerialValue('evaluation_questions');	
	}
	
	function removeResponse($response_index)
	{
		$sql = sprintf("DELETE FROM %s WHERE question_index=%d AND response_index=%d",
		mobilAP_evaluation_question::EVALUATION_QUESTION_RESPONSE_TABLE, $this->question_index, $response_index);
		$result = mobilAP::query($sql);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		
		$sql = sprintf("UPDATE %s SET response_index=response_index-1, response_value=response_value-1 WHERE question_index=%d AND response_index>%d ORDER BY response_index ASC",
		mobilAP_evaluation_question::EVALUATION_QUESTION_RESPONSE_TABLE, $this->question_index, $response_index);
		$result = mobilAP::query($sql);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		$this->updateSerial();
		return true;
	}
	
	function addResponse($response_text)
	{
		if (empty($response_text)) {
			return mobilAP_Error::throwError("Response cannot be empty");
		}
		
		$responses = $this->getResponses();
		$response_index = count($responses);
		$response_value = count($responses)+1;
		$sql = sprintf("INSERT INTO %s (question_index, response_index, response_text, response_value) 
		VALUES (?,?,?,?)", mobilAP_evaluation_question::EVALUATION_QUESTION_RESPONSE_TABLE);
		$params = array($this->question_index, $response_index, $response_text, $response_value);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		$this->updateSerial();
		return true;
	}
	
	function deleteQuestion($userID)
	{
		// check privilages
		if (!$user = mobilAP_user::getUserById($userID)) {
			return mobilAP_Error::throwError("Unauthorized");
		} elseif (!$user->isSiteAdmin()) {
			return mobilAP_Error::throwError("Unauthorized");
		}

		$questions = mobilAP::getEvaluationQuestions();
        
		$sql = sprintf("DELETE FROM %s WHERE question_index=%d",
		mobilAP_evaluation_question::EVALUATION_QUESTION_TABLE, $this->question_index);
		$result = mobilAP::query($sql);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
        
		$sql = sprintf("DELETE FROM %s WHERE question_index=%d",
		mobilAP_evaluation_question::EVALUATION_QUESTION_RESPONSE_TABLE, $this->question_index);
		$result = mobilAP::query($sql);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
        
		unset($questions[$this->question_index]);
        
		for ($i=$this->question_index+1; $i<=count($questions); $i++) {
        
			$sql = sprintf("UPDATE %s SET question_index=%d WHERE question_index=%d", mobilAP_evaluation_question::EVALUATION_QUESTION_TABLE, $i-1, $i);
			$result = mobilAP::query($sql);
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
            
			$sql = sprintf("UPDATE %s SET question_index=%d WHERE question_index=%d", mobilAP_evaluation_question::EVALUATION_QUESTION_RESPONSE_TABLE, $i-1, $i);
			$result = mobilAP::query($sql);
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
		}
		$this->updateSerial();
        return true;
	}
	
	function addQuestion($userID)
	{
		// check privilages
		if (!$user = mobilAP_user::getUserById($userID)) {
			return mobilAP_Error::throwError("Unauthorized");
		} elseif (!$user->isSiteAdmin()) {
			return mobilAP_Error::throwError("Unauthorized");
		}

		$questions = mobilAP::getEvaluationQuestions();
		$this->question_index = count($questions);

		$sql = sprintf("INSERT INTO %s (question_index, question_text, question_response_type)
		VALUES (?,?,?)", mobilAP_evaluation_question::EVALUATION_QUESTION_TABLE);
		$params = array($this->question_index,$this->question_text, $this->question_response_type);
		$result = mobilAP::query($sql,$params);

		if (mobilAP_Error::isError($result)) {
			return $result;
		}

		$this->updateSerial();
		return true;
	}

	function updateQuestion($userID)
	{
		// check privilages
		if (!$user = mobilAP_user::getUserById($userID)) {
			return mobilAP_Error::throwError("Unauthorized");
		} elseif (!$user->isSiteAdmin()) {
			return mobilAP_Error::throwError("Unauthorized");
		}

		$sql = sprintf("UPDATE %s SET question_text='%s', question_response_type='%s' WHERE question_index=%d", 
		mobilAP_evaluation_question::EVALUATION_QUESTION_TABLE);
		$params = array($this->question_text, $this->question_response_type, $this->question_index);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		$this->updateSerial();
		return true;
	}
	
	private function getColumnType()
	{
		switch ($this->question_response_type)
		{
			case mobilAP_evaluation_question::RESPONSE_TYPE_TEXT:
				return 'text';
			case mobilAP_evaluation_question::RESPONSE_TYPE_CHOICES:
				return 'tinyint(4) unsigned';
		}
	}
	
	function setQuestionText($question_text)
	{
		if (is_string($question_text) && !empty($question_text)) {
			$this->question_text = $question_text;
			return true;
		} else {
			return false;
		}
	}

	function setQuestionResponseType($question_response_type)
	{
		switch ($question_response_type)
		{
			case mobilAP_evaluation_question::RESPONSE_TYPE_TEXT:
			case mobilAP_evaluation_question::RESPONSE_TYPE_CHOICES:

				$this->question_response_type = $question_response_type;
				if (!is_null($this->question_index)) {
					$sql = sprintf("UPDATE %s SET question_response_type='%s' WHERE question_index=%d", 
					mobilAP_evaluation_question::EVALUATION_QUESTION_TABLE, $this->question_response_type, $this->question_index);
					$result = mobilAP::query($sql);
					if (mobilAP_Error::isError($result)) {
						return $result;
					}
					$this->updateSerial();
				}
				
				return true;
				break;
		}
		
		return false;
	}

	function getQuestionByIndex($question_index)
	{
		$sql = sprintf("SELECT * FROM %s WHERE question_index=%d", mobilAP_evaluation_question::EVALUATION_QUESTION_TABLE, $question_index);
		$result = mobilAP::query($sql);
		if (mobilAP_Error::isError($result)) {
			return false;
		}
		if ($row = $result->fetchRow()) {
			$question = new mobilAP_evaluation_question();
			$question->question_index = intval($row['question_index']);
			$question->question_text = $row['question_text'];
			$question->question_response_type = $row['question_response_type'];
		} else {
			$question = false;
		}
		
		return $question;
	}
	
	function getResponses()
	{
		if ($this->question_response_type == mobilAP_evaluation_question::RESPONSE_TYPE_TEXT) {
			return false;
		}
		
		$sql = sprintf("SELECT response_index, response_text, response_value FROM %s WHERE question_index=%d ORDER BY response_index", mobilAP_evaluation_question::EVALUATION_QUESTION_RESPONSE_TABLE, $this->question_index);
		$result = mobilAP::query($sql);
		$responses = array();
		if (mobilAP_Error::isError($result)) {
			return $responses;
		}
		while ($row = $result->fetchRow()) {
            $response = array(
                'response_index'=>intval($row['response_index']),
                'response_text'=>$row['response_text'],
                'response_value'=>intval($row['response_value'])
            );
			$responses[] = $response;
		}
		
		return $responses;
	}
}

?>