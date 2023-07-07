<?php
function logDebugMessage(string $debugmsg)
{
	$time = time();
	$debugmsg = "($time) $debugmsg <br/>\n";
	query("UPDATE System SET debuglog = CONCAT(debuglog, :msg) WHERE 1", ['msg' => $debugmsg]);
}

function logModMessage(string $debugmsg, string $id)
{
	$time = time();
	if ($id != 0)
		$debugmsg = "<a href='https://www.overseerreboot.xyz/submissions.php?view=$id'>(ID: $id @ $time)</a> $debugmsg <br/>\n";
	else
		$debugmsg = "(ID: N/A @ $time) $debugmsg <br/>\n";
	query("UPDATE System SET modlog = CONCAT(modlog, :msg) WHERE 1", ['msg' => $debugmsg]);
}

/**
 * @return bool Whether the user had enough encounters.
 */
function chargeEncounters(array $userrow, int $encounters, int $effectticks)
{
	if ($userrow['encounters'] >= $encounters) {
		$newenc = $userrow['encounters'] -= $encounters;
		$encspent = $userrow['encountersspent'] += $encounters;

		query("UPDATE Players SET encounters = :encounters, encountersspent = :encountersspent WHERE username = :username", [
			'encounters' => $newenc,
			'encountersspent' => $encspent,
			'username' => $userrow['username'],
		]);

		if ($effectticks > 0) {
			$statusarray = explode("|", $userrow['permstatus']);
			$i = 0;
			while (!empty($statusarray[$i])) {
				$currentarray = explode(":", $statusarray[$i]);
				if ($currentarray[0] != "ALLY") //allies are always permanent until their loyalty drops to 0, but that's handled elsewhere
				{
					$duration = explode(".", $currentarray[0]);
					$ticks = intval($duration[1]);
					if ($ticks != -1) {
						if ($ticks > $effectticks) {
							$ticks -= $effectticks;
							$statusarray[$i] = str_replace($currentarray[0], $duration[0] . "." . strval($ticks), $statusarray[$i]); // JD: There was a compiler warning here, the 3rd argument might be wrong
							if (!empty($duration[2]))
								$statusarray[$i] .= "." . $duration[2]; //I doubt wearables will have durations but just in case
						} else {
							$statusarray[$i] = ""; //this effect wears off
						}
					}
				}
				$i++;
			}
			$newstatus = implode("|", $statusarray);
			$newstatus = preg_replace("/\\|{2,}/", "|", $newstatus); //eliminate all blanks
			if ($newstatus == "|")
				$newstatus = "";
			if ($newstatus != $userrow['permstatus'])
				query("UPDATE Players SET permstatus = :permstatus WHERE username = :username", [
					'permstatus' => $newstatus,
					'username' => $userrow['username'],
				]);
		}
		return true;
	} else
		return false;
}

/**
 * @return int The actual amount of rungs ascended. 0 probably means the player is at the top of the echeladder.
 */
