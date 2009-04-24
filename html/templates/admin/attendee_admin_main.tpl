<div class="content">
<h1>Attendee Administration</h1>

<?= $App->getMessages() ?>

<ul>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=add">Add new attendee</a></li>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=import">Import attendees</a></li>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=export">Export attendees</a></li>
</ul>	

<p>Attendees: <?= $attendee_total ?> (<?= $checked_in ?> in)</p>

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

<table class="wide admin">
<tr>
	<th></th>
	<th><a href="<?= $App->SCRIPT_NAME ?>?sort=LastName">Name</th>
	<th><a href="<?= $App->SCRIPT_NAME ?>?sort=organization">Organization</th>
	<th>Admin</th>
	<th>Dir</th>
</tr>
<?php
$letter = '';
foreach ($attendees as $attendee) {
	
	if ($letter != $attendee->LastName[0]) { ?>
<tr class="">
	<th colspan="4" align="left"><a name="<?= $attendee->LastName[0] ?>"></a><?= $attendee->LastName[0] ?></th>
</tr>
<?php } ?>
<tr class="">
	<td align="center"><?= $attendee->checked_in ? 'x' : '' ?></td>
	<td><a href="<?= $App->SCRIPT_NAME ?>?action=edit&amp;attendee_id=<?= $attendee->attendee_id ?>"><?= $attendee->FirstName ?> <?= $attendee->LastName ?></a></td>
	<td><?= htmlentities(substr($attendee->organization,0,35)) ?></td>
	<td><?= $attendee->admin ? '<b>Y</b>' : 'N' ?></td>
	<td><?= $attendee->directory_active ? 'Y' : 'N' ?></td>
</tr>
<?php 
	$letter = $attendee->LastName[0];

} ?>
</table>
</div>