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
		$objectFace = $data['objFace'];
		$cabinetFace = $data['cabinetFace'];
		$objectID = $data['objID'];
		$partitionDepth = $data['partitionDepth'];
		
		$templateID = $objectID;
		$objectName = $trunkedTo = 'N/A';
		
		//Retrieve partition info
		$query = $qls->SQL->select('*', 'table_template_object_compatibility', array('template_id' => array('=', $templateID), 'AND', 'side' => array('=', $objectFace), 'AND', 'depth' => array('=', $partitionDepth)));
		$partitionData = $qls->SQL->fetch_assoc($query);
		$partitionType = $partitionData['partitionType'];
		//$portName = '';
		$portRange = false;
		
		if($partitionType == 'Connectable'){
			$portLayoutX = $partitionData['portLayoutX'];
			$portLayoutY = $partitionData['portLayoutY'];
			$portTotal = $portLayoutX * $portLayoutY;
			$portIndexFirst = 0;
			$portIndexLast = $portTotal - 1;
			$portNameFormat = json_decode($partitionData['portNameFormat'], true);
			$portNameFirst = $qls->App->generatePortName($portNameFormat, $portIndexFirst, $portTotal);
			$portNameLast = $qls->App->generatePortName($portNameFormat, $portIndexLast, $portTotal);
			$portRange = $portNameFirst.'&#8209;'.$portNameLast;
			//$portName = $partitionData['portPrefix'];
			//$portCount = generatePortCount($partitionData['portNumber'], $partitionData['portLayoutX'], $partitionData['portLayoutY']);
			$portProperties = getPortProperties($qls);
			$portType = $portProperties['portType'][$partitionData['portType']];
			$portOrientation = $portProperties['portOrientation'][$partitionData['portOrientation']];
			$mediaType = $partitionData['partitionFunction'] == 'Passive' ? $portProperties['mediaType'][$partitionData['mediaType']] : 'N/A';
			$trunkable = true;
		} else if($partitionType == 'Enclosure'){
			$portCount = $portType = $portOrientation = $mediaType = 'N/A';
			$trunkable = false;
		} else {
			// Generic partition... these won't be in the compatibility table so catch them with an else
			$partitionType = $portCount = $portType = $mediaType = 'N/A';
			$trunkable = false;
		}

		// Retrieve template info
		$templateInfo = $qls->SQL->select('*', 'table_template_object_templates', 'id='.$templateID);
		$templateInfo = $qls->SQL->fetch_assoc($templateInfo);
		
		if($templateInfo['templateType'] == 'Standard') {
			$mountConfig = $templateInfo['templateMountConfig'] == 0 ? '2-Post' : '4-Post';
			$RUSize = $templateInfo['templateRUSize'];
		} else if($templateInfo['templateType'] == 'Insert'){
			$mountConfig = $RUSize = 'N/A';
		}
		
		$templateImgFilename = $objectFace == 0 ? $templateInfo['frontImage'] : $templateInfo['rearImage'];
		if($templateImgFilename !== null) {
			$templateImgPath = '/images/templateImages/'.$templateImgFilename;
			if($templateInfo['templateType'] == 'Standard') {
				$templateImgHeight = $RUSize * 25;
				$templateImgWidth = 100;
			} else if($templateInfo['templateType'] == 'Insert'){
				// calculate height
				// calculate width
			}
		} else {
			$templateImgPath = '';
			$templateImgHeight = 0;
			$templateImgWidth = 0;
		}
		
		// Retrieve category info
		$categoryArray = array();
		$result = $qls->SQL->select('*', 'table_template_object_category');
		while($row = $qls->SQL->fetch_assoc($result)){
			array_push($categoryArray, array('value'=>$row['id'], 'text'=>$row['name']));
			if($row['id'] == $templateInfo['templateCategory_id']){
				$categoryID = $row['id'];
				$categoryName = $row['name'];
			}
		}
		
		// Compile response data
		$returnData = array(
			'objectName' => $objectName,
			'templateName' => $templateInfo['templateName'],
			'trunkedTo' => $trunkedTo,
			'categoryName' => $categoryName,
			'categoryArray' => $categoryArray,
			'categoryID' => $categoryID,
			'objectType' => $templateInfo['templateType'],
			'RUSize' => $RUSize,
			'function' => $templateInfo['templateFunction'],
			'mountConfig' => $mountConfig,
			'partitionType' => $partitionType,
			'portRange' => $portRange,
			'portType' => $portType,
			'mediaType' => $mediaType,
			'templateImgPath' => $templateImgPath,
			'templateImgHeight' => $templateImgHeight,
			'templateImgWidth' => $templateImgWidth,
			'trunkable' => $trunkable
		);
		$validate->returnData['success'] = $returnData;
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	//Validate page name
	$validate->validatePageName($data['page']);
	
	//Validate object ID
	$validate->validateObjectID($data['objID']);
	
	//Validate object face
	$validate->validateObjectFace($data['objFace']);

	//Validate partition depth
	$validate->validatePartitionDepth($data['partitionDepth']);
	
	return;
}

function generatePortCount($number, $x, $y){
	if($x == 1 and $y == 1) {
		$portCount = $number;
	} else {
		$lastNumber = $number+($x*$y-1);
		$portCount = $number.'&#8209;'.$lastNumber;
	}
	return $portCount;
}

function getPortProperties(&$qls){
	$portProperties = array();
	
	$query = $qls->SQL->select('*', 'shared_object_portType');
	while($row = $qls->SQL->fetch_assoc($query)){
		$portProperties['portType'][$row['value']] = $row['name'];
	}
	
	$query = $qls->SQL->select('*', 'shared_object_portOrientation');
	while($row = $qls->SQL->fetch_assoc($query)){
		$portProperties['portOrientation'][$row['value']] = $row['name'];
	}
	
	$query = $qls->SQL->select('*', 'shared_mediaType');
	while($row = $qls->SQL->fetch_assoc($query)){
		$portProperties['mediaType'][$row['value']] = $row['name'];
	}
	return $portProperties;
}
?>
