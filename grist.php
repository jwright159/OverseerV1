<?php
require_once "header.php";
//require 'log.php';
if (empty($_SESSION['username'])) {
	echo "Log in to view your grist cache.<br/>";
} else {


	$reachgrist = false;

	echo "Gristwire client v0.0.1a.<br/>";

	$compugood = true;
	if (strpos($userrow['storeditems'], "GRISTWIRE") === false) { //player has the gristtorrent CD or an equivalent in their storage
		echo "You'd better ask your server player to deploy the Gristtorrent CD<br/>";
		$compugood = false;
	}
	if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {
		if ($userrow['hascomputer'] < 3) {
			if ($compugood)
				echo "You don't have a hands-free computer equipped, so you can't wire grist during strife.<br/>";
			$compugood = false;
		}
	}
	if ($userrow['indungeon'] != 0 && $userrow['hascomputer'] < 2) {
		if ($compugood)
			echo "You don't have a portable computer in your inventory, so you can't wire grist while away from home.<br/>";
		$compugood = false;
	}
	if ($userrow['hascomputer'] == 0) {
		if ($compugood)
			echo "You need a computer in storage or your inventory to wire grist to other players.<br/>";
		$compugood = false;
	}

	if ($compugood) {
		//--Begin wiring code here--

		if (!empty($_POST['amount']) && intval($_POST['amount']) > 0) { //Player is attempting to wire a positive amount of grist.
			if ($_POST['intarget'] != "")
				$_POST['target'] = $_POST['intarget'];
			if ($_POST['target'] == $username) { //Player is trying to mail themselves grist!
				echo "You can't send grist to yourself!<br/>";
			} elseif (empty($_POST['target'])) {
				echo "You did not specify a recipient player.<br/>";
			} else {
				$wireresult = $mysqli->query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['target'] . "'");
				$targetfound = false;
				$poor = false;
				$type = $_POST['grist_type'];
				if (intval($_POST['amount']) <= $userrow[$type]) {
					while ($wirerow = $wireresult->fetch_array()) {
						if ($wirerow['username'] == $_POST['target']) {
							$targetfound = true;
							$wirename = $wirerow['username'];
							$modifier = intval($_POST['amount']);
							$mysqli->query("UPDATE `Players` SET `$type` = $userrow[$type]-$modifier WHERE `Players`.`username` = '$username' LIMIT 1 ;");
							$quantity = $userrow[$type] - $modifier;
							$mysqli->query("UPDATE `Players` SET `$type` = $wirerow[$type]+$modifier WHERE `Players`.`username` = '$wirerow[username]' LIMIT 1 ;");
							$timestr = produceIST(initTime());
							$event = $timestr . ": Sent $wirerow[username] $modifier $type";
							//logEvent($event,$username);
							$event = $timestr . ": Received $modifier $type from $userrow[username]";
							//logEvent($event,$_POST['target']);
							$giftstring = strval($modifier) . " " . $type;
							$sendresult = $mysqli->query("SELECT * FROM `Messages` WHERE `Messages`.`username` = '" . $_POST['target'] . "' LIMIT 1;");
							$sendrow = $sendresult->fetch_array();
							$check = 1;
							$max_inbox = 50;
							$foundempty = false;
							while ($check <= $max_inbox && !$foundempty) { //make sure there's a free spot in recipient's inbox
								if ($sendrow['msg' . strval($check)] == "")
									$foundempty = true;
								if (!$foundempty)
									$check++;
							}
							if ($foundempty) {
								if (!empty($_POST['body'])) {
									$bodystring = $_POST['body'];
								} else {
									$bodystring = '<a href="grist.php">' . $username . ' has wired you ' . $giftstring . '.</a>';
								}
								$sendstring = "Gristwire|" . $username . " has wired you " . $giftstring . "!|" . $bodystring;
								$sendstring = str_replace("'", "''", $sendstring); //god dang these apostrophes
								$mysqli->query("UPDATE `Messages` SET `msg" . strval($check) . "` = '" . $sendstring . "' WHERE `username` = '" . $sendrow['username'] . "' LIMIT 1;");
								$mysqli->query("UPDATE `Players` SET `Players`.`newmessage` = `newmessage` + 1 WHERE `Players`.`username` = '" . $sendrow['username'] . "' LIMIT 1;");
							} else
								echo "Attempted to send a message, but the user's inbox was full.<br/>";
						}
					}
				} else {
					echo "Transaction failed: You only have $userrow[$type] $type";
					$quantity = $userrow[$type];
					$poor = true;
				}
				if ($targetfound) {
					echo "Transaction successful. You now have $quantity $type after sending $modifier $type to $wirename";
				} elseif (!$poor) {
					echo "Transaction failed: Target $_POST[target] does not exist.";
				}
				echo "<br/>";
			}
		}
		if (empty($type))
			$type = "";

		//--End wiring code here.--

		echo '<form action="grist.php" method="post" id="wire">Target username (sessionmates): <select name="intarget"><option value=""></option>';
		$yoursessionresult = $mysqli->query("SELECT `username` FROM `Players` WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "'");
		while ($ysessionrow = $yoursessionresult->fetch_array()) {
			if ($ysessionrow['username'] != $username)
				echo '<option value="' . $ysessionrow['username'] . '">' . $ysessionrow['username'] . '</option>';
		}
		echo '</select><br/>Target username (other): <input id="target" name="target" type="text" /><br/> Type of grist: <select name="grist_type"> ';
		$result2 = $mysqli->query("SELECT * FROM `Players` LIMIT 1;");
		$reachgrist = false;
		while ($col = $result2->fetch_field()) {
			$gristtype = $col->name;
			if ($gristtype == "Build_Grist") { //Reached the start of the grists.
				$reachgrist = true;
			}
			if ($gristtype == "End_of_Grists") { //Reached the end of the grists.
				$reachgrist = false;
				break;
			}
			if ($reachgrist) {
				echo '<option value="' . $gristtype . '">' . $gristtype . '</option>'; //Produce an option in the dropdown menu for this grist.
			}
		}
		$reachgrist = false; //Paranoia: Reset this just in case
		echo '</select><br/>Amount to transfer: <input id="amount" name="amount" type="text" /><br/>Attach a message (optional):<br/><textarea name="body" rows="6" cols="40" form="wire"></textarea><br/><input type="submit" value="Wire it!" /> </form>';
	}

	$result2 = $mysqli->query("SELECT * FROM Players LIMIT 1 ;");
	echo "<div class='grister'>";
	$rowcount = 1;
	while ($col = $result2->fetch_field()) {
		$gristtype = $col->name;
		if ($gristtype == "Build_Grist") { //Reached the start of the grists.
			$reachgrist = true;
		}
		if ($gristtype == "End_of_Grists") { //Reached the end of the grists.
			$reachgrist = false;
			break;
		}
		if ($reachgrist && $userrow[$gristtype] != 0) { //Print grist, grist image, and grist total.
			echo "<div class='grist $gristtype'>";
			if ($gristtype == "Opal" || $gristtype == "Polychromite" || $gristtype == "Rainbow") { //Special cases for animated grists.
				echo '<center><img src="Images/Grist/' . $gristtype . '.gif " height="50" width="50"></img></center>' . $gristtype . ' - ';
				if (!empty($type) && $gristtype == $type) { //This is the grist we wired from this time. Horrible fix gogogo!
					echo number_format($quantity) . "<br/>";
				} else {
					echo "$userrow[$gristtype]<br/>";
				}
			} else {
				echo '<center><img src="Images/Grist/' . $gristtype . '.png " height="50" width="50"></img></center>' . $gristtype . ' - ';
				if (!empty($type) && $gristtype == $type) { //This is the grist we wired from this time.
					echo number_format($quantity) . "<br/>";
				} else {
					echo "$userrow[$gristtype]<br/>";
				}
			}
			echo "</div>";
		}
	}
	echo "</div>";
	$mysqli->close();
}
require_once "footer.php";
