<h1>Editing Session <?= $session->session_id ?></h1>

<?= $App->getMessages() ?>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST">
<input type="hidden" name="action" value="edit_session">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">

<fieldset>
<legend>Session Information</legend>
<?php include('session_form.tpl') ?>

<p>
<input type="submit" name="update_session" value="Update Session Data">
<?php if ($mobilAP_admin) { ?>
<input type="submit" name="delete_session" value="Delete Session" class="confirm">
<?php } ?>
<input type="submit" name="cancel" value="Cancel">
</p>
</fieldset>

<h2>Presenters</h2>
<?php
$session_presenters = $session->getPresenters();

if (count($session_presenters)>0) { ?>
<ul>
<?php 	
	foreach ($session_presenters as $presenter_index=>$presenter) {
?>	
<li><?php if ($mobilAP_admin) { ?><input type="submit" name="remove_presenter[<?= $presenter_index ?>]" class="confirm" value="Remove"><?php } ?> <?= sprintf("%s %s (%s)", $presenter->FirstName, $presenter->LastName, $presenter->email) ?></li>
<?php 	
}
?>
</ul>
<?php } ?>

<?php if ($mobilAP_admin) { ?>
<p>
	<input type="text" name="add_presenter_id"> <input type="submit" name="add_presenter" value="Add presenter">
</p>
<?php } ?>

<h2>Links/Resources</h2>
<?php

$session_links = $session->getLinks();

if (count($session_links)>0) { ?>
<ul>
<?php
	foreach ($session_links as $link) {
?>
	<li><?= $link->link_text ?> ( <?= $link->link_url ?>) <a href="<?= $App->SCRIPT_NAME ?>?action=edit_link&amp;session_id= <?= $session->session_id ?>&amp;link_id=<?= $link->link_id ?>">edit</a></li>
<?php
	} ?>
</ul>	
<?php } else { ?>
<div class="message">There are no links posted</div>
<?php } ?>

<p>
<a href="<?= $App->SCRIPT_NAME ?>?action=add_link&amp;session_id=<?= $session->session_id ?>">Add new link</a>
</p>

<h2>Interactive Questions</h2>
<?php

$session_questions = $session->getQuestions(true);

if (count($session_questions)>0) { ?>
<ol>
<?php 
	foreach ($session_questions as $question) { ?>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=edit_question&amp;session_id=<?= $session->session_id ?>&amp;question_id=<?= $question->question_id ?>"><?= $question->question_text ?></a></li>
<?php } ?>
</ol>
<?php } else { ?>
<div class="message">There are no questions posted</div>
<?php } ?>

<p>
<a href="<?= $App->SCRIPT_NAME ?>?action=add_question&amp;session_id=<?= $session->session_id ?>">Add new question</a>
</p>

<h2>Evaluations</h2>
<?php
if ($mobilAP_admin) { ?>
<input type="submit" name="clear_evaluations" value="Clear Evaluations" class="confirm">
<?php } 
$session_evaluations = $session->getEvaluations();
?>
<p><?= count($session_evaluations) ?> evaluations has been submitted</p>
<a href="<?= $App->SCRIPT_NAME ?>?action=view_evaluations&amp;session_id=<?= $session->session_id ?>">evaluation summary</a>


<h2>Discussion</h2>
<?php
if ($mobilAP_admin) { ?>
<input type="submit" name="clear_discussion" value="Clear Discussion" class="confirm">
<?php } 
$session_chat = $session->get_Chat();
?>
<p><?= count($session_chat) ?> posts have been submitted to the discussion section</p>

</form>

<p><a href="<?= $App->SCRIPT_NAME ?>">view all sessions</a></p>