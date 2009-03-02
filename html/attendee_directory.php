<?php

//require('inc/model_classes.php');

require_once('inc/app_classes.php');

$App = new Application();

$PAGE_TITLE = 'Attendee Directory';
$PAGE = 'attendee_directory';

if (!$attendees = mobilAP::getCache('mobilAP_attendees')) {
	$attendees = mobilAP_attendee::getAttendees(array('only_active'=>getConfig('show_only_active_attendees')));
}

$attendee = isset($_GET['view_attendee']) ? mobilAP_attendee::getAttendeeById($_GET['view_attendee']) : false;


include('templates/header.tpl');
include('templates/nav.tpl');

?>
<h2>Attendee Directory</h2>
<?php if ($attendee) { ?>
<div id="directory_detail">
	<div id="directory_top_box">
		<div id="directory_image_box"><img id="directory_detail_image" src="<?= $attendee->getImageURL() ?>"></div>
		<div id="directory_detail_info_box">
			<div id="directory_detail_name"><?= sprintf("%s %s", $attendee->FirstName, $attendee->LastName) ?></div>
			<div id="directory_detail_title"><?= $attendee->title ?></div>
			<div id="directory_detail_organization"><?= $attendee->organization ?></div>
			<div id="directory_detail_dept"><?= $attendee->dept ?></div>
		</div>
	</div>
	<div id="directory_email_box">
		<div id="directory_detail_email"><a href="mailto:<?= $attendee->email ?>"><?= $attendee->email ?></a></div>
	</div>
	<p id="directory_bio"><?= $attendee->bio ?></p>
</div>
<div class="clearbox"></div>
<p><a href="attendee_directory.php">Attendee Directory</a></p>
<?php } else { ?>
<ul id="attendee_directory">
<?php
foreach ($attendees as $attendee)
{ ?>
	<li>
		<div class="directory_list_name"><a href="attendee_directory.php?view_attendee=<?= $attendee->attendee_id ?>"><?= sprintf("%s %s", $attendee->FirstName, $attendee->LastName) ?></a></div>
	<?php if ($attendee->organization) { ?><div class="directory_list_organization"><?= $attendee->organization ?></div><?php } ?>
	</li>
<?php
}
?>
</ul>
<?php
}
include('templates/footer.tpl');

?>