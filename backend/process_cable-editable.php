<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('administrator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once '../includes/Validate.class.php';
	
	$validate = new Validate($qls);
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		$cableID = $data['cableID'];
		$action = $data['action'];
		
		$editable = $action == 'finalize' ? 0 : 1;
		
		$qls->SQL->update('app_inventory', array('editable' => $editable), array('id' => array('=', $cableID)));
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$actionArray = array('finalize', 'unfinalize');
	
	$validate->validateID($data['cableID'], 'cable ID');
	
	$validate->validateInArray($data['action'], $actionArray, 'editable action');
	
	return $error;
}

?>
