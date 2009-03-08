<?php include('nav.tpl'); ?>
<div class="content">
<h1>Attendee Directory</h1>
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
</div>
