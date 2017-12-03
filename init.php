<?php 


$connect = mysqli_connect('localhost', 'root', 'root', 'doingdone');

if (!$connect) {

	$error = mysqli_connect_error();
	$page = includeTemplate('templates/error.php', [ 'error' => $error	]);
	print($page);
	exit();

}