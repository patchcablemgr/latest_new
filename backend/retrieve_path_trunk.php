<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');
require_once '../includes/path_functions.php';

//[0] = element type
//[1] = element ID
//[2] = element face
//[3] = element depth
//[4] = port index

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once('../includes/Validate.class.php');
	$validate = new Validate($qls);
	$validate->returnData['success'] = array();
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate);
	
	if (!count($validate->returnData['error'])){
		$children = array();
		$selected = '';
		$valueArray = explode('-', $data['value']);
		$elementType = $valueArray[0];
		$elementID = $valueArray[1];
		$elementFace = $valueArray[2];
		$elementDepth = $valueArray[3];
		$elementPortIndex = $valueArray[4];
		$objectID = $data['objID'];
		$objectFace = $data['objFace'];
		$partitionDepth = $data['partitionDepth'];
		$action = $data['action'];
		
		$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $objectID)));
		$object = $qls->SQL->fetch_assoc($query);
		
		if($action == 'SELECT'){
			// Clear path
			if($elementID == 0) {
				$query = $qls->SQL->select('*', 'app_object_peer', array('a_id' => array('=', $objectID), 'OR', 'b_id' => array('=', $objectID)));
				$qls->SQL->delete('app_object_peer', array('a_id' => array('=', $objectID), 'OR', 'b_id' => array('=', $objectID)));
				$children = buildLocation('#', $qls);
				$resultData = array('selected' => 'clear', 'children' => $children);
				array_push($validate->returnData['success'], $resultData);
					
			// Location selected
			} else if($elementType == 0) {
				$children = buildLocation($elementID, $qls);
				$resultData = array('selected' => $selected, 'children' => $children);
				array_push($validate->returnData['success'], $resultData);
					
			// Cabinet selected
			} else if($elementType == 1) {
				$children = buildObjects($elementID, $object, $objectFace, $partitionDepth, $qls);
				$resultData = array('selected' => $selected, 'children' => $children);
				array_push($validate->returnData['success'], $resultData);
			
			// Object selected
			} else if($elementType == 2) {
				$children = buildPartitions($elementID, $elementFace, $object, $objectFace, $partitionDepth, $qls);
				$resultData = array('selected' => $selected, 'children' => $children);
				array_push($validate->returnData['success'], $resultData);
				
			// Partition selected
			} else if($elementType == 3) {
				$query = $qls->SQL->select('partitionFunction', 'app_object_compatibility', array('template_id' => array('=', $object['template_id'])));
				$partitionFunction = $qls->SQL->fetch_assoc($query);
				$objectEndpoint = $partitionFunction['partitionFunction'] == 'Endpoint' ? 1 : 0;
				
				$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $elementID)));
				$element = $qls->SQL->fetch_assoc($query);
				
				$query = $qls->SQL->select('partitionFunction', 'app_object_compatibility', array('template_id' => array('=', $element['template_id'])));
				$partitionFunction = $qls->SQL->fetch_assoc($query);
				$elementEndpoint = $partitionFunction['partitionFunction'] == 'Endpoint' ? 1 : 0;
				
				$qls->SQL->delete('app_object_peer', array('a_id' => array('=', $objectID), 'AND', 'a_face' => array('=', $objectFace), 'AND', 'a_depth' => array('=', $partitionDepth)));
				$qls->SQL->delete('app_object_peer', array('b_id' => array('=', $objectID), 'AND', 'b_face' => array('=', $objectFace), 'AND', 'b_depth' => array('=', $partitionDepth)));
				$qls->SQL->delete('app_object_peer', array('a_id' => array('=', $elementID), 'AND', 'a_face' => array('=', $elementFace), 'AND', 'a_depth' => array('=', $elementDepth)));
				$qls->SQL->delete('app_object_peer', array('b_id' => array('=', $elementID), 'AND', 'b_face' => array('=', $elementFace), 'AND', 'b_depth' => array('=', $elementDepth)));
				$qls->SQL->insert(
					'app_object_peer',
					array(
						'a_id',
						'a_face',
						'a_depth',
						'a_endpoint',
						'b_id',
						'b_face',
						'b_depth',
						'b_endpoint'
					),
					array(
						$objectID,
						$objectFace,
						$partitionDepth,
						$objectEndpoint,
						$elementID,
						$elementFace,
						$elementDepth,
						$elementEndpoint
					)
				);
				array_push($validate->returnData['success'], 'FIN');
			}
				
		} else if($action == 'GET') {
			$queryString = '(a_id = '.$objectID.' AND a_face = '.$objectFace.' AND a_depth = '.$partitionDepth.') OR (b_id = '.$objectID.' AND b_face = '.$objectFace.' AND b_depth = '.$partitionDepth.')';
			//$query = $qls->SQL->select('*', 'app_object_peer', array('a_id' => array('=', $objectID), 'OR', 'b_id' => array('=', $objectID)));
			$query = $qls->SQL->select('*', 'app_object_peer', $queryString);
			$peerRecord = $qls->SQL->fetch_assoc($query);
			if(!count($peerRecord)) {
				$children = buildLocation('#', $qls);
				$resultData = array('selected' => $selected, 'children' => $children);
				array_push($validate->returnData['success'], $resultData);
			} else {
				$peerPrefix = $peerRecord['a_id'] == $objectID ? 'b' : 'a';
				$objectPrefix = $peerRecord['a_id'] == $objectID ? 'a' : 'b';
				
				$peerID = $peerRecord[$peerPrefix.'_id'];
				$peerFace = $peerRecord[$peerPrefix.'_face'];
				$peerDepth = $peerRecord[$peerPrefix.'_depth'];
				
				$objectFace = $peerRecord[$objectPrefix.'_face'];
				$objectDepth = $peerRecord[$objectPrefix.'_depth'];
				
				$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $peerID)));
				$peer = $qls->SQL->fetch_assoc($query);
				
				$path = buildPath($peer, $peerFace, $peerDepth, $object, $objectFace, $objectDepth, $qls);
				
				$validate->returnData['success'] = $path;
			}
			$validate->returnData['pathArray'] = buildPathArray($validate->returnData['success']);
		}
	}
	//echo json_encode($return);
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	if($data['action'] != 'GET') {
		//Validate elementType
		$validate->validateElementValue($data['value']);
	}
	
	//Validate objectID
	$validate->validateObjectID($data['objID']);
	
	return;
}
?>
