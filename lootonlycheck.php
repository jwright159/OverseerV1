<?php
require_once "header.php";

if (empty($_SESSION['username'])) {
	echo "Log in to do stuff.<br/>";
} else {
	if ($userrow['session_name'] != "Developers") {
		echo "Hey! This tool is for the developers only. Nice try, pal.";
	} else {
		$gristname = initGrists();
		foreach ($gristname as $grist) {
			$maxgain[$grist . '_Cost'] = 0;
			$items[$grist . '_Cost'] = 0;
		}
		$gateitems[1] = 0;
		$gateitems[3] = 0;
		$gateitems[5] = 0;
		$result = $mysqli->query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`lootonly` = 1");
		echo "running through lootonlies<br/>";
		while ($row = $result->fetch_array()) {
			$maxgaint = $maxgain;
			echo $row['name'] . " acknowledged";
			$totalcost = 0;
			foreach ($gristname as $grist) {
				$gristcost = $grist . '_Cost';
				if ($row[$gristcost] > 0) {
					if ($row[$gristcost] > $maxgaint[$gristcost] && $row[$gristcost] <= 800000) {
						$maxgaint[$gristcost] = $row[$gristcost];
					}
					$totalcost += $row[$gristcost];
					$items[$gristcost]++;
				}
			}
			if ($totalcost >= 5 && $totalcost <= 2000) {
				$gateitems[1]++;
				echo " (gate 1)";
			}
			if ($totalcost >= 1000 && $totalcost <= 250000) {
				$gateitems[3]++;
				echo " (gate 3)";
			}
			if ($totalcost >= 100000 && $totalcost <= 800000) {
				$gateitems[5]++;
				echo " (gate 5)";
			}
			if ($totalcost < 5 || $totalcost > 800000) {
				$defunctitems++;
				echo ", but it can't be looted";
			} else
				$maxgain = $maxgaint;
			echo "<br/>";
		}
		echo "ANALYSIS:<br/>";
		echo strval($gateitems[1]) . " gate 1 items<br/>";
		echo strval($gateitems[3]) . " gate 3 items<br/>";
		echo strval($gateitems[5]) . " gate 5 items<br/>";
		echo strval($defunctitems) . " gate x items<br/>";
		foreach ($gristname as $grist) {
			echo $grist . " has " . strval($items[$grist . '_Cost']) . " items and " . strval($maxgain[$grist . '_Cost']) . " max gain<br/>";
		}
	}
}
