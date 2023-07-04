<?php
require_once "includes/SQLconnect.php";
echo "let's see if there are deungeons</br>";
$dungeonresult = $mysqli->query("SELECT * FROM `Dungeons`");
while ($drow = $dungeonresult->fetch_array()) {
	echo $drow['username'] . "</br>";
}
?>