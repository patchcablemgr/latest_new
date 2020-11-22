<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('operator.php');
require_once '../includes/path_functions.php';

//[0] = element type
//[1] = element ID
//[2] = element face
//[3] = element depth
//[4] = port index

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	$return = array(
		'error' => '',
		'result' => array()
	);
	
	$data = $_POST;
	$return['error'] = validate($data, $qls);
	
	if (count($return['error']) == 0){
		$children = array();
		$selected = '';
		$valueArray = explode('-', $data['value']);
		$elementType = $valueArray[0];
		$elementID = $valueArray[1];
		$elementFace = $valueArray[2];
		$elementDepth = $valueArray[3];
		$elementPortIndex = $valueArray[4];
		$connectorID = $data['connectorID'];
		$cableEnd = $data['cableEnd'];
		$connectorPathAttribute = $cableEnd == 'a' ? 'a_path' : 'b_path';
		$connectorAttributePrefix = $cableEnd == 'a' ? 'a' : 'b';
		$connectorIDAttribute = $cableEnd == 'a' ? 'a_id' : 'b_id';
		$connectorObjectIDAttribute = $cableEnd == 'a' ? 'a_object_id' : 'b_object_id';
		$connectorPortIDAttribute = $cableEnd == 'a' ? 'a_port_id' : 'b_port_id';
		$connectorRootIDAttribute = $cableEnd == 'a' ? 'a_root_id' : 'b_root_id';
		$connectorFaceAttribute = $cableEnd == 'a' ? 'a_object_face' : 'b_object_face';
		$connectorDepthAttribute = $cableEnd == 'a' ? 'a_object_depth' : 'b_object_depth';
		$action = $data['action'];
		
		$query = $qls->SQL->select('*', 'app_inventory', array($connectorIDAttribute => array('=', $connectorID)));
		$cable = $qls->SQL->fetch_assoc($query);
		
		if($action == 'SELECT'){
			// Clear path
			if($elementID == 0) {
				$qls->SQL->update(
					'app_inventory',
					array(
						$connectorAttributePrefix.'_object_id' => 0,
						$connectorAttributePrefix.'_port_id' => 0,
						$connectorAttributePrefix.'_object_face' => 0,
						$connectorAttributePrefix.'_object_depth' => 0
					),
					array(
						$connectorIDAttribute => array('=', $cable[$connectorIDAttribute])
					)
				);
				$children = buildLocation('#', $qls);
				$resultData = array('selected' => 'clear', 'children' => $children);
				array_push($return['result'], $resultData);
					
			// Location selected
			} else if($elementType == 0) {
				$children = buildLocation($elementID, $qls);
				$resultData = array('selected' => $selected, 'children' => $children);
				array_push($return['result'], $resultData);
					
			// Cabinet selected
			} else if($elementType == 1) {
				$children = buildObjectsConnector($elementID, $cable, $connectorAttributePrefix, $qls);
				$resultData = array('selected' => $selected, 'children' => $children);
				array_push($return['result'], $resultData);
			
			// Object selected
			} else if($elementType == 2) {
				$children = buildPorts($elementID, $elementFace, $cable, $connectorAttributePrefix, $qls);
				$resultData = array('selected' => $selected, 'children' => $children);
				array_push($return['result'], $resultData);
				
			// Port selected
			} else if($elementType == 4) {
				$inverseConnectorAttributePrefix = $connectorAttributePrefix == 'a' ? 'b' : 'a';
				// 'b', cable(17), 34, 0, 1, 0
				$peerID = $cable[$inverseConnectorAttributePrefix.'_object_id'];
				$peerFace = $cable[$inverseConnectorAttributePrefix.'_object_face'];
				$peerDepth = $cable[$inverseConnectorAttributePrefix.'_object_depth'];
				$peerPort = $cable[$inverseConnectorAttributePrefix.'_port_id'];
				if(loopDetected($qls, $peerID, $peerFace, $peerDepth, $peerPort, $elementID, $elementFace, $elementDepth, $elementPortIndex)) {
					array_push($return['error'], 'Loop detected.');
				} else {
					$qls->SQL->update(
						'app_inventory',
						array(
							$connectorAttributePrefix.'_object_id' => $elementID,
							$connectorAttributePrefix.'_port_id' => $elementPortIndex,
							$connectorAttributePrefix.'_object_face' => $elementFace,
							$connectorAttributePrefix.'_object_depth' => $elementDepth
						),
						array(
							$connectorAttributePrefix.'_id' => array('=', $cable[$connectorIDAttribute])
						)
					);
					$qls->SQL->delete(
						'app_populated_port',
						array(
							'object_id' => array('=', $elementID),
							'AND',
							'object_face' => array('=', $elementFace),
							'AND',
							'object_depth' => array('=', $elementDepth),
							'AND',
							'port_id' => array('=', $elementPortIndex)
						)
					);
					array_push($return['result'], 'FIN');
				}
			}
				
		} else if($action == 'GET') {
			if($cable[$connectorObjectIDAttribute] == 0) {
				$children = buildLocation($elementID, $qls);
				$resultData = array('selected' => $selected, 'children' => $children);
				array_push($return['result'], $resultData);
			} else {
				$path = buildConnectorPath($cable, $connectorAttributePrefix, $qls);
				
				$return['result'] = $path;
			}
		}
	}
	echo json_encode($return);
}

function validate($data, &$qls){
	$elementTypeArray = array('initial', 'location', 'cabinet', 'Connectable', 'Enclosure', 'Insert', 'port');
	$error = [];
	
	//Validate elementType
	if (!isset($data['value'])){
		array_push($error, array('alert' => 'Error: Element data is required.'));
	} else {
		$dataValue = explode('-', $data['value']);
		if (count($dataValue) != 5){
			array_push($error, array('alert' => 'Error: Invalid data value.'));
		} else {
			if (!preg_match('/^[0-9]+$/', $dataValue[0])){
				array_push($error, array('alert' => 'Error: Invalid element type.'));
			}
			if (!preg_match('/^[#0-9]+$/', $dataValue[1])){
				array_push($error, array('alert' => 'Error: Invalid element ID.'));
			}
			if (!preg_match('/^[01]$/', $dataValue[2])){
				array_push($error, array('alert' => 'Error: Invalid element face.'));
			}
			if (!preg_match('/^[0-9]+$/', $dataValue[3])){
				array_push($error, array('alert' => 'Error: Invalid partition ID.'));
			}
			if (!preg_match('/^[0-9]+$/', $dataValue[4])){
				array_push($error, array('alert' => 'Error: Invalid port ID.'));
			}
		}
	}
	
	//Validate cableEnd
	if (!isset($data['cableEnd'])){
		array_push($error, array('alert' => 'Error: Cable end value is required.'));
	} else {
		if (!preg_match('/^[ab]$/', $data['cableEnd'])){
			array_push($error, array('alert' => 'Error: Invalid cable end value.'));
		}
	}
	
	//Validate cableID
	if (!isset($data['connectorID'])){
		array_push($error, array('alert' => 'Error: Connector ID is required.'));
	} else {
		if (!preg_match('/^[0-9]+$/', $data['connectorID'])){
			array_push($error, array('alert' => 'Error: Invalid connector ID.'));
		}
	}
	
	return $error;
}

?>
