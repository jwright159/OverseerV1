<?php
require_once "includes/SQLconnect.php";
$players = $mysqli->query("SELECT * FROM Players");
while ($result = $players->fetch_array()) {
  $mysqli->query("UPDATE `Players` SET `Dream_Health_Vial` = $result[Gel_Viscosity] WHERE `Players`.`username` = '" . $result['username'] . "' LIMIT 1 ;");
  echo $result['username'];
}
?>