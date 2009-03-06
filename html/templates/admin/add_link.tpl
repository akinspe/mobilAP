<div class="content">
<h1>Editing Session <?= $session->session_id ?></h1>

<?= $App->getMessages() ?>
<p>Links are useful to direct attendees to additional websites and resources available to complement your
presentation.</p>
<form action="<?= $App->SCRIPT_NAME ?>" method="POST">
<input type="hidden" name="action" value="add_link">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">

<label>url</label>
<input type="text" name="link_url" size="50" maxlength="200" value="http://">
<br class="end">

<label>text</label>
<input type="text" name="link_text" size="50" maxlength="150" value="">
<br class="end">

<p>
<input type="submit" name="add_link" value="Add Link">
<input type="submit" name="cancel_session" value="Cancel">
</p>

</form>
</div>