function climbEcheladder(array $userrow, int $rungups)
{
	$rungs = 612 - $userrow['Echeladder'];
	if ($rungs > $rungups)
		$rungs = $rungups;

	$hpup = 0; //Paranoia: Handle weird Echeladder values.
	$boondollars = 0;

	for ($rungcounter = $rungs - 1; $rungcounter >= 0; $rungcounter--) {
		$currentRung = $userrow['Echeladder'] + $rungcounter;

		$boondollars += $currentRung * 55;

		if ($currentRung == 1)
			$hpup += 5; //First rung: +5
		elseif ($currentRung <= 5)
			$hpup += 10; //Rungs 2, 3, 4, and 5: +10
		else
			$hpup += 15; //Most rungs: +15
	}

	if ($rungs > 0) {
		query("UPDATE Players SET Echeladder = :echeladder, Boondollars = :boondollars, Gel_Viscosity = :gelViscosity, Health_Vial = :healthVial, Dream_Health_Vial = :dreamHealthVial, Aspect_Vial = :aspectVial WHERE username = :username LIMIT 1 ;", [
			'echeladder' => $userrow['Echeladder'] + $rungs,
			'boondollars' => $userrow['Boondollars'] + $boondollars,
			'gelViscosity' => $userrow['Gel_Viscosity'] + $hpup,
			'healthVial' => $userrow['Health_Vial'] + $hpup,
			'dreamHealthVial' => $userrow['Dream_Health_Vial'] + $hpup,
			'aspectVial' => $userrow['Aspect_Vial'] + $hpup,
			'username' => $userrow['username'],
		]);

		$echestr = "rung" . strval($userrow['Echeladder'] + $rungs);
		$echerow = fetchOne("SELECT :echestr FROM Echeladders WHERE username = :username", ['echestr' => $echestr, 'username' => $userrow['username']]);
		if (!empty($echerow[$echestr])) {
			if ($rungs > 1)
				echo "<br/>You scramble madly up your Echeladder, coming to rest on rung: $echerow[$echestr]!";
			else
				echo "<br/>You ascend to rung: $echerow[$echestr]!";
		}

		foreach (fetchAll("SELECT * FROM Abilities WHERE Aspect IN (:aspect, 'All') AND Class IN (:class, 'All') AND Rungreq BETWEEN :rungReqLower AND :rungReqUpper AND Godtierreq = 0 ORDER BY Rungreq DESC;", ['aspect' => $userrow['Aspect'], 'class' => $userrow['Class'], 'rungReqLower' => $userrow['Echeladder'] + 1, 'rungReqUpper' => $userrow['Echeladder'] + $rungs,]) as $levelerability)
			echo "<br/>You obtain new roletech: Lv. $levelerability[Rungreq] $levelerability[Name]!";

		if ($userrow['Echeladder'] + $rungs == 612)
			echo "<br/>You have at long last reached the top of your Echeladder!";

		echo "<br/>";
		echo "Gel Viscosity: +$hpup!<br/>";
		echo "Boondollars earned: $boondollars!<br/>";
	}
	return $rungs;
}

/**
 * This will register which abilities the player has in $abilities. The standard check is if (!empty($abilities[ID of ability to be checked for])).
 */
function loadAbilities(array $userrow)
{
	$abilities = ["Null ability. No, not void."];
	foreach (fetchAll("SELECT ID, Usagestr FROM Abilities WHERE Aspect IN (:aspect,'All') AND Class IN (:class,'All') AND Rungreq BETWEEN :rungReqLower AND :rungReqUpper AND Godtierreq BETWEEN :godTierReqLower AND :godTierReqUpper ORDER BY Rungreq DESC;", ['aspect' => $userrow['Aspect'], 'class' => $userrow['Class'], 'rungReqLower' => 0, 'rungReqUpper' => $userrow['Echeladder'], 'godTierReqLower' => 0, 'godTierReqUpper' => $userrow['Godtier'],]) as $temp) {
		//Create entry in abilities array for the ability the player has.
		//We save the usage message in, so pulling the usage message is as simple as pulling the correct element out of the abilities array via the ID.
		//Note that an ability with an empty usage message will be unusable since the empty function will spit empty at you.
		$abilities[$temp['ID']] = $temp['Usagestr'];
	}

	$currentstatus = $userrow['strifestatus'] . '|' . $userrow['permstatus'];
	$currentstatus = preg_replace("/\\|{2,}/", '|', $currentstatus); //eliminate all blanks

	//Check for any instances of HASABILITY
	if (!empty($currentstatus)) {
		foreach (explode("|", $currentstatus) as $status) {
			$statusarg = explode(":", $status);
			if ($statusarg[0] == "HASABILITY") //This is an ability the player possesses.
			{
				$abilityid = intval($statusarg[1]);
				$ability = fetchOne("SELECT ID, Usagestr FROM Abilities WHERE ID = :id LIMIT 1;", ['id' => $abilityid]);
				$abilities[$ability['ID']] = $ability['Usagestr'];
			}
		}
	}

	return $abilities;
}

