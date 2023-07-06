<?php
require_once "header.php";
if (empty($_SESSION['username'])) {
	echo "Log in to do stuff.<br/>";
} else {
	if ($userrow['session_name'] != "Developers") {
		echo "Hey! This tool is for the developers only. Nice try, pal.";
	} else {
		//echo "Begin cleanup!<br/>";

		if ($_POST['dosessions']) {
			echo "Beginning Sessions<br/>";
			$result = $mysqli->query("SELECT `name` FROM Sessions");
			while ($row = $result->fetch_array()) {
				$losername = $mysqli->real_escape_string($row['name']);
				$foundmsg = false;
				$msgresult = $mysqli->query("SELECT `username` FROM Players WHERE `Players`.`session_name` = '$losername'");
				while ($msgrow = $msgresult->fetch_array())
					$foundmsg = true;
				if (!$foundmsg) {
					$mysqli->query("DELETE FROM `Sessions` WHERE `name` = '$losername'"); //Create entry in message table.
					echo $losername . " session was empty and thus deleted<br/>";
				}
			}
			$mysqli->query("OPTIMIZE TABLE `Sessions`");
			echo "Sessions done<br/>";
		}
		if ($_POST['domessages']) {
			echo "Beginning Messages<br/>";
			$result = $mysqli->query("SELECT `username` FROM Messages");
			while ($row = $result->fetch_array()) {
				$losername = $mysqli->real_escape_string($row['username']);
				$foundmsg = false;
				$msgresult = $mysqli->query("SELECT `username` FROM Players WHERE `Players`.`username` = '$losername'");
				while ($msgrow = $msgresult->fetch_array())
					$foundmsg = true;
				if (!$foundmsg) {
					$mysqli->query("DELETE FROM `Messages` WHERE `username` = '$losername'"); //Create entry in message table.
					echo $losername . " message row did not have matching player row<br/>";
				}
			}
			$mysqli->query("OPTIMIZE TABLE `Messages`");
			echo "Messages done<br/>";
		}
		if ($_POST['doladders']) {
			echo "Beginning Echeladders<br/>";
			$result = $mysqli->query("SELECT `username` FROM Echeladders");
			while ($row = $result->fetch_array()) {
				$losername = $mysqli->real_escape_string($row['username']);
				$foundmsg = false;
				$msgresult = $mysqli->query("SELECT `username` FROM Players WHERE `Players`.`username` = '$losername'");
				while ($msgrow = $msgresult->fetch_array())
					$foundmsg = true;
				if (!$foundmsg) {
					$mysqli->query("DELETE FROM `Echeladders` WHERE `username` = '$losername'"); //Create entry in message table.
					echo $losername . " echeladder row did not have matching player row<br/>";
				}
			}
			$mysqli->query("OPTIMIZE TABLE `Echeladders`");
			echo "Echeladders done<br/>";
		}
		if ($_POST['dopatterns']) {
			echo "Beginning Patterns<br/>";
			$result = $mysqli->query("SELECT `username` FROM Ability_Patterns");
			while ($row = $result->fetch_array()) {
				$losername = $mysqli->real_escape_string($row['username']);
				$foundmsg = false;
				$msgresult = $mysqli->query("SELECT `username` FROM Players WHERE `Players`.`username` = '$losername'");
				while ($msgrow = $msgresult->fetch_array())
					$foundmsg = true;
				if (!$foundmsg) {
					$mysqli->query("DELETE FROM `Ability_Patterns` WHERE `username` = '$losername'"); //Create entry in message table.
					echo $losername . " ability row did not have matching player row<br/>";
				}
			}
			$mysqli->query("OPTIMIZE TABLE `Ability_Patterns`");
			echo "Ability patterns done<br/>";
		}
		if ($_POST['dodungeons']) {
			echo "Beginning Dungeons<br/>";
			$result = $mysqli->query("SELECT `username` FROM Dungeons");
			while ($row = $result->fetch_array()) {
				$losername = $mysqli->real_escape_string($row['username']);
				$foundmsg = false;
				$msgresult = $mysqli->query("SELECT `username` FROM Players WHERE `Players`.`username` = '$losername'");
				while ($msgrow = $msgresult->fetch_array())
					$foundmsg = true;
				if (!$foundmsg) {
					$mysqli->query("DELETE FROM `Dungeons` WHERE `username` = '$losername'"); //Create entry in message table.
					echo $losername . " echeladder row did not have matching player row<br/>";
				}
			}
			$mysqli->query("OPTIMIZE TABLE `Dungeons`");
			echo "Dungeons done<br/>";
		}
		echo '<form action="tablecleanup.php" method="post">Select which tables to clean up:<br/>
<input type="checkbox" name="dosessions" value="yes"> Sessions<br/>
<input type="checkbox" name="domessages" value="yes"> Messages<br/>
<input type="checkbox" name="doladders" value="yes"> Echeladders<br/>
<input type="checkbox" name="dopatterns" value="yes"> Ability Patterns<br/>
<input type="checkbox" name="dodungeons" value="yes"> Dungeons<br/>
<input type="submit" value="Begin"></form>';
	}
}
?>