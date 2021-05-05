<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

if($_SERVER['REQUEST_METHOD'] == 'GET'){
	if(isset($_GET['term'])) {
		$term = $_GET['term'];
		$autoCompleteData = array();
			
		// Objects
		foreach($qls->App->objectArray as $object) {
			if(strpos(strtolower($object['name']), strtolower($term)) !== false) {
				$obj = $qls->App->objectArray[$object['id']];
				$label = 'Explore - '.$qls->App->unConvertHyphens($obj['nameString']);
				$objID = $object['id'];
				$parentID = $object['env_tree_id'];
				$value = 'explore-'.$parentID.'-'.$objID;
				array_push($autoCompleteData, array('label'=>$label, 'value'=>$value));
			}
		}
		
		// Templates
		foreach($qls->App->templateArray as $template) {
			if(strpos(strtolower($template['templateName']), strtolower($term)) !== false) {
				$templateType = $template['templateType'];
				if($templateType != 'walljack' and $templateType != 'wap' and $templateType != 'device') {
					$label = 'Template - '.$template['templateName'];
					$templateID = $template['id'];
					$value = 'template-'.$templateID;
					array_push($autoCompleteData, array('label'=>$label, 'value'=>$value));
				}
			}
		}
		
		// Environment
		foreach($qls->App->envTreeArray as $location) {
			if(strpos(strtolower($location['name']), strtolower($term)) !== false) {
				$locationID = $location['id'];
				$treePathString = $qls->App->unConvertHyphens($qls->App->buildTreePathString($locationID));
				$label = 'Environment - '.$treePathString;
				$value = 'environment-'.$locationID;
				array_push($autoCompleteData, array('label'=>$label, 'value'=>$value));
			}
		}
		
		// Port Description
		foreach($qls->App->portDescriptionAllArray as $portDescription) {
			if(strpos(strtolower($portDescription['description']), strtolower($term)) !== false) {
				$objID = $portDescription['object_id'];
				$objFace = $portDescription['object_face'];
				$objDepth = $portDescription['object_depth'];
				$portID = $portDescription['port_id'];
				
				$obj = $qls->App->objectArray[$objID];
				$parentID = $obj['env_tree_id'];
				
				$portName = $qls->App->unConvertHyphens($qls->App->generateObjectPortName($objID, $objFace, $objDepth, $portID));
				$label = 'Port - '.$portName;
				$value = 'port-'.$parentID.'-'.$objID.'-'.$objFace.'-'.$objDepth.'-'.$portID;
				array_push($autoCompleteData, array('label'=>$label, 'value'=>$value));
			}
		}
		
		echo json_encode($autoCompleteData);
		
	} else if(isset($_GET['select'])) {
		
		$value = $_GET['select'];
		$data = explode('-', $value);
		$appFunction = $data[0];
		
		if($appFunction == 'explore') {
			$parentID = $data[1];
			$objID = $data[2];
			header('Location: /explore.php?parentID='.$parentID.'&objID='.$objID);
			exit();
		} else if($appFunction == 'template') {
			$templateID = $data[1];
			header('Location: /templates.php?templateID='.$templateID);
			exit();
		} else if($appFunction == 'environment') {
			$nodeID = $data[1];
			header('Location: /environment.php?nodeID='.$nodeID);
			exit();
		} else if($appFunction == 'port') {
			$parentID = $data[1];
			$objID = $data[2];
			$objFace = $data[3];
			$objDepth = $data[4];
			$portID = $data[5];
			header('Location: /explore.php?parentID='.$parentID.'&objID='.$objID.'&objFace='.$objFace.'&objDepth='.$objDepth.'&portID='.$portID);
			exit();
		}
		
	}
}

?>
