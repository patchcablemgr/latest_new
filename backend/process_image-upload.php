<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
require_once '../includes/image-uploader.class.php';
$qls->Security->check_auth_page('operator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once '../includes/Validate.class.php';
	
	$validate = new Validate($qls);
	$validate->returnData['success'] = array();
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$postData = array(
		'action' => $_POST['action'],
		'templateID' => $_POST['templateID'],
		'templateFace' => $_POST['templateFace'],
		'floorplanID' => $_POST['floorplanID']
	);
	
	validate($postData, $validate);
	
	if (!count($validate->returnData['error'])){
		$action = $postData['action'];
		if($action == 'templateImage') {
			$uploadDir = $_SERVER['DOCUMENT_ROOT'].'/images/templateImages/';
		} else if($action == 'floorplanImage') {
			$uploadDir = $_SERVER['DOCUMENT_ROOT'].'/images/floorplanImages/';
		}
		
		$uploader = new Uploader();
		$filename = md5(time().$_SERVER['REMOTE_ADDR']);
		$data = $uploader->upload($_FILES['files'], array(
			'limit' => 1, //Maximum Limit of files. {null, Number}
			'maxSize' => 5, //Maximum Size of files {null, Number(in MB's)}
			'extensions' => array('jpg', 'jpeg', 'png', 'gif'), //Whitelist for file extension. {null, Array(ex: array('jpg', 'png'))}
			'required' => false, //Minimum one file is required for upload {Boolean}
			'uploadDir' => $uploadDir, //Upload directory {String}
			'title' => $filename, //New file name {null, String, Array} *please read documentation in README.md
			'removeFiles' => true, //Enable file exclusion {Boolean(extra for jQuery.filer), String($_POST field name containing json data with file names)}
			'perms' => null, //Uploaded file permisions {null, Number}
			'onCheck' => null, //A callback function name to be called by checking a file for errors (must return an array) | ($file) | Callback
			'onError' => null, //A callback function name to be called if an error occured (must return an array) | ($errors, $file) | Callback
			'onSuccess' => null, //A callback function name to be called if all files were successfully uploaded | ($files, $metas) | Callback
			'onUpload' => null, //A callback function name to be called if all files were successfully uploaded (must return an array) | ($file) | Callback
			'onComplete' => null, //A callback function name to be called when upload is complete | ($file) | Callback
			'onRemove' => null //A callback function name to be called by removing files (must return an array) | ($removed_files) | Callback
		));
		
		if($data['isComplete']){
			
			$imgFilename = $data['data']['metas'][0]['name'];
			if($action == 'templateImage') {
				$templateID = $postData['templateID'];
				$templateFace = $postData['templateFace'];
				$imgAttr = $templateFace == 0 ? 'frontImage' : 'rearImage';
				
				// Update database with new filname
				$qls->SQL->update('app_object_templates', array($imgAttr => $imgFilename), array('id' => array('=', $templateID)));
				
				$query = $qls->SQL->select('*', 'app_object_templates', array('id' => array('=', $templateID)));
				$templateInfo = $qls->SQL->fetch_assoc($query);
				$RUSize = $templateInfo['templateRUSize'];
				
				if($templateInfo['templateType'] == 'Standard') {
					$templateImgHeight = $RUSize * 25;
					$templateImgWidth = 100;
				} else if($templateInfo['templateType'] == 'Insert') {
					$templateImgHeight = round(($RUSize*25)/$templateInfo['templateEncLayoutY']);
					$templateImgWidth = round(($templateInfo['templateHUnits']*10)/$templateInfo['templateEncLayoutX']);
				}
				
				$validate->returnData['success']['imgPath'] = '/images/templateImages/'.$imgFilename;
				$validate->returnData['success']['imgHeight'] = $templateImgHeight;
				$validate->returnData['success']['imgWidth'] = $templateImgWidth;
			} else if($action == 'floorplanImage') {
				$floorplanID = $postData['floorplanID'];
				error_log('floorplanID'.$floorplanID);
				
				// Update database with new filname
				$set = array(
					'floorplan_img' => $imgFilename
				);
				
				$where = array(
					'id' => array('=', $floorplanID)
				);
				
				$qls->SQL->update('app_env_tree', $set, $where);
				$validate->returnData['success']['imgPath'] = '/images/floorplanImages/'.$imgFilename;
			}
		}

		if($data['hasErrors']){
			$errors = $data['errors'];
			print_r($errors);
		}
	}
	echo json_encode($validate->returnData);
}

function validate($postData, &$validate){
	// Validate Action
	$actionArray = array(
		'templateImage',
		'floorplanImage'
	);
	if($validate-> validateInArray($postData['action'], $actionArray, 'image upload action')) {
		if($data['action'] == 'templateImage') {
			//Validate ObjectID
			$validate->validateObjectID($postData['templateID']);
			
			//Validate ObjectFace
			$validate->validateObjectFace($postData['templateFace']);
		} else if($data['action'] == 'floorplanImage') {
			
		}
	}
	return;
}

?>
