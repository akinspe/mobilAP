<form action="{$SCRIPT_NAME}" method="POST" id="poll_admin">
<input type="hidden" name="action" value="{$action}">
<input type="hidden" name="poll_id" value="{$poll_id}">

<fieldset>
<legend>Poll Details</legend>

<label>Poll Name</label>
<input type="text" name="poll_name" value="{$poll_name|escape}" size="60" maxlength="100">
<br class="end">

<label>Poll Begins:</label>
{html_select_date field_array="poll_begin" prefix="" start_year=2002 end_year="+1" month_format="%b" day_value_format="%02d" time=$poll_begin} at 
{html_select_time field_array="poll_begin" prefix="" minute_interval="5" time=$poll_begin display_seconds=false use_24_hours=false}
<br class="end">

<label>Poll Deadline:</label>
{html_select_date field_array="poll_deadline" prefix="" start_year=2002 end_year="+1" month_format="%b" day_value_format="%02d" time=$poll_deadline} at 
{html_select_time field_array="poll_deadline" prefix="" minute_interval="5" time=$poll_deadline display_seconds=false use_24_hours=false}
<br class="end">
</fieldset>

<p>
<input type="submit" name="update_poll" value="Update Poll Data">
<input type="submit" name="delete_poll" value="Delete poll" class="confirm">
</p>

<fieldset>
<legend>Poll Users</legend>

<label>Publicly viewable:</label>
{html_radios options=$YesNo name="poll_read_public" selected=$poll_read_public class="poll_read_public" id="poll_read_public" labels=false}
<br class="end">

<label>Anonymous voting:</label>
{html_radios options=$YesNo name="poll_vote_anon" selected=$poll_vote_anon class="poll_vote_anon" id="poll_vote_anon" labels=false}
<br class="end">

<label>Results viewable before deadline:</label>
{html_radios options=$YesNo name="poll_results_live" selected=$poll_results_live labels=false}
<br class="end">

<div id="poll_users">
<div style="float: right; width: 15em; max-height: 15em; overflow:auto;">
<h3>Users: ({$poll_users|@count})</h3>
<ul>
{foreach from=$poll_users item=u}
	<li>{$u|getFullName}</li>
{/foreach}
</ul>
</div>

{foreach from=$poll_rules item=rule key=ruleID name="rules"}
{if $smarty.foreach.rules.first}
<ul>
{/if}
    <li><input type="submit" name="remove_rule[{$ruleID}]" value="Remove" class="confirm"> {$rule->rule_text}</li>
{if $smarty.foreach.rules.last}
</ul>
{/if}
{foreachelse}
    <div class="message">No users for this poll.</div>
{/foreach}

<label>Add Rule</label>
{html_options id="add_rule_type" name="add_rule_type" options=$rule_type_data selected=$add_rule_type output_field=name first='-- Choose --'}
{include_shared file="user_rule_fields.tpl" rule_type_data=$rule_type_data}
<br class="end">
</div>

</fieldset>


<h2>Questions</h2>
<ol>
{foreach from=$questions item=question}
<li><a href="{$SCRIPT_NAME}?action=edit_question&amp;question_id={$question->question_id}&amp;poll_id={$poll_id}">{$question->question_text}</a> <input type="submit" name="remove_question[{$question->index}]" value="remove" class="confirm"></li>
{/foreach}
</ol>

<label>Add question:</label>
<input type="text" name="add_question_text" size="40" maxlength="200">
<input type="submit" name="add_question" value="Add question">
</form>

<h2>Poll Link</h2>
<a href="{$smarty.const.INTRANET_SERVER}/poll?poll_id={$poll_id}">{$smarty.const.INTRANET_SERVER}/poll?poll_id={$poll_id}</a>