<?php


/*

* Copyright (c) 2008, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('config.php');
require_once('utils.php');

define('DB_ERROR_ALREADY_EXISTS', -1);

class mobilAP
{
	const SCHEDULE_DAYS_TABLE='schedule_days';
	const SCHEDULE_TABLE='schedule';
	const EVALUATION_QUESTION_TABLE='evaluation_questions';	
	const EVALUATION_QUESTION_RESPONSE_TABLE='evaluation_question_responses';

	public static function flushCache($key)
	{
		if (function_exists('apc_delete')) {
			apc_delete($key);
		}
	}

	public static function setCache($key, $value, $ttl=0)
	{
		if (function_exists('apc_store')) {
			return apc_store($key, $value, $ttl);
		}
	}

	public static function getCache($key)
	{
		if (function_exists('apc_fetch')) {
			return apc_fetch($key);
		}
		return false;
	}

	function getConfigs($refresh=false)
	{
		static $configs;
		if ($configs && !$refresh) {
			return $configs;
		}
	
		$configs = array();
		$sql = sprintf("SELECT config_var, config_value FROM %sconfig", TABLE_PREFIX);
		$result = mobilAP::query($sql, true);
		if (mobilAP_Error::isError($result)) {
			return $configs;
		}
		while ($row = mysql_fetch_assoc($result)) {
			$configs[$row['config_var']] = $row['config_value'];
		}
	
		return $configs;
	}

	function getConfig($var)
	{
		$configs = mobilAP::getConfigs();
		return isset($configs[$var]) ? $configs[$var] : null;
	}
	
	function setConfig($var, $value)
	{
		$sql = sprintf("REPLACE INTO %sconfig (config_var, config_value) VALUES ('%s', '%s')", TABLE_PREFIX, mobilAP::db_escape($var), mobilAP::db_escape($value));
		$result = mobilAP::query($sql);
	}
	
	function resetConfigs()
	{
		$sql = sprintf("DELETE FROM %sconfig", TABLE_PREFIX);
		$result = mobilAP::query($sql);
		mobilAP::getConfigs(true);
	}

	function db_escape($val)
	{
		return mysql_real_escape_string($val, mobilAP::getConn());
	}

	function getConn()
	{
		static $conn;
		if (!$conn) {
			$conn = mysql_connect(getDBConfig('db_host'), getDBConfig('db_user'), getDBConfig('db_password')) or die("Error connecting");
		}
		
		return $conn;
	}
	
    static function query($sql,$continue=false)
    {
    	$conn = mobilAP::getConn();
        mysql_select_db(getDBConfig('db_database')) or die("Error selecting DB: " . mysql_error());
        $result = mysql_query($sql, $conn);
        if (!$result) {
        	if (!$continue) {
        		$bt = debug_backtrace();
				die(sprintf("Error with query (%s @ %d): %s", $bt[0]['file'], $bt[0]['line'], mysql_error()));
			} 
			
			$errno = mysql_errno();
			switch ($errno)
			{
				case 1007:
				case 1022:
				case 1050:
				case 1062:
					$errno = DB_ERROR_ALREADY_EXISTS;
					break;
			}

			$result = mobilAP_Error::throwError(mysql_error(), $errno, $sql);
        }
        return $result;
    }

	/* THIS FUNCTION COULD BE REWRITTEN TO HANDLE AUTHORIZATION. IT SHOULD RETURN true or false */
    static function Auth($id, $password)
    {
		$field = 'attendee_id';

		if (Utils::is_validEmail($id)) {
			$field = 'email';
		} elseif (!preg_match("/^[a-z0-9]{32}$/" ,$id)) {
			return false;
		}

		$id = strtolower($id);
		
    	$where = array(
    		sprintf("u.%s='%s'", $field, mobilAP::db_escape($id))
    	);
    	
    	if (getConfig('USE_PASSWORDS')) {
    		$where[] = "md5='" . md5($password) . "'";
    	} elseif (getConfig('USE_ADMIN_PASSWORDS')) {
    		$where[] = "(!admin || md5='" . md5($password) . "')";
    	}

		$sql = "SELECT attendee_id FROM " . TABLE_PREFIX . mobilAP_attendee::ATTENDEE_TABLE . " u 
				WHERE " . implode(" AND ", $where);
		
		$result = mobilAP::query($sql);
		return mysql_num_rows($result)>0;
    }
    
	static function getDays()
	{
		$sql = "SELECT DISTINCT DATE(`start_date`) `date` FROM " .  TABLE_PREFIX . mobilAP::SCHEDULE_TABLE . " d ORDER BY 1 ASC";
		$result = mobilAP::query($sql);
		$days = array();
		while ($row = mysql_fetch_assoc($result)) {
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

	static function getSchedule($expand_groups = false)
	{
		$days = mobilAP::getDays();
		$schedule = array();
		$day_map = array();
		$i=0;
		foreach ($days as $day) {
			$schedule[$i] = array('index'=>$i, 'date'=>$day['date'], 'date_str'=>$day['date_str'], 'date_ts'=>$day['date_ts']);
			$day_map[$day['date']] = $i;
			$i++;
		}
		
		$session_groups = mobilAP_session_group::getSessionGroups();
		$parsed_session_groups = array();
								
		$sql = "SELECT s.*, DATE(start_date) `date` FROM " .  TABLE_PREFIX . mobilAP::SCHEDULE_TABLE . " s ORDER BY start_date asc,session_id";
		$result = mobilAP::query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			if (!$expand_groups && $row['session_group_id']) {
				if (!in_array($row['session_group_id'], $parsed_session_groups)) {
					$row['title'] = $session_groups[$row['session_group_id']]->session_group_title;
					$row['detail'] = $session_groups[$row['session_group_id']]->session_group_detail;
					$row['start_date'] = strftime('%b %d, %Y %H:%M:%S', $row['start_ts']);
					$row['end_date'] = strftime('%b %d, %Y %H:%M:%S', $row['end_ts']);
					$row['session_id'] = '';
					$row['room'] = '';
					$parsed_session_groups[] = $row['session_group_id'];
					$schedule[$day_map[$row['date']]]['schedule'][] = $row;
				}
			} else {
				$row['start_date'] = strftime('%b %d, %Y %H:%M:%S', $row['start_ts']);
				$row['end_date'] = strftime('%b %d, %Y %H:%M:%S', $row['end_ts']);
				$schedule[$day_map[$row['date']]]['schedule'][] = $row;
			}
		}
				
		return $schedule;
	}
	
	function getEvaluationQuestions($type)
	{
		$sql = sprintf("SELECT * FROM %s%s ORDER BY question_index", TABLE_PREFIX, mobilAP::EVALUATION_QUESTION_TABLE);
		$result = mobilAP::query($sql);
		$questions = array();
		while ($row = mysql_fetch_assoc($result)) {
			$question = new mobilAP_evaluation_question();
			$question->question_index = intval($row['question_index']);
			$question->question_text = $row['question_text'];
			$question->question_response_type = $row['question_response_type'];
			$question->responses = $question->getResponses();
			$questions[] = $question;
		}
		
		return $questions;
	}
	
	function export_data_sql()
	{
		$exec = sprintf("%s/mysqldump -h%s -u%s -p%s %s", 
			getConfig('MYSQL_BIN_FOLDER'), getConfig('db_host'), getConfig('db_user'), getConfig('db_password'), getConfig('db_database'));
		exec($exec, $output, $retVal);
		if ($retVal !=0) {
			return mobilAP_Error::throwError("Error getting output: " . implode(PHP_EOL, $output));
		} else {
			return implode(PHP_EOL, $output);
		}
	}

	function countries()
	{
		$countries = array(
			'US'=>'United States',
			'AF' => 'Afghanistan',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua And Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia And Herzegowina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CG' => 'Congo',
			'CD' => 'Congo, The Democratic Republic Of The',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote D\'Ivoire',
			'HR' => 'Croatia (Local Name: Hrvatska)',
			'CU' => 'Cuba',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'TP' => 'East Timor',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands (Malvinas)',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'FX' => 'France, Metropolitan',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard And Mc Donald Islands',
			'VA' => 'Holy See (Vatican City State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran (Islamic Republic Of)',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KP' => 'Korea, Democratic People\'S Republic Of',
			'KR' => 'Korea, Republic Of',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Lao People\'S Democratic Republic',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macau',
			'MK' => 'Macedonia, Former Yugoslav Republic Of',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia, Federated States Of',
			'MD' => 'Moldova, Republic Of',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'KN' => 'Saint Kitts And Nevis',
			'LC' => 'Saint Lucia',
			'VC' => 'Saint Vincent And The Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome And Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovakia (Slovak Republic)',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia, South Sandwich Islands',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SH' => 'St. Helena',
			'PM' => 'St. Pierre And Miquelon',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard And Jan Mayen Islands',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania, United Republic Of',
			'TH' => 'Thailand',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad And Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks And Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'UM' => 'United States Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Viet Nam',
			'VG' => 'Virgin Islands (British)',
			'VI' => 'Virgin Islands (U.S.)',
			'WF' => 'Wallis And Futuna Islands',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'YU' => 'Yugoslavia',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe' 
		);
	
		return $countries;
	}        

	function states()
	{
		$states = array(
		'AL'=>'Alabama',
		'AK'=>'Alaska',
		'AZ'=>'Arizona',
		'AR'=>'Arkansas',
		'CA'=>'California',
		'CO'=>'Colorado',
		'CT'=>'Connecticut',
		'DE'=>'Delaware',
		'DC'=>'District Of Columbia',
		'FL'=>'Florida',
		'GA'=>'Georgia',
		'HI'=>'Hawaii',
		'ID'=>'Idaho',
		'IL'=>'Illinois',
		'IN'=>'Indiana',
		'IA'=>'Iowa',
		'KS'=>'Kansas',
		'KY'=>'Kentucky',
		'LA'=>'Louisiana',
		'ME'=>'Maine',
		'MD'=>'Maryland',
		'MA'=>'Massachusetts',
		'MI'=>'Michigan',
		'MN'=>'Minnesota',
		'MS'=>'Mississippi',
		'MO'=>'Missouri',
		'MT'=>'Montana',
		'NE'=>'Nebraska',
		'NV'=>'Nevada',
		'NH'=>'New Hampshire',
		'NJ'=>'New Jersey',
		'NM'=>'New Mexico',
		'NY'=>'New York',
		'NC'=>'North Carolina',
		'ND'=>'North Dakota',    
		'OH'=>'Ohio',
		'OK'=>'Oklahoma',
		'OR'=>'Oregon',
		'PA'=>'Pennsylvania',
		'PR'=>'Puerto Rico',
		'RI'=>'Rhode Island',
		'SC'=>'South Carolina',
		'SD'=>'South Dakota',
		'TN'=>'Tennessee',
		'TX'=>'Texas',
		'UT'=>'Utah',
		'VT'=>'Vermont',
		'VI'=>'Virgin Islands',
		'VA'=>'Virginia',
		'WA'=>'Washington',
		'WV'=>'West Virginia',
		'WI'=>'Wisconsin',
		'WY'=>'Wyoming');
	
		return $states;
	}

	function is_validState($state)
	{
		$states = mobilAP::states();
		return array_key_exists(strtoupper($state), $states);
	}

	function is_validCountry($country)
	{
		$countries = mobilAP::countries();
		return array_key_exists(strtoupper($country), $countries);
	}
}

class mobilAP_schedule_item
{
	var $schedule_id;
	var $date;
	var $start_date;
	var $start_ts;
	var $end_date;
	var $end_ts;
	var $day;
	var $title;
	var $detail;
	var $session_id;
	var $room;
	var $session_group_id;

	public function setStartTime($start_time)
	{
		if (utils::is_timestamp($start_time)) {
			$this->start_ts = $start_time;
			$this->start_date = strftime('%Y-%m-%d %H:%M:%S', $this->start_ts);
			$this->date = strftime('%Y-%m-%d', $this->start_ts);
			return true;
		}
	}

	public function setEndTime($end_time)
	{
		if (utils::is_timestamp($end_time)) {
			$this->end_ts = $end_time;
			$this->end_date = strftime('%Y-%m-%d %H:%M:%S', $this->end_ts);
			return true;
		}
	}

