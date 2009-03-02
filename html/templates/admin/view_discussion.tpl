<?php

$session_chat = $session->get_chat();
?>
<ul id="session_discussion">
<?php 
foreach ($session_chat as $item) { 
?>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=delete_discussion&amp;session_id=<?= $session->session_id ?>&amp;post_id=<?= $item['post_id'] ?>">remove</a> <?= date("m/d h:i:s", $item['post_timestamp']) ?> <a href="attendee_directory.php?view_attendee=<?= $item['post_user'] ?>"><?= $item['post_name'] ?></a> <?= $item['post_text'] ?></li>
<?php } ?>
</ul>
