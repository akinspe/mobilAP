<?php

class mobilAP_db_default extends mobilAP_db
{
    function query($sql, $parameters=array())
    {
		return new mobilAP_query_default($sql);
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