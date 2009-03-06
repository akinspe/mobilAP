<?php 	include("templates/nav.tpl"); ?>
<div class="content">
<h1>Login</h1>
<?= $App->getMessages() ?>
<p>Your login is your email address</p>
<form id="login_form" action="login.php" method="POST">
<?php if ($referrer) { ?>
	<input type="hidden" name="referrer" value="<?= htmlentities($referrer) ?>">
<?php  } ?>
	<label id="login_userID_label">email</label>
	<input type="text" size="30" name="login_userID" maxlength="50" value="" accesskey="l" tabindex="0" id="login_userID_input" class="login_input">
<?php
if (getConfig('use_passwords')) { ?>
	<label id="login_pword_label">password</label>
	<input type="password" size="17" maxlength="16" name="login_pword" tabindex="0" id="login_pword_input" class="login_input">
<?php
}
?>
	<input type="submit" name="login_submit" id="login_submit" value="login">
</form>
</div>