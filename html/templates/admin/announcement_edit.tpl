<div class="content">
<h1>Editing Announcement</h1>

<?= $App->getMessages() ?>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST">
<input type="hidden" name="action" value="<?= $action ?>">
<input type="hidden" name="announcement_id" value="<?= $announcement_id ?>">

<label>Title</label>
<input type="text" name="announcement_title" value="<?= htmlentities($announcement->announcement_title) ?>" size="50" maxlength="50">
<br>

<label>Text</label>
<textarea name="announcement_text" cols="60" rows="10"><?= htmlentities($announcement->announcement_text) ?></textarea>
<br>

<p>
<input type="submit" name="update_announcement" value="Save">
<input type="submit" name="cancel_announcement" value="Don't Save">
<input type="submit" name="delete_announcement" value="Delete announcement" class="confirm">
</p>

</form>
</div>