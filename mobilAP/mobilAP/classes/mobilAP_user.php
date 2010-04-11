<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

class mobilAP_User
{
    const USER_ROOT_USER='mobilAP@mobilAP.daap.uc.edu';
    const USER_ROOT_USERID='00000000000000000000000000000000';
	const USER_TABLE='mobilAP_users';
	public $userID;
	public $email;
	public $FirstName;
	public $LastName;
    public $admin=false;
    public $organization;
    public $imageURL;
    public $imageThumbURL;
	
    public function __construct($loadSession=false)
    {
    	if ($loadSession) {
    		$this->loadFromSession();
    	}
    }

    public function getUserID()
    {
		return $this->userID;
    }

    public function setUserID($userID)
    {
		$this->userID = $userID;
    }

	public function updateSerial($updateAllUsers=false)
	{
		mobilAP::setSerialValue($this->userID,mobilAP::SERIAL_TYPE_USER);
		if ($updateAllUsers) {
			mobilAP::setSerialValue('users');
		}
	}
	
	private static function getNextUserID()
	{
		$userID = md5(uniqid(rand(), true));
	
		// to be really sure we need to check if it's already there
		$sql = sprintf("SELECT count(*) FROM %s WHERE userID=?", mobilAP_user::USER_TABLE);
        
		$result = mobilAP::query($sql, array($userID));
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
	
		if ($result->fetchColumn()) {
			return mobilAP_User::getNextUserID();
		}
	
		return $userID;		
	}
    
    public function setEmail($email) 
    {
    	if (mobilAP_utils::is_validEmail($email)) {
			$this->email = $email;
			return true;
		} else {
			return false;
		}
    }
    
    public function setName($FirstName, $LastName)
    {
    	$this->FirstName = trim($FirstName);
    	$this->LastName = trim($LastName);
    }
    
    public function setOrganization($organization) 
    {
        $this->organization = trim($organization);
    }
    
    public function getFullName()
    {
		return sprintf("%s %s", $this->FirstName, $this->LastName);
	}
	
	public static function getUserIDFromEmail($email)
	{
    	$sql = sprintf("SELECT userID FROM %s WHERE email=?", 
    					mobilAP_user::USER_TABLE);
    	$result = mobilAP::query($sql, array($email));
		if (mobilAP_Error::isError($result)) {
			return false;
		}
    	
    	if ($row = $result->fetchRow()) {
    		return $row['userID'];
    	} 
    	
    	return false;
	}
	
	public static function getUserByID($userID)
	{
		$user = new mobilAP_User();

    	if (mobilAP_utils::is_validEmail($userID)) {
    		$userID = mobilAP_user::getUserIDFromEmail($userID);
		}

		$user->setUserID($userID);
		if (!$user->loadData()) {
			$user = false;
		}
		
		return $user;
	}
	
