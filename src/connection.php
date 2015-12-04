<?php
include("config.php");

$g_link = null;
function GetMyConnection() {
	global $g_link;
	global $config_mysql_host;
	global $config_mysql_user;
	global $config_mysql_password;
	global $config_mysql_database;

	if($g_link )
		return $g_link;
	$g_link = mysqli_connect($config_mysql_host, $config_mysql_user, $config_mysql_password, $config_mysql_database)
		or die('Could not connect to server.' );
	return $g_link;
}

function CleanUpDB() {
	global $g_link;
	if($g_link !== null)
		mysqli_close($g_link);
	$g_link = null;
}