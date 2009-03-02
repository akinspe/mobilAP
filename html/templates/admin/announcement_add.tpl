<h2>Adding Announcement</h2>

<?= $App->getMessages() ?>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST">
<input type="hidden" name="action" value="<?= $action ?>">

<label>Title</label>
<input type="text" name="announcement_title" value="<?= htmlentities($announcement->announcement_title) ?>" size="50" maxlength="50">
<br>

<label>Text</label>
<textarea name="announcement_text" cols="60" rows="10"><?= htmlentities($announcement->announcement_text) ?></textarea>
<br>

<p>
<input type="submit" name="add_announcement" value="Add">
<input type="submit" name="cancel_announcement" value="Cancel">
</p>

</form>