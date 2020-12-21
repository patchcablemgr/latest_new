<?php
define('QUADODO_IN_SYSTEM', true);
require_once './includes/header.php';
require_once './includes/redirectToLogin.php';
$qls->Security->check_auth_page('operator.php');
?>

<?php require 'includes/header_start.php'; ?>

	<!-- JSTree css -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.8/themes/default/style.min.css" />

	<!-- X-editable css -->
	<link type="text/css" href="assets/plugins/x-editable/css/bootstrap-editable.css" rel="stylesheet">

<?php require 'includes/header_end.php'; ?>
<?php require_once './includes/modals.php'; ?>

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
		<div class="card-box">
			<div class="row">
				<div class="m-t-20 col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
					<?php
						if(isset($_GET['connectorCode'])) {
							echo '<input id="connectorCodeParam" type="hidden" value="'.$_GET['connectorCode'].'">';
						}
					?>
					<button id="buttonScan" data-function="scan" type="button" class="btn btn-block btn-lg btn-primary waves-effect waves-light m-b-20">Scan Cable</button>
					
					<!-- Local End -->
					<div class="card">
						<div class="card-header">Local End</div>
						<div class="card-block">
							<blockquote class="card-blockquote">
								<table>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Connector ID:&nbsp&nbsp</strong>
										</td>
										<td id="localConnectorCode39">-</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Connector Type<span class="requiredFlag"></span>:&nbsp&nbsp</strong>
										</td>
										<td id="localConnectorTypeContainer">-</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight" valign="top">
											<strong>Connected To:&nbsp&nbsp</strong>
										</td>
										<td id="localConnectorPathContainer">-</td>
									</tr>
								</table>
							</blockquote>
						</div>
					</div>
					
					<!-- Cable -->
					<div class="card">
						<div class="card-header">Cable</div>
						<div class="card-block">
							<blockquote class="card-blockquote">
								<table>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Cable Length<span class="requiredFlag"></span> (<span id="cableUnitOfLength">m./ft.</span>):&nbsp&nbsp</strong>
										</td>
										<td id="cableLengthContainer">-</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Media Type<span class="requiredFlag"></span>:&nbsp&nbsp</strong>
										</td>
										<td id="cableMediaTypeContainer">-</td>
									</tr>
								</table>
								<button id="buttonFinalize" type="button" class="btn btn-success waves-effect waves-light m-t-10" style="display:none;" disabled>
									<span class="btn-label"><i class="fa fa-check"></i></span>
									Finalize*
								</button>
							</blockquote>
						</div>
					</div>
					
					<!-- Remote End -->
					<div class="card">
						<div class="card-header">Remote End</div>
						<div class="card-block">
							<blockquote class="card-blockquote">
								<div id="remoteInitialize" style="display:none;">
									<strong>Attention: </strong>
									<p>
									It appears this is a new cable.  Please scan the remote end to add it to your available cable inventory.
									</p>
									<button id="buttonInitialize" type="button" class="btn btn-info waves-effect waves-light btn-sm">
										Scan*
									</button>
								</div>
								<div id="remoteVerify">
									<table>
										<tr>
											<td class="objectDetailAlignRight">
												<strong>Connector ID:&nbsp&nbsp</strong>
											</td>
											<td id="remoteConnectorCode39">-</td>
										</tr>
										<tr>
											<td class="objectDetailAlignRight">
												<strong>Connector Type<span class="requiredFlag"></span>:&nbsp&nbsp</strong>
											</td>
											<td id="remoteConnectorTypeContainer">-</td>
										</tr>
										<tr>
											<td class="objectDetailAlignRight" valign="top">
												<strong>Connected To:&nbsp&nbsp</strong>
											</td>
											<td id="remoteConnectorPathContainer">-</td>
										</tr>
									</table>
									<button id="buttonVerify" data-function="verify" type="button" class="btn btn-info waves-effect waves-light m-t-10" disabled>
										<span class="btn-label"><i id="buttonVerifyIcon" class="fa fa-exclamation"></i>
									</span>Verify</button>
								</div>
							</blockquote>
						</div>
					</div>
					
					<!-- Path -->
					<div class="card">
						<div class="card-header">Path
							<span>
								<div class="btn-group pull-right">
									<button type="button" class="btn btn-sm btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions <span class="m-l-5"><i class="fa fa-cog"></i></span></button>
									<div class="dropdown-menu">
										<a id="printFullPath" class="dropdown-item" href="#"><i class="ion-map"></i> Print</a>
									</div>
								</div>
							</span>
						</div>
						<div class="card-block">
							<blockquote class="card-blockquote">
								<div id="pathContainer"></div>
							</blockquote>
						</div>
					</div>
				</div>
			</div><!-- end row -->
		</div><!-- end card-box -->
	</div><!-- end col-->
</div><!-- end row -->

<?php require 'includes/footer_start.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.8/jstree.min.js"></script>
<script src="assets/pages/jquery.scan.js"></script>

<!-- XEditable Plugin -->
<script src="assets/plugins/moment/moment.js"></script>
<script type="text/javascript" src="assets/plugins/x-editable/js/bootstrap-editable.min.js"></script>

<!-- printThis Plugin -->
<script src="assets/plugins/printThis/printThis.js"></script>

<!-- Quagga Plugin -->
<script type="text/javascript" src="assets/plugins/quagga/dist/quagga.js"></script>

<?php require 'includes/footer_end.php' ?>
