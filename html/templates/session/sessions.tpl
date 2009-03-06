<?php include('templates/nav.tpl'); ?>
<?php include('session_days.tpl'); ?>
<div class="content">
<h1 id="session_day"><?= date('l F j', $day_data['date_ts']) ?></h1>
<ul id="schedule">
<?php

foreach ($day_schedule as $schedule_item)
{ ?>
	<li><div class="schedule_time"><?= date('g:i', $schedule_item['start_ts']) ?></div>
	<div class="schedule_title"><?php if ($schedule_item['session_id']) { ?>
	<a href="session.php?session_id=<?= $schedule_item['session_id']?>"><?= $schedule_item['session_id'] ?> <?= $schedule_item['title'] ?></a>
	<?php } elseif ($schedule_item['session_group_id']) { ?>
	<a href="session_group.php?session_group_id=<?= $schedule_item['session_group_id']?>"><?= $schedule_item['title'] ?></a>
	<?php } else { ?>
	<?= $schedule_item['title'] ?>
	<?php } ?></div>
	<div class="schedule_room"><?= $schedule_item['room'] ?></div>
	<div class="schedule_detail"><?= $schedule_item['detail'] ?></div>
	</li>
<?php	
}

?>
</ul>
</div>