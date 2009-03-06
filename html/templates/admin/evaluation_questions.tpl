<div class="content">
<h1>Evaluation Questions</h1>

<?= $App->getMessages() ?>

<p><a href="<?= $App->SCRIPT_NAME ?>?action=add_evaluation_question">add question</a></p>

<?php 
if (count($evaluation_questions)>0) { ?>
<ol> 
<?php
	foreach ($evaluation_questions as $idx=>$question)
	{ ?>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=edit_evaluation_question&amp;question_index=<?= $question->question_index ?>"><?= $question->question_text ?></a></li>
<?php	}
?>
</ol>
<?php
} else { ?>
<div class="message">No evaluation questions have been defined</div>
<?php }
?>

</div>
