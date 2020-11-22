<?php
	$colorTable = array();
	$query = $qls->SQL->select('*', 'shared_cable_color');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$colorTable[$row['value']] = $row;
	}
	
	$productTable = array();
	$query = $qls->SQL->select('*', 'shared_cable_connectorOptions');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$productTable[$row['value']] = $row;
	}
	
	$connectorTable = array();
	$query = $qls->SQL->select('*', 'shared_cable_connectorType');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$connectorTable[$row['value']] = $row;
	}
	
	$mediaTypeTable = array();
	$query = $qls->SQL->select('*', 'shared_mediaType');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$mediaTypeTable[$row['value']] = $row;
	}
	
	$lengthTable = array();
	$query = $qls->SQL->select('*', 'shared_cable_length');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$lengthTable[$row['value']] = $row;
	}
	
	$mediaCategoryTypeTable = array();
	$query = $qls->SQL->select('*', 'shared_mediaCategoryType');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$mediaCategoryTypeTable[$row['value']] = $row;
	}
?>