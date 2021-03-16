<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('operator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once '../includes/Validate.class.php';
	$validate = new Validate($qls);
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error']) and !$validate->returnData['confirm']){
		$cableProperty = $data['property'];
		switch($cableProperty){
			case 'connectorType':
				$connectorTypeID = $data['value'];
				$cableEndID = $data['id'];
				$query = $qls->SQL->select('*', 'app_inventory', array('a_id' => array('=', $cableEndID), 'OR', 'b_id' => array('=', $cableEndID)));
				$cable = $qls->SQL->fetch_assoc($query);
				$cableAttrPrefix = $cable['a_id'] == $cableEndID ? 'a' : 'b';
				
				$qls->SQL->update('app_inventory', array($cableAttrPrefix.'_connector' => $connectorTypeID), array($cableAttrPrefix.'_id' => array('=', $cableEndID)));
				break;
				
			case 'cableLength':
				$cableLength = $data['value'];
				$cableID = $data['id'];
				
				$query = $qls->SQL->select('*', 'app_inventory', array('id' => array('=', $cableID)));
				$cable = $qls->SQL->fetch_assoc($query);
				$mediaTypeID = $cable['mediaType'];
				
				$query = $qls->SQL->select('*', 'shared_mediaType', array('value' => array('=', $mediaTypeID)));
				$mediaType = $cable = $qls->SQL->fetch_assoc($query);
				$mediaCategoryTypeID = $mediaType['category_type_id'];
				
				if($mediaCategoryTypeID == 1) {
					$cableLength = $qls->App->convertFeetToMillimeters($cableLength);
				} else {
					$cableLength = $qls->App->convertMetersToMillimeters($cableLength);
				}
				
				$qls->SQL->update('app_inventory', array('length' => $cableLength), array('id' => array('=', $cableID)));
				break;
				
			case 'cableMediaType':
				$mediaTypeID = $data['value'];
				$cableID = $data['id'];
				
				$qls->SQL->update('app_inventory', array('mediaType' => $mediaTypeID), array('id' => array('=', $cableID)));
				$query = $qls->SQL->select('*', 'shared_mediaType', array('value' => array('=', $mediaTypeID)));
				$mediaType = $qls->SQL->fetch_assoc($query);
				$query = $qls->SQL->select('*', 'shared_mediaCategoryType', array('value' => array('=', $mediaType['category_id'])));
				$mediaCategoryType = $qls->SQL->fetch_assoc($query);;
				$validate->returnData['success'] = $mediaCategoryType['unit_of_length'];
				break;
				
			case 'cableEditable':
				$cableID = $data['id'];
				
				$qls->SQL->update('app_inventory', array('editable' => 0), array('id' => array('=', $cableID)));
				break;
				
			case 'connectionScan':
				$validate->returnData['success'] = array();
				$value = $data['value'];
				if($value == 'clear') {
					$elementID = $elementFace = $elementDepth = $elementPort = 0;
				} else {
					$valueArray = explode('-', $value);
					$elementID = (int)$valueArray[1];
					$elementFace = (int)$valueArray[2];
					$elementDepth = (int)$valueArray[3];
					$elementPort = (int)$valueArray[4];
				}
				$connectorID = $data['connectorID'];
				$cable = $qls->App->inventoryByIDArray[$connectorID];
				$peerID = $cable['remote_object_id'];
				$peerFace = $cable['remote_object_face'];
				$peerDepth = $cable['remote_object_depth'];
				$peerPort = $cable['remote_object_port'];
				$localAttrPrefix = $cable['localAttrPrefix'];
				
				if($qls->App->loopDetected2($peerID, $peerFace, $peerDepth, $peerPort, $elementID, $elementFace, $elementDepth, $elementPort)) {
					$errMsg = 'Loop detected.';
					array_push($validate->returnData['error'], $errMsg);
				} else {
					
					// Remove any populated port entries that may exist
					$qls->SQL->delete(
						'app_populated_port',
						array(
							'object_id' => array('=', $elementID),
							'AND',
							'object_face' => array('=', $elementFace),
							'AND',
							'object_depth' => array('=', $elementDepth),
							'AND',
							'port_id' => array('=', $elementPort)
						)
					);
					
					// Clear any inventory entries
					if (isset($qls->App->inventoryArray[$elementID][$elementFace][$elementDepth][$elementPort])) {
						foreach($qls->App->inventoryArray[$elementID][$elementFace][$elementDepth][$elementPort] as $inventoryEntry) {
							$rowID = $inventoryEntry['rowID'];
							$localAttrPrefix = $inventoryEntry['localAttrPrefix'];
							
							if ($inventoryEntry['localEndID'] == 0) {
								
								// Found entry is not a managed cable... delete
								$qls->SQL->delete('app_inventory', array('id' => array('=', $rowID)));
							} else {
								
								// Found entry is a managed cable... zeroize
								$qls->SQL->update(
									'app_inventory',
									array(
										$localAttrPrefix.'_object_id' => 0,
										$localAttrPrefix.'_port_id' => 0,
										$localAttrPrefix.'_object_face' => 0,
										$localAttrPrefix.'_object_depth' => 0
									),
									array(
										'id' => array('=', $rowID)
									)
								);
							}
						}
					}
					
					// Update connection in database
					$qls->SQL->update(
						'app_inventory',
						array(
							$localAttrPrefix.'_object_id' => $elementID,
							$localAttrPrefix.'_port_id' => $elementPort,
							$localAttrPrefix.'_object_face' => $elementFace,
							$localAttrPrefix.'_object_depth' => $elementDepth
						),
						array(
							'id' => array('=', $cable['rowID'])
						)
					);
					
					$qls->App->inventoryByIDArray[$connectorID]['local_object_id'] = $elementID;
					$qls->App->inventoryByIDArray[$connectorID]['local_object_face'] = $elementFace;
					$qls->App->inventoryByIDArray[$connectorID]['local_object_depth'] = $elementDepth;
					$qls->App->inventoryByIDArray[$connectorID]['local_object_port'] = $elementPort;
					$cable = $qls->App->inventoryByIDArray[$connectorID];
					
					// Retrieve connector path
					$connectorFlatPath = $qls->App->buildConnectorFlatPath($cable, 'local');
					$validate->returnData['success']['connectorFlatPath'] = $connectorFlatPath;
				}
				break;
			
			case 'connectionExploreClear':
			
				$validate->returnData['success'] = array();
			
				// Retrieve object data
				$objID = $data['objID'];
				$objFace = $data['objFace'];
				$objDepth = $data['objDepth'];
				$objPort = $data['objPort'];
				if(isset($qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort])) {
					$deleteRowArray = array();
					$port = $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort];
					foreach($port as $connection) {
						
						$peerID = $connection['id'];
						$peerFace = $connection['face'];
						$peerDepth = $connection['depth'];
						$peerPort = $connection['port'];
						// Account for remote port being breakout cable
						if(isset($qls->App->inventoryArray[$peerID][$peerFace][$peerDepth][$peerPort])) {
							$peerPort = $qls->App->inventoryArray[$peerID][$peerFace][$peerDepth][$peerPort];
							foreach($peerPort as $peerConnection) {
								array_push($deleteRowArray, $peerConnection['rowID']);
							}
						} else {
							array_push($deleteRowArray, $peerConnection['rowID']);
						}
					}
					
					// Delete inventory entries
					foreach($deleteRowArray as $rowID) {
						if($connection['localEndID'] or $connection['remoteEndID']) {
							clearTableInventory($qls, $connection['localAttrPrefix'], $rowID);
						} else {
							$qls->SQL->delete('app_inventory', array('id' => array('=', $rowID)));
						}
					}
				}
				
				// Clear populated port entry
				clearTablePopulated($qls, $objID, $objFace, $objDepth, $objPort);
				
				// Log history
				$localPort = $qls->App->generateObjectPortName($objID, $objFace, $objDepth, $objPort);
				$port = $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort];
				$remotePortArray = array();
				foreach($port as $connection) {
					$remoteObjID = $connection['id'];
					$remoteObjFace = $connection['face'];
					$remoteObjDepth = $connection['depth'];
					$remoteObjPort = $connection['port'];
					$remotePortName = $qls->App->generateObjectPortName($remoteObjID, $remoteObjFace, $remoteObjDepth, $remoteObjPort);
					array_push($remotePortArray, $remotePortName);
				}
				$remotePortString = implode('<br>', $remotePortArray);
				$actionString = 'Deleted connection: <strong>'.$localPort.'</strong> to <strong>'.$remotePortString.'</strong>';
				error_log('Debug (actionString): '.$actionString);
				$qls->App->logAction(3, 3, $actionString);
			
				break;
			
			case 'connectionExplore':
				$validate->returnData['success'] = array();
				$value = $data['value'];
				$clear = $value == 'clear' ? true : false;
				
				foreach($value as $peerPortString) {
				
					$peerPortArray = explode('-', $peerPortString);
				
					$elementID = $peerPortArray[1];
					$elementFace = $peerPortArray[2];
					$elementDepth = $peerPortArray[3];
					$elementPort = $peerPortArray[4];
					$element = (isset($qls->App->inventoryArray[$elementID][$elementFace][$elementDepth][$elementPort])) ? $qls->App->inventoryArray[$elementID][$elementFace][$elementDepth][$elementPort] : false;
					
					$objID = $data['objID'];
					$objFace = $data['objFace'];
					$objDepth = $data['objDepth'];
					$objPort = $data['objPort'];
					$obj = (isset($qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort])) ? $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort] : false;
					
					// Clear trunk if this is a trunked floorplan object
					$objIDArray = array($elementID, $objID);
					foreach($objIDArray as $objID) {
						$templateID = $qls->App->objectArray[$objID]['template_id'];
						$templateType = $qls->App->templateArray[$templateID]['templateType'];
						if(isset($qls->App->floorplanObjDetails[$templateType])) {
							$templateFunction = $qls->App->templateArray[$templateID]['templateFunction'];
							if($templateFunction == 'Endpoint') {
								if(isset($qls->App->peerArrayWalljack[$objID])) {
									foreach($qls->App->peerArrayWalljack[$objID] as $peerEntry) {
										$rowID = $peerEntry['rowID'];
										$qls->SQL->delete('app_object_peer', array('id' => array('=', $rowID)));
									}
								}
							}
						}
					}
					
					// Clear existing connections
					if($obj and $element) {
						
						foreach($obj as $objConnection) {
							$objRowID = $objConnection['rowID'];
							foreach($element as $elementConnection) {
								$elementRowID = $elementConnection['rowID'];
								
								// Are the ports connected to each other?
								if($objConnection['rowID'] == $elementConnection['rowID']) {
									if($objConnection['localEndID'] or $objConnection['remoteEndID']) {
										clearTableInventory($qls, 'a', $objRowID);
										clearTableInventory($qls, 'b', $objRowID);
									} else {
										$qls->SQL->delete('app_inventory', array('id' => array('=', $objRowID)));
									}
								} else {
									if($objConnection['localEndID'] or $objConnection['remoteEndID']) {
										clearTableInventory($qls, $objConnection['localAttrPrefix'], $objRowID);
									} else {
										$qls->SQL->delete('app_inventory', array('id' => array('=', $objRowID)));
									}
									
									if($elementConnection['localEndID'] or $elementConnection['remoteEndID']) {
										clearTableInventory($qls, $elementConnection['localAttrPrefix'], $elementRowID);
									} else {
										$qls->SQL->delete('app_inventory', array('id' => array('=', $elementRowID)));
									}
								}
							}
						}
					} else if($obj) {
						
						foreach($obj as $objConnection) {
							$objRowID = $objConnection['rowID'];
							
							if($objConnection['localEndID'] or $objConnection['remoteEndID']) {
								clearTableInventory($qls, $objConnection['localAttrPrefix'], $objRowID);
							} else {
								$qls->SQL->delete('app_inventory', array('id' => array('=', $objRowID)));
							}
						}
						
					} else if($element) {
						
						foreach($element as $elementConnection) {
							$elementRowID = $elementConnection['rowID'];
							
							if($elementConnection['localEndID'] or $elementConnection['remoteEndID']) {
								clearTableInventory($qls, $elementConnection['localAttrPrefix'], $elementRowID);
							} else {
								$qls->SQL->delete('app_inventory', array('id' => array('=', $elementRowID)));
							}
						}
					}
					
					// Clear populated port entry
					clearTablePopulated($qls, $objID, $objFace, $objDepth, $objPort);
					clearTablePopulated($qls, $elementID, $elementFace, $elementDepth, $elementPort);
					
					// Insert new connection
					insertTableInventory($qls, $objID, $objFace, $objDepth, $objPort, $elementID, $elementFace, $elementDepth, $elementPort);
						
					// Log history
					$localPort = $qls->App->generateObjectPortName($objID, $objFace, $objDepth, $objPort);
					$remotePort = $qls->App->generateObjectPortName($elementID, $elementFace, $elementDepth, $elementPort);
					$actionString = 'Added connection: <strong>'.$localPort.'</strong> to <strong>'.$remotePort.'</strong>';
					$qls->App->logAction(3, 1, $actionString);
				
				}
				
				break;
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$propertiesArray = array('connectorType', 'cableLength', 'cableMediaType', 'cableEditable', 'connectionScan', 'connectionExplore', 'connectionExploreClear');
	
	//Validate property
	if($validate->validateInArray($data['property'], $propertiesArray, 'property type')) {
	
		if ($data['property'] == 'cableLength') {
			//Validate cable ID
			$validate->validateCableID($data['id'], $qls);
			
			//Validate cable length
			$validate->validateCableLength($data['value']);
			
		} else if ($data['property'] == 'cableMediaType') {
			//Validate cable ID
			$validate->validateCableID($data['id'], $qls);
		
			//Validate cable media type
			$validate->validateCableMediaType($data['value'], $qls);
			
		} else if ($data['property'] == 'connectorType') {
			//Validate connector ID
			$validate->validateConnectorID($data['id'], $qls);
			
			//Validate connector type
			$validate->validateCableConnectorType($data['value'], $qls);
			
		} else if ($data['property'] == 'connectionScan') {
			
			$localPortArray = explode('-', $data['value']);
			$localID = $localPortArray[1];
			$localFace = $localPortArray[2];
			$localDepth = $localPortArray[3];
			$localPort = $localPortArray[4];
			
			$connectorID = $data['connectorID'];
			$cable = $qls->App->inventoryByIDArray[$connectorID];
			$remoteID = $cable['remote_object_id'];
			$remoteFace = $cable['remote_object_face'];
			$remoteDepth = $cable['remote_object_depth'];
			$remotePort = $cable['remote_object_port'];
			
			if($remoteID == $localID and $remoteFace == $localFace and $remoteDepth == $localDepth and $remotePort == $localPort) {
				$errMsg = 'Cannot connect port to itself.';
				array_push($validate->returnData['error'], $errMsg);
			}
			
			if(!isset($data['confirmed'])) {
				if (isset($qls->App->inventoryArray[$localID][$localFace][$localDepth][$localPort])) {
					$validate->returnData['data']['confirmMsg'] = 'Overwrite existing connection?';
					$validate->returnData['confirm'] = true;
				}
			}
			
		} else if ($data['property'] == 'connectionExploreClear') {
			
		} else if ($data['property'] == 'connectionExplore') {
			
			$remotePortDataArray = $data['value'];
			$remotePortArray = array();
			if(is_array($remotePortDataArray)) {
				foreach($remotePortDataArray as $remotePortDataString) {
					$remotePortData = explode('-', $remotePortDataString);
					$workingArray = array(
						'remoteID' => $remotePortData[1],
						'remoteFace' => $remotePortData[2],
						'remoteDepth' => $remotePortData[3],
						'remotePort' => $remotePortData[4]
					);
					array_push($remotePortArray, $workingArray);
				}
			}
			
			$localID = $data['objID'];
			$localFace = $data['objFace'];
			$localDepth = $data['objDepth'];
			$localPort = $data['objPort'];
			
			foreach($remotePortArray as $remotePortData) {
				
				$remoteID = $remotePortData['remoteID'];
				$remoteFace = $remotePortData['remoteFace'];
				$remoteDepth = $remotePortData['remoteDepth'];
				$remotePort = $remotePortData['remotePort'];
				
				// Validate port is not connected to itself
				if($remoteID == $localID and $remoteFace == $localFace and $remoteDepth == $localDepth and $remotePort == $localPort) {
					$errMsg = 'Cannot connect port to itself.';
					array_push($validate->returnData['error'], $errMsg);
				}
			
				// Validate connection peers are not endpoints which are trunked
				$connectionPeerArray = array(
					array($localID, $localFace, $localDepth, $localPort),
					array($remoteID, $remoteFace, $remoteDepth, $remotePort)
				);
				$validate->validateTrunkedEndpoint($connectionPeerArray);
				
				// Validate no loops will result
				if($qls->App->loopDetected2($localID, $localFace, $localDepth, $localPort, $remoteID, $remoteFace, $remoteDepth, $remotePort)) {
					$errMsg = 'Loop detected.';
					array_push($validate->returnData['error'], $errMsg);
				}
				
				// Does this action need to be confirmed?
				if(!isset($data['confirmed'])) {
					if (isset($qls->App->inventoryArray[$remoteID][$remoteFace][$remoteDepth][$remotePort])) {
						$validate->returnData['data']['confirmMsg'] = 'Overwrite existing connection?';
						$validate->returnData['confirm'] = true;
					}
				}
				
				// Validate entitlement
				$query = $qls->SQL->select('id', 'app_inventory', array('a_object_id' => array('>', 0), 'AND', 'b_object_id' => array('>', 0)));
				$conNum = $qls->SQL->num_rows($query) + 1;
				
				if(!$qls->App->checkEntitlement('connection', $conNum)) {
					$errMsg = 'Exceeded entitled connection count.';
					array_push($validate->returnData['error'], $errMsg);
				}
			}
		}
	}
	return;
}

