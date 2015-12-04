<?php
include("header.php");
include("connection.php");
include("bencode.php");
include("func.php");

$mysq_link = GetMyConnection();

if(!isset($_GET['torrent']) && strlen($_GET['torrent'])!=40) {
	exit;
}

$sha = mysqli_real_escape_string($mysq_link, $_GET['torrent']);
$sql = "SELECT name, date FROM torrents WHERE sha='$sha'";
$result = mysqli_query($mysq_link, $sql);

if($result && mysqli_num_rows($result) > 0) {
	if($row = mysqli_fetch_assoc($result)) {
		echo 'Name: ';
		echo '<a href="' . $config_upload_dir . $sha . '.torrent">' . $row['name'] . '</a> ';
		echo '<a href="torrent.php?action=delete&sha=' . $sha . '">[X]</a><br><br>';

		echo 'Date: '. $row['date'] . '<br><br>';

		$torrent_file = $config_upload_dir . $sha . '.torrent';
		$myfile = fopen($torrent_file, "r") or die("Unable to open file!");
		$str_file = fread($myfile, filesize($torrent_file));
		fclose($myfile);
		$r = ben_decode($str_file);
		if (is_valid_torrent($r)) {
			if(($size = get_lenght($r)) !== null) {
				echo "Size: ".get_format_size($size) . "<br><br>";
				echo "Files:<br>";
				echo get_torrent_name($r);
			} else {
				echo "Size: ". get_format_size(get_multy_lenght($r)) . "<br><br>";
				echo "Files:<br>";
				$count = get_multy_count($r);
				for($i=0;$i<$count;++$i) {
					echo get_multy_index($r, $i) . "<br>";
				}
			}
		}
	}
}