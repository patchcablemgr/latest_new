<?php
define('QUADODO_IN_SYSTEM', true);
require_once './includes/header.php';
require_once './includes/redirectToLogin.php';
$qls->Security->check_auth_page('user.php');
?>

<?php require 'includes/header_start.php'; ?>
<?php require 'includes/header_end.php'; ?>


<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title">Account Profile</h4>
    </div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 col-xl-3">
		<div class="card-box">
			<h4 class="m-t-0 header-title"><b>Change Password</b></h4>
			<fieldset class="form-group">
				<input type="password" class="form-control" id="inputNewPassword" placeholder="New Password">
			</fieldset>

			<fieldset class="form-group">
				<input type="password" class="form-control" id="inputNewPasswordConfirm" placeholder="Confirm">
			</fieldset>
			<button id="buttonPasswordChangeSubmit" type="button" class="btn btn-primary">Submit</button>
		</div>
	</div>
	<div class="col-xs-12 col-sm-6 col-md-9 col-lg-9 col-xl-9">
		<div class="card-box">
			<h4 class="m-t-0 header-title"><b>2 Factor Authentication</b></h4>
			<small class="text-muted">Provides 2 factor authentication using Google Authenticator one-time passwords.</small>
			<div class="checkbox checkbox-primary">
			<?php
				if ($qls->user_info['mfa']) {
					echo '<input id="checkboxMFA" type="checkbox" checked>';
				} else {
					echo '<input id="checkboxMFA" type="checkbox">';
				}
			?>
			<label for="checkboxMFA">Enable 2FA</label>
			</div>
			<div id="QRCodeContainer"></div>
		</div>
	</div>
</div>

<?php require 'includes/footer_start.php' ?>

<script src="assets/pages/jquery.profile.js"></script>

<?php require 'includes/footer_end.php' ?>
