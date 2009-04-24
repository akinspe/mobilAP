<p>Here you can find and post links that are relevant to this session.</p>
<ul id="session_links">
<?php
foreach ($session->session_links as $link) { ?>
<li><a href="<?= $link->link_url ?>"><?= htmlentities($link->link_text) ?></a></li>

<?php } ?>
</ul>

<?php 

if ($App->is_LoggedIn()) { 
	if ( ($session->session_flags & mobilAP_session::SESSION_FLAGS_ATTENDEE_LINKS) || $session->isPresenter($App->getUserID())) { ?>
<form action="session.php" id="add_link_form" method="POST">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">
<input type="hidden" name="view" value="<?= $view ?>">
<fieldset>
<legend>Add Link</legend>
<p>Please make sure all links begin with http://</p>
<label>URL</label>
<input type="text" name="link_url" value="http://" id="link_url">
<label>Title</label>
<input type="text" name="link_text" value="" id="link_text">

<input type="submit" name="add_link" id="add_link" value="Add Link">
<?php } else { ?>

<?php } 
} elseif ($session->session_flags & mobilAP_session::SESSION_FLAGS_ATTENDEE_LINKS) { ?>
	<p class="message">You must login to post links</p>
<?php
}
?>

</fieldset>
</form>