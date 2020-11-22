<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once('../includes/Validate.class.php');
	$validate = new Validate($qls);
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate);
	
	if (!count($validate->returnData['error'])){
		$objectID = $data['objID'];
		$objectFace = $data['objFace'];
		$partitionDepth = $data['partitionDepth'];
		$portID = $data['portID'];
		$portPopulated = $data['portPopulated'];
		
		if($portPopulated) {
			$qls->SQL->insert(
				'app_populated_port',
				array(
					'object_id',
					'object_face',
					'object_depth',
					'port_id'
				),
				array(
					$objectID,
					$objectFace,
					$partitionDepth,
					$portID
				)
			);
			
			// Log history
			$portName = $qls->App->generateObjectPortName($objectID, $objectFace, $partitionDepth, $portID);
			$actionString = 'Marked port as populated: <strong>'.$portName.'</strong>';
			$qls->App->logAction(2, 2, $actionString);
			
		} else {
			$qls->SQL->delete(
				'app_populated_port',
				array(
					'object_id' => array('=', $objectID),
					'AND',
					'object_face' => array('=', $objectFace),
					'AND',
					'object_depth' => array('=', $partitionDepth),
					'AND',
					'port_id' => array('=', $portID)
				)
			);
			
			// Log history
			$portName = $qls->App->generateObjectPortName($objectID, $objectFace, $partitionDepth, $portID);
			$actionString = 'Marked port as unpopulated: <strong>'.$portName.'</strong>';
			$qls->App->logAction(3, 2, $actionString);
		}
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	
	$objID = $data['objID'];
	$objFace = $data['objFace'];
	$objDepth = $data['partitionDepth'];
	$objPortID = $data['portID'];
	
	//Validate object ID
	$validate->validateObjectID($objID);
	
	//Validate object face
	$validate->validateObjectFace($objFace);
	
	//Validate partition depth
	$validate->validatePartitionDepth($objDepth);
	
	//Validate port ID
	$validate->validatePortID($objPortID, 'port ID');
	
	//Validate endpoint port trunked
	$portArray = array(
		array($objID, $objFace, $objDepth, $objPortID)
	);
	$validate->validateTrunkedEndpoint($portArray);
	
	//Validate port populated
	$validate->validateTrueFalse($data['portPopulated'], 'port populated flag');
	
	return;
}
?>
