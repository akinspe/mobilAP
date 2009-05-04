<ul id="session_nav" class="nav">
<?php 
	$views = array('info'=>'Info', 'links'=>'Links', 'questions'=>'Questions', 'discussion'=>'Discussion', 'evaluation'=>'Evaluation');
	if (!($session->session_flags & mobilAP_session::SESSION_FLAGS_LINKS)) unset($views['links']);
	if (!($session->session_flags & mobilAP_session::SESSION_FLAGS_DISCUSSION)) unset($views['discussion']);
	if (!($session->session_flags & mobilAP_session::SESSION_FLAGS_EVALUATION) || count($evaluation_questions)==0) unset($views['evaluation']);
	if (count($session->session_questions)==0) unset($views['questions']);
	
	foreach ($views as $_view=>$_view_title) { ?>
 	<li<?php if ($view==$_view) {?> class="active"<?php } ?>><a href="session.php?session_id=<?= $session->session_id ?>&view=<?= $_view ?>"><?= $_view_title ?></a></li>
<?php } ?>
</ul>
<div class="clearbox"></div>
