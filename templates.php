<?php
define('QUADODO_IN_SYSTEM', true);
require_once './includes/header.php';
require_once './includes/redirectToLogin.php';
$qls->Security->check_auth_page('operator.php');
require_once './includes/content-templates.php';
?>

<?php require 'includes/header_start.php'; ?>

	<link href="assets/plugins/bootstrap-tagsinput/css/bootstrap-tagsinput.css" rel="stylesheet"/>
	<link href="assets/css/style-templates.css" rel="stylesheet" type="text/css"/>
	<link href="assets/css/style-cabinet.css" rel="stylesheet" type="text/css"/>
	<link href="assets/css/style-object.css" rel="stylesheet" type="text/css"/>
	<link href="assets/plugins/spectrum/css/spectrum.css" rel="stylesheet">
	<link type="text/css" href="assets/plugins/x-editable/css/bootstrap-editable.css" rel="stylesheet">

	<!-- Jquery filer css -->
	<link href="assets/plugins/jquery.filer/css/jquery.filer.css" rel="stylesheet" />
	<link href="assets/plugins/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css" rel="stylesheet" />

<?php require 'includes/header_end.php'; ?>
<?php require_once './includes/modals.php'; ?>

<!-- Make server data available to client via hidden inputs -->
<?php include_once('includes/content-build-serverData.php'); ?>

<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title">Build - Templates</h4>
    </div>
</div>

