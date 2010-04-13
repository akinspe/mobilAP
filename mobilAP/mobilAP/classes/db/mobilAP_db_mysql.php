<?php

class mobilAP_db_mysql extends mobilAP_db
{
    var $db_type = 'mysql';
	private $host;
	private $user;
	private $password;
	private $database;

	static function info()
	{
		$info = array(
			'title'=>'MySQL',
			'description'=>'A client/server database system. Requires an existing MySQL server (either on this server or another system). Advantages include the ability to remotely connect to the server to query and extract data (for backup or analysis)',
            'supported'=>true,
            'supported_message'=>'Supported'
        );

        if (!class_exists('PDO')) {
            $info['supported']=false;
            $info['supported_message']="Not Supported. PHP Data Objects (PDO) class is not available on this server's PHP installation";
        } elseif (!in_array('mysql', PDO::getAvailableDrivers())) {
            $info['supported']=false;
            $info['supported_message']="Not Supported. The MySQL PDO driver is not available on this server's PHP installation";
        }
        
        return $info;
        
	}
	
    public static function deleteIndexes()
    {
    	return;
    }

    protected function getTableDefinitions()
    {
        $tables = parent::getTableDefinitions();
        return $tables;
    }
	
	function __construct()
	{
		$this->host = mobilAP::getDBConfig('db_host');
		$this->user = mobilAP::getDBConfig('db_user');
		$this->password = mobilAP::getDBConfig('db_password');
		$this->database = mobilAP::getDBConfig('db_database');
        $this->dsn = sprintf("%s:host=%s;dbname=%s", $this->db_type, $this->host, $this->database);
        $this->error = false;

        try {
            $this->conn = new PDO($this->dsn, $this->user, $this->password);
        } catch (Exception $error) {
            $this->conn = false;
            $this->error = new MobilAP_Error($error->getMessage(), $error->getCode(), $error);
        }
    }
    
    function _autoincrement()
    {
        return 'AUTO_INCREMENT';
    }
	
	public static function testConnection($db_config)
	{
		$host = isset($db_config['db_host']) ? $db_config['db_host'] :'';
		$user = isset($db_config['db_user']) ? $db_config['db_user'] :'';
		$password = isset($db_config['db_password']) ? $db_config['db_password'] :'';
		$database = isset($db_config['db_database']) ? $db_config['db_database'] :'';
				
        $dsn = sprintf("%s:host=%s;dbname=%s", 'mysql', $host, $database);
        $result = true;

        try {
            $result = new PDO($dsn, $user, $password);
        } catch (Exception $error) {
            $result = new MobilAP_Error($error->getMessage(), $error->getCode(), $error);
        }

		if (empty($database) && !mobilAP_error::isError($result)) {
			$result = new MobilAP_Error("Database cannot be empty");
		}
        
        return $result;
	}

	function query($sql, $parameters=array())
	{
		$result = new mobilAP_query_mysql($this->conn, $sql, $parameters);
        if ($result->isError()) {

            $errno = $result->getErrno();
			switch ($errno)
			{
				case 1007:
				case 1022:
				case 1050:
				case 1062:
					$errno = DB_ERROR_ALREADY_EXISTS;
					break;
                default:
			}

			$result = mobilAP_Error::throwError($result->getError(), $errno, $sql);
        }
        
        return $result;
	
	}
    
}

class mobilAP_query_mysql extends mobilAP_query
{
}

?>