<div class="content">
<h1>mobilAP Installation</h1>

<?php echo $App->getMessages() ?>

<p>First we need to create an initial administrative account. This account can then make other accounts as necessary</p>

<form action="setup.php" method="POST">
<label>Name:</label> 
<input type="text" name="FirstName" value="<?= htmlentities($FirstName) ?>"> <input type="text" name="LastName" value="<?= htmlentities($LastName) ?>">
<br>

<label>email address:</label> 
<input type="text" name="email" value="<?= htmlentities($email) ?>">
<br>

<?php if (getConfig('USE_PASSWORDS')) { ?>
	<label>Password</label>
	<input type="password" size="17" maxlength="16" name="password" value="">
	<br class="end">

	<label>Verify Password</label>
	<input type="password" size="17" maxlength="16" name="password_verify" value="">
	<br class="end">
<?php } ?>

<input type="submit" name="submit_setup" value="Setup">

</form>
</div>
