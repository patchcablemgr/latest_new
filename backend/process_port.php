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
		
		$action = $data['action'];
		$objID = $data['objID'];
		$objFace = $data['objFace'];
		$objDepth = $data['objDepth'];
		$portID = $data['portID'];
		
		switch($action) {
			
			case 'portPopulated';
			
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
							$objID,
							$objFace,
							$objDepth,
							$portID
						)
					);
					
					// Log history
					$portName = $qls->App->generateObjectPortName($objID, $objFace, $objDepth, $portID);
					$actionString = 'Marked port as populated: <strong>'.$portName.'</strong>';
					$qls->App->logAction(2, 2, $actionString);
					
				} else {
					$qls->SQL->delete(
						'app_populated_port',
						array(
							'object_id' => array('=', $objID),
							'AND',
							'object_face' => array('=', $objFace),
							'AND',
							'object_depth' => array('=', $objDepth),
							'AND',
							'port_id' => array('=', $portID)
						)
					);
					
					// Log history
					$portName = $qls->App->generateObjectPortName($objID, $objFace, $objDepth, $portID);
					$actionString = 'Marked port as unpopulated: <strong>'.$portName.'</strong>';
					$qls->App->logAction(3, 2, $actionString);
				}
				break;
				
			case 'portDescription';
			
				$descriptionNew = $data['value'];
				$portName = $qls->App->generateObjectPortName($objID, $objFace, $objDepth, $portID);
			
				// Store original description
				if(isset($qls->App->portDescriptionArray[$objID][$objFace][$objDepth][$portID])) {
					
					$portDescription = $qls->App->portDescriptionArray[$objID][$objFace][$objDepth][$portID];
					$descriptionID = $portDescription['id'];
					$descriptionOrig = $portDescription['description'];
					
					if($descriptionNew == '') {
						
						$qls->SQL->delete('app_port_description', array('id' => array('=', $descriptionID)));
						$actionVerb = 3;
						$actionString = 'Deleted port description: <strong>'.$portName.'</strong> - <strong>'.$descriptionOrig.'</strong>';
					} else {
						
						$qls->SQL->update('app_port_description', array('description' => $descriptionNew), array('id' => array('=', $descriptionID)));
						$actionVerb = 2;
						$actionString = 'Changed port description: <strong>'.$portName.'</strong> - from <strong>'.$descriptionOrig.'</strong> to <strong>'.$descriptionNew.'</strong>';
					}
				} else {
				
					if($descriptionNew != '') {
						// Write new description
						$qls->SQL->insert(
							'app_port_description',
							array(
								'object_id',
								'object_face',
								'object_depth',
								'port_id',
								'description'
							),
							array(
								$objID,
								$objFace,
								$objDepth,
								$portID,
								$descriptionNew
							)
						);
						
						$actionVerb = 1;
						$actionString = 'Added port description: <strong>'.$portName.'</strong> - <strong>'.$descriptionNew.'</strong>';
					}
				}
				
				$qls->App->logAction(3, $actionVerb, $actionString);
				break;
		}
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	
	$action = $data['action'];
	$objID = $data['objID'];
	$objFace = $data['objFace'];
	$objDepth = $data['objDepth'];
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
	
	// Validate action
	$actionArray = array(
		'portPopulated',
		'portDescription'
	);
	$ref = 'action';
	if($validate->validateInArray($action, $actionArray, $ref)) {
		
		switch($action) {
			
			case 'portPopulated':
			
				// Validate port populated
				$portPopulatedFlag = $data['portPopulated'];
				$validate->validateTrueFalse($portPopulatedFlag, 'port populated flag');
				
				break;
			
			case 'portDescription':
			
				// Validate port description
				$portDescription = $data['value'];
				$validate->validateText($portDescription, 'port description');
			
				break;
				
		}
		
	}
	
	return;
}
?>
