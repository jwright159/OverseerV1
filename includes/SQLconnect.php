<?php

require_once 'includes/dotenv_load.php';

//I wonder...
$db_hostname = $_ENV['DB_HOSTNAME'];
$db_username = $_ENV['DB_USERNAME'];
$db_password = $_ENV['DB_PASSWORD'];
$db_database = $_ENV['DB_DATABASE'];

/*
try {
	$db = new PDO("mysql:host=$db_hostname;dbname=$db_database;", $db_username, $db_password);
} catch (PDOException $e) {
	exit("Could not connect to database: {$e->getMessage()}<br/>");
}
*/

$mysqli = new \mysqli($db_hostname, $db_username, $db_password, $db_database);
if (!$mysqli) {
   die('Could not connect: ' . $mysqli->error);
}

function query(string $query, array|null $params = null)
{
	global $mysqli;
	$statement = $mysqli->prepare($query);
	$statement->execute($params);
	return $statement->get_result();
}

function fetchOne(string $query, array|null $params = null)
{
	return query($query, $params)->fetch_array();
}

function fetchAll(string $query, array|null $params = null)
{
	return query($query, $params)->fetch_all();
}
