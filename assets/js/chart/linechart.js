google.charts.setOnLoadCallback(drawLinechart);

//Front-end config
var optionsLinechart = {
	legend: {
		position: 'none'
	}
};

//Back-end config
var filter = null, filterType = null;
var filterYearFrom = 2000, filterYearTo = 2016;
var arrayCountry = [];

function drawLinechart() {
	databaseLinechart = new google.visualization.DataTable();
	databaseLinechart.addColumn('date', 'Year');

	//Add categories
	$.ajax({
		type: 'POST',
		url: 'php/data_get.php',
		data:{
			'function': 'loadCountryListLinechart',
			'filter': filter,
			'filterType': filterType,
			'from': filterYearFrom,
			'to': filterYearTo
		},
		success:function(response){
			if(response == 'null'){
				Materialize.toast('No result', 4000);
				return;
			}

			var data = JSON.parse(response).Result;
			for (i = 0; i < data.length; i++){
				databaseLinechart.addColumn('number', data[i].Name);
				arrayCountry.push([data[i].ID, data[i].Name]);
			}

			$.ajax({
				type: 'POST',
				url: 'php/data_get.php',
				data:{
					'function': 'loadDatabaseLinechart',
					'filter': filter,
					'filterType': filterType,
					'from': filterYearFrom,
					'to': filterYearTo
				},
				success:function(response){
					if(response == 'null'){
						return;
					}

					//Add data
					var data = JSON.parse(response).Result;
					var i = 0;
					var start = 0;
					var previousYear = data[0].Year;
					//Add lower bound data
					if (parseInt(previousYear) != filterYearFrom){
						databaseLinechart.addRow(getArrayCountry(filterYearFrom, []));
					}
					//Add middle data
					while (true){
						while (i < data.length && data[i].Year == previousYear){
							i++;
						}
						databaseLinechart.addRow(getArrayCountry(previousYear, data.slice(start, i)));
						if (i == data.length)
							break;
						previousYear = data[i].Year;
						start = i;
					}
					//Add upper bound
					if (parseInt(previousYear) != filterYearTo){
						databaseLinechart.addRow(getArrayCountry(filterYearTo, []));
					}

					var linechart = new google.charts.Line(document.getElementById('chart-linechart'));
					linechart.draw(databaseLinechart, optionsLinechart);

					$('#card-linechart .card-reveal .card-title').click();
					$(".linechart-wrapper").addClass("loaded");
				}
			});
}
});
}

function getArrayCountry(year, data){
	var array = [new Date(year + '')];
	var cur = 0;
	for (i = 0; i < arrayCountry.length; i++){
		if (cur < data.length && arrayCountry[i][0] == data[cur].CountryID){
			array.push(parseInt(data[cur].Number));
			cur++;
		}
		else
			array.push(0);
	}
	return array;
}