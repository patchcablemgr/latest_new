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
		$objID = $data['objID'];
		$objFace = $data['objFace'];
		$objDepth = $data['partitionDepth'];
		$objPort = $data['portID'];
		
		$object = $qls->App->objectArray[$objID];
		$templateID = $object['template_id'];
		$template = $qls->App->templateArray[$templateID];
		$objType = $template['templateType'];
		$objFunction = $template['templateFunction'];
		
		$peerPortArray = array();
		
		// Retrieve peer port ID
		if(isset($qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort])) {
			$port = $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort];
			foreach($port as $connection) {
				$peerPortID = '4-'.$connection['id'].'-'.$connection['face'].'-'.$connection['depth'].'-'.$connection['port'];
				array_push($peerPortArray, $peerPortID);
			}
		}
		
		// Retrieve trunked state
		if($objType == 'walljack' or $objType == 'wap') {
			$trunked = (isset($qls->App->peerArrayWalljack[$objID])) ? true : false;
		} else {
			$trunked = (isset($qls->App->peerArrayStandard[$objID][$objFace][$objDepth])) ? true : false;
		}
		
		// Retrieve populated state
		if($qls->App->populatedPortArray[$objID][$objFace][$objDepth][$objPort]) {
			$populated = true;
		} else {
			$populated = false;
		}
		
		// Retrieve patched state
		$patched = $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort] ? true : false;
		
		if($objFunction == 'Endpoint') {
			if($trunked === true or $populated === true or $patched === true) {
				$populatedChecked = true;
			} else {
				$populatedChecked = false;
			}
			
			if($trunked === true or $patched === true or $qls->App->floorplanObjDetails[$objType]['populatable'] === false) {
				$populatedDisabled = true;
			} else {
				$populatedDisabled = false;
			}
		} else if($objFunction == 'Passive') {
			if($populated === true or $patched === true) {
				$populatedChecked = true;
			} else {
				$populatedChecked = false;
			}
			
			if($patched == true) {
				$populatedDisabled = true;
			} else {
				$populatedDisabled = false;
			}
		} else {
			$populatedChecked = false;
			$populatedDisabled = false;
		}
		
		// Retrieve port options
		$portOptions = $qls->App->retrievePorts($objID, $objFace, $objDepth, $objPort);
		
		// Compile response data
		$returnData = array(
			'portOptions' => $portOptions,
			'peerPortArray' => $peerPortArray,
			'populatedChecked' => $populatedChecked,
			'populatedDisabled' => $populatedDisabled
		);
		
		$validate->returnData['success'] = $returnData;
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	
	//Validate object ID
	$validate->validateObjectID($data['objID']);
	
	//Validate object face
	$validate->validateObjectFace($data['objFace']);
	
	//Validate partition depth
	$validate->validatePartitionDepth($data['partitionDepth']);
	
	//Validate port ID
	$validate->validateObjectID($data['portID']);
	
	return;
}

?>
