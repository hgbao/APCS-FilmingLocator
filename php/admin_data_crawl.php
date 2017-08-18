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
	case 'crawlingData':
		$function();
		break;
	default:
		break;
}

function crawlingData(){
	$connection = GetDatabaseConnection();
	//Get movie list from API ThemovieDB
	$data_method = "popularity.desc";
	$api_url = "http://api.themoviedb.org/3/discover/movie?sort_by=";

	$iPage = 1;//Starting page
	$nPage = 50;//number of pages to crawl

	for ($i = 0; $i < $nPage; $i++){
		echo PHP_EOL.'=== [UPDATE][CURRENT PAGE]: '. $iPage .PHP_EOL;
		$url = $api_url . $data_method . "&page=" . $iPage . "&api_key=" . $GLOBALS['api_key'];
		$response= file_get_contents($url);
		$jsonData = json_decode($response);

		$iPage = $jsonData->page + 1;
		$arrayResult = $jsonData->results;

		foreach ($arrayResult as $value) {
			//Check movie in database
			$sql = "SELECT * FROM MovieList WHERE ID='".$value->id."'";
			if ($connection->query($sql)->num_rows > 0) {

			} else{
				echo $value->id." ";
				crawlingDataLocation($value->id);
				flush();
			}
		}
	}
	$sql = "UPDATE Configuration SET Data='" .date("Y-m-d"). "' WHERE Name='last_update'; ";
	$sql .= "UPDATE Configuration SET Data='" .$nPage. "' WHERE Name='last_page'; ";
	$sql .= "UPDATE Configuration SET Data=(SELECT COUNT(*) FROM MovieList) WHERE Name='total_movies'; ";
	if ($connection->multi_query($sql) == FALSE) {
		echo '[ERROR][UPDATE]: Configuration' .PHP_EOL;
	}
	CloseDatabaseConnection();
}

function crawlingDataLocation($id){
	//Get movie information from API ThemovieDB
	$api_url = "http://api.themoviedb.org/3/movie/";
	$url = "http://api.themoviedb.org/3/movie/" . $id . "?api_key=" . $GLOBALS['api_key'];
	$response= file_get_contents($url);
	$jsonData = json_decode($response);

	//Get filming locations from IMDB website
	$imdb_id = $jsonData->imdb_id;
	$url_imdb = "http://www.imdb.com/title/" . $imdb_id . "/locations";
	$responseIMDB = file_get_contents($url_imdb);

	$step_one = explode('<div id="filming_locations_content" class="header">', $responseIMDB);
	$step_two = explode('<div class="article" id="see_also">', $step_one[1]);
	$step_three = explode("itemprop='url'>", $step_two[0]);

	//Add locations to Database
	queryAddRawDatabase($id, $jsonData->original_title, $imdb_id, $jsonData->overview, 
		$jsonData->poster_path, $jsonData->release_date, json_encode($jsonData->genres), $step_three);
}

function queryAddRawDatabase($id, $name, $imdb_id, $overview, $poster_path, $release_date, $genres, $step_three){
	$connection = GetDatabaseConnection();
	//Clean string
	$name = str_replace('"', '', $name);
	$name = str_replace("'", '', $name);
	$overview = str_replace('"', '', $overview);
	$overview = str_replace("'", '', $overview);
	//Insert to database
	$sql = "INSERT INTO MovieList (ID, Name, IMDB_ID, Overview, Poster, ReleasedDate, Genres) ";
	$sql.= "VALUES ('".$id."', N'".$name."','".$imdb_id."', N'".$overview."','".$poster_path."','".$release_date."','".$genres."')";
	if ($connection->query($sql) == FALSE) {
		echo '[ERROR][INSERT]: MovieList ' . $id .PHP_EOL;
	}

	for ($i = 1; $i < sizeof($step_three); $i = $i + 1){
		$location = explode('</a>', $step_three[$i])[0];
		//Clean string
		$location = str_replace('"', '', $location);
		$location = str_replace("'", '', $location);
		//Insert to database
		$sql = "INSERT INTO RawDatabase (ID, IMDB_ID, Location) VALUES ('".$id."', '".$imdb_id."', N'".$location."')";
		if ($connection->query($sql) == FALSE) {
			echo '[ERROR][INSERT]: RawDatabase ' . $imdb_id . " " . $location .PHP_EOL;
		}
	}
}
?>