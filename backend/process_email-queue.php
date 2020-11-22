<?php
$_SERVER = array('DOCUMENT_ROOT' => '/var/www/html');
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';



$query = $qls->SQL->select('*', 'email_queue', array('sent' => array('=', 0)));
while($row = $qls->SQL->fetch_assoc($query)) {
	$id = $row['id'];
	$recipient = $row['recipient'];
	$sender = $row['sender'];
	$subject = $row['subject'];
	$msg = $row['message'];
	
	$qls->PHPmailer->SMTPDebug = 3;
	$qls->PHPmailer->addAddress($recipient, '');
	$qls->PHPmailer->Subject = $subject;
	$qls->PHPmailer->msgHTML($msg);
	if(!$qls->PHPmailer->send()) {
		error_log($qls->PHPmailer->ErrorInfo);
	}
	$qls->PHPmailer->clearAllRecipients();
	
	$qls->SQL->update('email_queue', array('sent' => 1), array('id' => array('=', $id)));
}

?>
