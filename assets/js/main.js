$(function(){
	loadAllRegion();
});

$(document).ready(function() {
	addNavigation("definition");
	addNavigation("environment");
	addNavigation("background");
	addNavigation("treemap");
	addNavigation("linechart");
	addNavigation("reference");
	addNavigation("author");
	addNavigation("changelog");

	$('#btn-update-treemap').click(updateTreemap);
	$('#btn-update-linechart').click(updateLinechart);
	$('#config-linechart-back').click(decreaseScope);
	
	$("#config-treemap-number").ionRangeSlider({
		type: "single",
		min: 100,
		max: 1000,
		grid: true,
		from: 1000,
		step: 100,
    	onFinish: function (data) {
    		limitTreemap = data.from;
    	}
	});
	$("#config-linechart-year").ionRangeSlider({
		type: "double",
		grid: true,
		min: 1900,
		max: 2020,
		from: 2000,
		to: 2016,
		step: 1,
		onFinish: function (data) {
    		filterYearFrom = data.from;
    		filterYearTo = data.to;
    	}
	});

	//Height and width of Geochart
	var height = $("#config-linechart").height();
	var width = $("#config-linechart").width();
	if (height > width)
		optionsGeochart.width = width;
	else
		optionsGeochart.height = height / 2;
});

function addNavigation(name){
	jQuery("#nav-" + name).click(function(){ 
		jQuery('html,body').animate({
			scrollTop: jQuery("#card-" + name).offset().top - $('#header').height()
		}, 500);
	});
}

function updateTreemap(){
	databaseTreemap = [['Name', 'Parent', 'Number of Movies'],
	['World', null, 0]
	];

	$(".treemap-wrapper").removeClass("loaded");
	drawTreemap();
}

function updateLinechart(){
	arrayCountry = [];

	if (scope != 0){
		filter = currentChoice;
		filterType = arrayScope[scope-1];
	}
	else{
		filter = null;
		filterType = null;
	}

	$(".linechart-wrapper").removeClass("loaded");
	drawLinechart();
}

function loadAllRegion(){
	$.ajax({
		type: 'POST',
		async: false,
		url: 'php/data_get.php',
		data:{
			'function': 'getAllRegion'
		},
		success:function(response){
			if(response.length == 0){
				return;
			}

			var data = JSON.parse(response).Result;
			for (i = 0; i < data.length; i++){
				arrayRegion.push([data[i].ID, data[i].Parent, data[i].Name]);
			}
		}
	});
}