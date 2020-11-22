<?php
	
	echo '<table id="availableCableEndIDTable" class="table table-striped table-bordered">';
	echo '<thead>';
		echo '<tr>';
			echo '<th>Cable End ID</th>';
		echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	
	$initializedCableEndIDs = array();
	$query = $qls->SQL->select('*', 'app_inventory', array('order_id' => array('=', 0)));
	while($row = $qls->SQL->fetch_assoc($query)) {
		array_push($initializedCableEndIDs, $row['a_id']);
		array_push($initializedCableEndIDs, $row['b_id']);
	}
	
	$availableCableEndIDs = array();
	for($x=1; $x<=count($initializedCableEndIDs)+AVAILABLE_CABLE_END_ID_COUNT; $x++) {
		if(!in_array($x, $initializedCableEndIDs)) {
			array_push($availableCableEndIDs, $x);
		}
	}
	
	foreach($availableCableEndIDs as $cableEndID) {
		echo '<tr>';
			echo '<td>';
			echo strtoupper(base_convert($cableEndID, 10, 36));
			echo '</td>';
		echo '</tr>';
	}
	
	echo '</tbody>';
	echo '</table>';
?>
