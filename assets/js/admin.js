$(document).ready(function() {
	$('#load-data').click(adminHandleRawdb);
});

function adminCrawlingData(){
	$.ajax({
		type: 'POST',
		url: 'php/admin_data_crawl.php',
		data:{
			'function': 'crawlingData'
		},
		success:function(response){
			console.log(response);
		}
	});
}

function adminHandleRawdb(){
	$.ajax({
		type: 'POST',
		url: 'php/admin_data_handle.php',
		data:{
			'function': 'handleRawdb'
		},
		success:function(response){
			console.log(response);
		}
	});
}