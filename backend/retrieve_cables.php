<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('admin.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	$return = array('error' => '',
			'result' => ''
		);
	$return['result'] = array();
	
	$result = $qls->SQL->select('*', 'app_inventory');
	while($row = $qls->SQL->fetch_assoc($result)){
		array_push($return['result'], array(
				'a'=>array(
					'id'=>$row['a_id'],
					'code39'=>$row['a_code39']
				),
				'b'=>array(
					'id'=>$row['b_id'],
					'code39'=>$row['b_code39']
				)
			)
		);
	}
	echo json_encode($return);
}
?>
