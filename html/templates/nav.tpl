<ul id="mainnav" class="nav">
	<li<?php if ($PAGE=='index') echo ' class="active"'; ?>><a href="index.php">Welcome</a></li>
	<li<?php if ($PAGE=='announcements') echo ' class="active"'; ?>><a href="announcements.php">Announcements</a></li>
	<li<?php if ($PAGE=='sessions') echo ' class="active"'; ?>><a href="sessions.php">Sessions</a></li>
	<li<?php if ($PAGE=='attendee_directory') echo ' class="active"'; ?>><a href="attendee_directory.php">Attendee Directory</a></li>
<?php if ($App->is_LoggedIn()) { 
	$user = $App->getUser();
	$show_admin = $user->isAdmin() || $user->isPresenter();
?>
<li id="login_nav"><?= $App->getUserID() ?> logged in <a href="login.php?action=logout">logout</a><?php if ($show_admin) { ?> | <a href="admin.php">admin</a><?php } ?></li>
<?php } else { ?>
<li id="login_nav"><a href="login.php">Login</a></li>
<?php } ?>
</ul>
<div class="clearbox"></div>
