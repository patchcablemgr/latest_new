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
		'templateID' => $_POST['templateID'],
		'templateFace' => $_POST['templateFace']
	);
	validate($postData, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		$templateID = $postData['templateID'];
		$templateFace = $postData['templateFace'];
		$uploader = new Uploader();
		$filename = md5(time().$_SERVER['REMOTE_ADDR']);
		$data = $uploader->upload($_FILES['files'], array(
			'limit' => 1, //Maximum Limit of files. {null, Number}
			'maxSize' => 5, //Maximum Size of files {null, Number(in MB's)}
			'extensions' => array('jpg', 'jpeg', 'png', 'gif'), //Whitelist for file extension. {null, Array(ex: array('jpg', 'png'))}
			'required' => false, //Minimum one file is required for upload {Boolean}
			'uploadDir' => $_SERVER['DOCUMENT_ROOT'].'/images/templateImages/', //Upload directory {String}
			'title' => $filename, //New file name {null, String, Array} *please read documentation in README.md
			'removeFiles' => true, //Enable file exclusion {Boolean(extra for jQuery.filer), String($_POST field name containing json data with file names)}
			'perms' => null, //Uploaded file permisions {null, Number}
			'onCheck' => null, //A callback function name to be called by checking a file for errors (must return an array) | ($file) | Callback
			'onError' => null, //A callback function name to be called if an error occured (must return an array) | ($errors, $file) | Callback
			'onSuccess' => null, //A callback function name to be called if all files were successfully uploaded | ($files, $metas) | Callback
			'onUpload' => null, //A callback function name to be called if all files were successfully uploaded (must return an array) | ($file) | Callback
			'onComplete' => null, //A callback function name to be called when upload is complete | ($file) | Callback
			'onRemove' => 'onFilesRemoveCallback' //A callback function name to be called by removing files (must return an array) | ($removed_files) | Callback
		));
		
		if($data['isComplete']){
			//$files = $data['data'];
			//print_r($files);
			
			$imgFilename = $data['data']['metas'][0]['name'];
			$imgAttr = $templateFace == 0 ? 'frontImage' : 'rearImage';
			
			// Delete current image if one exists
			/*
			$query = $qls->SQL->select($imgAttr, 'app_object_templates', array('id' => array('=', $templateID)));
			$templateImg = $qls->SQL->fetch_assoc($query);
			$templateImgFilename = $templateImg[$imgAttr];
			
			if($qls->user_info['group_id'] != 1) {
				$file = $_SERVER['DOCUMENT_ROOT'].'/images/templateImages/'.$templateImgFilename;
				if(file_exists($file)){
					unlink($file);
				}
			}
			*/
			
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
		}

		if($data['hasErrors']){
			$errors = $data['errors'];
			print_r($errors);
		}
		
		function onFilesRemoveCallback($removed_files){
			foreach($removed_files as $key=>$value){
				$file = $_SERVER['DOCUMENT_ROOT'].'/images/templateImages/' . $value;
				if(file_exists($file)){
					unlink($file);
				}
			}
			
			return $removed_files;
		}
	}
	echo json_encode($validate->returnData);
}

function validate($postData, &$validate, &$qls){
	//Validate ObjectID
	$validate->validateObjectID($postData['templateID']);
	
	//Validate ObjectFace
	$validate->validateObjectFace($postData['templateFace']);
	
	return;
}

?>
