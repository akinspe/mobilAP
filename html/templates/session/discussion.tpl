<ul id="session_discussion">
<?php 
foreach ($session->session_chat as $item) { 
?>
	<li><?= date("m/d h:i:s", $item['post_timestamp']) ?> <a href="attendee_directory?view_attendee=<?= $item['post_user'] ?>"><?= $item['post_name'] ?></a> <?= $item['post_text'] ?></li>
<?php } ?>
</ul>

<form action="session.php" id="add_discussion_form" method="POST">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">
<input type="hidden" name="view" value="<?= $view ?>">
<label>Talk about this session here</label>
<textarea name="post_text" id="post_text" cols="50" rows="8"></textarea>
<br>
<input type="submit" name="add_discussion" id="add_discussion" value="Submit Post">
</form>