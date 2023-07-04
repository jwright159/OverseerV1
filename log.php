<?php
function logEvent($event, $username)
{
	require_once "includes/SQLconnect.php";

	$result = $mysqli->query("SELECT * FROM Logs");
	$logslots = $mysqli->num_fields($result);
	$logslots--; //Remove the username field from consideration.
	$i = 1;
	while ($row = $result->fetch_array()) {
		if ($row['username'] == $username) {
			$finalrow = $row;
		}
	}
	while ($i < $logslots) {
		$logindex = $logslots - $i;
		$logstr = "log" . strval($logindex);
		$replacestr = "log" . strval($logindex + 1);
		if (!empty($finalrow[$logstr])) {
			$log = $finalrow[$logstr];
			$mysqli->query("UPDATE `Logs` SET `" . $replacestr . "` = '" . $log . "' WHERE `Logs`.`username` = '" . $username . "' LIMIT 1 ;");
		}
		$i++;
	}
	$mysqli->query("UPDATE `Logs` SET `log1` = '" . $event . "' WHERE `Logs`.`username` = '" . $username . "' LIMIT 1 ;");
}
?>