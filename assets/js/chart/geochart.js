google.charts.setOnLoadCallback(drawGeochart);

//Front-end config
var optionsGeochart = {
	colorAxis: {colors: ['#00853f', '#e31b23']},
	defaultColor: '#f5f5f5',
	legend: 'none',
	height: '300',

	region: 'world',
	resolution: 'continents',
};

//Back-end config
var databaseGeochart = null;
var geochart = null;
var arrayRegion = [];

var arrayResolution = ['continents','subcontinents','country','provinces'];
var arrayScope = ['Continent', 'Subcontinent', 'Country'];
var currentChoice = "world";
var arrayChoice = [['world','All'],['',''],['',''],['','']];
var scope = 0;

function drawGeochart() {
	databaseGeochart = new google.visualization.DataTable();
	databaseGeochart.addColumn('string', 'ID');
	databaseGeochart.addColumn('number', 'Index');
	databaseGeochart.addColumn({type:'string', role:'tooltip'});

	if (scope < 3){
		for (i = 0; i < arrayRegion.length; i++){
			if (arrayRegion[i][1] == currentChoice)
				databaseGeochart.addRow([{v:arrayRegion[i][0], f:arrayRegion[i][2]}, i, arrayScope[scope]]);
		}
	}

	geochart = new google.visualization.GeoChart(document.getElementById('chart-geochart'));
	google.visualization.events.addListener(geochart, 'select', geochartSelected);
	geochart.draw(databaseGeochart, optionsGeochart);
}

function geochartSelected(){
	if (scope < 4){
		var row = geochart.getSelection()[0].row;
		var tmp = databaseGeochart.Tf[row].c[0];
		currentChoice = tmp.v;

		//Change value showed
		$('#config-linechart-region').html(tmp.f);

		//Remember choice selected
		scope++;
		arrayChoice[scope] = [currentChoice, tmp.f];

		//Change chart
		optionsGeochart.resolution = arrayResolution[scope];
		optionsGeochart.region = currentChoice;
		drawGeochart();
	}
}

function decreaseScope(){
	if (scope > 0){
		scope--;

		//Change value showed
		$('#config-linechart-region').html(arrayChoice[scope][1]);

		//Change data
		currentChoice = arrayChoice[scope][0];

		//Change chart
		optionsGeochart.resolution = arrayResolution[scope];
		optionsGeochart.region = currentChoice;
		drawGeochart();
	}
}