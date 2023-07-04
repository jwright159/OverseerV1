<?php
require_once "header.php";
if (empty($_SESSION['username'])) {
	echo "Log in to do stuff.</br>";
} else {
	if ($userrow['session_name'] != "Developers") {
		echo "Hey! This tool is for the developers only. Nice try, pal.";
	} else {
		$result = $mysqli->query("SELECT `username` FROM Players");
		while ($row = $result->fetch_array()) {
			$losername = $row['username'];
			$foundmsg = false;
			$msgresult = $mysqli->query("SELECT `username` FROM Messages WHERE `Messages`.`username` = '$losername'");
			while ($msgrow = $msgresult->fetch_array())
				$foundmsg = true;
			if (!$foundmsg) {
				$mysqli->query("INSERT INTO `Messages` (`username`) VALUES ('$losername');"); //Create entry in message table.
				echo $losername . " lacked message table</br>";
			}
			$foundmsg = false;
			$msgresult = $mysqli->query("SELECT `username` FROM Echeladders WHERE `Echeladders`.`username` = '$losername'");
			while ($msgrow = $msgresult->fetch_array())
				$foundmsg = true;
			if (!$foundmsg) {
				$mysqli->query("INSERT INTO `Echeladders` (`username`) VALUES ('$losername');"); //Give the player an Echeladder. Players love echeladders.
				echo $losername . " lacked echeladder table</br>";
			}
			$foundmsg = false;
			$msgresult = $mysqli->query("SELECT `username` FROM Ability_Patterns WHERE `Ability_Patterns`.`username` = '$losername'");
			while ($msgrow = $msgresult->fetch_array())
				$foundmsg = true;
			if (!$foundmsg) {
				$mysqli->query("INSERT INTO `Ability_Patterns` (`username`) VALUES ('$losername');"); //Create entry in pattern table.
				echo $losername . " lacked ability pattern table</br>";
			}
		}
	}
}
?>