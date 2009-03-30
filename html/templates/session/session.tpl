<?php
include('templates/nav.tpl'); 
include('templates/session/session_nav.tpl'); 
?>
<div class="content">
<h1><?= sprintf("%s %s", $session->session_id, htmlentities($session->session_title)) ?></h1>
<?= $App->getMessages() ?>

<?php
include("templates/session/{$view_template}.tpl");
?>
</div>