	public function setTitle($title)
	{
		if (strlen($title)>0) {
			$this->title = $title;
			return true;
		} else {
			return false;
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
		if ($session = mobilAP_session::getSessionByID($session_id)) {
			$this->session_id = $session->session_id;
		} else {
			$this->session_id = null;
		}
	}

	public function setSessionGroup($session_group_id)
	{
		if ($session_group = mobilAP_session_group::getSessionGroupByID($session_group_id)) {
			$this->session_group_id = $session_group->session_group_id;
		} else {
			$this->session_group_id = null;
		}
	}
	
	public function setDay($day)
	{
		$this->day = $day;
	}

	public function createItem()
	{
		if (empty($this->title)) {
			return mobilAP_Error::throwError("Title cannot be blank");
		} elseif (!$this->start_ts) {
			return mobilAP_Error::throwError("Invalid start time");
		} elseif (!$this->end_ts) {
			return mobilAP_Error::throwError("Invalid end time");
		} elseif ($this->start_ts > $this->end_ts) {
			return mobilAP_Error::throwError("Start time cannot be after end time");
		} 
		
		$sql = "INSERT INTO " . TABLE_PREFIX . mobilAP::SCHEDULE_TABLE . " 
				(title) 
				VALUES
				('" . mobilAP::db_escape($this->title) . "')";
		$result = mobilAP::query($sql);
		$this->schedule_id = mysql_insert_id();
		$this->updateItem();
		return true;
	}

	public function deleteItem()
	{
		$sql = "DELETE FROM " . TABLE_PREFIX .  mobilAP::SCHEDULE_TABLE . " 
				WHERE schedule_id=$this->schedule_id";
		$result = mobilAP::query($sql);
		mobilAP::flushCache(SITE_PREFIX . '_mobilAP_schedule');		
	}
	
	public function updateItem()
	{
		$session_id = strlen($this->session_id) ? "'" . $this->session_id . "'" : "NULL";
		$session_group_id = strlen($this->session_group_id) ? $this->session_group_id: "NULL";

		$sql = "UPDATE " . TABLE_PREFIX . mobilAP::SCHEDULE_TABLE . " SET
				start_ts=$this->start_ts,
				start_date='$this->start_date',
				end_ts=$this->end_ts,
				end_date='$this->end_date',
				title='" . mobilAP::db_escape($this->title) . "',
				detail='" . mobilAP::db_escape($this->detail) . "',
				room='" . mobilAP::db_escape($this->room) . "',
				session_id=$session_id,
				session_group_id=$session_group_id
				WHERE schedule_id=$this->schedule_id";
		$result = mobilAP::query($sql);
		mobilAP::flushCache(SITE_PREFIX . '_mobilAP_schedule');		
		return true;
	}
	
	static function getScheduleItem($schedule_id)
	{
		$sql = "SELECT * FROM " . TABLE_PREFIX .  mobilAP::SCHEDULE_TABLE . " WHERE schedule_id='" . mobilAP::db_escape($schedule_id) . "'";
		$result = mobilAP::query($sql);
		if ($row = mysql_fetch_assoc($result)) {
			$schedule_item = new mobilAP_schedule_item();
			$schedule_item->schedule_id = $row['schedule_id'];
			$schedule_item->start_date = $row['start_date'];
			$schedule_item->start_ts = $row['start_ts'];
			$schedule_item->date = strftime('%Y-%m-%d', $schedule_item->start_ts);
			$schedule_item->end_date = $row['end_date'];
			$schedule_item->end_ts = $row['end_ts'];
			$schedule_item->title = $row['title'];
			$schedule_item->detail = $row['detail'];
			$schedule_item->room = $row['room'];
			$schedule_item->session_id = $row['session_id'];
			$schedule_item->session_group_id = $row['session_group_id'];
		} else {
			$schedule_item = false;
		}
		
		return $schedule_item;
	}
}

class mobilAP_attendee
{
	const ATTENDEE_TABLE='attendees';
	var $attendee_id;
	var $salutation;
	var $FirstName;
	var $LastName;
	var $organization;
	var $title;
	var $dept;
	var $city;
	var $state;
	var $country;
	var $email;
	var $phone;
	var $bio;
	var $checked_in=0;
	var $directory_active=0;
	var $admin=0;
	var $image_url;
	
	static function commitImport($import_id)
	{
		if ($attendee = mobilAP_attendee::editImport($import_id)) {
			$result = $attendee->createAttendeeFromObj();
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
			$attendee->setPassword($attendee->password);
			$attendee->updateAttendee();
			mobilAP_attendee::deleteImport($import_id);
		} elseif ($import_id=='all') {
			$data = mobilAP_attendee::getImportData();
			foreach ($data as $item) {
				mobilAP_attendee::commitImport($item['import_id']);
			}
		}
	}

	static function deleteImport($import_id)
	{
		if ($import_id=='all') {
			$data = mobilAP_attendee::getImportData();
			foreach ($data as $item) {
				mobilAP_attendee::deleteImport($item['import_id']);
			}
		} else {
			$sql = sprintf("DELETE FROM %s%s WHERE import_id=%d", TABLE_PREFIX, 'attendees_import', $import_id);
			$result = mobilAP::query($sql);
		}
	}

	static function editImport($import_id)
	{
		$sql = sprintf("SELECT * FROM %s%s WHERE import_id=%d", TABLE_PREFIX, 'attendees_import', $import_id);
		$result = mobilAP::query($sql);
		if ($row = mysql_fetch_assoc($result)) {
			$attendee = new mobilAP_attendee();	

			$attendee->setName($row['salutation'], $row['FirstName'], $row['LastName']);
			$attendee->setOrganization($row['organization']);
			$attendee->setTitle($row['title']);
			$attendee->setDepartment($row['dept']);
			$attendee->setEmail($row['email']);
			$attendee->setLocation($row['city'], $row['state'], $row['country']);
			$attendee->setPhone($row['phone']);
			$attendee->password = $row['password'];
		} else {
			$attendee = false;
		}
		
		return $attendee;
	}
	
	function getImportData()
	{
		$sql = "SELECT * FROM " . TABLE_PREFIX . 'attendees_import';
		$result = mobilAP::query($sql);
		$data = array();
		while ($row = mysql_fetch_assoc($result)) {
			$data[] = $row;
		}
		return $data;
	}

	function purgeImportData()
	{
		mobilAP::query("TRUNCATE TABLE " . TABLE_PREFIX . 'attendees_import');
	}

	static function importDelimitedFile($fileName, $delimiter=",", $enclosure='"')
	{
		ini_set('auto_detect_line_endings', 1);
		mobilAP_attendee::purgeImportData();
		
		$field_count = 12;
		if (!$handle = fopen($fileName, "r")) {
			return mobilAP_Error::throwError("Error reading $fileName");
		}
		
		while ( ($line = fgetcsv($handle, 0, $delimiter)) !== FALSE)
		{
			if (count($line)<=$field_count) {
				foreach ($line as $i=>$val) {
					$line[$i] = strtoupper($val)=='NULL' ? null : mobilAP::db_escape($val);
				}

				while (count($line)<$field_count) {
					$line[] = '';
				}
				
				$email = isset($line[9]) ? $line[9] : '';
				
				if (!$attendee = mobilAP_attendee::getAttendeeById($email)) {
					$sql = "INSERT INTO " . TABLE_PREFIX . "attendees_import VALUES (null, '" . implode("','", $line) . "')";
					mobilAP::query($sql);
				}
			}
		}
		fclose($handle);	

		return mobilAP_attendee::getImportData();
	}
	
	public function getUserID()
	{
		return $this->attendee_id;
	}

	static function getNextAttendeeID()
	{
		$attendee_id = md5(uniqid(rand(), true));
	
		// to be really sure we need to check if it's already there
		$sql = "SELECT attendee_id FROM " . TABLE_PREFIX . mobilAP_attendee::ATTENDEE_TABLE . " WHERE `attendee_id`='$attendee_id'";
			
		$result = mobilAP::query($sql);
	
		if (mysql_numRows($result)>0) {
			return mobilAP_attendee::generateToken();
		}
	
		return $attendee_id;		
	}

	function check_in()
	{
		$this->checked_in = time();
		$this->directory_active = -1;
		$this->updateAttendee();
	}

	function setDirectoryActive($active)
	{
		$this->directory_active = $active ? -1 : 0;
	}

	public function setBio($bio_text)
	{
		$this->bio = $bio_text;
		return true;
	}
	
	public function setName($salutation, $FirstName, $LastName)
	{
		if (!array_key_exists($salutation, mobilAP_attendee::getSalutations()) && !empty($salutation)) {
			return false;
		}

		if (empty($FirstName) || empty($LastName)) {
			return false;
		}
		
		$this->salutation = $salutation;
		$this->FirstName = trim($FirstName);
		$this->LastName = trim($LastName);
		return true;
	}

	function setOrganization($organization)
	{
		if (empty($organization)) {
			return false;
		}
		
		$this->organization = $organization;
		return true;
	}

	function setTitle($title)
	{
		$this->title = $title;
		return true;
	}

	function setDepartment($dept)
	{
		$this->dept = $dept;
		return true;
	}

	function setEmail($email)
	{
		if (!Utils::is_validEmail($email)) {
			return false;
		}
		
		$this->email = $email;
		return true;
	}
	
	function setPassword($password) 
	{
		$password = $password != '' && (getConfig('USE_PASSWORDS') || (getConfig('USE_ADMIN_PASSWORDS') && $this->admin))  ? $password : getConfig('default_password');
		$sql = sprintf("UPDATE `%s%s` SET
				md5='%s'
				WHERE attendee_id='%s'",
				TABLE_PREFIX,
				mobilAP_attendee::ATTENDEE_TABLE,
				md5($password),
				$this->attendee_id);
		$result = mobilAP::query($sql);
	}

	function setLocation($city, $state, $country)
	{
		$ok = true;
		
		if (empty($city)) {
			$ok = false;
		} else {
			$this->city = $city;
		}
		
		if (!mobilAP::is_validCountry($country)) {
			$ok = false;
		} else {
			$this->country = $country;
		}
		
		if ($country=='US') {
			if (!mobilAP::is_validState($state)) {
				$ok = false;
			} else {
				$this->state = $state;
			}			

		}  else {
			$this->state = $state;
		}
		
		return $ok;
	}

	function setPhone($phone)
	{
		if ($this->country=='US' || $this->country=='CA') {
			if (!preg_match('/^\(?(\d\d\d)?[-.)\s]*(\d\d\d)[-.\s]*(\d\d\d\d)$/', $phone, $bits)) {
				return false;
			}
			
			$this->phone = Utils::phone_format($phone);
			return true;
		} else {
			$this->phone = $phone;
			return true;
		}
	}
	
	static function getSalutations()
	{
		return array('Mr'=>'Mr', 'Miss'=>'Miss', 'Ms'=>'Ms', 'Mrs'=>'Mrs', 'Dr'=>'Dr', 'Prof'=>'Prof', 'Hon'=>'Hon');
	}

	public function getImageURL()
	{
		if (is_file($this->getPhotoThumb())) {
			return sprintf("directory_images/%s.jpg", $this->getUserID());
		} else {
			return 'Images/directory_default.png';
		}
	}
	
	public function deleteDirectoryImage()
	{
		@unlink($this->getPhotoThumb());
		@unlink($this->getPhotoFile());
	}
	
	/* this probably needs to be a configuration setting */
	public function getPhotoDir()
	{
		
		$photo_dir = sprintf('%s/directory_images', $_SERVER['DOCUMENT_ROOT']);
		return $photo_dir;
	}

	public function getPhotoFile()
	{
		$photo_file = sprintf("%s/%s_orig.jpg", $this->getPhotoDir(), $this->getUserID());
		return $photo_file;
	}

	public function getPhotoThumb()
	{
		$photo_file = sprintf("%s/%s.jpg", $this->getPhotoDir(), $this->getUserID() );
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
		$width = getConfig('thumb_width');
		$height = getConfig('thumb_height');
		
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
			
			return true;
		}
	}
	
