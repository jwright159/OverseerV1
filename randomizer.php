<?php
require 'designix.php';
require_once "header.php";

function initGrists()
{
	$result2 = $mysqli->query("SELECT * FROM `Captchalogue` LIMIT 1;"); //document grist types now so we don't have to do it later
	$reachgrist = false;
	$terminateloop = false;
	$totalgrists = 0;
	while (($col = $result2->fetch_field()) && $terminateloop == false) {
		$gristcost = $col->name;
		$gristtype = substr($gristcost, 0, -5);
		if ($gristcost == "Build_Grist_Cost") { //Reached the start of the grists.
			$reachgrist = true;
		}
		if ($gristcost == "End_of_Grists") { //Reached the end of the grists.
			$reachgrist = false;
			$terminateloop = true;
		}
		if ($reachgrist == true) {
			$gristname[$totalgrists] = $gristtype;
			$totalgrists++;
		}
	}
	return $gristname;
}

function totalGristcost($countrow, $gristname, $totalgrists)
{
	$i = 0;
	$totalcost = 0;
	while ($i < $totalgrists) {
		//echo $gristname[$i] . " - " . strval($countrow[$gristname[$i] . '_Cost']) . "<br/>";
		$totalcost = $totalcost + $countrow[$gristname[$i] . '_Cost'];
		$i++;
	}
	return $totalcost;
}

