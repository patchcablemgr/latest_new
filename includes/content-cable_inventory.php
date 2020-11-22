<?php
	require_once './includes/shared_tables.php';
	require_once './includes/path_functions.php';
	
	$pillYes = '<span class="label label-pill label-success">Yes</span>';
	$pillNo = '<span class="label label-pill label-danger">No</span>';
	
	echo '<table id="inventoryTable" class="table table-striped table-bordered">';
	echo '<thead>';
		echo '<tr>';
			echo '<th colspan="3" style="text-align:center">Cable End A</th>';
			echo '<th colspan="3" style="text-align:center">Cable End B</th>';
			echo '<th colspan="3" style="text-align:center">Cable Properties</th>';
		echo '</tr>';
		echo '<tr>';
			echo '<th>ID</th>';
			echo '<th>Connector</th>';
			echo '<th>Connected</th>';
			echo '<th>ID</th>';
			echo '<th>Connector</th>';
			echo '<th>Connected</th>';
			echo '<th>Finalized</th>';
			echo '<th>Media</th>';
			echo '<th>Length</th>';
		echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	$query = $qls->SQL->select('*', 'app_inventory', array('active' => array('=', 1)));
	while($row = $qls->SQL->fetch_assoc($query)) {
		$mediaTypeID = $row['mediaType'];
		$categoryTypeID = $mediaTypeTable[$mediaTypeID]['category_type_id'];
		$length = calculateCableLength($mediaTypeTable, $mediaCategoryTypeTable, $row);
		
		echo '<tr>';
			echo '<td data-connectorID="'.$row['a_code39'].'"><a class="linkScan" href="#">'.$row['a_code39'].'</a><button class="displayBarcode pull-right btn btn-sm waves-effect waves-light btn-primary"><i class="fa fa-barcode"></i></button></td>';
			echo '<td>'.$connectorTable[$row['a_connector']]['name'].'</td>';
			if($row['a_object_id'] == 0) {
				echo '<td>'.$pillNo.'</td>';
			} else {
				echo '<td>'.$pillYes.'</td>';
			}
			echo '<td data-connectorID="'.$row['b_code39'].'"><a class="linkScan" href="#">'.$row['b_code39'].'</a><button class="displayBarcode pull-right btn btn-sm waves-effect waves-light btn-primary"><i class="fa fa-barcode"></i></button></td>';
			echo '<td>'.$connectorTable[$row['b_connector']]['name'].'</td>';
			if($row['b_object_id'] == 0) {
				echo '<td>'.$pillNo.'</td>';
			} else {
				echo '<td>'.$pillYes.'</td>';
			}
			if($row['editable'] == 0) {
				echo '<td>'.$pillYes.'&nbsp&nbsp<a title="Allow cable properties to be edited." class="linkEditable" data-action="unfinalize" data-cableID="'.$row['id'].'" href="javascript:void(0);">unFinalize</a></td>';
			} else {
				echo '<td>'.$pillNo.'&nbsp&nbsp<a title="Remove the ability to edit cable properties." class="linkEditable" data-action="finalize" data-cableID="'.$row['id'].'" href="javascript:void(0);">Finalize</a></td>';
			}
			echo '<td>'.$mediaTypeTable[$row['mediaType']]['name'].'</td>';
			echo '<td>'.$length.'</td>';
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
?>
