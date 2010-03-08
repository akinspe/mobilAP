<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

class mobilAP_announcement
{
	const ANNOUNCEMENT_TABLE='announcements';
	const ANNOUNCEMENT_READ_TABLE='announcements_read';
	public $announcement_id;
	public $announcement_title;
	public $announcement_timestamp;
	public $announcement_date;
	public $userID;
	public $announcement_text;
	
	private function updateSerial()
	{
		mobilAP::setSerialValue('announcements');
	}
    
    /*
     * commits the annoucement object to the database. You must set the object values before committing
     * @param string $userID, the userID of the user submitting the announcement
     * @return mixed, true if post was successful, or an error object if there was an error
     */
	public function postAnnouncement($userID)
	{
		if (!$user = mobilAP_user::getUserById($userID)) {
			return mobilAP_Error::throwError("Invalid user $userID");
		} elseif (!$user->isSiteAdmin()) {
			return mobilAP_Error::throwError("Unauthorized");
		}
        
        if (empty($this->announcement_title)) {
			return mobilAP_Error::throwError("Invalid title");
        }

        if (empty($this->announcement_text)) {
			return mobilAP_Error::throwError("Invalid text");
        }

        $ts = time();
		$sql = sprintf("INSERT INTO %s (announcement_title, announcement_timestamp, userID, announcement_text)
					    VALUES (?,?,?,?)", mobilAP_announcement::ANNOUNCEMENT_TABLE);
		$params = array($this->announcement_title, $ts, $user->getUserID(),$this->announcement_text);
		$result = mobilAP::query($sql, $params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
        $this->announcement_timestamp = $ts;
		$this->announcement_id = $result->get_last_insert_id();
		$this->updateSerial();
        return true;
	}
	
    /*
     * updates the annoucement object in the database.
     * @param string $userID, the userID of the user updating the announcement
     * @return mixed, true if update was successful, or an error object if there was an error
     */
	public function updateAnnouncement($userID)
	{
		if (!$user = mobilAP_user::getUserById($userID)) {
			return mobilAP_Error::throwError("Unauthorized");
		} elseif (!$user->isSiteAdmin()) {
			return mobilAP_Error::throwError("Unauthorized");
		}

		$sql = sprintf("UPDATE %s SET
						announcement_title=?, announcement_text=?
						WHERE announcement_id=?",
					    mobilAP_announcement::ANNOUNCEMENT_TABLE);
		$params = array($this->announcement_title,$this->announcement_text, $this->announcement_id);
		$result = mobilAP::query($sql, $params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		$this->updateSerial();
		return true;
	}

    /*
     * deletes the annoucement from the database.
     * @param string $userID, the userID of the user deleting the announcement
     * @return mixed, true if deletion was successful, or an error object if there was an error
     */
	public function deleteAnnouncement($userID)
	{
		if (!$user = mobilAP_user::getUserById($userID)) {
			return mobilAP_Error::throwError("Unauthorized");
		} elseif (!$user->isSiteAdmin()) {
			return mobilAP_Error::throwError("Unauthorized");
		}

		$tables = array(
            mobilAP_announcement::ANNOUNCEMENT_TABLE, 
            mobilAP_announcement::ANNOUNCEMENT_READ_TABLE
        );
        $params = array($this->announcement_id);
		foreach ($tables as $table) {
			$sql = sprintf("DELETE FROM %s WHERE announcement_id=?", $table);
			$result = mobilAP::query($sql, $params);
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
		}
		$this->updateSerial();
		return true;
	}
	
    /*
     * sets the announcement title
     * @param string $title
     * @return boolean true if successful, false if invalid title
     */
	public function setTitle($title)
	{
		if (!empty($title)) {
			$this->announcement_title = $title;
			return true;
		} else {
			return false;
		}
	}
	
    /*
     * sets the announcement text
     * @param string $text
     * @return boolean true if successful, false if invalid title
     */
	public function setText($text)
	{
		if (!empty($text)) {
			$this->announcement_text = $text;
			return true;
		} else {
			return false;
		}
	}
	
    /*
     * sets instance variables from an array
     * @param array $arr
     */
	private function loadFromArray($arr)
	{
		$this->announcement_id = intval($arr['announcement_id']);
		$this->announcement_title = $arr['announcement_title'];
		$this->announcement_timestamp = intval($arr['announcement_timestamp']);
		$this->announcement_date = strftime('%b %d, %Y %H:%M:%S', $arr['announcement_timestamp']);
		$this->userID = $arr['userID'];
		$this->announcement_text = $arr['announcement_text'];
	}	

    /*
     * returns an announcement object given an id
     * @param int $announcement_id, the announcement id to load
     * @return object an annoucement object. returns false if not found
     */
	public static function getAnnouncementByID($announcement_id)
	{
		$sql = sprintf("SELECT * FROM %s WHERE announcement_id=?", mobilAP_announcement::ANNOUNCEMENT_TABLE);
        $params = array($announcement_id);
		$result = mobilAP::query($sql, $params);
		if (mobilAP_Error::isError($result)) {
			return false;
		}

		if ($row = $result->fetchRow()) {
			$announcement = new mobilAP_announcement();
			$announcement->loadFromArray($row);
		} else {
			$announcement = false;
		}
		return $announcement;
	}

    /*
     * retrieves all the announcements
     * @return array, and array of announcement objects
     */
	function getAnnouncements()
	{
		$sql = sprintf("SELECT * FROM %s ORDER BY announcement_timestamp DESC", mobilAP_announcement::ANNOUNCEMENT_TABLE);
		$result = mobilAP::query($sql);
		$announcements=array();

		if (mobilAP_Error::isError($result)) {
			return $announcements;
		}

		while ($row = $result->fetchRow()) {
			$announcement = new mobilAP_announcement();
			$announcement->loadFromArray($row);
			$announcements[] = $announcement;
		}
		return $announcements;
	}

    /*
     * returns whether a user has read an announcement or not
     * @param string $userID, the user to check
     * @return boolean true if $userID has read this announcement or false
     */
	public function hasRead($userID)
	{
		if ($user = mobilAP_user::getUserById($userID)) {
			$sql = sprintf("SELECT * FROM %s WHERE announcement_id=? AND userID=?",
						   mobilAP_announcement::ANNOUNCEMENT_READ_TABLE);
            $params = array($this->announcement_id, $user->getUserID());
			$result = mobilAP::query($sql,$params);
			if (mobilAP_Error::isError($result)) {
				return false;
			}
			
			if ($row = $result->fetchRow()) {
				return $row['read_timestamp'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

    /*
     * sets the announcemnt read flag for a user
     * @param string $userID, the user who has read the announcement
     * @param bool $read, true to "read" it, false to "unread" it
     * @return true if sucessful, or an error object if there was an error
     */
	function readAnnouncement($userID, $read=true)
	{
		if ($user = mobilAP_user::getUserById($userID)) {
			if ($read) {
				$sql = sprintf("INSERT INTO %s (announcement_id, userID, read_timestamp)
								VALUES (?, ?, ?)",mobilAP_announcement::ANNOUNCEMENT_READ_TABLE);
				$params = array($this->announcement_id, $user->getUserID(), time());
			} else {
				$sql = sprintf("DELETE FROM %s WHERE announcement_id=? AND userID=?",
							    mobilAP_announcement::ANNOUNCEMENT_READ_TABLE);
				$params = array($this->announcement_id, $user->getUserID());
			}
			$result = mobilAP::query($sql,$params);
			return mobilAP_Error::isError($result) ? $result : true;
		} elseif ($userID) {
			return mobilAP_Error::throwError("Invalid user $userID");
		} else {
			return true; //no-op if there's no user
		}
	}

}

?>