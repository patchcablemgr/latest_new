<?php
define('QUADODO_IN_SYSTEM', true);
require_once('header.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	
	$lineID = $_GET['lineID'];
	$error = validate($lineID);
	if ($error == '') {
		$connectorTypeTable = array();
		$query = $qls->SQL->select('*', 'shared_cable_connectorOptions');
		while($row = $qls->SQL->fetch_assoc($query)) {
			array_push($connectorTypeTable, array('value'=>$row['value'], 'name'=>$row['name'], 'category_type_id'=>$row['category_type_id']));
		}
		
		$mediaCategoryTable = array();
		$query = $qls->SQL->select('*', 'shared_mediaCategory');
		while($row = $qls->SQL->fetch_assoc($query)) {
			$mediaCategoryTable[$row['id']] = array('name' => $row['name'], 'category' => $row['category']);
		}
		
		$mediaCategoryTypeTable = array();
		$query = $qls->SQL->select('*', 'shared_mediaCategoryType');
		while($row = $qls->SQL->fetch_assoc($query)) {
			$mediaCategoryTypeTable[$row['value']] = array('name' => $row['name'], 'unit_of_length' => $row['unit_of_length']);
		}
		
		$mediaTypeTable = array();
		$query = $qls->SQL->select('*', 'shared_mediaType');
		while($row = $qls->SQL->fetch_assoc($query)) {
			array_push($mediaTypeTable, array('value'=>$row['value'], 'name'=>$row['name'], 'category_id'=>$row['category_id'], 'category_type_id'=>$row['category_type_id']));
		}
		
		$cableLengthTable = array();
		$query = $qls->SQL->select('*', 'shared_cable_length', false, array('value', 'ASC'));
		while($row = $qls->SQL->fetch_assoc($query)) {
			array_push($cableLengthTable, array('value'=>$row['value'], 'name'=>$row['name'], 'category_type_id'=>$row['category_type_id']));
		}
		
		$cableColorTable = array();
		$query = $qls->SQL->select('*', 'shared_cable_color');
		while($row = $qls->SQL->fetch_assoc($query)) {
			array_push($cableColorTable, array('value'=>$row['value'], 'name'=>$row['name'], 'short_name'=>$row['short_name']));
		}
		?>
		<tr id="lineItem<?php echo $lineID; ?>" data-lineTotal="">
			<td>
				<img style="border:1px solid gray; display:inline-block;" src="/app/assets/images/no_img_available.jpg" alt="Product" height="50" width="75">
			</td>
			<td>
				<select class="form-control form-control-sm selectProduct">
					<option value="" selected>--</option>
					<?php
						foreach($connectorTypeTable as $selection) {
							$labelQty = strtolower($mediaCategoryTypeTable[$selection['category_type_id']]['name']) == 'label' ? ' (qty. '.FIBER_LABEL_COUNT.')' : '';// -=LABELCOUNT=-
							echo '<option value="'.$selection['value'].'" data-productCategory="'.$mediaCategoryTypeTable[$selection['category_type_id']]['name'].'">'.$selection['name'].$labelQty.'</option>';
						}
					?>
				</select>
			</td>
			<td>
				<select class="dependant form-control form-control-sm selectLength">
					<option value="" selected>--</option>
					<?php
						foreach($cableLengthTable as $selection) {
							echo '<option value="'.$selection['value'].'" class="'.$mediaCategoryTypeTable[$selection['category_type_id']]['name'].'">'.$selection['name'].' '.$mediaCategoryTypeTable[$selection['category_type_id']]['unit_of_length'].'</option>';
						}
					?>
				</select>
			</td>
			<td>
				<select class="dependant form-control form-control-sm selectMedia">
					<option value="" selected>--</option>
					<?php
						foreach($mediaTypeTable as $selection) {
							echo '<option value="'.$selection['value'].'" class="'.$mediaCategoryTypeTable[$selection['category_type_id']]['name'].'">'.$selection['name'].'</option>';
						}
					?>
				</select>
			</td>
			<td>
				<select class="dependant form-control form-control-sm selectColor">
					<option value="" selected>--</option>
					<?php
						foreach($cableColorTable as $selection) {
							echo '<option value="'.$selection['value'].'" data-shortName="'.$selection['short_name'].'">'.$selection['name'].'</option>';
						}
					?>
				</select>
			</td>
			<td>
				<input class="dependant form-control form-control-sm inputQty" style="max-width:75px;" type="number" name="qty" min="1" value="1">
			</td>
			<td>
			<?php if ($lineID > 1) { ?>
				<button class="btn btn-sm waves-effect waves-light btn-danger lineItemRemove"> <i class="fa fa-remove"></i> </button>
			<?php } ?>
			</td>
		</tr>
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
