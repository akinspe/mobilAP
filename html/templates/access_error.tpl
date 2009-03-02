<h1>Access Denied.</h1>

<p class="error">This page has been protected for use by authorized users.</p>
<?php 
if ($App->is_LoggedIn()) { ?>
<p>You do not have the proper privileges to view the page. If you believe this
is an error please contact the site administrators</p>
<?php } else { ?>
<p>You may need to <a href="login">Login</a> to view this page.</p>
<?php } ?>