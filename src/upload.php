<?php
include("header.php");
include("connection.php");
$mysq_link = GetMyConnection();
?>
<form action="upload.php" method="post" enctype="multipart/form-data">
    File to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload" name="submit">
</form>

<?php
include("bencode.php");
include("func.php");
include("config.php");

if(isset($_POST["submit"])) {
	if(isset($_FILES["fileToUpload"])) {
		$myfile = fopen($_FILES["fileToUpload"]["tmp_name"], "r") or die("Unable to open file!");
		$str_file = fread($myfile, filesize($_FILES["fileToUpload"]["tmp_name"]));
		fclose($myfile);

		$r = ben_decode($str_file);
		if(is_valid_torrent($r)) {
			$r[0]['announce'] = $config_announce;
			$r[0]['announce-list'] = 0;
			$str_file = ben_encode($r[0]['info']);
			$sha1_torrent = sha1('d'.$str_file.'e');
			echo $sha1_torrent . "<br>";
			$str_file = ben_encode($r);
			$torrent_name = mysqli_real_escape_string($mysq_link, get_torrent_name($r));
			$sql = "INSERT INTO torrents (name, sha) VALUES ('$torrent_name', '$sha1_torrent')";

			if (mysqli_query($mysq_link, $sql)) {
				echo "New record created successfully";
				$myfile = fopen($config_upload_dir.$sha1_torrent.".torrent", "w");
				fwrite($myfile, $str_file, strlen($str_file));
				fclose($myfile);
			} else {
				echo "Error: " . $sql . "<br>" . mysqli_error($mysq_link);
			}
		}
	}
}