	public function getUserData()
	{
		require_once('mobilAP_session.php');
		//initialize
		$session_data = array('questions'=>array(), 'evaluation'=>false);
		$data = array('sessions'=>array(),'announcements'=>array());

		$sql = sprintf("SELECT session_id FROM %s", mobilAP_session::SESSION_TABLE);
		$result = mobilAP::query($sql);		
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		while ($row = $result->fetchRow()) {
			$data['sessions'][$row['session_id']] = $session_data;
		}

		//populate questions
		$sql = sprintf("SELECT session_id,question_id FROM %s",
					mobilAP_session::POLL_QUESTIONS_TABLE);
		$result = mobilAP::query($sql);
		while ($row = $result->fetchRow()) {
			$data['sessions'][$row['session_id']]['questions'][$row['question_id']] = 0;
		}
		
		if ($this->getUserID()) {
			$sql = sprintf("SELECT session_id FROM %s WHERE post_user=?", mobilAP_session::SESSION_EVALUATION_TABLE);
			$params = array($this->getUserID());
			$result = mobilAP::query($sql,$params);
			while ($row = $result->fetchRow()) {
				$data['sessions'][$row['session_id']]['evaluation'] = true;
			}
			
			//get their question responses
			$sql = sprintf("SELECT q.session_id,a.* FROM %s a 
							LEFT JOIN %s q USING (question_id) 
							WHERE a.response_userID=?",
						mobilAP_session::POLL_ANSWERS_TABLE, 
						mobilAP_session::POLL_QUESTIONS_TABLE);
	
			$params = array($this->getUserID());
			$result = mobilAP::query($sql, $params);
	
			while ($row = $result->fetchRow()) {
				$data['sessions'][$row['session_id']]['questions'][$row['question_id']] = intval($row['answer_id']);
			}
		}

		require_once('mobilAP_announcement.php');
		//announcements
		$sql = sprintf("SELECT announcement_id FROM %s", mobilAP_announcement::ANNOUNCEMENT_TABLE);
		$result = mobilAP::query($sql);		
		while ($row = $result->fetchRow()) {
			$data['announcements'][$row['announcement_id']] = false;
		}

		if ($this->getUserID()) {
			//get their read announcements
			$sql = sprintf("SELECT announcement_id FROM %s a 
							WHERE userID=?",
						mobilAP_announcement::ANNOUNCEMENT_READ_TABLE);
	
			$params = array($this->getUserID());
			$result = mobilAP::query($sql, $params);
	
			while ($row = $result->fetchRow()) {
				$data['announcements'][$row['announcement_id']] = true;
			}
		}

		return $data;
	}

    function uploadImportFile($file)
    {
		ini_set('auto_detect_line_endings', 1);
		$file_keys = array('name','type','tmp_name','error','size');
		$fileName = isset($file['tmp_name']) ? $file['tmp_name'] : '';
		$delimiter = "\t";
        
        if (is_uploaded_file($fileName)) {
			$field_count = 4;
			$users = array();

			if (!$handle = fopen($fileName, "r")) {
				return mobilAP_Error::throwError("Error reading $fileName");
			}
			
			while ( ($line = fgetcsv($handle, 0, $delimiter)) !== FALSE)
			{
				if (count($line)==$field_count) {
                    $user = new mobilAP_User();
                    $user->setName($line[0], $line[1]);
                    $user->setEmail($line[2]);
                    $user->setOrganization($line[3]);
                    if ($user->validUser() && !mobilAP_user::getUserIDFromEmail($user->email)) {
                        $users[] = $user;
                    }
				}
			}
			
			fclose($handle);	
			return $users;
		}
        
        return new mobilAP_Error("File not uploaded");
    }
    
    public function loadFromSession()
    {
    	$userSession = new mobilAP_UserSession();
    	
    	if ($userSession->loggedIn()) {
            $userID = $userSession->getUserID();
    		$this->setUserID($userID);
			if (!$this->loadData()) {
				$this->userID = null;
				trigger_error("User $userID not found", E_USER_WARNING);
				return mobilAP_Error::throwError("User $userID not found");
			}
		}
    }
    
    public function validUser()
    {
		if (!mobilAP_utils::is_validEmail($this->email)) {
			return false;
		} elseif (strlen($this->FirstName)==0 || strlen($this->LastName)==0) {
			return false;
        }
        
        return true;
    }

