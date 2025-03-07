<?php

function parseEnemydata($userrow)
{
	$enemies = !empty($userrow['enemydata']) ? explode("|", $userrow['enemydata']) : [];
	//$allenemies = count($enemies);
	//if ($allenemies > 50)
	$allenemies = 50;
	$actualenemies = $allenemies;
	for ($i = 0; $i < $allenemies; $i++) {
		if (!empty($enemies[$i])) {
			$thisenemy = explode(":", $enemies[$i]);
			$enstr = 'enemy' . strval($i + 1);
			$userrow[$enstr . 'name'] = $thisenemy[0];
			$userrow[$enstr . 'power'] = intval($thisenemy[1]);
			$userrow[$enstr . 'maxpower'] = intval($thisenemy[2]);
			$userrow[$enstr . 'health'] = intval($thisenemy[3]);
			$userrow[$enstr . 'maxhealth'] = intval($thisenemy[4]);
			$thisenemy[5] = str_replace("THIS IS A LINE", "|", $thisenemy[5]);
			$thisenemy[5] = str_replace("THIS IS A COLON", ":", $thisenemy[5]);
			$userrow[$enstr . 'desc'] = $thisenemy[5];
			$userrow[$enstr . 'category'] = $thisenemy[6];
		} else {
			$actualenemies--; //only the last entries should be empty if anything, hopefully this doesn't cause issues?
			$enstr = 'enemy' . strval($i + 1);
			$userrow[$enstr . 'name'] = "";
		}
	}
	//$userrow['maxenemies'] = $actualenemies; //"maxenemies" doesn't exist as a user field, but it's inserted into the userrow so that the function can return it. It's also for some reason causing a bug that prevents more than one enemy from existing, so it's commented out for now.
	return $userrow;
}

function writeEnemydata(array $userrow)
{
	//echo "begin enemy data write<br/>";
	global $mysqli;
	$endatastr = "";
	if (empty($userrow['maxenemies']))
		$userrow['maxenemies'] = 50;
	for ($i = 0; $i < $userrow['maxenemies']; $i++) {
		$enstr = 'enemy' . strval($i + 1);
		//echo $enstr . ":";
		if (!empty($userrow[$enstr . 'name'])) { //name will be blanked when enemy is defeated, so we'll blank all of its stats. All other enemies will shift down a slot.
			$endatastr .= $userrow[$enstr . 'name'] . ":";
			$endatastr .= strval($userrow[$enstr . 'power']) . ":";
			$endatastr .= strval($userrow[$enstr . 'maxpower']) . ":";
			$endatastr .= strval($userrow[$enstr . 'health']) . ":";
			$endatastr .= strval($userrow[$enstr . 'maxhealth']) . ":";
			$userrow[$enstr . 'desc'] = str_replace("|", "THIS IS A LINE", $userrow[$enstr . 'desc']);
			$userrow[$enstr . 'desc'] = str_replace(":", "THIS IS A COLON", $userrow[$enstr . 'desc']);
			$endatastr .= $userrow[$enstr . 'desc'] . ":";
			$endatastr .= $userrow[$enstr . 'category'];
			$endatastr .= "|";
			//echo $endatastr . "<br/>";
		}
	}
	$endatastr = $mysqli->real_escape_string($endatastr); //yeeeeah
	//echo "final countdown: " . "UPDATE `Players` SET `enemydata` = '$endatastr' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;";
	$mysqli->query("UPDATE `Players` SET `enemydata` = '$endatastr' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;");
}

function refreshEnemydata($userrow)
{ //a necessary function for functions like generateEnemy, so that they don't continually overwrite the same slot
	global $mysqli;
	$dataresult = $mysqli->query("SELECT `enemydata` FROM `Players` WHERE `Players`.`username` = '" . $userrow['username'] . "'");
	$row = $dataresult->fetch_array();
	$userrow['enemydata'] = $row['enemydata'];
	$userrow = parseEnemydata($userrow);
	return $userrow;
}