	function uploadDirectoryImage()
	{
		$FILES = array();
		
		$file_keys = array('name','type','tmp_name','error','size');
		
		//cycle through files
		foreach ($_FILES as $key=>$file) {
			
			//make sure the array is a valid php upload array
			if (is_array($file) && array_keys($file)===$file_keys) {
		
				if (is_array($file['tmp_name'])) {
					foreach ($file['tmp_name'] as $k=>$f) {
						if (is_uploaded_file($file['tmp_name'][$k])) {
							$f = array('name'=>$file['name'][$k],
									   'type'=>$file['type'][$k],
									   'tmp_name'=>$file['tmp_name'][$k],
									   'error'=>$file['error'][$k],
									   'size'=>$file['size'][$k]
							);
									
							$FILES[] = $f;
						}
					}
				} elseif (is_uploaded_file($file['tmp_name'])) {
					$FILES[] = $file;
				}
			}
		}

		foreach ($FILES as $file) {
			if ($file['type'] != 'image/jpeg') {
				return mobilAP_Error::throwError("Image must be JPEG");
			}
			
			if (!move_uploaded_file($file['tmp_name'], $this->getPhotoFile())) {
				return mobilAP_Error::throwError("Error saving file");
			}

			chmod($this->getPhotoFile(), 0644);
			$result = $this->createPhotoThumb();
			return $result;
		}
	}
	
	public function loadData($row=null)
	{
		if (!is_array($row)) {
			$sql = "SELECT * FROM  " . TABLE_PREFIX .  mobilAP_attendee::ATTENDEE_TABLE . " WHERE
					attendee_id='" . $this->getUserID()  . "'";
			$result = mobilAP::query($sql);
			$row = mysql_fetch_assoc($result);
		}
		
		if ($row) {
			$this->salutation=$row['salutation'];
			$this->FirstName=$row['FirstName'];
			$this->LastName=$row['LastName'];
			$this->organization=$row['organization'];
			$this->title=$row['title'];
			$this->dept=$row['dept'];
			$this->city=$row['city'];
			$this->state=$row['state'];
			$this->country=$row['country'];
			$this->email=$row['email'];
			$this->phone=$row['phone'];
			$this->bio=$row['bio'];
			$this->checked_in= intval($row['checked_in']);
			$this->directory_active= intval($row['directory_active']);
			$this->image_url = $this->getImageURL();
			$this->admin= intval($row['admin']);
			return true;
		
		} else {
			return false;
		}
	}

		
	public function updateAttendee()
	{
		$sql = sprintf("UPDATE `%s%s` SET
				salutation='%s',
				FirstName='%s',
				LastName='%s',
				organization='%s',
				title='%s',
				dept='%s',
				city='%s',
				state='%s',
				country='%s',
				email='%s',
				phone='%s',
				bio='%s',
				checked_in=%d,
				directory_active=%d,
				admin=%d
				WHERE attendee_id='%s'",
				TABLE_PREFIX,
				mobilAP_attendee::ATTENDEE_TABLE,
				$this->salutation,
				mobilAP::db_escape($this->FirstName),
				mobilAP::db_escape($this->LastName),
				mobilAP::db_escape($this->organization),
				mobilAP::db_escape($this->title),
				mobilAP::db_escape($this->dept),
				mobilAP::db_escape($this->city),
				mobilAP::db_escape($this->state),
				mobilAP::db_escape($this->country),
				mobilAP::db_escape($this->email),
				mobilAP::db_escape($this->phone),
				mobilAP::db_escape($this->bio),
				$this->checked_in,
				$this->directory_active,
				$this->admin,
				$this->attendee_id);
		$result = mobilAP::query($sql, true);
		if (mobilAP_Error::isError($result)) {
			if ($result->getCode()==DB_ERROR_ALREADY_EXISTS) {
				$result = mobilAP_Error::throwError("User already exists", DB_ERROR_ALREADY_EXISTS);
			}
		}
		mobilAP::flushCache(SITE_PREFIX . '_mobilAP_attendees');
		mobilAP::flushCache(SITE_PREFIX . '_mobilAP_attendee_summary');
		return $result;	
	}

	function createAttendeeFromObj()
	{
		if (!Utils::is_validEmail($this->email)) {
			return mobilAP_Error::throwError("Invalid email: $this->email");
		} elseif (strlen($this->FirstName)==0 || strlen($this->LastName)==0) {
			return mobilAP_Error::throwError("Name cannot be blank");
		}
		$this->attendee_id = mobilAP_attendee::getNextAttendeeID();
		
		$sql = sprintf("INSERT INTO %s%s
				(attendee_id, email, FirstName, LastName)
				VALUES
				('%s', '%s', '%s', '%s')", 
				TABLE_PREFIX,
				mobilAP_attendee::ATTENDEE_TABLE, 
				$this->attendee_id,
				mobilAP::db_escape($this->email),
				mobilAP::db_escape($this->FirstName),
				mobilAP::db_escape($this->LastName)
		);

		$result = mobilAP::query($sql, true);
		if (mobilAP_Error::isError($result)) {
			if ($result->getCode()==DB_ERROR_ALREADY_EXISTS) {
				$result = mobilAP_Error::throwError("User already exists", DB_ERROR_ALREADY_EXISTS);
			}			
		} else {
			$result = true;
		}
		
		return $result;
	}
	
	function deleteAttendee()
	{
		$this->deleteDirectoryImage();		

		$sql = sprintf("DELETE FROM %s%s
				WHERE response_userID='%s'",
				TABLE_PREFIX,
				mobilAP_session::POLL_ANSWERS_TABLE, 
				$this->attendee_id);
		$result = mobilAP::query($sql);
		
		$sessions = mobilAP_session::getSessionsForUser($this->getUserID());
		foreach ($sessions as $session) {
			$index = $session->getIndexForPresenter($this->getUserID());
			if (!mobilAP_Error::isError($index)) {
				$session->removePresenter($index);
			}
		}		
		
		$tables = array(
			mobilAP_session::SESSION_CHAT_TABLE, 
			mobilAP_session::SESSION_LINK_TABLE, 
			mobilAP_session::SESSION_EVALUATION_TABLE
		);
		foreach ($tables as $table) {

			$sql = sprintf("DELETE FROM %s%s
					WHERE post_user='%s'",
					TABLE_PREFIX,
					$table,
					$this->attendee_id);
			$result = mobilAP::query($sql);
		}		

		$sql = sprintf("DELETE FROM %s%s
				WHERE attendee_id='%s'",
				TABLE_PREFIX,
				mobilAP_attendee::ATTENDEE_TABLE, 
				$this->attendee_id);
		$result = mobilAP::query($sql);

		mobilAP::flushCache(SITE_PREFIX . '_mobilAP_attendees');
		mobilAP::flushCache(SITE_PREFIX . '_mobilAP_attendee_summary');
	}
	
	public function setAdmin($admin)
	{
		$this->admin = $admin ? -1 : 0;
	}
	
	public function isAdmin()
	{
		return $this->admin;
	}
	
	static function getAttendeeByID($token)
	{
		$field = 'attendee_id';

		if (Utils::is_validEmail($token)) {
			$field = 'email';
		} elseif (!preg_match("/^[a-z0-9]{32}$/", $token)) {
			return false;
		}
		
		$sql = "SELECT * FROM  " . TABLE_PREFIX . mobilAP_attendee::ATTENDEE_TABLE . " WHERE
				$field='" . mobilAP::db_escape($token) . "'";
		$result = mobilAP::query($sql);
		
		if ($row = mysql_fetch_assoc($result)) {
			$attendee = new mobilAP_attendee();
			$attendee->attendee_id = $row['attendee_id'];
			$attendee->loadData($row);
		} else {
			$attendee = false;
		}
		
		return $attendee;
	}
	
