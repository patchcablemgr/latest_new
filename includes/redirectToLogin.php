<?php
if ($qls->user_info['username'] == '') {
	$uri = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);
	header('Location: '.$uri.'login.php');
}
?>