    public function addUser($admin_userID)
    {
		if (mobilAP::getConfig('ALLOW_SELF_CREATED_USERS')) {
			// allow people to create new accounts
		} elseif (!mobilAP::getConfig('setupcomplete')) {
			// allow the initial account
		} elseif (!$user = mobilAP_user::getUserById($admin_userID)) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		} elseif (!$user->isSiteAdmin()) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		}

		if (!mobilAP_utils::is_validEmail($this->email)) {
			return mobilAP_Error::throwError(empty($this->email) ? "Email cannot be blank" : "Invalid email: $this->email");
		} elseif (strlen($this->FirstName)==0 || strlen($this->LastName)==0) {
			return mobilAP_Error::throwError("Name cannot be blank");
		} elseif ($userID = mobilAP_user::getUserIDFromEmail($this->email)) {
			return mobilAP_Error::throwError("User already exists for email $this->email");
		}

		$this->userID = mobilAP_user::getNextUserID();
		
		$sql = sprintf("INSERT INTO %s
				(userID, email, FirstName, LastName, admin, organization)
				VALUES
				(?,?,?,?,?,?)", 
				mobilAP_user::USER_TABLE);
		
        $params = array($this->userID,$this->email,$this->FirstName,$this->LastName, $this->admin ? -1 : 0,$this->organization);

		$result = mobilAP::query($sql, $params);
		if (!mobilAP_Error::isError($result)) {
            $this->resetPassword();
            $result = true;
        }
		
		$this->updateSerial(true);
		return $result;
    }
    
    public function updateUser($admin_userID)
    {
		if (!$user = mobilAP_user::getUserById($admin_userID)) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		} elseif (!$user->isSiteAdmin() && $user->getUserID() != $this->getUserID()) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		}

    	$sql = sprintf("UPDATE %s SET
    		FirstName=?, 
    		LastName=?, 
    		email=?,
            organization=?,
            admin=?
			WHERE userID=?", 
			mobilAP_user::USER_TABLE);
		$params = array(
			$this->FirstName,
			$this->LastName,
			$this->email,
			$this->organization,
            $this->admin ? -1 : 0,
			$this->getUserID());
    	$result = mobilAP::query($sql,$params);
		$this->updateSerial(true);
		return mobilAP_Error::isError($result) ? $result : true;
    }
    
    public function deleteUser($admin_userID)
    {
		if (!$user = mobilAP_user::getUserById($admin_userID)) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		} elseif (!$user->isSiteAdmin()) {
			return mobilAP_Error::throwError("Unauthorized", mobilAP_UserSession::USER_UNAUTHORIZED);
		}

        require_once('mobilAP_session.php');
		$this->deleteDirectoryImage();		

		$sql = sprintf("DELETE FROM %s
				WHERE response_userID=?",
				mobilAP_session::POLL_ANSWERS_TABLE);
        $params = array($this->getUserID());
		$result = mobilAP::query($sql,$params);
		
		$sessions = mobilAP_session::getSessionsForUser($this->getUserID());
		foreach ($sessions as $session) {
            $session->removePresenter($this->getUserID());
		}		
		
		$tables = array(
			mobilAP_session::SESSION_DISCUSSION_TABLE, 
			mobilAP_session::SESSION_LINK_TABLE, 
			mobilAP_session::SESSION_EVALUATION_TABLE
		);
		foreach ($tables as $table) {

			$sql = sprintf("DELETE FROM %s
					WHERE post_user=?",
					$table);
            $params = array($this->getUserID());
			$result = mobilAP::query($sql,$params);
		}		

		$tables = array(
			mobilAP_User::USER_TABLE,
			mobilAP_UserSession::COOKIE_TABLE
		);

		foreach ($tables as $table) {
            $sql = sprintf("DELETE FROM %s
                    WHERE userID=?",
                    $table);
            $params = array($this->getUserID());
            $result = mobilAP::query($sql,$params);
        }
        
		mobilAP::setSerialValue('users');
		mobilAP::purgeSerialValue($this->getUserID());
		
        return true;
    }
    
    public function loadData($row=null)
    {
        if ($this->getUserID()==mobilAP_User::USER_ROOT_USERID) {
    		$this->setName('MobilAP', 'Root User');
    		$this->setEmail(mobilAP_User::USER_ROOT_USER);
            $this->setAdmin(true);
            return true;
        }
        
        if (!$this->getUserID()) {
        	return false;
        }
        
    	if (!is_array($row)){
			$sql = sprintf("SELECT * FROM %s WHERE userID=?", 
							mobilAP_user::USER_TABLE);
			$result = mobilAP::query($sql, array($this->getUserID()));
			if (mobilAP_Error::isError($result)) {
				return false;
			}
			$row = $result->fetchRow();
		}
    	
    	if ($row) {
    		$this->setUserID($row['userID']);
    		$this->setName($row['FirstName'], $row['LastName']);
    		$this->setEmail($row['email']);
            $this->setAdmin($row['admin']);
            $this->organization = $row['organization'];
            $this->imageURL = $this->getPhotoURL();
            $this->imageThumbURL = $this->getPhotoThumbURL();
    		return true;
    	} 
    	
    	return false;
    }
    
    public function isSiteAdmin()
    {
        return $this->admin;
    }
    
    public function isSessionAdmin()
    {
        require_once('mobilAP_session.php');
        $sessions = mobilAP_Session::getSessionsForUser($this->getUserID());
        return count($sessions)>0;
    }
    
    public function setAdmin($admin) 
    {
        $this->admin = $admin ? true : false;
    }

    function setMD5Password($md5_password)
    {
        if (!preg_match("/^[a-z0-9]{32}$/", $md5_password)) {
            return false;
        }
        
		$sql = sprintf("UPDATE %s SET md5=? WHERE userID=?", mobilAP_user::USER_TABLE);
        $params = array($md5_password, $this->getUserID());
		$result = mobilAP::query($sql, $params);
		return mobilAP_Error::isError($result) ? $result : true;
    }
	
	function setPassword($password) 
	{
        return $this->setMD5Password(md5($password));
	}

	function resetPassword()
	{
        return $this->setMD5Password(md5($this->email));
	}

	public static function getUsers($args=null)
	{
		$order = 'LastName,FirstName';
		$args = is_array($args) ? $args : array();
		$where = array();
        $params = array();
		$quick = false;
		$only_active = true;
		
		foreach ($args as $arg=>$value) 
		{
			switch ($arg)
			{
                case 'search':
                    if ($arg) {
                        $where[] = "(FirstName LIKE ? OR LastName LIKE ? OR email LIKE ?)";
                        $params[] = "%$value%";
                        $params[] = "%$value%";
                        $params[] = "%$value%";
                    }
                    break;
				case 'letter':
					if (preg_match("/^[a-zA-Z]$/", $value)) {
						$where[] = "UPPER(SUBSTR(LastName,1,1))=?";
                        $params[] = strtoupper($value);
					} else {
						return array();
					}
					break;
				case 'order':
					$$arg = $value;
					break;
				case 'only_active':
				case 'quick':
					$$arg = $value ? true : false;
					break;
			}
		}
		
		if ($order !='LastName') {
			$order .=',LastName,FirstName';
		}
		
		if ($only_active) {
			$where[] = 'directory_active';
		}
		
		
		$sql = "SELECT * FROM  " . mobilAP_user::USER_TABLE;
		
		if (count($where)>0) {
			$sql .= " WHERE " . implode(" AND ", $where);
		}
		
		$sql .= " ORDER BY $order";
		$users = array();

		$result = mobilAP::query($sql, $params);
		if (mobilAP_Error::isError($result)) {
			return $users;
		}
		
		while ($row = $result->fetchRow()) {
			$user = new mobilAP_User();
			$user->setUserID($row['userID']);
			$user->loadData($row);
			$users[] = $user;
		}
		
		return $users;
	}
    
	public function getImageURL()
	{
        return '';
		if (is_file($this->getPhotoThumb())) {
			return sprintf("directory_images/%s.jpg", $this->getUserToken());
		} elseif (getConfig('SHOW_AD_PLACEHOLDER')) {
			return 'Images/directory_default.png';
		} else {
			return false;
		}
	}
	
	public function deleteDirectoryImage()
	{
		@unlink($this->getPhotoThumb());
		@unlink($this->getPhotoFile());
		$this->updateSerial();
	}
	
	/* this probably needs to be a configuration setting */
	public function getPhotoDir()
	{
		$photo_dir = sprintf('%s%smobilAP%sdata%sdirectory', MOBILAP_BASE, DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR);
		return $photo_dir;
	}

	public function getPhotoURL()
    {
        return is_file($this->getPhotoFile()) ? 
            sprintf("%s/mobilAP/data/directory/%s_orig.jpg", MOBILAP_URL_BASE, $this->getUserID()) :
            '';
    }

	public function getPhotoThumbURL()
    {
        return is_file($this->getPhotoThumb()) ? 
            sprintf("%s/mobilAP/data/directory/%s.jpg", MOBILAP_URL_BASE, $this->getUserID()) : '';
    }
    
	public function getPhotoFile()
	{
		$photo_file = sprintf("%s%s%s_orig.jpg", $this->getPhotoDir(), DIRECTORY_SEPARATOR, $this->getUserID());
		return $photo_file;
	}

	public function getPhotoThumb()
	{
		$photo_file = sprintf("%s%s%s.jpg", $this->getPhotoDir(),  DIRECTORY_SEPARATOR, $this->getUserID() );
		return $photo_file;
	}

	function getThumbSize()
	{	
		if (!file_exists($this->getPhotoFile())) {
			return mobilAP::throwError("Error locating original file");
		}

		if (!$imageData = getimagesize($this->getPhotoFile())) {
			return mobilAP_Error::throwError("Error processing JPEG file");
		}
		
		$BaseWidth = $imageData[0];
		$BaseHeight = $imageData[1];
		$width = mobilAP::getConfig('THUMB_WIDTH');
		$height = mobilAP::getConfig('THUMB_HEIGHT');
		
		$aspect = $BaseWidth / $BaseHeight;
		$boxAspect = $width / $height;
		
		if ($aspect>=$boxAspect) {
			$newWidth = $width;
			$newHeight = floor($width / $aspect);
		} else {
			$newHeight = $height;
			$newWidth = floor ($height * $aspect);
		}
		
		if (
			($newWidth >= $BaseWidth) &&
			($newHeight >= $BaseHeight)
		   ) 
		{
			$newWidth = $BaseWidth;
			$newHeight = $BaseHeight;
		}
		
		return array($newWidth, $newHeight);
	}

	function rotatePhoto($deg)
	{
		if (!is_numeric($deg)) {
			return DAAP::raiseError("Invalid rotation $deg");
		}

		$files = array($this->getPhotoFile(), $this->getPhotoThumb());
		foreach($files  as $fileName) {
		
			if (!getConfig('use_gd') && file_exists('/usr/bin/sips')) {
				$options = array(
					"-r $deg"
				);
		
				$exec = sprintf("%s %s %s", '/usr/bin/sips', implode(' ', $options), escapeshellarg($fileName));
				$result = exec($exec, $output, $retVal);
		
				if ($retVal != 0) {
					return DAAP::raiseError("Error rotating image $retVal");
				}
			} else {
				// try GD
				if (!function_exists('ImageCreateFromJPEG')) {
					return mobilAP::throwError("GD Functions not available");
				}
			
				$im = ImageCreateFromJPEG($fileName);
				$rotate = imagerotate($im, $deg*-1, 0);	
				
				// Write image
				if (!ImageJPEG($rotate, $fileName)) {
					return mobilAP_Error::throwError("Error rotating image");
				}
				
			}
			$this->updateSerial();
		}
	}

	private function createPhotoThumb() 
	{
		if (!file_exists($this->getPhotoFile())) {
			return mobilAP::throwError("Error locating original file");
		}
				
		// Remove current thumb
		if (file_exists($this->getPhotoThumb())) {
			unlink($this->getPhotoThumb());
		}
		
		list($image_width, $image_height) = getimagesize($this->getPhotoFile());
		list($thumb_width, $thumb_height) = $this->getThumbSize();
		
		// use SIPS if it's available. GD functions are not part of default OS X install, but SIPS is
		if (file_exists('/usr/bin/sips')) {
		
			$options = array(
				"-z {$thumb_height} {$thumb_width}",
				"-s format JPEG"
			);
		
			$exec = sprintf("%s %s %s --out %s", '/usr/bin/sips', implode(' ', $options), escapeshellarg($this->getPhotoFile()), escapeshellarg($this->getPhotoThumb()));
			$result = exec($exec . " 2>&1", $output, $retVal);
		
			if ($retVal!==0) {
				return mobilAP_Error::throwError("Error creating thumbnail");
			}
		
			$this->updateSerial();
			return true;
		} else {
			// try GD
			if (!function_exists('ImageCreateFromJPEG')) {
				return mobilAP::throwError("GD Functions not available");
			}
		
			$im = ImageCreateFromJPEG($this->getPhotoFile());
			
			// Create thumb canvas
			$thumb = ImageCreateTrueColor($thumb_width, $thumb_height);
			
			// Resize image onto thumb canvas
			ImageCopyResampled($thumb, $im, 0, 0, 0, 0, $thumb_width, $thumb_height, $image_width, $image_height);
			
			// Write image
			if (!ImageJPEG($thumb, $this->getPhotoThumb())) {
				return mobilAP_Error::throwError("Error creating thumbnail");
			}
			
			$this->updateSerial();
			return true;
		}
        
        return false;
	}
    
    public function uploadImage($file)
	{
		$file_keys = array('name','type','tmp_name','error','size');
        
        if (!is_dir($this->getPhotoDir())) {
            if (!mkdir($this->getPhotoDir())) {
				return mobilAP_Error::throwError("Error creating photo directory", -1, $this->getPhotoDir());
            }
        }
		
        //make sure the array is a valid php upload array
        if (isset($file['tmp_name']) && isset($file['type'])) {

			if (is_uploaded_file($file['tmp_name'])) {
				if ($file['type'] != 'image/jpeg') {
					return mobilAP_Error::throwError("Image must be JPEG");
				}
				
				if (!move_uploaded_file($file['tmp_name'], $this->getPhotoFile())) {
					return mobilAP_Error::throwError("Error saving file");
				}
	
				chmod($this->getPhotoFile(), 0644);
				$this->fixPhotoOrientation($this->getPhotoFile());
				$result = $this->createPhotoThumb();
				$this->updateSerial();
				return $result;
			}
    
		}
        
        return false;
    }
    
    /*  attempt to fix orientations from photos taken containing EXIF orientation information. It should
    	prevent photos from displaying incorrectly in browsers that do not propery parse EXIF orientation information
    	This is a optimistic function. If it fails it's non-fatal
	*/
    public function fixPhotoOrientation($file)
    {
		if (!function_exists('exif_read_data')) {
			return; // no exif functions
		}

		if (!function_exists('ImageCreateFromJPEG')) {
			return; //no gd functions
		}

		if (!is_file($file)) {
			return; //file not there
		}

		if (!$data = @exif_read_data($file)) {
			return; // no exif data
		}
		
		if (!isset($data['Orientation'])) {
			return; // no orientation data
		}

		switch ($data['Orientation'])
		{
			case 1:
				return;
				break;
			case 3:
				$deg = 180;
				break;
			case 6:
				$deg = 90;
				break;
			case 8:
				$deg = -90;
				break;
			default:
				return; // image flipped. we're not handling that now
				break;				
		}
		
		$im = ImageCreateFromJPEG($file);
		$rotate = imagerotate($im, $deg*-1, 0);	
		ImageJPEG($rotate, $file);
		
		return;
	}
    
    
	/* THIS FUNCTION COULD BE REWRITTEN TO HANDLE AUTHORIZATION. IT SHOULD RETURN true or false */
    static function Auth($id, $password)
    {
		$field = 'attendee_id';

		if (mobilAP_utils::is_validEmail($id)) {
			$field = 'email';
		} elseif (!preg_match("/^[a-z0-9]{32}$/" ,$id)) {
			return false;
		}

		$id = strtolower($id);
		
    	$where = array(
    		sprintf("%s=?", $field)
    	);
    	$params = array($id);
    	
    	if (mobilAP::getConfig('USE_PASSWORDS')) {
    		$where[] = "md5=?";
    		$params[] = md5($password);
    	} else {
    	
			if (mobilAP::getConfig('USE_ADMIN_PASSWORDS')) {
				$where[] = "(not admin OR md5=?)";
				$params[] = md5($password);
			}
			
			if (mobilAP::getConfig('USE_PRESENTER_PASSWORDS')) {
                require_once('mobilAP_session.php');
				$where[] = sprintf("(md5=? OR (userID NOT IN (SELECT presenter_id FROM %s)))",
					mobilAP_session::SESSION_PRESENTER_TABLE);
				$params[] = md5($password);
			}
		}
    	
		$sql = sprintf("SELECT userID FROM %s
				WHERE %s", mobilAP_User::USER_TABLE, implode(" AND ", $where));
		
		$result = mobilAP::query($sql,$params);
        if (mobilAP_Error::isError($result)) {
            return false;
        } 
        return $result->fetchRow() ? true : false;
    }
}

