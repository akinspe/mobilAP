<?php

require_once('inc/app_classes.php');

$App = new Application();

if (!$schedule = mobilAP::getCache('mobilAP_schedule')) {
	$schedule = mobilAP::getSchedule();
	mobilAP::setCache('mobilAP_schedule', $schedule, 600);
}

$day_index = isset($_GET['day_index']) ? $_GET['day_index'] : 0;
$day_data = isset($schedule[$day_index]) ? $schedule[$day_index] : current($schedule);
$day_schedule = isset($day_data['schedule']) ? $day_data['schedule'] : array();

$PAGE_TITLE = 'mobilAP: Sessions';
$PAGE = 'sessions';

include('templates/header.tpl');
include('templates/nav.tpl');

?>
<ul id="session_days">
<?php 
foreach ($schedule as $_day_index=>$day)
{
?>
	<li<?php if ($day_index==$_day_index) echo ' class="active"'; ?>><a href="sessions.php?day_index=<?= $_day_index ?>"><?= strftime("%A", $day['date_ts']) ?></a></li>
<?php
}

?>
</ul>
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


<?php

include('templates/footer.tpl');

?>