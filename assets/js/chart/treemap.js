google.charts.setOnLoadCallback(drawTreemap);

//Front-end config
var optionsTreemap = {
	highlightOnMouseOver: true,
	maxDepth: 1,
	maxPostDepth: 3,
	headerColor: '#1de9b6',
	minColor: '#b2dfdb',
	midColor: '#5C6BC0',
	maxColor: '#26A69A',
	headerHeight: 20,
	textStyle: { 
		fontName: 'roboto',
		fontSize: 15,
	},
	useWeightedAverageForAggregation: false,
	generateTooltip: showTreemapTooltip
};

function showTreemapTooltip(row, size, value) {
	var tooltipStyle = '<div class="material-tooltip" style="background-color:teal; opacity:1; display:block; max-width:50rem; width: 7rem;">';
	var tooltipContent = databaseTreemap[row+1][0] + '<br>(' + size + ' stages)';
	return tooltipStyle + tooltipContent + '</div>';
}

//Back-end config
var treemap = null;
var limitTreemap = 1000;
var databaseTreemap = [['Name', 'Parent', 'Number of Movies'],
['World', null, 0]
];

function drawTreemap() {
	$.ajax({
		type: 'POST',
		url: 'php/data_get.php',
		data:{
			'function': 'loadDatabaseTreemap',
			'limit' : limitTreemap
		},
		success:function(response){
			if(response == 'null'){
				Materialize.toast("NO RESULT!", 4000);
				return;
			}
			var data = JSON.parse(response).Result;
			for (i = 0; i < data.length; i++){
				databaseTreemap.push([data[i].Name, data[i].Parent, parseInt(data[i].Count)]);
			}
			var dataTreemap = google.visualization.arrayToDataTable(databaseTreemap);

			treemap = new google.visualization.TreeMap(document.getElementById('chart-treemap'));
			treemap.draw(dataTreemap, optionsTreemap);
			
			$('#card-treemap .card-reveal .card-title').click();
			$(".treemap-wrapper").addClass("loaded");
			$(".treemap-wrapper").height("auto");
			$(".linechart-wrapper").height($(".treemap-wrapper").height());
		}
	});
}