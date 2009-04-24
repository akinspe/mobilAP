<?php include('nav.tpl'); ?>
<div class="content">
<h1><?= getConfig('NAV_ANNOUCEMENTS_LINK') ?></h1>

<?php
if ($announcements) {
foreach ($announcements as $announcement) { ?>
<div class="announcement">
<h2 class="announcement_title"><?= htmlentities($announcement->announcement_title) ?></h2>
<div class="announcement_time">Posted: <?= date('m/d @ g:ia', $announcement->announcement_timestamp) ?></div>
<p class="announcement_text"><?= htmlentities($announcement->announcement_text) ?></p>
</div>
<?php }
} else {
?>
<p class="message">There have been no announcements posted</p>
<?php
}
?>
</div>
