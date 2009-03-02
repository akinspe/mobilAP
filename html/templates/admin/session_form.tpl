<?php if (!$session->session_id) { ?>
<label>Session Number:</label>
<input type="text" name="session_id" value="" size="4" maxlength="3">	
<br class="end">
<?php } ?>

<label>Session Title</label>
<input type="text" name="session_title" value="<?= htmlentities($session->session_title) ?>" size="60" maxlength="100">	
<br class="end">

<label>Session Abstract</label>
<textarea name="session_abstract" rows="15" cols="60"><?= htmlentities($session->session_abstract) ?></textarea>
<br class="end">