	static function getAttendees($args=null)
	{
		$order = 'LastName,FirstName';
		$args = is_array($args) ? $args : array();
		$where = array();
		$only_active = true;
		
		foreach ($args as $arg=>$value) 
		{
			switch ($arg)
			{
				case 'order':
					$$arg = $value;
					break;
				case 'only_active':
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
		
		
		$sql = "SELECT * FROM  " . TABLE_PREFIX . mobilAP_attendee::ATTENDEE_TABLE;
		
		if (count($where)>0) {
			$sql .= " WHERE " . implode(" AND ", $where);
		}
		
		$sql .= " ORDER BY $order";
		$result = mobilAP::query($sql);

		$attendees=array();
		
		while ($row = mysql_fetch_assoc($result)) {
			$attendee = new mobilAP_attendee();
			$attendee->attendee_id = $row['attendee_id'];
			$attendee->loadData($row);
			$attendees[] = $attendee;
		}
		
		return $attendees;
	}
	
	static function getWelcomeImageSrc()
	{
		if (!$attendee_summary = mobilAP::getCache(SITE_PREFIX . '_mobilAP_attendee_summary')) {
			$attendee_summary = mobilAP_attendee::getAttendeeSummary();
		}
		
		$src = 'http://chart.apis.google.com/chart?chtm=usa&chs=280x140&cht=t';
		$states = array();
		$values = array();
		foreach ($attendee_summary['states'] as $state=>$value) {
			$values[] = floor($value*100/$attendee_summary['total']);
		}
		
		if (count($attendee_summary['states'])>0) {
			$src .= '&chd=t:' . implode(',', $values);
			$src .= '&chld=' . implode('', array_keys($attendee_summary['states']));
		} else {
			$src .= '&chd=s:_';
		}
		
		return $src;
	}
	
	static function getAttendeeSummary()
	{
		$where = array();
		if (getConfig('show_only_active_attendees')) {
			$where[] = 'directory_active';
		}
		
		$sql = "SELECT state,country,organization FROM  " . TABLE_PREFIX .  mobilAP_attendee::ATTENDEE_TABLE;
		if (count($where)>0) {
			$sql .= " WHERE " . implode(" AND ", $where);
		}
		
		$result = mobilAP::query($sql);
		$data = array(
			'total'=>0,
			'states'=>array(),
			'states_count'=>0,
			'organizations'=>array(),
			'organizations_count'=>0
		);
		while ($row = mysql_fetch_assoc($result)) {
		
			$data['total']++;
			if ($row['state']) {
				if (!isset($data['states'][$row['state']])) {
					$data['states'][$row['state']] = 0;
				}
				$data['states'][$row['state']]++;
			}

			if (!isset($data['organizations'][$row['organization']])) {
				$data['organizations'][$row['organization']] = 0;
			}
			$data['organizations'][$row['organization']]++;
		}
		
		ksort($data['states']);
		ksort($data['organizations']);

		$data['states_count'] = count($data['states']);
		$data['organizations_count'] = count($data['organizations']);
		unset($data['organizations']);

		return $data;
	}
	
	public function isPresenter()
	{
		$sql = "SELECT * FROM " . TABLE_PREFIX . mobilAP_session::SESSION_PRESENTER_TABLE . " 
				WHERE 
				presenter_id='" . $this->getUserID() . "'";
				
		$result = mobilAP::query($sql);
		return mysql_numRows($result)>0;
	}
	
}

class mobilAP_session
{
	const POLL_QUESTIONS_TABLE='poll_questions';
	const POLL_RESPONSES_TABLE='poll_responses';
	const POLL_ANSWERS_TABLE='poll_answers';
	const SESSION_TABLE='sessions';
	const SESSION_LINK_TABLE='session_links';
	const SESSION_PRESENTER_TABLE='session_presenters';
	const SESSION_CHAT_TABLE='session_chat';
	const SESSION_EVALUATION_TABLE='session_evaluations';
	const ERROR_NO_USER=-1;
	const ERROR_USER_ALREADY_SUBMITTED=-2;
	const SESSION_FLAGS_DEFAULT=15;
	const SESSION_FLAGS_LINKS=1;
	const SESSION_FLAGS_ATTENDEE_LINKS=2;
	const SESSION_FLAGS_DISCUSSION=4;
	const SESSION_FLAGS_EVALUATION=8;
	var $session_id;
	var $session_title;
	var $session_abstract;
	var $session_flags=mobilAP_session::SESSION_FLAGS_DEFAULT;
	
	public function setSessionID($session_id)
	{
		if (!preg_match('/^[a-zA-Z0-9]{3}$/', $session_id)) {
			return mobilAP_Error::throwError("Session code must be 3 characters");
		}

		if ($session_id == $this->session_id) {
			return true;
		}
		
		if ($session = mobilAP_session::getSessionByID($session_id)) {
			return mobilAP_Error::throwError("Session $session_id already exists");
		}


		$tables = array(mobilAP_session::POLL_QUESTIONS_TABLE, 
						mobilAP_session::SESSION_LINK_TABLE,
						mobilAP_session::SESSION_PRESENTER_TABLE,
						mobilAP_session::SESSION_CHAT_TABLE,
						mobilAP_session::SESSION_EVALUATION_TABLE,
						mobilAP::SCHEDULE_TABLE,
						mobilAP_session::SESSION_TABLE);
						
		foreach ($tables as $table)
		{
			$sql = sprintf("UPDATE %s%s SET session_id='%s' WHERE session_id='%s'", TABLE_PREFIX, $table, $session_id, $this->session_id);
			mobilAP::query($sql);
		}
		
		$this->session_id = $session_id;
		return true;
	}
	
	public function setSessionFlags($session_flags)
	{
		$this->session_flags = intval($session_flags);
		return true;
	}
	
	/* this function gets what a user has done with a session, most notably if they've finished the evaluation or answered the questions */
	public function getUserSubmissions($userID)
	{
		$data = array('questions'=>array(), 'evaluation'=>false);
		$sql = "SELECT evaluation_id FROM " . TABLE_PREFIX . mobilAP_session::SESSION_EVALUATION_TABLE . " 
				WHERE session_id='$this->session_id' AND post_user='$userID'";
		$result = mobilAP::query($sql);
		$data['evaluation'] = mysql_numRows($result)>0;
				
		$sql = "SELECT a.* FROM " . TABLE_PREFIX  . mobilAP_session::POLL_ANSWERS_TABLE . " a
				WHERE question_id IN (SELECT question_id FROM " . TABLE_PREFIX . mobilAP_session::POLL_QUESTIONS_TABLE . " WHERE session_id='$this->session_id') AND response_userID='$userID'";
		$result = mobilAP::query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$data['questions'][$row['question_id']] = $row['answer_id'];
		}

		return $data;
	}
	
	public function loadSessionFromArray($arr)
	{
		$session = new mobilAP_session();
		$session->session_id = (string) $arr['session_id'];
		$session->session_title = $arr['session_title'];
		$session->session_abstract = $arr['session_abstract'];
		$session->session_flags = intval($arr['session_flags']);
		return $session;
	}
	
	public function deleteSession()
	{
		$questions = $this->getQuestions();
		foreach ($questions as $question) {
			$question->deleteQuestion();
		}
		
		$tables = array(mobilAP_session::SESSION_TABLE, mobilAP_session::SESSION_LINK_TABLE, mobilAP_session::SESSION_PRESENTER_TABLE, mobilAP_session::SESSION_EVALUATION_TABLE, mobilAP_session::SESSION_CHAT_TABLE);
		foreach ($tables as $table) {
			$sql = "DELETE FROM `" . TABLE_PREFIX . $table . "` WHERE session_id='$this->session_id'";
			$result = mobilAP::query($sql);
		}
		$sql = "UPDATE `" . TABLE_PREFIX . mobilAP::SCHEDULE_TABLE . "` SET session_id=null WHERE session_id='$this->session_id'";
		$result = mobilAP::query($sql);
	}
	
	static function createSession($session_id, $session_title)
	{
		if (!preg_match('/^[a-zA-Z0-9]{3}$/', $session_id)) {
			return mobilAP_Error::throwError("Session code must be 3 characters");
		} elseif (empty($session_title)) {
			return mobilAP_Error::throwError("Session title cannot be blank");
		} elseif ($session = mobilAP_session::getSessionByID($session_id)) {
			return mobilAP_Error::throwError("Session $session_id already exists");
		}
		
		$sql = "INSERT INTO " . TABLE_PREFIX . mobilAP_session::SESSION_TABLE . " (session_id, session_title) VALUES ($session_id, '" . mobilAP::db_escape($session_title) . "')";
		$result = mobilAP::query($sql);
		return true;
	}

	static function getSessionByID($session_id)
	{
		$sql = "SELECT s.*
				FROM " . TABLE_PREFIX . mobilAP_session::SESSION_TABLE . " s 
				WHERE session_id='" . mobilAP::db_escape($session_id) . "'";
		$result = mobilAP::query($sql);
		$session = false;
		if ($row = mysql_fetch_assoc($result)) {
			$session = mobilAP_session::loadSessionFromArray($row);
		}
		return $session;
	}
	
	static function getSessions()
	{
		$sql = "SELECT s.* FROM " . TABLE_PREFIX . mobilAP_session::SESSION_TABLE . " s ORDER BY session_id";
		$result = mobilAP::query($sql);
		$sessions = array();
		while ($row = mysql_fetch_assoc($result)) {
			$session = mobilAP_session::loadSessionFromArray($row);
			$session->session_links = $session->getLinks();
			$session->session_questions = $session->getQuestions();				
			$session->session_presenters = $session->getPresenters();
			$session->session_chat = $session->get_chat();
			$session->session_evaluations = $session->getEvaluations();
			$sessions[$session->session_id] = $session;
		}
		
		return $sessions;
	}
	
	static function getSessionsForUser($userID)
	{
		$sessions = array();
		if ($user = mobilAP_attendee::getAttendeeByID($userID)) {
			if ($user->isAdmin()) {
				return mobilAP_session::getSessions();
			}
			
			
			$sql = "SELECT s.* FROM " . TABLE_PREFIX . mobilAP_session::SESSION_TABLE . " s 
					WHERE session_id IN (SELECT session_id FROM " . mobilAP_session::SESSION_PRESENTER_TABLE . " WHERE presenter_id='" . $user->getUserID() . "')
					ORDER BY session_id";
			$result = mobilAP::query($sql);
			
			while ($row = mysql_fetch_assoc($result)) {
				$session = mobilAP_session::loadSessionFromArray($row);
				$sessions[$session->session_id] = $session;
			}
		}
		return $sessions;
	}

	public function getEvaluationSummary()
	{
		$evaluation_questions = mobilAP::getEvaluationQuestions();
		$avg_fields = array();
		$text_fields = array();
		$data = array();
		foreach ($evaluation_questions as $question) {
			if ($question->question_response_type == mobilAP_evaluation_question::RESPONSE_TYPE_CHOICES) {
				$data['q' . $question->question_index] = array('avg'=>0, 'count'=>array());
				foreach ($question->responses as $response) {
					$data['q' . $question->question_index]['count'][$response['response_value']] = 0;
				}
				
				$avg_fields[] = sprintf("avg(q%d) q%d", $question->question_index, $question->question_index);
				$count_fields[] = sprintf("q%d", $question->question_index);
			} else {
				$data['q' . $question->question_index] = array();
				$text_fields[] = sprintf("q%d", $question->question_index);
			}
		}
	
		$sql = "SELECT " . implode(',', $avg_fields) . " FROM " . TABLE_PREFIX . mobilAP_session::SESSION_EVALUATION_TABLE . "
				WHERE session_id='$this->session_id'";

		$result = mobilAP::query($sql);
		if ($row=mysql_fetch_assoc($result)) {
			foreach ($row as $idx=>$avg) {
				if ($avg) {
					$data[$idx]['avg'] = $avg;
				}
			}
		}

		$sql = "SELECT " . implode(',', $count_fields) . " FROM " . TABLE_PREFIX . mobilAP_session::SESSION_EVALUATION_TABLE . "
				WHERE session_id='$this->session_id'";

		$result = mobilAP::query($sql);
		if ($row=mysql_fetch_assoc($result)) {
			foreach ($row as $idx=>$value) {
				$data[$idx]['count'][$value]++;
			}
		}

		$sql = "SELECT " . implode(',', $text_fields) . " FROM " . TABLE_PREFIX . mobilAP_session::SESSION_EVALUATION_TABLE . "
				WHERE session_id='$this->session_id'";

		$result = mobilAP::query($sql);
		while ($row=mysql_fetch_assoc($result)) {
			foreach ($row as $idx=>$text) {
				if ($text) {
					$data[$idx][] = $text;
				}
			}
		}

		return $data;		
	}
	
	public function getEvaluations()
	{
		$sql = "SELECT * FROM " . TABLE_PREFIX .  mobilAP_session::SESSION_EVALUATION_TABLE . " 
				WHERE session_id='$this->session_id'";
		$result = mobilAP::query($sql);
		$evaluations = array();
		while ($row=mysql_fetch_assoc($result)) {
			$evaluations[] = $row;
		}
		
		return $evaluations;
	}
	
	public function getLinks()
	{
		$sql = "SELECT * FROM " . TABLE_PREFIX . mobilAP_session::SESSION_LINK_TABLE . " 
				WHERE session_id='$this->session_id'
				ORDER BY post_timestamp DESC";
		$result = mobilAP::query($sql);
		$links = array();
		while ($row=mysql_fetch_assoc($result)) {
			$links[] = mobilAP_session_link::loadLinkFromArray($row);
		}
		
		return $links;				
	}

	public function getQuestions($show_all=false)
	{
		$where = array(
			"session_id='$this->session_id'"
		);
		
		if (!$show_all) {
			$where[] = 'question_active';
		}
		
		$sql = "SELECT * FROM " . TABLE_PREFIX . mobilAP_session::POLL_QUESTIONS_TABLE . " 
				WHERE " . implode(" AND ", $where) . "
				ORDER BY `index`";
		$result = mobilAP::query($sql);
		$questions = array();
		while ($row=mysql_fetch_assoc($result)) {
			$questions[] = mobilAP_poll_question::loadQuestionFromArray($row);
		}
		
		return $questions;				
	}

	public function getNextQuestionIndex()
	{
		$sql = "SELECT `index` FROM " . TABLE_PREFIX . mobilAP_session::POLL_QUESTIONS_TABLE . " 
				WHERE session_id='$this->session_id'";
		$result = mobilAP::query($sql);
		return mysql_numrows($result);
	}

	public function getQuestionById($question_id)
	{
		$sql = "SELECT * FROM " . TABLE_PREFIX . mobilAP_session::POLL_QUESTIONS_TABLE . " 
				WHERE 
				question_id='" . mobilAP::db_escape($question_id) . "' AND
				session_id='$this->session_id'";
		$result = mobilAP::query($sql);
		$question = false;
		if ($row=mysql_fetch_assoc($result)) {
			$question = mobilAP_poll_question::loadQuestionFromArray($row);
		}
		return $question;		
	}

	public function getLinkById($link_id)
	{
		$sql = "SELECT * FROM " . TABLE_PREFIX  . mobilAP_session::SESSION_LINK_TABLE . " 
				WHERE 
				link_id='" . mobilAP::db_escape($link_id) . "' AND
				session_id='$this->session_id'";
		$result = mobilAP::query($sql);
		$link = false;
		if ($row=mysql_fetch_assoc($result)) {
			$link = mobilAP_session_link::loadLinkFromArray($row);
		}
		return $link;
	}
	
	public function isPresenter($userID)
	{
		$user = mobilAP_attendee::getAttendeeById($userID);
		
		if ($user) {
			$sql = "SELECT * FROM " . TABLE_PREFIX . mobilAP_session::SESSION_PRESENTER_TABLE . " 
					WHERE 
					session_id='$this->session_id'
					AND presenter_id='" . $user->getUserID() . "'";
					
			$result = mobilAP::query($sql);
			return mysql_num_rows($result);
		}
		return false;
			
	}

	public function getPresenters()
	{
		$sql = "SELECT * FROM " . TABLE_PREFIX .  mobilAP_session::SESSION_PRESENTER_TABLE . " 
				WHERE session_id='$this->session_id' ORDER BY `index`";
		$result = mobilAP::query($sql);
		$presenters = array();
		while ($row=mysql_fetch_assoc($result)) {
			$presenters[$row['index']] = mobilAP_attendee::getAttendeeById($row['presenter_id']);
		}
		return $presenters;
	}

	public function getPresentersDirectory()
	{
		$sql = "SELECT * FROM " . TABLE_PREFIX . mobilAP_session::SESSION_PRESENTER_TABLE . " 
				WHERE session_id='$this->session_id' ORDER BY `index`";
		$result = mobilAP::query($sql);
		$presenters = array();
		while ($row=mysql_fetch_assoc($result)) {
			if ($user = mobilAP_attendee::getAttendeeById($row['presenter_id'])) {
				$presenters[$row['index']] = $user;
			}
		}
		return $presenters;
	}
	
	public function getNextPresenterIndex()
	{
		$sql = "SELECT `index` FROM " . TABLE_PREFIX .  mobilAP_session::SESSION_PRESENTER_TABLE . " 
				WHERE session_id='$this->session_id'";
		$result = mobilAP::query($sql);
		return mysql_numrows($result);
	}
	
	public function addPresenter($presenter_userID)
	{
		if ($user = mobilAP_attendee::getAttendeeByID($presenter_userID)) {
			$index = $this->getNextPresenterIndex();
			$sql = "INSERT INTO " . TABLE_PREFIX .  mobilAP_session::SESSION_PRESENTER_TABLE . " (session_id, `index`, presenter_id)
			VALUES ('$this->session_id', $index, '" . $user->getUserID() . "')";
			$result = mobilAP::query($sql, true);
			if (mobilAP_Error::isError($result) && $result->getCode()==DB_ERROR_ALREADY_EXISTS) {
				$result = true;
			}
			return $result;
		} else {
			return mobilAP_Error::throwError("User $presenter_userID not found");
		}
	}
	
	public function getIndexForPresenter($presenter_userID)
	{
		if ($user = mobilAP_attendee::getAttendeeByID($presenter_userID)) {
			$sql = sprintf("SELECT `index` FROM %s%s
							WHERE 
							session_id='%s' AND presenter_id='%s'", 
							TABLE_PREFIX,
							mobilAP_session::SESSION_PRESENTER_TABLE,
							$this->session_id,
							$user->getUserID()
							);
			$result = mobilAP::query($sql);
			
			if ($row = mysql_fetch_assoc($result)) {
				return $row['index'];
			} else {
				return mobilAP_Error::throwError("User $presenter_userID not a presenter for this session");
			}
			
		} else {
			return mobilAP_Error::throwError("User $presenter_userID not found");
		}
	}
		

	public function removePresenter($index)
	{
		$sql = "DELETE FROM " . TABLE_PREFIX . mobilAP_session::SESSION_PRESENTER_TABLE . "
			    WHERE session_id='$this->session_id' AND `index`=$index";
		$result = mobilAP::query($sql);
		$sql = "UPDATE " . TABLE_PREFIX . mobilAP_session::SESSION_PRESENTER_TABLE . "
				SET `index`=`index`-1 
			    WHERE session_id='$this->session_id' AND `index`>$index";
		$result = mobilAP::query($sql);
	}
	
	public function addQuestion($question_text)
	{
		if (empty($question_text)) {
			return mobilAP_Error::throwError("Question cannot be empty");
		}
		
		$index = $this->getNextQuestionIndex();
			
		$sql = "INSERT INTO " . TABLE_PREFIX . mobilAP_session::POLL_QUESTIONS_TABLE . " 
		(session_id, `index`, question_text)
		VALUES
		('$this->session_id', $index, '" . mobilAP::db_escape($question_text) . "')";
		
		$result = mobilAP::query($sql);
		$question_id = mysql_insert_id();
		return $this->getQuestionById($question_id);
	}
	
	public function addLink($link_url, $link_text, $post_user)
	{
		if (!Utils::is_validURL($link_url)) {
			return mobilAP_Error::throwError("Invalid url");
		} elseif (empty($link_text)) {
			return mobilAP_Error::throwError("Link text cannot be blank");
		} elseif (!$user = mobilAP_attendee::getAttendeeById($post_user)) {
			return mobilAP_Error::throwError("You must login to post a link", mobilAP_session::ERROR_NO_USER);
		} elseif (!($this->session_flags & mobilAP_session::SESSION_FLAGS_ATTENDEE_LINKS) && !$this->isPresenter($post_user)) {
			return mobilAP_Error::throwError("Posting of links to this session has been disabled");
		}

		$ts = time();
		
		$link_type = $this->isPresenter($post_user) ? 'A' : 'U';
		
		$sql = "INSERT INTO " . TABLE_PREFIX . mobilAP_session::SESSION_LINK_TABLE . "
				(session_id, link_url, link_text, link_type, post_user, post_timestamp)
				VALUES
				('$this->session_id', '" . mobilAP::db_escape($link_url) . "','" . mobilAP::db_escape($link_text) . "','$link_type', '" . mobilAP::db_escape($post_user) . "', $ts)";
		$result = mobilAP::query($sql);
		$link_id = mysql_insert_id();
		return $this->getLinkById($link_id);
	}
	
	public function addEvaluation($post_user, $responses)
	{
		if (!is_array($responses)) {
			return mobilAP_Error::throwError("Invalid responses");
		}
		
		$evaluation_questions = mobilAP::getEvaluationQuestions();
		
		for($i=0; $i<count($evaluation_questions); $i++) {
			if (!isset($responses[$i])) {
				$responses[$i] = 'NULL';
			} else {
				$responses[$i] = $evaluation_questions[$i]->question_response_type == 'T' ? "'" . mobilAP::db_escape($responses[$i]) . "'"  : intval($responses[$i]);
			}
		}
		
		ksort($responses);
		
		$ts = time();
		
		if (!$user = mobilAP_attendee::getAttendeeById($post_user)) {
			return mobilAP_Error::throwError("You must login to post an evaluation", mobilAP_session::ERROR_NO_USER);
		}

		if ($post_user) {		
			$sql = "SELECT evaluation_id FROM " . TABLE_PREFIX .  mobilAP_session::SESSION_EVALUATION_TABLE . " 
				    WHERE session_id='$this->session_id' AND post_user='$post_user'";
			$result = mobilAP::query($sql);
			if (mysql_numrows($result)>0) {
				return mobilAP_Error::throwError("You have already evaluated this session", mobilAP_session::ERROR_USER_ALREADY_SUBMITTED);
			}
		}
		
		
		$sql = "INSERT INTO " . TABLE_PREFIX . mobilAP_session::SESSION_EVALUATION_TABLE . "
				(session_id, post_user, post_timestamp, q" . implode(", q", array_keys($evaluation_questions)) . ") 
				VALUES
				('$this->session_id', '" . mobilAP::db_escape($post_user) . "', $ts, " . implode(",", $responses) . ")";
		$result = mobilAP::query($sql);
		return $this->getUserSubmissions($post_user);
	}

	public function clearEvaluations()
	{
		$sql = "DELETE FROM " . TABLE_PREFIX .  mobilAP_session::SESSION_EVALUATION_TABLE . "
				WHERE session_id='$this->session_id'";
		$result = mobilAP::query($sql);
		return true;
	}

	public function clearChat()
	{
		$sql = "DELETE FROM " . TABLE_PREFIX .  mobilAP_session::SESSION_CHAT_TABLE . "
				WHERE session_id='$this->session_id'";
		$result = mobilAP::query($sql);
		return true;
	}
	
	public function post_chat($post_text, $post_user)
	{
		if (empty($post_text)) {
			return mobilAP_Error::throwError("Text cannot be empty");
		} elseif (!$user = mobilAP_attendee::getAttendeeById($post_user)) {
			return mobilAP_Error::throwError("Please login to participate in the discussion", mobilAP_session::ERROR_NO_USER);
		} elseif (!($this->session_flags & mobilAP_session::SESSION_FLAGS_DISCUSSION)) {
			return mobilAP_Error::throwError("Discussion to this session has been disabled");
		}

		
		$ts = time();
		$sql = "INSERT INTO " . TABLE_PREFIX . mobilAP_session::SESSION_CHAT_TABLE . "
				(session_id, post_user, post_timestamp, post_text)
				VALUES
				('$this->session_id', '" . mobilAP::db_escape($post_user) . "', $ts, '" . mobilAP::db_escape($post_text) . "')";
		$result = mobilAP::query($sql);
		return true;
	}
	
	public function delete_chat($post_id)
	{
		$sql = "DELETE FROM " . TABLE_PREFIX . mobilAP_session::SESSION_CHAT_TABLE . "
				WHERE session_id='$this->session_id' AND post_id='" . mobilAP::db_escape($post_id) . "'";
		$result = mobilAP::query($sql);
		return true;
	}

	public function get_chat($last_post=0)
	{
		$sql = "SELECT post_id,post_timestamp, post_user, post_text, concat(FirstName, ' ', LastName) post_name, email FROM " . TABLE_PREFIX . mobilAP_session::SESSION_CHAT_TABLE . " c
				LEFT JOIN " . TABLE_PREFIX . mobilAP_attendee::ATTENDEE_TABLE . " u ON c.post_user=u.attendee_id
				WHERE session_id='$this->session_id' AND post_id>$last_post
				ORDER BY post_timestamp DESC";
		$result = mobilAP::query($sql);
		$chats = array();
		while ($row = mysql_fetch_assoc($result)) {
			$row['post_id'] = intval($row['post_id']);
			$row['date'] = strftime('%b %d, %Y %H:%M:%S', $row['post_timestamp']);
			$chats[] = $row;
		}
			
		return $chats;
	}

	public function setSessionTitle($session_title)
	{
		if (!empty($session_title)) {
			$this->session_title = $session_title;
			return true;
		} else {
			return false;
		}
	}

	public function setSessionAbstract($session_abstract)
	{
		$this->session_abstract = $session_abstract;
		return true;
	}
	
	public function updateSession()
	{
		$sql = sprintf("UPDATE %s%s SET
		session_title='%s', session_abstract='%s', session_flags=%d
		WHERE session_id='%s'",
		TABLE_PREFIX, mobilAP_session::SESSION_TABLE,
		mobilAP::db_escape($this->session_title), mobilAP::db_escape($this->session_abstract), $this->session_flags,
		$this->session_id);
		$result = mobilAP::query($sql);
		mobilAP::flushCache(SITE_PREFIX . '_mobilAP_schedule');				
	}
	
}

class mobilAP_session_group
{
	var $session_group_id;
	var $session_group_title;
	var $session_group_detail;
	var $schedule_items=array();
	const SESSION_GROUP_TABLE='session_groups';

