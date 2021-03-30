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
		$objectID = $data['objectID'];
		
		$floorplanObject = $qls->App->objectArray[$objectID];
		$floorplanObjectName = $qls->App->unConvertHyphens($floorplanObject['name']);
		$floorplanObjectTemplateID = $floorplanObject['template_id'];
		$floorplanObjectTemplate = $qls->App->templateArray[$floorplanObjectTemplateID];
		
		$type = $floorplanObjectTemplate['templateType'];
		$trunkable = $qls->App->floorplanObjDetails[$type]['trunkable'];
		$peerIDArray = array();
		$objPortArray = array();
		if($trunkable) {
			$trunkFlatPath = isset($qls->App->peerArray[$objectID]) ? 'Yes' : 'No';
			
			foreach($qls->App->peerArray[$objectID][0][0]['peerArray'] as $peerID => $peer) {
				foreach($peer as $peerFace => $face) {
					foreach($face as $peerDepth => $peerPortArray) {
						foreach($peerPortArray as $peerEntryID => $peerPort) {
							$peerTemplateID = $qls->App->objectArray[$peerID]['template_id'];
							$peerPort = $peerPort[1];
							$peerCompatibility = $qls->App->compatibilityArray[$peerTemplateID][$peerFace][$peerDepth];
							$peerPortLayoutX = $peerCompatibility['portLayoutX'];
							$peerPortLayoutY = $peerCompatibility['portLayoutY'];
							$peerPortTotal = $peerPortLayoutX * $peerPortLayoutY;
							$peerPortNameFormatJSON = $peerCompatibility['portNameFormat'];
							$peerPortNameFormat = json_decode($peerPortNameFormatJSON, true);
							$peerPortName = $qls->App->generatePortName($peerPortNameFormat, $peerPort, $peerPortTotal);
							
							$peerIDValue = '4-'.$peerID.'-'.$peerFace.'-'.$peerDepth.'-'.$peerPort;
							
							$objPort = array(
								//'peerEntryID' => $qls->App->peerArray[$objectID][0][0]['id'],
								'peerEntryID' => $peerEntryID,
								'portName' => $peerPortName
							);
							
							array_push($peerIDArray, $peerIDValue);
							array_push($objPortArray, $objPort);
						}
					}
				}
			}
		} else {
			$trunkFlatPath = 'N/A';
		}
		
		$returnData = array(
			'name' => $floorplanObjectName,
			'trunkable' => $trunkable,
			'peerIDArray' => $peerIDArray,
			'objPortArray' => $objPortArray,
			'trunkFlatPath' => $trunkFlatPath
		);
		
		$validate->returnData['success'] = $returnData;
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	
	//Validate object ID
	$validate->validateObjectID($data['objectID']);
	
	return;
}
?>
