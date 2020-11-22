<?php
define('QUADODO_IN_SYSTEM', true);
require_once('../includes/header.php');
$qls->Security->check_auth_page('backend/process_invoices.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$orderID = $_POST['orderID'];
	$orderStatus = $_POST['orderStatus'];
	$org_id = $_POST['orgID'];
	
	//Assume the organization ID
	$qls->assume_SQL = new SQL($qls, 'app_', $org_id);
	
	$qls->SQL->update('invoices', array('status' => $orderStatus), array('id' => array('=', $orderID)));
	$active = $orderStatus == 'Delivered' ? 1 : 0;
	$qls->assume_SQL->update('app_inventory', array(
			'active' => $active
		), array(
			'order_id' => array(
				'=', $orderID
			)
		)
	);
}
?>
