<?php
session_start();
if (empty($_SESSION['username'])) {
	echo "Log in to use consumable items.<br/>";
	echo '<a href="/">Home</a> <a href="controlpanel.php">Control Panel</a><br/>';
} else {
	$con = $mysqli->connect("localhost", "theovers_DC", "pi31415926535");
	if (!$con) {
		echo "Connection failed.\n";
		die('Could not connect: ' . $mysqli->error());
	}
	$mysqli->select_db("theovers_HS", $con);
	$username = $_SESSION['username'];
	$result = $mysqli->query("SELECT * FROM Players");
	while ($row = $result->fetch_array()) { //Fetch the user's database row. We're going to need it several times.
		if ($row['username'] == $username) {
			$userrow = $row;
		}
	}
	if ($userrow['session_name'] != "Developers") {
		echo "Hey! This tool is for the developers only. Nice try, pal.";
	} else {
		$i = 4;
		while ($i <= 612) {
			$rungstr = "rung" . strval($i);
			$mysqli->query("ALTER TABLE `Echeladders` ADD `" . $rungstr . "` VARCHAR( 100 ) NOT NULL;");
			echo "$rungstr added.";
			$i++;
		}
	}
}
?>