<?php

$path = array();

if($connectorCode39) {
	$connectorID = base_convert($connectorCode39, 36, 10);
	$rootCable = $qls->App->inventoryByIDArray[$connectorID];
	
	$objID = $rootCable['local_object_id'];
	$objFace = $rootCable['local_object_face'];
	$objDepth = $rootCable['local_object_depth'];
	$objPort = $rootCable['local_object_port'];
}

$selectedObjID = $objID;
$selectedObjFace = $objFace;
$selectedObjDepth = $objDepth;
$selectedObjPort = $objPort;

function crawlConnSet($objID, $objFace, $objDepth, $objPort, $connSetID=0, $connSet=array(array(),array())) {
	
	// Store port details
	$workingArray = array(
		'objID' => $objID,
		'objFace' => $objFace,
		'objDepth' => $objDepth,
		'objPort' => $objPort,
	);
	
	// Verify this node has not been visited already
	$alreadySeen = false;
	foreach($connSet as $conn) {
		foreach($conn as $port) {
			if($port['objID'] == $objID and $port['objFace'] == $objFace and $port['objDepth'] == $objDepth and $port['objPort'] == $objPort) {
				$alreadySeen = true;
			}
		}
	}
	
	// Is local port connected?
	if(isset($qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort]) and !$alreadySeen) {
		
		// Add port info to connection set
		array_push($connSet[$connSetID], $workingArray);
		
		// Loop over each local port connection
		$inventoryEntry = $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort];
		foreach($inventoryEntry as $connection) {
			
			// Collect remote object data
			$remoteObjID = $connection['id'];
			$remoteObjFace = $connection['face'];
			$remoteObjDepth = $connection['depth'];
			$remoteObjPort = $connection['port'];
			
			// Flip the connection set ID
			$connSetID = ($connSetID == 0) ? 1 : 0;
			crawlConnSet($remoteObjID, $remoteObjFace, $remoteObjDepth, $remoteObjPort, $connSetID, $connSet);
			
		}
	}
}

?>
