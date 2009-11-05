<?php

class mobilAP_db_sqlite extends mobilAP_db
{
    var $db_type = 'sqlite';
    
    function db_folder()
    {
        return sprintf("%s%smobilAP%sdata", MOBILAP_BASE, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
    }

    function db_file()
    {
        return sprintf("%s%smobilAP.sqlite", mobilAP_db_sqlite::db_folder(), DIRECTORY_SEPARATOR);
    }

    function _autoincrement()
    {
        return 'AUTOINCREMENT';
    }
    
    protected function getTableDefinitions()
    {
        $tables = parent::getTableDefinitions();
        return $tables;
    }
    
	static function info()
	{
		$info = array(
			'title'=>'SQLite',
			'description'=>'An embedded database system. Requires no separate server. Only requires that the webserver can write to the mobilAP directory. Database exists as a single file that can be backed up or archived.',
            'supported'=>true,
            'supported_message'=>'Supported'
        );

        if (!class_exists('PDO')) {
            $info['supported']=false;
            $info['supported_message']="Not Supported. PHP Data Objects (PDO) class is not available on this server's PHP installation";
        } elseif (!in_array('sqlite', PDO::getAvailableDrivers())) {
            $info['supported']=false;
            $info['supported_message']="Not Supported. The SQLite driver is not available on this server's PHP installation";
        } elseif (!is_writable(mobilAP_db_sqlite::db_folder())) {
            $info['supported']=false;
            $info['supported_message']="Not Available. The webserver cannot write to " . mobilAP_db_sqlite::db_folder();
        }
        
        return $info;
	}

	function query($sql, $parameters=array())
	{
		$result = new mobilAP_query_sqlite($this->conn, $sql, $parameters);
        if ($result->isError()) {
            $errno = $result->getErrno();
            /*
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
            */

			$result = new mobilAP_Error($result->getError(), $errno, $sql);
        }
        
        return $result;
	}
    
    function __construct()
    {
        try {
            $this->conn = new PDO('sqlite:' . $this->db_file());
        } catch (Exception $error) {
            $this->conn = false;
            $this->error = new MobilAP_Error($error->getMessage(), $error->getCode(), $error);
        }
    }

}

class mobilAP_query_sqlite extends mobilAP_query
{

}

?>