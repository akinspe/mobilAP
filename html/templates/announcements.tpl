<?php include('nav.tpl'); ?>
<div class="content">
<h1>Announcements</h1>

<?php
foreach ($announcements as $announcement) { ?>
<div class="announcement">
<h2 class="announcement_title"><?= htmlentities($announcement->announcement_title) ?></h2>
<div class="announcement_time">Posted: <?= date('m/d @ g:ia', $announcement->announcement_timestamp) ?></div>
<p class="announcement_text"><?= htmlentities($announcement->announcement_text) ?></p>
</div>
<?php } ?>
</div>