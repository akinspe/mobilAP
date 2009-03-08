<h2><?= $question->question_text ?></h2>
<img src="<?= $question->getChartURL() ?>">
<p>There <?= sprintf("%s %d response%s", $question->answers['total'] == 1 ? "is" : "are", $question->answers['total'], $question->answers['total'] == 1 ? "" : "s") ?>.</p>

<ol id="question_responses">
<?php foreach ($question->responses as $response) { ?>
<li><?= $response->response_text ?> <?= $question->answers[$response->response_value] ?></li>
<?php } ?>
</ol>

