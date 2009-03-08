<div id="session_abstract">
<?= $session->session_abstract ?>
</div>

<?php 
$presenters = $session->getPresenters();

if (count($presenters)>0) { ?>
<h3>Presenters</h3>
<ul>
<?php 
foreach ($presenters as $presenter) { ?>
	<li>
		<div class="directory_list_name"><a href="attendee_directory.php?view_attendee=<?= $presenter->attendee_id ?>"><?= sprintf("%s %s", $presenter->FirstName, $presenter->LastName) ?></a></div>
	<?php if ($presenter->organization) { ?><div class="directory_list_organization"><?= $presenter->organization ?></div><?php } ?>
	</li>
<?php } ?>
</ul>
<?php } ?>
