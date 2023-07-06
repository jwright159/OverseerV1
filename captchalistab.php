<?php
require_once "header.php";
if (empty($_SESSION['username'])) {
	echo "Log in to access the captchalogue list.<br/>";
} else {

	if ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods") {
		$result = $mysqli->query("SELECT * FROM Captchalogue ORDER BY name");
		while ($row = $result->fetch_array()) {
			$realname = str_replace("\\", "", $row['name']);
			echo "$realname<br/>";
		}
	} else {
		$result = $mysqli->query("SELECT * FROM Captchalogue ORDER BY name");
		while ($row = $result->fetch_array()) {
			$realname = str_replace("\\", "", $row['name']);
			echo $realname . "=" . $row['captchalogue_code'] . "<br/>";
		}
		$sresult = $mysqli->query("SELECT * FROM System");
		$srow = $sresult->fetch_array();
		$newaddlog = $srow['debuglog'] . "<br/>Dev Captchalist accessed by " . $username;
		$newaddlog = $mysqli->real_escape_string($newaddlog);
		$mysqli->query("UPDATE `System` SET `debuglog` = '$newaddlog' WHERE 1");
	}
}
?>