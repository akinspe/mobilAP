<?php

/*

* Copyright (c) 2009, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

class mobilAP_schedule
{
	const SCHEDULE_DAYS_TABLE='schedule_days';
	const SCHEDULE_TABLE='schedule';
	
	static function getDays()
	{
		$sql = sprintf("SELECT DISTINCT date(start_time) date FROM %s ORDER BY 1 ASC", 
						mobilAP_schedule::SCHEDULE_TABLE);
		$result = mobilAP::query($sql);
		$days = array();
		if (mobilAP_Error::isError($result)) {
			return $days;
		}
		while ($row = $result->fetchRow()) {
			$row['date_ts'] = strtotime($row['date']);
			$row['date_str'] = strftime("%b %d, %Y", $row['date_ts']);
			$days[] = $row;
		}
		
		return $days;
	}
	
	static function getScheduleForDate($date)
	{
		$schedule = mobilAP::getSchedule(true);
		foreach($schedule as $day_schedule) {
			if ($day_schedule['date']==$date) {
				return $day_schedule;
			}
		}
		
		return false;
	}

}

class mobilAP_schedule_item
{
	public $schedule_id;
    public $schedule_type='item';
	public $start_time;
    private $start_ts;
    private $start_time_info;
	public $end_time;
    private $end_ts;
    private $end_time_info;
    public $title;
	public $detail;
	public $room;
	public $session_id;
	public $session_group_id;

    private function getStartTime() {
        return $this->start_time;
    }
	public function setStartTime($start_time)
	{
		if ( ($date_info = date_parse($start_time)) && ($date_ts = strtotime($start_time))) {
			$this->start_ts = $date_ts;
			$this->start_time = strftime('%a %h %d %Y %H:%M:%S', $this->start_ts);
            $this->start_time_info = $date_info;
			return true;
		}
	}

    private function getEndTime() {
        return $this->end_time;
    }

	public function setEndTime($end_time)
	{
		if ( ($date_info = date_parse($end_time)) && ($date_ts = strtotime($end_time))) {
			$this->end_ts = $date_ts;
			$this->end_time = strftime('%a %h %d %Y %H:%M:%S', $this->end_ts);
            $this->end_time_info = $date_info;
			return true;
		}
	}

	public function setDetail($detail)
	{
		$this->detail = $detail;
		return true;
	}

	public function setRoom($room)
	{
		$this->room = $room;
		return true;
	}
	
	public function setSession($session_id)
	{
		if ($session = mobilAP::getSessionByID($session_id)) {
			$this->session_id = $session->session_id;
			$this->session_group_id = null;
			$this->schedule_type = 'session';
            $this->title = $session->session_title;
		} else {
			$this->session_id = null;
		}
	}

	public function setSessionGroup($session_group_id)
	{
		if ($session_group = mobilAP_session_group::getSessionGroupByID($session_group_id)) {
            $this->session_id = null;
			$this->session_group_id = $session_group->session_group_id;
			$this->schedule_type = 'group';
            $this->title = $session->session_group_title;
		} else {
			$this->session_group_id = null;
		}
	}

	public function createItem()
	{
        if (!$this->start_ts) {
			return mobilAP_Error::throwError("Invalid start time");
		} elseif (!$this->end_ts) {
			return mobilAP_Error::throwError("Invalid end time");
		} elseif ($this->start_ts > $this->end_ts) {
			return mobilAP_Error::throwError("Start time cannot be after end time");
		} 
		        
		$sql = sprintf("INSERT INTO %s (start_time, end_time, detail, room, session_id, session_group_id) 
        VALUES (?,?,?,?,?,?)", mobilAP_schedule::SCHEDULE_TABLE);
        $params = array(
        strftime('%Y-%m-%d %H:%M:%S', $this->start_ts),
        strftime('%Y-%m-%d %H:%M:%S', $this->end_ts),
        $this->detail,$this->room,$this->session_id, $this->session_group_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}

		$this->schedule_id = $result->get_last_insert_id();
		$this->updateItem();
		return true;
	}

	public function deleteItem()
	{
		$sql = sprintf("DELETE FROM %s WHERE schedule_id=?", mobilAP_schedule::SCHEDULE_TABLE);
        $params = array($this->schedule_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		mobilAP_Cache::flushCache('mobilAP_schedule');		
        return true;
	}
	
	public function updateItem()
	{
		$sql = sprintf("UPDATE %s SET
				start_time=?,
				end_time=?,
				detail=?,
				room=?, 
				session_id=?,
				session_group_id=?
				WHERE schedule_id=?",
                mobilAP_schedule::SCHEDULE_TABLE);
		$params = array(
                strftime('%Y-%m-%d %H:%M:%S', $this->start_ts),
                strftime('%Y-%m-%d %H:%M:%S', $this->end_ts),
                $this->detail,$this->room, $this->session_id, $this->session_group_id, $this->schedule_id);
		$result = mobilAP::query($sql, $params);
		if (mobilAP_Error::isError($result)) {
			return $result;
		}
		mobilAP_Cache::flushCache('mobilAP_schedule');		
		return true;
	}
    
    public function loadData($array)
    {
        $this->schedule_id = intval($array['schedule_id']);
        $this->setStartTime($array['start_time']);
        $this->setEndTime($array['end_time']);
        $this->detail = $array['detail'];
        $this->room = $array['room'];
        $this->setSession($array['session_id']);
        $this->setSessionGroup($array['session_group_id']);
    }
	
	static function getScheduleItem($schedule_id)
	{
		$sql = sprintf("SELECT * FROM %s WHERE schedule_id=?", mobilAP_schedule::SCHEDULE_TABLE);
        $params=array($schedule_id);
		$result = mobilAP::query($sql,$params);
		if (mobilAP_Error::isError($result)) {
			return false;
		}
		if ($row = $result->fetchRow()) {
			$schedule_item = new mobilAP_schedule_item();
            $schedule_item->loadData($row);
		} else {
			$schedule_item = false;
		}
		
		return $schedule_item;
	}
}


?>