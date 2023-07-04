<?php

require_once 'includes/dotenv_load.php';

$SQLhostName = $_ENV['SQL_HOSTNAME'];
$SQLuserName = $_ENV['SQL_USERNAME'];
$SQLpassword = $_ENV['SQL_PASSWORD'];
$SQLdatabase = $_ENV['SQL_DATABASE'];
$SQLconnect = $mysqli->connect($SQLhostName, $SQLuserName, $SQLpassword);



if (!$SQLconnect) {
   die('Could not connect: ' . $mysqli->error());
}
else{
}
// make dbname the current db
//
$db_selected = $mysqli->select_db($SQLdatabase,$SQLconnect);
if (!$db_selected) {
   die ('could not connect to mysqli database : ' . $mysqli->error());
}
$con = $SQLconnect;
// Server:      Database: 
  
  ?>
  
  
  <div style = " 
  <?php 
  if ($_SERVER['SERVER_NAME'] != 'overseerdev.ctri.co.uk') 
	{ echo "border: 2px solid red; background-color:#ffcccc; text-color:red;"; } 
  else 
	{ echo "border: 2px solid green; background-color:#ccffcc; text-color:green;"; }
  ?>
  
  width:600px; margin-left:auto;margin-right:auto;
  font-family:Courier New; font-weight:bold; padding:10px; text-align:center;">
	We are connected to the <b>dev</b> database! <Br/>
	<?php
	if ($_SERVER['SERVER_NAME'] != 'overseerdev.ctri.co.uk') 
		echo "	If this is on the live site, let a developer know AT ONCE!";
	?>
  </div>