<?php

// pathArray contains all necessary path data
$pathArray = array();

if($connectorCode39) {
	$managedCableID = base_convert($connectorCode39, 36, 10);
	$rootCable2 = $qls->App->inventoryByIDArray[$managedCableID];
	
	$objID2 = $rootCable2['local_object_id'];
	$objFace2 = $rootCable2['local_object_face'];
	$objDepth2 = $rootCable2['local_object_depth'];
	$objPort2 = $rootCable2['local_object_port'];
}

$selectedObjID2 = $objID;
$selectedObjFace2 = $objFace;
$selectedObjDepth2 = $objDepth;
$selectedObjPort2 = $objPort;

// Retrieve initial connection set
$selected = true;
$connSet = crawlConn($qls, $selected, $selectedObjID2, $selectedObjFace2, $selectedObjDepth2, $selectedObjPort2);
detectDivergence($connSet[0]);
detectDivergence($connSet[1]);
//$connSet[0]['selected'] = true;
array_push($pathArray, $connSet);

for($direction=0; $direction<2; $direction++) {

	do {
		
		// Set path array pointer
		// 0 for up, -1 for down
		$pathArrayPointer = ($direction == 0) ? 0 : count($pathArray)-1;
		
		error_log('Debug (pathArray): '.json_encode($pathArray));
		error_log('Debug (pathArrayPointer): '.$pathArrayPointer);
		error_log('Debug (direction): '.$direction);
		error_log('Debug (crawlTrunk data): '.json_encode($pathArray[$pathArrayPointer][$direction]));
		
		// Get port trunk peer
		$trunkSet = crawlTrunk($qls, $pathArray[$pathArrayPointer][$direction]);
		detectDivergence($trunkSet);
		$trunkFound = (count($trunkSet)) ? true : false;
		
		// Find connections for each trunked port
		$workingConnSet = array(array(),array());
		foreach($trunkSet as $port) {
			
			// Store port info
			$selectedObjID2 = $port['objID'];
			$selectedObjFace2 = $port['objFace'];
			$selectedObjDepth2 = $port['objDepth'];
			$selectedObjPort2 = $port['objPort'];
			
			// Find connections
			$selected = false;
			$connSet = crawlConn($qls, $selected, $selectedObjID2, $selectedObjFace2, $selectedObjDepth2, $selectedObjPort2);
			detectDivergence($connSet[0]);
			detectDivergence($connSet[1]);
			
			// Add ports to workingConnSet
			if($direction == 0) {
				foreach($connSet[0] as $port) {
					array_push($workingConnSet[1], $port);
				}
				foreach($connSet[1] as $port) {
					array_push($workingConnSet[0], $port);
				}
			} else {
				foreach($connSet[0] as $port) {
					array_push($workingConnSet[0], $port);
				}
				foreach($connSet[1] as $port) {
					array_push($workingConnSet[1], $port);
				}

			}
		}
		
		// Add connection set to appropriate end of pathArray
		if($trunkFound) {
			if($direction == 0) {
				array_unshift($pathArray, $workingConnSet);
			} else {
				array_push($pathArray, $workingConnSet);
			}
		}
	} while($trunkFound);
}

error_log('Debug (FINAL pathArray): '.json_encode($pathArray));

function crawlTrunk(&$qls, $portSet) {
	
	$trunkSet = array();
		
	// Loop over each port of $conn
	foreach($portSet as $portSetID => $port) {
		
		// Gather port data
		$objID = $port['objID'];
		$objFace = $port['objFace'];
		$objDepth = $port['objDepth'];
		$objPort = $port['objPort'];
		
		// Gather trunk peer data
		if(isset($qls->App->peerArray[$objID][$objFace][$objDepth])) {
			
			// Gather trunk peer object
			$peer = $qls->App->peerArray[$objID][$objFace][$objDepth];
			
			// Gather trunk peer data
			$peerObjID = $peer['peerID'];
			$peerObjFace = $peer['peerFace'];
			$peerObjDepth = $peer['peerDepth'];
			$peerObjPort = $objPort;
			
			// Create a working array for cleanliness
			$workingArray = array(
				'objID' => $peerObjID,
				'objFace' => $peerObjFace,
				'objDepth' => $peerObjDepth,
				'objPort' => $peerObjPort
			);
			
			// Store trunk data
			$trunkSet[$portSetID] = $workingArray;
		}
	}
	
	return $trunkSet;
}

function crawlConn(&$qls, $selected, $objID, $objFace, $objDepth, $objPort, &$connSet=array(array(),array()), $connSetID=0) {
	
	$managedCableID = (isset($qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort])) ? $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort][0]['localEndID'] : 0;
	if($managedCableID != 0) {
		$managedCable = $qls->App->inventoryByIDArray[$managedCableID];
		$managedCableMediaTypeID = $managedCable['mediaType'];
		$managedCableLength = $managedCable['length'];
		$includeUnit = true;
		$length = $qls->App->calculateCableLength($managedCableMediaTypeID, $managedCableLength, $includeUnit);
		$mediaTypeID = $managedCable['mediaType'];
		$connectorTypeID = $managedCable['localConnector'];
	} else {
		$length = 'Unk. Length';
		$mediaTypeID = false;
		$connectorTypeID = false;
	}
	
	// Store port details
	$workingArray = array(
		'objID' => $objID,
		'objFace' => $objFace,
		'objDepth' => $objDepth,
		'objPort' => $objPort,
		'selected' => $selected,
		'length' => $length,
		'mediaTypeID' => $mediaTypeID,
		'connectorTypeID' => $connectorTypeID
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
			$managedCableID = $connection['localEndID'];
			
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
				$selected = false;
				crawlConn($qls, $selected, $remoteObjID, $remoteObjFace, $remoteObjDepth, $remoteObjPort, $connSet, $connSetID);
			}
		}
	}
	
	return $connSet;
}

function detectDivergence(&$dataSet) {
	
	$pathDiverges = false;
	
	// Detect path divergence
	foreach($dataSet as $portIndex => $port) {
		
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
			if($portObjParentID != $baselineParentID) {
				
				// Flag this path as divergent
				$pathDiverges = true;
				
				// Remove divergent connection
				unset($dataSet[$portIndex]);
			}
		}
	}
	
	return $pathDiverges;
}

?>