function clearTableInventory(&$qls, $attr, $id){
	$qls->SQL->update(
		'app_inventory',
		array(
			$attr.'_object_id' => 0,
			$attr.'_object_face' => 0,
			$attr.'_object_depth' => 0,
			$attr.'_port_id' => 0
		),
		array(
			'id' => array('=', $id)
		)
	);
}

function insertTableInventory(&$qls, $objID, $objFace, $objDepth, $objPort, $elementID, $elementFace, $elementDepth, $elementPort){
	$qls->SQL->insert(
		'app_inventory',
		array(
			'a_object_id',
			'a_object_face',
			'a_object_depth',
			'a_port_id',
			'b_object_id',
			'b_object_face',
			'b_object_depth',
			'b_port_id',
			'length',
			'editable',
			'order_id',
			'active'
		),
		array(
			$objID,
			$objFace,
			$objDepth,
			$objPort,
			$elementID,
			$elementFace,
			$elementDepth,
			$elementPort,
			0,
			0,
			0,
			0
		)
	);
}

function clearTablePopulated(&$qls, $objID, $objFace, $objDepth, $objPort){
	$qls->SQL->delete(
		'app_populated_port',
		array(
			'object_id' => array('=', $objID),
			'AND',
			'object_face' => array('=', $objFace),
			'AND',
			'object_depth' => array('=', $objDepth),
			'AND',
			'port_id' => array('=', $objPort)
		)
	);
}
?>
