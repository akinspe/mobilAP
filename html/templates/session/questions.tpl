<ol id="session_questions">
<?php foreach ($session->session_questions as $question) { ?>
	<li><a href="session.php?session_id=<?= $session->session_id ?>&amp;view=question&amp;question_id=<?= $question->question_id ?>"><?= $question->question_text ?></a></li>
<?php } ?>
</ol>