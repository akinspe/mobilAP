<fieldset>
<legend>Question Details</legend>

<label>Question:</label>
<input type="text" name="question_text" value="<?= htmlentities($question->question_text) ?>" size="70" maxlength="200">
<br class="end">

<?php if ($mobilAP_admin) { ?>
<label>Question Text (in list):</label>
<input type="text" name="question_list_text" value="<?= htmlentities($question->question_list_text) ?>" size="50" maxlength="50">
<br class="end">
<?php } else {?>
<input type="hidden" name="question_list_text" value="<?= htmlentities($question->question_list_text) ?>">
<?php } ?>

<label>Question active:</label>
<p>Questions that are not active are not shown to attendees. This is useful for preparing questions ahead of time, but not having them active until a certain time in your presentation</p>
<input type="checkbox" name="question_active" value="-1"<?= $question->question_active ? "checked" : '' ?>>
<br class="end">

<label>Minimum choices:</label>
<?= utils::html_options(array('name'=>"question_minchoices",'options'=>$question_minchoices_options, 'selected'=>$question->question_minchoices)) ?>
<br class="end">

<label>Maximum choices:</label>
<?= utils::html_options(array('name'=>"question_maxchoices",'options'=>$question_maxchoices_options, 'selected'=>$question->question_maxchoices)) ?>
<br class="end">

<label>Chart Type:</label>
<?= utils::html_options(array('name'=>"chart_type",'options'=>$chart_types, 'selected'=>$question->chart_type)) ?>
<br class="end">

</fieldset>