	function getSessionGroupByID($session_group_id) {	
		$sql = sprintf("SELECT * FROM %s%s WHERE session_group_id=%d", TABLE_PREFIX, mobilAP_session_group::SESSION_GROUP_TABLE, $session_group_id);
		$result = mobilAP::query($sql);
		
		$session_group = null;
		if ($row = mysql_fetch_assoc($result)) {
			$session_group = new mobilAP_session_group();
			$session_group->session_group_title = $row['session_group_title'];
			$session_group->session_group_id = $row['session_group_id'];
			$session_group->session_group_detail = $row['session_group_detail'];
			$session_group->schedule_items = $session_group->getScheduleItems();
		}
		
		return $session_group;
	}
	
	function setTitle($session_group_title)
	{
		if (!empty($session_group_title)) {
			$this->session_group_title = $session_group_title;
			return true;
		} else {
			return false;
		}
	}

	function setDetail($session_group_detail)
	{
		$this->session_group_detail = $session_group_detail;
	}
	
	function updateGroup()
	{
		$sql = sprintf("UPDATE %s%s SET 
						session_group_title='%s' ,
						session_group_detail='%s'
						WHERE session_group_id=%d", TABLE_PREFIX, mobilAP_session_group::SESSION_GROUP_TABLE, mobilAP::db_escape($this->session_group_title), mobilAP::db_escape($this->session_group_detail), $this->session_group_id);
		$result = mobilAP::query($sql);
	}

	function createGroup()
	{
		if (empty($this->session_group_title)) {
			return mobilAP_Error::throwError("Session group title cannot be blank");
		}

		$sql = sprintf("INSERT INTO %s%s (session_group_title, session_group_detail) VALUES ('%s', '%s')",
						TABLE_PREFIX, mobilAP_session_group::SESSION_GROUP_TABLE, mobilAP::db_escape($this->session_group_title), mobilAP::db_escape($this->session_group_detail));
		$result = mobilAP::query($sql);
		$this->session_group_id = mysql_insert_id();
		$this->updateGroup();
		return;
	}
	
	function deleteGroup()
	{
		$tables = array(mobilAP_session_group::SESSION_GROUP_TABLE);
		foreach ($tables as $table) {
			$sql = sprintf("DELETE FROM %s%s WHERE session_group_id=%d" , TABLE_PREFIX, $table,  $this->session_group_id);
			$result = mobilAP::query($sql);
		}

		$sql = sprintf("UPDATE %s%s SET session_group_id=NULL WHERE session_group_id=%d" , TABLE_PREFIX, mobilAP::SCHEDULE_TABLE,  $this->session_group_id);
		$result = mobilAP::query($sql);
	}
	
	function getScheduleItems()
	{
		$schedule_items = array();
		$sql = sprintf("SELECT * FROM %s%s WHERE session_group_id=%d", TABLE_PREFIX, mobilAP::SCHEDULE_TABLE, $this->session_group_id);
		$result = mobilAP::query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$schedule_item = new mobilAP_schedule_item();
			$schedule_item->schedule_id = $row['schedule_id'];
			$schedule_item->start_date = strftime('%b %d, %Y %H:%M:%S', $row['start_ts']);
			$schedule_item->start_ts = $row['start_ts'];
			$schedule_item->date = strftime('%Y-%m-%d', $schedule_item->start_ts);
			$schedule_item->end_date = strftime('%b %d, %Y %H:%M:%S', $row['end_ts']);
			$schedule_item->end_ts = $row['end_ts'];
			$schedule_item->title = $row['title'];
			$schedule_item->detail = $row['detail'];
			$schedule_item->room = $row['room'];
			$schedule_item->session_id = $row['session_id'];
			$schedule_item->session_group_id = $row['session_group_id'];
			$schedule_items[] = $schedule_item;
		}
		return $schedule_items;
	}
	
