<?php

/*$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbFilmingLocator";
$api_key = "1e61dd900daeedb06c0cb78db8be91c2";*/

$servername = "sql300.epizy.com";
$username = "epiz_18895740";
$password = "hgbao1807";
$dbname = "epiz_18895740_cs427";
$api_key = "1e61dd900daeedb06c0cb78db8be91c2";

function GetDatabaseConnection(){
	global $conn;
	if($conn){
		return $conn;
	}
	$conn = new mysqli($GLOBALS['servername'], $GLOBALS['username'], $GLOBALS['password'], $GLOBALS['dbname']);
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	return $conn;
}

function CloseDatabaseConnection(){
	global $conn;
	if($conn){
		$conn->close();
	}
}
?>