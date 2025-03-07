<?php
$invslots = 50; //Increase this if inventory size goes up.
$strifeslots = 16;
function addItem($item, $userrow, $incode = "00000000")
{ //Adds an item to a user's inventory. Returns true if successful, or false if the user's inventory is full.
	global $mysqli;
	$invslots = 50; //Placed here so function can use it.
	for ($i = 1; $i <= $invslots; $i++) {
		$invstr = "inv" . strval($i);
		//echo $invstr;
		if ($userrow[$invstr] == "") { //First empty inventory card
			$compuname = str_replace("'", "\\\\''", $item); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$compuname = str_replace("\\\\\\", "\\\\", $compuname); //really hope this works
			$compuresult = $mysqli->query("SELECT `captchalogue_code`,`name`, `size`, `effects` FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $compuname . "' LIMIT 1;");
			$compurow = $compuresult->fetch_array();
			$item = str_replace("\\", "", $item); //Remove escape backslashes since inventory doesn't have 'em.
			if (itemSize($compurow['size']) <= itemSize($userrow['moduspower'])) {
				if ($item == "Captchalogue Card")
					$item = "Captchalogue Card (CODE:$incode)";
				if ($item == "Cruxite Dowel")
					$item = "Cruxite Dowel (CODE:$incode)";
				if ($item == "Punch Card Shunt") {
					if ($incode != "00000000")
						$item = "Punch Card Shunt (CODE:$incode)"; //shunts containing unpunched cards? the card will disappear when retrieved because lazy also who would do this and expect something to happen
				}
				$mysqli->query("UPDATE `Players` SET `" . $invstr . "` = '" . $mysqli->real_escape_string($item) . "' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;");
				$userrow[$invstr] = $item;
				$athenresult = $mysqli->query("SELECT `atheneum` FROM Sessions WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "' LIMIT 1;");
				$athenrow = $athenresult->fetch_array();
				if (!strrpos($athenrow['atheneum'], $compurow['captchalogue_code']) && strpos($compurow['effects'], "OBSCURED|") === false) {
					$newatheneum = $athenrow['atheneum'] . $compurow['captchalogue_code'] . "|";
					$mysqli->query("UPDATE `Sessions` SET `atheneum` = '" . $newatheneum . "' WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "' LIMIT 1 ;");
				}
				compuRefresh($userrow);
				return $invstr;
			} else {
				for ($j = 1; $j <= $invslots; $j++) {
					$jnvstr = "inv" . strval($j);
					$compuname = str_replace("'", "\\\\''", $userrow[$jnvstr]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
					$compuname = str_replace("\\\\\\", "\\\\", $compuname); //really hope this works
					$compuresult = $mysqli->query("SELECT `name`, `effects` FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $compuname . "' LIMIT 1;");
					while ($gostrow = $compuresult->fetch_array()) {
						$ghosters = specialArray($gostrow['effects'], "GHOSTER");
						if ($ghosters[0] == "GHOSTER") {
							echo "<br/>This item is too big for you to captchalogue! Instead, you use your " . $gostrow['name'] . " to create a ghost image of it.<br/>";
							$mysqli->query("UPDATE `Players` SET `" . $invstr . "` = '" . $mysqli->real_escape_string($item . " (ghost image)") . "' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;");
							$athenresult = $mysqli->query("SELECT `atheneum` FROM Sessions WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "' LIMIT 1;");
							$athenrow = $athenresult->fetch_array();
							if (!strrpos($athenrow['atheneum'], $compurow['captchalogue_code']) && strpos($compurow['effects'], "OBSCURED|") === false) {
								$newatheneum = $athenrow['atheneum'] . $compurow['captchalogue_code'] . "|";
								$mysqli->query("UPDATE `Sessions` SET `atheneum` = '" . $newatheneum . "' WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "' LIMIT 1 ;");
							}
							return "inv-1"; //we didn't actually obtain the item, so return failure
						}
					}
				}
				echo "<br/>This item is too big for you to captchalogue! You will need to find a way to upgrade your Fetch Modus first.<br/>";
				return "inv-1";
			}
		}
	}
	return "inv-1";
}

function addAbstratus($absstring, $userrow)
{
	global $mysqli;
	echo "WARNING: addAbstratus function is now defunct. Please use addSpecibus (includes/fieldparser.php) instead. If you're not a developer and you see this message, please submit a bug report immediately!<br/>";
	$strifeslots = 16;
	//require_once "includes/SQLconnect.php";
	/*$result = $mysqli->query("SELECT * FROM Players WHERE `Players`.`username` = '" . $username . "'");
	 while($row = $result->fetch_array()) {
	   if ($row['username'] == $username) {
		 $userrow = $row;
	   }
	 }*/
	$i = 1;
	if (strrpos($absstring, ",")) {
		$mainabstratus = "";
		$alreadydone = false;
		$foundcomma = false;
		$j = 0;
		while ($foundcomma != true) {
			$char = "";
			$char = substr($absstring, $j, 1);
			if ($char == ",") { //Found a comma. We know there is one because of the if statement above. Break off the string as the main abstratus.
				$mainabstratus = substr($absstring, 0, $j);
				$foundcomma = true;
			} else {
				$j++;
			}
		}
		if ($alreadydone == false && $mainabstratus != "notaweapon" && $mainabstratus != "headgear" && $mainabstratus != "bodygear" && $mainabstratus != "facegear" && $mainabstratus != "accessory" && $mainabstratus != "computer") { //New abstratus to add to the options.
			$newabstratus = $mainabstratus; //only add the main abstratus of the weapon, consider having it choose a random one instead later
		}
	} else {
		$newabstratus = $absstring;
	}
	while ($i <= $strifeslots) {
		$invstr = "abstratus" . strval($i);
		//echo $invstr;
		if ($userrow[$invstr] == "") { //First empty strife slot
			$mysqli->query("UPDATE `Players` SET `abstrati` = '" . strval($userrow['abstrati'] + 1) . "' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;");
			$mysqli->query("UPDATE `Players` SET `" . $invstr . "` = '" . $newabstratus . "' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;");
			$userrow[$invstr] = $newabstratus;
			return $invstr;
		}
		$i++;
	}
	//$mysqli->close();
	return "abstratus-1";
}

function autoUnequip($userrow, $exception, $invslot)
{
	global $mysqli;
	if ($exception != "headgear" && $userrow['headgear'] == $invslot) {
		$mysqli->query("UPDATE `Players` SET `headgear` = '' WHERE `Players`.`username` = '$userrow[username]'");
		if ($userrow['facegear'] == "2HAND")
			$mysqli->query("UPDATE `Players` SET `facegear` = '' WHERE `Players`.`username` = '$userrow[username]'");
		$lookfor = "headgear";
	}
	if ($exception != "facegear" && $userrow['facegear'] == $invslot) {
		$mysqli->query("UPDATE `Players` SET `facegear` = '' WHERE `Players`.`username` = '$userrow[username]'");
		$lookfor = "facegear";
	}
	if ($exception != "bodygear" && $userrow['bodygear'] == $invslot) {
		$mysqli->query("UPDATE `Players` SET `bodygear` = '' WHERE `Players`.`username` = '$userrow[username]'");
		$lookfor = "bodygear";
	}
	if ($exception != "accessory" && $userrow['accessory'] == $invslot) {
		$mysqli->query("UPDATE `Players` SET `accessory` = '' WHERE `Players`.`username` = '$userrow[username]'");
		$lookfor = "accessory";
	}
	if ($exception != "equipped" && $userrow['equipped'] == $invslot) {
		$mysqli->query("UPDATE `Players` SET `equipped` = '' WHERE `Players`.`username` = '$userrow[username]'");
		if ($userrow['offhand'] == "2HAND")
			$mysqli->query("UPDATE `Players` SET `offhand` = '' WHERE `Players`.`username` = '$userrow[username]'");
		$lookfor = "equipped";
	}
	if ($exception != "offhand" && $userrow['offhand'] == $invslot) {
		$mysqli->query("UPDATE `Players` SET `offhand` = '' WHERE `Players`.`username` = '$userrow[username]'");
		$lookfor = "offhand";
	}
	if (!empty($lookfor) && strpos($userrow['permstatus'], "." . $lookfor) !== false) { //this wearable is granting a perm effect
		$statusarray = explode("|", $userrow['permstatus']);
		$i = 0;
		while (!empty($statusarray[$i])) {
			$currentarray = explode(":", $statusarray[$i]);
			if ($currentarray[0] != "ALLY") { //allies are always permanent until their loyalty drops to 0, but that's handled elsewhere
				$duration = explode(".", $currentarray[0]);
				if ($duration[2] == $lookfor) {
					$statusarray[$i] = ""; //this effect wears off
				}
			}
			$i++;
		}
		$newstatus = implode("|", $statusarray);
		$newstatus = preg_replace("/\\|{2,}/", "|", $newstatus); //eliminate all blanks
		if ($newstatus == "|")
			$newstatus = "";
		if ($newstatus != $userrow['permstatus']) {
			$mysqli->query("UPDATE `Players` SET `permstatus` = '$newstatus' WHERE `Players`.`username` = '" . $userrow['username'] . "'");
		}
	}
}

function itemSize($size)
{
	switch ($size) {
		case "intangible": return 0;
		case "miniature": return 1;
		case "tiny": return 5;
		case "small": return 10;
		case "average": return 20;
		case "large": return 40;
		case "huge": return 100;
		case "immense": return 250;
		case "ginormous": return 1000;
		default: return 20; //in case some weird value is listed, treat it as "average"
	}
}

function storageSpace($storestring)
{
	global $mysqli;
	$boom = explode("|", $storestring);
	$space = 0;
	for ($i = 0; $i < count($boom); $i++) {
		$args = explode(":", $boom[$i]);
		$irow = $mysqli->query("SELECT `captchalogue_code`,`size` FROM `Captchalogue` WHERE `Captchalogue`.`captchalogue_code` = '$args[0]' LIMIT 1")->fetch_array();
		if (!empty($irow)) { //Item found.
			$space += itemSize($irow['size']) * $args[1];
		} else
			echo "ERROR: Items with code $args[0] stored, but no matching item was found. Please inform a dev immediately.<br/>";
	}
	return $space;
}

function compuRefresh($userrow)
{
	global $mysqli;
	//echo "running compuRefresh() for $userrow[username]<br/>";
	$complevel = 0;
	if (strpos($userrow['storeditems'], "ISCOMPUTER") !== false)
		$complevel = 1; //the player has a computer in storage
	$maxitems = 50;
	$captchalogue = "SELECT `name`,`abstratus`,`size` FROM Captchalogue WHERE ";
	$firstinvslot = array();
	$hasitems = false;
	for ($i = 1; $i <= $maxitems; $i++)
	{
		$invslot = 'inv' . strval($i);
		if ($userrow[$invslot] != "") { //This is a non-empty inventory slot.
			$hasitems = true;
			$pureitemname = str_replace("\\", "", $userrow[$invslot]);
			$pureitemname = str_replace("'", "", $pureitemname);
			$itemname = str_replace("'", "\\\\''", $userrow[$invslot]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$itemname = str_replace("\\\\\\''", "\\\\''", $itemname); //Fix extra backslash irregularities if any occur.
			//$captchalogue = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $itemname . "'");
			if (empty($captchaloguequantities[$pureitemname])) {
				$captchalogue = $captchalogue . "`Captchalogue`.`name` = '" . $itemname . "' OR ";
				$firstinvslot[$pureitemname] = $invslot;
				$captchaloguequantities[$pureitemname] = 1;
			} else {
				$captchaloguequantities[$pureitemname] += 1;
			}
		}
	}

	if ($hasitems)
	{
		$captchalogue = substr($captchalogue, 0, -4);
		//echo $captchalogue . "<br/>";
		$captchalogueresult = $mysqli->query($captchalogue);
		while ($compurow = $captchalogueresult->fetch_array())
		{
			$pureitemname = str_replace("\\", "", $compurow['name']);
			$pureitemname = str_replace("'", "", $pureitemname);
			$invstr = $firstinvslot[$pureitemname];
			if (strrpos($compurow['abstratus'], "computer") !== false)
			{
				if (itemSize($compurow['size']) <= itemSize("average") && $complevel <= 1)
					$complevel = 2; //the computer is portable and can be used from inventory
				if ($complevel <= 2) {
					if ($userrow['equipped'] == $invstr)
						$complevel = 3;
					if ($userrow['offhand'] == $invstr)
						$complevel = 3;
					if ($userrow['headgear'] == $invstr)
						$complevel = 3;
					if ($userrow['facegear'] == $invstr)
						$complevel = 3;
					if ($userrow['bodygear'] == $invstr)
						$complevel = 3;
					if ($userrow['accessory'] == $invstr)
						$complevel = 3;
				}
				//echo "complevel is $complevel<br/>";
			}
		}
	}
	//echo "final compulevel: $complevel<br/>";
	if ($complevel != $userrow['hascomputer'])
		$mysqli->query("UPDATE `Players` SET `hascomputer` = $complevel WHERE `Players`.`username` = '$userrow[username]'");
}

function specialArray($itemeffects, $search)
{ //finds a tag in the "effects" field and returns the array associated with it, useful for looking up single effects
	$effectarray = explode('|', $itemeffects);
	for ($effectnumber = 0; !empty($effectarray[$effectnumber]); $effectnumber++) {
		$currenteffect = $effectarray[$effectnumber];
		$currentarray = explode(':', $currenteffect);
		if ($currentarray[0] == $search)
			return $currentarray;
	}
	return ["notfound"]; //indicates the searched tag doesn't appear
}

function grantEffects($userrow, $itemeffects, $slot)
{ //finds a tag in the "effects" field and returns the array associated with it, useful for looking up single effects
	global $mysqli;
	$grantarray = specialArray($itemeffects, "GRANT");
	if ($grantarray[0] == "GRANT") {
		$granted = explode(".", $grantarray[1]);
		$i = 0;
		while (!empty($granted[$i])) {
			$grantedtag = explode("/", $granted[$i]);
			$grantedtag[0] .= ".-1." . $slot;
			$granted[$i] = implode(":", $grantedtag);
			$i++;
		}
		if (!empty($userrow['permstatus']))
			$newstatus = $userrow['permstatus'] . "|" . implode("|", $granted) . "|";
		else
			$newstatus = implode("|", $granted) . "|";
		$newstatus = preg_replace("/\\|{2,}/", "|", $newstatus); //eliminate all blanks
		$mysqli->query("UPDATE `Players` SET `permstatus` = '$newstatus' WHERE `Players`.`username` = '" . $userrow['username'] . "'");
	}
}

function storeItem($item, $tostorage, $userrow, $stackcode = "00000000")
{ //making this a function because it's too useful.
	global $mysqli;
	$compuname = str_replace("'", "\\\\''", $item); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
	$compuname = str_replace("\\\\\\", "\\\\", $compuname); //really hope this works
	$compuresult = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $compuname . "' LIMIT 1;");
	$itemrow = $compuresult->fetch_array();
	$space = storageSpace($userrow['storeditems']);
	$boom = explode("|", $userrow['storeditems']);
	$totalitems = count($boom);
	$i = 0;
	$storesize = itemSize($itemrow['size']);
	$nospace = false;
	$itemstored = false;
	$madeanewone = false;
	$updatestring = "";
	$actualstore = 0;
	$maxstorage = $userrow['house_build_grist'] + 1000;
	$codetag = specialArray($itemrow['effects'], "CODEHOLDER");
	if ($codetag[0] == "CODEHOLDER")
		$stackwith = "CODE=" . $stackcode . ".";
	while ($i < $totalitems) {
		if (!empty($boom[$i]))
			$args = explode(":", $boom[$i]);
		else { //this is the one beyond the final line, which will always be empty
			if (!$itemstored) { //Paranoia: make sure we didn't already send items to storage
				$args[0] = $itemrow['captchalogue_code']; //make this slot the item we're making since it doesn't exist in storage
				$args[1] = 0;
				$storagetag = specialArray($itemrow['effects'], "STORAGE");
				$args[2] = "";
				if (strpos($itemrow['abstratus'], "computer") !== false)
					$args[2] .= "ISCOMPUTER.";
				if ($storagetag[0] == "STORAGE")
					$args[2] .= $storagetag[1]; //semicolons should be included in the effect string
				if ($codetag[0] == "CODEHOLDER") {
					$args[2] .= "CODE=" . $stackcode . ".";
					$stackwith = $args[2];
				}
				$madeanewone = true;
			} else {
				$args[0] = ""; //lol forgot to unset these when they should be blank
				$args[1] = 0;
				$args[2] = "";
			}
		}
		if ($args[0] == $itemrow['captchalogue_code'] && (empty($stackwith) || strpos($args[2], $stackwith) !== false)) {
			while ($tostorage > 0) {
				if ($space + $storesize <= $maxstorage) {
					$tostorage--;
					$space += $storesize;
					$actualstore++;
					$args[1]++;
				} else {
					$tostorage = 0;
					$nospace = true;
				}
			}
		}
		$i++;
		if (!empty($args[0]) && $args[1] != 0) {
			$updatestring .= $args[0] . ":" . $args[1] . ":" . $args[2] . "|";
		}
	}
	if ($updatestring != $userrow['storeditems']) {
		$mysqli->query("UPDATE `Players` SET `storeditems` = '$updatestring' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1");
		$userrow['storeditems'] = $updatestring;
		compuRefresh($userrow);
	}
	return $actualstore; //returns number of items stored
}
