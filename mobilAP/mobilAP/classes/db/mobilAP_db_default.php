<?php

class mobilAP_db_default extends mobilAP_db
{
    function query($sql, $parameters=array())
    {
		$result = new mobilAP_query_default($sql);
        if ($result->isError()) {
            $errno = $result->getErrno();
			$result = new mobilAP_Error($result->getError(), $errno, $sql);
        }
        
        return $result;
    }
}

class mobilAP_query_default extends mobilAP_query
{
	var $error;
	
    public function get_last_insert_id()
    {
        return false;
    }

    public function affectedRows()
    {
    	return false;
    }
    
    public function numRows()
    {
		return false;
    }
    
    public function fetchRow($fetchMode=DB_FETCHMODE_ASSOC)
    {
        return false;
    }
    
    public function free()
    {
    }
    
    function __destruct()
    {
    }

	public function __construct($sql)
	{
		$this->sql = $sql;
        $this->error = mobilAP_Error::throwError('Database not configured', 0, $sql);
	}    
}

?>