<div class="content">
<h1>Announcements</h1>

<?= $App->getMessages() ?>

<a href="admin.php?action=add_announcement">Add Announcement</a>
<ul>
<?php
foreach ($announcements as $announcement) { ?>
	<li><a href="admin.php?action=edit_announcement&amp;announcement_id=<?= $announcement->announcement_id ?>"><?= htmlentities($announcement->announcement_title) ?></a></li>
<?php } ?>
</ul>
</div>