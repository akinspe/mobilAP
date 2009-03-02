<?php

require('inc/app_classes.php');

$App = new Application();

$PAGE_TITLE = "mobilAP: Announcements";
$PAGE = 'announcements';

$announcements = mobilAP_announcement::getAnnouncements();

include('templates/header.tpl');
include('templates/nav.tpl');

?>
<h2>Announcements</h2>

<?php
foreach ($announcements as $announcement) { ?>
<div class="announcement">
<div class="announcement_title"><?= $announcement->announcement_title ?></div>
<div class="announcement_time">Posted: <?= date('m/d @ g:ia', $announcement->announcement_timestamp) ?></div>
<p class="announcement_text"><?= $announcement->announcement_text ?></p>
</div>
<?php } ?>


<?php

include('templates/footer.tpl');

?>