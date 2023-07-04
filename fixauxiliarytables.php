<?php
session_start();
if (empty($_SESSION['username'])) {
	echo "Log in to fix the overseer's derps</br>";
	echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} else {
	$con = $mysqli->connect("localhost", "theovers_DC", "pi31415926535");
	if (!$con) {
		echo "Connection failed.\n";
		die('Could not connect: ' . $mysqli->error());
	}
	$mysqli->select_db("theovers_HS", $con);
	$username = $_SESSION['username'];
	$result = $mysqli->query("SELECT `username` FROM Players");
	if ($username != "The Overseer") {
		echo "Hey! This tool is for The Overseer only. Nice try, pal.";
	} else {
		while ($row = $result->fetch_array()) {
			$echeresult = $mysqli->query("SELECT `username` FROM Echeladders WHERE `Echeladders`.`username` = '$row[username]' LIMIT 1;");
			if (!($echerow = $echeresult->fetch_array())) {
				$mysqli->query("INSERT INTO `Echeladders` (`username`) VALUES ('$row[username]');"); //Give the player an Echeladder. Players love echeladders.
				$mysqli->query("INSERT INTO `Messages` (`username`) VALUES ('$row[username]');"); //Create entry in message table.
				$mysqli->query("INSERT INTO `Ability_Patterns` (`username`) VALUES ('$row[username]');"); //Create entry in pattern table.
				echo "$row[username] fixed.</br>";
			}
		}
	}
}
?>