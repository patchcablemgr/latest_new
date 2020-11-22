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
		$userType = $data['userType'];
		switch($userType) {
			case 'active':
				$userID = $qls->User->username_to_id($data['username']);
				$query = $qls->SQL->select('*', 'users', array('id' => array('=', $userID)));
				$userInfo = $qls->SQL->fetch_assoc($query);
				if($userInfo['original_org_id'] != $userInfo['org_id']) {
					$originalOrgID = $userInfo['original_org_id'];
					$originalGroupID = $userInfo['original_group_id'];
					$qls->SQL->update('users', array('org_id' => $originalOrgID, 'group_id' => $originalGroupID), array('id' => array('=', $userID)));
				} else {
					if(!$qls->Admin->remove_user($userID)) {
						$msg = $qls->Admin->remove_user_error;
						array_push($validate->returnData['error'], $msg);
					}
				}
				break;
				
			case 'invitation':
				$qls->SQL->delete('invitations', array('email' => array('=', $data['username']), 'AND', 'org_id' => array('=', $qls->user_info['org_id'])));
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$error = [];
	//$qls->User->check_username_existence($data['username']);
	return $error;
}

?>
