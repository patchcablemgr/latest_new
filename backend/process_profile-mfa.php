<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

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
		$mfaState = $data['mfaState'];
		$userID = $qls->user_info['id'];
		
		$html = '';
		if($mfaState) {
			$username = $qls->user_info['username'];
			$secret = $qls->gAuth->generateSecret();
			if (substr($qls->config['cookie_domain'], 0, 1) == '.') {
				$domain = substr($qls->config['cookie_domain'], 1);
			} else {
				$domain = $qls->config['cookie_domain'];
			}
			$QRCodeURL = '<img src="'.$qls->gAuth->getURL($username, $domain, $secret).'" />';

			$qls->SQL->update('users', array('mfa' => 1, 'mfa_secret' => $secret), array('id' => array('=', $userID)));
			
			$html .= 'Configuration:';
			$html .= '<ol>';
			$html .= '<li>Download and install the Google Authenticator app from you mobile device\'s app store</li>';
			$html .= '<li>Scan the QR Code below with the Google Authenticator app to configure your device</li>';
			$html .= '</ol>';
			$html .= $QRCodeURL;
			
			$validate->returnData['success']['html'] = $html;
		} else {
			$qls->SQL->update('users', array('mfa' => 0, 'mfa_secret' => NULL), array('id' => array('=', $userID)));
			$validate->returnData['success']['html'] = $html;
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){

}

?>
