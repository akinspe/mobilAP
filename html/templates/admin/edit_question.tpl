<div class="content">
<h1>Editing Session <?= $session->session_id ?></h1>

<?= $App->getMessages() ?>
<form action="<?= $App->SCRIPT_NAME ?>" method="POST" id="poll_question_admin">
<input type="hidden" name="action" value="edit_question">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">
<input type="hidden" name="question_id" value="<?= $question->question_id ?>">

<fieldset>
<legend>Question Results</legend>

<label>Link:</label>
Use this address to view a automatically updating display of question results:
<p><a href="results.html?question_id=<?= $question->question_id ?>">results.html?question_id=<?= $question->question_id ?></a></p>
</fieldset>

<?php include('question_form.tpl') ?>

<fieldset>
<legend>Question Responses</legend>
<p>You add responses to indicate the options the user has. For best results, keep responses brief. Long responses may get cut off when viewing the chart.</p>
<ol>
<?php 

foreach ($question->responses as $response_id=>$response) { ?>
	<li><input type="submit" name="remove_response[<?= $response->response_value ?>]" value="Remove" class="confirm"> <?= htmlentities($response->response_text) ?>	
	<a href="<?= $App->SCRIPT_NAME ?>?action=edit_response&amp;session_id=<?= $session->session_id ?>&amp;question_id=<?= $question->question_id ?>&amp;response_value=<?= $response->response_value ?>">edit</a>
	</li>
<?php } ?>
</ol>

<label>Add response</label>
<input type="text" name="add_response_text" id="add_response_text">
<input type="submit" name="add_response" value="add" id="add_response"> 
<br class="end">

</fieldset>

<fieldset>
<legend>Question Answers</legend>
<p>If you are testing this question you can clear your test responses</p>
<input type="submit" name="clear_answers" value="Clear all answers" class="confirm">

<ul>
	<li>Total: <?= $question->answers['total'] ?></li>
<?php	
foreach ($question->responses as $response_id=>$response) { ?>
	<li><?= htmlentities($response->response_text) ?> <?= $question->answers[$response->response_value] ?></li>
<?php } ?>
</ul>
<a href="<?= $App->SCRIPT_NAME ?>?action=view_responses&amp;session_id=<?= $session->session_id ?>&amp;question_id=<?= $question->question_id ?>">View Detailed Results</a>
</fieldset>

<p>
<input type="submit" name="update_question" value="Save">
<input type="submit" name="cancel_session" value="Don't Save">
<input type="submit" name="delete_question" value="Remove Question" class="confirm">
</p>
</form>
</div>