function convertHybrid(array $workrow, bool $isbodygear) //when wearable defense is calculated, it will go here if it's a hybrid (both a weapon and wearable) and cut the power down
{
	$bonusrow['abstain'] = $workrow['abstain'];
	$bonusrow['abjure'] = $workrow['abjure'];
	$bonusrow['accuse'] = $workrow['accuse'];
	$bonusrow['abuse'] = $workrow['abuse'];
	$bonusrow['aggrieve'] = $workrow['aggrieve'];
	$bonusrow['aggress'] = $workrow['aggress'];
	$bonusrow['assail'] = $workrow['assail'];
	$bonusrow['assault'] = $workrow['assault'];

	$hybridmod = specialArray($workrow['effects'], "HYBRIDMOD");
	$divisor = 30;
	if ($hybridmod[0] == "HYBRIDMOD")
		$divisor *= $hybridmod[1] / 100;
	if ($isbodygear)
		$divisor /= 3;
	$workrow['power'] = ceil($workrow['power'] / $divisor);

	$bestbonus = max($bonusrow);

	if ($bestbonus == 0)
		$bestname = "none";
	elseif ($bonusrow['abstain'] == $bestbonus)
		$bestname = "abstain";
	elseif ($bonusrow['abjure'] == $bestbonus)
		$bestname = "abjure";
	elseif ($bonusrow['accuse'] == $bestbonus)
		$bestname = "accuse";
	elseif ($bonusrow['abuse'] == $bestbonus)
		$bestname = "abuse";
	elseif ($bonusrow['aggrieve'] == $bestbonus)
		$bestname = "aggrieve";
	elseif ($bonusrow['aggress'] == $bestbonus)
		$bestname = "aggress";
	elseif ($bonusrow['assail'] == $bestbonus)
		$bestname = "assail";
	elseif ($bonusrow['assault'] == $bestbonus)
		$bestname = "assault";

	if ($bestname == "abstain" || $workrow['abstain'] < 0)
		$workrow['abstain'] = ceil($workrow['abstain'] / $divisor);
	else
		$workrow['abstain'] = 0;
	if ($bestname == "abjure" || $workrow['abjure'] < 0)
		$workrow['abjure'] = ceil($workrow['abjure'] / $divisor);
	else
		$workrow['abjure'] = 0;
	if ($bestname == "accuse" || $workrow['accuse'] < 0)
		$workrow['accuse'] = ceil($workrow['accuse'] / $divisor);
	else
		$workrow['accuse'] = 0;
	if ($bestname == "abuse" || $workrow['abuse'] < 0)
		$workrow['abuse'] = ceil($workrow['abuse'] / $divisor);
	else
		$workrow['abuse'] = 0;
	if ($bestname == "aggrieve" || $workrow['aggrieve'] < 0)
		$workrow['aggrieve'] = ceil($workrow['aggrieve'] / $divisor);
	else
		$workrow['aggrieve'] = 0;
	if ($bestname == "aggress" || $workrow['aggress'] < 0)
		$workrow['aggress'] = ceil($workrow['aggress'] / $divisor);
	else
		$workrow['aggress'] = 0;
	if ($bestname == "assail" || $workrow['assail'] < 0)
		$workrow['assail'] = ceil($workrow['assail'] / $divisor);
	else
		$workrow['assail'] = 0;
	if ($bestname == "assault" || $workrow['assault'] < 0)
		$workrow['assault'] = ceil($workrow['assault'] / $divisor);
	else
		$workrow['assault'] = 0;

	return $workrow;
}

/**
 * @param int $result Result can have 3 values: 0 is victory, 1 is defeat, 2 is abscond.
 * The latter two will attempt to summon another aide.
 * There is a special value -1 which will eject EVERYONE from strife as if they absconded, and doesn't affect dungeon strife or exploration.
 */
