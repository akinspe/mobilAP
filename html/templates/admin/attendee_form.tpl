<fieldset>
<legend>Check-in</legend>

<?php 

if ($attendee->checked_in ) { ?>
Attendee checked in.<br>

<?php
if ($attendee->directory_active) { ?>
Visible in directory. <input type="submit" name="directory_inactive" value="Turn off directory" class="confirm"> 
<?php } else { ?>
NOT visible in directory. <input type="submit" name="directory_active" value="Turn on directory" class="confirm">
<?php }
} else { ?>
Attendee <b>NOT</b> checked in <input type="submit" name="check_in" value="Check in now" class="confirm">
<?php } ?>

</fieldset>

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

<?php if (getConfig('USE_PASSWORDS')) { ?>
	<label id="login_pword_label">Password</label>
	<input type="text" size="17" maxlength="16" name="password" value="<?= isset($attendee->password) ? htmlentities($attendee->password) : '' ?>">
	Note: If left blank, the password will not change
	<br class="end">
<?php } ?>

<label>Phone</label>
<input type="text" name="phone" value="<?= htmlentities(Utils::phone_format($attendee->phone,'-')) ?>" size="12" maxlength="15">
<br class="end">

<label>City</label>
<input type="text" name="city" value="<?= htmlentities($attendee->city) ?>" size="40" maxlength="50">
<br class="end">

<label>State</label>
<?= Utils::html_options(array('name'=>'state', 'options'=>mobilAP::states(), 'selected'=>$attendee->state, 'first'=>'-- Choose --')) ?>
<br class="end">

<label>Country</label>
<?= Utils::html_options(array('name'=>'country', 'options'=>mobilAP::countries(), 'selected'=>$attendee->country, 'first'=>'-- Choose --')) ?>
<br class="end">

<label>Administrator?</label>
<input type="checkbox" name="admin" value="-1"<?php if ($attendee->admin) echo " CHECKED" ?>>
<br class="end">

<label>Biography</label>
<textarea name="bio" cols="60" rows="10"><?= htmlentities($attendee->bio) ?></textarea>
<br class="end">

<p>
	<input type="submit" name="update_attendee" value="Save Data">
	<input type="submit" name="cancel_attendee" value="Don't Save">
	<input type="submit" name="delete_attendee" value="Delete Attendee">
</p>	



</fieldset>

<?php if (getConfig('SHOW_AD_PHOTOS')) { ?>
<fieldset id="directory_image_fieldset">
<legend>Directory Image</legend>

<label>Upload Image</label>
<input type="file" name="directory_image">
<br>

<input type="submit" name="update_attendee" value="Upload Photo">
<br>
<br class="end">

<div id="directory_image">
<img src="<?= $attendee->getImageURL() ?>?unique=<?= time() ?>">
<input type="submit" name="rotate[90]" value="CW"> 
<input type="submit" name="rotate[-90]" value="CCW"> 
<input type="submit" name="delete_photo" value="Delete Photo" class="confirm">
</div>

</fieldset>
<?php } ?>