<h1>Attendee Administration</h1>

<?= $App->getMessages() ?>
<a href="<?= $App->SCRIPT_NAME ?>">Back to attendee list</a>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="action" value="<?= $action ?>">
<?php if (isset($import_id)) { ?>
<input type="hidden" name="import_id" value="<?= $import_id ?>">
<?php } ?>
<fieldset>
<legend>Attendee Information</legend>

<label>Salutation</label>
<?= Utils::html_options(array('name'=>'salutation', 'options'=>$salutations, 'selected'=>$attendee->salutation, 'first'=>'-- Choose --')) ?>
<br class="end">

<label class="required">Name (First / Last)</label>
<input type="text" name="FirstName" value="<?= htmlentities($attendee->FirstName) ?>" size="20" maxlength="50">
<input type="text" name="LastName" value="<?= htmlentities($attendee->LastName) ?>" size="25" maxlength="50">
<br class="end">

<label>Title</label>
<input type="text" name="title" value="<?= htmlentities($attendee->title) ?>" size="40" maxlength="50">
<br class="end">

<label class="required">Organization</label>
<input type="text" name="organization" value="<?= htmlentities($attendee->organization) ?>" size="40" maxlength="50">
<br class="end">

<label>Department</label>
<input type="text" name="dept" value="<?= htmlentities($attendee->dept) ?>" size="40" maxlength="50">
<br class="end">

<label class="required">Email</label>
<input type="text" name="email" value="<?= htmlentities($attendee->email) ?>" size="50" maxlength="50">
<br class="end">

<label>Administrator?</label>
<input type="checkbox" name="admin" value="-1"<?php if ($attendee->admin) echo " CHECKED" ?>>
<br class="end">

<p>
	<input type="submit" name="add_attendee" value="Save Data">
	<input type="submit" name="cancel_attendee" value="Don't Save">
</p>	



</fieldset>

</form>

<a href="<?= $App->SCRIPT_NAME ?>">Back to attendee list</a>
