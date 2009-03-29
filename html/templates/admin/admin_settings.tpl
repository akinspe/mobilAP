<div class="content">
<h1>Conference Settings</h1>
<?= $App->getMessages() ?>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="action" value="<?= $action ?>">

<input type="submit" name="reset_settings" class="confirm" value="Reset all settings to default">

<fieldset>
<legend>Attendee Directory Settings</legend>

<p class="explanation">These settings affect how the attendee directory is shown</p>
<label>Attendee Directory</label>
<input type="radio" name="setting[SHOW_ATTENDEE_DIRECTORY]" value="-1"<?= getConfig('SHOW_ATTENDEE_DIRECTORY') ? ' checked' : ''?>> On
<input type="radio" name="setting[SHOW_ATTENDEE_DIRECTORY]" value="0"<?= getConfig('SHOW_ATTENDEE_DIRECTORY') ? '' : ' checked' ?>> Off
<br class="end">

<label>Show photos</label>
<input type="radio" name="setting[SHOW_AD_PHOTOS]" value="-1"<?= getConfig('SHOW_AD_PHOTOS') ? ' checked' : ''?>> On
<input type="radio" name="setting[SHOW_AD_PHOTOS]" value="0"<?= getConfig('SHOW_AD_PHOTOS') ? '' : ' checked' ?>> Off
<br class="end">

<label>Show title</label>
<input type="radio" name="setting[SHOW_AD_TITLE]" value="-1"<?= getConfig('SHOW_AD_TITLE') ? ' checked' : ''?>> On
<input type="radio" name="setting[SHOW_AD_TITLE]" value="0"<?= getConfig('SHOW_AD_TITLE') ? '' : ' checked' ?>> Off
<br class="end">

<label>Show organization</label>
<input type="radio" name="setting[SHOW_AD_ORG]" value="-1"<?= getConfig('SHOW_AD_ORG') ? ' checked' : ''?>> On
<input type="radio" name="setting[SHOW_AD_ORG]" value="0"<?= getConfig('SHOW_AD_ORG') ? '' : ' checked' ?>> Off
<br class="end">

<label>Show department</label>
<input type="radio" name="setting[SHOW_AD_DEPT]" value="-1"<?= getConfig('SHOW_AD_DEPT') ? ' checked' : ''?>> On
<input type="radio" name="setting[SHOW_AD_DEPT]" value="0"<?= getConfig('SHOW_AD_DEPT') ? '' : ' checked' ?>> Off
<br class="end">

<label>Show email</label>
<input type="radio" name="setting[SHOW_AD_EMAIL]" value="-1"<?= getConfig('SHOW_AD_EMAIL') ? ' checked' : ''?>> On
<input type="radio" name="setting[SHOW_AD_EMAIL]" value="0"<?= getConfig('SHOW_AD_EMAIL') ? '' : ' checked' ?>> Off
<br class="end">

<label>Show phone number</label>
<input type="radio" name="setting[SHOW_AD_PHONE]" value="-1"<?= getConfig('SHOW_AD_PHONE') ? ' checked' : ''?>> On
<input type="radio" name="setting[SHOW_AD_PHONE]" value="0"<?= getConfig('SHOW_AD_PHONE') ? '' : ' checked' ?>> Off
<br class="end">

<label>Show Location</label>
<input type="radio" name="setting[SHOW_AD_LOCATION]" value="-1"<?= getConfig('SHOW_AD_LOCATION') ? ' checked' : ''?>> On
<input type="radio" name="setting[SHOW_AD_LOCATION]" value="0"<?= getConfig('SHOW_AD_LOCATION') ? '' : ' checked' ?>> Off
<br class="end">

<label>Show biography</label>
<input type="radio" name="setting[SHOW_AD_BIO]" value="-1"<?= getConfig('SHOW_AD_BIO') ? ' checked' : ''?>> On
<input type="radio" name="setting[SHOW_AD_BIO]" value="0"<?= getConfig('SHOW_AD_BIO') ? '' : ' checked' ?>> Off
<br class="end">


</fieldset>

<fieldset>
<legend>Other Settings</legend>

<label>Use Passwords</label>
<p class="explanation">You can require attendees to enter a password when logging in. You assign passwords either at import
or by editing the user in the attendee administration. The default password is: &quot;<?= getConfig('default_password') ?>&quot;</p>
<input type="radio" name="setting[USE_PASSWORDS]" value="-1"<?= getConfig('USE_PASSWORDS') ? " checked": ''?>> Yes
<input type="radio" name="setting[USE_PASSWORDS]" value="0"<?= getConfig('USE_PASSWORDS') ? '' : " checked"?>> No
<br class="end">

<?php if (!getConfig('USE_PASSWORDS')) { ?>
<label>Require Password for admins</label>
<p class="explanation">You can require admins to enter a password when logging in. This will allow attendees to
login without a password, but prevent unauthorized administration without a password. The default password is: &quot;<?= getConfig('default_password') ?>&quot;</p>
<input type="radio" name="setting[USE_ADMIN_PASSWORDS]" value="-1"<?= getConfig('USE_ADMIN_PASSWORDS') ? " checked": ''?>> Yes
<input type="radio" name="setting[USE_ADMIN_PASSWORDS]" value="0"<?= getConfig('USE_ADMIN_PASSWORDS') ? '' : " checked"?>> No
<br class="end">
<?php } ?>

</fieldset>

<p>
<input type="submit" name="update_settings" value="Update Settings">
<input type="submit" name="cancel" value="Cancel">
</p>

</form>
</div>
