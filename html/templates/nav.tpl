<ul id="mainnav" class="nav">
	<li<?php if ($PAGE=='index') echo ' class="active"'; ?>><a href="index.php"><?= getConfig('NAV_HOME_LINK') ?></a></a></li>
	<li<?php if ($PAGE=='announcements') echo ' class="active"'; ?>><a href="announcements.php"><?= getConfig('NAV_ANNOUCEMENTS_LINK') ?></a></a></li>
	<li<?php if ($PAGE=='sessions') echo ' class="active"'; ?>><a href="sessions.php"><?= getConfig('NAV_SESSIONS_LINK') ?></a></li>
	<?php if (getConfig('SHOW_ATTENDEE_DIRECTORY')) { ?>
	<li<?php if ($PAGE=='attendee_directory') echo ' class="active"'; ?>><a href="attendee_directory.php"><?= getConfig('NAV_DIRECTORY_LINK') ?></a></li>
	<?php } ?>
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
