<?php
define('QUADODO_IN_SYSTEM', true);
require_once './includes/header.php';
require_once './includes/redirectToLogin.php';
$qls->Security->check_auth_page('administrator.php');
?>

<?php require 'includes/header_start.php'; ?>

	<!-- Jquery filer css -->
	<link href="assets/plugins/jquery.filer/css/jquery.filer.css" rel="stylesheet" />
	<link href="assets/plugins/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css" rel="stylesheet" />
	
<?php require 'includes/header_end.php'; ?>
<?php require_once './includes/modals.php'; ?>

<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title">Admin - Integration</h4>
    </div>
</div>

<div class="row">
	
	<div class="col-sm-12 col-xs-12 col-md-6 col-lg-6 col-xl-4">
		<div class="card-box">
			<h4 class="header-title m-t-0 m-b-30">Manage Data</h4>
			<button id="buttonDataExport" type="button" class="btn btn-success waves-effect waves-light">
				<span class="btn-label"><i class="fa fa-upload"></i>
				</span>Backup
			</button>
			<button id="buttonDataImport" type="button" class="btn btn-danger waves-effect waves-light" data-toggle="modal" data-target="#importModal">
				<span class="btn-label"><i class="fa fa-download"></i>
				</span>Restore
			</button>
		</div>
	</div>
</div>

<?php require 'includes/footer_start.php' ?>

<!-- Jquery filer js -->
<script src="assets/plugins/jquery.filer/js/jquery.filer.min.js"></script>

<script src="assets/pages/jquery.admin-integration.js"></script>

<?php require 'includes/footer_end.php' ?>
