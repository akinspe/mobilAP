<?php

define('DB_ERROR_ALREADY_EXISTS', -1);

define('DB_FETCHMODE_ORDERED', PDO::FETCH_NUM);
define('DB_FETCHMODE_ASSOC', PDO::FETCH_ASSOC);
define('DB_FETCHMODE_OBJECT', PDO::FETCH_OBJ);
define('MOBILAP_DB_VERSION',2);

$mobilAP_db = false;

/* database abstraction class */
class mobilAP_db
{
    protected $conn;
    protected $error;
    
    public static function conn()
    {
        global $mobilAP_db;
   		if (!$db_type = mobilAP::getDBConfig('db_type')) {
            $db_type ='default';
        }
                
   		require_once(sprintf("db/mobilAP_db_%s.php", $db_type));
        if (!$mobilAP_db) {
            $object = "mobilAP_db_$db_type";
            $mobilAP_db = new $object(mobilAP::getDBConfig('db_host'),mobilAP::getDBConfig('db_username'),mobilAP::getDBConfig('db_password'),mobilAP::getDBConfig('db_database'));
        }
   		return $mobilAP_db;
    }
    
    public static function known_db_types()
    {
        return array(
            'mysql',
            'sqlite'
        );
    }
    
    public static function getDBType()
    {
        if (!$db_type = mobilAP::getDBConfig('db_type')) {
            $db_type = 'default';
        }
        
        return $db_type;
    }
    