class mobilAP_UserSession
{
	const USER_NOT_FOUND=-1;
	const USER_ALREADY_LOGGED_IN=-2;
	const USER_LOGIN_FAILURE=-3;
	const USER_REQUIRES_PASSWORD=-4;
	const USER_ADMIN_LOGIN_FAILURE=-5;
	const USER_CREATE_NEW_USER=-6;
	const USER_UNAUTHORIZED=-7;
	const LOGIN_COOKIE_LENGTH=1209600; //2 weeks
	const COOKIE_TABLE='login_cookies';
	public $userID;
	public $cookie_path='';
	public $login_token;

    public function loggedIn()
    {
        return $this->getUserID() ? true : false;
    }   
    
    public function getUserID()
    {
		return $this->userID;
    }

    public function getUser()
    {
        return mobilAP_user::getUserByID($this->getUserID());
    }

    public function login($userID, $pword)
    {
        $userID = strtolower($userID);
        if ($this->loggedIn()) {
        	return mobilAP_Error::throwError('User already logged in', mobilAP_UserSession::USER_ALREADY_LOGGED_IN);
        } elseif (!$user = mobilAP_user::getUserByID($userID)) {
            if (mobilAP::getConfig('ALLOW_SELF_CREATED_USERS')) {
                return mobilAP_Error::throwError("Need to create new user", mobilAP_UserSession::USER_CREATE_NEW_USER);
            }
        	return mobilAP_Error::throwError("User $userID is not a user", mobilAP_UserSession::USER_NOT_FOUND);
        } else {
			
            if ($login = mobilAP_user::Auth($userID, $pword)) {

                $this->setUserID($user->getUserID());
				session_regenerate_id(true);
                $this->_setSession(true);
                return true;
                
            } elseif (mobilAP::getConfig('USE_ADMIN_PASSWORDS') && $user->isSiteAdmin() && $pword==mobilAP::getConfig('default_password')) {
				return mobilAP_Error::throwError("This account requires a password", mobilAP_UserSession::USER_REQUIRES_PASSWORD);
            } elseif (mobilAP::getConfig('USE_PRESENTER_PASSWORDS') && $user->isSessionAdmin() && $pword==mobilAP::getConfig('default_password')) {
				return mobilAP_Error::throwError("This account requires a password", mobilAP_UserSession::USER_REQUIRES_PASSWORD);
            } elseif (mobilAP::getConfig('USE_ADMIN_PASSWORDS') && $user->isSiteAdmin()) {
				return mobilAP_Error::throwError("Login Failure.", mobilAP_UserSession::USER_ADMIN_LOGIN_FAILURE);
            } elseif (mobilAP::getConfig('USE_PRESENTER_PASSWORDS') && $user->isSessionAdmin()) {
				return mobilAP_Error::throwError("Login Failure.", mobilAP_UserSession::USER_ADMIN_LOGIN_FAILURE);
            } else {
				return mobilAP_Error::throwError("Login Failure.", mobilAP_UserSession::USER_LOGIN_FAILURE);
            }
        }
        
        return $this->getResult();
    }

