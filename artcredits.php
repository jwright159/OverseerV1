<?php
require_once "header.php";
if (empty($_SESSION['username'])) {
	echo "Log in to access the list of art credits.<br/>";
} else {

	$result = $mysqli->query("SELECT * FROM Captchalogue where `art` <> '' ORDER BY name ;") or die($mysqli->error());
	while ($row = $result->fetch_array()) {

		echo '<a href="Images/Items/' . $row['art'] . '">' . stripslashes($row['name']) . '</a> - ' . stripslashes($row['credit']) . '<br/>';
	}
}
require_once "footer.php";
?>