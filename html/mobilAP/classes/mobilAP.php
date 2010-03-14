<?php


/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

/**
	mobliAP entry class. Most of the public methods called from front end 
	code is contained in this class. This is to abstract as much
	code as possible.
	
*/
class mobilAP
{
    const CONFIG_TABLE='mobilAP_config';
    const SERIAL_TABLE='mobilAP_serials';
    /**
     * returns whether or not mobilAP has been setup or not
     * @return bool
     */
	function isSetup()
	{
        return mobilAP::getConfig('setupcomplete');
	}
    
    /**
    * returns a list of serial number entries. serial numbers reduce transferring large amounts of code to clients 
    * that can cache data by tracking changes to the underlying data
    * @return array
    */
    function getSerials()
    {
        $serials = array();
		$sql = sprintf("SELECT serial_var,serial_value FROM %s", mobilAP::SERIAL_TABLE);
		$result = mobilAP::query($sql);
		if (mobilAP_Error::isError($result)) {
			return $serials;
		}
        
		while ($row = $result->fetchRow()) {
			$serials[$row['serial_var']] = intval($row['serial_value']);
		}
	
		return $serials;
    }
    
    /**
    * returns a serial value for a particular serial key
    * @param string $serial_var a serial variable to return
    * @return int a serial value or false if none exists
    */
    function getSerialValue($serial_var)
    {
        $serials = mobilAP::getSerials();
		return isset($serials[$serial_var]) ? $serials[$serial_var] : false;
    }

    /**
    * purges a serial value
    * @param string $serial_var a serial variable to purge
    * @return void
    */
    function purgeSerialValue($serial_var)
    {
		$sql = sprintf("DELETE FROM %s WHERE serial_var=?", mobilAP::SERIAL_TABLE);		
		$result = mobilAP::query($sql, array($serial_var));
    }

    /**
    * returns a hash to distinguish mobilAP instances from each other
    * @return string a 32 character hash value
    */
    function getHash()
    {
    	if (!$hash = mobilAP::getConfig('MOBILAP_HASH')) {
    		$hash = md5(uniqid(rand(), true));
    		mobilAP::setConfig('MOBILAP_HASH', $hash);
    	}
    	
    	return $hash;
    }

    /**
    * sets/updates a serial value
    * @param string $serial_var a serial variable to update
    * @return void
    */
    function setSerialValue($serial_var)
    {
		$sql = sprintf("REPLACE INTO %s (serial_var,serial_value) VALUES (?,?)", mobilAP::SERIAL_TABLE);		
		$result = mobilAP::query($sql, array($serial_var,time()));
    }
    
    function canSaveDBConfigFile()
    {
        if (file_exists(mobilAP::dbConfigFile())) {
            return is_writable(mobilAP::dbConfigFile());
        } else {
            return is_writable(mobilAP::dbConfigFolder());
        }
    }

    function dbConfigFolder()
    {
        return implode(DIRECTORY_SEPARATOR, array(MOBILAP_BASE, 'mobilAP','data'));
    }
    
    function dbConfigFile()
    {
        return implode(DIRECTORY_SEPARATOR, array(mobilAP::dbConfigFolder(),'mobilAP_dbconfig.php'));
    }

    /**
     * retrieves a specific database configuration variable
     * @param string $var key of configuration variable to retrieve
     * @return mixed
     */
	public static function getDBConfig($var)
	{
        global $_DBCONFIG;
		return isset($_DBCONFIG[$var]) ? $_DBCONFIG[$var] : null;
	}
    
	public static function getConfigs($refresh=false)
	{
		static $configs;
		if ($configs && !$refresh) {
			return $configs;
		}
	
        require_once('setup/mobilAP_default_config.php');
        $configs = $GLOBALS['CONFIG'];
		$sql = sprintf("SELECT config_var, config_type, config_value FROM %s", 'mobilAP_config');
		$result = mobilAP::query($sql);
		if (mobilAP_Error::isError($result)) {
            
			return $configs;
		}
        
		while ($row = $result->fetchRow()) {
            $value = $row['config_value'];
            switch ($row['config_type'])
            {
                case 'B':
                    $value = $value ? true : false;
                    break;
                case 'I':
                    $value = intval($value);
                    break;
            }
			$configs[$row['config_var']] = $value;
		}
	
		return $configs;
	}
    
    /**
     * retrieves a specific configuration variable
     * @param string $var key of configuration variable to retrieve
     * @return mixed
     */
	public static function getConfig($var)
	{
		$configs = mobilAP::getConfigs();
		return isset($configs[$var]) ? $configs[$var] : null;
	}
	
