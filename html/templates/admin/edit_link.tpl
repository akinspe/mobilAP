<div class="content">
<h1>Editing Session <?= $session->session_id ?></h1>

<?= $App->getMessages() ?>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST">
<input type="hidden" name="action" value="edit_link">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">
<input type="hidden" name="link_id" value="<?= $link->link_id ?>">

<label>url</label>
<input type="text" name="link_url" size="50" maxlength="200" value="<?= htmlentities($link->link_url) ?>">
<br class="end">

<label>text</label>
<input type="text" name="link_text" size="50" maxlength="150" value="<?= htmlentities($link->link_text) ?>">
<br class="end">

<p>
<input type="submit" name="update_link" value="Save">
<input type="submit" name="cancel_session" value="Don't Save">
<input type="submit" name="delete_link" value="Remove Link" class="confirm">
</p>

</form>
</div>