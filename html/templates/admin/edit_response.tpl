<div class="content">
<h1>Editing Session <?= $session->session_id ?></h1>
<h2>Question: <?= $question->question_text ?></h2>

<?= $App->getMessages() ?>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST" id="poll_question_admin">
<input type="hidden" name="action" value="edit_response">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">
<input type="hidden" name="question_id" value="<?= $question->question_id ?>">
<input type="hidden" name="response_value" value="<?= $response->response_value ?>">

<label>Edit response</label>
<input type="text" name="response_text" id="response_text" value="<?= htmlentities($response->response_text) ?>">
<br class="end">

<p>
<input type="submit" name="update_response" value="Save">
<input type="submit" name="cancel_question" value="Don't Save">
<input type="submit" name="remove_response" value="Remove Response" class="confirm">
</p>

</form>
</div>