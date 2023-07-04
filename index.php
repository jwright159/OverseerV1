<?php
require_once "header.php";
if (empty($_SESSION['username'])) {
	echo 'This content is currently empty! This used to hold the Tumblr page before it went down.';
} else {
	//if($username == "The Overseer") sendPost($userrow['pesternoteUsername'], $userrow['pesternotePassword'], "I LOADED THE INDEX PAGE WOOOOOO");
	echo '<a href="overview.php"><img src="/Images/title/playnew.png" width="200" /></a>';
	echo '<a href="strife.php"><img src="/Images/title/strife.png" width="200" /></a>';
	$sql = "SELECT *  FROM `Players`
WHERE `session_name` LIKE '$userrow[session_name]'
AND `enemydata` != '';";
	$sessionmates = $mysqli->query($sql);

	echo '<a href="grist.php"><img src="/Images/title/gristly.png" width="200" /></a>';
	echo '<a href="porkhollow.php"><img src="/Images/title/booney.png" width="200" /></a><br/>';

	while ($row = $sessionmates->fetch_array()) {

		if ($row['username'] != $username) {
			echo "$row[username] is strifing right now!</br>";
		} else {
			echo "You are strifing right now!</br>";
		}
	}
	$sessionresult = $mysqli->query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "'");
	$sessionrow = $sessionresult->fetch_array();
	if ($sessionrow['admin'] == $username && $userrow['admin'] == 0) {
		$userrow['admin'] = 1;
		$mysqli->query("UPDATE `Players` SET `admin` = 1 WHERE `Players`.`username` = '$username' LIMIT 1;");
		echo "You were set as the session's head admin, but you were not marked as an admin yourself. We have just attempted to fix this, but if you have gotten this message more than once, Blahdev/Babby Overseer would appreciate it if you reported it to him.</br>";
	}

	//echo '<a href="events.php">Event Log</a></br>'; -- Will work on event log later.
}
require_once "footer.php";
?>