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
	case 'getAllRegion':
		@$function();
		break;
	case 'getDataTable':
		@$function($_POST['tb_name']);
		break;
	case 'loadDatabaseTreemap':
		@$function($_POST['limit']);
		break;
	case 'loadCountryListLinechart':
		@$function($_POST['filter'],$_POST['filterType'],$_POST['from'],$_POST['to']);
		break;
	case 'loadDatabaseLinechart':
		@$function($_POST['filter'],$_POST['filterType'],$_POST['from'],$_POST['to']);
		break;
	default:
		break;
}

function convertData($mysql_result){
	if ($mysql_result->num_rows > 0){
		$array = array (
			'Result' => array (),
			);
		$i = 0;
		while ($row = mysqli_fetch_array($mysql_result))
			$array['Result'][$i++] = $row;
		return $array;
	}
	return null;
}

function getDataTable($tb_name){
	$connection = GetDatabaseConnection();
	$sql = "SELECT * FROM " .$tb_name;
	$result = $connection->query($sql);

	echo json_encode(convertData($result));
	CloseDatabaseConnection();
}

function getAllRegion(){
	$connection = GetDatabaseConnection();
	$sql ="SELECT ID AS 'ID', 'world' AS 'Parent', Name AS 'Name' FROM continent ";
	$sql .= "UNION SELECT ID, ContinentID, Name FROM subcontinent ";
	$sql .= "UNION SELECT ID, SubcontinentID, Name FROM country ";
	$result = $connection->query($sql);

	echo json_encode(convertData($result));
	CloseDatabaseConnection();
}

//================ TREEMAP LOCATION ===================
function loadDatabaseTreemap($limit){
	$connection = GetDatabaseConnection();
	$sql = "SELECT C.Name AS 'Name', 'World' AS 'Parent', 0 AS 'Count' ";
	$sql .="FROM continent C ";
	$sql .="UNION ";
	$sql .="SELECT S.Name, C.Name, 0 ";
	$sql .="FROM subcontinent S LEFT JOIN continent C ON S.ContinentID = C.ID ";
	$sql .="UNION ";
	$sql .="SELECT C.Name, S.Name, COUNT(*) - 1 ";
	$sql .="FROM country C JOIN subcontinent S ON C.SubcontinentID = S.ID ";
	$sql .="LEFT JOIN (SELECT * FROM rawdatabase LIMIT " .$limit. ") R ON C.ID = R.CountryID ";
	$sql .="GROUP BY C.Name ";

	$result = $connection->query($sql);

	echo json_encode(convertData($result));
	CloseDatabaseConnection();
}


//================ LINE CHART TIME SERIES ===================
function loadCountryListLinechart($filter, $filterType, $from, $to){
	$connection = GetDatabaseConnection();
	$condition_filter = "";
	if ($filter != null){
		$condition_filter = "AND R." .$filterType. "ID = '" .$filter. "'";
	}

	$sql = "SELECT DISTINCT C.ID, C.Name ";
	$sql .= "FROM rawdatabase R LEFT JOIN movielist M ON R.ID = M.ID ";
	$sql .= "INNER JOIN country C ON R.CountryID = C.ID ";
	$sql .= "WHERE R.CountryID IS NOT NULL AND ";
	$sql .= "SUBSTRING(M.ReleasedDate,1,4) >= '" .$from. "' AND SUBSTRING(M.ReleasedDate,1,4) <= '" .$to. "' " .$condition_filter;
	$sql .= " ORDER BY R.CountryID";

	$result = $connection->query($sql);

	echo json_encode(convertData($result));
	CloseDatabaseConnection();
}

function loadDatabaseLinechart($filter, $filterType, $from, $to){
	$connection = GetDatabaseConnection();
	$condition_filter = "";
	if ($filter != null){
		$condition_filter = "AND R." .$filterType. "ID = '" .$filter. "'";
	}

	$sql = "SELECT DISTINCT SUBSTRING(M.ReleasedDate,1,4) AS 'Year', R.CountryID, R.SubcontinentID, R.ContinentID, COUNT(*) AS 'Number' ";
	$sql .= "FROM rawdatabase R LEFT JOIN movielist M ON R.ID = M.ID ";
	$sql .= "WHERE R.CountryID IS NOT NULL AND ";
	$sql .= "SUBSTRING(M.ReleasedDate,1,4) >= '" .$from. "' AND SUBSTRING(M.ReleasedDate,1,4) <= '" .$to. "' " .$condition_filter;
	$sql .= " GROUP BY Year, R.CountryID ";
	$sql .= "ORDER BY Year, R.CountryID";

	$result = $connection->query($sql);

	echo json_encode(convertData($result));
	CloseDatabaseConnection();
}
?>