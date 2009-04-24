<div class="content">
<h1>Evaluation Summary</h1>

<p><?= count($evaluations) ?> evaluations submitted</p>
<ul>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=<?= $action ?>&amp;session_id=<?= $session->session_id ?>&amp;clear_evaluations=1" class="confirm">clear evaluation data</a></li>
</ul>	

<?php

foreach ($evaluation_questions as $index=>$evaluation_question)
{ ?>
<h3><?= sprintf("%d. %s", $index+1, htmlentities($evaluation_question->question_text)) ?></h3>
<?php 
switch ($evaluation_question->question_response_type)
{
	case 'M': ?>
	Average: <?= $eval_summary['q' . $index]['avg'] ?>
<ol class="evaluation_responses">
<?php 
	foreach ($evaluation_question->responses as $response) { ?>
		<li><b><?= htmlentities($response['response_text']) ?></b> <?= $eval_summary['q' . $index]['count'][$response['response_value']] ?></li>
	<?php } ?>
</ol>
<?php	
		break;
	case 'T': ?>
<ul class="evaluation_responses">
<?php
	foreach ($eval_summary['q' . $index]  as $response) { ?>
		<li><?= htmlentities($response) ?></li>
	<?php } ?>
</ul>
<?php
		break;
} ?>
<?php } ?>

</div>