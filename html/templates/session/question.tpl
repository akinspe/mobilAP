<h2><?= $question->question_text ?></h2>
<form action="session.php" id="add_question_form" method="POST">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">
<input type="hidden" name="view" value="<?= $view ?>">
<input type="hidden" name="question_id" value="<?= $question->question_id ?>">

<ul id="question_responses">
<?php foreach ($question->responses as $response) { ?>
<li><input type="<?= $question->question_maxchoices >1 ? 'checkbox' : 'radio' ?>" name="response[]" value="<?= $response->response_value ?>"> <?= $response->response_text ?></li>
<?php } ?>
</ul>

<input type="submit" name="submit_response" id="submit_response" value="Submit Response">

</form>