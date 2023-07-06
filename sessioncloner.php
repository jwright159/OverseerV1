<?php
require_once "header.php";

if (empty($_SESSION['username'])) {
	echo "Log in to do stuff.<br/>";
} elseif ($userrow['session_name'] != "Developers") {
	echo "You need to be a developer to do that!";
} else {
	if (!empty($_POST['clone'])) {
		echo "Looking up session " . $_POST['clone'] . "...<br/>";
		$cloneresult = $mysqli->query("SELECT * FROM `Sessions` WHERE `Sessions`.`name` = '" . $_POST['clone'] . "' LIMIT 1;");
		$clonerow = $cloneresult->fetch_array();
		if ($clonerow['name'] == $_POST['clone']) {
			echo "Session found! Cloning...<br/>";
			$fieldresult = $mysqli->query("SELECT * FROM `Sessions` LIMIT 1;");
			$clonequery = "INSERT INTO `Sessions` (";
			$clonevalues = "VALUES (";
			$dbpass = "debug" . strval(rand(1, 999999));
			while ($field = $fieldresult->fetch_field()) {
				$fname = $field->name;
				$clonefield = $mysqli->real_escape_string(strval($clonerow[$fname]));
				if ($fname == "name") {
					$clonefield .= "_DEBUG";
				} elseif ($fname == "password") {
					$clonefield = $dbpass;
				} elseif ($fname == "allowrandoms") {
					$clonefield = "0"; //don't allow random people to join this session! that'd be awkward!
				}
				$clonequery .= "`" . $fname . "`, ";
				$clonevalues .= "'$clonefield', ";
			}
			$clonequery = substr($clonequery, 0, -2) . ") ";
			$clonevalues = substr($clonevalues, 0, -2) . ");";
			//$mysqli->query($clonequery . $clonevalues);
			echo $clonequery . $clonevalues . "<br/>"; //testing
			echo "Done! Looking up players...<br/>";
			$fieldresult = $mysqli->query("SELECT * FROM `Players` LIMIT 1;"); //get the fields
			$i = 1;
			$clonequery = "INSERT INTO `Players` (";
			while ($field = $fieldresult->fetch_field()) {
				$pfname[$i] = $field->name;
				$clonequery .= "`" . $pfname[$i] . "`, ";
				$i++;
			}
			$clonequery = substr($clonequery, 0, -2) . ") "; //we'll use this clonequery for all of the players
			$istop = $i;
			$cloneresult = $mysqli->query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $_POST['clone'] . "'");
			while ($row = $cloneresult->fetch_array()) {
				$i = 1;
				$clonevalues = "VALUES (";
				while ($i < $istop) {
					$fname = $pfname[$i];
					$clonefield = $mysqli->real_escape_string(strval($row[$fname]));
					if ($fname == "username" || $fname == "session_name" || $fname == "client_player" || $fname == "server_player" || $fname == "aiding" || $fname == "questland") { //catch any field that can have a username/session name and add "_DEBUG" to it
						if (!empty($clonefield))
							$clonefield .= "_DEBUG";
					} elseif ($fname == "password") {
						$clonefield = password_hash($dbpass, PASSWORD_BCRYPT);
					}
					$clonevalues .= "'$clonefield', ";
					$i++;
				}
				$clonevalues = substr($clonevalues, 0, -2) . ");";
				//$mysqli->query($clonequery . $clonevalues);
				echo $clonequery . $clonevalues . "<br/>"; //testing
				$mysqli->query("INSERT INTO `Echeladders` (`username`) VALUES ('$row[username]');"); //Give the player an Echeladder. Players love echeladders.
				$mysqli->query("INSERT INTO `Messages` (`username`) VALUES ('$row[username]');"); //Create entry in message table.
				$mysqli->query("INSERT INTO `Ability_Patterns` (`username`) VALUES ('$row[username]');"); //Create entry in pattern table.
				//we're making new rows here instead of copying them because they're not as important
				echo $row['username'] . " cloned.<br/>";
			}
			echo "Cloning complete! Global password for cloned session: " . $dbpass . "<br/>This password can be used to log in to any cloned account in this session, or to create a new account in this session.<br/>";
		} else {
			echo "Session not found. Aborting.<br/>";
		}
	}

	if (!empty($_POST['clean'])) {
		if (strpos($_POST['clean'], "_DEBUG") !== false) {
			echo "Cleaning session " . $_POST['clean'] . "...<br/>";
			$mysqli->query("DELETE FROM `Sessions` WHERE `Sessions`.`name` = '" . $_POST['clean'] . "'");
			echo "Cleaning players from session...<br/>";
			$cloneresult = $mysqli->query("SELECT * FROM `Players` WHERE `Players`.`session_name` = '" . $_POST['clean'] . "'");
			while ($row = $cloneresult->fetch_array()) {
				$thisname = $row['username'];
				$mysqli->query("DELETE FROM `Players` WHERE `Players`.`username` = '" . $thisname . "'");
				$mysqli->query("DELETE FROM `Echeladders` WHERE `Echeladders`.`username` = '" . $thisname . "'");
				$mysqli->query("DELETE FROM `Ability_Patterns` WHERE `Ability_Patterns`.`username` = '" . $thisname . "'");
				$mysqli->query("DELETE FROM `Messages` WHERE `Messages`.`username` = '" . $thisname . "'");
			}
			echo "Done!<br/>";
		} else
			echo "You can't clean a non-debug session!<br/>";
	}

	echo "Blahsadfeguie's Session Cloner<br/>";
	echo '<form action="sessioncloner.php" method="post">Session to clone: <input type="text" name="clone" /><input type="submit" value="Clone it!" /></form><br/>';
	echo "Be sure to clean up the debug session when you're done with it:<br/>";
	echo '<form action="sessioncloner.php" method="post">Cloned session to clean up: <input type="text" name="clean" /><input type="submit" value="Clean it!" /></form><br/>';
}

require_once "footer.php";
?>