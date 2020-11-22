/**
 * Theme: Uplon Admin Template
 * Author: Coderthemes
 * Dashboard
 */
 
function handleInventoryCompatibility(categoryType){
	$('#inventorySelectMediaType option').hide();
	$('.'+categoryType).eq(0).prop('selected', true);
	$('.'+categoryType).show();
}

function getInventoryData(connectorValue, mediaValue){
	var data = {
		connectorValue: connectorValue,
		mediaValue: mediaValue,
		requestedData: 'inventory'
		};
	data = JSON.stringify(data);

	$.post('backend/retrieve_dashboard-data.php', {data:data}, function(response){
		var responseJSON = JSON.parse(response);
		if (responseJSON.error != ''){
			displayError(responseJSON.error, $('#alertMsg'));
		} else {
			$('#inventory-donut').empty();
			var inventoryDonutData = responseJSON.success.donutData;
			
			Morris.Donut({
				element: 'inventory-donut',
				data: inventoryDonutData,
				resize: true,
				colors: ['#007bff', '#28a745', '#ffc107', '#dc3545']
			});
		}
	});
}
 
$( document ).ready(function() {
	var categoryType = $('#inventorySelectConnectorType option:selected').attr('data-categoryType');
	handleInventoryCompatibility(categoryType);
	
	var connectorValue = $('#inventorySelectConnectorType').val();
	var mediaValue = $('#inventorySelectMediaType').val();
		var data = {
		requestedData: 'initial'
		};
	data = JSON.stringify(data);

	$.post('backend/retrieve_dashboard-data.php', {data:data}, function(response){
		var responseJSON = JSON.parse(response);
		if (responseJSON.error != ''){
			displayError(responseJSON.error, $('#alertMsg'));
		} else {
			$('#inventory-donut').empty();
			var inventoryDonutData = responseJSON.success.donutData;
			var utilizationTable = responseJSON.success.utilizationTable;
			var historyTable = responseJSON.success.historyTable;
			
			Morris.Donut({
				element: 'inventory-donut',
				data: inventoryDonutData,
				resize: true,
				colors: ['#007bff', '#28a745', '#ffc107', '#dc3545']
			});
			
			$('#tableUtilizationBody').html(utilizationTable);
			$('#tableUtilization').DataTable({
				'order': [[ 3, 'desc' ]]
			});
			
			$('#tableHistoryBody').html(historyTable);
			$('#tableHistory').DataTable({
				'order': [[ 0, 'desc' ]]
			});
		}
	});

	$('#inventorySelectConnectorType').on('change', function(){
		var categoryType = $('#inventorySelectConnectorType option:selected').attr('data-categoryType');
		handleInventoryCompatibility(categoryType);
		
		var connectorValue = $('#inventorySelectConnectorType').val();
		var mediaValue = $('#inventorySelectMediaType').val();
		getInventoryData(connectorValue, mediaValue);
	});
	
	$('#inventorySelectMediaType').on('change', function(){
		var connectorValue = $('#inventorySelectConnectorType').val();
		var mediaValue = $('#inventorySelectMediaType').val();
		getInventoryData(connectorValue, mediaValue);
	});
});
