<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('operator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once('../includes/Validate.class.php');
	$validate = new Validate($qls);
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$rawData = json_decode($_POST['data'], true);
	$data = [];
	foreach ($rawData as $input){
		$data[$input['name']] = $input['value'];
	}
	$data['defaultOption'] = isset($data['defaultOption']) ? 1 : 0;
	validate($data, $validate);
	
	if (!count($validate->returnData['error'])){
		$action = $data['action'];
		$categoryID = $data['id'];
		
		if($action == 'add') {
			
			$name = $data['name'];
			$color = $data['color'];
			$defaultOption = $data['defaultOption'];
			if($categoryID > 0){
				
				$results = $qls->SQL->select('*', 'app_object_category', array('id' => array('=', $categoryID)));
				if($qls->SQL->num_rows($results)) {
					$defaultCategoryChanged = false;
					
					
					$rows = $qls->SQL->fetch_assoc($results);
					$originalCategoryName = $rows['name'];
					$originalCategoryColor = $rows['color'];
					
					// Handle default category selection
					if($rows['defaultOption'] == 1 and $defaultOption == 0) {
						// Find new default category
						$result = $qls->SQL->select('*', 'app_object_category', false, false, array(1));
						$row = $qls->SQL->fetch_assoc($result);
						$qls->SQL->update('app_object_category', array('defaultOption' => 1), array('id' => array('=', $row['id'])));
						
						// Prepare to log default category change
						$defaultCategoryChanged = true;
						$newDefaultName = $qls->App->categoryArray[$row['id']]['name'];
					} else if($rows['defaultOption'] == 0 and $defaultOption == 1) {
						// Remove default status from current default
						$qls->SQL->update('app_object_category', array('defaultOption' => 0), array('defaultOption' => array('=', 1)));
						
						// Prepare to log default category change
						$defaultCategoryChanged = true;
						$newDefaultName = $name;
					}
					
					$qls->SQL->update('app_object_category', array(
							'name' => $name,
							'color' => $color,
							'defaultOption' => $defaultOption
						), array('id' => array('=', $categoryID))
					);
					$validate->returnData['success'] = array('action' => 'update', 'id' => $categoryID, 'name' => $name, 'color' => $color, 'defaultOption' => $defaultOption);
					
					// Did default category change?
					if($defaultCategoryChanged) {
						// Log action in history
						// $qls->App->logAction($function, $actionType, $actionString)
						$actionString = 'Default template category changed to <strong>'.$newDefaultName.'</strong>';
						$qls->App->logAction(1, 2, $actionString);
					}
					
					// Did category name change?
					if(strtolower($name) != strtolower($originalCategoryName)) {
						// Log action in history
						// $qls->App->logAction($function, $actionType, $actionString)
						$actionString = 'Changed template category name from <strong>'.$originalCategoryName.'</strong> to <strong>'.$name.'</strong>';
						$qls->App->logAction(1, 2, $actionString);
					}
					
					// Did category color change?
					if(strtolower($color) != strtolower($originalCategoryColor)) {
						// Log action in history
						// $qls->App->logAction($function, $actionType, $actionString)
						$actionString = 'Changed template category color from <strong style="color:'.$originalCategoryColor.';">'.$originalCategoryColor.'</strong> to <strong style="color:'.$color.';">'.$color.'</strong>';
						$qls->App->logAction(1, 2, $actionString);
					}
				} else {
					array_push($validate->returnData['error'], 'Category does not exist.');
				}
			} else {
				
				if($defaultOption == 1) {
					$qls->SQL->update('app_object_category', array('defaultOption' => 0), array('defaultOption' => array('=', 1)));
					// Log action in history
					// $qls->App->logAction($function, $actionType, $actionString)
					$actionString = 'Default template category changed to <strong>'.$name.'</strong>';
					$qls->App->logAction(1, 2, $actionString);
				}
				$qls->SQL->insert('app_object_category', array('name', 'color', 'defaultOption'), array($name, $color, $defaultOption));
				$categoryID = $qls->SQL->insert_id();
				$validate->returnData['success'] = array('action' => 'add', 'id' => $categoryID, 'name' => $name, 'color' => $color, 'defaultOption' => $defaultOption);
				
				// Log action in history
				// $qls->App->logAction($function, $actionType, $actionString)
				$actionString = 'Created new template category: <strong>'.$name.'</strong>';
				$qls->App->logAction(1, 1, $actionString);
			}
		} else if($action == 'delete') {
				
			// Check if category is in use
			$result = $qls->SQL->select('id', 'app_object_templates', array('templateCategory_id' => array('=', $categoryID)));
			if ($qls->SQL->num_rows($result) == 0) {
				// Check if only 1 category exists
				$result = $qls->SQL->select('*', 'app_object_category');
				if($qls->SQL->num_rows($result) > 1){
					
					$name = $qls->App->categoryArray[$categoryID]['name'];
					$result = $qls->SQL->select('*', 'app_object_category', array('id' => array('=', $categoryID)));
					$row = $qls->SQL->fetch_assoc($result);
					if($row['defaultOption'] == 1) {
						$result = $qls->SQL->select('*', 'app_object_category', false, false, array(1));
						$row = $qls->SQL->fetch_assoc($result);
						$qls->SQL->update('app_object_category', array('defaultOption' => 1), array('id' => array('=', $row['id'])));
						
						$newDefaultName = $qls->App->categoryArray[$row['id']]['name'];
						// Log action in history
						// $qls->App->logAction($function, $actionType, $actionString)
						$actionString = 'Default template category changed to <strong>'.$newDefaultName.'</strong>';
						$qls->App->logAction(1, 2, $actionString);
					}
					$qls->SQL->delete('app_object_category', array('id' => array('=', $categoryID)));
					$validate->returnData['success'] = $categoryID;
					
					// Log action in history
					// $qls->App->logAction($function, $actionType, $actionString)
					$actionString = 'Deleted template category: <strong>'.$name.'</strong>';
					$qls->App->logAction(1, 3, $actionString);
				} else {
					array_push($validate->returnData['error'], 'At least 1 category is required.');
				}
			} else {
				array_push($validate->returnData['error'], 'Category is in use.');
			}
		}
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	if ($data['action'] == 'add') {
		//Validate category ID
		$validate->validateCategoryID($data['id']);
		
		//Validate category name
		$isEdit = $data['id'] > 0 ? true : false;
		$validate->validateCategoryName($data['name'], $isEdit);
		
		//Validate category color
		$validate->validateCategoryColor($data['color']);
		
		//Validate category default
		$validate->validateBinaryValue($data['defaultOption']);
	} else if($data['action'] == 'delete') {
		//Validate category ID
		$validate->validateCategoryID($data['id']);
	} else {
		$errorMsg = 'Invalid action.';
		array_push($validate->returnData['error'], $errorMsg);
	}
	
	return;
}
?>
