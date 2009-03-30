<?php include('nav.tpl'); ?>
<div class="content">
<h1>Attendee Directory</h1>
<div id="directory_detail">
	<div id="directory_top_box">
<?php if (getConfig('SHOW_AD_PHOTOS')) { ?><div id="directory_image_box"><img id="directory_detail_image" src="<?= $attendee->getImageURL() ?>"></div><?php } ?>
		<div id="directory_detail_info_box">
			<div id="directory_detail_name"><?= sprintf("%s %s", $attendee->FirstName, $attendee->LastName) ?></div>
<?php if (getConfig('SHOW_AD_TITLE')) { ?><div id="directory_detail_title"><?= htmlentities($attendee->title) ?></div><?php } ?>
<?php if (getConfig('SHOW_AD_ORG')) { ?><div id="directory_detail_organization"><?= htmlentities(attendee->organization) ?></div><?php } ?>
<?php if (getConfig('SHOW_AD_DEPT')) { ?><div id="directory_detail_dept"><?= htmlentities($attendee->dept) ?></div><?php } ?>
<?php if (getConfig('SHOW_AD_LOCATION')) { ?><div id="directory_detail_location"><?= sprintf("%s%s %s %s", $attendee->city, $attendee->state ? ', ': '', $attendee->state, $attendee->country == 'US' ? '' : $attendee->country) ?></div><?php } ?>
		</div>
	</div>
<?php if (getConfig('SHOW_AD_EMAIL')) { ?>
	<div id="directory_email_box">
		<div id="directory_detail_email"><a href="mailto:<?= $attendee->email ?>"><?= $attendee->email ?></a></div>
	</div>
<?php } ?>	
<?php if (getConfig('SHOW_AD_PHONE')) { ?>
	<div id="directory_phone_box">
		<div id="directory_detail_phone"><?= Utils::phone_format($attendee->phone, '-') ?></div>
	</div>
<?php } ?>	
<?php if (getConfig('SHOW_AD_BIO')) { ?>
	<p id="directory_bio"><?= $attendee->bio ?></p>
<?php } ?>	
</div>
<div class="clearbox"></div>
<p><a href="attendee_directory.php">Attendee Directory</a></p>
</div>