if (empty($_SESSION['username'])) {
	echo "Log in to use the Randomizer.<br/>";
} elseif ($userrow['modlevel'] <= -2) {
	echo "You have been banned from using the Randomizer, most likely due to abuse or spam.<br/>";
} else {

	$showdetails = false;
	$dontshowitems = false;
	$urladdon = "?";
	if (empty($_GET['detail']) || $_GET['detail'] != "no") {
		$showdetails = true;
		$urladdon .= "detail=yes";
	}

	$sessionname = $userrow['session_name'];
	$sessionresult = $mysqli->query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$sessionname'");
	$sessionrow = $sessionresult->fetch_array();
	$challenge = $sessionrow['challenge'];

	if ($challenge == 1) {
		echo "In Challenge Mode, you are limited to combinations from your Atheneum.<br/>";
		$_GET['atheneum'] = "yes";
	}

	if (!empty($_POST['newitem'])) { //User is submitting an item.
		$systemresult = $mysqli->query("SELECT * FROM System");
		$systemrow = $systemresult->fetch_array();
		$newid = $systemrow['totalsubmissions'];
		$newitem = $mysqli->real_escape_string(str_replace(';', ':', $_POST['newitem']));
		$newdesc = $mysqli->real_escape_string(str_replace(';', ':', $_POST['newdesc']));
		$items = $mysqli->real_escape_string(str_replace(';', ':', $_POST['items']));
		if (strrpos($items, "&&"))
			$newcode = $_POST['andcode'];
		elseif (strrpos($items, "||"))
			$newcode = $_POST['orcode'];
		$newother = $mysqli->real_escape_string($_POST['other']);
		if (!empty($_POST['recpower'])) {
			$newpower = intval($_POST['recpower']);
			$newother = $newother . " (NOTE: Power level was suggested by the Randomizer.";
			if ($newpower > 9999) {
				$bonuspower = $newpower - 9999;
				$newpower = 9999;
				$newother = $newother . " It also suggests an average bonus of +" . strval($bonuspower);
			}
			$newother = $newother . ")";
		} else
			$newpower = intval($_POST['power']);
		$aok = true;
		if ($newpower > 9999) {
			echo "Submission error: new item's power level cannot exceed 9999. Use additional comments to convey combat bonuses or uncertainty<br/>";
			$aok = false;
		}
		if ($newitem == "") {
			echo "Submission error: please give this item a name<br/>";
			$aok = false;
		}
		if ($newdesc == "") {
			echo "Submission error: please give this item a description, it can be as vague or as short as you want as long as we can tell what it is<br/>";
			$aok = false;
		}
		if (strlen($newcode) == 8) {
			$existresult = $mysqli->query("SELECT `captchalogue_code`,`name` FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $newcode . "' LIMIT 1;");
			$existrow = $existresult->fetch_array();
			if ($existrow['captchalogue_code'] == $newcode) {
				echo "Submission error: the code you gave refers to an item that already exists. Make sure you've given the correct code.<br/>";
				$aok = false;
			} elseif ($challenge == 1 && strrpos($sessionrow['atheneum'], $newcode) === false) {
				echo "Some people think they can outsmart me... maybe. *sniff* maybe. I have yet to meet someone who can outsmart hardcoding.<br/>";
				$aok = false;
			}
		}
		if (strrpos($newother, "bladekind"))
			echo "ahahaha bladekind you so funny<br/>";
		if ($aok) {
			$currenttime = time();
			$mysqli->query("INSERT INTO `Feedback` (`ID`, `user`, `type`, `name`, `code`, `recipe`, `power`, `description`, `comments`, `urgent`, `randomized`, `lastupdated`) VALUES ('" . $newid . "', '" . $username . "', 'item', '" . $newitem . "', '" . $newcode . "', '" . $items . "', '" . strval($newpower) . "', '" . $newdesc . "', '" . $newother . "', $challenge, 1, $currenttime)");
			$mysqli->query("UPDATE `System` SET `totalsubmissions` = " . strval($newid + 1) . " WHERE 1");
			echo 'Item submitted! (ID: ' . strval($newid) . ') <a href="submissions.php?view=' . strval($newid) . '">You can view your suggestion here.</a><br/>';
		}
	}
	echo 'Welcome to the Randomizer! You will receive the names of two random items every page load.<br/>';
	echo "You can use these for inspiration to make a new, unique item; or if you're feeling lucky, recipes for existing items.<br/><br/>";
	$totalitems = 0;
	if (!empty($_GET['atheneum']) && $_GET['atheneum'] == "yes") {
		if ($urladdon != "?")
			$urladdon .= "&";
		$urladdon .= "atheneum=yes";
	}
	if (empty($_GET['invonly']) || $_GET['invonly'] != "yes") {
		if (!empty($_GET['baseonly']) && $_GET['baseonly'] == "yes") {
			if ($urladdon != "?")
				$urladdon .= "&";
			$urladdon .= "baseonly=yes";
			$itemsresult = $mysqli->query("SELECT `captchalogue_code`,`name` FROM `Captchalogue` WHERE `Captchalogue`.`catalogue` = 1");
		} else {
			$itemsresult = $mysqli->query("SELECT `captchalogue_code`,`name` FROM `Captchalogue`");
		}
		$realtotalitems = 0;
		while ($row = $itemsresult->fetch_array()) {
			if (!empty($_GET['atheneum']) && $_GET['atheneum'] == "yes") {
				$realtotalitems++;
				if (!(strrpos($sessionrow['atheneum'], $row['captchalogue_code']) === false)) {
					$totalitems++;
					$athenitem[$totalitems] = $realtotalitems;
				}
			} else
				$totalitems++;
		}
		if (!empty($_GET['userabstratus'])) {
			if ($urladdon != "?")
				$urladdon .= "&";
			$urladdon .= "userabstratus=" . $_GET['userabstratus'];
			$aotwstring = $_GET['userabstratus'];
			$totalaotw = 0;
			$itemsresult = $mysqli->query("SELECT `captchalogue_code`,`name` FROM `Captchalogue` WHERE `Captchalogue`.`abstratus` LIKE '%" . $aotwstring . "%'");
			$realtotalaotw = 0;
			while ($row = $itemsresult->fetch_array()) {
				if ($_GET['atheneum'] == "yes") {
					$realtotalaotw++;
					if (!(strrpos($sessionrow['atheneum'], $row['captchalogue_code']) === false)) {
						$totalaotw++;
						$athenitemb[$totalaotw] = $realtotalaotw;
					}
				} else
					$totalaotw++;
			}
		}
		$makecombo = true;
		$attempts = 0;
		while ($makecombo == true && $attempts < 100) {
			$makecombo = false;
			$attempts++;
			if (empty($_GET['userabstratus'])) {
				$item1 = rand(1, $totalitems);
				if (empty($_GET['atheneum']) || $_GET['atheneum'] != "yes")
					$item1--;
				else
					$item1 = $athenitem[$item1] - 1;
			} else {
				$item1 = rand(1, $totalaotw);
				if (empty($_GET['atheneum']) || $_GET['atheneum'] != "yes")
					$item1--;
				else
					$item1 = $athenitemb[$item1] - 1;
			}
			$item2 = rand(1, $totalitems);
			if (empty($_GET['atheneum']) || $_GET['atheneum'] != "yes") {
				$item2--;
				while ($item1 == $item2)
					$item2 = rand(1, $totalitems) - 1;
			} else {
				$item2 = $athenitem[$item2] - 1;
				while ($item1 == $item2)
					$item2 = $athenitem[rand(1, $totalitems)] - 1;
			}
			while ($item1 == $item2)
				$item2 = $athenitem[rand(1, $totalitems)] - 1;
			if (!empty($_GET['baseonly']) && $_GET['baseonly'] == "yes") { //only base items
				if (!empty($_GET['userabstratus'])) { //first item must be abstratus of the week
					$itemresult1 = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`catalogue` = 1 AND `Captchalogue`.`abstratus` LIKE '%" . $aotwstring . "%' LIMIT " . $item1 . " , 1 ;");
				} else {
					$itemresult1 = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`catalogue` = 1 LIMIT " . $item1 . " , 1 ;");
				}
				$itemresult2 = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`catalogue` = 1 LIMIT " . $item2 . " , 1 ;");
			} else {
				if (!empty($_GET['userabstratus'])) {
					$itemresult1 = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`abstratus` LIKE '%" . $aotwstring . "%' LIMIT " . $item1 . " , 1 ;");
				} else {
					$itemresult1 = $mysqli->query("SELECT * FROM Captchalogue LIMIT " . $item1 . " , 1 ;");
				}
				$itemresult2 = $mysqli->query("SELECT * FROM Captchalogue LIMIT " . $item2 . " , 1 ;");
			}
			if (!empty($_GET['usercode'])) {
				if (strrpos($sessionrow['atheneum'], $_GET['usercode']) !== false || $challenge == 0) {
					$itemresult1 = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $_GET['usercode'] . "'");
				}
			}
			$irow1 = $itemresult1->fetch_array();
			$irow2 = $itemresult2->fetch_array();
			//if ($username == "Blahdev") echo "Tried " . $irow1['name'] . " and " . $irow2['name'] . "<br/>";
			$codeand = andcombine($irow1['captchalogue_code'], $irow2['captchalogue_code']); //combine the codes so we can check if the combination exists already
			$codeor = orcombine($irow1['captchalogue_code'], $irow2['captchalogue_code']);
			$itemresult = $mysqli->query("SELECT `captchalogue_code` FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $codeand . "'");
			while ($itemrow = $itemresult->fetch_array()) {
				if ($itemrow['captchalogue_code'] == $codeand)
					$makecombo = true; //and if it does, re-shuffle
			}
			$itemresult = $mysqli->query("SELECT `captchalogue_code` FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $codeor . "'");
			while ($itemrow = $itemresult->fetch_array()) {
				if ($itemrow['captchalogue_code'] == $codeor)
					$makecombo = true;
			}
		}
	} else {
		if ($urladdon != "?")
			$urladdon .= "&";
		$urladdon .= "invonly=yes";
		$k = 1;
		while ($k <= 50) {
			if ($userrow['inv' . $k] != "")
				$totalitems++;
			$k++;
		}
		if ($totalitems < 3) {
			echo "You don't have enough items for randomly choosing between them to make a difference.<br/>";
			$dontshowitems = true;
		} else {
			$item1 = 0;
			$item2 = 0;
			$item1t = rand(1, $totalitems);
			$item2t = rand(1, $totalitems);
			while ($item1t == $item2t)
				$item2t = rand(1, $totalitems);
			$k = 1;
			while ($k <= 50) {
				if ($userrow['inv' . $k] != "")
					$itemcount++;
				if ($itemcount == $item1t)
					$item1 = $k;
				if ($itemcount == $item2t)
					$item2 = $k;
				if ($item1 != 0 && $item2 != 0) {
					$k = 51;
				} else {
					$k++;
				}
			}
			$makecombo = true;
			while ($makecombo == true) {
				$makecombo = false;
				$truename1 = str_replace("'", "\\\\''", $userrow['inv' . $item1]);
				$itemresult1 = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $truename1 . "' LIMIT 1 ;");
				$irow1 = $itemresult1->fetch_array();
				$truename2 = str_replace("'", "\\\\''", $userrow['inv' . $item2]);
				$itemresult2 = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $truename2 . "' LIMIT 1 ;");
				$irow2 = $itemresult2->fetch_array();
				$codeand = andcombine($irow1['captchalogue_code'], $irow2['captchalogue_code']); //combine the codes so we can check if the combination exists already
				$codeor = orcombine($irow1['captchalogue_code'], $irow2['captchalogue_code']);
				$itemresult = $mysqli->query("SELECT `captchalogue_code` FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $codeand . "'");
				while ($itemrow = $itemresult->fetch_array()) {
					if ($itemrow['captchalogue_code'] == $codeand)
						$makecombo = true; //and if it does, re-shuffle
				}
				$itemresult = $mysqli->query("SELECT `captchalogue_code` FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $codeor . "'");
				while ($itemrow = $itemresult->fetch_array()) {
					if ($itemrow['captchalogue_code'] == $codeor)
						$makecombo = true;
				}
			}
		}
	}

	if ($dontshowitems == false) {
		$irow1['name'] = str_replace("\\'", "'", $irow1['name']);
		$irow2['name'] = str_replace("\\'", "'", $irow2['name']);
		echo 'Your random items are: <br/>';
		if ($showdetails) {
			echo $irow1['name'] . '(' . $irow1['abstratus'] . ')<br/>' . $irow1['description'];
			echo '<br/>...and...<br/>';
			echo $irow2['name'] . '(' . $irow2['abstratus'] . ')<br/>' . $irow2['description'];
		} else {
			echo $irow1['name'];
			echo '<br/>...and...<br/>';
			echo $irow2['name'];
		}
	}
	echo "<br/><br/>";
	$andstring = $irow1['name'] . " && " . $irow2['name'];
	$orstring = $irow1['name'] . " || " . $irow2['name'];
	if ($challenge == 1) { //add resulting codes to atheneum so that the randomizer knows that they were valid results at one point
		$updateatheneum = false;
		$newatheneum = $sessionrow['atheneum'];
		if (strrpos($sessionrow['atheneum'], $codeand) === false) {
			$newatheneum = $newatheneum . $codeand . "|";
			$updateatheneum = true;
		}
		if (strrpos($sessionrow['atheneum'], $codeor) === false) {
			$newatheneum = $newatheneum . $codeor . "|";
			$updateatheneum = true;
		}
		if ($updateatheneum)
			$mysqli->query("UPDATE Sessions SET `atheneum` = '$newatheneum' WHERE `Sessions`.`name` = '$sessionname' LIMIT 1;");
	}
	$binaryand = breakdown($codeand);
	$binaryor = breakdown($codeor);
	$bitcountand = 0;
	$bitcountor = 0;
	$i = 0;
	while ($i < 48) {
		$thisdigit = substr($binaryand, $i, 1);
		if ($thisdigit == "1")
			$bitcountand++;
		$i++; //Increment i
	}
	$i = 0;
	while ($i < 48) {
		$thisdigit = substr($binaryor, $i, 1);
		if ($thisdigit == "1")
			$bitcountor++;
		$i++; //Increment i
	}
	if ($bitcountand > 24)
		$bitcountand = 48 - $bitcountand;
	if ($bitcountor > 24)
		$bitcountor = 48 - $bitcountor;
	$recc = "none";
	if ($bitcountand > $bitcountor && $bitcountor < 8)
		$recc = "and";
	if ($bitcountand < $bitcountor && $bitcountand < 8)
		$recc = "or";
	echo '<form action="randomizer.php' . $urladdon . '" method="post" id="newitem">Like this combo? Why not turn it into a suggestion! (Code and recipe will be automatically filled out.)<br/>';

	echo 'Operation to use: <select name="items">';
	if ($recc == "and")
		echo '<option value="' . $andstring . '">&& (Recommended)</option><option value="' . $orstring . '">||</option>';
	elseif ($recc == "or")
		echo '<option value="' . $orstring . '">|| (Recommended)</option><option value="' . $andstring . '">&&</option>';
	else
		echo '<option value="' . $andstring . '">&&</option><option value="' . $orstring . '">||</option>';
	echo '</select><br/>';
	echo '<input type="hidden" name="andcode" value="' . $codeand . '"><input type="hidden" name="orcode" value="' . $codeor . '">';
	echo 'New item\'s name: <input id="newitem" name="newitem" type="text" /><br/>';
	echo 'New item\'s description:<br/><textarea name="newdesc" rows="6" cols="40" form="newitem"></textarea><br/>';
	echo 'Comments on the new item. This field is for suggestions like command bonuses, abstratus the item should have, grist to be used, etc:<br/><textarea name="other" rows="6" cols="40" form="newitem"></textarea><br/>';
	if ($irow1['power'] == 0 || $irow2['power'] == 0) {
		$gristname = initGrists();
		$totalgrists = count($gristname);
		if ($irow1['power'] == 0) {
			$irow1['power'] = floor(sqrt(totalGristcost($irow1, $gristname, $totalgrists) * 8));
		}
		if ($irow2['power'] == 0) {
			$irow2['power'] = floor(sqrt(totalGristcost($irow2, $gristname, $totalgrists) * 8));
		}
	}
	$fullpower = $irow1['power'] + $irow2['power'];
	$reccpower = rand($fullpower, $fullpower * 2) + 1;
	if ($reccpower > 19998)
		$reccpower = 19998;
	if ($fullpower * 1.5 > 19998)
		$fullpower = 13332;
	echo 'Suggested power/defense level: <input id="power" name="power" type="text" /><br/>';
	echo 'Average recommended power for these components, if a weapon (including bonuses): ' . strval(ceil($fullpower * 1.5)) . '<br/>';
	echo '<input type="checkbox" name="recpower" value="' . strval($reccpower) . '">Use the randomizer\'s recommendation, randomized (will give a random value near the recommended level)<br/>';
	echo '<input type="submit" name="button" value="Suggest it!" /></form><br/><br/>';

	echo '<form action="randomizer.php" method="get">';
	echo '<input type="checkbox" name="detail" value="no">Hide abstratus and description<br/><input type="checkbox" name="invonly" value="yes">Pick from just inventory items<br/><input type="checkbox" name="atheneum" value="yes">Pick from just items in your Atheneum<br/>';
	$aotwresult = $mysqli->query("SELECT * FROM `System` WHERE 1 ;");
	while ($sysrow = $aotwresult->fetch_array())
		$aotwstring = $sysrow['abstratusoftheweek'];
	if (empty($aotwstring))
		$aotwstring = "None yet. Go vote for one!";
	echo '<input type="checkbox" name="baseonly" value="yes">Limit selections to base items<br/>Make first item be one of a specified abstratus: <input name="userabstratus" type="text" /><br/>Code to use for the first item: <input name="usercode" type="text" /><br/>';
	echo '<input type="submit" value="Randomize it!" /></form>';
}
require_once "footer.php";
?>