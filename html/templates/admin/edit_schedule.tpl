<div class="content">
<h1>Editing Schedule for <?= $day_schedule['date_str'] ?></h1>

<p><a href="<?= $App->SCRIPT_NAME ?>?action=add_schedule_item&amp;date=<?= $day_schedule['date'] ?>">add item</a></p>

<?php if ($day_schedule['schedule']) { ?>
<table>
<tr>
	<th>Start</th>
	<th>End</th>
	<th>Room</th>
	<th>Title</th>
	<th>Detail</th>
	<th>Session</th>
	<th>Group</th>
</tr>
<?php $i=0; foreach ($day_schedule['schedule'] as $schedule_item) { ?>
<tr class="row<?= $i %2 ? 1 : 2 ?>">
	<td><?= strtolower(strftime('%I:%M%p', $schedule_item['start_ts'])) ?></td>
	<td><?= strtolower(strftime('%I:%M%p', $schedule_item['end_ts'])) ?></td>
	<td><?= $schedule_item['room'] ?></td>
	<td><a href="<?= $App->SCRIPT_NAME ?>?action=edit_schedule_item&amp;schedule_id=<?= $schedule_item['schedule_id'] ?>"><?= $schedule_item['title'] ?></a></td>
	<td><?= $schedule_item['detail'] ?></td>
	<td><?= $schedule_item['session_id'] ?></td>
	<td><?= $schedule_item['session_group_id'] ? $session_groups[$schedule_item['session_group_id']]->session_group_title : '' ?></td>
</tr>	
<?php $i++; } ?>

</table>
<?php } ?>
</div>