	function getSessionGroups()
	{
		$sql = "SELECT * FROM " . TABLE_PREFIX . mobilAP_session_group::SESSION_GROUP_TABLE ;
		$result = mobilAP::query($sql);
		$session_groups = array();
		
		while ($row = mysql_fetch_assoc($result)) {
			$session_group = new mobilAP_session_group();
			$session_group->session_group_id = $row['session_group_id'];
			$session_group->session_group_title = $row['session_group_title'];
			$session_group->session_group_detail = $row['session_group_detail'];
			$session_group->schedule_items = $session_group->getScheduleItems();
			$session_groups[$row['session_group_id']] = $session_group;
		}
		
		return $session_groups;
	}
}

class mobilAP_session_link
{
	var $link_id;
	var $session_id;
	var $link_url;
	var $link_text;
	var $post_user;
	var $link_type;
	var $post_timestamp;
	
	function setURL($url)
	{
		if (preg_match('@http[s]?://(.+)@', $url)) {
			$this->link_url = $url;
			return true;
		} else {
			trigger_error("Invalid url $url found when setting link");
			return false;
		}
	}

	function setText($text)
	{
		if (!empty($text)) {
			$this->link_text = $text;
			return true;
		} else {
			return false;
		}
	}
	
	function updateLink()
	{
		$sql = "UPDATE " . TABLE_PREFIX . mobilAP_session::SESSION_LINK_TABLE . " SET
				link_url='" . mobilAP::db_escape($this->link_url) . "',
				link_text='" . mobilAP::db_escape($this->link_text) . "'
				WHERE link_id=$this->link_id";
		$result = mobilAP::query($sql);
	}

	function deleteLink()
	{
		$tables = array(mobilAP_session::SESSION_LINK_TABLE);
		foreach ($tables as $table)
		{
			$sql = "DELETE FROM `" . TABLE_PREFIX . $table . "` WHERE link_id=$this->link_id";
			$result = mobilAP::query($sql);
		}
		
		return true;
	}
	
	function loadLinkFromArray($arr)
	{
		$link = new mobilAP_session_link();
		$link->link_id = $arr['link_id'];
		$link->session_id = $arr['session_id'];
		$link->link_url = $arr['link_url'];
		$link->link_text = $arr['link_text'];
		$link->link_type = $arr['link_type'];
		$link->post_user = $arr['post_user'];
		$link->post_timestamp = $arr['post_timestamp'];
		return $link;
	}

}

class mobilAP_poll_question
{
	const RESPONSE_TYPE_CHOICE='C';
	const RESPONSE_TYPE_ZIP='Z';
	const RESPONSE_TYPE_='Z';
	var $question_id;
	var $session_id;
	var $index=0;
	var $question_text;
	var $question_list_text;
	var $question_minchoices=0;
	var $question_maxchoices=1;
	var $question_active=-1;
	var $response_type=mobilAP_poll_question::RESPONSE_TYPE_CHOICE;
	var $chart_type='p';
	var $responses=array();
	var $answers=array();
	
	function __toString()
	{
		return $this->question_text;
	}
	
	function __construct($session_id)
	{
		$this->session_id = $session_id;
	}
	
	function getChartTypes()
	{
		return array(
			'p'=>'Pie Chart',
			'bhs'=>'Bar Chart'
		);
			
	}
	
	function setQuestionActive($active)
	{
		$this->question_active = $active ? -1 : 0;
	}
	
	function setChartType($chart_type)
	{
		$chart_types = mobilAP_poll_question::getChartTypes();
		if (isset($chart_types[$chart_type])) {
			$this->chart_type = $chart_type;
			return true;
		} else {
			return false;
		}
	}
	
	function setText($question_text)
	{
		if (is_string($question_text) && !empty($question_text)) {
			$this->question_text = $question_text;
			return true;
		} else {
			return false;
		}
	}
	
	function setMaxChoices($max_choices)
	{
		if (intval($max_choices)) {
			$this->question_maxchoices = intval($max_choices);
			return true;
		} else {
			return false;
		}
	}

	function setMinChoices($min_choices)
	{
		if (intval($min_choices) || $min_choices==0) {
			$this->question_minchoices = intval($min_choices);
			return true;
		} else {
			return false;
		}
	}

    function getChartURL()
    {
        
        $max_label_length=15;
        
        //go through the responses, for pie charts, don't include responses with zero answers
        //max_data value represents the highest value and is used to scale the bar charts
        
        $max_data = 0;
        $data = array();
        $labels = array();
        
        foreach ($this->responses as $i=>$response) {
        	if ($this->answers[$response->response_value]>0 || $this->chart_type != 'p') {
        		$data[] = $this->answers[$response->response_value];

                if ($this->answers[$response->response_value] > $max_data) {
                    $max_data = $this->answers[$response->response_value];
                }
        		
				$labels[] = strlen($response->response_text)>$max_label_length ? $i+1 : urlencode($response->response_text);
        	}
        }

		// base url with type, size and background
        $src = 'http://chart.apis.google.com/chart?cht=' . $this->chart_type . '&chf=bg,s,00000000';

		// add the data using text encoding
		$src .='&chd=t:' . implode(",", $data);
        
        //make no more than 10 x-axis legends, use the next whole factor for the max
        $step = 0;
        do {
            $step +=2;
            $even_max = $max_data % $step ? ($max_data+($max_data % $step)) : $max_data;
            
        } while ($even_max / $step > 10);
        
        switch ($this->chart_type)
        {
            case 'p':
                $src .='&chs=280x140';
                $src .='&chl=' . implode('|', $labels);
                break;
            
            case 'bhs':
                $src .='&chs=280x' . ((count($this->responses)*33)+20);
                $src .='&chxt=x,y';
                $src .='&chds=0,' . $even_max;
                $range = array();
                for ($i=0; $i<=$even_max; $i+=$step) {
                    $range[] = $i;
                }               
                                                
                $src .='&chxl=0:|' . implode('|', $range) . '|1:|' . implode('|', array_reverse($labels));
                break;
        }
        
        return $src;
    }

	function submitAnswer($responses, $user_token)
	{
		$userIDStr = $user_token ? "'$user_token'" : 'NULL';
		$responses = is_array($responses) ? $responses : array();
		$ts = time();
		$_responses=array();
								
		if (count($responses) < $this->question_minchoices || count($responses) > $this->question_maxchoices) {
			$message = "Question should have ";
			if ($this->question_maxchoices==1) {
				$message .= "1 choice";
			} elseif ($this->question_minchoices==$this->question_maxchoices) {
				$message .= sprintf("%d choices", $this->question_maxchoices);
			} else { 
				$message .= sprintf(" between %d and %d choices", $this->question_minchoices, $this->question_maxchoices);
			}					
			
			return mobilAP_Error::throwError($message);
		} 

		if (!$user = mobilAP_attendee::getAttendeeById($user_token)) {
			return mobilAP_Error::throwError("Please login to answer this question", mobilAP_session::ERROR_NO_USER);
		}
		
		if ($user_token) {		
			$sql = "SELECT answer_id FROM " . TABLE_PREFIX . mobilAP_session::POLL_ANSWERS_TABLE . " 
				    WHERE question_id=$this->question_id AND response_userID='$user_token'";
			$result = mobilAP::query($sql);
			if (mysql_numrows($result)>0) {
				return mobilAP_Error::throwError("You have already answered this question", mobilAP_session::ERROR_USER_ALREADY_SUBMITTED);
			}
		}
		
		foreach ($responses as $response_value) {
			if ($this->is_response($response_value)) {
				$sql = "INSERT INTO " . TABLE_PREFIX . mobilAP_session::POLL_ANSWERS_TABLE . " (question_id, response_value, response_timestamp, response_userID)
				VALUES ($this->question_id, $response_value,$ts, $userIDStr)";
				mobilAP::query($sql);
			}
		}
		$this->answers = $this->getAnswers();
	}
	
	function is_response($response_value) 
	{
		foreach ($this->responses as $response) {
			if ($response->response_value ==$response_value) 
				return true;
		}
		
		return false;
	}
	
	function loadQuestionFromArray($arr)
	{
		$question = new mobilAP_poll_question($arr['session_id']);
		$question->question_id = $arr['question_id'];
		$question->index = intval($arr['index']);
		$question->question_text = $arr['question_text'];
		$question->question_list_text = $arr['question_list_text'];
		$question->question_minchoices =  intval($arr['question_minchoices']);
		$question->question_maxchoices =  intval($arr['question_maxchoices']);
		$question->question_active =  intval($arr['question_active']);
		$question->response_type =  $arr['response_type'];
		$question->chart_type =  $arr['chart_type'];
		$question->responses = $question->getResponses();
		$question->answers = $question->getAnswers();
		return $question;
		
	}
	
	function setQuestion($question_text)
	{
		if (is_string($question_text) && !empty($question_text)) {
			$this->question_text = $question_text;
			return true;
		} else {
			return false;
		}
	}

	function setQuestionListText($question_list_text)
	{
		$this->question_list_text = $question_list_text;
		return true;
	}
	
	function deleteQuestion()
	{
		$tables = array(mobilAP_session::POLL_QUESTIONS_TABLE, mobilAP_session::POLL_RESPONSES_TABLE, mobilAP_session::POLL_ANSWERS_TABLE);
		foreach ($tables as $table)
		{
			$sql = "DELETE FROM `" . TABLE_PREFIX . $table . "` WHERE question_id=$this->question_id";
			$result = mobilAP::query($sql);
		}
		
		//reindex questions
		$sql = "UPDATE " . TABLE_PREFIX . mobilAP_session::POLL_QUESTIONS_TABLE . " SET `index`=`index`-1 WHERE session_id='$this->session_id' AND `index`>$this->index";
		$result = mobilAP::query($sql);
		return true;
	}
	
	function clearAnswers()
	{
		$sql = "DELETE FROM `" . TABLE_PREFIX . mobilAP_session::POLL_ANSWERS_TABLE . "` WHERE question_id=$this->question_id";
		$result = mobilAP::query($sql);
		$this->answers = $this->getAnswers();
	}
	
	function getNextResponseIndex()
	{
		return count($this->responses);
	}

	function getNextResponseValue()
	{
		$sql = "SELECT MAX(response_value) response_value FROM " . TABLE_PREFIX . mobilAP_session::POLL_RESPONSES_TABLE . " 
				WHERE question_id=$this->question_id";
		$result = mobilAP::query($sql);
		
		$row = mysql_fetch_assoc($result);
		if ($row['response_value']) {
			$next = intval($row['response_value'])+1;
		} else {
			$next = 1;
		}
		
		return $next;
	}

