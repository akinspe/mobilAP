<h2>Session Evaluation</h2>

<p>We appreciate your feedback. Please answer the following brief questions and include additional comments if you wish.</p>
<form action="session.php" id="evaluation_form" method="POST">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">
<input type="hidden" name="view" value="<?= $view ?>">

<?php 
	$evaluation_questions = mobilAP::getEvaluationQuestions();

foreach ($evaluation_questions as $index=>$evaluation_question)
{ ?>
<h3><?= sprintf("%d. %s", $index+1, $evaluation_question->question_text) ?></h3>
<?php 
switch ($evaluation_question->question_response_type)
{
	case 'M': ?>
<ul class="evaluation_responses">
<?php 
	foreach ($evaluation_question->responses as $response) { ?>
		<li><input type="radio" name="responses[<?= $index ?>]" value="<?= $response['response_value'] ?>"> <?= $response['response_text'] ?></li>
	<?php } ?>
</ul>
<?php	
		break;
	case 'T': ?>
<textarea name="responses[<?= $index ?>]" cols="50" rows="8"></textarea>
	
<?php
		break;
}
?>

<?php } ?>

<br>
<input type="submit" name="submit_evaluation" value="Submit Post">
</form>
	