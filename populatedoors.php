<?php
require_once 'header.php';

if (empty($_SESSION['username'])) {
	echo "Log in to do stuff bro<br/>";
} elseif ($userrow['session_name'] != "Developers") {
	echo "Dude go away this shit be private yo<br/>";
} else {
	$gristname = initGrists();
	$keyresult = $mysqli->query("SELECT * FROM `Captchalogue` WHERE `Captchalogue`.`abstratus` LIKE '%keykind%'");
	while ($krow = $keyresult->fetch_array()) {
		echo "Key: " . $krow['name'] . "<br/>";
		$alreadyfound = false;
		$doorresult = $mysqli->query("SELECT * FROM `Dungeon_Doors` WHERE `Dungeon_Doors`.`keys` LIKE '%" . $mysqli->real_escape_string($krow['name']) . "%'");
		while ($drow = $doorresult->fetch_array()) {
			$alreadyfound = true;
		}
		if (strpos($krow['abstratus'], "bladekind") !== false || strpos($krow['abstratus'], "birdkind") !== false || strpos($krow['description'], "blade") !== false || strpos($krow['description'], "sword") !== false) {
			echo "nope screw it<br/>";
			$alreadyfound = true;
		}
		if (!$alreadyfound) {
			$newpower = $krow['power'] * 2;
			$newdesc = str_replace("key", "door", $krow['description']);
			$newdesc = str_replace("Key", "Door", $newdesc);
			$newdesc = str_replace("\\", "", $newdesc); //no backslashes before the escaping
			$newdesc = $mysqli->real_escape_string($newdesc);
			$total = 0;
			$grist = 0;
			while (!empty($gristname[$grist])) {
				$gristcost = $gristname[$grist] . "_Cost";
				$total += $krow[$gristcost];
				$grist++;
			}
			if ($total > 1000000)
				$newgate = 6;
			elseif ($total > 100000)
				$newgate = 5;
			elseif ($total > 1000)
				$newgate = 3;
			else
				$newgate = 1;
			$newkeys = $mysqli->real_escape_string($krow['name']);
			$query = "INSERT INTO `Dungeon_Doors` (`gate`,`keys`,`description`,`strength`) VALUES ($newgate, '$newkeys', '$newdesc', $newpower);";
			echo $query . "<br/>";
			//$mysqli->query($query);
		}
	}
	echo "Done!<br/>";
}

require 'footer.php';
?>