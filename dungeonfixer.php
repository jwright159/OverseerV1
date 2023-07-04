<?php
require_once("header.php");

if ($userrow['session_name'] != "Developers") {
	echo "denied.";
} else {
	echo "2.2 update dungeon fixing script start!<br />";
	$dfixresult = $mysqli->query("SELECT * FROM `Players` WHERE `Players`.`indungeon` = 1 AND `Players`.`currentdungeon` = ''");
	while ($row = $dfixresult->fetch_array()) {
		echo "Fixing player " . $row['username'] . "...";
		$dresult = $mysqli->query("SELECT * FROM `Dungeons` WHERE `Dungeons`.`username` = '" . $row['username'] . "'");
		$drow = $dresult->fetch_array();
		$mysqli->query("UPDATE `Players` SET `currentdungeon` = '" . $row['username'] . "', `dungeonrow` = " . strval($drow['dungeonrow']) . ", `dungeoncol` = " . strval($drow['dungeoncol']) . ", `olddungeonrow` = " . strval($drow['olddungeonrow']) . ", `olddungeoncol` = " . strval($drow['olddungeoncol']) . " WHERE `Players`.`username` = '" . $row['username'] . "'");
		//echo "UPDATE `Players` SET `currentdungeon` = '" . $row['username'] . "', `dungeonrow` = " . strval($drow['dungeonrow']) . ", `dungeoncol` = " . strval($drow['dungeoncol']) . ", `olddungeonrow` = " . strval($drow['olddungeonrow']) . ", `olddungeoncol` = " . strval($drow['olddungeoncol']) . " WHERE `Players`.`username` = '" . $row['username'] . "'";
		echo "Done!<br />";
	}
	echo "That's everyone!<br />";
}

require_once("footer.php");
?>