<?php
require_once "header.php";
if (empty($_SESSION['username'])) {
	echo "Log in to access this developer tool.<br/>";
} else {
	require_once "includes/SQLconnect.php";
	$allowall = true;
	if ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods" && !$allowall) {
		echo "Public rewards are now closed.";
	} else {
		if (!empty($_POST['gift'])) {
			if ($userrow['modlevel'] < 10) {
				$_POST['user'] = $username; //get rid of this when public rewards are removed
			}
			$_POST['user'] = $mysqli->real_escape_string($_POST['user']);
			$_POST['gift'] = $mysqli->real_escape_string($_POST['gift']);
			$_POST['quantity'] = intval($mysqli->real_escape_string($_POST['quantity']));
			$_POST['captcha'] = $mysqli->real_escape_string($_POST['captcha']);
			if ($_POST['gift'] == "lookup") {
				$targetresult = $mysqli->query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
				$targetrow = $targetresult->fetch_array();
				if ($targetrow['username'] == $_POST['user']) {
					echo 'Username: ' . $targetrow['username'] . '<br/>';
					echo 'Session: ' . $targetrow['session_name'] . '<br/>';
					echo 'Echeladder: ' . $targetrow['Echeladder'] . '<br/>';
					echo 'Equipped Weapon(s): ' . $targetrow[$targetrow['equipped']];
					if ($targetrow['offhand'] != "2HAND" && $targetrow['offhand'] != "")
						echo ', ' . $targetrow[$targetrow['offhand']];
					echo '<br/>';
					echo 'Solo Fraymotif I: ' . $targetrow['solo1'] . '<br/>';
					echo 'Solo Fraymotif II: ' . $targetrow['solo2'] . '<br/>';
					echo 'Solo Fraymotif III: ' . $targetrow['solo3'] . '<br/>';
					echo 'Boondollars: ' . $targetrow['Boondollars'] . '<br/>';
					echo 'Encounters: ' . $targetrow['encounters'] . '<br/>';
					$reachgrist = false;
					$result2 = $mysqli->query("SELECT * FROM Players LIMIT 1;");
					while ($col = $result2->fetch_field()) {
						$gristtype = $col->name;
						if ($gristtype == "Build_Grist") { //Reached the start of the grists.
							$reachgrist = true;
						}
						if ($gristtype == "End_of_Grists") { //Reached the end of the grists.
							$reachgrist = false;
						}
						if ($reachgrist == true) {
							echo $gristtype . ': ' . $targetrow[$gristtype] . '<br/>';
						}
					}
				} else
					echo 'No player by the username of ' . $_POST['user'] . ' found.<br/>';
			} else {
				$targetresult = $mysqli->query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
				$targetrow = $targetresult->fetch_array();
				$quantity = intval($_POST['quantity']);
				if (is_int($quantity) == false || $quantity < 0) {
					echo "Invalid quantity! Defaulting to 1.";
					$quantity = 1;
				}
				if ($targetrow['username'] == $_POST['user']) {
					if ($_POST['gift'] == "item") {
						$itemresult = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['captcha'] . "' LIMIT 1;");
						$itemrow = $itemresult->fetch_array();
						if ($itemrow['captchalogue_code'] == $_POST['captcha']) {
							$k = 1;
							$itemsgiven = 0;
							while ($k <= 50 && $itemsgiven < $quantity) {
								$foundblank = false;
								while ($foundblank == false) {
									if ($targetrow['inv' . $k] == "") {
										$foundblank = true;
									} else {
										$k++;
										if ($k > 50) {
											$foundblank = true;
										}
									}
								}
								if ($k <= 50) {
									$mysqli->query("UPDATE Players SET `inv" . strval($k) . "` = '" . $itemrow['name'] . "' WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
									$itemsgiven++;
									$k++;
								}
							}
							echo $_POST['user'] . " was given " . $itemrow['name'] . " x" . $itemsgiven . "!<br/>";
							$giftstring = $itemrow['name'] . " x" . $itemsgiven;
						} else
							echo "That captcha code doesn't appear to belong to any item.<br/>";
					} else {
						$amount = $quantity;
						$field = $_POST['gift'];
						if ($field == "encounters" && $amount + $targetrow['encounters'] > 100)
							$amount = 100 - $targetrow['encounters'];
						if ($field == "Echeladder" && $amount + $targetrow['Echeladder'] > 612)
							$amount = 612 - $targetrow['Echeladder'];
						if ($field == "Echeladder" && $amount + $targetrow['Echeladder'] < 1)
							$amount = 1 + $targetrow['Echeladder'];
						if ($field == "allgrists") {
							$reachgrist = false;
							$result2 = $mysqli->query("SELECT * FROM Players LIMIT 1;");
							while ($col = $result2->fetch_field()) {
								$gristtype = $col->name;
								if ($gristtype == "Build_Grist") { //Reached the start of the grists.
									$reachgrist = true;
								}
								if ($gristtype == "End_of_Grists") { //Reached the end of the grists.
									$reachgrist = false;
								}
								if ($reachgrist == true) {
									$mysqli->query("UPDATE Players SET `$gristtype` = " . strval($targetrow[$gristtype] + $amount) . " WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
								}
							}
							echo $_POST['user'] . " was given " . strval($amount) . " of all grist types!<br/>";
							$giftstring = strval($amount) . " of all grist types";
						} else {
							$mysqli->query("UPDATE Players SET `$field` = " . strval($targetrow[$field] + $amount) . " WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
							if ($field == "Echeladder") {
								if ($targetrow['Echeladder'] == 1) {
									$mysqli->query("UPDATE Players SET `Gel_Viscosity` = 10 WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
								} elseif ($targetrow['Echeladder'] == 2) {
									$mysqli->query("UPDATE Players SET `Gel_Viscosity` = 15 WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
								} elseif ($targetrow['Echeladder'] == 3 || $targetrow['Echeladder'] == 4 || $targetrow['Echeladder'] == 5) {
									$mysqli->query("UPDATE Players SET `Gel_Viscosity` = " . strval($targetrow['Echeladder'] * 10 - 5) . " WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
								} else {
									$mysqli->query("UPDATE Players SET `Gel_Viscosity` = " . strval($targetrow['Echeladder'] * 15 - 25) . " WHERE `Players`.`username` = '" . $_POST['user'] . "' LIMIT 1;");
								}
								echo $_POST['user'] . " was given " . strval($amount) . " Echeladder rungs!<br/>";
								$giftstring = strval($amount) . " Echeladder rungs";
							} else {
								echo $_POST['user'] . " was given " . strval($amount) . " " . $field . "!<br/>";
								$giftstring = strval($amount) . " " . $field;
							}
						}
					}
					if (!empty($_POST['body'])) {
						$sendresult = $mysqli->query("SELECT * FROM `Messages` WHERE `Messages`.`username` = '" . $sendto[$rcount] . "' LIMIT 1;");
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
							$sendstring = "<i>" . $username . "</i>" . "|The devs have gifted you " . $giftstring . "!|" . $_POST['body'];
							$sendstring = str_replace("'", "''", $sendstring); //god dang these apostrophes
							$mysqli->query("UPDATE `Messages` SET `msg" . strval($check) . "` = '" . $sendstring . "' WHERE `username` = '" . $sendrow['username'] . "' LIMIT 1;");
							$mysqli->query("UPDATE `Players` SET `Players`.`newmessage` = 1 WHERE `Players`.`username` = '" . $sendrow['username'] . "' LIMIT 1;");
						} else
							echo "Attempted to send a message, but the user's inbox was full.<br/>";
					}
				} else
					echo 'No player by the username of ' . $_POST['user'] . ' found.<br/>';
			}
		}
		echo '<form action="rewards.php" method="post" id="reward">';
		if ($userrow['modlevel'] >= 10) {
			echo 'Name of recipient: <input id="user" name="user" type="text" /><br/>';
		}
		echo 'What to gift: <select name="gift">';
		echo '<option value="lookup">No reward, just look up info</option>';
		echo '<option value="Boondollars">Boondollars</option>';
		echo '<option value="encounters">Encounters</option>';
		echo '<option value="Echeladder">Echeladder rungs</option>';
		echo '<option value="abstrati">Strife abstrati</option>';
		$reachgrist = false;
		$result2 = $mysqli->query("SELECT * FROM Players LIMIT 1;");
		while ($col = $result2->fetch_field()) {
			$gristtype = $col->name;
			if ($gristtype == "Build_Grist") { //Reached the start of the grists.
				$reachgrist = true;
			}
			if ($gristtype == "End_of_Grists") { //Reached the end of the grists.
				$reachgrist = false;
			}
			if ($reachgrist == true) {
				echo '<option value="' . $gristtype . '">' . $gristtype . '</option>'; //Produce an option in the dropdown menu for this grist.
			}
		}
		echo '<option value="allgrists">All grists</option>';
		echo '<option value="item">Item</option>';
		echo '</select><br/>';
		echo '<form action="rewards.php" method="post">Quantity of reward: <input id="quantity" name="quantity" type="text" /><br/>';
		echo '<form action="rewards.php" method="post">Captcha code of item (leave blank if other reward type): <input id="captcha" name="captcha" type="text" /><br/>';
		echo 'Attach a message (optional):<br/><textarea name="body" rows="6" cols="40" form="reward"></textarea><br/>';
		echo '<input type="submit" value="Give reward!" /></form>';
	}
}
require_once "footer.php";
?>