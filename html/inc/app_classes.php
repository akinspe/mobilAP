<?php

/*

* Copyright (c) 2008, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('utils.php');
require_once('model_classes.php');

$PAGE = '';

class Application
{
    public $messages = array();
    public $SCRIPT_NAME='';
    private $webUser = null;
    private $user = null;
    
    
    function __construct()
    {
        $this->SCRIPT_NAME = $_SERVER['SCRIPT_NAME'];
        $this->webUser = $this->getWebUser();
    }

    public function addErrorMessage($string)
    {
    	return $this->addMessage($string, 'error');
    }
    
    public function getUserID()
    {
    	$webUser = $this->getWebUser();
    	return $webUser->getUserID();
    }

    public function getUserToken()
    {
    	if ($user = $this->getUser()) {
			return $user->getUserID();
		}
    }
    
    public function is_loggedIn()
    {
    	$webUser = $this->getWebUser();
    	return $webUser->is_loggedIn();
    }
    
    public function getWebUser()
    {
    	if (!$this->webUser) {
			$this->webUser = new mobilAP_webuser();
		}
		
        return $this->webUser;
    }
    
    public function getUser()
    {
    	if (!$this->user) {
			$webuser = $this->getWebUser();
			$this->user = $webuser->getUser();
		}
		
		return $this->user;
    }
    
        
    public function addMessage($string, $message_type='message')
    {
        switch ($message_type)
        {
            case 'message':
            case 'error':
                break;
            default:
            	return mobilAP_Error::throwError("Inavlid message type $message_type");
        }

		if (is_array($string)) {
			foreach ($string as $s) {
				$this->messages[$message_type][] = $s;
			}
		} else {
			$this->messages[$message_type][] = $string;
        }
        
    }

    public function getMessages()
    {	
    	$message_text = '<div id="message_container">';
    	
    	foreach ($this->messages as $message_type=>$messages) {
    		$message_text .= "<div class=\"". $message_type . "\">" . 
				implode('<br />', $messages) .
				"</div>";
    	}
    	
    	$message_text .= '</div>';
    	return $message_text;
    	
    }

}

?>