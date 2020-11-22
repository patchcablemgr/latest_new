<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('operator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once '../includes/Validate.class.php';
	
	$validate = new Validate($qls);
	//$validate->returnData['success'] = array();
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		$templateID = $data['templateID'];
		$templateFace = $data['templateFace'];
		$imgAttr = $templateFace == 0 ? 'frontImage' : 'rearImage';
		$qls->SQL->update('app_object_templates', array($imgAttr => null), array('id' => array('=', $templateID)));
		
		/*
		$file = $_SERVER['DOCUMENT_ROOT'].'/images/templateImages/' . $_POST['file'];
		if(file_exists($file)){
			unlink($file);
		}
		*/
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	//Validate template ID
	$validate->validateObjectID($data['templateID']);
	
	//Validate template face
	$validate->validateObjectFace($data['templateFace']);
	
	return;
}

?>
