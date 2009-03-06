<div class="content">
<h1>Attendee Administration</h1>

<?= $App->getMessages() ?>
<a href="<?= $App->SCRIPT_NAME ?>">Back to attendee list</a>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="action" value="<?= $action ?>">

<fieldset>
<legend>Import Attendees</legend>

<label>Upload file</label>
<input type="file" name="file_upload">

<select name="delimiter">
	<option value="tab">Tab-Delimited</option>
	<option value="csv">Comma-delimited</option>
</select>	

<p>
	<input type="submit" name="import_file" value="Import File">
	<input type="submit" name="cancel" value="Cancel">
</p>	



</fieldset>

</form>

<?php if ( isset($import_data) && !empty($import_data)) { ?>
<table>
<tr>
	<td><a href="<?= $App->SCRIPT_NAME ?>?action=<?= $action ?>&amp;commit_import=all">add all</a></td>
	<td></td>
	<td><a href="<?= $App->SCRIPT_NAME ?>?action=<?= $action ?>&amp;delete_import=all">remove all</a></td>
	<th>Salutation</th>
	<th>First Name</th>
	<th>Last Name</th>
	<th>Organization</th>
	<th>Title</th>
	<th>Department</th>
	<th>City</th>
	<th>State</th>
	<th>Country</th>
	<th>email</th>
	<th>Phone</th>
<?php if (getConfig('use_passwords')) { ?>
	<th>Password</th>
<?php } ?>	
</tr>
<?php foreach ($import_data as $data) { ?>
<tr>
	<td><a href="<?= $App->SCRIPT_NAME ?>?action=<?= $action ?>&amp;commit_import=<?= $data['import_id'] ?>">add</a></td>
	<td><a href="<?= $App->SCRIPT_NAME ?>?action=<?= $action ?>&amp;edit_import=<?= $data['import_id'] ?>">edit</a></td>
	<td><a href="<?= $App->SCRIPT_NAME ?>?action=<?= $action ?>&amp;delete_import=<?= $data['import_id'] ?>">remove</a></td>
	<td><?= $data['salutation'] ?></td>
	<td><?= $data['FirstName'] ?></td>
	<td><?= $data['LastName'] ?></td>
	<td><?= $data['organization'] ?></td>
	<td><?= $data['title'] ?></td>
	<td><?= $data['dept'] ?></td>
	<td><?= $data['city'] ?></td>
	<td><?= $data['state'] ?></td>
	<td><?= $data['country'] ?></td>
	<td><?= $data['email'] ?></td>
	<td><?= $data['phone'] ?></td>
<?php if (getConfig('use_passwords')) { ?>
	<td><?= $data['password'] ?></td>
<?php } ?>	
</tr>
<? }?>
</table>
<?php } ?>

<a href="<?= $App->SCRIPT_NAME ?>">Back to attendee list</a>
</div>