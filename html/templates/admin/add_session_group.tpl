<div class="content">
<h1>Add Session Group</h1>

<?= $App->getMessages() ?>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST">
<input type="hidden" name="action" value="add_session_group">

<?php include('session_group_form.tpl'); ?>

<p>
	<input type="submit" name="add_session_group" value="Add Session Group">
	<input type="submit" name="cancel_session_group" value="Cancel">
</p>
</form>
</div>