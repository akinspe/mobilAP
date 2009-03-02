<div class="content">
<h1><?= $session->session_id . ' ' . $session->session_title ?></h1>
<?php
include('templates/session/session_nav.tpl'); 
?>
<?= $App->getMessages() ?>

<?php
include("templates/session/{$view_template}.tpl");
?>
</div>