    /**
     * sets a configuration variable
     * @param string $var key of configuration variable
     * @param mixed $value new value to set
     * @param string $type type of value (B=boolean, I=int, S=string)
     */
	public static function setConfig($var, $value, $type='S')
	{
        if (!in_array($type, array('B','I','S'))) {
            return new mobilAP_Error("Invalid config type");
        }
        
		$sql = sprintf("REPLACE INTO %s (config_var, config_type, config_value) VALUES (?,?,?)", 'mobilAP_config');
		$result = mobilAP::query($sql, array($var, $type, $value));
        
        switch ($var)
        {
            case 'SINGLE_SESSION_MODE':
                if ($value) {
                    require_once('mobilAP_session.php');
                    if (!$session = mobilAP_session::getSessionById(mobilAP_session::SESSION_SINGLE_ID)) {
                        $session = new mobilAP_session();
                        $session->setSessionTitle('Session');
                        $data = $session->createSession(mobilAP_User::USER_ROOT_USERID);
                    }
                }
                break;
        }
        
        mobilAP::setSerialValue('config');
		return mobilAP_Error::isError($result) ? $result : true;
	}
    
    public static function setDefaultConfigs()
    {
        require_once('setup/mobilAP_default_config.php');
        foreach ($GLOBALS['CONFIG'] as $var=>$value) {
            if (is_bool($value)) {
                $type = 'B';
                $value = $value ? -1 : 0;
            } elseif (is_int($value)) {
                $type = 'I';
            } else {
                $type ='S';
            }
            mobilAP::setConfig($var, $value, $type);
        }
    }

	public static function setDBConfig($var, $value)
	{
        if (!mobilAP::canSaveDBConfigFile()) {
            return new mobilAP_Error('Cannot save config file');
        }

        global $_DBCONFIG;
        $_DBCONFIG[$var] = $value;
        return mobilAP::saveDBConfig($_DBCONFIG);
	}

	private static function saveDBConfig($CONFIG)
    {
        if (!mobilAP::canSaveDBConfigFile()) {
            return new mobilAP_Error('Cannot save database config file');
        }

        $contents = array();
        $contents[]  = '<' . '?php';
        $contents[] = '$_DBCONFIG = unserialize(\'' . serialize($CONFIG) . "');";
        $contents[] = "?" . ">";
        return file_put_contents(mobilAP::dbConfigFile(), implode(PHP_EOL, $contents));
    }
	
    /**
     * resets configuration variables to factory defaults
     */
	public static function resetConfigs()
	{
		$sql = sprintf("DELETE FROM %s", 'mobilAP_config');
		$result = mobilAP::query($sql);
		return true;
	}

    /** 
      * executes a query on the database
      @param string $sql sql query to execute
      @return mobilAP_query object (actually a subclass depending on the db type)
    */
   	public static function query($sql, $parameters=array())
   	{
        $conn = mobilAP_db::conn();
        return $conn->query($sql, $parameters);
   	}
   	
    /**
     * retrieves the schedule
     * @param boolean $expand_groups 
     * @return array 
     */
   	public static function getSchedule($expand_groups = false)
	{
		require_once('mobilAP_schedule.php');
		require_once('mobilAP_session.php');
		$days = mobilAP_schedule::getDays();
		$schedule = array();
		$day_map = array();
		$i=0;
		foreach ($days as $day) {
			$schedule[$i] = array('index'=>$i, 'date'=>$day['date'], 'date_str'=>$day['date_str'], 'date_ts'=>$day['date_ts']);
			$day_map[$day['date']] = $i;
			$i++;
		}
		
		$session_groups = mobilAP_session_group::getSessionGroups();
		$parsed_session_groups = array();
								
		$sql = "SELECT s.*, DATE(start_time) date FROM " . mobilAP_schedule::SCHEDULE_TABLE . " s ORDER BY start_time asc,session_id";
		$result = mobilAP::query($sql);
		if (mobilAP_Error::isError($result)) {
			return $schedule;
		}
		while ($row = $result->fetchRow()) {
			if (!$expand_groups && $row['session_group_id']) {
				if (!in_array($row['session_group_id'], $parsed_session_groups)) {
					$row['start_time'] = strftime('%b %d, %Y %H:%M:%S', $row['start_time']);
					$row['end_time'] = strftime('%b %d, %Y %H:%M:%S', $row['end_time']);
					$row['session_id'] = '';
					$row['room'] = '';
					$parsed_session_groups[] = $row['session_group_id'];
					$schedule[$day_map[$row['date']]]['schedule'][] = $row;
				}
			} else {
                $item = new mobilAP_schedule_item();
                $item->loadData($row);
				$schedule[$day_map[$row['date']]]['schedule'][] = $item;
			}
		}
				
		return $schedule;
	}
	
