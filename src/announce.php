<?php

include("config.php");
include("bencode.php");
include("connection.php");
include("func.php");

$mysq_link = GetMyConnection();

$arr = array();

if(!isset($_GET['info_hash'])) {
	$arr[0]['failure reason'] = "Missing info_hash";
	$arr[0]['failure code'] = 100;
	echo ben_encode($arr);
	exit();
}
if(!isset($_GET['peer_id'])) {
	$arr[0]['failure reason'] = "Missing peer_id";
	$arr[0]['failure code'] = 102;
	echo ben_encode($arr);
	exit();
}
if(!isset($_GET['port'])) {
	$arr[0]['failure reason'] = "Missing port";
	$arr[0]['failure code'] = 103;
	echo ben_encode($arr);
	exit();
}

if(strlen($_GET['info_hash']) != 20) {
	$arr[0]['failure reason'] = "Invalid infohash: infohash is not 20 bytes long";
	$arr[0]['failure code'] = 150;
	echo ben_encode($arr);
	exit();
}
if(strlen($_GET['peer_id']) != 20) {
	$arr[0]['failure reason'] = "Invalid peerid: peerid is not 20 bytes long";
	$arr[0]['failure code'] = 151;
	echo ben_encode($arr);
	exit();
}
$sha = mysqli_real_escape_string($mysq_link, bin2hex($_GET['info_hash']));
$sql = "SELECT sha FROM torrents WHERE sha='$sha'";
$result = mysqli_query($mysq_link, $sql);

if(!$result || mysqli_num_rows($result) == 0) {
	$arr[0]['failure reason'] = "info_hash not found in the database";
	$arr[0]['failure code'] = 200;
	echo ben_encode($arr);
	exit();
}

//FIX ME - needs to be done on regular time not always
$del_sec = $config_interval * 3;
$sql = "DELETE FROM peers WHERE date < (NOW() - INTERVAL $del_sec SECOND)";
$result = mysqli_query($mysq_link, $sql);

$arr[0]['interval'] = $config_interval;
$arr[0]['peers'] = array();

$ip = mysqli_real_escape_string($mysq_link, getip());
$port = (int)$_GET['port'];
$peer_id = mysqli_real_escape_string($mysq_link, bin2hex($_GET['peer_id']));

if(isset($_GET['event'])) {
	if ($_GET['event'] == "stopped") {
		$sql = "DELETE FROM peers WHERE sha='$sha' AND ip='$ip' AND port='$port' AND peer_id='$peer_id'";
		mysqli_query($mysq_link, $sql);
		exit();
	}
}

$sql = "INSERT INTO peers (sha, date, ip, port, peer_id) VALUES ('$sha', CURRENT_TIMESTAMP, '$ip', '$port', '$peer_id') ON DUPLICATE KEY UPDATE date=VALUES(date), peer_id=VALUES(peer_id)";
mysqli_query($mysq_link, $sql);

$sql = "SELECT peer_id, ip, port FROM peers WHERE sha='$sha' LIMIT 30";
$result = mysqli_query($mysq_link, $sql);
$index = 0;
if($result && mysqli_num_rows($result) > 0) {
	while($row = mysqli_fetch_assoc($result)) {
		$arr[0]['peers'][$index] = array();
		$arr[0]['peers'][$index]['ip'] = $row["ip"];
		$arr[0]['peers'][$index]['peer id'] = hex2bin($row["peer_id"]);
		$arr[0]['peers'][$index]['port'] = (int)$row["port"];
		$index++;
	}
}

echo ben_encode($arr);
