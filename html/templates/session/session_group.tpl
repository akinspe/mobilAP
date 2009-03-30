<?php
include('templates/nav.tpl'); 
?>

<div class="content">
<h1><?= htmlentities($session_group->session_group_title) ?></h1>
<ul id="schedule">
<?php

foreach ($session_group->schedule_items as $schedule_item)
{ ?>
	<li><div class="schedule_time"><?= date('g:i', $schedule_item->start_ts) ?></div>
	<div class="schedule_title"><?php if ($schedule_item->session_id) { ?>
	<a href="session.php?session_id=<?= $schedule_item->session_id ?>"><?= $schedule_item->session_id ?> <?= htmlentities($schedule_item->title) ?></a>
	<?php } elseif ($schedule_item->session_group_id) { ?>
	<a href="session_group.php?session_group_id=<?= $schedule_item->session_group_id?>"><?= htmlentities($schedule_item->title) ?></a>
	<?php } else { ?>
	<?= htmlentities($schedule_item->title) ?>
	<?php } ?></div>
	<div class="schedule_room"><?= htmlentities($schedule_item->room) ?></div>
	<div class="schedule_detail"><?= htmlentities($schedule_item->detail) ?></div>
	</li>
<?php	
}
?>
</ul>
</div>