function terminateStrife(array $userrow, int $result)
{
	$maxEnemies = 50;
	$username = $userrow['username'];

	$sessioname = str_replace("'", "''", $userrow['session_name']); //Add escape characters so we can find session correctly in database.
	$sessionmates = fetchAll("SELECT * FROM Players WHERE session_name = :sessionName", ['sessionName' => $sessioname]);

	if ($result > 0) //player was defeated (1) or player absconded/was ejected (2)
	{
		$playerWasDefeated = $result == 1;
		$playerAbsconded = $result == 2;

		$newfighter = "";
		$aides = 0;
		foreach ($sessionmates as $row)
			if ($row['aiding'] == $username) //Aiding character.
				$aides += 1;
		foreach ($sessionmates as $row) {
			if ($row['aiding'] == $username) //Aiding character.
			{
				if ($newfighter == "" && rand(1, $aides) == 1) //Character has been selected to be the next target.
					$newfighter = $row['username'];
				$aides -= 1; //One aide removed.
			}
		}

		foreach ($sessionmates as $row) {
			if ($row['aiding'] == $username) //Aiding character.
			{
				if ($row['username'] == $newfighter) //Character needs to be given this encounter.
				{
					query("UPDATE Players SET aiding = '' WHERE username = :username LIMIT 1 ;", ['username' => $row['username']]);
					for ($p = 1; $p <= $maxEnemies; $p++) {
						$aidenemystr = "enemy" . strval($p) . "name";
						$aidpowerstr = "enemy" . strval($p) . "power";
						$aidmaxpowerstr = "enemy" . strval($p) . "maxpower";
						$aidhealthstr = "enemy" . strval($p) . "health";
						$aidmaxhealthstr = "enemy" . strval($p) . "maxhealth";
						$aiddescstr = "enemy" . strval($p) . "desc"; //Need this to check for nulls.
						$aidcategorystr = "enemy" . strval($p) . "category";
						$row[$aidenemystr] = $userrow[$aidenemystr];
						$row[$aidpowerstr] = $userrow[$aidpowerstr];
						$row[$aidmaxpowerstr] = $userrow[$aidmaxpowerstr];
						$row[$aidhealthstr] = $userrow[$aidhealthstr];
						$row[$aidmaxhealthstr] = $userrow[$aidmaxhealthstr];
						$row[$aiddescstr] = $userrow[$aiddescstr];
						$row[$aidcategorystr] = $userrow[$aidcategorystr];
						writeEnemydata($row);
					}
				} else {
					query("UPDATE Players SET aiding = :aiding WHERE username = :username LIMIT 1 ;", ['aiding' => $newfighter, 'username' => $row['username']]); //Player assists new combatant.
				}
			}
		}

		if ($playerWasDefeated) {
			if ($userrow['dreamingstatus'] == "Awake")
				$downstr = "down";
			else
				$downstr = "dreamdown";

			query("UPDATE Players SET :down = 1 WHERE username = :username LIMIT 1 ;", ['down' => $downstr, 'username' => $username]);
			$userrow[$downstr] = 1; //Makes messages appear.
		}

		if (!empty($userrow['strifesuccessexplore']) && !empty($userrow['strifefailureexplore'])) //User exploring!
		{
			query("UPDATE Players SET exploration = :exploration, strifesuccessexplore = '', strifefailureexplore = '', strifeabscondexplore = '' WHERE username = :username LIMIT 1 ;", [
				'exploration' => $userrow[$playerAbsconded ? 'strifeabscondexplore' : 'strifefailureexplore'],
				'username' => $username,
			]);
			echo ' <a href="explore.php">Continue exploring</a><br/>';
		}

		if ($userrow['dungeonstrife'] == 2) //User strifing in a dungeon
		{
			query("UPDATE Players SET dungeonstrife = 1 WHERE username = :username LIMIT 1;", ['username' => $username]);
			echo "You flee back the way you came.<br/>";
			echo "<a href='dungeons.php'>==&gt;</a><br/>";
		} elseif ($userrow['dungeonstrife'] == 4) //User fighting dungeon guardian
		{
			query("UPDATE Players SET dungeonstrife = 3 WHERE username = :username LIMIT 1;", ['username' => $username]);
			echo "You flee from the guardian. Perhaps you should prepare a bit more before trying to enter the dungeon...<br/>";
			echo "<a href='dungeons.php#display'>==&gt;</a><br/>";
		} elseif ($userrow['dungeonstrife'] == 6) //User strifing for a quest
		{
			query("UPDATE Players SET dungeonstrife = 5 WHERE username = :username LIMIT 1;", ['username' => $username]);
			$userrow['dungeonstrife'] = 5;

			if ($playerAbsconded) {
				echo "You abscond, but the quest is still on for you to try again when you are better prepared...<br/>";
			} else {
				$qrow = fetchOne("SELECT context FROM Consort_Dialogue WHERE ID = :id", ['id' => $userrow['currentquest']]);
				if (strpos($qrow['context'], "questrescue") !== false)
					echo "This quest's challenge has gotten the better of you! It looks as though you will not have a second chance, unfortunately...<br/>";
				else
					echo "You have failed the quest! You should come back and try again after you're rested up and fully prepared.<br/>";
			}

			echo "<a href='consortquests.php'>==&gt;</a><br/>";
		}
	} elseif ($result == 0) //player is victorious!
	{
		if (!empty($userrow['strifesuccessexplore']) && !empty($userrow['strifefailureexplore'])) { //User exploring!
			query("UPDATE Players SET exploration = :exploration, strifesuccessexplore = '', strifefailureexplore = '', strifeabscondexplore = '' WHERE username = :username LIMIT 1 ;", [
				'exploration' => $userrow['strifesuccessexplore'],
				'username' => $username,
			]);
			echo ' <a href="explore.php">Continue exploring</a><br/>';
		}

		if ($userrow['dungeonstrife'] == 2) //User strifing in a dungeon
		{
			echo "<br/>You have successfully defeated the dungeon foes!<br/>";
			echo "<a href='dungeons.php#display'>==&gt;</a><br/>";
		} elseif ($userrow['dungeonstrife'] == 4) //User strifing against dungeon guardian
		{
			echo "<br/>You have successfully defeated the dungeon guardian! The entrance lies before you...<br/>";
			echo "<a href='dungeons.php#display'>==&gt;</a><br/>";
		} elseif ($userrow['dungeonstrife'] == 6) //User strifing for a quest
		{
			$qrow = fetchOne("SELECT context FROM Consort_Dialogue WHERE ID = :id", ['id' => $userrow['currentquest']]);
			if (strpos($qrow['context'], "questrescue") !== false) //whoops, you weren't supposed to kill them all!
			{
				echo "<br/>...however, defeating all the enemies has caused you to fail the quest!<br/>";
				query("UPDATE Players SET dungeonstrife = 5 WHERE username = :username LIMIT 1;", ['username' => $username]);
				$userrow['dungeonstrife'] = 5;
			} else
				echo "<br/>You have successfully cleared the quest! You should talk to the quest giver and claim your reward.<br/>";
			echo "<a href='consortquests.php'>==&gt;</a><br/>";
		} else {
			echo '<br/><a href="strife.php">Strife again</a><br/>';
		}
	} else //special case
	{
		/* // FIXME: JD: These aren't part of the global variable, they aren't getting used...
			  if ($userrow['enemydata'] == "")
				  $exstr = " AND aiding = '" . $username . "'";
			  else
				  $exstr = " AND (username = '" . $userrow['aiding'] . "' OR aiding = '" . $userrow['aiding'] . "')";
			  */

		foreach ($sessionmates as $row)
			query("UPDATE Players SET enemydata = '', aiding = '' WHERE username = :username", ['username' => $row['username']]);
	}

	$maxEnemies = 50;
	for ($i = 1; $i <= $maxEnemies; $i++)
		$userrow['enemy' . strval($i) . 'name'] = "";

	//Power boosts wear off.
	query("UPDATE Players SET
		powerboost = 0,
		offenseboost = 0,
		defenseboost = 0,
		temppowerboost = 0,
		tempoffenseboost = 0,
		tempdefenseboost = 0,
		Brief_Luck = 0,
		invulnerability = 0,
		buffstrip = 0,
		noassist = 0,
		cantabscond = 0,
		motifcounter = 0,
		combatconsume = 0,
		strifestatus = '',
		enemydata = ''
		WHERE username = :username LIMIT 1 ;", ['username' => $username]);
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
	$userrow['enemydata'] = "";

	return $userrow; //in case something changed and the megaquery tries to reverse it
}

function getRandeffect()
{
	$r = rand(1, 22);
	switch ($r) {
		case 1:
			return "TIMESTOP";
		case 2:
			return "POISON";
		case 3:
			return "WATERYGEL";
		case 4:
			return "SHRUNK";
		case 5:
			return "LOCKDOWN";
		case 6:
			return "CHARMED";
		case 7:
			return "LIFESTEAL";
		case 8:
			return "SOULSTEAL";
		case 9:
			return "MISFORTUNE";
		case 10:
			return "BLEEDING";
		case 11:
			return "HOPELESS";
		case 12:
			return "DISORIENTED";
		case 13:
			return "DISTRACTED";
		case 14:
			return "ENRAGED";
		case 15:
			return "MELLOW";
		case 16:
			return "KNOCKDOWN";
		case 17:
			return "GLITCHED";
		case 18:
			return "GLITCHY"; // lol
		case 19:
			return "BURNING";
		case 20:
			return "FREEZING";
		case 21:
			return "SMITE";
		case 22:
			return "RECOIL";
		default:
			return "GLITCHY"; //bugged = bugged
	}
}

function wearableAffinity($resistances, $aspect, $effects)
{
	if (strpos($effects, "AFFINITY") !== false) {
		$tag = explode("|", $effects);
		$i = 0;
		while (!empty($tag[$i])) {
			$arg = explode(":", $tag[$i]);
			if ($arg[0] == "AFFINITY") {
				$affadd = $arg[2];
				if ($arg[1] != $aspect)
					$affadd = floor($affadd * 0.8);
				$resistances[$arg[1]] += $affadd;
				if ($resistances[$arg[1]] > 100)
					$resistances[$arg[1]] = 100;
			}
			$i++;
		}
	}
	return $resistances;
}

function aspectDamage($resistances, $aspect, $damage, $resfactor = 1)
{
	$resfactor *= 100;
	$damage = $damage * ($resfactor - $resistances[$aspect]) / $resfactor;
	return ceil($damage);
}

/**
 * Add escape characters so we can find item correctly in database.
 */
function escapeItemName(string $itemName)
{
	return str_replace("'", "\\\\''", $itemName);
}

/**
 * Remove escape characters.
 */
function unescapeItemName(string $itemNameEscaped)
{
	return str_replace("\\", "", $itemNameEscaped);
}

function getItemPower($userrow, $invSlot)
{
	if (empty($userrow[$invSlot]))
		return 0;

	$itemName = $userrow[$invSlot];
	$itemNameEscaped = escapeItemName($itemName);
	foreach (fetchAll("SELECT * FROM Captchalogue WHERE `name` = '$itemNameEscaped'") as $row) {
		$itemResultNameEscaped = $row['name'];
		$itemResultName = unescapeItemName($itemResultNameEscaped);
		if ($itemResultName == $itemName)
			return $row['power'];
	}
	return 0;
}

/**
 * Document grist types now so we don't have to do it later
 */
function initGrists()
{
	$reachgrist = false;
	$totalgrists = 0;
	foreach (fetchColumns('Captchalogue') as $gristcost)
	{
		$gristtype = substr($gristcost, 0, -5);

		if ($gristcost == "Build_Grist_Cost") //Reached the start of the grists.
		{
			$reachgrist = true;
		}
		elseif ($gristcost == "End_Of_Grists") //Reached the end of the grists.
		{
			$reachgrist = false;
			break;
		}

		if ($reachgrist) {
			$gristname[$totalgrists] = $gristtype;
			$totalgrists++;
		}
	}
	return $gristname;
}
