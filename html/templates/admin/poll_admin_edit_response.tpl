<form action="{$SCRIPT_NAME}" method="POST" id="poll_question_admin">
<input type="hidden" name="action" value="{$action}">
<input type="hidden" name="poll_id" value="{$poll_id}">
<input type="hidden" name="question_id" value="{$question_id}">
<input type="hidden" name="response_value" value="{$response_value}">

<fieldset>
<legend>Response Details</legend>
<label>Election</label>
<a href="{$SCRIPT_NAME}?action=edit_poll&amp;poll_id={$poll_id}">Edit Poll</a>
<br class="end">

<label>Question:</label>
<a href="{$SCRIPT_NAME}?action=edit_question&amp;poll_id={$poll_id}&amp;question_id={$question_id}">Edit Question</a>
<br class="end">

<label>Response Text</label>
<input type="text" name="response_text" id="response_text" value="{$response_text|escape}"{if $response_type=='U'} class="user_search_field"{/if}>
<br class="end">

<label>Description</label>
<textarea name="response_description" cols="60" rows="5">{$response_description}</textarea>
<br class="end">

</fieldset>


<p>
<input type="submit" name="update_response" value="Update">
</p>
</form>