function endStrife($userrow)
{ //a quick function to reset all strife values and ensure they don't return via megaquery
	global $mysqli;
	$mysqli->query("UPDATE `Players` SET `powerboost` = 0, `offenseboost` = 0, `defenseboost` = 0, `temppowerboost` = 0,
		`tempoffenseboost` = 0, `tempdefenseboost` = 0, `Brief_Luck` = 0, `invulnerability` = 0, `buffstrip` = 0, `noassist` = 0,
		`cantabscond` = 0, `motifcounter` = 0, `strifestatus` = '', `sessionbossengaged` = 1 WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1 ;"); //Power boosts wear off.
	$userrow['powerboost'] = 0;
	$userrow['offenseboost'] = 0;
	$userrow['defenseboost'] = 0;
	$userrow['temppowerboost'] = 0;
	$userrow['tempoffenseboost'] = 0;
	$userrow['tempdefenseboost'] = 0;
	$userrow['Brief_Luck'] = 0;
	$userrow['invulnerability'] = 0;
	$userrow['buffstrip'] = 0;
	$userrow['noassist'] = 0;
	$userrow['cantabscond'] = 0;
	$userrow['motifcounter'] = 0;
	$userrow['strifestatus'] = "";
	$userrow['sessionbossengaged'] = 0; //Just in case.
	return $userrow;
}

function freeSpecibi($userabs, $userslots, $echothem)
{
	$abs = explode("|", $userabs);
	$i = 0;
	$hasabs = count($abs);
	$free = $userslots;
	while ($i < $hasabs) {
		if (!empty($abs[$i])) {
			$free--;
			if ($echothem)
				echo $abs[$i] . "<br/>";
		}
		$i++;
	}
	return $free;
}

function addSpecibus($userrow, $newabs)
{ //this function assumes you've already checked if the user has a free slot because reasons
	global $mysqli;
	$abs = $userrow['abstratus1'];
	if (substr($abs, 0, -1) != "|" && !empty($abs)) {
		$abs .= "|";
	}
	$abs .= $newabs;
	$mysqli->query("UPDATE `Players` SET `abstratus1` = '$abs' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;");
	$userrow['abstratus1'] = $abs;
	return $userrow;
}

function matchesAbstratus($userabs, $abstr)
{
	if (empty($userabs) || empty($abstr)) return false;

	$itemabs = explode(", ", $abstr);
	$abs = explode("|", $userabs);
	$totalitem = count($itemabs);
	$totaluser = count($abs);
	$i = 0;
	$j = 0;
	while ($i < $totalitem) {
		$j = 0;
		while ($j < $totaluser) {
			if ($itemabs[$i] == $abs[$j])
				return true; //found a matching abstratus, we're done here
			else
				$j++;
		}
		$i++;
	}
	return false;
}

function parseLastfought($userrow)
{
	$enemies = explode("|", $userrow['oldenemydata']);
	$allenemies = count($enemies);
	$actualenemies = $allenemies;
	for ($i = 0; $i < $allenemies; $i++) {
		if (!empty($enemies[$i])) {
			$thisenemy = explode(":", $enemies[$i]);
			$enstr = strval($i + 1);
			$userrow['oldenemy' . $enstr] = $thisenemy[0];
			$userrow['oldgrist' . $enstr] = $thisenemy[1];
			$userrow['olddreamenemy' . $enstr] = $thisenemy[2];
		} else {
			$actualenemies--; //only the last entry should be empty if anything, hopefully this doesn't cause issues?
		}
	}
	$userrow['lastenemies'] = $actualenemies; //"lastenemies" doesn't exist as a user field, but it's inserted into the userrow so that the function can return it
	return $userrow;
}

function writeLastfought($userrow)
{
	//echo "writing last fought!<br/>";
	global $mysqli;
	if (empty($userrow['lastenemies']))
		$userrow['lastenemies'] = 50;
	$endatastr = "";
	for ($i = 0; $i < $userrow['lastenemies']; $i++) {
		$enstr = strval($i + 1);
		//echo "checking slot $enstr<br/>";
		if (!empty($userrow['oldenemy' . $enstr]) || !empty($userrow['olddreamenemy' . $enstr])) { //name will be blanked when enemy is defeated, so we'll blank all of its stats
			//echo "data found: ";
			$endatastr .= (!empty($userrow['oldenemy' . $enstr]) ? $userrow['oldenemy' . $enstr] : '') . ":";
			$endatastr .= (!empty($userrow['oldgrist' . $enstr]) ? $userrow['oldgrist' . $enstr] : "None") . ":";
			$endatastr .= (!empty($userrow['olddreamenemy' . $enstr]) ? $userrow['olddreamenemy' . $enstr] : '');
			$endatastr .= "|";
			//echo $endatastr . "<br/>";
		}
	}
	//echo "final countdown: $endatastr<br/>";
	$mysqli->query("UPDATE `Players` SET `oldenemydata` = '$endatastr' WHERE `Players`.`username` = '" . $userrow['username'] . "' LIMIT 1;");
}

function hydraSplitChance($abs)
{
	switch ($abs) {
		case "bladekind":
		case "chainsawkind":
			return 95;
		case "axekind":
		case "knifekind":
		case "scythekind":
			return 80;
		case "polearmkind":
		case "ninjakind":
		case "razorkind":
			return 60;
		case "boomerangkind":
		case "laserkind":
		case "sicklekind":
			return 50;
		case "scissorkind":
		case "shearkind":
			return 40;
		case "hammerkind":
		case "clubkind":
		case "flamethrowerkind":
		case "pankind":
		case "rockkind":
		case "staffkind":
		case "yoyokind":
			return 10;
		case "bunnykind":
		case "cakekind":
		case "fabrickind":
		case "fancysantakind":
		case "inflatablekind":
			return 1;
		case "metakind":
		case "pillowkind":
			return 0;
		default:
			return 25;
	}
}

?>