    /**
     * retrieves the evaluation questions
     * @return array of evaluation questions
     */
	public static function getEvaluationQuestions()
	{
        require_once('mobilAP_evaluation.php');
		$questions = array();
        
		$sql = sprintf("SELECT * FROM %s ORDER BY question_index", mobilAP_evaluation_question::EVALUATION_QUESTION_TABLE);
		$result = mobilAP::query($sql);
		if (mobilAP_Error::isError($result)) {
			return $questions;
		}
		while ($row = $result->fetchRow()) {
			$question = new mobilAP_evaluation_question();
			$question->question_index = intval($row['question_index']);
			$question->question_text = $row['question_text'];
			$question->question_response_type = $row['question_response_type'];
			$question->responses = $question->getResponses();
			$questions[] = $question;
		}
		
		return $questions;
	}
    
    /**
     * retrieves the announcements
     * @return array of announcements
     */
    function getAnnouncements()
    {
        require_once('mobilAP_announcement.php');
        return mobilAP_announcement::getAnnouncements();
    }
    
    /**
     * retrieves a session object
     * @param string $session_id session_id of session to get
     * @return object mobilAP_session
     */
    public static function getSessionByID($session_id)
    {
        require_once('mobilAP_session.php');
        return mobilAP_session::getSessionByID($session_id);
    }

    /**
     * retrieves all sessions
     * @return array an array of mobilAP_session objects for each session
     */
    function getSessions()
    {
        require_once('mobilAP_session.php');
        return mobilAP_session::getSessions();
    }

}

/**
	class to handle caching of data
*/
class mobilAP_Cache
{
    /**
     * purges a value from the cache
     * @param string $key key of variable to purge
     */
	public static function flushCache($key)
	{
		if (function_exists('apc_delete')) {
			apc_delete($key);
		}
	}

    /**
     * caches a value
     * @param string $key key of variable to set
     * @param mixed $value value to set
     * @param int $ttl optional time to live in seconds
     */
	public static function setCache($key, $value, $ttl=0)
	{
		if (function_exists('apc_store')) {
			//return apc_store($key, $value, $ttl);
		}
	}

    /**
     * retrieves value from the cache
     * @param $key key of variable to retrieve
     */
	public static function getCache($key)
	{
		if (function_exists('apc_fetch')) {
			//return apc_fetch($key);
		}
		return false;
	}

}

/**
	error handling class
*/
class mobilAP_Error
{
	public $error_message;
	public $error_code;
	public $userinfo;
	
    /**
     * standard PHP toString magic function
     * @return string
     */
	public function __toString()
	{
		if ($this->error_code) {
			return sprintf('%s: %d (%s)', __CLASS__, $this->error_code, $this->error_message);
		} else {
			return sprintf('%s: %s', __CLASS__, $this->error_message);
		}		
	}

    /**
     * constructor
     * @param string $message error message
     * @code int $code error code
     * @userinfo mixed $userinfo optional data
     */
	public function __construct($message=null, $code=null, $userinfo=null) {
        $this->setMessage($message);
        $this->setCode($code);
        $this->setUserInfo($userinfo);
    }

    /**
     * factory method
     * @param string $message error message
     * @code int $code error code
     * @userinfo mixed $userinfo optional data
     */
    public static function throwError($message = null, $code = null, $userinfo = null)
    {
    	$a = new mobilAP_Error($message, $code, $userinfo);
    	return $a;
    }
    
    /**
     * returns whether the given value is an error object
     * @param mixed $data variable to check
     * @return boolean returns true if the value is an error object
     */
    public static function isError($data)
    {
        return is_a($data, 'mobilAP_Error');
    }    

    /**
     * returns error message
     * @return string
     */
	public function getMessage()
	{
		return $this->error_message;
	}

    /**
     * sets error message
     * @param string $message
     */
	public function setMessage($message)
	{
		$this->error_message = $message;
	}

    /**
     * sets error code
     * @param int $code
     */
	public function setCode($code)
	{
		$this->error_code = $code;
	}

    /**
     * sets error data
     * @param mixed $userinfo
     */
	public function setUserInfo($userinfo)
	{
		$this->userinfo = $userinfo;
	}

    /**
     * returns error code
     * @return int
     */
	public function getCode()
	{
		return $this->error_code;
	}

    /**
     * returns error data
     * @return mixed
     */
    public function getUserInfo()
    {
        return $this->error_userinfo;
    }
}

?>