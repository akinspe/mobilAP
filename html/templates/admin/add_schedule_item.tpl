<h1>Adding new item</h1>
<?= $App->getMessages() ?>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST">
<input type="hidden" name="action" value="add_schedule_item">

<fieldset>
<?php include('schedule_form.tpl') ?>
<p>
<input type="submit" name="add_item" value="Add Item">
<input type="submit" name="cancel_item" value="Cancel">
</p>
</fieldset>
<p><a href="<?= $App->SCRIPT_NAME ?>?action=edit_schedule&amp;date=<?=$schedule_item->date?>">return to day</a></p>