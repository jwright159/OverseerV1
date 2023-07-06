<?php
require_once "header.php";

function getColumns(string $table)
{
	$columns = [];
	$result = query('SELECT * FROM :table LIMIT 0;', ['table' => $table]);
	for ($i = 0; $i < $result->columnCount(); $i++)
		$columns[] = $result->getColumnMeta($i)['name'];
	return $columns;
}

function insertFromPost(string $table)
{
	$query = "INSERT INTO `$table` VALUES (";
	$params = [];
	
	foreach (getColumns($table) as $column)
	{
		$query .= "?, ";
		$params[] = $_POST[$column];
	}

	$query = substr($query, 0, -2) . ");";
	
	echo $query . "<br/>";
	query($query, $params);
}

function updateFromPost(string $table, string $key, string $keyValue)
{
	$query = "UPDATE `$table` SET ";
	$params = [];
	
	foreach (getColumns($table) as $column)
	{
		$query .= "`$column` = ?, ";
		$params[] = $_POST[$column];
	}
	
	$query = substr($query, 0, -2) . " WHERE `$key` = ?;";
	$params[] = $keyValue;
	
	echo $query . "<br/>";
	query($query, $params);
}

if ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods")
	echo "What are you doing here?";
else
{
	if (!empty($_GET['event']))
	{
		$editevent = $_GET['event'];
		$populate = true;
	}
	else
	{
		$editevent = "";
		$populate = false;
	}

	if (!empty($_GET['area']))
	{
		$areastr = "Explore_" . $_GET['area'];
	}
	else
	{
		$areastr = "Explore_Derse";
		$populate = false;
	}
	
	$editevent = $_POST['name'];

	if (!empty($_POST['name']))
	{
		$blocked = false;

		if ($_POST['boonreward'] != 0 && empty($_POST['transform']))
		{
			echo "When giving a boon reward, 'transform' must be set or else refreshing = infinite boondollars.<br/>";
			$blocked = true;
		}

		if ($_POST['cansleep'] == 1 && empty($_POST['sleepevent']))
		{
			echo "Please provide a sleepevent if a player can sleep at this event. 'wakeup' is default.<br/>";
			$blocked = true;
		}
		
		if (!$blocked)
		{
			$areastr = "Explore_" . $_POST['exarea'];
			if ($row = fetchOne("SELECT * FROM :area WHERE `name` = :editevent LIMIT 1;", ['area' => $areastr, 'editevent' => $editevent]))
			{
				$founditem = true;
				$erow = $row;
			}
			
			if ($founditem)
				updateFromPost($areastr, 'name', $editevent);
			else
				insertFromPost($areastr);
			
			//now test to see if it worked
			if ($founditem)
			{
				$victory = true;
				echo "Event updated.<br/>";
			}
			else
			{
				$testrow = fetchOne("SELECT `name` FROM :area WHERE `name` = :editevent LIMIT 1;", ['area' => $areastr, 'editevent' => $editevent]);
				if ($testrow['name'] == $editevent)
				{
					$victory = true;
					echo "Event added.<br/>";
				}
				else
				{
					$victory = false;
					echo "Oops, something is wrong! The query didn't go through, and the event wasn't created. If all else fails, send that query to Blah!<br/>";
				}
			}
		}
	}

	if ($populate && ($row = fetchOne("SELECT * FROM :area WHERE :area.`name` = :editevent LIMIT 1;", ['area' => $areastr, 'editevent' => $editevent])))
	{
		$founditem = true;
		echo "{$row['name']} loaded<br/>";
		$erow = $row;
	}

	echo '<form action="addexploration.php" method="post" id="itemeditor"><table cellpadding="0" cellspacing="0"><tbody><tr><td align="right">Exploration Editor:</td><td> Let\'s Actually Do This Edition</td></tr>';
	if (!$populate)
		echo '<tr><td align="right">Area this event appears in:</td><td><select name="exarea"><option value="Derse">Derse</option><option value="Prospit">Prospit</option><option value="Battlefield">Battlefield</option></select></td></tr>';
	else
		echo '<input type="hidden" name="exarea" value="' . $_GET['area'] . '" />';
	$fieldresult = $mysqli->query("SELECT * FROM `Explore_Prospit` LIMIT 1;");
	while ($field = $fieldresult->fetch_field())
	{
		echo '<tr><td align="right">';
		$fname = $field->name;
		if ($fname == "description")
		{
			echo $fname . ':</td><td><textarea name="description" rows="6" cols="40" form="itemeditor">';
			if ($founditem)
				echo $erow[$fname];
			elseif (!empty($_POST[$fname]))
				echo $_POST[$fname];
			echo '</textarea></td></tr>';
		}
		else
		{
			echo $fname . ':</td><td> <input type="text" name="' . $fname . '"';
			if ($founditem)
				echo ' value="' . $erow[$fname] . '"';
			elseif (!empty($_POST[$fname]))
				echo $_POST[$fname];
			echo '></td></tr>';
		}
	}
	echo '</table><input type="submit" value="Edit/Create"></form><br/>';
}

require_once "footer.php";
