<?php
include("connection.php");
include("config.php");
include("header.php");
$mysq_link = GetMyConnection();

$page = 0;

if(isset($_GET['page'])) {
	$page = (int)$_GET['page'];
}

if(isset($_GET['action'])){
	if($_GET['action'] == "delete" && isset($_GET['sha'])) {
		$sha = mysqli_real_escape_string($mysq_link, $_GET['sha']);
		$torrent_file = $config_upload_dir . $sha .".torrent";
		if(file_exists($torrent_file))
			unlink($torrent_file);
		$sql = "DELETE FROM torrents WHERE sha='$sha'";
		if (mysqli_query($mysq_link, $sql)) {
			echo "Record deleted successfully" . "<br>";
		} else {
			echo "Error deleting record: " . mysqli_error($mysq_link) . "<br>";
		}
	}
}
$sql = "SELECT * FROM torrents";
$result = mysqli_query($mysq_link, $sql) or die(mysqli_error($mysq_link));
$rows = mysqli_num_rows($result);

$offset = $page * $config_max_torrent_page;
$sql = "SELECT name, sha, date FROM torrents ORDER BY id DESC LIMIT $offset , $config_max_torrent_page";

$result = mysqli_query($mysq_link, $sql) or die(mysqli_error($mysq_link));

if (mysqli_num_rows($result) > 0) {
	echo '<table style="width:100%">';
	echo '<tr><td style="width: 500px;">Torrent</td><td style="width: 100px;">Date</td><td style="width: 100px;">Download</td><td style="width: 100px;">Delete</td></tr><td></td>';
	
    while($row = mysqli_fetch_assoc($result)) {
		echo '<tr><td><a href="show.php?torrent=' . $row["sha"] . '">' . $row["name"] . '</a></td>';
		echo '<td>' . $row["date"] . '</td>';
		echo '<td><a href="' . $config_upload_dir . $row["sha"] . ".torrent" . '">' . '[D]' . '</a></td>';
		echo '<td><a href="torrent.php?action=delete&sha=' . $row["sha"] . '">[X]</a><br></td></tr>';
    }
	echo '</table>';
} else {
    echo "0 results";
}

$pages = (int)ceil((float)$rows/$config_max_torrent_page);
if($pages > 0) {
	echo "<br><center>Page ";
	for($i=0;$i<$pages;++$i){
		echo '<a href="torrent.php?page=' . $i . '">'. $i . '</a> ';
	}
	echo "</center>";
}