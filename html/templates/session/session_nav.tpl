<ul id="session_nav" class="nav">
<?php 
	$views = array('info'=>'Info', 'links'=>'Links', 'questions'=>'Questions', 'discussion'=>'Discussion', 'evaluation'=>'Evaluation');
	if (!($session->session_flags & mobilAP_session::SESSION_FLAGS_LINKS)) unset($views['links']);
	if (!($session->session_flags & mobilAP_session::SESSION_FLAGS_DISCUSSION)) unset($views['discussion']);
	if (!($session->session_flags & mobilAP_session::SESSION_FLAGS_EVALUATION)) unset($views['evaluation']);
	
	foreach ($views as $_view=>$_view_title) { ?>
	<?php if ($view==$_view) { ?>
 	<li class="active"><?= $_view_title ?></li>
		<?php } elseif ($_view != 'questions' || count($session->session_questions)>0) { ?>
	<li><a href="session.php?session_id=<?= $session->session_id ?>&view=<?= $_view ?>"><?= $_view_title ?></a></li>
		<?php } ?>
<?php } ?>	
</ul>
<div class="clearbox"></div>