    public function logout()
    {
    	$this->_reset();
        return true;
    }

    private function _reset()
    {
        unset($_SESSION['mobilAP_userID']);
		
        $this->userID = null;
		$this->_clearLoginCookie();
		session_regenerate_id(true);
        $this->_setSession();
    }
	
    private function _setSession($setLoginCookie=false)
    {
    	$_SESSION['mobilAP_userID'] = $this->getUserID();

		if ($setLoginCookie) {
			$this->_setLoginCookie();
		}
    }    

    private function _setLoginCookie()
    {
    	if ($this->loggedIn()) {
			$login_token = md5(uniqid(rand(), true));
			$expires = time() + mobilAP_UserSession::LOGIN_COOKIE_LENGTH;
			
			if ($this->_getLoginCookie()) {
				$sql = sprintf("UPDATE %s SET token=?, timestamp=?, expires=? WHERE userID=? AND token=?", mobilAP_UserSession::COOKIE_TABLE);
                $params = array($login_token, date('Y-m-d H:i:s'), date('Y-m-d H:i:s', $expires), $this->getUserID(), $this->login_token);
			} else {
				$sql = sprintf("INSERT INTO %s (userID, token, timestamp, expires) VALUES (?,?,?,?)", mobilAP_UserSession::COOKIE_TABLE);
                $params = array($this->getUserID(), $login_token, date('Y-m-d H:i:s'), date('Y-m-d H:i:s', $expires));
			}
			
			$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
			$this->login_token = $login_token;
			setCookie('mobilAP_login_token', $this->login_token, $expires, $this->cookie_path);
			setCookie('mobilAP_login_userID', $this->getUserID(), $expires, $this->cookie_path);
		}
    }
    