    protected function getTableDefinitions()
    {
        $tables = array(
            'announcements'=>sprintf("CREATE TABLE IF NOT EXISTS announcements (announcement_id INTEGER PRIMARY KEY %s, announcement_title varchar(50),announcement_timestamp int(11), userID char(32),announcement_text text)", $this->_autoincrement()),
            'announcements_read'=>"CREATE TABLE IF NOT EXISTS announcements_read (announcement_id INTEGER NOT NULL DEFAULT '0',userID char(32) NOT NULL DEFAULT '',read_timestamp int(11) DEFAULT NULL,PRIMARY KEY (userID,announcement_id))",
            'evaluation_question_responses'=>"CREATE TABLE IF NOT EXISTS evaluation_question_responses (question_index tinyint(4)  NOT NULL DEFAULT '0',response_index tinyint(4)  NOT NULL DEFAULT '0', response_text varchar(50) DEFAULT NULL,response_value smallint(6) DEFAULT NULL, PRIMARY KEY (question_index,response_index))",
            'evaluation_questions'=>"CREATE TABLE IF NOT EXISTS evaluation_questions (question_index tinyint(4)  NOT NULL DEFAULT '0', question_text varchar(100) DEFAULT NULL,question_response_type char(1) DEFAULT NULL, PRIMARY KEY (question_index))",
            'login_cookies'=>"CREATE TABLE IF NOT EXISTS login_cookies (userID char(32) NOT NULL DEFAULT '',token char(32) NOT NULL DEFAULT '', timestamp datetime DEFAULT NULL, expires datetime DEFAULT NULL, PRIMARY KEY (userID,token))",
            'mobilAP_config'=>"CREATE TABLE IF NOT EXISTS mobilAP_config (config_var varchar(32) NOT NULL DEFAULT '',config_type char(1) DEFAULT NULL,config_value varchar(100) DEFAULT NULL, PRIMARY KEY  (config_var))",
            'mobilAP_serials'=>"CREATE TABLE IF NOT EXISTS mobilAP_serials (serial_var varchar(32) NOT NULL DEFAULT '',serial_value int(11) DEFAULT NULL, PRIMARY KEY  (serial_var))",
            'mobilAP_users'=>"CREATE TABLE IF NOT EXISTS mobilAP_users (userID char(32) NOT NULL DEFAULT '', FirstName varchar(50) DEFAULT NULL,LastName varchar(50) DEFAULT NULL, email varchar(50) DEFAULT NULL,
  md5 char(32) DEFAULT NULL,directory_active tinyint(1) NOT NULL DEFAULT '-1', admin tinyint(1) NOT NULL DEFAULT '0', organization char(50) DEFAULT NULL, PRIMARY KEY (userID),UNIQUE (email))",
            'poll_answers'=>sprintf("CREATE TABLE IF NOT EXISTS poll_answers (answer_id INTEGER PRIMARY KEY %s,question_id int(11)  DEFAULT NULL,response_value smallint(5)  DEFAULT NULL,response_timestamp int(11) DEFAULT NULL,response_userID char(32) DEFAULT NULL)", $this->_autoincrement()),
            'poll_questions'=>sprintf("CREATE TABLE IF NOT EXISTS poll_questions (question_id INTEGER PRIMARY KEY %s,session_id int(10)  DEFAULT NULL,question_index smallint(6)  NOT NULL DEFAULT '0',question_text varchar(200) DEFAULT NULL,question_minchoices tinyint(4)  NOT NULL DEFAULT '0',question_maxchoices tinyint(4)  NOT NULL DEFAULT '0',response_type char(1) DEFAULT NULL,chart_type char(3) DEFAULT NULL,question_active tinyint(1) NOT NULL DEFAULT '-1',question_list_text varchar(50) DEFAULT NULL,UNIQUE (session_id,question_index))", $this->_autoincrement()),
            'poll_responses'=>"CREATE TABLE IF NOT EXISTS poll_responses (question_id int(11) NOT NULL DEFAULT '0',response_index smallint(6)  NOT NULL DEFAULT '0',response_value smallint(6)  NOT NULL DEFAULT '0',response_text varchar(200) DEFAULT NULL,PRIMARY KEY (question_id,response_index),UNIQUE (question_id,response_value),UNIQUE  (question_id,response_text))",
            'schedule'=>sprintf("CREATE TABLE IF NOT EXISTS schedule (schedule_id INTEGER PRIMARY KEY %s,schedule_type char(1) DEFAULT NULL,start_time datetime DEFAULT NULL,end_time datetime DEFAULT NULL,detail varchar(100) DEFAULT NULL,room char(32) DEFAULT NULL,session_id int(11) DEFAULT NULL,session_group_id int(11) DEFAULT NULL)", $this->_autoincrement()),
            'session_discussion'=>sprintf("CREATE TABLE IF NOT EXISTS session_discussion (post_id INTEGER PRIMARY KEY %s,session_id int(11) DEFAULT NULL,post_timestamp int(11) DEFAULT NULL,post_user char(32) DEFAULT NULL,post_text text)", $this->_autoincrement()),
            'session_evaluations'=>sprintf("CREATE TABLE IF NOT EXISTS session_evaluations (evaluation_id INTEGER PRIMARY KEY %s,session_id int(11) DEFAULT NULL,post_user char(32) DEFAULT NULL,post_timestamp int(11))", $this->_autoincrement()),
            'session_evaluation_answers'=>"CREATE TABLE IF NOT EXISTS session_evaluation_answers (evaluation_id INTEGER, question_index INTEGER, question_answer TEXT)",
            "session_groups"=>sprintf("CREATE TABLE IF NOT EXISTS session_groups (session_group_id INTEGER PRIMARY KEY %s,session_group_title varchar(100) DEFAULT NULL,session_group_detail varchar(100) DEFAULT NULL)", $this->_autoincrement()),
            'session_links'=>sprintf("CREATE TABLE IF NOT EXISTS session_links (link_id INTEGER PRIMARY KEY %s,session_id int(11) DEFAULT NULL,link_url varchar(200) DEFAULT NULL,link_text varchar(150) DEFAULT NULL,post_user char(32) DEFAULT NULL,link_type char(1) DEFAULT NULL,post_timestamp int(11) DEFAULT NULL)", $this->_autoincrement()),
            'session_presenters'=>"CREATE TABLE IF NOT EXISTS session_presenters (session_id int(11) NOT NULL DEFAULT '0',presenter_id char(32) NOT NULL DEFAULT '',presenter_index tinyint(4)  DEFAULT NULL,PRIMARY KEY (session_id,presenter_id))",
            'sessions'=>sprintf("CREATE TABLE IF NOT EXISTS sessions (session_id INTEGER PRIMARY KEY %s,session_title varchar(100) DEFAULT NULL,session_description text,session_flags int(10)  NOT NULL DEFAULT '15')", $this->_autoincrement())
        );
        
