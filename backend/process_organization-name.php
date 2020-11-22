<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('administrator.php');
require_once '../includes/path_functions.php';

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
		$orgName = $data['value'];
		$qls->SQL->update('app_organization_data', array('name' => $orgName), array('id' => array('=', 1)));
		$validate->returnData['success'] = $data['value'];
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$orgName = $data['value'];
	$validate->validateOrgName($orgName);
}

?>
