/**
 * Order
 * This page takes in order items, shipping address, and payment info
 */

$( document ).ready(function() {
	$('#inventoryTable').DataTable();
	$('#availableCableEndIDTable').DataTable({'ordering': false, 'searching': false});
	
	$('.linkScan').on('click', function(e){
		e.preventDefault();
		var code39 = $(this).parent().attr('data-connectorID');
		$(location).attr('href', '/scan.php?connectorCode='+code39);
	});
	
	$('.displayBarcode').on('click', function(){
		var tableCell = $(this).parent();
		var connectorID = $(tableCell).attr('data-connectorID');
		
		$(this).siblings().hide();
		$(this).hide();
		
		$(tableCell).append('<div id="barcodeContainer'+connectorID+'"></div>');
		$('#barcodeContainer'+connectorID).barcode(connectorID, "code39", {barWidth:2}).on('click', function(){
			$(this).siblings().show();
			$(this).remove();
		});
	});
	
	$('.linkEditable').on('click', function(){
		var linkEditable = $(this);
		var action = $(linkEditable).attr('data-action');
		var cableID = $(linkEditable).attr('data-cableID');
		
		var data = {
			cableID: cableID,
			action: action
		}
		data = JSON.stringify(data);
		
		$.post("backend/process_cable-editable.php", {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				var pill = $(linkEditable).siblings('.label-pill');
				
				if(action == 'finalize') {
					$(pill).removeClass('label-danger').addClass('label-success');
					$(pill).html('Yes');
					$(linkEditable).html('unFinalize');
					$(linkEditable).attr('data-action', 'unfinalize');
					$(linkEditable).attr('title', 'Allow cable properties to be edited.');
					
				} else if(action == 'unfinalize') {
					$(pill).removeClass('label-success').addClass('label-danger');
					$(pill).html('No');
					$(linkEditable).html('Finalize');
					$(linkEditable).attr('data-action', 'finalize');
					$(linkEditable).attr('title', 'Remove the ability to edit cable properties.');
				}
			}
		});
	});
});