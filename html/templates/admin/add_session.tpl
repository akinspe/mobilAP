<h1>Add new Session</h1>
<?= $App->getMessages() ?>
<form action="<?= $App->SCRIPT_NAME ?>" method="POST">
<input type="hidden" name="action" value="add_session">

<?php include('session_form.tpl') ?>

<p>
<input type="submit" name="add_session" value="Add Session">
<input type="submit" name="cancel" value="Cancel">
</p>

</form>

<p><a href="<?= $App->SCRIPT_NAME ?>">return to admin</a></p>