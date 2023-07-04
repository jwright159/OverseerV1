<?php
require_once "header.php";
require_once 'time.php';

if (empty($_SESSION['username'])) {
  echo "Log in to view stuff that happened to you.</br>";
} else {


  $result = $mysqli->query("SELECT * FROM Logs WHERE `Logs`.`username` = '$username'");
  while ($row = $result->fetch_array()) {
    if ($row['username'] == $username) {
      echo "It is currently " . produceIST(initTime($con)) . "</br>";
      echo "Events for " . $username . ":</br>";
      $result2 = $mysqli->query("SELECT * FROM Logs");
      $col = $result2->fetch_field(); //Skip the username.
      while ($col = $result2->fetch_field()) {
	$log = $col->name;
	echo $row[$log];
	echo "</br>";
      }
    }
  }
}
require_once "footer.php";
echo '</br><a href="/">Home</a> <a href="controlpanel.php">Control Panel</a>';
?>