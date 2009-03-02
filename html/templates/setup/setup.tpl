<h2>mobilAP Installation</h2>

<?php echo $App->getMessages() ?>

<p>First we need to create an initial administrative account. This account can then make other accounts as necessary</p>

<form action="setup.php" method="POST">
<label>Name:</label> 
<input type="text" name="FirstName"> <input type="text" name="LastName">
<br>

<label>email address:</label> 
<input type="text" name="email" value="">
<br>

<input type="submit" name="submit_setup" value="Setup">

</form>