	function getQuestionById($question_id)
	{
		$sql = "SELECT * FROM " . TABLE_PREFIX . mobilAP_session::POLL_QUESTIONS_TABLE . " 
				WHERE 
				question_id='" . mobilAP::db_escape($question_id) . "'";
		$result = mobilAP::query($sql);
		$question = false;
		if ($row=mysql_fetch_assoc($result)) {
			$question = mobilAP_poll_question::loadQuestionFromArray($row);
		}
		return $question;		
	}

	function removeResponse($response_value)
	{
		if ($response = $this->getResponseByValue($response_value)) {

			$index = $response->index;
			$result = $response->deleteResponse();
			if (mobilAP_Error::isError($result)) {
				return $result;
			}
			
			$sql = "UPDATE " . TABLE_PREFIX . mobilAP_session::POLL_RESPONSES_TABLE . " SET 
				`index`=`index`-1 
				WHERE question_id=$this->question_id AND `index`>$index 
				ORDER BY `index` ASC";
			$result = mobilAP::query($sql);
			$this->responses = $this->getResponses();
			$this->answers = $this->getAnswers();
			return true;
		} else {
			return mobilAP_Error::throwError("There is no response $response_value for this question");
		}
	}
	
	function addResponse($response_text)
	{
		if (empty($response_text)) {
			return mobilAP_Error::throwError("Invalid response");
		}
		
		$index = $this->getNextResponseIndex();
		$response_value = $this->getNextResponseValue();
		
		
		$sql = "INSERT INTO " . TABLE_PREFIX . mobilAP_session::POLL_RESPONSES_TABLE . " (question_id, `index`, response_value, response_text)
				VALUES
				($this->question_id, $index, $response_value,'" . mobilAP::db_escape($response_text) . "')";
		$result = mobilAP::query($sql, true);
		$this->responses = $this->getResponses();
		$this->answers = $this->getAnswers();

		if (mobilAP_Error::isError($result)) {
			if ($result->getCode()==DB_ERROR_ALREADY_EXISTS) {
				$result = mobilAP_Error::throwError("$response_text already exists", DB_ERROR_ALREADY_EXISTS);
			} else {
			}
			return $result;
		}
		return true;
	}
	
	function &getResponseByValue($response_value)
	{
		$return = false;
		foreach ($this->responses as $index=>$response) {
			if ($response->response_value==$response_value) {
				return $response;
			}
		}
		return $return;
	}
	
	function getResponses()
	{
		$sql = "SELECT * FROM " . TABLE_PREFIX . mobilAP_session::POLL_RESPONSES_TABLE . " 
		WHERE question_id=$this->question_id ORDER BY `index`";
		$result = mobilAP::query($sql);
		$responses = array();
		while ($row = mysql_fetch_assoc($result)) {
			$row['session_id'] = $this->session_id;
			$responses[] = mobilAP_poll_response::loadResponseFromArray($row);
		}
		
		return $responses;
	}
	
	function getAllAnswers()
	{
		$sql = "SELECT pa.*, a.FirstName, a.LastName, a.email FROM " . TABLE_PREFIX . mobilAP_session::POLL_ANSWERS_TABLE . " pa
				LEFT JOIN " . TABLE_PREFIX . mobilAP_attendee::ATTENDEE_TABLE . " a ON pa.response_userID=a.attendee_id
				WHERE question_id=$this->question_id
				ORDER BY response_value, LastName, FirstName
				";
		$result = mobilAP::query($sql);
		$answers = array();
		while ($row = mysql_fetch_assoc($result)) {
			$answers[$row['response_value']][] = $row;
		}
		
		return $answers;
	}

	function getAnswers()
	{
		$answers = array('total'=>0);
		foreach ($this->responses as $index=>$response) {
			$answers[$response->response_value] = 0;
		}
		
		if (count($this->responses)>0) {
			$sql = "SELECT count(*) count, response_value FROM " . TABLE_PREFIX . mobilAP_session::POLL_ANSWERS_TABLE . " 
			WHERE question_id=$this->question_id
			GROUP BY response_value";
			$result = mobilAP::query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$answers['total']+=$row['count'];
				$answers[$row['response_value']]+=$row['count'];
			}
		}		
		
		return $answers;
	}
	
	function updateQuestion()
	{
		$sql = "UPDATE " . TABLE_PREFIX . mobilAP_session::POLL_QUESTIONS_TABLE  . " SET
				question_text='" . mobilAP::db_escape($this->question_text) . "',
				question_list_text='" . mobilAP::db_escape($this->question_list_text) . "',
				question_minchoices=$this->question_minchoices,
				question_maxchoices=$this->question_maxchoices,
				question_active=$this->question_active,
				chart_type='$this->chart_type',
				response_type='$this->response_type'
				WHERE question_id='$this->question_id'";
		$result = mobilAP::query($sql);
		return true;
	}

	
}

class mobilAP_poll_response
{
	var $session_id;
	var $question_id;
	var $index;
	var $response_value;
	var $response_text;

	function __toString()
	{
		return $this->response_text;
	}
	
	function deleteResponse()
	{
		$tables = array(mobilAP_session::POLL_RESPONSES_TABLE, mobilAP_session::POLL_ANSWERS_TABLE);
		foreach ($tables as $table)
		{
			$sql = "DELETE FROM `" . TABLE_PREFIX . $table . "` WHERE question_id=$this->question_id AND response_value=$this->response_value";
			$result = mobilAP::query($sql);
		}
		return true;
	}
	
	function setResponseText($response_text)
	{
		if (empty($response_text)) {
			return mobilAP_Error::throwError("Invalid response");
		}
		$this->response_text = $response_text;
		return true;
	}

	function updateResponse()
	{
		$sql = "UPDATE " . TABLE_PREFIX . mobilAP_session::POLL_RESPONSES_TABLE . " SET
			response_text='" . mobilAP::db_escape($this->response_text) . "'
			WHERE question_id=$this->question_id AND response_value=$this->response_value";
		$result = mobilAP::query($sql);
		return true;
	}

	function loadResponseFromArray($arr)
	{
		$response = new mobilAP_poll_response();
		$response->session_id = $arr['session_id'];
		$response->question_id = intval($arr['question_id']);
		$response->index = intval($arr['index']);
		$response->response_value = intval($arr['response_value']);
		$response->response_text = $arr['response_text'];
		return $response;
	}
	
}

class mobilAP_webuser
{
	const USER_NOT_FOUND=-1;
	const USER_ALREADY_LOGGED_IN=-2;
	const USER_LOGIN_FAILURE=-3;
	const USER_REQUIRES_PASSWORD=-4;
	const USER_ADMIN_LOGIN_FAILURE=-5;
	var $mobilAP_userID;
    var $user;
    
    function __construct()
    {
		if (!isset($_SESSION)) {
    		session_start();
    	}
    	
		$_SESSION['last_ping'] = isset($_SESSION['ping']) ? $_SESSION['ping'] : 0;
		$_SESSION['ping'] = time();
    	    	
        $val = $this->_getSession();

        $this->sid = session_id();
        $this->_setSession();
    }
	
	function getUserToken()
	{
		if ($user = $this->getUser()) {
			return $user->getUserID();
		}
		
		return null;
	}

	function getUser()
    {
    	if (!$this->user) {
    		$this->user = mobilAP_attendee::getAttendeeById($this->getUserID());
    	}
    	
    	return $this->user;
    }

    function is_LoggedIn()
    {
        return $this->getUserID() ? 1 : 0;
    }   
    
    function getUserID()
    {
		return $this->mobilAP_userID;
    }
    
    function setmobilAP_userID($userID)
    {
    	$this->mobilAP_userID = strtolower($userID);
    	$user = $this->getUser();
    	return true;
    }
    
    function _setSession()
    {
    	$_SESSION[SITE_PREFIX . '_mobilAP_userID'] = $this->getUserID();
    	//set expiration to 1 week
		setCookie(SITE_PREFIX . '_mobilAP_userID', $this->getUserID(), time()+604800, getConfig('mobilAP_base_path'));
    }    

    function _getSession()
    {
    	
        $mobilAP_userID = isset($_COOKIE[SITE_PREFIX . '_mobilAP_userID']) ? $_COOKIE[SITE_PREFIX . '_mobilAP_userID'] : '';
        
        if ($mobilAP_userID) {

            $this->setmobilAP_userID($mobilAP_userID);
            
            if ($this->is_loggedIn()) {
				if (!$user = $this->getUser()) {
					$this->logout();
					trigger_error("User $mobilAP_userID not found", E_USER_WARNING);
					return mobilAP_Error::throwError("User $mobilAP_userID not found");
				}
				
			}			
            
            return true;
        } else {
            return true;
        }
                
    }
    
    function login($userID, $pword, $mode=null)
    {
        $userID = strtolower($userID);
        if ($this->is_LoggedIn()) {
        	return mobilAP_Error::throwError('User already logged in', mobilAP_webuser::USER_ALREADY_LOGGED_IN);
        } elseif (!$user = mobilAP_attendee::getAttendeeByID($userID)) {
        	return mobilAP_Error::throwError("User $userID is not a user", mobilAP_webuser::USER_NOT_FOUND);
        } else {
			
            if ($login = mobilAP::Auth($userID, $pword, $mode)) {

                $this->setmobilAP_userID($user->email);

                //set login times
                $sql = "UPDATE " . TABLE_PREFIX . mobilAP_attendee::ATTENDEE_TABLE . " u SET 
                	u.login_last=login_now 
                	WHERE 
                	u.attendee_id='" . $this->getUserID() . "' AND 
                	!isnull(u.login_now)";
                	
                $result = mobilAP::query($sql);

				$sql = "UPDATE " . TABLE_PREFIX . mobilAP_attendee::ATTENDEE_TABLE . " u SET 
						u.login_now= " . time() . "
						WHERE 
						u.attendee_id='" . $this->getUserID() . "'";
	
				$result = mobilAP::query($sql);                
				session_regenerate_id(true);
                $this->_setSession();
                                
                return true;
                
            } elseif (getConfig('USE_ADMIN_PASSWORDS') && $user->admin && $pword==getConfig('default_password')) {
				return mobilAP_Error::throwError("This account requires a password", mobilAP_webuser::USER_REQUIRES_PASSWORD);
            } elseif (getConfig('USE_ADMIN_PASSWORDS') && $user->admin) {
				return mobilAP_Error::throwError("Login Failure.", mobilAP_webuser::USER_ADMIN_LOGIN_FAILURE);
            } else {
				return mobilAP_Error::throwError("Login Failure.", mobilAP_webuser::USER_LOGIN_FAILURE);
            }
        }
        
        return $this->getResult();
    }
    
    function _reset()
    {
        unset($_SESSION[SITE_PREFIX . '_mobilAP_userID']);
		setCookie(SITE_PREFIX . '_mobilAP_userID', '', mktime(0,0,0,10,11,1977), getConfig('mobilAP_base_path'));
		
        $this->mobilAP_userID = null;
		session_regenerate_id(true);
        $this->_setSession();
    }
    
    function logout()
    {
    	if ($this->is_LoggedIn()) {
			$sql = "UPDATE " . TABLE_PREFIX . mobilAP_attendee::ATTENDEE_TABLE . " SET 
					login_last=login_now, 
					login_now=NULL 
					WHERE attendee_id='" . $this->getUserID() ."'";
			$result = mobilAP::query($sql);
    	}
    	
    	$this->_reset();
		session_regenerate_id(true);

        return true;
    }

}

class mobilAP_Error
{
	public $error_message;
	public $error_code;
	public $error_userinfo;
	public $message;
	public $code;
	public $userinfo;
	
	public function __toString()
	{
		if ($this->code) {
			return sprintf('%s: %d (%s)', __CLASS__, $this->code, $this->message);
		} else {
			return sprintf('%s: %s', __CLASS__, $this->message);
		}		
	}

	public function __construct($message=null, $code=null, $userinfo=null) {
        $this->setMessage($message);
        $this->setCode($code);
        $this->setUserInfo($userinfo);
    }

    public static function throwError($message = null, $code = null, $userinfo = null)
    {
    	$a = new mobilAP_Error($message, $code, $userinfo);
    	return $a;
    }
    
