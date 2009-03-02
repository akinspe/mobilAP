<h1>Editing Session Group <?= $session_group->session_group_title ?></h1>

<?= $App->getMessages() ?>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST">
<input type="hidden" name="action" value="edit_session_group">
<input type="hidden" name="session_group_id" value="<?= $session_group->session_group_id ?>">

<?php include('session_group_form.tpl'); ?>


<p>
	<input type="submit" name="update_session_group" value="Save">
	<input type="submit" name="cancel_session_group" value="Don't Save">
	<input type="submit" name="delete_session_group" value="Delete" class="confirm">
</p>
</form>