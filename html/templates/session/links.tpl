<ul id="session_links">
<?php
foreach ($session->session_links as $link) { ?>
<li><a href="<?= $link->link_url ?>"><?= $link->link_text ?></a></li>

<?php } ?>
</ul>

<form action="session.php" id="add_link_form">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">
<input type="hidden" name="view" value="<?= $view ?>">
<fieldset>
<legend>Add Link</legend>
<label>URL</label>
<input type="text" name="link_url" value="http://" id="link_url">
<label>Title</label>
<input type="text" name="link_text" value="" id="link_text">

<input type="submit" name="add_link" id="add_link" value="Add Link">

</fieldset>
</form>