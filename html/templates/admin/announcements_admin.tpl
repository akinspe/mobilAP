<h2>Announcements</h2>

<?= $App->getMessages() ?>

<a href="admin.php?action=add_announcement">Add Announcement</a>
<ul>
<?php
foreach ($announcements as $announcement) { ?>
	<li><a href="admin.php?action=edit_announcement&amp;announcement_id=<?= $announcement->announcement_id ?>"><?= $announcement->announcement_title ?></a></li>
<?php } ?>
</ul>

<p><a href="admin.php">Return to admin</a></p>