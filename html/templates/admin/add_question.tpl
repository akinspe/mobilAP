<h1>Editing Session <?= $session->session_id ?></h1>

<?= $App->getMessages() ?>
<p>Questions are a key interactive component to your session. Imagine a faculty member taking an instant
poll of a topic in their class and seeing if students are understanding the material. Imagine gathering
statistical or demographic information to help stear your discussion. These are examples of how 
instant questions can assist a presentation or lecture. Keep in mind that these questions are updated
LIVE and you can even consider adding a question DURING your presentation as the conversation changes.</p>

<p>Options include the ability to select the minimum and maximum choices (Most single choice questions
can be left to the default). Also you can choose the type of chart shown when the results show.</p>

<form action="<?= $App->SCRIPT_NAME ?>" method="POST" id="poll_question_admin">
<input type="hidden" name="action" value="add_question">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">

<?php include('question_form.tpl'); ?>

<p>You will add the responses after you create the question</p>

<p>
<input type="submit" name="add_question" value="Add Question">
<input type="submit" name="cancel_session" value="Cancel">
</p>
</form>