<?php
require_once "header.php";

if ($userrow['session_name'] != "Developers") {
	echo "denied.";
} else {
	if (!empty($_POST['admn'])) {
		$sresult = $mysqli->query("SELECT * FROM `Sessions` WHERE `Sessions`.`name` = '" . $_POST['sesn'] . "' LIMIT 1;");
		$srow = $sresult->fetch_array();
		if ($srow['name'] == $_POST['sesn']) {
			$presult = $mysqli->query("SELECT * FROM `Players` WHERE `Players`.`username` = '" . $_POST['admn'] . "' LIMIT 1;");
			$prow = $presult->fetch_array();
			if ($prow['username'] == $_POST['admn']) {
				if ($prow['session_name'] == $srow['name']) {
					$mysqli->query("UPDATE `Players` SET `admin` = 1 WHERE `Players`.`username` = '" . $prow['username'] . "' LIMIT 1;");
					if (!empty($_POST['head'])) {
						$mysqli->query("UPDATE `Sessions` SET `admin` = '" . $prow['username'] . "' WHERE `Sessions`.`name` = '" . $srow['name'] . "' LIMIT 1;");
					}
					echo "Done! " . $prow['username'] . " is now admin of session " . $srow['name'] . "<br/>";
				} else
					echo "ERROR: That player is not in that session<br/>";
			} else
				echo "ERROR: Player " . $_POST['admn'] . " not found<br/>";
		} else
			echo "ERROR: Session " . $_POST['sesn'] . " not found<br/>";
	}

	echo "Admin re-assigner v. Lazy Blahsadfeguie.<br/>";
	echo '<form action="adminassign.php" method="post">Session: <input type="text" name="sesn" /><br/>';
	echo 'New admin: <input type="text" name="admn" /><br/>';
	echo '<input type="checkbox" name="head" value="yes" /> Replace existing head admin<br/>';
	echo '<input type="submit" value="Kaboom!" /></form>';
}

?>