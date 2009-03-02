<form action="{$SCRIPT_NAME}" method="POST" id="poll_question_admin">
<input type="hidden" name="action" value="{$action}">
<input type="hidden" name="poll_id" value="{$poll_id}">
<input type="hidden" name="question_id" value="{$question_id}">

<fieldset>
<legend>Question Details</legend>
<label>Election</label>
<a href="{$SCRIPT_NAME}?action=edit_poll&amp;poll_id={$poll_id}">Edit Poll</a>
<br class="end">

<label>Question:</label>
<input type="text" name="question_text" value="{$question_text|escape}" size="40" maxlength="50">
<br class="end">

<label>Description:</label>
<textarea name="question_description" cols="60" rows="5">{$question_description}</textarea>
<br>

<label>Response Type:</label>
{if $responses|@count>0}
{$response_types[$response_type]} (to change, you must remove all responses)
<input type="hidden" name="response_type" id="response_type" value="{$response_type}">
{else}
{html_radios name="response_type" options=$response_types selected=$response_type class="response_type" labels=false}
{/if}
<br>

<label>Minimum choices:</label>
{html_options name="question_minchoices" options=$question_minchoices_options selected=$question_minchoices}
<br class="end">

<label>Maximum choices:</label>
{html_options name="question_maxchoices" options=$question_maxchoices_options selected=$question_maxchoices}
<br class="end">

</fieldset>

<fieldset>
<legend>Question Responses</legend>

<ol>
{foreach from=$responses key=response_id item=response}
	<li><input type="submit" name="remove_response[{$response->response_value}]" value="Remove" class="confirm"> {$response->response_text}
	({$response->response_description|truncate:60|default:'No description'}) <a href="{$SCRIPT_NAME}?action=edit_response&amp;poll_id={$poll_id}&amp;question_id={$question_id}&amp;response_value={$response->response_value}">edit</a>
	</li>
{/foreach}
</ol>

<label>Add response</label>
<input type="text" name="add_response_text" id="add_response_text" value=""{if $response_type=='U'} class="user_search_field"{/if}>
<br class="end">

<label>Description</label>
<textarea name="add_response_description" cols="60" rows="5"></textarea>
<br>
<input type="submit" name="add_response" value="Add Response"> 
<br class="end">

</fieldset>


<p>
<input type="submit" name="update_question" value="Update">
</p>
</form>