    public static function isError($data)
    {
        return is_a($data, 'mobilAP_Error');
    }    

	public function getMessage()
	{
		return $this->message;
	}

	public function setMessage($message)
	{
		$this->message = $message;
		$this->error_message = $message;
	}

	public function setCode($code)
	{
		$this->code = $code;
		$this->error_code = $code;
	}

	public function setUserInfo($userinfo)
	{
		$this->userinfo = $userinfo;
		$this->error_userinfo = $userinfo;
	}

	public function getCode()
	{
		return $this->code;
	}

    public function getUserInfo()
    {
        return $this->userinfo;
    }
}

class mobilAP_announcement
{
	const ANNOUNCEMENT_TABLE='announcements';
	const ANNOUNCEMENT_READ_TABLE='announcements_read';
	var $announcement_id;
	var $announcement_title;
	var $announcement_timestamp;
	var $attendee_id;
	var $announcement_text;

	function postAnnouncement($attendee_id)
	{
		if (!$attendee = mobilAP_attendee::getAttendeeById($attendee_id)) {
			return mobilAP_Error::throwError("Invalid user $attendee_id");
		}

		$sql = sprintf("INSERT INTO %s%s (announcement_title, announcement_timestamp, attendee_id, announcement_text)
					    VALUES ('%s', %d, '%s', '%s')", TABLE_PREFIX, mobilAP_announcement::ANNOUNCEMENT_TABLE, mobilAP::db_escape($this->announcement_title), time(), $attendee->getUserID(), mobilAP::db_escape($this->announcement_text));
		$result = mobilAP::query($sql);
		$this->announcement_id = mysql_insert_id();
	}
	
	function updateAnnouncement($attendee_id)
	{
		$sql = sprintf("UPDATE %s%s SET
						announcement_title='%s', announcement_text='%s'
						WHERE announcement_id=%d",
						TABLE_PREFIX,
					    mobilAP_announcement::ANNOUNCEMENT_TABLE, mobilAP::db_escape($this->announcement_title), mobilAP::db_escape($this->announcement_text), $this->announcement_id);
		$result = mobilAP::query($sql);
	}

	function deleteAnnouncement($attendee_id)
	{
		$tables = array(mobilAP_announcement::ANNOUNCEMENT_TABLE, mobilAP_announcement::ANNOUNCEMENT_READ_TABLE);
		foreach ($tables as $table) {
			$sql = sprintf("DELETE FROM %s%s
							WHERE announcement_id=%d",
					    	TABLE_PREFIX, $table, $this->announcement_id);
			$result = mobilAP::query($sql);
		}
	}
	
	function setTitle($title)
	{
		if (!empty($title)) {
			$this->announcement_title = $title;
			return true;
		} else {
			return false;
		}
	}
	
	function setText($text)
	{
		if (!empty($text)) {
			$this->announcement_text = $text;
			return true;
		} else {
			return false;
		}
	}
	
	
	function loadFromArray($arr)
	{
		$this->announcement_id = $arr['announcement_id'];
		$this->announcement_title = $arr['announcement_title'];
		$this->announcement_timestamp = $arr['announcement_timestamp'];
		$this->attendee_id = $arr['attendee_id'];
		$this->announcement_text = $arr['announcement_text'];
	}	

	function getAnnouncementByID($announcement_id)
	{
		$sql = sprintf("SELECT * FROM %s%s WHERE announcement_id=%d", TABLE_PREFIX, mobilAP_announcement::ANNOUNCEMENT_TABLE, $announcement_id);
		$result = mobilAP::query($sql);
		if ($row = mysql_fetch_assoc($result)) {
			$announcement = new mobilAP_announcement();
			$announcement->loadFromArray($row);
		} else {
			$announcement = false;
		}
		return $announcement;
	}

	function getAnnouncements()
	{
		$sql = sprintf("SELECT * FROM %s%s ORDER BY announcement_timestamp DESC", TABLE_PREFIX, mobilAP_announcement::ANNOUNCEMENT_TABLE);
		$result = mobilAP::query($sql);
		$announcements=array();
		while ($row = mysql_fetch_assoc($result)) {
			$announcement = new mobilAP_announcement();
			$announcement->loadFromArray($row);
			$announcements[] = $announcement;
		}
		return $announcements;
	}

	function hasRead($userID)
	{
		if ($attendee = mobilAP_attendee::getAttendeeById($userID)) {
			$sql = sprintf("SELECT * FROM %s%s WHERE announcement_id=%d AND attendee_id='%s'", TABLE_PREFIX,
						   mobilAP_announcement::ANNOUNCEMENT_READ_TABLE, $this->announcement_id, $attendee->getUserID());
			$result = mobilAP::query($sql);
			if ($row = mysql_fetch_assoc($result)) {
				return $row['read_timestamp'];
			} else {
				return false;
			}
		}
	}

	function readAnnouncement($userID)
	{
		if ($attendee = mobilAP_attendee::getAttendeeById($userID)) {
			$sql = sprintf("INSERT INTO %s%s (announcement_id, attendee_id, read_timestamp)
							VALUES (%d, '%s', %d)", TABLE_PREFIX, mobilAP_announcement::ANNOUNCEMENT_READ_TABLE, $this->announcement_id, $attendee->getUserID(), time());
			$result = mobilAP::query($sql, true);
			return mobilAP_Error::isError($result) ? $result : true;
		} else {
			return mobilAP_Error::throwError("Invalid attendee $userID");
		}
	}

}

class mobilAP_evaluation_question
{
	var $question_index;
	var $question_text;
	var $question_response_type='M';
	const RESPONSE_TYPE_TEXT='T';
	const RESPONSE_TYPE_CHOICES='M';
	
	function removeResponse($response_index)
	{
		$sql = sprintf("DELETE FROM %s%s WHERE question_index=%d AND response_index=%d",
		TABLE_PREFIX, mobilAP::EVALUATION_QUESTION_RESPONSE_TABLE, $this->question_index, $response_index);
		$result = mobilAP::query($sql);
		
		$sql = sprintf("UPDATE %s%s SET response_index=response_index-1, response_value=response_value-1 WHERE question_index=%d AND response_index>%d ORDER BY response_index ASC",
		TABLE_PREFIX, mobilAP::EVALUATION_QUESTION_RESPONSE_TABLE, $this->question_index, $response_index);
		$result = mobilAP::query($sql);
	}
	
	function addResponse($response_text)
	{
		if (empty($response_text)) {
			return mobilAP_Error::throwError("Response cannot be empty");
		}
		
		$responses = $this->getResponses();
		$response_index = count($responses);
		$response_value = count($responses)+1;
		$sql = sprintf("INSERT INTO %s%s (question_index, response_index, response_text, response_value) 
		VALUES (%d, %d, '%s', %d)", TABLE_PREFIX, mobilAP::EVALUATION_QUESTION_RESPONSE_TABLE, $this->question_index, $response_index, mobilAP::db_escape($response_text), $response_value);
		$result = mobilAP::query($sql);
	}
	
	function deleteQuestion()
	{
		$questions = mobilAP::getEvaluationQuestions();
		$sql = sprintf("DELETE FROM %s%s WHERE question_index=%d",
		TABLE_PREFIX, mobilAP::EVALUATION_QUESTION_TABLE, $this->question_index);
		$result = mobilAP::query($sql);
		$sql = sprintf("DELETE FROM %s%s WHERE question_index=%d",
		TABLE_PREFIX, mobilAP::EVALUATION_QUESTION_RESPONSE_TABLE, $this->question_index);
		$result = mobilAP::query($sql);
		$sql = sprintf("ALTER TABLE %s%s DROP q%d", TABLE_PREFIX, mobilAP_session::SESSION_EVALUATION_TABLE, $this->question_index);
		$result = mobilAP::query($sql);
		unset($questions[$this->question_index]);
		for ($i=$this->question_index+1; $i<=count($questions); $i++) {
			$sql = sprintf("ALTER TABLE %s%s CHANGE q%d q%d %s", TABLE_PREFIX, mobilAP_session::SESSION_EVALUATION_TABLE, $i, $i-1, $questions[$i]->getColumnType());
			$result = mobilAP::query($sql);
			$sql = sprintf("UPDATE %s%s SET question_index=%d WHERE question_index=%d", TABLE_PREFIX, mobilAP::EVALUATION_QUESTION_TABLE, $i-1, $i);
			$result = mobilAP::query($sql);
		}
	}
	
	function addQuestion()
	{
		$questions = mobilAP::getEvaluationQuestions();
		$this->question_index = count($questions);
		$sql = sprintf("INSERT INTO %s%s (question_index, question_text, question_response_type)
		VALUES (%d, '%s', '%s')", TABLE_PREFIX, mobilAP::EVALUATION_QUESTION_TABLE, $this->question_index, mobilAP::db_escape($this->question_text), $this->question_response_type);
		$result = mobilAP::query($sql);
		$sql = sprintf("ALTER TABLE %s%s ADD q%d %s", TABLE_PREFIX, mobilAP_session::SESSION_EVALUATION_TABLE, $this->question_index, $this->getColumnType());
		$result = mobilAP::query($sql);
		return true;
	}

	function updateQuestion()
	{
		$sql = sprintf("UPDATE %s%s SET question_text='%s', question_response_type='%s' WHERE question_index=%d", 
		TABLE_PREFIX, mobilAP::EVALUATION_QUESTION_TABLE, mobilAP::db_escape($this->question_text), $this->question_response_type, $this->question_index);
		$result = mobilAP::query($sql);
		return true;
	}
	
	private function getColumnType()
	{
		switch ($this->question_response_type)
		{
			case mobilAP_evaluation_question::RESPONSE_TYPE_TEXT:
				return 'text';
			case mobilAP_evaluation_question::RESPONSE_TYPE_CHOICES:
				return 'tinyint(4) unsigned';
		}
	}
	
	function setQuestionText($question_text)
	{
		if (is_string($question_text) && !empty($question_text)) {
			$this->question_text = $question_text;
			return true;
		} else {
			return false;
		}
	}

	function setQuestionResponseType($question_response_type)
	{
		switch ($question_response_type)
		{
			case mobilAP_evaluation_question::RESPONSE_TYPE_TEXT:
			case mobilAP_evaluation_question::RESPONSE_TYPE_CHOICES:

				if (!is_null($this->question_index) && ($this->question_response_type != $question_response_type)) {
					$this->question_response_type = $question_response_type;
					$sql = sprintf("ALTER TABLE %s%s CHANGE q%d q%d %s", TABLE_PREFIX, mobilAP_session::SESSION_EVALUATION_TABLE, $this->question_index, $this->question_index, $this->getColumnType());
					$result = mobilAP::query($sql);
				}
			
				$this->question_response_type = $question_response_type;
				if (!is_null($this->question_index)) {
					$sql = sprintf("UPDATE %s%s SET question_response_type='%s' WHERE question_index=%d", 
					TABLE_PREFIX, mobilAP::EVALUATION_QUESTION_TABLE, $this->question_response_type, $this->question_index);
					$result = mobilAP::query($sql);
				}
				
				return true;
				break;
		}
		
		return false;
	}

	function getQuestionByIndex($question_index)
	{
		$sql = sprintf("SELECT * FROM %s%s WHERE question_index=%d", TABLE_PREFIX, mobilAP::EVALUATION_QUESTION_TABLE, $question_index);
		$result = mobilAP::query($sql);
		if ($row = mysql_fetch_assoc($result)) {
			$question = new mobilAP_evaluation_question();
			$question->question_index = intval($row['question_index']);
			$question->question_text = $row['question_text'];
			$question->question_response_type = $row['question_response_type'];
		} else {
			$question = false;
		}
		
		return $question;
	}
	
	function getResponses()
	{
		if ($this->question_response_type == mobilAP_evaluation_question::RESPONSE_TYPE_TEXT) {
			return false;
		}
		
		$sql = sprintf("SELECT * FROM %s%s WHERE question_index=%d ORDER BY response_index", TABLE_PREFIX, mobilAP::EVALUATION_QUESTION_RESPONSE_TABLE, $this->question_index);
		$result = mobilAP::query($sql);
		$responses = array();
		while ($row = mysql_fetch_assoc($result)) {
			$responses[] = $row;
		}
		
		return $responses;
	}
}

?>