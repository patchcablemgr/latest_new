<?php
define('QUADODO_IN_SYSTEM', true);
require_once './includes/header.php';
$qls->Security->check_auth_registration();
?>



<?php
// Is the user logged in already?
if ($qls->user_info['username'] == '') {
	if (isset($_POST['process'])) {
		// Try to register the user
		if ($qls->User->register_user()) {
			$email = (isset($_POST['email']) && strlen($_POST['email']) > 6 && strlen($_POST['email']) < 256 && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? $qls->Security->make_safe($_POST['email']) : false;
			
			// Redirect to login page if registration was successful
			$qls->redirect('/login.php?s=0');
		}
		else {
            // Output register error
            echo $qls->User->register_error . REGISTER_TRY_AGAIN;
		}
	}
	else {
        // Get the random id for use in the form
        $random_id = $qls->Security->generate_random_id();
        require_once('html/register_form.php');
	}
}
else {
    echo REGISTER_ALREADY_LOGGED;
}
?>
