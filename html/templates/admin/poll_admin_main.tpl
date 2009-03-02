<form action="{$SCRIPT_NAME}" method="POST">
<input type="hidden" name="action" value="{$action}">

<h2>Polls</h2>
<ul>
{foreach from=$polls item=poll}
<li><a href="{$SCRIPT_NAME}?action=edit_poll&amp;poll_id={$poll.poll_id}">{$poll.poll_name}</a> |  <a href="{$SCRIPT_NAME}?action=view_results&amp;poll_id={$poll.poll_id}">results</a></li>
{/foreach}
</ul>

<h2>Add Poll:</h2>
<input type="text" name="add_poll_name" size="60" maxlength="100">
<input type="submit" name="add_poll" value="Add Poll">
</form>