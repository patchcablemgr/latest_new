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
		if(!isset($data['messageID'])) {
			$validate->returnData['success'] = array();
			$query = $qls->SQL->select('*', 'shared_user_messages', array('to_id' => array('=', $qls->user_info['id'])));
			while($row = $qls->SQL->fetch_assoc($query)) {
				$from = $qls->User->id_to_username($row['from_id']);
				array_push($validate->returnData['success'], array(
					'messageID' => $row['message_id'],
					'date' => $row['date'],
					'from' => $from,
					'subject' => $row['subject'],
					'viewed' => $row['viewed']
				));
			}
		} else {
			$messageID = $data['messageID'];
			$query = $qls->SQL->select('*', 'shared_user_messages', array('message_id' => array('=', $messageID)));
			if($qls->SQL->num_rows($query)) {
				$message = $qls->SQL->fetch_assoc($query);
				$from = $qls->User->id_to_username($message['from_id']);
				$validate->returnData['success'] = array(
					'messageID' => $message['message_id'],
					'date' => $message['date'],
					'from' => $from,
					'subject' => $message['subject'],
					'message' => $message['message']
				);
				$qls->SQL->update('shared_user_messages', array('viewed' => 1), array('message_id' => array('=', $messageID)));

			}
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$error = [];
	
	return $error;
}

?>
