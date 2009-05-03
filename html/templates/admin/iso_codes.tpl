<div class="content">
<h1>ISO Country Codes</h1>

<p>Use these codes in your attendee import spreadsheet</p>

<table border="1">
<?php 

foreach ($countries as $code=>$country) { ?>
<tr>
	<td><?= $code ?></td>
	<td><?= $country ?></td>
</tr>
<?php } ?>
</table>
</div>