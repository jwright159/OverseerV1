<?php
require_once "header.php";

$_POST['session'] = str_replace(">", "", $_POST['session']); //this is why we can't have nice things
$_POST['session'] = str_replace("<", "", $_POST['session']);
$_POST['session'] = str_replace("'", "", $_POST['session']); //kill apostrophes while we're at it
$result = $mysqli->query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '" . $_POST['session'] . "'");
$clash = false;

if ($_POST['session'] != "" && $_POST['sessionpw'] == $_POST['confirmpw']) {
	while ($row = $result->fetch_array()) {
		if ($_POST['session'] == $row['name']) { //Name clash: Session name is already taken.
			echo "Session creation failed: Session name is in use.";
			$clash = true;
		}
	}
	if ($clash == false) {
		$name = $mysqli->real_escape_string($_POST['session']);
		$pw = $mysqli->real_escape_string($_POST['sessionpw']);
		if (!empty($_POST['randoms'])) {
			$randoms = "1";
		} else {
			$randoms = "0";
		}
		if (!empty($_POST['unique'])) {
			$unique = "1";
		} else {
			$unique = "0";
		}
		if (!empty($_POST['challenge'])) {
			$chall = "1";
		} else {
			$chall = "0";
		}
		if (!empty($_POST['canon'])) {
			$canon = "1";
		} else {
			$canon = "0";
		}
		if (!empty($_POST['admin'])) {
			$mysqli->query("INSERT INTO `Sessions` (`name` ,`password` ,`admin` ,`allowrandoms` ,`uniqueclasspects` ,`challenge`, `canon`)VALUES ('$name', '$pw', 'default', $randoms, $unique, $chall, $canon);"); //default is the flag for "first player to enter receives admin powers"
		} else {
			$mysqli->query("INSERT INTO `Sessions` (`name` ,`password` ,`allowrandoms` ,`uniqueclasspects` ,`challenge`, `canon`)VALUES ('$name', '$pw', $randoms, $unique, $chall, $canon);");
		}
		echo "Session $name creation successful.";
	}
} else {
	echo "Session creation failed: Session name empty or passwords do not match.";
}
$mysqli->close();
echo '<br/><a href="/">Home</a>';
require_once "footer.php";
?>