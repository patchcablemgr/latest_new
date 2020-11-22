/**
 * Admin
 * This page allows administrators to manage users
 */

$( document ).ready(function() {

	$('#buttonPasswordChangeSubmit').on('click', function(){
		var newPassword = $('#inputNewPassword').val();
		var newPasswordConfirm = $('#inputNewPasswordConfirm').val();
		
		$.post('backend/process_password-change.php', {new_password:newPassword,new_password_confirm:newPasswordConfirm}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				displaySuccess('Password has been changed.');
			}
		});
	});
	
	$('#checkboxMFA').on('change', function(){
		if($(this).is(':checked')) {
			var mfaState = true;
		} else {
			var mfaState = false;
		}
		
		//Collect object data
		var data = {
			mfaState:mfaState
		};
		data = JSON.stringify(data);
		
		//Retrieve object details
		$.post('backend/process_profile-mfa.php', {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				displaySuccess('2FA has been updated.');
				$('#QRCodeContainer').html(responseJSON.success.html);
			}
		});
	});
});
