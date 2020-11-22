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
	require_once '../includes/Validate.class.php';
	
	$validate = new Validate($qls);
	$validate->returnData['success'] = array();
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		$children = array();
		$selected = '';
		$valueArray = explode('-', $data['value']);
		$elementType = $valueArray[0];
		$elementID = $valueArray[1];
		$elementFace = $valueArray[2];
		$elementDepth = $valueArray[3];
		$elementPortIndex = $valueArray[4];
		
		// Clear path
		if($elementID == 0) {
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
			$clickedObjID = $data['clickedObjID'];
			$clickedObjFace = $data['clickedObjFace'];
			$clickedObjDepth = $data['clickedObjDepth'];
			$children = buildObjectsPathFinder($elementID, $clickedObjID, $clickedObjFace, $clickedObjDepth, $qls);
			$resultData = array('selected' => $selected, 'children' => $children);
			array_push($validate->returnData['success'], $resultData);
		
		// Object selected
		} else if($elementType == 2) {
			$clickedObjID = $data['clickedObjID'];
			$clickedObjFace = $data['clickedObjFace'];
			$clickedObjDepth = $data['clickedObjDepth'];
			$clickedObjPortID = $data['clickedObjPortID'];
			$children = buildPortsPathFinder($elementID, $elementFace, $clickedObjID, $clickedObjFace, $clickedObjDepth, $clickedObjPortID, $qls);
			$resultData = array('selected' => $selected, 'children' => $children);
			array_push($validate->returnData['success'], $resultData);
			
		// Port selected
		} else if($elementType == 4) {
			$selectedObjID = $elementID;
			$selectedObjFace = $elementFace;
			$selectedObjDepth = $elementDepth;
			$selectedObjPortID = $elementPortIndex;
			$validate->returnData['success'] = array('FIN', $selectedObjID, $selectedObjFace, $selectedObjDepth, $selectedObjPortID);
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$qls){
	$error = [];
	
	//Validate elementType
	if (!isset($data['value'])){
		array_push($error, array('alert' => 'Error: Element data is required.'));
	} else {
		$dataValue = explode('-', $data['value']);
		if (count($dataValue) != 5){
			array_push($error, array('alert' => 'Error: Invalid data value.'));
		} else {
			$x=true;
		}
	}
	
	return $error;
}

?>
