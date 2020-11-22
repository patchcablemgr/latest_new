<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';

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
		$mailMethod = $data['mailMethod'];
		$fromEmail = $data['fromEmail'];
		$fromName = $data['fromName'];
		if ($mailMethod == 'smtp'){
			$smtpServer = $data['smtpServer'];
			$smtpPort = $data['smtpPort'];
			$smtpAuth = $data['smtpAuth'];
			if ($smtpAuth == 'yes'){
				$smtpUsername = $data['smtpUsername'];
				$smtpPassword = $data['smtpPassword'];
			}
		}
		
		$configArray = array(
			'mail_method' => isset($mailMethod)? $mailMethod : '',
			'smtp_auth' => isset($smtpAuth)? $smtpAuth : '',
			'from_email' => isset($fromEmail)? $fromEmail : '',
			'from_name' => isset($fromName)? $fromName : '',
			'smtp_username' => isset($smtpUsername)? $smtpUsername : '',
			'smtp_password' => isset($smtpPassword)? $smtpPassword : '',
			'smtp_port' => isset($smtpPort)? $smtpPort : '',
			'smtp_server' => isset($smtpServer)? $smtpServer : ''
		);
		
		foreach($configArray as $name => $value) {
			$qls->SQL->update('config', array('value' => $value), array('name' => array('=', $name)));
		}
		
		$validate->returnData['success'] = 'Email settings updated.';
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$error = [];
	
	return $error;
}
?>
