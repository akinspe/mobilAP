<div class="content">
<h1>Conference Settings</h1>
<?= $App->getMessages() ?>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="action" value="<?= $action ?>">

<fieldset>
<legend>Other Settings</legend>

<label>Use Passwords</label>
<p class="explanation">You can require attendees to enter a password when logging in. You assign passwords either at import
or by editing the user in the attendee administration</p>
<input type="radio" name="setting[USE_PASSWORDS]" value="-1"<?= getConfig('USE_PASSWORDS') ? " checked": ''?>> Yes
<input type="radio" name="setting[USE_PASSWORDS]" value="0"<?= getConfig('USE_PASSWORDS') ? '' : " checked"?>> No
<br class="end">

<label>Attendee Directory</label>
<input type="radio" name="setting[HIDE_ATTENDEE_DIRECTORY]" value="0"<?= getConfig('HIDE_ATTENDEE_DIRECTORY') ? '' : " checked"?>> On
<input type="radio" name="setting[HIDE_ATTENDEE_DIRECTORY]" value="-1"<?= getConfig('HIDE_ATTENDEE_DIRECTORY') ? " checked" : '' ?>> Off
<br class="end">

</fieldset>

<p>
<input type="submit" name="update_settings" value="Update Settings">
<input type="submit" name="cancel" value="Cancel">
</p>

</form>
</div>