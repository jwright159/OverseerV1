<?php
//This file is mostly vestigial now. It may function as a backup wire.
require_once "header.php";
if (empty($_SESSION['username'])) {
	echo "Log in to wire grist.";
} else {

	require_once "includes/SQLconnect.php";

	$result = $mysqli->query("SELECT * FROM Players where username = '$row[username]'");
	$result2 = $mysqli->query("SELECT * FROM Players where ");
	$targetfound = false;
	$poor = false;
	$username = $_SESSION['username'];

	while ($row = $result->fetch_array()) {
		if ($row[username] == $username) {
			$type = $_POST[grist_type];
			if (intval($_POST[amount]) <= $row[$type]) {
				while ($row2 = $result2->fetch_array()) {
					if ($row2[username] == $_POST[target]) {
						$targetfound = true;
						$modifier = intval($_POST[amount]);
						$mysqli->query("UPDATE `Players` SET `Build_Grist` = $row[$type]-$modifier WHERE `Players`.`username` = '$row[username]' LIMIT 1 ;");
						$quantity = $row[$type] - $modifier;
						$mysqli->query("UPDATE `Players` SET `Build_Grist` = $row2[$type]+$modifier WHERE `Players`.`username` = '$_POST[target]' LIMIT 1 ;");
					}
				}
			} else {
				echo "Transaction failed: You only have $row[$type] $type";
				$poor = true;
			}
		}
	}
	if ($targetfound == true) {
		echo "Transaction successful. You now have $quantity $type";
	} else if ($poor == false) {
		echo "Transaction failed: Target does not exist.";
	}
	$mysqli->close();
}
echo '<br/><a href="/">Home</a>';
?>