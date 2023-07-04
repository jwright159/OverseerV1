<?php

require_once 'includes/dotenv_load.php';

//I wonder...
$SQLhostName = $_ENV['SQL_HOSTNAME'];
$SQLuserName = $_ENV['SQL_USERNAME'];
$SQLpassword = $_ENV['SQL_PASSWORD'];
$SQLdatabase = $_ENV['SQL_DATABASE'];

$con = mysqli_connect($SQLhostName, $SQLuserName, $SQLpassword, $SQLdatabase);

if (!$con) {
   die('Could not connect: ' . mysql_error());
}