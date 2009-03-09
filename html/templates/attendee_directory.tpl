<?php include('nav.tpl'); ?>
<div class="content">
<h1>Attendee Directory</h1>

<p>
<?php
foreach ($letters as $letter) {
	if (in_array($letter, $usedLetters)) {
		printf('<a href="#%s">%s</a>', $letter, $letter);
	} else {
		print($letter);
	}
	
	if ($letter != 'Z') {
		echo " | ";
	}
} ?>
</p>

<ul id="attendee_directory">
<?php
$letter = '';
	
foreach ($attendees as $attendee)
{ ?>
	<li><?php
	if ($letter != $attendee->LastName[0]) { ?><a name="<?= $attendee->LastName[0] ?>"></a><?php } 
		?><div class="directory_list_name"><a href="attendee_directory.php?view_attendee=<?= $attendee->attendee_id ?>"><?= sprintf("%s %s", $attendee->FirstName, $attendee->LastName) ?></a></div>
	<?php if (getConfig('SHOW_AD_ORG') && $attendee->organization) { ?><div class="directory_list_organization"><?= $attendee->organization ?></div><?php } ?>
	</li>
<?php
	$letter = $attendee->LastName[0];
}
?>
</ul>
</div>