    private function _getLoginCookie()
    {
    	if (isset($_COOKIE['mobilAP_login_token'], $_COOKIE['mobilAP_login_userID'])) {
    		$sql = sprintf("SELECT userID, token FROM %s WHERE userID=? AND token=?", 
    						mobilAP_UserSession::COOKIE_TABLE);
            $params = array($_COOKIE['mobilAP_login_userID'], $_COOKIE['mobilAP_login_token']);
    		$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
			if ($row = $result->fetchRow()) {
                if ($user = mobilAP_User::getUserByID($row['userID'])) {
                    if ($row['token']) {
                        $this->login_token = $row['token'];
                        return $user->getUserID();
                    }
                } else {
                    $this->_clearLoginCookie();
                }
    		} else {
    			$this->_clearLoginCookie();
    		}
    	}
    	
    	return '';
    }

    private function _clearLoginCookie()
    {
    	if (isset($_COOKIE['mobilAP_login_token'], $_COOKIE['mobilAP_login_userID'])) {
    		$sql = sprintf("DELETE FROM %s WHERE userID=? AND token=?", 
    						mobilAP_UserSession::COOKIE_TABLE);
            $params = array($_COOKIE['mobilAP_login_userID'], $_COOKIE['mobilAP_login_token']);
    		$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
			setCookie('mobilAP_login_token', false, 1225344860, $this->cookie_path);
			setCookie('mobilAP_login_userID', false, 1225344860, $this->cookie_path);
			$this->login_token = '';
    	}
    }

	public function __construct() 
	{
		if (!isset($_SESSION)) {
			session_start();
		}
	
		$userID = isset($_SESSION['mobilAP_userID']) ? $_SESSION['mobilAP_userID'] : '';
		$setLoginCookie = false;
				
		if (!$userID) {
			if ($userID = $this->_getLoginCookie()) {
				$setLoginCookie = true;
			}
		}
	
		$this->setUserID($userID);
		$this->_setSession($setLoginCookie);
	}
	
	public function setUserID($userID)
	{
		if ($user = mobilAP_User::getUserByID($userID)) {
			$this->userID = $user->getUserID();
		}
	}
}

?>