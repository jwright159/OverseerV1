<?php
//require 'log.php';
require_once "header.php";

if (empty($_SESSION['username'])) {
	echo "Log in to view your virtual porkhollow.<br/>";
} else {

	$boon = "Boondollars";
	echo "Virtual Porkhollow v0.0.1a.<br/>";

	$compugood = true;
	if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {
		if ($userrow['hascomputer'] < 3) {
			if ($compugood == true)
				echo "You don't have a hands-free computer equipped, so you can't wire boondollars during strife.<br/>";
			$compugood = false;
		}
	}
	if ($userrow['indungeon'] != 0 && $userrow['hascomputer'] < 2) {
		if ($compugood == true)
			echo "You don't have a portable computer in your inventory, so you can't wire boondollars while away from home.<br/>";
		$compugood = false;
	}
	if ($userrow['hascomputer'] == 0) {
		if ($compugood == true)
			echo "You need a computer in storage or your inventory to wire boondollars to other players.<br/>";
		$compugood = false;
	}

	if ($compugood) {
		//--Begin wiring code here--

		if (!empty($_POST['amount']) && $_POST['amount'] > 0) { //We have a positive amount of Boondollars to transfer.
			if ($_POST['intarget'] != "")
				$_POST['target'] = $_POST['intarget'];
			if ($_POST['target'] == $username) { //Player is trying to wire themselves boondollars!
				echo "You can't wire boondollars to yourself!<br/>";
			} elseif (empty($_POST['target'])) {
				echo "You didn't specify a recipient!<br/>";
			} else {
				$wireresult2 = $mysqli->query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['target'] . "'");
				$targetfound = false;
				$poor = false;
				if (intval($_POST['amount']) <= $userrow['Boondollars']) {
					while ($wirerow2 = $wireresult2->fetch_array()) {
						if ($wirerow2['username'] == $_POST['target']) {
							$targetfound = true;
							$modifier = intval($_POST['amount']);
							$mysqli->query("UPDATE `Players` SET `Boondollars` = $userrow[$boon]-$modifier WHERE `Players`.`username` = '$username' LIMIT 1 ;");
							$quantity = $userrow[$boon] - $modifier;
							$mysqli->query("UPDATE `Players` SET `Boondollars` = $wirerow2[$boon]+$modifier WHERE `Players`.`username` = '$_POST[target]' LIMIT 1 ;");
							//$timestr = produceIST(initTime($con));
							//$event = $timestr . ": Sent $wirerow2[username] $modifier boondollars";
							//logEvent($event,$username);
							//$event = $timestr . ": Received $modifier boondollars from $wirerow[username]";
							//logEvent($event,$_POST['target']);
							$giftstring = strval($modifier) . " Boondollars";
							$sendresult = $mysqli->query("SELECT * FROM `Messages` WHERE `Messages`.`username` = '" . $_POST['target'] . "' LIMIT 1;");
							$sendrow = $sendresult->fetch_array();
							$check = 1;
							$max_inbox = 50;
							$foundempty = false;
							while ($check <= $max_inbox && $foundempty == false) { //make sure there's a free spot in recipient's inbox
								if ($sendrow['msg' . strval($check)] == "")
									$foundempty = true;
								if ($foundempty == false)
									$check++;
							}
							if ($foundempty) {
								if (!empty($_POST['body'])) {
									$bodystring = $_POST['body'];
								} else {
									$bodystring = '<a href="porkhollow.php">' . $username . ' has wired you ' . $giftstring . '.</a>';
								}
								$sendstring = "Porkhollow|" . $username . " has wired you " . $giftstring . "!|" . $bodystring;
								$sendstring = str_replace("'", "''", $sendstring); //god dang these apostrophes
								$mysqli->query("UPDATE `Messages` SET `msg" . strval($check) . "` = '" . $sendstring . "' WHERE `username` = '" . $sendrow['username'] . "' LIMIT 1;");
								$mysqli->query("UPDATE `Players` SET `Players`.`newmessage` = `newmessage` + 1 WHERE `Players`.`username` = '" . $sendrow['username'] . "' LIMIT 1;");
							} else
								echo "Attempted to send a message, but the user's inbox was full.<br/>";
						}
					}
				} else {
					echo "Transaction failed: You only have $userrow[$boon] boondollars";
					$poor = true;
				}
				if ($targetfound == true) {
					echo "Transaction successful. Boondollars: $quantity";
				} else if ($poor == false) {
					echo "Transaction failed: Target does not exist.<br/>Boondollars: $userrow[$boon]";
				}
				echo "<br/>";
			}
		} else {
			echo "Boondollars: $userrow[$boon]";
		}

		//--End wiring code here. Consider making this bit a function.--

		echo '<form action="porkhollow.php" method="post" id="wire">Target username (sessionmates): <select name="intarget"><option value=""></option>';
		$yoursessionresult = $mysqli->query("SELECT `username` FROM `Players` WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "'");
		while ($ysessionrow = $yoursessionresult->fetch_array()) {
			if ($ysessionrow['username'] != $username)
				echo '<option value="' . $ysessionrow['username'] . '">' . $ysessionrow['username'] . '</option>';
		}
		echo '</select><br/>Target username (other): <input id="target" name="target" type="text" /><br/>';
		echo 'Amount of boondollars to transfer: <input id="amount" name="amount" type="text" /><br/>Attach a message (optional):<br/><textarea name="body" rows="6" cols="40" form="wire"></textarea><br/><input type="submit" value="Wire it!" /></form>';
	} else
		echo "Boondollars: " . strval($userrow[$boon]);
	$mysqli->close();
}
require_once "footer.php";
