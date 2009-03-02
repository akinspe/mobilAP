<fieldset>
<legend>Evaluation Question</legend>

<label>Question:</label>
<input type="text" name="question_text" value="<?= htmlentities($question->question_text) ?>" size="70" maxlength="200">
<br class="end">

<label>Response type:</label>
<input type="radio" name="question_response_type" value="M"<?php if ($question->question_response_type=='M') echo "CHECKED"?>> Multiple Choices
<input type="radio" name="question_response_type" value="T"<?php if ($question->question_response_type=='T') echo "CHECKED"?>> Text (open answer)<br>
<br class="end">
</fieldset>

