<?php
include 'data_config.php';
// Create connection
if(!isset($_SESSION)){
	session_start();
}

$function ='';
if(isset($_POST['function']))
	$function = $_POST['function'];

switch ($function) {
	case 'handleRawdb':
		$function();
		break;
	default:
		break;
}

function handleRawdb(){
	$connection = GetDatabaseConnection();
	$sql = "SELECT No, Location FROM rawdatabase";
	$result = $connection->query($sql);

	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    	$split = explode(', ', $row['Location']);
    	$country = $split[0];
    	$province = null;
    	if (sizeof($split) > 1){
    		$country = $split[sizeof($split) - 1];
    		$province = $split[sizeof($split) - 2];
    	}
    	$country = preg_replace('~[\r\n]+~', '', $country);
    	
    	$arrayID = getRegionID($country);
    	if ($arrayID != null){
    		$sql = "UPDATE rawdatabase SET ";
    		$sql .= "Province='".  $province . "',";
    		$sql .= "CountryID='" . $arrayID['CountryID'] ."',";
    		$sql .= "SubcontinentID='". $arrayID['SubcontinentID'] ."',";
    		$sql .= "ContinentID='". $arrayID['ContinentID'] ."'";
    		$sql .= " WHERE No='". $row['No'] ."';";
    		$connection->query($sql);
    	}
	}

	CloseDatabaseConnection();
}

function getRegionID($name){
	$connection = GetDatabaseConnection();
	//Replace name by a name stored in database by specific cases
	$sql = "SELECT * FROM replacement WHERE text='".$name."'";
	$result = $connection->query($sql);
	if ($result->num_rows != 0){
		$name = mysqli_fetch_array($result, MYSQLI_ASSOC)['tobe'];
	}

	//Find country code by name in database
	//$sql = "SELECT C.id AS 'CountryID', SubcontinentID, ContinentID, C.Name FROM Country C JOIN subcontinent S ON SubcontinentID=S.ID where C.Name LIKE '%".$name."%'";
	$sql = "SELECT C.id AS 'CountryID', SubcontinentID, ContinentID, C.Name FROM Country C JOIN subcontinent S ON SubcontinentID=S.ID where C.Name='".$name."'";
	$result = $connection->query($sql);
	if ($result->num_rows == 1)
		return mysqli_fetch_array($result, MYSQLI_ASSOC);

	if ($result->num_rows == 0)
		echo "- Country name [0]: ".$name.PHP_EOL;
	else if ($result->num_rows > 1){
		echo "- Country name [2]: ".$name.PHP_EOL;
	}
	return null;
}

?>