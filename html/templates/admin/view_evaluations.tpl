<h1>Evaluation Summary</h1>

<p><?= count($evaluations) ?> evaluations submitted</p>


<h2>Responses</h2>
<p>Average response:
(Exceptional=1, Good=2, Fair=3, Poor=4)

<ol>
	<li>Value of session: <?= $eval_summary['q0'] ?></li>
	<li>Relevance of Information Presented: <?= $eval_summary['q1'] ?></li>
	<li>Effectiveness of Presenters: <?= $eval_summary['q2'] ?></li>
	<li>From what I learned in this session, I will make changes: 
		<ul>
			<li>Definately: <?= $eval_summary['q3'][1] ?></li>
			<li>Maybe: <?= $eval_summary['q3'][2] ?></li>
			<li>Possibly: <?= $eval_summary['q3'][3] ?></li>
			<li>Unlikely: <?= $eval_summary['q3'][4] ?></li>
		</ul>
	</li>		
</ol>
<h2>Comments</h2>
<ul>
<?php
foreach ($evaluations as $evaluation) {
	if ($evaluation['q4']) { ?>
<li><?= $evaluation['q4'] ?></li>
<?php
	}
} ?>
</ul>

