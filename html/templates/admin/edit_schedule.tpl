<h1>Editing Schedule for <?= $day_schedule['date_str'] ?></h1>

<p><a href="<?= $App->SCRIPT_NAME ?>?action=add_schedule_item&amp;date=<?= $day_schedule['date'] ?>">add item</a></p>

<table>
<?php foreach ($day_schedule['schedule'] as $schedule_item) { ?>
<tr>
	<td><?= strftime('%H:%M:%S', $schedule_item['start_ts']) ?></td>
	<td><?= strftime('%H:%M:%S', $schedule_item['end_ts']) ?></td>
	<td><?= $schedule_item['room'] ?></td>
	<td><a href="<?= $App->SCRIPT_NAME ?>?action=edit_schedule_item&amp;schedule_id=<?= $schedule_item['schedule_id'] ?>"><?= $schedule_item['title'] ?></a></td>
	<td><?= $schedule_item['detail'] ?></td>
	<td><?= $schedule_item['session_id'] ?></td>
</tr>	
<?php } ?>

</table>

<p>
<a href="<?= $App->SCRIPT_NAME ?>">return to admin</a>
</p>