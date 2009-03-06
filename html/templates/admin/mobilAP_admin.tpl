<div class="content">
<h1>mobilAP Administration</h1>

<?= $App->getMessages() ?>

<?php if ($mobilAP_admin) { ?>
<h2>Attendees</h2>

<p><a href="attendee_admin.php">Manage Attendees/Check-in</a></p>
<?php } ?>

<?php if ($mobilAP_admin) { ?>
<h2>Other</h2>
<ul>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=announcements">Manage Announcements</a></li>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=export_data">Export SQL Data</a></li>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=evaluation_questions">Evaluation Questions</a></li>
</ul>

<h2>Schedule</h2>
<ul>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=add_schedule_item">Add Item</a></li>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=session_groups">Session Groups</a></li>
</ul>	

<ul>
<?php foreach ($mobilAP_days as $date) {  ?>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=edit_schedule&amp;date=<?= $date['date'] ?>"><?= $date['date_str'] ?></a></li>
<?php } ?>
</ul>
<?php } ?>

<h2>Sessions</h2>
<?php if ($mobilAP_admin) { ?>
<p><a href="<?= $App->SCRIPT_NAME ?>?action=add_session">Add Session</a></p>
<?php } 
if ($sessions) { 
if ($mobilAP_admin) { ?>
<table class="wide admin">
<tr>
	<th colspan="2">Session</th>
	<th>Evaluations</th>
	<th>Links</th>
	<th>Questions</th>
	<th>Posts</th>
</tr>
<?php } else { ?>
<ul>
<?php } ?>
<?php 
$i=0;
foreach ($sessions as $session) { 
	
if ($mobilAP_admin) {
?>
<tr class="row<?= $i %2 ? 1 : 2 ?>">
	<td><?= $session->session_id ?></td>
	<td><a href="<?= $App->SCRIPT_NAME ?>?action=edit_session&amp;session_id=<?= $session->session_id ?>"><?= $session->session_title ?></a></td>
	<td align="center"><?= count($session->session_evaluations) ?></td>
	<td align="center"><?= count($session->session_links) ?></td>
	<td align="center"><?= count($session->session_questions) ?></td>
	<td align="center"><?= count($session->session_chat) ?></td>
</tr>
<?php } else { ?>
<li><?= $session->session_id ?> <a href="<?= $App->SCRIPT_NAME ?>?action=edit_session&amp;session_id=<?= $session->session_id ?>"><?= $session->session_title ?></a></li>
<?php }
	$i++;
} ?>
<?php if ($mobilAP_admin) { ?>
</table>
<?php } else { ?>
</ul>
<?php } 
}?>
</div>