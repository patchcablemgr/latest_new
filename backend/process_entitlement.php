<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('administrator.php');

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
		
		$action = strtolower($data['action']);
		
		if($action == 'update') {
			
			$entitlementID = strtolower($data['entitlementID']);
			$qls->SQL->update('app_organization_data', array('entitlement_id' => $entitlementID), array('id' => array('=', 1)));
			$qls->App->updateEntitlementData($entitlementID);
			$qls->App->gatherEntitlementData();
			$validate->returnData['success'] = $qls->App->entitlementArray;
			
		} else if($action == 'check') {
			
			$entitlementID = $qls->App->entitlementArray['id'];
			$qls->App->updateEntitlementData($entitlementID);
			$qls->App->gatherEntitlementData();
			$validate->returnData['success'] = $qls->App->entitlementArray;
			
		} else if($action == 'cancel') {
			$qls->App->cancelEntitlement();
			$qls->App->gatherEntitlementData();
			$validate->returnData['success'] = $qls->App->entitlementArray;
			
		} else if($action == 'portal') {
			$entitlementID = $qls->App->entitlementArray['id'];
		
			// POST Request
			$data = array(
				'action' => 'portal',
				'entitlementID' => $entitlementID
			);
			$dataJSON = json_encode($data);
			$POSTData = array('data' => $dataJSON);
			
			$ch = curl_init('https://patchcablemgr.com/public/process_subscription.php');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTData);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, "/etc/ssl/certs/");
			
			// Submit the POST request
			$responseJSON = curl_exec($ch);
			
			//Check for request errors.
			$errMsg = false;
			if(!curl_errno($ch)) {
				if($response = json_decode($responseJSON, true)) {
					if(!count($response['error'])) {
						if($response['success'] != '') {
							$validate->returnData['success']['customerPortalURL'] = $response['success'];
						} else {
							$errMsg = 'Entitlement not found, please contact support@patchcablemgr.com';
						}
					} else {
						$errMsg = $response['error'][0];
					}
				} else {
					$errMsg = 'Invalid server response, please contact support@patchcablemgr.com';
				}
			} else {
				$errMsg = 'Unable to contact server, please contact support@patchcablemgr.com';
			}
			
			if($errMsg) {
				array_push($validate->returnData['error'], $errMsg);
			}
			
			// Close cURL session handle
			curl_close($ch);
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$actionsArray = array('update', 'check', 'cancel', 'portal');
	$action = strtolower($data['action']);
	
	//Validate action
	if($validate->validateInArray($action, $actionsArray, 'action')) {
		
		if($action == 'update') {
			
			// Validate entitlement ID
			$entitlementID = strtolower($data['entitlementID']);
			$validate->validateSHA($entitlementID, 'Invalid entitlement ID.');
			
		}
	}
}

?>
