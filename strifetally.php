<?php
require_once "header.php";

echo 'The following is a list of every "main" abstratus and the number of weapons present in each.<br/>';
$itemresult = $mysqli->query("SELECT * FROM Captchalogue ORDER BY abstratus");
$currentabstratus = "";
$k = 0;
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
	if ($alreadydone == false && $mainabstratus != "notaweapon" && $mainabstratus != "headgear" && $mainabstratus != "bodygear" && $mainabstratus != "facegear" && $mainabstratus != "accessory" && $mainabstratus != "computer") { //I HAVE NEW WEAPON!
		$absresult = $mysqli->query("SELECT * FROM `Captchalogue` WHERE `abstratus` LIKE '" . $mainabstratus . "%' OR `abstratus` LIKE '%, " . $mainabstratus . "%'");
		//ensures that we don't catch dartkind with artkind, inflatablekind with tablekind, etc
		$total = 0;
		while ($itemrow = $absresult->fetch_array()) {
			$total++;
		}
		$ordered[$mainabstratus] = $total;
		$abs[$k] = $mainabstratus;
		$k++;
		if ($_GET['sort'] != "yes")
			echo $mainabstratus . ": $total<br/>";
	}
}
if ($_GET['sort'] == "yes") {
	$allabs = $k;
	$i = 1;
	$maxx = max($ordered);
	while ($i <= $maxx) {
		$k = 0;
		while ($k < $allabs) {
			if ($ordered[$abs[$k]] == $i) {
				echo $abs[$k] . ": " . strval($ordered[$abs[$k]]) . "<br/>";
				$countresult = $mysqli->query("SELECT `ID` FROM `Feedback` WHERE `Feedback`.`type` = 'item' AND `Feedback`.`comments` LIKE '%" . $abs[$k] . "%' AND `Feedback`.`suspended` = 0");
				while ($row = $countresult->fetch_array()) {
					echo ' - Submission <a href="submissions.php?view=' . strval($row['ID']) . '">' . strval($row['ID']) . '</a><br/>';
				}
			}
			$k++;
		}
		$i++;
	}
}
require_once "footer.php";
?>