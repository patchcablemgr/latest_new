<?php
define('QUADODO_IN_SYSTEM', true);
require_once('header.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	
	$lineID = $_GET['lineID'];
	$error = validate($lineID);
	if ($error == '') {
		?>
		<div class="row" id="lineItem<?php echo $lineID; ?>">
			<div style="display:flex; margin-bottom:20px;" class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
				<img style="border:1px solid gray; display:inline-block; margin-right:20px;" src="/images/product.jpg" alt="Product" height="50" width="50">
				<form style="margin-top:auto; margin-bottom:auto;" class="form-inline">
					<fieldset class="form-group">
						<select class="form-control lineItem">
							<option value="" selected>--Product--</option>
							<option value="label">Label</option>
							<option value="cable">Patch Cable</option>
						</select>
					</fieldset>
					<!-- Enclosure Layout -->
					<fieldset class="dependantField-item form-group" style="display:none;">
						<select class="form-control" id="exampleSelect1">
							<option selected>--Type--</option>
							<option>Cat5e</option>
							<option>SingleMode</option>
							<option>MultiMode</option>
						</select>
					</fieldset>
					<fieldset class="dependantField-item form-group" style="display:none;">
						<select class="form-control" id="exampleSelect1">
							<option selected>--Color--</option>
							<option>Blue</option>
							<option>White</option>
							<option>Yellow</option>
							<option>Red</option>
							<option>Black</option>
						</select>
					</fieldset>
					<fieldset class="dependantField-item-label form-group" style="display:none;">
						<input class="form-control" style="max-width:75px;" type="number" name="qty" min="1" value="1">
					</fieldset>
				</form>
				<div style="margin-top:auto; margin-bottom:auto;">
				<button type="button" class="btn btn-danger waves-effect waves-light">
					<span class="btn-label"><i class="fa fa-times"></i>
				</span>Remove</button></div>
			</div>
		</div>
		<?php
	} else {
		echo $error;
	}
}

function validate($lineID) {
	//Validate line item ID
	if (!preg_match('/^[0-9]+$/', $lineID)) {
		return 'Invalid line item ID';
	}
	
	if ($lineID > 100) {
		return 'Number of line items cannot exceed 100';
	}
	return '';
}
?>

<div class="row">
	<div style="display:flex; margin-bottom:20px;" class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
		<img style="border:1px solid gray; display:inline-block; margin-right:20px;" src="/images/product.jpg" alt="Product" height="50" width="50">
		<form style="margin-top:auto; margin-bottom:auto;" class="form-inline">
			<fieldset class="form-group">
				<select class="form-control lineItem">
					<option value="" selected>--Product--</option>
					<option value="label">Label</option>
					<option value="cable">Patch Cable</option>
				</select>
			</fieldset>
			<!-- Enclosure Layout -->
			<fieldset class="dependantField-item form-group" style="display:none;">
				<select class="form-control" id="exampleSelect1">
					<option selected>--Type--</option>
					<option>Cat5e</option>
					<option>SingleMode</option>
					<option>MultiMode</option>
				</select>
			</fieldset>
			<fieldset class="dependantField-item form-group" style="display:none;">
				<select class="form-control" id="exampleSelect1">
					<option selected>--Color--</option>
					<option>Blue</option>
					<option>White</option>
					<option>Yellow</option>
					<option>Red</option>
					<option>Black</option>
				</select>
			</fieldset>
			<fieldset class="dependantField-item-label form-group" style="display:none;">
				<input class="form-control" style="max-width:75px;" type="number" name="qty" min="1" value="1">
			</fieldset>
		</form>
		<div style="margin-top:auto; margin-bottom:auto;">
		<button type="button" class="btn btn-danger waves-effect waves-light">
			<span class="btn-label"><i class="fa fa-times"></i>
		</span>Remove</button></div>
	</div>
</div>