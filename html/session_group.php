<?php

require_once('inc/app_classes.php');

$App = new Application();
$session_group_id = isset($_REQUEST['session_group_id']) ? $_REQUEST['session_group_id'] : '';
if (!$session_group = mobilAP_session_group::getSessionGroupByID($session_group_id)) {
	include('sessions.php');
	exit();
}

$PAGE_TITLE = $session_group->session_group_title;
$PAGE = 'sessions';

include('templates/header.tpl');
include('templates/nav.tpl');

?>
<h2><?= $session_group->session_group_title ?></h2>
<ul id="schedule">
<?php

foreach ($session_group->schedule_items as $schedule_item)
{ ?>
	<li><div class="schedule_time"><?= date('g:i', $schedule_item->start_ts) ?></div>
	<div class="schedule_title"><?php if ($schedule_item->session_id) { ?>
	<a href="session.php?session_id=<?= $schedule_item->session_id ?>"><?= $schedule_item->session_id ?> <?= $schedule_item->title ?></a>
	<?php } elseif ($schedule_item->session_group_id) { ?>
	<a href="session_group.php?session_group_id=<?= $schedule_item->session_group_id?>"><?= $schedule_item->title ?></a>
	<?php } else { ?>
	<?= $schedule_item->title ?>
	<?php } ?></div>
	<div class="schedule_room"><?= $schedule_item->room ?></div>
	<div class="schedule_detail"><?= $schedule_item->detail ?></div>
	</li>
<?php	
}
?>
</ul>
<div class="clearbox"></div>
<?= $App->getMessages() ?>

<?php

include('templates/footer.tpl');

?>