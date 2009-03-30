<h2>Session Discussion</h2>
<form action="session.php" id="add_discussion_form" method="POST">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">
<input type="hidden" name="view" value="<?= $view ?>">
<label>Talk about this session here</label>
<textarea name="post_text" id="post_text" cols="50" rows="8"></textarea>
<br>
<input type="submit" name="add_discussion" id="add_discussion" value="Submit Post">
</form>
<ul id="session_discussion">
<?php 
foreach ($session->session_chat as $item) { 
?>
	<li><span class="post_timestamp"><?= date("m/d h:i:s", $item['post_timestamp']) ?></span> <span class="post_user"><a href="attendee_directory.php?view_attendee=<?= $item['post_user'] ?>"><?= $item['post_name'] ?></a></span> <span class="post_text"><?= htmlentities($item['post_text']) ?></span></li>
<?php } ?>
</ul>

