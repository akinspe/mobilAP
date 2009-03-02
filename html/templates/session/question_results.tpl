<h2><?= $question->question_text ?></h2>
<img src="<?= $question->getChartURL() ?>">
<ul id="question_responses">
<?php foreach ($question->responses as $response) { ?>
<li><?= $response->response_text ?> <?= $question->answers[$response->response_value] ?></li>
<?php } ?>
</ul>

