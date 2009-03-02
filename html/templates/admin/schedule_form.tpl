<legend>Schedule Data</legend>
<label>Date</label>
<?= Utils::html_select_date(array('prefix'=>'', 'field_array'=>'date', 'time'=>$schedule_item->start_ts, 'end_year'=>'+2')) ?>
<br>

<label>Start Time</label>
<?= Utils::html_select_time(array('prefix'=>'', 'field_array'=>'start_time', 'time'=>$schedule_item->start_ts, 'minute_interval'=>5, 'display_seconds'=>false, 'use_24_hours'=>false)) ?>
<br>

<label>End Time</label>
<?= Utils::html_select_time(array('prefix'=>'', 'field_array'=>'end_time', 'time'=>$schedule_item->end_ts, 'minute_interval'=>5, 'display_seconds'=>false, 'use_24_hours'=>false)) ?>
<br>

<label>Title</label>
<input type="text" name="title" value="<?= htmlentities($schedule_item->title) ?>" size="75" maxlength="100">
<br>

<label>Detail</label>
<input type="text" name="detail" value="<?= htmlentities($schedule_item->detail) ?>" size="75" maxlength="100">
<br>


<label>Room</label>
<input type="text" name="room" value="<?= htmlentities($schedule_item->room) ?>" size="30" maxlength="32">
<br>

<label>Session</label>
<select name="session_id">
	<option value="">None</option>
<?php
	$sessions = mobilAP_session::getSessions();
	foreach ($sessions as $session) {
?>
	<option value="<?= $session->session_id ?>"<?= ($schedule_item->session_id==$session->session_id) ? ' selected' : '' ?>><?= $session->session_id ?>: <?= $session->session_title ?></option>
<?php } ?>	
</select>
<br>

<label>Session Group</label>
<select name="session_group_id">
	<option value="">None</option>
<?php
	$session_groups = mobilAP_session_group::getSessionGroups();
	foreach ($session_groups as $session_group) {
?>
	<option value="<?= $session_group->session_group_id ?>"<?= ($schedule_item->session_group_id==$session_group->session_group_id) ? ' selected' : '' ?>><?= $session_group->session_group_title ?></option>
<?php } ?>	
</select>
<br>
