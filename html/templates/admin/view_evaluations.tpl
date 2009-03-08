<div class="content">
<h1>Evaluation Summary</h1>

<p><?= count($evaluations) ?> evaluations submitted</p>

<?php

foreach ($evaluation_questions as $index=>$evaluation_question)
{ ?>
<h3><?= sprintf("%d. %s", $index+1, $evaluation_question->question_text) ?></h3>
<?php 
switch ($evaluation_question->question_response_type)
{
	case 'M': ?>
	Average: <?= $eval_summary['q' . $index]['avg'] ?>
<ol class="evaluation_responses">
<?php 
	foreach ($evaluation_question->responses as $response) { ?>
		<li><b><?= $response['response_text'] ?></b> <?= $eval_summary['q' . $index]['count'][$response['response_value']] ?></li>
	<?php } ?>
</ol>
<?php	
		break;
	case 'T': ?>
<ul class="evaluation_responses">
<?php
	foreach ($eval_summary['q' . $index]  as $response) { ?>
		<li><?= $response ?></li>
	<?php } ?>
</ul>
<?php
		break;
} ?>
<?php } ?>

</div>