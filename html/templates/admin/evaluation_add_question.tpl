<div class="content">
<h1>Adding Evaluation Question</h1>

<?= $App->getMessages() ?>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST">
<input type="hidden" name="action" value="<?= $action ?>">

<?php include('evaluation_question_form.tpl'); ?>
<p>
	<input type="submit" name="add_question" value="Add">
	<input type="submit" name="cancel_evaluation_question" value="Cancel">
</p>
</form>
</div>