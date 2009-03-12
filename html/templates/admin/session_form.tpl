<label>Session Number:</label>
<input type="text" name="<?php if ($session->session_id) echo "edit_"; ?>session_id" value="<?= $session->session_id ?>" size="4" maxlength="3">
<br class="end">

<label>Session Title</label>
<input type="text" name="session_title" value="<?= htmlentities($session->session_title) ?>" size="60" maxlength="100">	
<br class="end">

<label>Session Abstract</label>
<textarea name="session_abstract" rows="15" cols="60"><?= htmlentities($session->session_abstract) ?></textarea>
<br class="end">

<label>Options</label>
<ul>
	<li><input type="checkbox" name="session_flags[]" value="<?= mobilAP_session::SESSION_FLAGS_LINKS ?>"<?php if ($session->session_flags & mobilAP_session::SESSION_FLAGS_LINKS) echo ' checked';?>> Show Links</li>
	<li><input type="checkbox" name="session_flags[]" value="<?= mobilAP_session::SESSION_FLAGS_ATTENDEE_LINKS ?>"<?php if ($session->session_flags & mobilAP_session::SESSION_FLAGS_ATTENDEE_LINKS) echo ' checked';?>> Allow non-presenters to post links</li>
	<li><input type="checkbox" name="session_flags[]" value="<?= mobilAP_session::SESSION_FLAGS_DISCUSSION ?>"<?php if ($session->session_flags & mobilAP_session::SESSION_FLAGS_DISCUSSION) echo ' checked';?>> Enable Discussion</li>
	<li><input type="checkbox" name="session_flags[]" value="<?= mobilAP_session::SESSION_FLAGS_EVALUATION ?>"<?php if ($session->session_flags & mobilAP_session::SESSION_FLAGS_EVALUATION) echo ' checked';?>> Enable Evaluation</li>
</ul>	