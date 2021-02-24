<?php

$path2 = array();

if($connectorCode39) {
	$connectorID2 = base_convert($connectorCode39, 36, 10);
	$rootCable2 = $qls->App->inventoryByIDArray[$connectorID2];
	
	$objID2 = $rootCable2['local_object_id'];
	$objFace2 = $rootCable2['local_object_face'];
	$objDepth2 = $rootCable2['local_object_depth'];
	$objPort2 = $rootCable2['local_object_port'];
}

$selectedObjID2 = $objID;
$selectedObjFace2 = $objFace;
$selectedObjDepth2 = $objDepth;
$selectedObjPort2 = $objPort;

$connSet = crawlConnSet($qls, $selectedObjID2, $selectedObjFace2, $selectedObjDepth2, $selectedObjPort2);

function crawlTrunk($connSet) {
	
}

function detectDivergence($connSet) {
	// Detect path divergence
	foreach($connSet as $conn) {
		foreach($conn as $portIndex => $port) {
			
			// Identify parent object ID
			$portObjID = $port['objID'];
			$portObj = $qls->App->objectArray[$portObjID];
			$portObjParentID = $portObj['parent_id'];
			while($portObjParentID != 0) {
				$portObj = $qls->App->objectArray[$portObjParentID];
				$portObjParentID = $portObj['parent_id'];
			}
			
			// Determine path divergence
			if($portIndex == 0) {
				$baselineParentID = $portObjParentID;
			} else {
				if($portObjParentID == $baselineParentID) {
					// Path does not diverge
				} else {
					// Path does diverge
				}
			}
		}
	}
}

function crawlConnSet(&$qls, $objID, $objFace, $objDepth, $objPort, &$connSetID=0, &$connSet=array(array(),array())) {
	
	// Store port details
	$workingArray = array(
		'objID' => $objID,
		'objFace' => $objFace,
		'objDepth' => $objDepth,
		'objPort' => $objPort,
	);
	
	// Add port info to connection set
	array_push($connSet[$connSetID], $workingArray);
	
	// Is local port connected?
	if(isset($qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort])) {
		
		// Flip the connection set ID
		$connSetID = ($connSetID == 0) ? 1 : 0;
		
		// Loop over each local port connection
		$inventoryEntry = $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort];
		foreach($inventoryEntry as $connection) {
			
			// Collect remote object data
			$remoteObjID = $connection['id'];
			$remoteObjFace = $connection['face'];
			$remoteObjDepth = $connection['depth'];
			$remoteObjPort = $connection['port'];
			
			// Verify this node has not been visited already
			$alreadySeen = false;
			foreach($connSet as $conn) {
				foreach($conn as $port) {
					if($port['objID'] == $remoteObjID and $port['objFace'] == $remoteObjFace and $port['objDepth'] == $remoteObjDepth and $port['objPort'] == $remoteObjPort) {
						$alreadySeen = true;
					}
				}
			}
			
			if(!$alreadySeen) {
				error_log('Debug: '.$remoteObjID.'-'.$remoteObjFace.'-'.$remoteObjDepth.'-'.$remoteObjPort);
				crawlConnSet($qls, $remoteObjID, $remoteObjFace, $remoteObjDepth, $remoteObjPort, $connSetID, $connSet);
			}
			
		}
		
		
	}
	
	return $connSet;
}

?>
