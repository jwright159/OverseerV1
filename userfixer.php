<?php
require_once "header.php";
require_once "includes/fieldparser.php";

if (empty($_SESSION['username']) || $userrow['session_name'] != "Developers") {
	echo "go away plz";
} else {
	$allresult = $mysqli->query("SELECT * FROM `Players`");
	while ($row = $allresult->fetch_array()) {
		echo "Updating " . $row['username'] . "<br/>";
		$i = 1;
		$newstr = "";
		while ($i <= 16) {
			$astr = 'abstratus' . strval($i);
			if (!empty($row[$astr])) {
				$newstr .= $row[$astr];
				$newstr .= "|";
			}
			$i++;
		}
		$mysqli->query("UPDATE `Players` SET `abstratus1` = '$newstr' WHERE `Players`.`username` = '" . $row['username'] . "' LIMIT 1;");
		//writeLastfought($row);
		//writeEnemydata($row);
	}
	echo "Done! whew.";
}
?>