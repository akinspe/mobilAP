<ul id="session_days" class="nav">
<?php 
foreach ($schedule as $_day_index=>$day)
{
?>
	<li<?php if ($day_index==$_day_index) echo ' class="active"'; ?>><a href="sessions.php?day_index=<?= $_day_index ?>"><?= strftime("%A", $day['date_ts']) ?></a></li>
<?php
}

?>
</ul>
