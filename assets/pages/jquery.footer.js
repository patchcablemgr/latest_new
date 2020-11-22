/**
 * Admin
 * This page allows administrators to manage users
**/

$( document ).ready(function() {
/*
	$('#messageModalButtonBack').on('click', function(){
		$('#messagesModalBodyTable').show();
		$('#messagesModalBodyMessage').hide();
	});

	// View all messages
	$('#messagesViewAll').on('click', function(event){
		event.preventDefault();
		
		//Retrieve messages
		$.post("backend/retrieve_messages.php", function(response){
			var alertMsg = '';
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error, $('#alertMsg'));
			} else {
				$('#messagesModalBodyTable').empty().show();
				$('#messagesModalBodyMessage').hide();
				var html = '';
				html += '<table class="table table-sm table-hover">';
                html += '<thead>';
                html += '<tr>';
                html += '<th>Date</th>';
                html += '<th>From</th>';
                html += '<th>Subject</th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';
				$(responseJSON.success).each(function(){
					var boldOpen = this.viewed == 0 ? '<strong>' : '';
					var boldClose = this.viewed == 0 ? '</strong>' : '';
					html += '<tr class="messageRow" data-messageid="'+this.messageID+'">';
					html += '<td>'+boldOpen+this.date+boldClose+'</td>';
					html += '<td>'+boldOpen+this.from+boldClose+'</td>';
					html += '<td>'+boldOpen+this.subject+boldClose+'</td>';
					html += '</tr>';
				});
				html += '</tbody>';
				html += '</table>';
				$('#messagesModalBodyTable').html(html);
				$('#messagesModal').modal('show');
				$('.messageRow').on('click', function(){
					var messageID = $(this).attr('data-messageid');
					var data = {
						messageID: messageID
					};
					data = JSON.stringify(data);

					//Retrieve message
					$.post("backend/retrieve_messages.php", {data:data}, function(response){
						var alertMsg = '';
						var responseJSON = JSON.parse(response);
						if (responseJSON.active == 'inactive'){
							window.location.replace("/");
						} else if ($(responseJSON.error).size() > 0){
							displayError(responseJSON.error, $('#alertMsg'));
						} else {
							var html = '';
							html += '<p>';
							html += '<strong>From: </strong>'+responseJSON.success.from+'<br>';
							html += '<strong>Date: </strong>'+responseJSON.success.date+'<br>';
							html += '<strong>Subject: </strong>'+responseJSON.success.subject;
							html += '</p>';
							html += '<p>'+responseJSON.success.message+'</p>';
							$('#messagesModalBodyMessageContent').html(html);
							$('#messagesModalBodyTable').hide();
							$('#messagesModalBodyMessage').show();
						}
					});
				});
			}
		});
	});
*/
});
