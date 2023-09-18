<?php
require_once "header.php";

function canFly($checkrow)
{
	if ($checkrow['Godtier'] > 0)
		return true; //player is godtier and can fly no matter what
	$invcheck = 1;
	$invSlots = 50;
	while ($invcheck <= $invSlots) {
		$invstring = 'inv' . strval($invcheck);
		if (!empty($checkrow[$invstring])) {
			$chname = str_replace("'", "\\\\''", $checkrow[$invstring]);
			$chrow = fetchOne("SELECT `name`,`abstratus` FROM Captchalogue WHERE `Captchalogue`.`name` = '$chname' LIMIT 1;");
			if (strrpos($chrow['abstratus'], "flying"))
				return true;
		}
		$invcheck++;
	}
	return false;
}

if (empty($_SESSION['username'])) {
	echo "Log in to view your player overview.<br/>";
	echo '<br/><a href="/">Home</a> <a href="controlpanel.php">Control Panel</a><br/>';
} else {
	require_once "includes/SQLconnect.php";
	//echo "Confirmed: the page I'm editing is indeed the page on the live build<br/>";
	$fly = canFly($userrow);
	//--Begin naming/prototyping code here.--

	if (!empty($_POST['spritename'])) { //Renaming sprite.
		$spritename = $mysqli->real_escape_string($_POST['spritename']) . "sprite";
		$mysqli->query("UPDATE `Players` SET `sprite_name` = '$spritename' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	}
	if (!empty($_POST['item1'])) { //First prototyping. LOL FAILED SESSION
		if (empty($userrow['prototype_item_1'])) {
			if (intval($_POST['strength1']) < 1000 && intval($_POST['strength1']) > -1000) { //Valid prototype strength
				$strengthone = intval($_POST['strength1']);
				$newstrength = ($strengthone * 2) + $userrow['sprite_strength']; //Sprite receives double the item's native prototyping strength.
				$itemone = $mysqli->real_escape_string($_POST['item1']);
				if ($newstrength > ($userrow['sprite_strength'] * 2) || $newstrength > 2050) {
					echo "The sprite dodges the prototyping item! Looks like it was too powerful for the sprite to sustain.<br/>";
				} else {
					$mysqli->query("UPDATE `Players` SET `prototype_item_1` = '$itemone' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
					$mysqli->query("UPDATE `Players` SET `sprite_strength` = '$newstrength' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
				}
			}
		} else {
			echo "STOP TRYING TO BLOW YOUR SPRITE UP, FUCKASS.";
		}
	}
	if (!empty($_POST['item2'])) { //Second prototyping
		if (empty($userrow['prototype_item_2'])) {
			if (intval($_POST['strength2']) < 1000 && intval($_POST['strength2']) > -1000) { //Valid prototype strength
				$itemtwo = $mysqli->real_escape_string($_POST['item2']);
				$strengthtwo = intval($_POST['strength2']);
				$newstrength = ($strengthtwo * 2) + $userrow['sprite_strength']; //Sprite receives double the item's native prototyping strength.
				if ($newstrength > ($userrow['sprite_strength'] * 2) || $newstrength > 2050) {
					echo "The sprite dodges the prototyping item! Looks like it was too powerful for the sprite to sustain.<br/>";
				} else {
					$mysqli->query("UPDATE `Players` SET `prototype_item_2` = '$itemtwo' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
					$mysqli->query("UPDATE `Players` SET `sprite_strength` = '$newstrength' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
				}
			}
		} else {
			echo "STOP TRYING TO BLOW YOUR SPRITE UP, FUCKASS.";
		}
	}

	//--End naming/prototyping code here. Begin class/aspect assignment code here.--
	if (!empty($_POST['class']) && !empty($_POST['aspect'])) {
		if ((empty($userrow['Class']) || $userrow['Class'] == "Default") && empty($userrow['Aspect'])) {
			$titlegood = 0;
			$aspects = $mysqli->query("SELECT * FROM Titles LIMIT 1;");
			$reachaspect = false;
			while ($col = $aspects->fetch_field()) {
				$aspect = $col->name;

				if ($aspect == "Breath")
					$reachaspect = true;
				if ($aspect == "General")
					$reachaspect = false;
				
				if ($reachaspect && $_POST['aspect'] == $aspect)
					$titlegood++;
			}
			$classes = $mysqli->query("SELECT * FROM Titles");
			$reachclass = true;
			while ($row = $classes->fetch_array()) {
				$classresult = $mysqli->query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$row[Class]';");
				$classrow = $classresult->fetch_array();
				if ($row['Class'] == "General")
					$reachclass = false;
				if ($reachclass && $_POST['class'] == $row['Class'])
					$titlegood++;
			}
			if ($titlegood == 2) {
				$newclass = $mysqli->real_escape_string($_POST['class']);
				$newclass = str_replace("<", "&lt;", $newclass);
				$newaspect = $mysqli->real_escape_string($_POST['aspect']);
				$newaspect = str_replace("<", "&lt;", $newaspect);
				$titleresult = $mysqli->query("SELECT * FROM `Titles` WHERE `Titles`.`Class` = 'Adjective'");
				$titlerow = $titleresult->fetch_array();
				$sesname = $userrow['session_name'];
				$sessionresult = $mysqli->query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '$sesname' LIMIT 1;"); //select session so we know if uniques are on
				$sesrow = $sessionresult->fetch_array();
				$aok = false;
				if ($sesrow['uniqueclasspects'] == 1) {
					$teamresult = $mysqli->query("SELECT `Class`,`Aspect` FROM Players WHERE `Players`.`session_name` = '$sesname'");
					$totalplayers = 0;
					$classclash = false;
					$aspectclash = false;
					$doubleclash = false;
					$failreason = "nothing";
					while ($row = $teamresult->fetch_array()) {
						$totalplayers++;
						if ($row['Class'] == $newclass)
							$classclash = true;
						if ($row['Aspect'] == $newaspect)
							$aspectclash = true;
						if ($row['Class'] == $newclass && $row['Aspect'] == $newaspect)
							$doubleclash = true;
					}
					if ($totalplayers <= 12 && $classclash)
						$failreason = $newclass;
					if ($totalplayers <= 12 && $aspectclash)
						$failreason = $newaspect;
					if ($totalplayers <= 144 && $doubleclash)
						$failreason = $newclass . " of " . $newaspect;
					if ($failreason == "nothing")
						$aok = true;
					else {
						$newclass = "";
						$newaspect = "";
					}
				} else
					$aok = true;
				if ($aok) {
					$mysqli->query("UPDATE `Players` SET `Class` = '$newclass' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
					$mysqli->query("UPDATE `Players` SET `Aspect` = '$newaspect' WHERE `Players`.`username` = '" . $username . "' LIMIT 1 ;");
					$_SESSION['adjective'] = $titlerow[$newaspect];
				} else
					echo "Class/aspect failed to set: Unique classpects is on, and $failreason already belongs to a player in this session<br/>";
			} else
				echo "pfft what are you smoking that's not even a real title<br/>";
		} else {
			echo "NICE TRY, WIGGLER.";
		}
	}
	//--End class/aspect assignment code here. Begin status code here.--
	if (!empty($_POST['status'])) { //Renaming sprite.
		$newstatus = $mysqli->real_escape_string($_POST['status']);
		$newstatus = str_replace("<", "&lt;", $newstatus);
		$mysqli->query("UPDATE `Players` SET `status` = '$newstatus' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	}
	//End status code here. Begin symbol code
	if (!empty($_POST['newimage'])) { //Reimagining image.
		$newimg = $mysqli->real_escape_string($_POST['newimage']);
		$newimg = str_replace("<", "&lt;", $newimg);
		$userrow['symbol'] = $newimg;
		$mysqli->query("UPDATE `Players` SET `symbol` = '$newimg' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
	}
	//End symbol code here. Begin awakening code here.
	if (!empty($_POST['dreamer'])) { //Choosing dream affiliation.
		if (empty($userrow['Dreamer'])) {
			if ($_POST['dreamer'] == "Prospit" || $_POST['dreamer'] == "Derse") { //pretty sure prospit and derse will forever remain the only two moons
				$dreamstatus = $mysqli->real_escape_string($_POST['dreamer']);
				$mysqli->query("UPDATE `Players` SET `dreamer` = '" . $dreamstatus . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
			} else
				echo "suddenly, WEED DREAMS.<br/>";
		} else {
			echo "WHAT THE FUCK. YOU CAN'T JUST CHANGE DREAMING MOONS, YOU MORON.";
		}
	}
	//End awakening code here. Begin colouring code here.

	if (!empty($_POST['colour'])) {
		$mysqli->query("UPDATE `Players` SET `colour` = '" . $mysqli->real_escape_string($_POST['colour']) . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
		$userrow['colour'] = $mysqli->real_escape_string($_POST['colour']);
		$colournew = $userrow['colour']; //So we don't double-post with the header.
	}

	//End colouring code here. Begin consort defining code here.

	if (!empty($_POST['consortcolor'])) {
		if (empty($userrow['consort_name'])) {
			$newconsortname = $_POST['consortcolor'] . " " . $_POST['consorttype'];
			$mysqli->query("UPDATE `Players` SET `consort_name` = '" . $mysqli->real_escape_string($newconsortname) . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
			$userrow['consort_name'] = $mysqli->real_escape_string($newconsortname);
		} else
			echo "Two kinds of consorts on the same land?! You're not entirely sure they'll be able to get along...<br/>";
	}

	//End consort code here.
	if (!empty($colournew))
		echo "<!DOCTYPE html><html><head><style>favcolour{color: $userrow[colour];}</style></head><body>";
	if (empty($client))
		$client = "";
	echo "Username: $username <br/>";
	if (!empty($userrow['Class']) && !empty($userrow['Aspect'])) {
		$classresult = $mysqli->query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$userrow[Class]';");
		$classrow = $classresult->fetch_array();
		if ($classrow['activefactor'] > 100) {
			$activepassivestr = "(Active, $classrow[activefactor]%)";
		} else {
			$activepassivestr = "(Passive, $classrow[passivefactor]%)";
		}
		echo "Title: $userrow[Class] of $userrow[Aspect] $activepassivestr<br/>";
	} elseif (!empty($newclass) && !empty($newaspect)) {
		$classresult = $mysqli->query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$newclass';");
		$classrow = $classresult->fetch_array();
		if ($classrow['activefactor'] > 100) {
			$activepassivestr = "(Active, $classrow[activefactor]%)";
		} else {
			$activepassivestr = "(Passive, $classrow[passivefactor]%)";
		}
		echo "Title: $newclass of $newaspect $activepassivestr<br/>";
	} else {
		echo "Both your class and aspect must be accepted simultaneously.";
		echo '<form action="overview.php" method="post">Select class:<select name="class"> '; //Select a class
		$classes = $mysqli->query("SELECT * FROM Titles");
		$reachclass = true;
		while ($row = $classes->fetch_array()) {
			$classresult = $mysqli->query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$row[Class]';");
			$classrow = $classresult->fetch_array();
			if ($classrow['activefactor'] > 100) {
				$activepassivestr = "(Active, $classrow[activefactor]%)";
			} else {
				$activepassivestr = "(Passive, $classrow[passivefactor]%)";
			}
			if ($row['Class'] == "General")
				$reachclass = false;
			if ($reachclass)
				echo '<option value="' . $row['Class'] . '">' . $row['Class'] . ' ' . $activepassivestr . '</option>';
		}
		echo '</select><br/>';
		echo 'Select aspect:<select name="aspect"> '; //Select an aspect
		$aspects = $mysqli->query("SELECT * FROM Titles LIMIT 1;");
		$reachaspect = false;
		while ($col = $aspects->fetch_field()) {
			$aspect = $col->name;
			if ($aspect == "Breath")
				$reachaspect = true;
			if ($aspect == "General")
				$reachaspect = false;
			
			if ($reachaspect)
				echo '<option value="' . $aspect . '">' . $aspect . '</option>';
		}
		echo '</select><br/><input type="submit" value="Accept it." /> </form>';
	}
	if (!empty($dreamstatus)) {
		echo "Moon: $dreamstatus";
	} else {
		echo "Moon: $userrow[dreamer]";
	}
	if ($userrow['dreamer'] == "Unawakened" && empty($dreamstatus)) {
		echo '<form action="overview.php" method="post"><select name="dreamer"><option value="Prospit">Prospit</option><option value="Derse">Derse</option></select>';
		echo '<input type="submit" value="Awaken." /></form>';
	} else {
		echo "<br/>";
	}
	$exploresult = $mysqli->query("SELECT * FROM `Explore_" . $userrow['dreamingstatus'] . "` WHERE `Explore_" . $userrow['dreamingstatus'] . "`.`name` = '" . $userrow['exploration'] . "';");
	$explorow = $exploresult->fetch_array();
	if ((!empty($explorow['canleave']) && $explorow['canleave'] == 1) || $userrow['dreamingstatus'] == "Awake") {
		echo '<form action="dreamtransition.php" method="post"><input type="hidden" name="sleep" value="sleep" /><input type="submit" value="Sleep?" /></form><br/>';
		echo '<form action="wasteoftime.php" method="post"><input type="hidden" name="timewaster" value="timewaster" /><input type="submit" value="Sit around wasting time" /></form><br/>';
	} else {
		echo '<form action="explore.php" method="post"><input type="submit" value="Go exploring!" /></form><br/>';
	}
	if (!empty($userrow['colour']))
		echo "Color: <favcolour>$userrow[colour]</favcolour>";
	echo '<form action="overview.php" method="post"><select name="colour">';
	$colouresult = $mysqli->query("SELECT * FROM Colours");
	while ($colourow = $colouresult->fetch_array()) {
		echo '<option value="' . $colourow['Name'] . '">' . $colourow['Name'] . '</option>';
	}
	echo "</select><br/>";
	echo '<input type="submit" value="Select it!" /></form><br/>';
	echo 'Symbol:<br/><img src="/Images/symbols/' . $userrow['symbol'] . '"><br/><form action="overview.php" method="post">Choose an image: <select name="newimage">';
	$dir = "./Images/symbols";
	if (is_dir($dir)) {
		$dh = opendir($dir);
		$i = 0;
		while (($filename = readdir($dh)) !== false) {
			$symbols[$i] = $filename;
			$i++;
			if ($i > 1000)
				break;
		}
	}
	sort($symbols);
	$allsymbols = count($symbols);
	$i = 0;
	while ($i < $allsymbols) {
		if (strpos($symbols[$i], ".png") !== false && $symbols[$i] != "nobody.png") { //this is a png image that isn't nobody (completely blank image)
			$symname = str_replace(".png", "", $symbols[$i]);
			echo '<option value="' . $symbols[$i] . '">' . $symname . '</option>';
		}
		$i++;
	}
	echo '</select><input type="submit" value="Select it!" /></form><br/>';
	if (!$symbols)
		echo "I'm working on this right now dw<br/>";
	if (empty($userrow['consort_name'])) {
		echo 'Consorts: Your consorts are currently undefined. Define them below by choosing a color and species.<br/>';
		echo '<form action="overview.php" method="post"><select name="consortcolor">';
		$colouresult = $mysqli->query("SELECT * FROM Colours");
		while ($colourow = $colouresult->fetch_array()) {
			echo '<option value="' . $colourow['Name'] . '">' . $colourow['Name'] . '</option>';
		}
		echo '</select><select name="consorttype">';
		echo '<option value="Salamander">Salamander</option><option value="Turtle">Turtle</option><option value="Crocodile">Crocodile</option><option value="Iguana">Iguana</option>'; //consorts are hardcoded for now since there's only 4
		echo '</select><br/>';
		echo '<input type="submit" value="Populate my land!" /></form>';
	} else
		echo "Consorts: Your land is home to the " . $userrow['consort_name'] . ".<br/>";
	echo "Health Vial: ";
	if ($userrow['dreamingstatus'] == "Awake") {
		echo strval(floor(($userrow['Health_Vial'] / $userrow['Gel_Viscosity']) * 100)); //Computes % of max HP remaining.
		echo "%<br/>";
	} else {
		echo strval(floor(($userrow['Dream_Health_Vial'] / $userrow['Gel_Viscosity']) * 100)); //Computes % of max HP remaining.
		echo "%<br/>";
	}
	echo "Aspect Vial: ";
	echo strval(floor(($userrow['Aspect_Vial'] / $userrow['Gel_Viscosity']) * 100)); //Computes % of max Aspect remaining.
	echo "%<br/>";
	if (!empty($newstatus)) {
		$newstatus = str_replace("\'", "'", $newstatus);
		echo "Currently: $newstatus <br/>";
	} else {
		echo "Currently: $userrow[status] <br/>";
	}
	echo '<form action="overview.php" method="post">What are you doing? <input id="status" name="status" type="text" /> <input type="submit" value="Do it!" /></form>';
	echo "Session designation: $userrow[session_name] <br/>";
	$sessionpwresult = $mysqli->query("SELECT * FROM `Sessions` WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "';");
	$sessionpwrow = $sessionpwresult->fetch_array();
	echo "Session password: $sessionpwrow[password]<br/>";
	if ($userrow['dreamingstatus'] == "Awake") { //Only deal with sprite while awake.
		if (!empty($spritename)) {
			echo "Sprite name: $spritename";
		} else {
			echo "Sprite name: $userrow[sprite_name]";
		}
		echo '<form action="overview.php" method="post"><input id="spritename" name="spritename" type="text" />sprite <input type="submit" value="Rename it!" /></form>';
		if ($userrow['prototype_item_1'] == "") {
			if (!empty($itemone)) {
				echo "First prototyped item: $itemone <br/>";
			} else {
				echo '<form action="overview.php" method="post">First item: <input id="item1" name="item1" type="text" /><br/>';
				echo 'Prototyping strength: <input id="strength1" name="strength1" type="text" /><br/><input type="submit" value="Prototype it!" /></form>';
			}
		} else {
			echo "First prototyped item: $userrow[prototype_item_1]";
		}
		if ($userrow['prototype_item_2'] == "") {
			if (!empty($itemtwo)) {
				echo "<br/>Second prototyped item: $itemtwo <br/>";
			} else {
				if (!empty($itemone) || $userrow['prototype_item_1'] != "") { //Only allow second prototyping if first is done.
					echo '<form action="overview.php" method="post">Second item: <input id="item2" name="item2" type="text" /><br/>';
					echo 'Prototyping strength: <input id="strength2" name="strength2" type="text" /><br/><input type="submit" value="Prototype it!" /></form><br/>';
				}
			}
		} else {
			echo "<br/>Second prototyped item: $userrow[prototype_item_2] <br/>";
		}
		echo "NOTE - All prototyping strengths must be between -999 and 999 or they will fail!<br/>";
	}

	//begin chain-displaying code (replaces current player listing as it achieves the same effect)
	echo "Players in your session, organized by client ==&gt; server (if a connection exists):<br/>";
	$sessionmates = $mysqli->query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "' ;");
	$buddies = 0;
	while ($row = $sessionmates->fetch_array()) {
		if ($row['session_name'] == $userrow['session_name']) {
			//echo "$row[username] <br/>";
			$buddyname[$buddies] = $row['username'];
			$printed[$buddyname[$buddies]] = false;
			$buddies++;
		}
	}
	echo "Known server/client chains in your session:<br/>";
	$clientless = $mysqli->query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "' AND `Players`.`client_player` = '' ;");
	while ($currentrow = $clientless->fetch_array()) { //first show the chains of those without clients to ensure that chains aren't repeated
		$done = false;
		echo $currentrow['username'];
		$printed[$currentrow['username']] = true;
		$namebeingchecked = $currentrow['username'];
		while (!$done) {
			if (!empty($currentrow['server_player']) && $currentrow['server_player'] != $namebeingchecked) {
				echo " ==&gt; ";
				$currentresult = $mysqli->query("SELECT * FROM Players WHERE `Players`.`username` = '$currentrow[server_player]';");
				$currentrow = $currentresult->fetch_array();
				echo $currentrow['username'];
				$printed[$currentrow['username']] = true;
			} else {
				$done = true;
				echo "<br/>";
			}
		}
	}
	$currentrow = $userrow;
	$superdone = false;
	$done = false;
	if (!empty($userrow['client_player']) && !$printed[$userrow['client_player']]) {
		echo $userrow['client_player'] . " ==&gt; ";
		$printed[$userrow['client_player']] = true;
	}
	if (!$printed[$username]) {
		echo $username;
		$printed[$username] = true;
	}
	$check = 0;
	$namebeingchecked = $username;
	$printed[$username] = true;
	while (!$superdone) {
		while (!$done) {
			if (!empty($currentrow['server_player']) && $currentrow['server_player'] != $namebeingchecked && $printed[$currentrow['server_player']] == false) {
				echo " ==&gt; ";
				$currentresult = $mysqli->query("SELECT * FROM Players WHERE `Players`.`username` = '$currentrow[server_player]';");
				$currentrow = $currentresult->fetch_array();
				echo $currentrow['username'];
				$printed[$currentrow['username']] = true;
			} else {
				$done = true;
				echo "<br/>";
			}
		}
		while ($check < $buddies && $done) {
			if (!$printed[$buddyname[$check]]) { //see if this person was already listed as part of a chain
				$namebeingchecked = $buddyname[$check];
				$currentresult = $mysqli->query("SELECT * FROM Players WHERE `Players`.`username` = '" . $buddyname[$check] . "';");
				$currentrow = $currentresult->fetch_array();
				echo $currentrow['username'];
				$printed[$currentrow['username']] = true;
				$done = false; //break this while loop and go print the chain of this person
			}
			$check++;
		}
		if ($check == $buddies)
			$superdone = true; //that's all of 'em!
	}
	//end chain-displaying code

	$compugood = true;
	if ($userrow['enemydata'] != "" || $userrow['aiding'] != "") {
		if ($userrow['hascomputer'] < 3) {
			if ($compugood == true)
				echo "You don't have a hands-free computer equipped, so you can't check in on your client/server during strife.<br/>";
			$compugood = false;
		}
	}
	if ($userrow['indungeon'] != 0 && $userrow['hascomputer'] < 2) {
		if ($compugood == true)
			echo "You don't have a portable computer in your inventory, so you can't check in on your client/server while away from home.<br/>";
		$compugood = false;
	}
	if ($userrow['hascomputer'] == 0) {
		if ($compugood == true)
			echo "You need a computer in storage or your inventory to check in on your client and server.<br/>";
		$compugood = false;
	}

	if ($compugood) {
		if ($userrow['client_player'] != "")
			echo "<br/>Client player: $userrow[client_player]<br/>";
		if (!empty($client))
			echo "<br/>Client player: $client<br/>";
		//house-building and client connection are done from sburbserver.php now
		if ($userrow['server_player'] == "") {
			echo "Server player has not entered the Medium yet or has not registered their connection. Your status report is unable to track them.<br/>";
		} else {
			echo "Server player: $userrow[server_player] <br/>";
		}

		echo "Grist expended expanding your dwelling: $userrow[house_build_grist] <br/>";
	}
	echo "House gates accessible on your Land: ";
	$gates = 0;
	$i = 1;
	$gateresult = $mysqli->query("SELECT * FROM Gates");
	$gaterow = $gateresult->fetch_array(); //Gates only has one row.
	while ($i <= 7) {
		$gatestr = "gate" . strval($i);
		if ($gaterow[$gatestr] <= $userrow['house_build_grist']) {
			$gates++;
		} else {
			$i = 7; //We are done.
		}
		$i++;
	}
	echo strval($gates) . "<br/>";
	if (($gates == 7 || $fly) && $userrow['dreamingstatus'] == "Awake" && $userrow['denizendown'] == 0 && !empty($userrow['Aspect'])) {
		if ($userrow['enemydata'] == "" && $userrow['aiding'] == "") {
			$denizenresult = $mysqli->query("SELECT * FROM Titles WHERE `Titles`.`Class` = 'Denizen';");
			$denizenrow = $denizenresult->fetch_array();
			echo '<form action="strifebegin.php" method="post">';
			echo '<input type="hidden" name="gristtype" value="None">'; //Gristless enemies.
			echo '<input type="hidden" name="noassist" value="noassist">';
			echo '<input type="hidden" name="stripbuffs" value="stripbuffs">';
			echo '<input type="hidden" name="noprevious" value="noprevious">';
			echo '<input type="hidden" name="land" value="LASTFOUGHT">';
			$enemystr = "enemy1";
			$griststr = "grist1";
			echo '<input type="hidden" name="' . $griststr . '" value="None">'; //Gristless enemies.
			echo '<input type="hidden" name="' . $enemystr . '" value="' . $denizenrow[$userrow['Aspect']] . '">';
			echo '<input type="submit" value="Face ' . $denizenrow[$userrow['Aspect']] . '" /></form>';
		}
	}
	echo "<br/>The more players in your client/server chain have built up to their gates, the more Lands you can access.";
}

require_once "footer.php";
