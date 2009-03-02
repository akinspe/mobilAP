<ul id="session_nav" class="nav">
<?php foreach (array('info'=>'Info', 'links'=>'Links', 'questions'=>'Questions', 'discussion'=>'Discussion', 'evaluation'=>'Evaluation') as $_view=>$_view_title) { ?>
	<?php if ($view==$_view) { ?>
 	<li class="active"><?= $_view_title ?></li>
		<?php } elseif ($_view != 'questions' || count($session->session_questions)>0) { ?>
	<li><a href="session.php?session_id=<?= $session->session_id ?>&view=<?= $_view ?>"><?= $_view_title ?></a></li>
		<?php } ?>
<?php } ?>	
</ul>
<div class="clearbox"></div>
