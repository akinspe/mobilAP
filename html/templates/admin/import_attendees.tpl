<div class="content">
<h1>Attendee Administration</h1>

<?= $App->getMessages() ?>
<a href="<?= $App->SCRIPT_NAME ?>">Back to attendee list</a>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="action" value="<?= $action ?>">

<fieldset>
<legend>Import Attendees</legend>
<p>You can import attendes into the attendee directory by uploading a tab delimited file using this 
template (<a href="example_import.xls">xls</a> | <a href="example_import.tab">tab</a>). Once 
you upload this file, the rows will be imported into a tempoarary table. You then will see the individual
rows of users. You should verify that the fields are in the proper order. Keep in mind the following rules for certain fields:
<ul>
	<li>All fields must be present, Take care that your spreadsheet program does not remove unused columns when exporting to tab-delimited. It may help to keep the field names in your spreadsheet and then remove them in the exported tab file.</li>
	<li>The salutation must be blank or one of the following: <?= implode(', ', $salutations) ?> Do <b>not</b> include a period at the end</li>
	<li>First and last names are required</li>
	<li>State/Provinces are optional, but should be 2 letter postal code and are only recognized for US and Canada</li>
	<li>Country is optional, but must be 2 digit ISO code if included. If you use states, you must include US as the country. (<a href="<?= $App->SCRIPT_NAME ?>?action=iso_codes">see ISO list</a>)</li>
	<li>Email is required and must be unique. It is used for login. If you include a user in the TAB file who already exists in the directory, they will be ignored. You don't have to worry about accidentally overwriting existing users</li>
	<li>All other fields may be blank</li>
</ul>	
</p>

<label>Upload file</label>
<input type="file" name="file_upload">
<input type="hidden" name="delimiter" value="tab">

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
<?php if (getConfig('USE_PASSWORDS')) { ?>
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
<?php if (getConfig('USE_PASSWORDS')) { ?>
	<td><?= $data['password'] ?></td>
<?php } ?>	
</tr>
<? }?>
</table>
<?php } ?>

<a href="<?= $App->SCRIPT_NAME ?>">Back to attendee list</a>
</div>