<?php
require_once 'additem.php';
require_once 'includes/effectprinter.php'; //for printing effects, consolidated into an include for simplicity (also includes glitches)
require_once "header.php";
require_once "includes/fieldparser.php";
$max_items = 50;

if (empty($_SESSION['username'])) {
	echo "Log in to view and manipulate your strife portfolio and options.<br/>";
} elseif ($userrow['dreamingstatus'] != "Awake") {

	$classresult = $mysqli->query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$userrow[Class]';");
	$classrow = $classresult->fetch_array();
	$unarmedpower = floor($userrow['Echeladder'] * (pow(($classrow['godtierfactor'] / 100), $userrow['Godtier'])));
	$factor = ((612 - $userrow['Echeladder']) / 611);
	$unarmedpower = ceil($unarmedpower * ((($classrow['level1factor'] / 100) * $factor) + (($classrow['level612factor'] / 100) * (1 - $factor)))); //Finish calculating unarmed power.
	//This will register which abilities the player has in $abilities. The standard check is if (!empty($abilities[ID of ability to be checked for>]))
	$abilityresult = $mysqli->query("SELECT `ID`, `Usagestr` FROM `Abilities` WHERE `Abilities`.`Aspect` IN ('$userrow[Aspect]','All') AND `Abilities`.`Class` IN ('$userrow[Class]','All') 
AND `Abilities`.`Rungreq` BETWEEN 0 AND $userrow[Echeladder] AND `Abilities`.`Godtierreq` BETWEEN 0 AND $userrow[Godtier] ORDER BY `Abilities`.`Rungreq` DESC;");
	$abilities = array(0 => "Null ability. No, not void.");
	while ($temp = $abilityresult->fetch_array()) {
		$abilities[$temp['ID']] = $temp['Usagestr']; //Create entry in abilities array for the ability the player has. We save the usage message in, so pulling the usage message is as simple
		//as pulling the correct element out of the abilities array via the ID. Note that an ability with an empty usage message will be unusable since the empty function will spit empty at you.
	}
	echo "As your dream self, you have only yourself with which to strife.<br/>";

	//Begin Echerung naming code here.

	if (!empty($_POST['echename'])) {
		$newrung = $mysqli->real_escape_string($_POST['echename']);
		$rungstr = "rung" . strval($userrow['Echeladder']);
		//$mysqli->query("UPDATE `Players` SET `Echeladder_Rung` = '" . $newrung . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //Used to be for updating Echeladder rung. Now outdated.
		$mysqli->query("UPDATE `Echeladders` SET `" . $rungstr . "` = '" . $newrung . "' WHERE `Echeladders`.`username` = '$username' LIMIT 1 ;");
	}

	//End Echerung naming code here.

	$echeresult = $mysqli->query("SELECT * FROM Echeladders WHERE `Echeladders`.`username` = '" . $username . "'");
	$echerow = $echeresult->fetch_array();
	echo "Current Echeladder height: $userrow[Echeladder]";
	if (!empty($newrung)) {
		echo "<br/>Current Echeladder rung: $newrung <br/>";
	} else {
		$echestr = "rung" . strval($userrow['Echeladder']);
		if ($echerow[$echestr] != "") {
			echo "<br/>Current Echeladder rung: $echerow[$echestr]<br/>";
		} else {
			echo '<form action="portfolio.php" method="post">';
			echo 'Current Echeladder rung: <input id="echename" name="echename" type="text" /><input type="submit" value="Name it!" /> </form>';
		}
	}
	if ($userrow['powerboost'] != 0)
		echo "Current temporary power modifier: $userrow[powerboost]<br/>";
	if ($userrow['offenseboost'] != 0)
		echo "Current temporary offense modifier: $userrow[offenseboost]<br/>";
	if ($userrow['defenseboost'] != 0)
		echo "Current temporary defense modifier: $userrow[defenseboost]<br/>";
	$powerlevel = $unarmedpower + $userrow['powerboost'];
	echo "Current power level: $powerlevel <br/>";
	if ($powerlevel == 9001)
		echo "(Yes, yes, very funny.)<br/>";
	echo "Health Vial: ";
	echo strval(floor(($userrow['Dream_Health_Vial'] / $userrow['Gel_Viscosity']) * 100)); //Computes % of max HP remaining.
	echo "%<br/>";
	echo "Aspect vial: " . strval(floor(($userrow['Aspect_Vial'] / $userrow['Gel_Viscosity']) * 100)) . "%<br/>";
} else {
	require_once "includes/SQLconnect.php";

	//if assigning abstratus, do so first so that equip selection can reflect change
	if (!empty($_POST['new_abstratus'])) {
		if ($_POST['new_abstratus'] == "notaweapon" || $_POST['new_abstratus'] == "headgear" || $_POST['new_abstratus'] == "facegear" || $_POST['new_abstratus'] == "headgear" || $_POST['new_abstratus'] == "bodygear" || $_POST['new_abstratus'] == "accessory" || $_POST['new_abstratus'] == "computer") { //somehow, the player tried to assign a non-abstratus
			echo "ahahaha how HIGH do you even have to BE just to DO something like that..........<br/>";
		} else {
			if (freeSpecibi($userrow['abstratus1'], $userrow['abstrati'], false) > 0) {
				$userrow = addSpecibus($userrow, $_POST['new_abstratus']);
				echo "A kind abstratus has been instantiated to " . $_POST['new_abstratus'] . "<br/>";
			} else
				echo "You have no abstrati remaining to assign!<br/>";
		}
	}

	//next, check which items can be equipped
	$i = 1;
	while ($i <= $max_items) {
		$invstr = 'inv' . strval($i);
		$itemenabledm[$i] = false;
		$itemenabledo[$i] = false;
		$itemname = str_replace("'", "\\\\''", $userrow[$invstr]);
		$itemresult = $mysqli->query("SELECT `name`,`abstratus`,`size` FROM `Captchalogue` WHERE `Captchalogue`.`name` = '$itemname' AND `Captchalogue`.`abstratus` NOT LIKE '%notaweapon%'"); //shouldn't return anything if notaweapon
		while ($row = $itemresult->fetch_array()) {
			if (matchesAbstratus($userrow['abstratus1'], $row['abstratus'])) { //User has existing matching abstratus
				$sizevalue = itemSize($row['size']);
				if ($sizevalue <= itemSize("average")) {
					$itemenabledo[$i] = true;
				}
				if ($sizevalue <= itemSize("large")) {
					$itemenabledm[$i] = true;
				}
			}
		}
		$i++;
	}

	$abilities = array(0 => "Null ability. No, not void.");
	if (!empty($userrow['Class']))
	{
		$classresult = $mysqli->query("SELECT * FROM Class_modifiers WHERE Class = '$userrow[Class]';");
		$classrow = $classresult->fetch_array();
		$unarmedpower = floor($userrow['Echeladder'] * (pow(($classrow['godtierfactor'] / 100), $userrow['Godtier'])));
		$factor = ((612 - $userrow['Echeladder']) / 611);
		$unarmedpower = ceil($unarmedpower * ((($classrow['level1factor'] / 100) * $factor) + (($classrow['level612factor'] / 100) * (1 - $factor)))); //Finish calculating unarmed power.
		//This will register which abilities the player has in $abilities. The standard check is if (!empty($abilities[ID of ability to be checked for>]))
		$abilityresult = $mysqli->query("SELECT ID, Usagestr FROM Abilities WHERE Aspect IN ('$userrow[Aspect]','All') AND Class IN ('$userrow[Class]','All') AND Rungreq BETWEEN 0 AND $userrow[Echeladder] AND Godtierreq BETWEEN 0 AND $userrow[Godtier] ORDER BY Rungreq DESC;");
		while ($temp = $abilityresult->fetch_array()) {
			$abilities[$temp['ID']] = $temp['Usagestr']; //Create entry in abilities array for the ability the player has. We save the usage message in, so pulling the usage message is as simple
			//as pulling the correct element out of the abilities array via the ID. Note that an ability with an empty usage message will be unusable since the empty function will spit empty at you.
		}
	}

	//--Begin equipping code here.--

	if (!empty($_POST['equipmain'])) { //User is equipping an item to their main hand.
		$inum = intval(str_replace("inv", "", $_POST['equipmain']));
		if ($_POST['equipmain'] == "Remove Equipment") {
			$equippedmain = $_POST['equipmain']; //For use later.
			echo "You remove your main weapon.<br/>"; //NOTE - Unauthorized equipping prevented by menu options not being there.
			autoUnequip($userrow, "none", $userrow['equipped']); //will also remove any granted effects, if any exist
			$mysqli->query("UPDATE `Players` SET `equipped` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
			unset($_SESSION['mainrow']);
		} elseif ($itemenabledm[$inum]) {
			$equipname = str_replace("'", "\\\\''", $userrow[$_POST['equipmain']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$itemresult = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $equipname . "'");
			while ($itemrow = $itemresult->fetch_array()) {
				$itemname = $itemrow['name'];
				$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
				if ($itemname == $userrow[$_POST['equipmain']]) {
					if (itemSize($itemrow['size']) < itemSize("huge")) { //No putting two-handed weapons in the offhand.
						$equippedmain = $_POST['equipmain']; //For use later.
						echo "You equip your $itemname as your main weapon.<br/>"; //NOTE - Unauthorized equipping prevented by menu options not being there.
						$_SESSION['mainrow'] = $itemrow;
						$mysqli->query("UPDATE `Players` SET `equipped` = '" . $_POST['equipmain'] . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
						autoUnequip($userrow, "equipped", $equippedmain);
						$userrow['equipped'] = $_POST['equipmain'];
						compuRefresh($userrow);
						grantEffects($userrow, $itemrow['effects'], "equipped");
						if ($itemrow['size'] == "large") { //Item is two-handed. Note that weapons bigger than "large" are classified as "notaweapon"
							$mysqli->query("UPDATE `Players` SET `offhand` = '2HAND' WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //Current weapon is two-handed.
							$equippedoff = "2HAND";
						}
						if ($userrow['offhand'] == $equippedmain) { //Offhand weapon transferred to main hand.
							$mysqli->query("UPDATE `Players` SET `offhand` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //Move offhand weapon.
						}
					} else
						echo "That weapon is too big to be wielded!<br/>";
				}
			}
		} else
			echo "You cannot equip that.<br/>";
	}
	if (!empty($_POST['equipoff'])) { //User is equipping an item to their offhand.
		$inum = intval(str_replace("inv", "", $_POST['equipoff']));
		if ($_POST['equipoff'] == "Remove Equipment") {
			$equippedoff = $_POST['equipoff']; //For use later.
			echo "You remove your offhand weapon.<br/>"; //NOTE - Unauthorized equipping prevented by menu options not being there.
			autoUnequip($userrow, "none", $userrow['offhand']); //will also remove any granted effects, if any exist
			$mysqli->query("UPDATE `Players` SET `offhand` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
			unset($_SESSION['offrow']);
		} elseif ($itemenabledo[$inum]) {
			$offname = str_replace("'", "\\\\''", $userrow[$_POST['equipoff']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$itemresult = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $offname . "'");
			while ($itemrow = $itemresult->fetch_array()) {
				$itemname = $itemrow['name'];
				$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
				if ($itemname == $userrow[$_POST['equipoff']]) {
					if (itemSize($itemrow['size']) < itemSize("large")) { //No putting two-handed weapons in the offhand.
						$equippedoff = $_POST['equipoff']; //For use later.
						echo "You equip your $itemname as your offhand weapon.<br/>";
						$_SESSION['offrow'] = $itemrow;
						if ($userrow['offhand'] == "2HAND") {
							$userrow['equipped'] = ""; //Remove two-handed weapon if we equip to the offhand.
							$mysqli->query("UPDATE `Players` SET `equipped` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
						}
						$mysqli->query("UPDATE `Players` SET `offhand` = '" . $_POST['equipoff'] . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
						autoUnequip($userrow, "offhand", $equippedoff);
						$userrow['offhand'] = $_POST['equipoff'];
						compuRefresh($userrow);
						grantEffects($userrow, $itemrow['effects'], "offhand");
					} else {
						echo "That weapon is too big to be wielded in your offhand!<br/>";
					}
				}
			}
		} else
			echo "You cannot equip that.<br/>";
	}

	//--End equipping code here.--
	//NOTE - Equipping of unauthorized items is impossible due to them not appearing as options OH WAIT NOPE LOL. Begin echeladder naming code here.

	if (!empty($_POST['echename'])) {
		$newrung = $mysqli->real_escape_string($_POST['echename']);
		$rungstr = "rung" . strval($userrow['Echeladder']);
		//$mysqli->query("UPDATE `Players` SET `Echeladder_Rung` = '" . $newrung . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //Used to be for updating Echeladder rung. Now outdated.
		$mysqli->query("UPDATE `Echeladders` SET `" . $rungstr . "` = '" . $newrung . "' WHERE `Echeladders`.`username` = '$username' LIMIT 1 ;");
	}

	//--End echeladder naming code here. (New abstratus code was moved to the top because reasons)

	if (empty($equippedmain))
		$equippedmain = "";
	if (empty($equippedoff))
		$equippedoff = "";
	echo "Strife Portfolio Manager v0.0.1a. Please select a captchalogued weapon.<br/>";
	echo "Abstrati available:<br/>";
	$free = freeSpecibi($userrow['abstratus1'], $userrow['abstrati'], true);
	if (!empty($abilities[15]))
		echo "fistkind<br/>"; //ID 15 "One with Nothing" is possessed by the player. Give them the fistkind abstratus!
	if (!empty($newabstratus)) {
		echo "$newabstratus <br/>";
		if ($free > 0) {
			$free--; //The new abstratus wasn't counted.
		}
	} else {
		$newabstratus = "None.";
	}
	echo "Abstrati unassigned: $free";

	function checkvalues($itemname)
	{
		//echo "wut";
		global $mysqli;
		$itemname = str_replace("'", "\\\\''", $itemname); //tch tch.
		$itemresult = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
		while ($itemrow = $itemresult->fetch_array()) { //Pull itemrow data from mysqli array
			// var_dump($itemrow); //DEV, checks for array conents
			//Stolen code from inventory.php START
			$PrintBit = "";
			$actives = $itemrow['aggrieve'] + $itemrow['aggress'] + $itemrow['assail'] + $itemrow['assault'];
			if ($actives != 0)
				$PrintBit = $PrintBit . " Actives: $actives";
			$passives = $itemrow['abuse'] + $itemrow['accuse'] + $itemrow['abjure'] + $itemrow['abstain'];
			if ($passives != 0)
				$PrintBit = $PrintBit . " Passives: $passives";
			if ($itemrow['power'] != 0)
				$PrintBit = $PrintBit . " Power: $itemrow[power]";
			if ($PrintBit != "")
				$PrintBit = " (" . $PrintBit . " )";

			return $PrintBit;

			//End stolen code
		}

	}

	echo '<form action="portfolio.php" method="post"><select name="new_abstratus">';
	$itemresult = $mysqli->query("SELECT * FROM Captchalogue  WHERE `Captchalogue`.`abstratus` NOT LIKE '%notaweapon%' ORDER BY abstratus");
	$currentabstratus = "";
	while ($itemrow = $itemresult->fetch_array()) {
		$mainabstratus = "";
		$alreadydone = false;
		$foundcomma = false;
		$j = 0;
		if (strrchr($itemrow['abstratus'], ',') == false) {
			$mainabstratus = $itemrow['abstratus'];
		} else {
			while ($foundcomma != true) {
				$char = "";
				$char = substr($itemrow['abstratus'], $j, 1);
				if ($char == ",") { //Found a comma. We know there is one because of the if statement above. Break off the string as the main abstratus.
					$mainabstratus = substr($itemrow['abstratus'], 0, $j);
					$foundcomma = true;
				} else {
					$j++;
				}
			}
		}
		if ($currentabstratus == $mainabstratus) {
			$alreadydone = true;
		} else {
			$currentabstratus = $mainabstratus;
		}
		if ($alreadydone == false && $mainabstratus != "notaweapon" && $mainabstratus != "headgear" && $mainabstratus != "bodygear" && $mainabstratus != "facegear" && $mainabstratus != "accessory" && $mainabstratus != "computer") { //New abstratus to add to the options.
			echo '<option value = "' . $mainabstratus . '">' . $mainabstratus . '</option>';
		}
	}
	echo '</select> <input type="submit" value="Assign it!" /> </form>';
	echo '<form action="portfolio.php" method="post"><select name="equipmain">';
	$i = 1;
	while ($i <= $max_items) {
		$invslot = "inv" . strval($i);
		if ($itemenabledm[$i]) { //User has existing matching abstratus
			echo '<option value = "' . $invslot . '">' . $userrow[$invslot];
			echo checkvalues($userrow[$invslot]); //Put in more details
			if (!$itemenabledo[$i])
				echo " (Two-handed)";
			echo '</option>';
		}
		$i++;
	}
	echo '<option value="Remove Equipment">Remove Equipment</option>';
	echo '</select> <input type="submit" value="Equip to main hand" /> </form>';
	echo '<form action="portfolio.php" method="post"><select name="equipoff">';
	$i = 1;
	while ($i <= $max_items) {
		$invslot = "inv" . strval($i);
		if ($itemenabledo[$i]) { //User has existing matching abstratus
			echo '<option value = "' . $invslot . '">' . $userrow[$invslot];
			echo checkvalues($userrow[$invslot]); //Put in more details
			echo '</option>';
		}
		$i++;
	}
	echo '<option value="Remove Equipment">Remove Equipment</option>';
	echo '</select> <input type="submit" value="Equip to offhand" /> </form>';
	$echeresult = $mysqli->query("SELECT * FROM Echeladders WHERE `Echeladders`.`username` = '" . $username . "'");
	$echerow = $echeresult->fetch_array();
	echo "Current Echeladder height: $userrow[Echeladder]";
	if (!empty($newrung)) {
		echo "<br/>Current Echeladder rung: $newrung <br/>";
	} else {
		$echestr = "rung" . strval($userrow['Echeladder']);
		if (!empty($echerow[$echestr])) {
			echo "<br/>Current Echeladder rung: $echerow[$echestr]<br/>";
		} else {
			echo '<form action="portfolio.php" method="post">';
			echo 'Current Echeladder rung: <input id="echename" name="echename" type="text" /><input type="submit" value="Name it!" /> </form>';
		}
	}

	if ($userrow['powerboost'] != 0)
		echo "Current temporary power modifier: $userrow[powerboost]<br/>";
	if ($userrow['offenseboost'] != 0)
		echo "Current temporary offense modifier: $userrow[offenseboost]<br/>";
	if ($userrow['defenseboost'] != 0)
		echo "Current temporary defense modifier: $userrow[defenseboost]<br/>";
	
	echo "Sprite's power level: $spritePower<br/>";
	if ($spritePower < 0) {
		echo "Your sprite is useless in combat! You decide to leave it behind.<br/>";
		$spritePower = 0;
	}

	echo "Current power level: $powerLevel <br/>";
	if ($powerLevel == 9001)
		echo "(Yes, yes, very funny.)<br/>";
	echo "Health Vial: ";
	if ($userrow['dreamingstatus'] == "Awake") {
		echo strval(floor(($userrow['Health_Vial'] / $userrow['Gel_Viscosity']) * 100)); //Computes % of max HP remaining.
		echo "%<br/>";
	} else {
		echo strval(floor(($userrow['Dream_Health_Vial'] / $userrow['Gel_Viscosity']) * 100)); //Computes % of max HP remaining.
		echo "%<br/>";
	}
	echo "Aspect vial: " . strval(floor(($userrow['Aspect_Vial'] / $userrow['Gel_Viscosity']) * 100)) . "%<br/>";
	$invresult = $mysqli->query("SELECT * FROM Players WHERE `Players`.`username` = '$username'");
	echo $username;
	echo "'s captchalogued weapons:<br/><br/>";
	$reachinv = false;
	while (($col = $invresult->fetch_field())) {
		$invslot = $col->name;
		if ($invslot == "inv1") { //Reached the start of the inventory.
			$reachinv = true;
		}
		if ($invslot == "abstratus1") { //Reached the end of the inventory.
			$reachinv = false;
			break;
		}
		if ($reachinv && $userrow[$invslot] != "") { //This is a non-empty inventory slot.
			$itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$captchalogue = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
			while ($row = $captchalogue->fetch_array()) {
				$itemname = $row['name'];
				$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
				$firstabstratus = "";
				$foundcomma = false;
				$j = 0;
				if (strrchr($row['abstratus'], ',') == false) {
					$firstabstratus = $row['abstratus'];
				} else {
					while ($foundcomma != true) {
						$char = "";
						$char = substr($row['abstratus'], $j, 1);
						if ($char == ",") { //Found a comma. We know there is one because of the if statement above. Break off the string as the main abstratus.
							$firstabstratus = substr($row['abstratus'], 0, $j);
							$foundcomma = true;
						} else {
							$j++;
						}
					}
				}
				if ($itemname == $userrow[$invslot] && $firstabstratus != "notaweapon") { //Item found in captchalogue database, and it is a weapon. Print out details.
					echo "Weapon: $itemname<br/>";
					if ($row['art'] != "") {
						echo '<img src="/Images/Items/' . $row['art'] . '" title="Image by ' . $row['credit'] . '"><br/>';
					}
					if (($invslot == $userrow['equipped'] || $invslot == $equippedmain) && $invslot != $equippedoff) { //Item is equipped in the main hand.
						if ($invslot == $equippedmain || $equippedmain == "") { //Most recently equipped item is either this or nothing.
							if ($row['size'] == "large") {
								echo "Equipped in: both hands.<br/>";
							} else {
								echo "Equipped in: main hand.<br/>";
							}
						}
					}
					if (($invslot == $userrow['offhand'] || $invslot == $equippedoff) && $invslot != $equippedmain) { //Item is equipped in the offhand.
						if ($invslot == $equippedoff || $equippedoff == "") { //Most recently equipped item is either this or nothing.
							echo "Equipped in: offhand.<br/>";
						}
					}
					echo "Abstratus: $row[abstratus]<br/>";
					echo "Strength: $row[power]<br/>";
					if ($row['aggrieve'] > 0) {
						echo "Aggrieve bonus: $row[aggrieve] <br/>";
					}
					if ($row['aggrieve'] < 0) {
						echo "Aggrieve penalty: $row[aggrieve] <br/>";
					}
					if ($row['aggress'] > 0) {
						echo "Aggress bonus: $row[aggress] <br/>";
					}
					if ($row['aggress'] < 0) {
						echo "Aggress penalty: $row[aggress] <br/>";
					}
					if ($row['assail'] > 0) {
						echo "Assail bonus: $row[assail] <br/>";
					}
					if ($row['assail'] < 0) {
						echo "Assail penalty: $row[assail] <br/>";
					}
					if ($row['assault'] > 0) {
						echo "Assault bonus: $row[assault] <br/>";
					}
					if ($row['assault'] < 0) {
						echo "Assault penalty: $row[assault] <br/>";
					}
					if ($row['abuse'] > 0) {
						echo "Abuse bonus: $row[abuse] <br/>";
					}
					if ($row['abuse'] < 0) {
						echo "Abuse penalty: $row[abuse] <br/>";
					}
					if ($row['accuse'] > 0) {
						echo "Accuse bonus: $row[accuse] <br/>";
					}
					if ($row['accuse'] < 0) {
						echo "Accuse penalty: $row[accuse] <br/>";
					}
					if ($row['abjure'] > 0) {
						echo "Abjure bonus: $row[abjure] <br/>";
					}
					if ($row['abjure'] < 0) {
						echo "Abjure penalty: $row[abjure] <br/>";
					}
					if ($row['abstain'] > 0) {
						echo "Abstain bonus: $row[abstain] <br/>";
					}
					if ($row['abstain'] < 0) {
						echo "Abstain penalty: $row[abstain] <br/>";
					}
					if (!empty($row['effects'])) { //Item has effects. Print those here.
						$effectarray = explode('|', $row['effects']);
						$effectnumber = 0;
						while (!empty($effectarray[$effectnumber])) {
							$currenteffect = $effectarray[$effectnumber];
							$currentarray = explode(':', $currenteffect);
							$efound = printEffects($currentarray);
							if (!$efound)
								logDebugMessage($username . " - unrecognized item property $currentarray[0] from $row[name]");
							$effectnumber++;
						}
					}
					$desc = descvarConvert($userrow, $row['description'], $row['effects']);
					echo "Description: $desc<br/><br/>";
				}
			}
		}
	}
}
require_once "footer.php";
?>