<?php
include('templates/nav.tpl'); 
include('templates/session/session_nav.tpl'); 
?>
<div class="content">
<h1><?= $session->session_id . ' ' . $session->session_title ?></h1>
<?= $App->getMessages() ?>

<?php
include("templates/session/{$view_template}.tpl");
?>
</div>
