<div class="content">
<h1>Attendee Administration</h1>

<?= $App->getMessages() ?>
<a href="<?= $App->SCRIPT_NAME ?>">Back to attendee list</a>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="action" value="<?= $action ?>">
<input type="hidden" name="attendee_id" value="<?= $attendee_id ?>">
<?php include('attendee_form.tpl'); ?>

</form>

<a href="<?= $App->SCRIPT_NAME ?>">Back to attendee list</a>
</div>