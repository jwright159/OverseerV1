<?php
//This is a filler line.
if ($_POST['mako'] == "kawaii") {
	require_once "includes/SQLconnect.php";
	$result = $mysqli->query("SELECT * FROM Players WHERE `Players`.`username` = '" . $mysqli->real_escape_string($_POST['username']) . "'");
	$loggedin = False;

	while ($userrow = $result->fetch_array()) {
		if ($userrow['username'] == $mysqli->real_escape_string($_POST['username']) && password_verify($mysqli->real_escape_string($_POST['password']), $userrow['password'])) {
			session_start(); //Begin initializing the session here. This involves initializing anything we don't want to call from the database all the time.
			$username = $mysqli->real_escape_string($_POST['username']);
			$_SESSION['username'] = $username;
			$titleresult = $mysqli->query("SELECT * FROM `Titles` WHERE `Titles`.`Class` = 'Adjective'");
			$titlerow = $titleresult->fetch_array();
			//Grab aspect and class modifiers.
			if (!empty($titlerow[$userrow['Aspect']])) {
				$_SESSION['adjective'] = $titlerow[$userrow['Aspect']];
				$classresult = $mysqli->query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$userrow[Class]';");
				$_SESSION['classrow'] = $classresult->fetch_array();
				$aspectresult = $mysqli->query("SELECT * FROM `Aspect_modifiers` WHERE `Aspect_modifiers`.`Aspect` = '$userrow[Aspect]';");
				$_SESSION['aspectrow'] = $aspectresult->fetch_array();
			}
			//Grab grist types.
			$gristresult = $mysqli->query("SELECT * FROM `Grist_Types`");
			while ($row = $gristresult->fetch_array()) {
				$_SESSION[$row['name']] = $row;
			}
			$mysqli->query("UPDATE `Players` SET `active` = 1 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
			if ($userrow['equipped'] != "") {
				$equipname = str_replace("'", "\\\\''", $userrow[$userrow['equipped']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $equipname . "'");
				while ($row = $itemresult->fetch_array()) {
					$itemname = $row['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $userrow[$userrow['equipped']]) {
						$_SESSION['mainrow'] = $row; //We save this to check weapon-specific bonuses to various commands.
					}
				}
			}
			if ($userrow['offhand'] != "" && $userrow['offhand'] != "2HAND") {
				$offname = str_replace("'", "\\\\''", $userrow[$userrow['offhand']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $offname . "'");
				while ($row = $itemresult->fetch_array()) {
					$itemname = $row['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $userrow[$userrow['offhand']]) {
						$_SESSION['offrow'] = $row;
					}
				}
			}
			if ($userrow['headgear'] != "") {
				$headname = str_replace("'", "\\\\''", $userrow[$userrow['headgear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $headname . "'");
				while ($row = $itemresult->fetch_array()) {
					$itemname = $row['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $userrow[$userrow['headgear']]) {
						$_SESSION['headrow'] = $row; //We save this to check weapon-specific bonuses to various commands.
					}
				}
			}
			if ($userrow['facegear'] != "" && $userrow['facegear'] != "2HAND" && $userrow['dreamingstatus'] == "Awake") {
				$facename = str_replace("'", "\\\\''", $userrow[$userrow['facegear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $facename . "'");
				while ($row = $itemresult->fetch_array()) {
					$itemname = $row['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $userrow[$userrow['facegear']]) {
						$_SESSION['facerow'] = $row; //We save this to check weapon-specific bonuses to various commands.
					}
				}
			}
			if ($userrow['bodygear'] != "") {
				$bodyname = str_replace("'", "\\\\''", $userrow[$userrow['bodygear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $bodyname . "'");
				while ($row = $itemresult->fetch_array()) {
					$itemname = $row['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $userrow[$userrow['bodygear']]) {
						$_SESSION['bodyrow'] = $row; //We save this to check weapon-specific bonuses to various commands.
					}
				}
			}
			if ($userrow['accessory'] != "" && $userrow['dreamingstatus'] == "Awake") {
				$accname = str_replace("'", "\\\\''", $userrow[$userrow['accessory']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
				$itemresult = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $accname . "'");
				while ($row = $itemresult->fetch_array()) {
					$itemname = $row['name'];
					$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
					if ($itemname == $userrow[$userrow['accessory']]) {
						$_SESSION['accrow'] = $row; //We save this to check weapon-specific bonuses to various commands.
					}
				}
			}
			$loggedin = True;
			echo "true";
		}
	}
	if ($loggedin == False) {
		echo "false";
	}
	$mysqli->close();
} else {
	echo '<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<script>
$(document).ready(function () {
    window.location = "loginer.php";
});
</script>';
}
?>