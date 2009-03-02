<div class="content">
<h1>Editing Evaluation Question</h1>

<?= $App->getMessages() ?>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST">
<input type="hidden" name="action" value="<?= $action ?>">
<input type="hidden" name="question_index" value="<?= $question->question_index ?>">

<?php include('evaluation_question_form.tpl'); ?>

<?php if ($question->question_response_type=='M') { ?>
<fieldset>
<legend>Question Responses</legend>
<ol>
<?php 
$responses = $question->getResponses();
foreach ($responses as $idx=>$response) { ?>
	<li><input type="submit" name="remove_response[<?= $response['response_index'] ?>]" value="Remove" class="confirm"> <?= $response['response_text'] ?>	
	</li>
<?php } ?>
</ol>

<label>Add response</label>
<input type="text" name="add_response_text" id="add_response_text" value="">
<input type="submit" name="add_response" value="add" id="add_response"> 
<br class="end">

</fieldset>
<?php } ?>
<p>
	<input type="submit" name="update_question" value="Save">
	<input type="submit" name="cancel_evaluation_question" value="Don't Save">
	<input type="submit" name="delete_question" value="Delete Question" class="confirm">
</p>
</form>
</div>