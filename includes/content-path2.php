<?php

if($connectorCode39) {
	$managedCableID = base_convert($connectorCode39, 36, 10);
	$rootCable = $qls->App->inventoryByIDArray[$managedCableID];
	
	$objID = $rootCable['local_object_id'];
	$objFace = $rootCable['local_object_face'];
	$objDepth = $rootCable['local_object_depth'];
	$objPort = $rootCable['local_object_port'];
}

$selectedObjID = $objID;
$selectedObjFace = $objFace;
$selectedObjDepth = $objDepth;
$selectedObjPort = $objPort;

$detectDivergence = true;
//$pathArray = $qls->App->crawlPath($selectedObjID, $selectedObjFace, $selectedObjDepth, $selectedObjPort, $detectDivergence);
$pathArray = $qls->App->crawlPath2($selectedObjID, $selectedObjFace, $selectedObjDepth, $selectedObjPort, $detectDivergence);
//error_log('Debug (pathArray2): '.json_encode($pathArray));

?>
