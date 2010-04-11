<?php

class mobilAP_db_sqlite extends mobilAP_db
{
    var $db_type = 'sqlite';
	private $db_file;
    
    function db_folder()
    {
	    $folder = mobilAP::getDBConfig('db_folder');
	    return $folder ? $folder : sprintf("%s%smobilAP%sdata", MOBILAP_BASE, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
    }

    function db_file()
    {
        return sprintf("%s%s%s", mobilAP_db_sqlite::db_folder(), DIRECTORY_SEPARATOR, mobilAP_db_sqlite::db_filename());
    }

    function db_filename()
    {
        return "mobilAP.sqlite";
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
        }
        
        return $info;
	}

	function query($sql, $parameters=array())
	{
		$result = new mobilAP_query_sqlite($this->conn, $sql, $parameters);
        if ($result->isError()) {
            $errno = $result->getErrno();
			$result = new mobilAP_Error($result->getError(), $errno, $sql);
        }
        
        return $result;
	}
	
	public static function testConnection($db_config)
	{
		$db_folder = isset($db_config['db_folder']) ? $db_config['db_folder'] : '';
		$db_folder = $db_folder ? $db_folder : mobilAP_db_sqlite::db_folder() ;
		$db_file = sprintf("%s%s%s", $db_folder, DIRECTORY_SEPARATOR, mobilAP_db_sqlite::db_filename());

		if (!is_writable($db_folder)) {
			return new MobilAP_Error("$db_folder is not writable by the webserver");
		} elseif (file_exists($db_file)) {
			try {
				$conn = new PDO('sqlite:' . $db_file);
			} catch (Exception $error) {
				return new MobilAP_Error($error->getMessage(), $error->getCode(), $this->db_file);
			}
		}
		return true;
	}
    
    function __construct()
    {
		$this->db_file = $this->db_file();
        try {
            $this->conn = new PDO('sqlite:' . $this->db_file);
        } catch (Exception $error) {
            $this->conn = false;
            $this->error = new MobilAP_Error($error->getMessage(), $error->getCode(), $this->db_file);
        }
    }

}

class mobilAP_query_sqlite extends mobilAP_query
{

}

?>