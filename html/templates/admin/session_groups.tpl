<div class="content">
<h1>Session Groups</h1>
<p>Session groups provide a way to group multiple sessions that occur at the same time into one main item. The users can then "drill down" to see the
sessions at that time. It provides a convenient way to keep the schedule compact.</p>

<p>The system does NOT check if the items occur at the same time. Incorrect results will happen if items in the same group do not share the same time.</p>

<p><a href="<?= $App->SCRIPT_NAME ?>?action=add_session_group">add session group</a></p>

<ul>
<?php foreach ($session_groups as $session_group) { ?>
	<li><a href="<?= $App->SCRIPT_NAME ?>?action=edit_session_group&amp;session_group_id=<?= $session_group->session_group_id ?>"><?= $session_group->session_group_title ?></a></li>
<?php } ?>
</ul>

</div>