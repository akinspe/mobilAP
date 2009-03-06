<div class="content">
<h1>Editing Session <?= $session->session_id ?></h1>

<?= $App->getMessages() ?>
<form action="<?= $App->SCRIPT_NAME ?>" method="POST" id="poll_question_admin">
<input type="hidden" name="action" value="edit_question">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">
<input type="hidden" name="question_id" value="<?= $question->question_id ?>">

<h2><?= $question->question_text ?></h2>

<?php	
$answers = $question->getAllAnswers();

foreach ($question->responses as $response) { ?>
<h3><?= $response->response_text ?></h3>
<p><?= $question->answers[$response->response_value] ?> responses</p>
<?php
if (isset($answers[$response->response_value])) { ?>
<ul>
<?php
foreach ($answers[$response->response_value] as $answer) { ?>
<li><?= sprintf("%s %s (%s)", $answer['FirstName'], $answer['LastName'], $answer['email']) ?></li>
<?php }
} ?>
</ul>
<?php 
}
//print_r($question);
//print_r($answers);
?>
<p>
<a href="<?= $App->SCRIPT_NAME ?>?action=edit_question&amp;session_id=<?= $session->session_id ?>&amp;question_id=<?= $question->question_id ?>">Return to question</a>
</p>
</form>
</div>