        return $tables;
    }
    
    public static function createTables()
    {
        if ($table_version = mobilAP::getConfig('DB_VERSION')) {
            if ($table_version != MOBILAP_DB_VERSION) {
                return new mobilAP_Error('Database tables already exist');
            } else {
                return true;
            }
        }
        
        $conn = mobilAP_db::conn();
        $definitions = $conn->getTableDefinitions();
        foreach ($definitions as $table=>$sql) {
            $result = mobilAP::query($sql);
            if (mobilAP_Error::isError($result)) {
                return new mobilAP_Error("Error creating table $table", 1, $result);
                mobilAP_db::deleteTables();
            }
        }
        mobilAP::setConfig('DB_VERSION', MOBILAP_DB_VERSION, 'I');
        mobilAP::setDBConfig('DB_VERSION', MOBILAP_DB_VERSION);
        mobilAP::setDefaultConfigs();
        return true;
    }
    
    public static function deleteTables()
    {
        $conn = mobilAP_db::conn();
        $definitions = $conn->getTableDefinitions();
        foreach ($definitions as $table=>$create_sql) {
            $sql = "DROP TABLE IF EXISTS $table";
            mobilAP::query($sql);
        }
        
        return true;
    }
    
    public function isError()
    {
        return mobilAP_Error::isError($this->error);
    }
    
    public static function testConnection($db_type, $db_host, $db_username, $db_password, $db_database)
    {
        if (!in_array($db_type, mobilAP_db::known_db_types())) {
            return mobilAP_Error::throwError("Unknown database type $db_type");
        }
        
        require_once(sprintf("db/mobilAP_db_%s.php", $db_type));
   		$object = "mobilAP_db_$db_type";
   		$db = new $object($db_host, $db_username, $db_password, $db_database);
        return $db->isError() ? $db->error : true;
    }
    
    public static function db_types()
    {
        $db_types = array();
        foreach (mobilAP_db::known_db_types() as $db_type) {
            require_once(sprintf("db/mobilAP_db_%s.php", $db_type));
            $class = "mobilAP_db_" . $db_type;
            $db_types[$db_type] = call_user_func(array($class,'info'));
        }
        
        return $db_types;
    }
	
}

class mobilAP_query
{
	protected $conn;
	protected $result;
	public $sql;
	public $error;
	
	public function getError()
	{
		return $this->isError() ? $this->error->getMessage() : false;
	}

	public function getErrno()
	{
		return $this->isError() ? $this->error->getCode() : false;
	}
	
	public function isError()
	{
		return mobilAP_Error::isError($this->error);
	}

    public function get_last_insert_id()
    {
        return $this->conn->lastInsertId();
    }

    public function affectedRows()
    {
        if (!$this->result) {
            throw new Exception("Result not valid");
        }
        
        return $this->result->rowCount();
    }

    public function fetchColumn($index=0) 
    {
        if (!$this->result) {
            throw new Exception("Result not valid");
        }
        
        return $this->result->fetchColumn($index);
    }

    public function fetchRow($fetchMode=DB_FETCHMODE_ASSOC)
    {
        if (!$this->result) {
            throw new Exception("Result not valid");
        }
    	switch ($fetchMode)
    	{
    		case DB_FETCHMODE_ORDERED:
    			return $this->result->fetch(PDO::FETCH_NUM);
    		
    		case DB_FETCHMODE_ASSOC:
    			return $this->result->fetch(PDO::FETCH_ASSOC);

    		case DB_FETCHMODE_OBJECT:
    			return $this->result->fetch(PDO::FETCH_OBJ);
    		default:
    			return false;
    	}
    }
    
	public function __construct($conn, $sql, $parameters)
	{
        $this->conn = $conn;
		$this->sql = $sql;
        $this->parameters = is_array($parameters) ? $parameters : array();
        $this->result = false;
        $this->error = false;

        if (!$this->conn) {
            $this->error = new MobilAP_Error("No connection", 1);
            return;
        }

        if (!$this->result = $this->conn->prepare($sql)) {
            $errorInfo = $this->conn->errorInfo();
            $this->error = new MobilAP_Error($errorInfo[2], $errorInfo[1], $sql);
            if (mobilAP::getDBConfig('DB_VERSION')) {
                error_log(sprintf("Error with %s: %s", $sql, $errorInfo[2]));
            }
            return;
        }

        $this->result->setFetchMode(PDO::FETCH_ASSOC);
        
        if (!$this->result->execute($parameters)) {
            $errorInfo = $this->result->errorInfo();
            $this->error = new MobilAP_Error($errorInfo[2], $errorInfo[1], $sql);
            if (mobilAP::getDBConfig('DB_VERSION')) {
                error_log(sprintf("Error with %s: %s", $sql, $errorInfo[2]));
            }
        }
        
        return true;
	}
	
}

?>