<div class="row">
	<div class="col-md-4">
		<div class="card-box">
			<h4 class="header-title m-t-0 m-b-30">Properties</h4>
			<input id="inputSideCount" type="hidden" name="sideCount" value="0">
			<input id="inputFrontImage" type="hidden" name="frontImage" value="">
			<input id="inputRearImage" type="hidden" name="frontImage" value="">
		
			<!-- Name -->
			<fieldset class="form-group">
				<label>Name <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Alphanumeric characters as well as hyphens (-), underscores (_), forward slashes (\/), and backslashes (\)."></i></label>
				<input id="inputName" class="form-control" type="text" name="name" placeholder="New_Template" value="New_Template">
			</fieldset>
			
			<!-- Category -->
			<fieldset class="form-group">
				<label>Category <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Group and color code objects."></i></label>
				<div class="input-group" style="display:flex;">
					<!-- Category select input -->
					<div>
					<select id="inputCategory" name="category" class="form-control">
						<?php $qls->App->generateCategoryOptions(); ?>
					</select>
					</div>
					
					<!-- Category edit button -->
					<div style="margin:auto 0px auto 5px">
						<button class="btn btn-sm waves-effect waves-light btn-primary" data-toggle="modal" type="button" data-target="#myModal">
							<i class="zmdi zmdi-edit"></i>
						</button>
					</div>
				</div>
				
			</fieldset>
			
			<!-- Object Type -->
			<fieldset id="objectType" class="form-group">
				<label>Template Type <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" data-html="true" title="Standard: Typical 19&quot; rack mounted equipment (switch, router, server, etc.) Insert: Modular template nested inside of standard template enclosure partitions (network module, fiber cassette, etc.)"></i></label>
				<div class="inputBlock">
					<div class="radio">
						<input class="objectType" type="radio" name="objectTypeRadio" id="objectTypeStandard" value="Standard" checked>
							<label for="objectTypeStandard">Standard</label>
					</div>
					<div class="radio">
						<input class="objectType" type="radio" name="objectTypeRadio" id="objectTypeInsert" value="Insert">
							<label for="objectTypeInsert">Insert</label>
					</div>
				</div>
			</fieldset>
			
			<!-- RU Size -->
			<fieldset id="objectRUSize" class="dependantField objectType standard form-group">
				<label>Template Size <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Size of template in rack units."></i></label>
				<div class="inputBlock" style="margin-bottom:10px;">
					<div style="display:inline;">RU:</div>
					<input style="position:absolute; left:40px;" id="inputRU" name="RUSize" type="number" min="1" max="25" value="1"/>
				</div>
			</fieldset>
			
			<!-- Object Function -->
			<fieldset id="objectFunction" class="dependantField objectType standard form-group">
				<label>Template Function <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Endpoint: Terminates layer1 (switch, router, server, etc.) Passive: Layer1 only (patch panel, fiber cassette, etc.)"></i></label>
				<div class="inputBlock" >
					<div class="radio">
						<input class="objectFunction" type="radio" name="objectFunction" id="inputObjectFunctionPassive" value="Endpoint" checked>
						<label for="inputObjectFunctionPassive">Endpoint</label>
					</div>
					<div class="radio">
						<input class="objectFunction" type="radio" name="objectFunction" id="inputObjectFunctionEndpoint" value="Passive">
						<label for="inputObjectFunctionEndpoint">Passive</label>
					</div>
				</div>
			</fieldset>
			
			<!-- Mounting Configuration -->
			<fieldset id="objectMountConfig" class="dependantField objectType standard form-group">
				<label>Mounting Configuration <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Template depth."></i></label>
				<div class="inputBlock" >
					<div class="radio">
						<input class="sideCount" type="radio" name="sideCount" id="inputSideCount2Post" value="0" checked>
						<label for="inputSideCount2Post">2-Post</label>
					</div>
					<div class="radio">
						<input class="sideCount" type="radio" name="sideCount" id="inputSideCount4Post" value="1">
						<label for="inputSideCount4Post">4-Post</label>
					</div>
				</div>
			</fieldset>
			
			<!-- Partition Type -->
			<fieldset id="objectPartitionType" class="form-group">
				<label>Partition Type <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Generic: Serves to space and size neighboring and nested partitions.  Connectable: Contains ports and interfaces.  Enclosure: Contains insert objects."></i></label>
				<div class="inputBlock" >
					<div class="radio">
						<input class="partitionType" type="radio" name="partitionType" id="inputPartitionTypeGeneric" value="Generic" checked>
						<label for="inputPartitionTypeGeneric">Generic</label>
					</div>
					<div class="radio">
						<input class="partitionType" type="radio" name="partitionType" id="inputPartitionTypeConnectable" value="Connectable">
						<label for="inputPartitionTypeConnectable">Connectable</label>
					</div>
					<div class="radio">
						<input class="partitionType" type="radio" name="partitionType" id="inputPartitionTypeEnclosure" value="Enclosure">
						<label for="inputPartitionTypeEnclosure">Enclosure</label>
					</div>
				</div>
			</fieldset>
			
			<!-- Custom Add/Remove Partition -->
			<fieldset id="objectPartitionAddRemove" class="form-group">
				<label>Add/Remove Partition <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Horizontal: Adds a partition inside the one selected spanning the entire width and grows down.  Vertical: Adds a partition inside the one selected spanning the entire height and grows to the right."></i></label>
				<div class="inputBlock">
					<div class="radio">
						<input type="radio" id="partitionH" class="partitionAxis" value="h" name="customPartitionAxis">
						<label for="partitionH"> Horizontal </label>
					</div>
					<div class="radio">
						<input type="radio" id="partitionV" class="partitionAxis" value="v" name="customPartitionAxis" checked>
						<label for="partitionV"> Vertical </label>
					</div>
					<button id="customPartitionAdd" class="btn btn-sm waves-effect waves-light btn-primary" type="button"> <i class="fa fa-plus"></i> </button>
					<button id="customPartitionRemove" class="btn btn-sm waves-effect waves-light btn-danger disabled" type="button" disabled> <i class="fa fa-remove"></i> </button>
				</div>
			</fieldset>
			
			<!-- Custom Partition Size -->
			<fieldset id="objectPartitionSize" class="form-group">
				<label>Partition Size <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Horizontal partitions are sized in half RU increments.  Vertical partitions are sized in 1/24th increments."></i></label>
				<div class="inputBlock" >
				<input id="inputCustomPartitionSize" name="customSectionSize" type="number" step="0.5" min="0.5" max="1" value="0.5" disabled />
				</div>
			</fieldset>
			
			<!-- Enclosure Layout -->
			<fieldset id="objectEnclosureLayout" class="dependantField partitionType enclosureField form-group">
				<label>Enclosure Layout</label>
				<div class="inputBlock" style="margin-bottom:10px;">
					<div style="display:inline;">Col:</div>
					<input style="position:absolute; left:40px;" id="inputEnclosureLayoutX" name="encLayoutX" type="number" min="1" max="12" value="1"/>
				</div>
				<div class="inputBlock" >
					<div style="display:inline;">Row:</div>
					<input style="position:absolute; left:40px;" id="inputEnclosureLayoutY" name="encLayoutY" type="number" min="1" max="12" value="1"/>
				</div>
			</fieldset>
			
			<!-- Enclosure Insert Fitment -->
			<fieldset id="objectEnclosureTolerance" class="dependantField partitionType enclosureField form-group">
				<label>Enclosure Tolerance</label>
				<div class="inputBlock" >
					<div class="radio">
						<input class="enclosureTolerance" type="radio" name="enclosureTolerance" id="inputEnclosureToleranceStrict" value="Strict" checked>
						<label for="inputEnclosureToleranceStrict">Strict</label>
					</div>
					<div class="radio">
						<input class="enclosureTolerance" type="radio" name="enclosureTolerance" id="inputEnclosureToleranceLoose" value="Loose">
						<label for="inputEnclosureToleranceLoose">Loose</label>
					</div>
				</div>
			</fieldset>
			
			<!-- Port Numbering -->
			<fieldset id="objectPortPrefix" class="dependantField partitionType connectable endpoint passive form-group">
				<label>Port ID</label><br>
				<div id="portNameDisplay"></div>
				<button id="buttonPortNameConfigure" class="btn btn-sm waves-effect waves-light btn-primary" data-port-name-action="add" data-toggle="modal" data-target="#portNameModal" type="button">
					<span class="btn-label">
						<i class="zmdi zmdi-edit"></i>
					</span>
					Configure
				</button>
			</fieldset>
			
			<!-- Port Layout -->
			<fieldset id="objectPortLayout" class="dependantField partitionType connectable endpoint passive form-group">
				<label>Port Layout</label>
				<div class="inputBlock" style="margin-bottom:10px;">
					<div style="display:inline;">Col:</div>
					<input style="position:absolute; left:40px;" id="inputPortLayoutX" name="portLayoutX" type="number" min="0" max="48" value="0"/>
				</div>
				<div class="inputBlock">
					<div style="display:inline;">Row:</div>
					<input style="position:absolute; left:40px;" id="inputPortLayoutY" name="portLayoutY" type="number" min="0" max="6" value="0"/>
				</div>
			</fieldset>
			
			<!-- Port Orientation -->
			<fieldset id="objectPortOrientation" class="dependantField partitionType connectable endpoint passive form-group">
				<label>Port Orientation</label>
				<div class="inputBlock">
					<?php echo generateOrientation($qls); ?>
				</div>
			</fieldset>
			
			<!-- Port Type -->
			<fieldset id="objectPortType" class="dependantField partitionType connectable endpoint passive form-group">
				<label>Port Type</label>
				<select id="inputPortType" name="portType" class="form-control">
					<?php echo generatePortType($qls); ?>
				</select>
			</fieldset>
			
			<!-- Media Type -->
			<fieldset id="objectMediaType" class="dependantField partitionType connectable passive form-group">
				<label>Media Type</label>
				<select id="inputMediaType" name="mediaType" class="form-control">
					<?php echo generateMediaType($qls); ?>
				</select>
			</fieldset>
			
			<button id="objectEditor-Submit" type="submit" class="btn btn-success waves-effect waves-light">
				<span class="btn-label"><i class="fa fa-check"></i></span>
				Submit
			</button>
		</div>
	</div>

	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-4 col-xl-4">
		<div id="cabinetContainer" style="padding-bottom:50px;" class="card-box">
			<div style="display:flex;">
			<h4 class="header-title m-t-0 m-b-30">Preview</h4>
			<div class="checkbox" style="margin-left: 15px;">
				<?php
					if($qls->user_info['scrollLock']) {
						$scrollLockState = 'checked';
						$scrollLockBool = true;
					} else {
						$scrollLockState = '';
						$scrollLockBool = false;
					}
				?>
				<input id="checkboxScrollLock" type="checkbox" <?php echo $scrollLockState; ?>>
				<label for="checkboxScrollLock">
					Lock <small>(set to <span id="scrollState"></span>)</small>
				</label>
			</div>
			</div>
			<div class="m-t-0 m-b-30"  style="display:flex;">
				<div class="radio radio-inline">
					<input class="sideSelector" type="radio" name="sideSelector" id="sideSelectorFront" value="0" checked disabled>
					<label for="sideSelectorFront">Front</label>
				</div>
				<div class="radio radio-inline">
					<input class="sideSelector" type="radio" name="sideSelector" id="sideSelectorBack" value="1" disabled>
					<label for="sideSelectorBack">Back</label>
				</div>
				
			</div>
			<?php include_once('includes/content-cabinet.php'); ?>
		</div>
	</div>

	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-4 col-xl-4">
		<div class="card-box">
			<h4 class="header-title m-t-0">Template Details</h4>
			
			<div id="objectCardBox" class="card">
				<div class="card-header">Selected Template
					<span>
						<div class="btn-group pull-right">
							<button type="button" class="btn btn-sm btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions <span class="m-l-5"><i class="fa fa-cog"></i></span></button>
							<div class="dropdown-menu">
								<a id="objFind" class="dropdown-item disabled" href="#" data-toggle="modal" data-target="#modalTemplateWhereUsed"><i class="ion-map"></i> Where Used</a>
								<a id="objClone" class="dropdown-item disabled" href="#" ><i class="fa fa-copy"></i></span> Clone</a>
								<a id="objDelete" class="dropdown-item disabled" href="#" data-toggle="modal" data-target="#modalConfirm"><i class="fa fa-times"></i></span> Delete</a>
							</div>
						</div>
					</span>
				</div>
				<div class="card-block">
					<blockquote class="card-blockquote">
						<input id="selectedObjectID" type="hidden">
						<input id="selectedObjectFace" type="hidden">
						<input id="selectedPartitionDepth" type="hidden">
						<?php include_once('./includes/content-build-objectDetails.php'); ?>
					</blockquote>
				</div>
			</div>
			<div id="availableObjects">
				<div class="card">
				
					<div class="card-header">Available Templates
						<span>
							<div class="btn-group pull-right">
								<button type="button" class="btn btn-sm btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions <span class="m-l-5"><i class="fa fa-cog"></i></span></button>
								<div class="dropdown-menu">
									<a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalTemplateCatalog"><i class="ti-import"></i> Import</a>
								</div>
							</div>
						</span>
					</div>
					
					<div class="m-t-15 m-l-5">
						<div class="radio radio-inline">
							<input class="sideSelectorDetail" type="radio" name="sideSelectorDetails" id="sideSelectorDetailsFront" value="0" checked>
							<label for="sideSelectorDetailsFront">Front</label>
						</div>
						<div class="radio radio-inline">
							<input class="sideSelectorDetail" type="radio" name="sideSelectorDetails" id="sideSelectorDetailsBack" value="1">
							<label for="sideSelectorDetailsBack">Back</label>
						</div>
					</div>
					
					<div id="availableContainer" class="card-block">
						<h6>Name Filter:</h6>
						<select id="templateFilter" multiple data-role="tagsinput"></select>
						<div id="templateContainer">
							<?php
								include_once('./includes/content-build-objects.php');
							?>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
</div>

<?php require 'includes/footer_start.php' ?>
<script src="assets/pages/jquery.templates.js"></script>

<!-- Tags Input -->
<script src="assets/plugins/bootstrap-tagsinput/js/bootstrap-tagsinput.js"></script>

<!-- XEditable Plugin -->
<script src="assets/plugins/moment/moment.js"></script>
<script type="text/javascript" src="assets/plugins/x-editable/js/bootstrap-editable.min.js"></script>

<!-- Spectrum Colorpicker -->
<script src="assets/plugins/spectrum/js/spectrum.js"></script>

<!-- Modal-Effect -->
<script src="assets/plugins/custombox/js/custombox.min.js"></script>
<script src="assets/plugins/custombox/js/legacy.min.js"></script>

<!-- Jquery filer js -->
<script src="assets/plugins/jquery.filer/js/jquery.filer.min.js"></script>
<?php require 'includes/footer_end.php' ?>
