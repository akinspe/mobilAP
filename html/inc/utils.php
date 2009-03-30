<?php

/*

* Copyright (c) 2008, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

class Utils
{
	function is_validEmail($emailAddress) 
	{
		if (!is_string($emailAddress)) {
			return false;
		}
		$pattern = "/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/";
		return preg_match($pattern, $emailAddress);
	}
	
	function is_validURL($url)
	{
		if (!is_string($url)) {
			return false;
		}
		$pattern = "@^(http\:\/\/[a-zA-Z0-9_\-]+(?:\.[a-zA-Z0-9_\-]+)*\.[a-zA-Z]{2,4}(?:\/[a-zA-Z0-9_]+)*(?:\/[a-zA-Z0-9_]+\.[a-zA-Z]{2,4}(?:\?[a-zA-Z0-9_]+\=[a-zA-Z0-9_]+)?)?(?:\&[a-zA-Z0-9_]+\=[a-zA-Z0-9_]+)*)$@";
		return preg_match($pattern, $url);
	}

	function make_timestamp($string)
	{
		if(empty($string)) {
			// use "now":
			$time = time();
	
		} elseif (preg_match('/^\d{14}$/', $string)) {
			// it is mysql timestamp format of YYYYMMDDHHMMSS?            
			$time = mktime(substr($string, 8, 2),substr($string, 10, 2),substr($string, 12, 2),
						   substr($string, 4, 2),substr($string, 6, 2),substr($string, 0, 4));
			
		} elseif (is_numeric($string)) {
			// it is a numeric string, we handle it as timestamp
			$time = (int)$string;
			
		} else {
			// strtotime should handle it
			$time = strtotime($string);
			if ($time == -1 || $time === false) {
				// strtotime() was not able to parse $string, use "now":
				$time = time();
			}
		}
		return $time;
	
	}

	function html_select_date($params)
	{
		/* Default values. */
		$prefix          = "Date_";
		$start_year      = strftime("%Y");
		$end_year        = $start_year;
		$display_days    = true;
		$display_months  = true;
		$display_years   = true;
		$month_format    = "%B";
		/* Write months as numbers by default  GL */
		$month_value_format = "%m";
		$day_format      = "%02d";
		/* Write day values using this format MB */
		$day_value_format = "%d";
		$year_as_text    = false;
		/* Display years in reverse order? Ie. 2000,1999,.... */
		$reverse_years   = false;
		/* Should the select boxes be part of an array when returned from PHP?
		   e.g. setting it to "birthday", would create "birthday[Day]",
		   "birthday[Month]" & "birthday[Year]". Can be combined with prefix */
		$field_array     = null;
		/* <select size>'s of the different <select> tags.
		   If not set, uses default dropdown. */
		$day_size        = null;
		$month_size      = null;
		$year_size       = null;
		/* Unparsed attributes common to *ALL* the <select>/<input> tags.
		   An example might be in the template: all_extra ='class ="foo"'. */
		$all_extra       = null;
		/* Separate attributes for the tags. */
		$day_extra       = null;
		$month_extra     = null;
		$year_extra      = null;
		/* Order in which to display the fields.
		   "D" -> day, "M" -> month, "Y" -> year. */
		$field_order     = 'MDY';
		/* String printed between the different fields. */
		$field_separator = "\n";
		$time = time();
		$all_empty       = null;
		$day_empty       = null;
		$month_empty     = null;
		$year_empty      = null;
		$extra_attrs     = '';
		
		$month_selected = null;
		$day_selected = null;
		$year_selected = null;
		
		foreach ($params as $_key=>$_value) {
			switch ($_key) {
				case 'prefix':
				case 'time':
				case 'start_year':
				case 'end_year':
				case 'month_format':
				case 'day_format':
				case 'day_value_format':
				case 'field_array':
				case 'day_size':
				case 'month_size':
				case 'year_size':
				case 'all_extra':
				case 'day_extra':
				case 'month_extra':
				case 'year_extra':
				case 'field_order':
				case 'field_separator':
				case 'month_value_format':
				case 'month_empty':
				case 'day_empty':
				case 'year_empty':
				case 'month_selected':
				case 'day_selected':
				case 'year_selected':
					$$_key = (string)$_value;
					break;
	
				case 'all_empty':
					$$_key = (string)$_value;
					$day_empty = $month_empty = $year_empty = $all_empty;
					break;
	
				case 'display_days':
				case 'display_months':
				case 'display_years':
				case 'year_as_text':
				case 'reverse_years':
					$$_key = (bool)$_value;
					break;
	
				default:
					if(!is_array($_value)) {
						$extra_attrs .= ' '.$_key.'="'.smarty_function_escape_special_chars($_value).'"';
					} else {
						$smarty->trigger_error("html_select_date: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
					}
					break;
			}
		}
	
		if (preg_match('!^-\d+$!', $time)) {
			// negative timestamp, use date()
			$time = date('Y-m-d', $time);
		}
		// If $time is not in format yyyy-mm-dd
		if (preg_match('/^(\d{0,4}-\d{0,2}-\d{0,2})/', $time, $found)) {
			$time = $found[1];
		} else {
			// use smarty_make_timestamp to get an unix timestamp and
			// strftime to make yyyy-mm-dd
			$time = strftime('%Y-%m-%d', utils::make_timestamp($time));
		}
		// Now split this in pieces, which later can be used to set the select
		$time = explode("-", $time);
		
		if (!is_null($month_selected)) {
			$time[1] = $month_selected;
		}
	
		if (!is_null($day_selected)) {
			$time[2] = $day_selected;
		}
	
		if (!is_null($year_selected)) {
			$time[0] = $year_selected;
		}
		
		// make syntax "+N" or "-N" work with start_year and end_year
		if (preg_match('!^(\+|\-)\s*(\d+)$!', $end_year, $match)) {
			if ($match[1] == '+') {
				$end_year = strftime('%Y') + $match[2];
			} else {
				$end_year = strftime('%Y') - $match[2];
			}
		}
		if (preg_match('!^(\+|\-)\s*(\d+)$!', $start_year, $match)) {
			if ($match[1] == '+') {
				$start_year = strftime('%Y') + $match[2];
			} else {
				$start_year = strftime('%Y') - $match[2];
			}
		}
		if (strlen($time[0]) > 0) {
			if ($start_year > $time[0] && !isset($params['start_year'])) {
				// force start year to include given date if not explicitly set
				$start_year = $time[0];
			}
			if($end_year < $time[0] && !isset($params['end_year'])) {
				// force end year to include given date if not explicitly set
				$end_year = $time[0];
			}
		}
	
		$field_order = strtoupper($field_order);
	
		$html_result = $month_result = $day_result = $year_result = "";
	
		if ($display_months) {
			$month_names = array();
			$month_values = array();
			if(isset($month_empty)) {
				$month_names[''] = $month_empty;
				$month_values[''] = '';
			}
			for ($i = 1; $i <= 12; $i++) {
				$month_names[$i] = strftime($month_format, mktime(0, 0, 0, $i, 1, $time[0]));
				$month_values[$i] = strftime($month_value_format, mktime(0, 0, 0, $i, 1, $time[0]));
			}
	
			$month_result .= '<select name=';
			if (null !== $field_array){
				$month_result .= '"' . $field_array . '[' . $prefix . 'Month]"';
			} else {
				$month_result .= '"' . $prefix . 'Month"';
			}
			
			$month_result .= ' id=';
	
			if (null !== $field_array){
				$month_result .= '"' . $field_array . '_' . $prefix . 'Month"';
			} else {
				$month_result .= '"' . $prefix . 'Month"';
			}
			
			if (null !== $month_size){
				$month_result .= ' size="' . $month_size . '"';
			}
			if (null !== $month_extra){
				$month_result .= ' ' . $month_extra;
			}
			if (null !== $all_extra){
				$month_result .= ' ' . $all_extra;
			}
			$month_result .= $extra_attrs . '>'."\n";
	
			$month_result .= utils::html_options(array('output'     => $month_names,
																'values'     => $month_values,
																'selected'   => (int)$time[1] ? strftime($month_value_format, mktime(0, 0, 0, (int)$time[1], 1, 2000)) : '',
																'print_result' => false));
			$month_result .= '</select>';
		}
	
		if ($display_days) {
			$days = array();
			if (isset($day_empty)) {
				$days[''] = $day_empty;
				$day_values[''] = '';
			}
			for ($i = 1; $i <= 31; $i++) {
				$days[] = sprintf($day_format, $i);
				$day_values[] = sprintf($day_value_format, $i);
			}
	
			$day_result .= '<select name=';
			if (null !== $field_array){
				$day_result .= '"' . $field_array . '[' . $prefix . 'Day]"';
			} else {
				$day_result .= '"' . $prefix . 'Day"';
			}
	
			$day_result .= ' id=';
			if (null !== $field_array){
				$day_result .= '"' . $field_array . '_' . $prefix . 'Day"';
			} else {
				$day_result .= '"' . $prefix . 'Day"';
			}
	
			if (null !== $day_size){
				$day_result .= ' size="' . $day_size . '"';
			}
			if (null !== $all_extra){
				$day_result .= ' ' . $all_extra;
			}
			if (null !== $day_extra){
				$day_result .= ' ' . $day_extra;
			}
			$day_result .= $extra_attrs . '>'."\n";
			$day_result .= utils::html_options(array('output'     => $days,
															  'values'     => $day_values,
															  'selected'   => $time[2],
															  'print_result' => false));
			$day_result .= '</select>';
		}
	
		if ($display_years) {
			if (null !== $field_array){
				$year_name = $field_array . '[' . $prefix . 'Year]';
				$year_id = $field_array . '_' . $prefix . 'Year';
			} else {
				$year_name = $prefix . 'Year';
				$year_id = $prefix . 'Year';
			}
			if ($year_as_text) {
				$year_result .= '<input type="text" name="' . $year_name . '" value="' . $time[0] . '" size="4" maxlength="4"';
				if (null !== $all_extra){
					$year_result .= ' ' . $all_extra;
				}
				if (null !== $year_extra){
					$year_result .= ' ' . $year_extra;
				}
				$year_result .= ' />';
			} else {
				$years = range((int)$start_year, (int)$end_year);
				if ($reverse_years) {
					rsort($years, SORT_NUMERIC);
				} else {
					sort($years, SORT_NUMERIC);
				}
				$yearvals = $years;
				if(isset($year_empty)) {
					array_unshift($years, $year_empty);
					array_unshift($yearvals, '');
				}
				$year_result .= '<select name="' . $year_name . '" id="' . $year_id . '"';
				if (null !== $year_size){
					$year_result .= ' size="' . $year_size . '"';
				}
				if (null !== $all_extra){
					$year_result .= ' ' . $all_extra;
				}
				if (null !== $year_extra){
					$year_result .= ' ' . $year_extra;
				}
				$year_result .= $extra_attrs . '>'."\n";
				$year_result .= utils::html_options(array('output' => $years,
																   'values' => $yearvals,
																   'selected'   => $time[0],
																   'print_result' => false));
				$year_result .= '</select>';
			}
		}
	
		// Loop thru the field_order field
		for ($i = 0; $i <= 2; $i++){
			$c = substr($field_order, $i, 1);
			switch ($c){
				case 'D':
					$html_result .= $day_result;
					break;
	
				case 'M':
					$html_result .= $month_result;
					break;
	
				case 'Y':
					$html_result .= $year_result;
					break;
			}
			// Add the field seperator
			if($i != 2) {
				$html_result .= $field_separator;
			}
		}
	
		return $html_result;
	}
	
	function html_select_time($params)
	{
		/* Default values. */
		$prefix             = "Time_";
		$time               = time();
		$display_hours      = true;
		$display_minutes    = true;
		$display_seconds    = true;
		$display_meridian   = true;
		$use_24_hours       = true;
		$minute_interval    = 1;
		$second_interval    = 1;
		/* Should the select boxes be part of an array when returned from PHP?
		   e.g. setting it to "birthday", would create "birthday[Hour]",
		   "birthday[Minute]", "birthday[Seconds]" & "birthday[Meridian]".
		   Can be combined with prefix. */
		$field_array        = null;
		$all_extra          = null;
		$hour_extra         = null;
		$minute_extra       = null;
		$second_extra       = null;
		$meridian_extra     = null;
	/* mod pete akins. allow_blank includes a "--" at the top of each label and if there is no "time", it's the default */
		$allow_blank        = false;
		$extra_attrs     = '';
	/* end allow_blank mod */
	
		foreach ($params as $_key=>$_value) {
			switch ($_key) {
				case 'prefix':
				case 'time':
				case 'field_array':
				case 'all_extra':
				case 'hour_extra':
				case 'minute_extra':
				case 'second_extra':
				case 'meridian_extra':
					$$_key = (string)$_value;
					break;
	
				case 'display_hours':
				case 'display_minutes':
				case 'display_seconds':
				case 'display_meridian':
				case 'use_24_hours':
				case 'allow_blank':
					$$_key = (bool)$_value;
					break;
	
				case 'minute_interval':
				case 'second_interval':
					$$_key = (int)$_value;
					break;
	
				default:
					if(!is_array($_value)) {
						$extra_attrs .= ' '.$_key.'="'.htmlspecialchars($_value).'"';
					} else {
						trigger_error("html_select_time: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
					}
					break;
			}
		}
	
		if (!$allow_blank || !empty($time)) {
			//$time = make_timestamp($time);
		} 
		
		$html_result = '';
	
		if ($display_hours) {
			$hours       = $use_24_hours ? range(0, 23) : range(1, 12);
			$hour_fmt = $use_24_hours ? '%H' : '%I';
			for ($i = 0, $for_max = count($hours); $i < $for_max; $i++)
				$hours[$i] = sprintf('%02d', $hours[$i]);
	
			if ($allow_blank) {
				$hours = array_merge(array(''=>''), $hours);
			}
	
			$html_result .= '<select name=';
			if (null !== $field_array) {
				$html_result .= '"' . $field_array . '[' . $prefix . 'Hour]"';
			} else {
				$html_result .= '"' . $prefix . 'Hour"';
			}
			
			$html_result .= ' id=';
	
			if (null !== $field_array){
				$html_result .= '"' . $field_array . '_' . $prefix . 'Hour"';
			} else {
				$html_result .= '"' . $prefix . 'Hour"';
			}
			
			if (null !== $hour_extra){
				$html_result .= ' ' . $hour_extra;
			}
			if (null !== $all_extra){
				$html_result .= ' ' . $all_extra;
			}
			$html_result .= $extra_attrs . '>'."\n";
			$html_result .= utils::html_options(array('output'          => $hours,
															   'values'          => $hours,
															   'selected'      => $time ? strftime($hour_fmt, $time) : '',
															   'print_result' => false));
			$html_result .= "</select>\n";
		}
	
		if ($display_minutes) {
			$all_minutes = range(0, 59);
			if ($allow_blank) {
				$minutes['']= '';
			}
			for ($i = 0, $for_max = count($all_minutes); $i < $for_max; $i+= $minute_interval)
				$minutes[] = sprintf('%02d', $all_minutes[$i]);
			$selected = $time ? intval(floor(strftime('%M', $time) / $minute_interval) * $minute_interval) : '';
			$html_result .= '<select name=';
			if (null !== $field_array) {
				$html_result .= '"' . $field_array . '[' . $prefix . 'Minute]"';
			} else {
				$html_result .= '"' . $prefix . 'Minute"';
			}
			
			$html_result .= ' id=';
	
			if (null !== $field_array){
				$html_result .= '"' . $field_array . '_' . $prefix . 'Minute"';
			} else {
				$html_result .= '"' . $prefix . 'Minute"';
			}
			
			if (null !== $minute_extra){
				$html_result .= ' ' . $minute_extra;
			}
			if (null !== $all_extra){
				$html_result .= ' ' . $all_extra;
			}
			$html_result .= $extra_attrs .'>'."\n";
			
			$html_result .= utils::html_options(array('output'          => $minutes,
															   'values'          => $minutes,
															   'selected'      => $selected,
															   'print_result' => false));
												
			$html_result .= "</select>\n";
		}
	
		if ($display_seconds) {
			$all_seconds = range(0, 59);
			if ($allow_blank) {
				$seconds[''] = '';
			}
			for ($i = 0, $for_max = count($all_seconds); $i < $for_max; $i+= $second_interval)
				$seconds[] = sprintf('%02d', $all_seconds[$i]);
			$selected = $time ? intval(floor(strftime('%S', $time) / $second_interval) * $second_interval) : '';
			$html_result .= '<select name=';
			if (null !== $field_array) {
				$html_result .= '"' . $field_array . '[' . $prefix . 'Second]"';
			} else {
				$html_result .= '"' . $prefix . 'Second"';
			}
	
			$html_result .= ' id=';
	
			if (null !== $field_array){
				$html_result .= '"' . $field_array . '_' . $prefix . 'Second"';
			} else {
				$html_result .= '"' . $prefix . 'Second"';
			}
			
			if (null !== $second_extra){
				$html_result .= ' ' . $second_extra;
			}
			if (null !== $all_extra){
				$html_result .= ' ' . $all_extra;
			}
			$html_result .= $extra_attrs .'>'."\n";   
			
			$html_result .= utils::html_options(array('output'          => $seconds,
															   'values'          => $seconds,
															   'selected'      => $selected,
															   'print_result' => false));
			$html_result .= "</select>\n";
		}
	
		if ($display_meridian && !$use_24_hours) {
			$meridian_output = array('AM','PM');
			$meridian_values = array('am','pm');
			if ($allow_blank) {
				array_unshift($meridian_output, '--');
				array_unshift($meridian_values, '--');
			}
			
			$html_result .= '<select name=';
			if (null !== $field_array) {
				$html_result .= '"' . $field_array . '[' . $prefix . 'Meridian]"';
			} else {
				$html_result .= '"' . $prefix . 'Meridian"';
			}
	
			$html_result .= ' id=';
	
			if (null !== $field_array){
				$html_result .= '"' . $field_array . '_' . $prefix . 'Meridian"';
			} else {
				$html_result .= '"' . $prefix . 'Meridian"';
			}
			
			if (null !== $meridian_extra){
				$html_result .= ' ' . $meridian_extra;
			}
			if (null !== $all_extra){
				$html_result .= ' ' . $all_extra;
			}
			$html_result .= $extra_attrs .'>'."\n";
			
			$html_result .= utils::html_options(array('output'          => $meridian_output,
															   'values'          => $meridian_values,
															   'selected'      => $time ? strtolower(strftime('%p', $time)) : $time,
															   'print_result' => false));
			$html_result .= "</select>\n";
		}
	
		return $html_result;
	}

	function html_options($params=array())
	{
		$name = null;
		$values = null;
		$options = null;
		$selected = array();
		$output = null;
		/* MOD PETE AKINS */
		$label_limit = 0;
		$labels = true;
		$first = null;
		$output_field = null;
		$id = null;
		/* END MOD */
		
		$extra = '';
		
		foreach($params as $_key => $_val) {
			switch($_key) {
				case 'name':
				case 'first':
				case 'id':
				case 'output_field':
					$$_key = (string)$_val;
					break;
				
				case 'options':
					$$_key = (array)$_val;
					break;
					
				case 'values':
				case 'output':
					$$_key = array_values((array)$_val);
					break;
	
				case 'selected':
					$$_key = array_map('strval', array_values((array)$_val));
					break;
					
				case 'label_limit':
					$$_key = (int) $_val;
					break;
				case 'labels':
					$$_key = (bool)$_val;
					break;
	
				  
				default:
					if(!is_array($_val)) {
						$extra .= ' '.$_key.'="'.htmlspecialchars($_val).'"';
					} else {
						trigger_error("html_options: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
					}
					break;
			}
		}
	
		if (!isset($options) && !isset($values))
			return ''; /* raise error here? */
	
		$_html_result = '';
			
		if (is_array($options)) {
			
			foreach ($options as $_key=>$_val) {
				/* MOD PETE AKINS */
				if ($output_field) {
					if (is_array($_val)) {
						$_val = isset($_val[$output_field]) ? $_val[$output_field] : $_val;
					} elseif (is_object($_val)) {
						$_val = isset($_val->$output_field) ? $_val->$output_field : $_val;
					} else {
					}
				}
				
				$_html_result .= utils::html_options_optoutput($_key, $_val, $selected, $label_limit, $output_field);
				/* END MOD */
				
			}
		} else {
			
			foreach ((array)$values as $_i=>$_key) {
				$_val = isset($output[$_i]) ? $output[$_i] : '';
				/* MOD PETE AKINS */
	
				if ($output_field) {
					if (is_array($_val)) {
						$_val = isset($_val[$output_field]) ? $_val[$output_field] : $_val;
					} elseif (is_object($_val)) {
						$_val = isset($_val->$output_field) ? $_val->$output_field : $_val;
					}
				}
				$_html_result .= utils::html_options_optoutput($_key, $_val, $selected, $label_limit, $output_field);
				/* END MOD */
			}
	
		}
		
		if(!empty($first)) {
			$_html_result = utils::html_options_optoutput('', $first, $selected, $label_limit, $output_field) . $_html_result;
		}
	
		if(!empty($name)) {
			$id = !empty($id) ? $id : $name;
			$_html_result = '<select id="' . $id . '" name="' . $name . '"' . $extra . '>' . "\n" . $_html_result . '</select>' . "\n";
		}
	
		return $_html_result;
	
	}

	function html_options_optoutput($key, $value, $selected, $label_limit, $output_field) 
	{
		if(!is_array($value)) {
			$_html_result = '<option label="';
			
			$_html_result .= $label_limit > 0  ? substr(htmlspecialchars($value), 0, $label_limit) :
												 htmlspecialchars($value);
			$_html_result .= '" value="' . htmlspecialchars($key) . '"';
	
			if (in_array((string)$key, $selected)) {
				$_html_result .= ' selected="selected"';
			}
			
			$_html_result .= '>';
			
			$_html_result .= $label_limit > 0  ? substr(htmlspecialchars($value), 0, $label_limit) :
												 htmlspecialchars($value);
			
			$_html_result .= '</option>' . "\n";
		} else {
			$_html_result = html_options_optgroup($key, $value, $selected, $label_limit, $output_field);
		}
	
		return $_html_result;
	}

	function html_options_optgroup($key, $values, $selected, $label_limit, $output_field) 
	{
		$optgroup_html = '<optgroup label="' . htmlspecialchars($key) . '">' . "\n";
		foreach ($values as $key => $value) {
			if ($output_field) {
				$value = isset($value[$output_field]) ? $value[$output_field] : $v;
			}
			$optgroup_html .= html_options_optoutput($key, $value, $selected, $label_limit, $output_field);
		}
		$optgroup_html .= "</optgroup>\n";
		return $optgroup_html;
	}
	
	function createTimeStampFromArray($var)
	{
		$timestamp = false;
		if (!is_array($var) || !isset($var['Year'], $var['Month'], $var['Day'])) {
			return false;
		}

		$var['Hour'] = isset($var['Hour']) ? $var['Hour'] : 0;
		$var['Minute'] = isset($var['Minute']) ? $var['Minute'] : 0;
		$var['Second'] = isset($var['Second']) ? $var['Second'] : 0;

		if (isset($var['Meridian'])) {
			if ($var['Meridian']=='pm') {
				if ($var['Hour']!='12') {
					$var['Hour']+= 12;
				}
			} elseif ($var['Meridian']=='am') {
				if ($var['Hour']=='12') {
					$var['Hour'] == '00';
				}
			}
		}
		
		$timestamp = checkdate($var['Month'], $var['Day'], $var['Year']) ? 
			mktime($var['Hour'], $var['Minute'], $var['Second'], $var['Month'], $var['Day'], $var['Year']) :
			false ;
		
		return $timestamp;
	   
	}

	function krange($low, $high)
	{
		$arr = range(0, $high);
		for ($i=0; $i<$low; $i++) {
			unset($arr[$i]);
		}
		
		return $arr;
		
	}
	
	function is_timestamp($arg)
	{
		return preg_match('/^-?\d+$/', $arg);
	}
	
	function getLetters($case='upper')
	{
		switch ($case)
		{
			case 'upper':
				$letters = array(
			   'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 
			   'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	
				break;
			case 'lower':
				$letters = array(
				'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 
				'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
				break;
		}
		return $letters;
	}
	
	function phone_format($phone, $separator='', $default_area_code='') 
	{
		if (preg_match('/^\(?(\d\d\d)?[-).\s]*(\d\d\d)[-.\s]?(\d\d\d\d)$/', $phone, $matches)) {
			unset($matches[0]);
			if (empty($matches[1])) {
				if ($default_area_code) {
					$matches[1] = $default_area_code;
				} else {
					unset($matches[1]);
				}
			}
	
			return implode($separator, $matches);
		} else {
			return false;
		}
	}
	
}


?>