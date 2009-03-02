<?php

$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
if (preg_match('#AppleWebKit/.*Mobile/#', $user_agent)) {
	include('index_mobile.html');
	exit();
}

require('inc/app_classes.php');

$App = new Application();

$PAGE_TITLE = "mobilAP";
$PAGE = 'index';
include('templates/header.tpl');
include('templates/nav.tpl');

?>
<h1>mobileAP Home</h1>

<?php

include('templates/footer.tpl');

?>