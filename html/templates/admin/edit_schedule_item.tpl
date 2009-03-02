<h1>Editing <?= $schedule_item->title ?></h1>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST">
<input type="hidden" name="action" value="edit_schedule_item">
<input type="hidden" name="schedule_id" value="<?= $schedule_item->schedule_id ?>">
<input type="hidden" name="day" value="<?= $schedule_item->day ?>">

<fieldset>
<?php include('schedule_form.tpl') ?>
<p>
<input type="submit" name="update_item" value="Save">
<input type="submit" name="cancel_item" value="Don't Save">
<input type="submit" name="delete_item" value="Delete Item" class="confirm">
</p>
</fieldset>
<p>
<a href="<?= $App->SCRIPT_NAME ?>?action=edit_schedule&amp;day=<?= $schedule_item->day ?>">return to day</a>
</p>