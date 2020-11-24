<?php
function buildTemplateTable(&$qls){
	$return = array();
	$result = $qls->SQL->select('*', 'app_object_templates');
	while($row = $qls->SQL->fetch_assoc($result)){
		$return[$row['id']] = $row;
	}
	return $return;
}

function buildCompatibilityTable(&$qls){
	$return = array();
	$result = $qls->SQL->select('*', 'app_object_compatibility');
	while($row = $qls->SQL->fetch_assoc($result)){
		$return[$row['template_id']][$row['side']][$row['depth']] = $row;
	}
	return $return;
}

function buildLocation($elementID, &$qls){
	$children = array();
	$result = $qls->SQL->select('*', 'app_env_tree', array('parent' => array('=', $elementID)), array('name', 'ASC'));
	while ($row = $qls->SQL->fetch_assoc($result)) {
		if($row['type'] == 'location' || $row['type'] == 'pod') {
			$elementType = 0;
		} else if($row['type'] == 'cabinet') {
			$elementType = 1;
		}
		$value = array(
			$elementType,
			$row['id'],
			0,
			0,
			0
		);
		$value = implode('-', $value);
		array_push($children, array(
			'value' => $value,
			'text' => $row['name']
			)
		);
	}
	if($elementID == '#') {
		array_push($children, array(
				'text' => '----',
				'children' => array(
					array(
					'value' => '0-0-0-0-0',
					'text' => 'Clear'
					)
				)
			)
		);
	}
	return $children;
}

function buildObjects($elementID, $object, $objectFace, $partitionDepth, &$qls){
	$children = array();
	$templateTable = buildTemplateTable($qls);
	$objectFunction = $templateTable[$object['template_id']]['templateFunction'];
	
	$query = $qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $object['template_id']), 'AND', 'side' => array('=', $objectFace), 'AND', 'depth' => array('=', $partitionDepth)));
	$objectCompatibility = $qls->SQL->fetch_assoc($query);
	
	if($objectCompatibility['partitionFunction'] == 'Endpoint') {
		$query = $qls->SQL->select(
			'*',
			'app_object_compatibility',
			'portTotal = '.$objectCompatibility['portTotal'].' AND portType = 1 AND partitionFunction <> "Endpoint"'
		);
	} else if($objectCompatibility['partitionFunction'] == 'Passive' and $objectCompatibility['portType'] == 1) {
		$query = $qls->SQL->select(
			'*',
			'app_object_compatibility',
			'portTotal = '.$objectCompatibility['portTotal'].' AND ((partitionFunction = "Endpoint" AND (portType = 1 OR portType = 4)) OR (partitionFunction = "Passive" AND mediaType = '.$objectCompatibility['mediaType'].'))'
		);
	} else if($objectCompatibility['partitionFunction'] == 'Passive') {
		$query = $qls->SQL->select(
			'*',
			'app_object_compatibility',
			array(
				'portTotal' => array('=', $objectCompatibility['portTotal']),
				'AND',
				'mediaType' => array('=', $objectCompatibility['mediaType']),
				'AND',
				'partitionFunction' => array('=', 'Passive')
			)
			//'portTotal = '.$objectCompatibility['portTotal'].' AND mediaType = '.$objectCompatibility['mediaType'].' AND partitionFunction = "Passive"'
		);
	}

	$compatibleObjectsArray = array();
	$compatibleEnclosureArray = array();
	
	while ($row = $qls->SQL->fetch_assoc($query)) {
		if($row['templateType'] == 'Insert') {
			$queryInsert = $qls->SQL->select('*', 'app_object', array('env_tree_id' => array('=', $elementID), 'AND', 'template_id' => array('=', $row['template_id'])));
			while ($rowInsert = $qls->SQL->fetch_assoc($queryInsert)) {
				array_push($compatibleEnclosureArray, $rowInsert['parent_id']);
			}
		} else if($row['templateType'] == 'Standard') {
			array_push($compatibleObjectsArray, $row['template_id']);
		}
	}
	$compatibleObjectsArray = array_unique($compatibleObjectsArray);
	$compatibleEnclosureArray = array_unique($compatibleEnclosureArray);
	
	$query = $qls->SQL->select('*', 'app_object', array('env_tree_id' => array('=', $elementID), 'AND', 'parent_id' => array('=', 0), 'AND', 'id' => array('<>', $object['id'])), array('name', 'ASC'));
	while ($row = $qls->SQL->fetch_assoc($query)) {
		if(in_array($row['template_id'], $compatibleObjectsArray) or in_array($row['id'], $compatibleEnclosureArray)) {
			$mountConfig = $templateTable[$row['template_id']]['templateMountConfig'];
			for($x=0; $x<=$mountConfig; $x++){
				if($mountConfig == 1) {
					$side = $x == 0 ? '(front)' : '(rear)';
				} else {
					$side = '';
				}
				$value = array(
					2,
					$row['id'],
					$x,
					0,
					0
				);
				$value = implode('-', $value);
				array_push($children, array(
					'value' => $value,
					//'text' => $row['name'].$side
					'text' => $row['name']
					)
				);
			}
		}
	}
	return $children;
}

function buildObjectsConnector($elementID, $cable, $connectorAttributePrefix, &$qls){
	$children = array();
	$templateTable = buildTemplateTable($qls);
	$connectorType = $cable[$connectorAttributePrefix.'_connector'];
	$cableMediaType = $cable['mediaType'];
	
	//$query = $qls->SQL->select('*', 'app_object_compatibility', '(portType = '.$connectorType.' OR portType = 4) AND (mediaType = '.$cableMediaType.' OR partitionFunction = "Endpoint")');
	$query = $qls->SQL->select('*', 'app_object_compatibility', 'portType = '.$connectorType.' OR portType = 4');
	
	$compatibleObjectsArray = array();
	$compatibleEnclosureArray = array();
	while ($row = $qls->SQL->fetch_assoc($query)) {
		if($row['templateType'] == 'Insert') {
			$queryInsert = $qls->SQL->select('*', 'app_object', array('env_tree_id' => array('=', $elementID), 'AND', 'template_id' => array('=', $row['template_id'])));
			while ($rowInsert = $qls->SQL->fetch_assoc($queryInsert)) {
				array_push($compatibleEnclosureArray, $rowInsert['parent_id']);
			}
		} else if($row['templateType'] == 'Standard') {
			array_push($compatibleObjectsArray, $row['template_id']);
		}
	}
	$compatibleObjectsArray = array_unique($compatibleObjectsArray);
	$compatibleEnclosureArray = array_unique($compatibleEnclosureArray);
	
	$query = $qls->SQL->select('*', 'app_object', array('env_tree_id' => array('=', $elementID), 'AND', 'parent_id' => array('=', 0)));
	while ($row = $qls->SQL->fetch_assoc($query)) {
		if(in_array($row['template_id'], $compatibleObjectsArray) or in_array($row['id'], $compatibleEnclosureArray)) {
			$mountConfig = $templateTable[$row['template_id']]['templateMountConfig'];
			for($x=0; $x<=$mountConfig; $x++){
				if($mountConfig == 1) {
					$side = $x == 0 ? ' (front)' : ' (rear)';
				} else {
					$side = '';
				}
				$value = array(
					2,
					$row['id'],
					$x,
					0,
					0
				);
				$value = implode('-', $value);
				array_push($children, array(
					'value' => $value,
					//'text' => $row['name'].$side
					'text' => $row['name']
					)
				);
			}
		}
	}
	return $children;
}

function buildObjectsPathFinder($elementID, $objectID, $objectFace, $objectDepth, &$qls){
	$children = array();
	$templateTable = buildTemplateTable($qls);

	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $objectID)));
	$object = $qls->SQL->fetch_assoc($query);

	$query = $qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $object['template_id']), 'AND', 'side' => array('=', $objectFace), 'AND', 'depth' => array('=', $objectDepth)));
	$objectCompatibility = $qls->SQL->fetch_assoc($query);
	
	$mediaType = $objectCompatibility['mediaType'];
	$mediaCategory = $objectCompatibility['mediaCategory'];
	$mediaCategoryType = $objectCompatibility['mediaCategoryType'];
	
	if($mediaType == 8) {
		if($mediaCategory == 5) {
			if($mediaCategoryType == 4) {
				$compatibilityQuery = array('partitionType' => array('=', 'connectable'));
			} else {
				$compatibilityQuery = array('mediaCategoryType' => array('=', $mediaCategoryType));
			}
		} else {
			$compatibilityQuery = array('mediaCategory' => array('=', $mediaCategory));
		}
	} else {
		$compatibilityQuery = array('mediaType' => array('=', $mediaType));
	}
	
	$query = $qls->SQL->select('*', 'app_object_compatibility', $compatibilityQuery);
	
	$compatibleObjectsArray = array();
	$compatibleEnclosureArray = array();
	while ($row = $qls->SQL->fetch_assoc($query)) {
		if($row['templateType'] == 'Insert') {
			$queryInsert = $qls->SQL->select('*', 'app_object', array('env_tree_id' => array('=', $elementID), 'AND', 'template_id' => array('=', $row['template_id'])));
			while ($rowInsert = $qls->SQL->fetch_assoc($queryInsert)) {
				array_push($compatibleEnclosureArray, $rowInsert['parent_id']);
			}
		} else if($row['templateType'] == 'Standard') {
			array_push($compatibleObjectsArray, $row['template_id']);
		}
	}
	$compatibleObjectsArray = array_unique($compatibleObjectsArray);
	$compatibleEnclosureArray = array_unique($compatibleEnclosureArray);
	
	$query = $qls->SQL->select('*', 'app_object', array('env_tree_id' => array('=', $elementID), 'AND', 'parent_id' => array('=', 0)), array('name', 'ASC'));
	while ($row = $qls->SQL->fetch_assoc($query)) {
		if(in_array($row['template_id'], $compatibleObjectsArray) or in_array($row['id'], $compatibleEnclosureArray)) {
			$mountConfig = $templateTable[$row['template_id']]['templateMountConfig'];
			for($x=0; $x<=$mountConfig; $x++){
				if($mountConfig == 1) {
					$side = $x == 0 ? ' (front)' : ' (rear)';
				} else {
					$side = '';
				}
				$value = array(
					2,
					$row['id'],
					$x,
					0,
					0
				);
				$value = implode('-', $value);
				array_push($children, array(
					'value' => $value,
					//'text' => $row['name'].$side
					'text' => $row['name']
					)
				);
			}
		}
	}
	return $children;
}

function buildPartitions($elementID, $elementFace, $object, $objectFace, $partitionDepth, &$qls){
	$children = array();
	$templateTable = buildTemplateTable($qls);
	
	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $elementID)));
	$element = $qls->SQL->fetch_assoc($query);
	
	$query = $qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $object['template_id']), 'AND', 'side' => array('=', $objectFace), 'AND', 'depth' => array('=', $partitionDepth)));
	$objectCompatibility = $qls->SQL->fetch_assoc($query);
	
	// Passive/Endpoint
	$objectFunction = $templateTable[$object['template_id']]['templateFunction'];
	$elementFunction = $templateTable[$element['template_id']]['templateFunction'];
	$elementType = $templateTable[$element['template_id']]['templateType'];
	
	// Find element partitions
	$query = $qls->SQL->select('*','app_object_compatibility',array('template_id' => array('=',$element['template_id']),'AND','side' => array('=',$elementFace)));
	
	while($row = $qls->SQL->fetch_assoc($query)) {
		if($row['partitionType'] == 'Connectable') {
			if($row['portTotal'] == $objectCompatibility['portTotal']) {
				if($objectFunction == 'Endpoint' or $elementFunction == 'Endpoint') {
					if(($objectCompatibility['portType'] == 1 or $objectCompatibility['portType'] == 4) and ($row['portType'] == 1 or $row['portType'] == 4)) {
						$addChild = true;
					} else {
						$addChild = false;
					}
				} else if($row['mediaType'] == $objectCompatibility['mediaType']) {
					$addChild = true;
				} else {
					$addChild = false;
				}
				
				if($addChild) {
					if($elementType == 'Insert') {
						if($elementFunction == 'Passive') {
							$insertNamePrefix = $element['name'].'.';
						} else {
							$insertNamePrefix = $element['name'];
						}
					} else {
						$insertNamePrefix = '';
					}
					$value = array(
						3,
						$elementID,
						$elementFace,
						$row['depth'],
						0
					);
					$portCount = $row['portLayoutX']*$row['portLayoutY'];
					$portStart = $row['portNumber'];
					$portEnd = $portStart + ($portCount - 1);
					$value = implode('-', $value);
					array_push($children, array(
						'value' => $value,
						'text' => $insertNamePrefix.$row['portPrefix'].$portStart.'&#8209;'.$portEnd
						)
					);
				}
			}
		} else if($row['partitionType'] == 'Enclosure') {
			$queryInserts = $qls->SQL->select(
				'*',
				'app_object',
				array(
					'parent_id' => array(
						'=',
						$elementID
					),
					'AND',
					'parent_face' => array(
						'=',
						$elementFace
					),
					'AND',
					'parent_depth' => array(
						'=',
						$row['depth']
					)
				)
			);
			
			while($insert = $qls->SQL->fetch_assoc($queryInserts)){
				$queryCompatibility = $qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $insert['template_id'])));
				$insertCompatibility = $qls->SQL->fetch_assoc($queryCompatibility);

				if(($objectFunction == 'Endpoint' and $elementFunction == 'Passive') or ($objectFunction == 'Passive' and $elementFunction == 'Endpoint')) {
					if(($objectCompatibility['portType'] == 1 or $objectCompatibility['portType'] == 4) and ($insertCompatibility['portType'] == 1 or $insertCompatibility['portType'] == 4)) {
						$addChild = true;
					} else {
						$addChild = false;
					}
				} else if($objectFunction == 'Passive' and $elementFunction == 'Passive') {
					if($insertCompatibility['mediaType'] == $objectCompatibility['mediaType']) {
						$addChild = true;
					} else {
						$addChild = false;
					}
				}
				
				if($addChild and $insertCompatibility['portTotal'] == $objectCompatibility['portTotal']) {
					if($insert['id'] != $object['id']) {
						// Check if insert is already peered
						$queryInsertPeer = $qls->SQL->select('*', 'app_object_peer', array('a_id' => array('=', $insert['id']), 'OR', 'b_id' => array('=', $insert['id'])));
						if($qls->SQL->num_rows($queryInsertPeer) == 0) {
							$addChild = true;
						} else {
							$addChild = false;
						}
					} else {
						$addChild = false;
					}
				} else {
					$addChild = false;
				}
				
				if($addChild) {
					$value = array(
						3,
						$insert['id'],
						0,
						0,
						0
					);
					$portCount = $insertCompatibility['portLayoutX']*$insertCompatibility['portLayoutY'];
					$portStart = $insertCompatibility['portNumber'];
					$portEnd = $portStart + ($portCount - 1);
					$value = implode('-', $value);
					array_push($children, array(
						'value' => $value,
						'text' => $insert['name'].'.'.$insertCompatibility['portPrefix'].$portStart.'&#8209;'.$portEnd
						)
					);
				}
			}
		}
	}
	return $children;
}

function buildPorts($elementID, $elementFace, $cable, $connectorAttributePrefix, &$qls){
	$children = array();
	
	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $elementID)));
	$element = $qls->SQL->fetch_assoc($query);
	
	$object = $cable[$connectorAttributePrefix.'_object_id'];
	$depth = $cable[$connectorAttributePrefix.'_object_depth'];
	$port = $cable[$connectorAttributePrefix.'_port_id'];
	
	$mediaTypeArray = array();
	$query = $qls->SQL->select('*', 'shared_mediaType');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$mediaTypeArray[$row['value']] = $row;
	}
	
	$occupiedPorts = array();
	$query = $qls->SQL->select('*', 'app_inventory');
	while($row = $qls->SQL->fetch_assoc($query)){
		$attrPrefixArray = array('a','b');
		foreach($attrPrefixArray as $attrPrefix) {
			if($row[$attrPrefix.'_object_id'] != 0) {
				if($row[$attrPrefix.'_object_id'] != $object or $row[$attrPrefix.'_object_depth'] != $depth or $row[$attrPrefix.'_port_id'] != $port) {
					$portValue = $row[$attrPrefix.'_object_id'].'-'.$row[$attrPrefix.'_object_face'].'-'.$row[$attrPrefix.'_object_depth'].'-'.$row[$attrPrefix.'_port_id'];
					array_push($occupiedPorts, $portValue);
				}
			}
		}
	}
	/*
	// Identify occupied ports of the element
	$query = $qls->SQL->select('*', 'app_inventory', '(a_object_id = '.$elementID.' AND a_object_face = '.$elementFace.') OR (b_object_id = '.$elementID.' AND b_object_face = '.$elementFace.')');
	while($row = $qls->SQL->fetch_assoc($query)){
		$attrPrefixArray = array('a','b');
		foreach($attrPrefixArray as $attrPrefix) {
			if($row[$attrPrefix.'_object_id'] == $elementID and $row[$attrPrefix.'_object_face'] == $elementFace) {
				if($row[$attrPrefix.'_object_depth'] != $depth or $row[$attrPrefix.'_port_id'] != $port) {
					$portValue = $row[$attrPrefix.'_object_id'].'-'.$row[$attrPrefix.'_object_face'].'-'.$row[$attrPrefix.'_object_depth'].'-'.$row[$attrPrefix.'_port_id'];
					array_push($occupiedPorts, $portValue);
				}
			}
		}
	}
	// Identify occupied ports of inserts which are installed in the element
	$query = $qls->SQL->select('*', 'app_object', array('parent_id' => array('=', $elementID), 'AND', 'parent_face' => array('=', $elementFace)));
	while($row = $qls->SQL->fetch_assoc($query)){
		$queryInsert = $qls->SQL->select('*', 'app_inventory', '(a_object_id = '.$row['id'].') OR (b_object_id = '.$row['id'].')');
		while($rowInsert = $qls->SQL->fetch_assoc($queryInsert)){
			$attrPrefixArray = array('a','b');
			foreach($attrPrefixArray as $attrPrefix) {
				if($rowInsert[$attrPrefix.'_object_id'] == $row['id']) {
					if($rowInsert[$attrPrefix.'_object_depth'] != $depth or $rowInsert[$attrPrefix.'_port_id'] != $port) {
						$portValue = $queryInsert[$attrPrefix.'_object_id'].'-'.$queryInsert[$attrPrefix.'_object_face'].'-'.$queryInsert[$attrPrefix.'_object_depth'].'-'.$queryInsert[$attrPrefix.'_port_id'];
						array_push($occupiedPorts, $portValue);
					}
				}
			}
		}
	}
	*/
	
	// Retrieve selected object partitions
	$query = $qls->SQL->select('*',
		'app_object_compatibility',
		array(
			'template_id' => array(
				'=',
				$element['template_id']
			),
			'AND',
			'side' => array(
				'=',
				$elementFace
			)
		)
	);
	
	while($row = $qls->SQL->fetch_assoc($query)){
		$elementArray = array();
		if($row['partitionType'] == 'Enclosure') {
			$queryInsertObject = $qls->SQL->select(
				'*',
				'app_object',
				array(
					'parent_id' => array(
						'=',
						$elementID
					),
					'AND',
					'parent_face' => array(
						'=',
						$row['side']
					),
					'AND',
					'parent_depth' => array(
						'=',
						$row['depth']
					)
				)
			);
			
			while($rowInsertObject = $qls->SQL->fetch_assoc($queryInsertObject)) {
				$queryInsertPartition = $qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $rowInsertObject['template_id'])));
				while($rowInsertPartition = $qls->SQL->fetch_assoc($queryInsertPartition)) {
					if(!isObjectTrunkedAndEndpoint($rowInsertObject['id'], 0, $rowInsertPartition['depth'], $qls)) {
						$rowInsertElement = $rowInsertPartition;
						$rowInsertElement['objectID'] = $rowInsertObject['id'];
						$rowInsertElement['portPrefix'] = $rowInsertObject['name'] == '' ? $rowInsertElement['portPrefix'] : $rowInsertObject['name'].'.'.$rowInsertElement['portPrefix'];
						array_push($elementArray, $rowInsertElement);
					}
				}
			}
		} else if($row['templateType'] == 'Insert') {
			if(!isObjectTrunkedAndEndpoint($elementID, $elementFace, $row['depth'], $qls)) {
				$separator = $row['partitionFunction'] == 'Endpoint' ? '' : '.';
				$rowPartitionElement = $row;
				$rowPartitionElement['objectID'] = $elementID;
				$rowPartitionElement['portPrefix'] = $element['name'] == '' ? $rowPartitionElement['portPrefix'] : $element['name'].$separator.$rowPartitionElement['portPrefix'];
				array_push($elementArray, $rowPartitionElement);
			}
		} else {
			if(!isObjectTrunkedAndEndpoint($elementID, $elementFace, $row['depth'], $qls)) {
				$rowPartitionElement = $row;
				$rowPartitionElement['objectID'] = $elementID;
				array_push($elementArray, $rowPartitionElement);
			}
		}
		
		
		foreach($elementArray as $elementItem) {
			$cablePortType = $cable[$connectorAttributePrefix.'_connector'];
			$cableMediaCategory = $mediaTypeArray[$cable['mediaType']]['category_id'];
			$elementPortType = $elementItem['portType'];
			$elementMediaCategory = $mediaTypeArray[$elementItem['mediaType']]['category_id'];
			$elementPartitionFunction = $elementItem['partitionFunction'];
			
			if(($elementPortType == $cablePortType or $elementPortType == 4) and ($elementMediaCategory == $cableMediaCategory or $elementPartitionFunction == 'Endpoint')) {
				$portStart = $elementItem['portNumber'];
				$portIndex = 0;
				$portCount = $elementItem['portLayoutX']*$elementItem['portLayoutY'];
				for($x=$portIndex; $x<$portCount; $x++) {
					$portValue = $elementItem['objectID'].'-'.$elementItem['side'].'-'.$elementItem['depth'].'-'.$x;
					// Test if port is already connected
					if(!in_array($portValue, $occupiedPorts)) {
						$portNumber = $portStart+$x;
						$value = array(
							4,
							$elementItem['objectID'],
							$elementItem['side'],
							$elementItem['depth'],
							$portIndex+$x
						);
						$value = implode('-', $value);
						array_push($children, array(
							'value' => $value,
							'text' => $elementItem['portPrefix'].$portNumber
							)
						);
					}
				}
			}
		}
	}
	return $children;
}

function buildPortsPathFinder($elementID, $elementFace, $selectedObjID, $selectedObjFace, $selectedObjDepth, $selectedObjectPortID, &$qls){
	$children = array();
	$compatibilityTable = buildCompatibilityTable($qls);
	
	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $elementID)));
	$element = $qls->SQL->fetch_assoc($query);

	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $selectedObjID)));
	$object = $qls->SQL->fetch_assoc($query);

	$mediaCategory = $compatibilityTable[$object['template_id']][$selectedObjFace][$selectedObjDepth];
	
	$mediaType = $compatibilityTable[$object['template_id']][$selectedObjFace][$selectedObjDepth]['mediaType'];
	$mediaCategory = $compatibilityTable[$object['template_id']][$selectedObjFace][$selectedObjDepth]['mediaCategory'];
	$mediaCategoryType = $compatibilityTable[$object['template_id']][$selectedObjFace][$selectedObjDepth]['mediaCategoryType'];
	
	if($mediaType == 8) {
		if($mediaCategory == 5) {
			if($mediaCategoryType == 4) {
				$compatibilityAttr = 'partitionType';
				$compatibilityValue = 'Connectable';
			} else {
				$compatibilityAttr = 'mediaCategoryType';
				$compatibilityValue = $mediaCategoryType;
			}
		} else {
			$compatibilityAttr = 'mediaCategory';
			$compatibilityValue = $mediaCategory;
		}
	} else {
		$compatibilityAttr = 'mediaType';
		$compatibilityValue = $mediaType;
	}
	
	// Build array containing ports that are already connected
	$occupiedPorts = array();

	$query = $qls->SQL->select('*', 'app_inventory', 'a_object_id = '.$elementID.' AND a_object_face = '.$elementFace);
	while($row = $qls->SQL->fetch_assoc($query)){
		if($row['a_object_id'] != $selectedObjectID or $row['a_object_depth'] != $selectedObjectDepth or $row['a_port_id'] != $selectedObjectPortID) {
			$portValue = $row['a_object_depth'].'-'.$row['a_port_id'];
			array_push($occupiedPorts, $portValue);
		}
	}

	$query = $qls->SQL->select('*', 'app_inventory', 'b_object_id = '.$elementID.' AND b_object_face = '.$elementFace);
	while($row = $qls->SQL->fetch_assoc($query)){
		if($row['b_object_id'] != $selectedObjectID or $row['b_object_depth'] != $selectedObjectDepth or $row['b_port_id'] != $selectedObjectPortID) {
			$portValue = $row['b_object_depth'].'-'.$row['b_port_id'];
			array_push($occupiedPorts, $portValue);
		}
	}

	$occupiedPorts = array_unique($occupiedPorts);
	
	$query = $qls->SQL->select('*',
		'app_object_compatibility',
		array(
			'template_id' => array(
				'=',
				$element['template_id']
			),
			'AND',
			'side' => array(
				'=',
				$elementFace
			)
		)
	);
	
	$elementArray = array();
	while($row = $qls->SQL->fetch_assoc($query)){
		
		if($row['partitionType'] == 'Enclosure') {
			// Find all the inserts installed in the enclosure
			$queryInsertObject = $qls->SQL->select(
				'*',
				'app_object',
				array(
					'parent_id' => array(
						'=',
						$elementID
					),
					'AND',
					'parent_face' => array(
						'=',
						$row['side']
					),
					'AND',
					'parent_depth' => array(
						'=',
						$row['depth']
					)
				)
			);
			
			while($rowInsertObject = $qls->SQL->fetch_assoc($queryInsertObject)) {
				$queryInsertPartitions = $qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $rowInsertObject['template_id'])));
				while($rowInsertPartition = $qls->SQL->fetch_assoc($queryInsertPartitions)) {
					if(!isObjectTrunkedAndEndpoint($rowInsertObject['id'], 0, $rowInsertPartition['depth'], $qls) or $row['partitionFunction'] == 'Endpoint') {
						// Determine if the insert port media is compatible with the clicked object's port
						$queryInsertCompatibility = $qls->SQL->select('*',
							'app_object_compatibility',
							array(
								'template_id' => array(
									'=',
									$rowInsertObject['template_id']
								),
								'AND',
								$compatibilityAttr => array(
									'=',
									$compatibilityValue
								),
								'AND',
								'depth' => array(
									'=',
									$rowInsertPartition['depth']
								)
							)
						);
						
						if($qls->SQL->num_rows($queryInsertCompatibility)) {
							$rowInsertElement = $qls->SQL->fetch_assoc($queryInsertCompatibility);
							$separator = $rowInsertElement['partitionFunction'] == 'Endpoint' ? '' : '.';
							$rowInsertElement['objectID'] = $rowInsertObject['id'];
							$rowInsertElement['portPrefix'] = $rowInsertObject['name'] == '' ? $rowInsertElement['portPrefix'] : $rowInsertObject['name'].$separator.$rowInsertElement['portPrefix'];
							array_push($elementArray, $rowInsertElement);
						}
					}
				}
			}
		} else if($row['templateType'] == 'Insert') {
			if(!isObjectTrunkedAndEndpoint($elementID, $elementFace, $row['depth'], $qls) or $row['partitionFunction'] == 'Endpoint') {
				if($row[$compatibilityAttr] == $compatibilityValue) {
					$separator = $row['partitionFunction'] == 'Endpoint' ? '' : '.';
					$rowPartitionElement = $row;
					$rowPartitionElement['objectID'] = $elementID;
					$rowPartitionElement['portPrefix'] = $element['name'] == '' ? $rowPartitionElement['portPrefix'] : $element['name'].$separator.$rowPartitionElement['portPrefix'];
					array_push($elementArray, $rowPartitionElement);
				}
			}	
		} else {
			if(!isObjectTrunkedAndEndpoint($elementID, $elementFace, $row['depth'], $qls) or $row['partitionFunction'] == 'Endpoint') {
				if($row[$compatibilityAttr] == $compatibilityValue) {
					$rowPartitionElement = $row;
					$rowPartitionElement['objectID'] = $elementID;
					array_push($elementArray, $rowPartitionElement);
				}
			}
		}
	}
	
	foreach($elementArray as $element) {
		$portStart = $element['portNumber'];
		$portIndex = 0;
		$portCount = $element['portLayoutX']*$element['portLayoutY'];
		for($x=$portIndex; $x<$portCount; $x++) {
			$portValue = $element['depth'].'-'.$x;
			// Test if port is already connected
			if(!in_array($portValue, $occupiedPorts)) {
				$portNumber = $portStart+$x;
				$value = array(
					4,
					$element['objectID'],
					$element['side'],
					$element['depth'],
					$portIndex+$x
				);
				$value = implode('-', $value);
				array_push($children, array(
					'value' => $value,
					'text' => $element['portPrefix'].$portNumber
					)
				);
			}
		}
	}
	
	return $children;
}

function buildConnectorPath($cable, $connectorAttributePrefix, &$qls){
	$returnArray = array();
	$objectID = $cable[$connectorAttributePrefix.'_object_id'];
	$objectFace = $cable[$connectorAttributePrefix.'_object_face'];
	$objectDepth = $cable[$connectorAttributePrefix.'_object_depth'];
	$objectPortID = $cable[$connectorAttributePrefix.'_port_id'];
	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $objectID)));
	$object = $qls->SQL->fetch_assoc($query);
	
	// Ports
	$children = buildPorts($objectID, $objectFace, $cable, $connectorAttributePrefix, $qls);
	$value = array(
		4,
		$objectID,
		$objectFace,
		$objectDepth,
		$objectPortID
	);
	$value = implode('-', $value);
	array_unshift($returnArray, array(
		'selected' => $value,
		'children' => $children
	));
	
	// Objects
	$children = buildObjectsConnector($object['env_tree_id'], $cable, $connectorAttributePrefix, $qls);
	$objectID = $object['parent_id'] > 0 ? $object['parent_id'] : $objectID;
	$value = array(
		2,
		$objectID,
		$objectFace,
		0,
		0
	);
	$value = implode('-', $value);
	array_unshift($returnArray, array(
		'selected' => $value,
		'children' => $children
	));
	
	// Locations
	$envObjectID = $object['env_tree_id'];
	$query = $qls->SQL->select('*', 'app_env_tree', array('id' => array('=', $envObjectID)));
	$envObject = $qls->SQL->fetch_assoc($query);
	$envObjectParentID = $envObject['parent'];
	while($envObjectParentID != '#'){
		$envObjectType = $envObject['type'];
		if($envObjectType == 'cabinet') {
			$envObjectTypeID = 1;
		} else if($envObjectType == 'location' || $envObjectType == 'pod') {
			$envObjectTypeID = 0;
		}
		$children = buildLocation($envObjectParentID, $qls);
		$value = array(
			$envObjectTypeID,
			$envObjectID,
			0,
			0,
			0
		);
		$value = implode('-', $value);
		array_unshift($returnArray, array(
			'selected' => $value,
			'children' => $children
		));
		$envObjectID = $envObjectParentID;
		$query = $qls->SQL->select('*', 'app_env_tree', array('id' => array('=', $envObjectID)));
		$envObject = $qls->SQL->fetch_assoc($query);
		$envObjectParentID = $envObject['parent'];
	}
	
	$children = buildLocation('#', $qls);
	$value = array(
		0,
		$envObject['id'],
		0,
		0,
		0
	);
	$value = implode('-', $value);
	array_unshift($returnArray, array(
		'selected' => $value,
		'children' => $children
	));
	
	return $returnArray;
}

function buildConnectorFlatPath($cable, $connectorEnd, &$qls){
	$returnArray = array();
	
	if($cable[$connectorEnd.'_object_id']) {
		// Input variables
		$objectID = $cable[$connectorEnd.'_object_id'];
		$objectFace = $cable[$connectorEnd.'_object_face'];
		$objectDepth = $cable[$connectorEnd.'_object_depth'];
		$objectPortID = $cable[$connectorEnd.'_object_port'];
		
		// Object variables
		$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $objectID)));
		$object = $qls->SQL->fetch_assoc($query);
		$objectName = $object['name'];
		
		// Partition variables
		$compatibilityTable = buildCompatibilityTable($qls);
		$partitionCompatibility = $compatibilityTable[$object['template_id']][$objectFace][$objectDepth];
		$templateType = $partitionCompatibility['templateType'];
		$partitionFunction = $partitionCompatibility['partitionFunction'];
		$portLayoutX = $partitionCompatibility['portLayoutX'];
		$portLayoutY = $partitionCompatibility['portLayoutY'];
		$portTotal = $portLayoutX * $portLayoutY;
		$portNameFormat = json_decode($partitionCompatibility['portNameFormat'],true);
		$portName = $qls->App->generatePortName($portNameFormat, $objectPortID, $portTotal);
		
		// Port
		if($templateType == 'Insert') {
			if($partitionFunction == 'Endpoint') {
				$portString = $objectName.$portNumber;
			} else {
				$portString = '.&#8203;'.$objectName.'.&#8203;'.$portName;
			}
		} else {
			$portString = '.&#8203;'.$portName;
		}
		
		// Object
		if($templateType == 'Insert') {
			$parentID = $object['parent_id'];
			$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $parentID)));
			$object = $qls->SQL->fetch_assoc($query);
		}
		$objectString = $object['name'];
		
		//Locations
		$locationString = '';
		$envNodeID = $object['env_tree_id'];
		$rootEnvNode = false;
		while(!$rootEnvNode) {
			$query = $qls->SQL->select('*', 'app_env_tree', array('id' => array('=', $envNodeID)));
			
			$envNode = $qls->SQL->fetch_assoc($query);
			$envNodeID = $envNode['parent'];
			$rootEnvNode = $envNodeID == '#' or !$qls->SQL->num_rows($query) ? true : false;
			$locationString = $envNode['name'].'.&#8203;'.$locationString;
		}
		
		$flatPath = $locationString.$objectString.$portString;
	} else {
		$flatPath = 'None';
	}
	
	return $flatPath;
}

function buildTrunkFlatPath($objectID, $objectFace, $objectDepth, &$qls){
	if(isset($qls->App->peerArray[$objectID][$objectFace][$objectDepth])) {
		// Peer variables
		$peerRecord = $qls->App->peerArray[$objectID][$objectFace][$objectDepth];
		$peerID = $peerRecord['peerID'];
		$peerFace = $peerRecord['peerFace'];
		$peerDepth = $peerRecord['peerDepth'];
		
		// Peer object variables
		$peer = $qls->App->objectArray[$peerID];
		$peerName = $peer['name'];
		$peerTemplateID = $peer['template_id'];
		$peerTemplateType = $qls->App->templateArray[$peerTemplateID]['templateType'];
		if($peerRecord['floorplan_peer']) {
			$object = $objectArray[$objectID];
			$templateID = $object['template_id'];
			$face = $objectFace;
			$depth = $objectDepth;
		} else {
			$templateID = $peerTemplateID;
			$face = $peerFace;
			$depth = $peerDepth;
		}
		
		// Partition variables
		$partitionCompatibility = $qls->App->compatibilityArray[$templateID][$face][$depth];
		$templateType = $partitionCompatibility['templateType'];
		$partitionFunction = $partitionCompatibility['partitionFunction'];
		
		$portNameFormat = json_decode($partitionCompatibility['portNameFormat'], true);
		$portTotal = $partitionCompatibility['portLayoutX']*$partitionCompatibility['portLayoutY'];
		$firstIndex = 0;
		$lastIndex = $portTotal - 1;
		
		$firstPortName = $qls->App->generatePortName($portNameFormat, $firstIndex, $portTotal);
		$lastPortName = $qls->App->generatePortName($portNameFormat, $lastIndex, $portTotal);
		$portRange = $firstPortName.'&nbsp;&#8209;&nbsp;'.$lastPortName;
		
		// Port
		if($templateType == 'Insert') {
			if($partitionFunction == 'Endpoint') {
				$portString = $peerName.$portRange;
			} else {
				$portString = '.&#8203;'.$peerName.'.&#8203;'.$portRange;
			}
		} else {
			$portString = '.&#8203;'.$portRange;
		}
		
		// Peer
		if($templateType == 'Insert') {
			$parentID = $peer['parent_id'];
			$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $parentID)));
			$peer = $qls->SQL->fetch_assoc($query);
		}
		$objectString = $peer['name'];
		
		//Locations
		$locationString = '';
		$envNodeID = $peer['env_tree_id'];
		$rootEnvNode = false;
		while(!$rootEnvNode) {
			$query = $qls->SQL->select('*', 'app_env_tree', array('id' => array('=', $envNodeID)));
			
			$envNode = $qls->SQL->fetch_assoc($query);
			$envNodeID = $envNode['parent'];
			$rootEnvNode = $envNodeID == '#' or !$qls->SQL->num_rows($query) ? true : false;
			$locationString = $envNode['name'].'.&#8203;'.$locationString;
		}
		
		$flatPath = $locationString.$objectString.$portString;
	} else {
		$flatPath = 'None';
	}
	
	return $flatPath;
}

function buildPath($peer, $peerFace, $peerDepth, $object, $objectFace, $objectDepth, &$qls){
	$templateTable = buildTemplateTable($qls);
	$peerTemplateType = $templateTable[$peer['template_id']]['templateType'];
	$peerTemplateFunction = $templateTable[$peer['template_id']]['templateFunction'];
	$returnArray = array();
	
	// Partitions and Inserts
	$children = buildPartitions($peer['id'], $peerFace, $object, $objectFace, $objectDepth, $qls);
	$value = array(
		3,
		$peer['id'],
		$peerFace,
		$peerDepth,
		0
	);
	$value = implode('-', $value);
	$queryString = 'template_id = '.$peer['template_id'].' AND side = '.$peerFace.' AND depth = '.$peerDepth;
	$query = $qls->SQL->select('*', 'app_object_compatibility', $queryString);
	$partitionCompatibility = $qls->SQL->fetch_assoc($query);
	$portCount = $partitionCompatibility['portLayoutX']*$partitionCompatibility['portLayoutY'];
	$portStart = $partitionCompatibility['portNumber'];
	$portEnd = $portStart + ($portCount - 1);
	array_unshift($returnArray, array(
		'selected' => $value,
		'children' => $children
	));
	
	// Objects
	$children = buildObjects($peer['env_tree_id'], $object, $objectFace, $objectDepth, $qls);
	$elementID = $peerTemplateType == 'Insert' ? $peer['parent_id'] : $peer['id'];
	$value = array(
		2,
		$elementID,
		$objectFace,
		0,
		0
	);
	$value = implode('-', $value);
	array_unshift($returnArray, array(
		'selected' => $value,
		'children' => $children
	));
	
	// Locations
	$envObjectID = $peer['env_tree_id'];
	$query = $qls->SQL->select('*', 'app_env_tree', array('id' => array('=', $envObjectID)));
	$envObject = $qls->SQL->fetch_assoc($query);
	$envObjectParentID = $envObject['parent'];
	while($envObjectParentID != '#'){
		$envObjectType = $envObject['type'];
		if($envObjectType == 'cabinet') {
			$envObjectTypeID = 1;
		} else if($envObjectType == 'location' || $envObjectType == 'pod') {
			$envObjectTypeID = 0;
		}
		$children = buildLocation($envObjectParentID, $qls);
		$value = array(
			$envObjectTypeID,
			$envObjectID,
			0,
			0,
			0
		);
		$value = implode('-', $value);
		array_unshift($returnArray, array(
			'selected' => $value,
			'children' => $children
		));
		$envObjectID = $envObjectParentID;
		$query = $qls->SQL->select('*', 'app_env_tree', array('id' => array('=', $envObjectID)));
		$envObject = $qls->SQL->fetch_assoc($query);
		$envObjectParentID = $envObject['parent'];
	}
	
	$children = buildLocation('#', $qls);
	$value = array(
		0,
		$envObject['id'],
		0,
		0,
		0
	);
	$value = implode('-', $value);
	array_unshift($returnArray, array(
		'selected' => $value,
		'children' => $children
	));
	
	return $returnArray;
}

function isObjectTrunked($id, $face, $depth, &$qls){
	$query = $qls->SQL->select('id', 'app_object_peer', '(a_id = '.$id.' AND a_face = '.$face.' AND a_depth = '.$depth.' AND floorplan_peer = 0) OR (b_id = '.$id.' AND b_face = '.$face.' AND b_depth = '.$depth.' AND floorplan_peer = 0)');
	return $qls->SQL->num_rows($query);
}

function isObjectTrunkedAndEndpoint($id, $face, $depth, &$qls){
	$query = $qls->SQL->select('id', 'app_object_peer', '(a_id = '.$id.' AND a_face = '.$face.' AND a_depth = '.$depth.' AND a_endpoint = True) OR (b_id = '.$id.' AND b_face = '.$face.' AND b_depth = '.$depth.' AND b_endpoint = True)');
	return $qls->SQL->num_rows($query);
}

function loopDetected(&$qls, $aID, $aFace, $aDepth, $aPort, $bID, $bFace, $bDepth, $bPort){
	
	// If cable is connected to an object
	if($aID != 0) {
		$query = $qls->SQL->select('*', 'app_object_peer', '(a_id = '.$aID.' AND a_face = '.$aFace.' AND a_depth = '.$aDepth.') OR (b_id = '.$aID.' AND b_face = '.$aFace.' AND b_depth = '.$aDepth.')');
		
		// If object is trunked
		if($qls->SQL->num_rows($query)) {
			$peerRecord = $qls->SQL->fetch_assoc($query);			// table_object_peer(5)
			$peerNearAttr = $peerRecord['a_id'] == $aID ? 'a' : 'b';		// 'b'
			$peerFarAttr = $peerRecord['a_id'] == $aID ? 'b' : 'a';		// 'a'
			
			// If object's peer is not an endpoint
			if($peerRecord[$peerNearAttr.'_endpoint'] != 1) {
				$objID = $peerRecord[$peerFarAttr.'_id'];				// 22
				$objFace = $peerRecord[$peerFarAttr.'_face'];			// 0
				$objDepth = $peerRecord[$peerFarAttr.'_depth'];			// 0
				
				$query = $qls->SQL->select('*', 'app_inventory', '(a_object_id = '.$objID.' AND a_object_face = '.$objFace.' AND a_object_depth = '.$objDepth.' AND a_port_id = '.$aPort.') OR (b_object_id = '.$objID.' AND b_object_face = '.$objFace.' AND b_object_depth = '.$objDepth.' AND b_port_id = '.$aPort.')');
				
				// If peer has cable connected
				if($qls->SQL->num_rows($query)) {
					$peerCable = $qls->SQL->fetch_assoc($query);
					$connectorAttrPrefix = $peerCable['a_object_id'] == $objID ? 'b' : 'a';
					$peerID = $peerCable[$connectorAttrPrefix.'_object_id'];
					$peerFace = $peerCable[$connectorAttrPrefix.'_object_face'];
					$peerDepth = $peerCable[$connectorAttrPrefix.'_object_depth'];
					$peerPort = $peerCable[$connectorAttrPrefix.'_port_id'];
					return loopDetected($qls, $peerID, $peerFace, $peerDepth, $peerPort, $objID, $objFace, $objDepth, $aPort);
				} else if($objID == $bID and $objFace == $bFace and $objDepth == $bDepth and $aPort == $bPort) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function buildPathArray($path){
	$pathArray = array();
	foreach($path as $pathElement) {
		$selected = $pathElement['selected'];
		if($selected == '') {
			$pathArray = array('-');
		} else {
			foreach($pathElement['children'] as $child) {
				if($child['value'] == $selected) {
					array_push($pathArray, $child['text']);
				}
			}
		}
	}
	return $pathArray;
}

function displayArrow($orientation, $scanned, $code39){
	$fill = $scanned ? '#039cfd' : '#ffffff';
	$top = '<path stroke="#000000" fill="'.$fill.'" id="'.$code39.'" transform="rotate(-180 10,10)" d="m12.34666,15.4034l0.12924,-1.39058l-1.52092,-0.242c-3.85063,-0.61265 -7.62511,-3.21056 -9.7267,-6.69472c-0.37705,-0.62509 -0.62941,-1.22733 -0.56081,-1.33833c0.15736,-0.25462 3.99179,-2.28172 4.31605,-2.28172c0.13228,0 0.45004,0.37281 0.70613,0.82847c1.09221,1.9433 3.91879,3.97018 5.9089,4.2371l0.80686,0.10823l-0.13873,-1.2018c-0.14402,-1.24763 -0.10351,-1.50961 0.23337,-1.50961c0.21542,0 6.64622,4.79111 6.83006,5.08858c0.13947,0.22565 -0.74504,1.06278 -3.91187,3.70233c-1.37559,1.14654 -2.65852,2.08463 -2.85095,2.08463c-0.308,0 -0.33441,-0.16643 -0.22064,-1.39058l0,0l0,0l0,-0.00001z" stroke-linecap="null" stroke-linejoin="null" stroke-dasharray="null"/>';
	$btm = '<path stroke="#000000" fill="'.$fill.'" id="'.$code39.'" transform="rotate(-180 10,10)" stroke-dasharray="null" stroke-linejoin="null" stroke-linecap="null" d="m12.34666,4.88458l0.12924,1.38058l-1.52092,0.24026c-3.85063,0.60825 -7.62511,3.18748 -9.7267,6.64659c-0.37705,0.6206 -0.62941,1.21851 -0.56081,1.32871c0.15736,0.25279 3.99179,2.26532 4.31605,2.26532c0.13228,0 0.45004,-0.37013 0.70613,-0.82251c1.09221,-1.92933 3.91879,-3.94164 5.9089,-4.20664l0.80686,-0.10745l-0.13873,1.19316c-0.14402,1.23866 -0.10351,1.49876 0.23337,1.49876c0.21542,0 6.64622,-4.75667 6.83006,-5.052c0.13947,-0.22403 -0.74504,-1.05514 -3.91187,-3.67571c-1.37559,-1.1383 -2.65852,-2.06964 -2.85095,-2.06964c-0.308,0 -0.33441,0.16523 -0.22064,1.38058l0,0l0,0l0,0.00001l0.00001,-0.00001z"/>';
	
	$arrow = '<div class="cableArrow" data-code39="'.$code39.'" title="'.$code39.'">';
	$arrow .= '<svg width="20" height="20" style="display:block;">';
	$arrow .= '<g>';
	$arrow .= $orientation == 'top' ? $top : $btm;
	$arrow .= '</g>';
	$arrow .= '</svg>';
	$arrow .= '</div>';
	
	return $arrow;
}

function displayTrunk(){
	$trunk = '';
	$trunk .= '<svg width="20" height="40">';
	$trunk .= '<g>';
	$trunk .= '<path stroke="#000000" fill="#ffffff" transform="rotate(-90 10,20)" d="m-6.92393,20.00586l9.84279,-8.53669l0,4.26834l14.26478,0l0,-4.26834l9.84279,8.53669l-9.84279,8.53665l0,-4.26832l-14.26478,0l0,4.26832l-9.84279,-8.53665z" stroke-linecap="null" stroke-linejoin="null" stroke-dasharray="null" stroke-width="null"/>';
	$trunk .= '</g>';
	$trunk .= '</svg>';
	return $trunk;
}

function buildCable($topCode39, $btmCode39, $connectorCode39, $length){
	$return = '';
	$return .= '<td rowspan="2" style="vertical-align:middle;">';
		$scanned = $topCode39 == $connectorCode39 ? true : false;
		$return .= displayArrow('top', $scanned, $topCode39);
		$return .= $length;
		$scanned = $btmCode39 == $connectorCode39 ? true : false;
		$return .= displayArrow('btm', $scanned, $btmCode39);
	$return .= '</td>';
	return $return;
}

function buildObject($obj){
	$objectID = $obj['id'];
	$objectElements = $obj['obj'];
	$function = $obj['function'];
	$objSelected = $obj['selected'];
	$return = '';
	$return .= '<td>';
		if ($objectID != 0) {
			$buttonClass = $function == 'Endpoint' ? 'btn-success' : 'btn-purple';
			$return .= '<button id="'.$objectID.'" type="button" class="btn btn-block btn-sm '.$buttonClass.' waves-effect waves-light">';
			$return .= $objSelected ? '<i class="ion-location"></i>&nbsp' : '';			
			foreach($objectElements as $elementIndex => $element){
				$delimiter = $elementIndex < count($objectElements)-1 ? '.' : '';
				$return .= $element.$delimiter;
			}
			$return .= '</button>';
		} else {
			$return .= '<button id="'.$objectID.'" type="button" class="btn btn-block btn-sm btn-danger waves-effect waves-light">';
			$return .= 'None';
			$return .= '</button>';
		}
	$return .= '</td>';
	return $return;
}

function getCable(&$qls, $objID, $portID, $objFace, $objDepth){
	//Build the cable
	$cbl = $qls->SQL->select('*', 'app_inventory', '(a_object_id = '.$objID.' AND a_port_id = '.$portID.' AND a_object_face = '.$objFace.' AND a_object_depth = '.$objDepth.') OR (b_object_id = '.$objID.' AND b_port_id = '.$portID.' AND b_object_face = '.$objFace.' AND b_object_depth = '.$objDepth.')');
	
	if($qls->SQL->num_rows($cbl)>0){
		$cbl = $qls->SQL->fetch_assoc($cbl);
		if($cbl['a_object_id'] == $objID and $cbl['a_port_id'] == $portID) {
			$cbl['nearEnd'] = 'a';
			$cbl['farEnd'] = 'b';
		} else {
			$cbl['nearEnd'] = 'b';
			$cbl['farEnd'] = 'a';
		}
	} else {
		return 0;
	}
	
	return $cbl;
}

function findPeer(&$qls, $objID, $objFace, $objDepth){
	//Build the object 
	$query = $qls->SQL->select('*', 'app_object_peer', '(a_id = '.$objID.' AND a_face = '.$objFace.' AND a_depth = '.$objDepth.') OR (b_id = '.$objID.' AND b_face = '.$objFace.' AND b_depth = '.$objDepth.')');
	
	// If nothing found, quit the function.
	if($qls->SQL->num_rows($query)>0){
		$peer = $qls->SQL->fetch_assoc($query);
	} else {
		return false;
	}
	
	$peerCblAttrPrefix = $peer['a_id'] == $objID ? 'b' : 'a';
	$peerReturn = array(
		'id' => $peer[$peerCblAttrPrefix.'_id'],
		'face' => $peer[$peerCblAttrPrefix.'_face'],
		'depth' => $peer[$peerCblAttrPrefix.'_depth'],
		'endpoint' => $peer[$peerCblAttrPrefix.'_endpoint']
	);
	return $peerReturn;
}

function getObject($templateTable, &$qls, $objID, $portID=0, $objFace=0, $objDepth=0){
	$return = array(
		'obj' => array(),
		'function' => '',
		'id' => $objID,
		'selected' => false
	);
	
	//Build the object 
	$query = $qls->SQL->select('*', 'app_object',array('id' => array('=',$objID)));
	
	// If nothing found, quit the function.
	if($qls->SQL->num_rows($query)>0){
		$obj = $qls->SQL->fetch_assoc($query);
	} else {
		$return['id'] = 0;
		return $return;
	}

	// Retrieve port info
	$query = $qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $obj['template_id']), 'AND', 'side' => array('=', $objFace), 'AND', 'depth' => array('=', $objDepth)));
	$objCompatibility = $qls->SQL->fetch_assoc($query);
	$return['function'] = $objCompatibility['partitionFunction'];
	
	$portNameFormat = json_decode($objCompatibility['portNameFormat'], true);
	$portTotal = $objCompatibility['portLayoutX']*$objCompatibility['portLayoutY'];
	$portName = $qls->App->generatePortName($portNameFormat, $portID, $portTotal);
	
	if($objCompatibility['templateType'] == 'Insert') {
		$separator = $return['function'] == 'Passive' ? '.' : ''; 
		$portName = $obj['name'] == '' ? $portName : $obj['name'].$separator.$portName;
		
		$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $obj['parent_id'])));
		$obj = $qls->SQL->fetch_assoc($query);
	}
	
	array_unshift($return['obj'], $portName);
	
	$side = '';
	if($templateTable[$obj['template_id']]['templateMountConfig'] == 1){
		$side = $objFace == 0 ? '(front)' : '(back)';
	}
	//array_unshift($return['obj'], $obj['name'].$side);
	array_unshift($return['obj'], $obj['name']);
	
	$objParentID = $obj['env_tree_id'];
	
	while($objParentID != '#'){
		$obj = $qls->SQL->select('*', 'app_env_tree', array('id' => array('=', $objParentID)));
		$obj = $qls->SQL->fetch_assoc($obj);
		array_unshift($return['obj'], $obj['name']);
		$objParentID = $obj['parent'];
	}
	
	return $return;
}

function getObjectString($templateTable, &$qls, $objID, $portID=0, $objFace=0, $objDepth=0){
	$return = array(
		'obj' => array(),
		'function' => ''
	);
	
	//Build the object 
	$query = $qls->SQL->select('*', 'app_object',array('id' => array('=',$objID)));
	
	// If nothing found, quit the function.
	if($qls->SQL->num_rows($query)>0){
		$obj = $qls->SQL->fetch_assoc($query);
	} else {
		$return['id'] = 0;
		return $return;
	}
	
	// Retrieve port info
	$query = $qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $obj['template_id']), 'AND', 'side' => array('=', $objFace), 'AND', 'depth' => array('=', $objDepth)));
	$objCompatibility = $qls->SQL->fetch_assoc($query);
	
	$return['function'] = $objCompatibility['partitionFunction'];
	
	$portNameFormat = json_decode($objCompatibility['portNameFormat'], true);
	$portTotal = $objCompatibility['portLayoutX']*$objCompatibility['portLayoutY'];
	$portName = $qls->App->generatePortName($portNameFormat, $portID, $portTotal);
	
	if($objCompatibility['templateType'] == 'Insert') {
		$separator = $return['function'] == 'Passive' ? '.' : ''; 
		$portNamePrefix = $obj['name'] == '' ? '' : $obj['name'].$separator;
		
		$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $obj['parent_id'])));
		$obj = $qls->SQL->fetch_assoc($query);
	}
	
	$port = $portNamePrefix.$portName;
	array_unshift($return['obj'], $port);
	
	$side = '';
	if($templateTable[$obj['template_id']]['templateMountConfig'] == 1){
		$side = $objFace == 0 ? '(front)' : '(back)';
	}
	//array_unshift($return['obj'], $obj['name'].$side);
	array_unshift($return['obj'], $obj['name']);
	
	$objParentID = $obj['env_tree_id'];
	
	while($objParentID != '#'){
		$obj = $qls->SQL->select('*', 'app_env_tree', array('id' => array('=', $objParentID)));
		$obj = $qls->SQL->fetch_assoc($obj);
		array_unshift($return['obj'], $obj['name']);
		$objParentID = $obj['parent'];
	}
	$objString = '';
	for($x=0; $x<count($return['obj']); $x++) {
		$separator = $x<(count($return['obj'])-1) ? '.' : '';
		$objString = $objString.$return['obj'][$x].$separator;
	}
	$return['obj'] = $objString;
	
	return $return;
}

function getAvailablePortArray($objID, $objFace, $objDepth, &$qls){
	$occupiedPortArray = array();
	$attrArray = array('a','b');
	
	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $objID)));
	$obj = $qls->SQL->fetch_assoc($query);
	$templateID = $obj['template_id'];
	
	$query = $qls->SQL->select(
		'*',
		'app_object_compatibility',
		array(
			'template_id' => array(
				'=',
				$templateID
			),
			'AND',
			'side' => array(
				'=',
				$objFace
			),
			'AND',
			'depth' => array(
				'=',
				$objDepth
			)
		)
	);
	$templateCompatibility = $qls->SQL->fetch_assoc($query);
	$portTotal = $templateCompatibility['portLayoutX'] * $templateCompatibility['portLayoutY'];
	
	// Gather patched ports
	$query = $qls->SQL->select('*', 'app_inventory', '(a_object_id = '.$objID.' AND a_object_face = '.$objFace.' AND a_object_depth = '.$objDepth.') OR (b_object_id = '.$objID.' AND b_object_face = '.$objFace.' AND b_object_depth = '.$objDepth.')');
	while($row = $qls->SQL->fetch_assoc($query)) {
		foreach($attrArray as $attr) {
			if($row[$attr.'_object_id'] == $objID and $row[$attr.'_object_face'] == $objFace and $row[$attr.'_object_depth'] == $objDepth) {
				array_push($occupiedPortArray, $row[$attr.'_port_id']);
			}
		}
	}
	
	// Gather populated ports
	$query = $qls->SQL->select('*', 'app_populated_port', array('object_id' => array('=', $objID), 'AND', 'object_face' => array('=', $objFace), 'AND', 'object_depth' => array('=', $objDepth)));
	while($row = $qls->SQL->fetch_assoc($query)) {
		array_push($occupiedPortArray, $row['port_id']);
	}
	
	$availablePortArray = array();
	for($x=0; $x<$portTotal; $x++) {
		if(!in_array($x, $occupiedPortArray)) {
			array_push($availablePortArray, $x);
		}
	}
	
	return $availablePortArray;
}

function convertHyphens($string){
	return str_replace($string, '&#8209;', '-');
}

function getObjElevations($obj, $template, &$qls){
	$template = $template[$obj['template_id']];
	$RUSize = 44.5;
	$width = 482;
	
	// If obj is insert, then get distances of parent object
	if($template['templateType'] == 'Insert') {
		$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $obj['parent_id'])));
		$obj = $qls->SQL->fetch_assoc($query);
	}
	
	$elevationMin = convertToHighestHalfMeter(($obj['RU'] - ($template['templateRUSize'] -1)) * $RUSize);
	$elevationMax = convertToHighestHalfMeter($obj['RU'] * $RUSize);
	return array('elevationMin' => $elevationMin, 'elevationMax' => $elevationMax, 'width' => $width);
}

function getElevationDifference($ARU, $ASize, $BRU, $BSize){
	$min = 100;
	$max = 0;
	$ATopRU = $ARU;
	$ABottomRU = $ARU-($ASize-1);
	$BTopRU = $BRU;
	$BBottomRU = $BRU-($BSize-1);
	$elevationArray = array(
		$ATopRU,
		$ABottomRU,
		$BTopRU,
		$BBottomRU
	);
	foreach($elevationArray as $elevation) {
		if($elevation < $min) {
			$min = $elevation;
		}
		
		if($elevation > $max) {
			$max = $elevation;
		}
	}
	return array('min' => $min, 'max' => $max);
}

function convertToHighestHalfMeter($millimeter){
	$meters = $millimeter * 0.001;
	return round($meters * 2) / 2;
}

function convertToHighestHalfFeet($millimeter){
	$feet = $millimeter * 0.00328084;
	return round($feet * 2) / 2;
}

function getPortType(&$qls, $compatibilityTable, $portTable, $objID, $objFace, $objDepth){
	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $objID)));
	$obj = $qls->SQL->fetch_assoc($query);
	$template = $compatibilityTable[$obj['template_id']][$objFace][$objDepth];
	$portType = $portTable[$template['portType']]['name'];
	return $portType;
}

function calculateCableLength($mediaTypeTable, $mediaCategoryTypeTable, $cbl, $includeUnit=true) {

	// Collect details about the cable to help us calculate length
	$mediaCategoryTypeID = $mediaTypeTable[$cbl['mediaType']]['category_type_id'];
	$mediaCategoryType = $mediaCategoryTypeTable[$mediaCategoryTypeID];
	
	if($cbl['length'] == 0) {
		$length = 'unknown';
		$includeUnit = false;
	} else if($mediaCategoryType['name'] == 'Copper') {
		// Convert to feet
		$length = convertToHighestHalfFeet($cbl['length']);
	} else if($mediaCategoryType['name'] == 'Fiber') {
		// Convert to meters
		$length = convertToHighestHalfMeter($cbl['length']);
	} else {
		$length = $cbl['length'];
	}
	
	if($includeUnit) {
		$length = $length.' '.$mediaCategoryType['unit_of_length'];
	}
	
	return $length;
}

function buildTreeLocation(&$qls){
	$treeArray = array();
	
	$nodeQuery = $qls->SQL->select('*', 'app_env_tree', false, array('name', 'ASC'));
	while ($envNode = $qls->SQL->fetch_assoc($nodeQuery)){
		
		if($envNode['type'] == 'location' || $envNode['type'] == 'pod') {
			$elementType = 0;
		} else if($envNode['type'] == 'cabinet') {
			$elementType = 1;
		}
		
		$value = array($elementType, $envNode['id'], 0, 0, 0);
		$value = implode('-', $value);
		
		array_push($treeArray, array(
			'id' => $envNode['id'],
			'text' => $envNode['name'],
			'parent' => $envNode['parent'],
			'type' => $envNode['type'],
			'data' => array('globalID' => $value)
		));
	}
	
	return $treeArray;
}

function buildTreeObjects(&$qls, $cabinetID){
	$treeArray = array();
	
	// Determine object sort type
	$objectSort = 0;
	if($objectSort == 0) {
		$sort = array('name', 'ASC');
	} else if($objectSort == 1) {
		$sort = array('RU', 'ASC');
	}
	
	$objectQuery = $qls->SQL->select('*', 'app_object', array('env_tree_id' => array('=', $cabinetID), 'AND', 'parent_id' => array('=', 0)), $sort);
	while ($objectNode = $qls->SQL->fetch_assoc($objectQuery)){
		$objectID = $objectNode['id'];
		$objectName = $objectNode['name'];
		
		$value = array(2, $objectID, 0, 0, 0);
		$value = implode('-', $value);
		
		array_push($treeArray, array(
			'id' => 'O'.$objectNode['id'],
			'text' => $objectNode['name'],
			'parent' => $cabinetID,
			'type' => 'object',
			'data' => array('globalID' => $value, 'objectID' => $objectID)
		));
	}
	
	return $treeArray;
}

function buildTreePorts(&$qls, $nodeID, $objectPortType, $objectPartitionFunction, $cablePortType, $cableMediaType, $forTrunk=false){
	$treeArray = array();
	
	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $nodeID)));
	$element = $qls->SQL->fetch_assoc($query);
	
	$occupiedPorts = array();
	if(!$forTrunk) {
		$query = $qls->SQL->select('*', 'app_inventory');
		while($row = $qls->SQL->fetch_assoc($query)){
			$attrPrefixArray = array('a','b');
			foreach($attrPrefixArray as $attrPrefix) {
				if($row[$attrPrefix.'_object_id'] != 0) {
					$portValue = $row[$attrPrefix.'_object_id'].'-'.$row[$attrPrefix.'_object_face'].'-'.$row[$attrPrefix.'_object_depth'].'-'.$row[$attrPrefix.'_port_id'];
					array_push($occupiedPorts, $portValue);
				}
			}
		}
	}
	
	if(!$forTrunk) {
		$whereArray = array('template_id' => array('=', $element['template_id']));
	} else {
		$whereArray = array('template_id' => array('=', $element['template_id']), 'AND', 'partitionFunction' => array('<>', 'Endpoint'));
	}
	
	// Retrieve selected object partitions
	$query = $qls->SQL->select('*',
		'app_object_compatibility',
		$whereArray
	);
	
	$elementArray = array();
	while($row = $qls->SQL->fetch_assoc($query)){
		
		if($row['partitionType'] == 'Enclosure') {
			$queryInsertObject = $qls->SQL->select(
				'*',
				'app_object',
				array(
					'parent_id' => array(
						'=',
						$nodeID
					),
					'AND',
					'parent_face' => array(
						'=',
						$row['side']
					),
					'AND',
					'parent_depth' => array(
						'=',
						$row['depth']
					)
				)
			);
			
			while($rowInsertObject = $qls->SQL->fetch_assoc($queryInsertObject)) {
				$queryInsertPartition = $qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $rowInsertObject['template_id'])));
				while($rowInsertPartition = $qls->SQL->fetch_assoc($queryInsertPartition)) {
					if(!isObjectTrunkedAndEndpoint($rowInsertObject['id'], 0, $rowInsertPartition['depth'], $qls)) {
						$separator = $rowInsertPartition['partitionFunction'] == 'Endpoint' ? '' : '.';
						$rowInsertPartition['objectID'] = $rowInsertObject['id'];
						$rowInsertPartition['portNamePrefix'] = $rowInsertObject['name'] == '' ? '' : $rowInsertObject['name'].$separator;
						array_push($elementArray, $rowInsertPartition);
					}
				}
			}
		} else if($row['templateType'] == 'Insert') {
			if(!isObjectTrunkedAndEndpoint($nodeID, $row['side'], $row['depth'], $qls)) {
				$separator = $row['partitionFunction'] == 'Endpoint' ? '' : '.';
				$rowPartitionElement = $row;
				$rowPartitionElement['objectID'] = $nodeID;
				$rowPartitionElement['portNamePrefix'] = $element['name'] == '' ? '' : $element['name'].$separator;
				array_push($elementArray, $rowPartitionElement);
			}
		} else {
			if(!isObjectTrunkedAndEndpoint($nodeID, $row['side'], $row['depth'], $qls)) {
				$rowPartitionElement = $row;
				$rowPartitionElement['objectID'] = $nodeID;
				$rowPartitionElement['portNamePrefix'] = '';
				array_push($elementArray, $rowPartitionElement);
			}
		}
	}
	
	foreach($elementArray as $elementItem) {
		$elementPortType = $elementItem['portType'];
		$elementMediaCategory = $mediaTypeArray[$elementItem['mediaType']]['category_id'];
		$elementPartitionFunction = $elementItem['partitionFunction'];
		
		if($cablePortType) {
			$mediaTypeArray = array();
			$query = $qls->SQL->select('*', 'shared_mediaType');
			while($row = $qls->SQL->fetch_assoc($query)) {
				$mediaTypeArray[$row['value']] = $row;
			}
			
			$cableMediaCategory = $mediaTypeArray[$cableMediaType]['category_id'];
			
			$isCompatible = ($elementPortType == $cablePortType or $elementPortType == 4) and ($elementMediaCategory == $cableMediaCategory or $elementPartitionFunction == 'Endpoint') ? true : false;
		} else if($objectPortType) {
			$isCompatible = ($elementPortType == $objectPortType or $elementPortType == 4 or $objectPortType == 4) and ($elementMediaCategory == $objectMediaCategory or $elementPartitionFunction == 'Endpoint' or $objectPartitionFunction == 'Endpoint') ? true : false;
		}
		
		if($forTrunk and isObjectTrunked($nodeID, $elementItem['side'], $elementItem['depth'], $qls)) {
			$isCompatible = false;
		}
		
		if($isCompatible) {
			
			$portNameFormat = json_decode($elementItem['portNameFormat'], true);
			$portTotal = $elementItem['portLayoutX']*$elementItem['portLayoutY'];
			
			for($x=0; $x<$portTotal; $x++) {
				$portValue = $elementItem['objectID'].'-'.$elementItem['side'].'-'.$elementItem['depth'].'-'.$x;
				$occupiedIndicator = in_array($portValue, $occupiedPorts) ? '*' : '';
				$portName = $qls->App->generatePortName($portNameFormat, $x, $portTotal);
				
				$value = array(
					4,
					$elementItem['objectID'],
					$elementItem['side'],
					$elementItem['depth'],
					$x
				);
				$value = implode('-', $value);

				array_push($treeArray, array(
					'id' => $value,
					'text' => $elementItem['portNamePrefix'].$portName.$occupiedIndicator.$trunkedFlag,
					'parent' => 'O'.$nodeID,
					'type' => 'port',
					'data' => array('globalID' => $value)
				));
			}
		}
	}
	
	return $treeArray;
}

function buildTreePortGroups(&$qls, $objectID, $objectFace, $objectDepth, $elementID){
	
	$treeArray = array();
	$templateTable = buildTemplateTable($qls);
	
	// Tree element
	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $elementID)));
	$element = $qls->SQL->fetch_assoc($query);
	$elementID = $element['id'];
	$elementTemplateID = $element['template_id'];
	
	// Object selected by user
	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $objectID)));
	$object = $qls->SQL->fetch_assoc($query);
	$objectID = $object['id'];
	$objectTemplateID = $object['template_id'];
	
	$query = $qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $objectTemplateID), 'AND', 'side' => array('=', $objectFace), 'AND', 'depth' => array('=', $objectDepth)));
	$objectCompatibility = $qls->SQL->fetch_assoc($query);
	$objectPortType = $objectCompatibility['portType'];
	$objectMediaType = $objectCompatibility['mediaType'];
	$objectPortLayoutX = $objectCompatibility['portLayoutX'];
	$objectPortLayoutY = $objectCompatibility['portLayoutY'];
	$objectPortTotal = $objectPortLayoutX * $objectPortLayoutY;
	
	// Passive/Endpoint
	$objectFunction = $templateTable[$objectTemplateID]['templateFunction'];
	$elementFunction = $templateTable[$elementTemplateID]['templateFunction'];
	$elementType = $templateTable[$elementTemplateID]['templateType'];
	
	// Find element partitions
	$query = $qls->SQL->select('*','app_object_compatibility',array('template_id' => array('=',$elementTemplateID)));
	
	while($row = $qls->SQL->fetch_assoc($query)) {
		$elementFace = $row['side'];
		$elementDepth = $row['depth'];
		$elementPartitionType = $row['partitionType'];
		
		// Partition is connectable
		if($elementPartitionType == 'Connectable') {
			if($row['portTotal'] == $objectPortTotal) {
				
				if($objectFunction == 'Endpoint' or $elementFunction == 'Endpoint') {
					if(($objectPortType == 1 or $objectPortType == 4) and ($row['portType'] == 1 or $row['portType'] == 4)) {
						$addChild = true;
					} else {
						$addChild = false;
					}
				} else if($row['mediaType'] == $objectMediaType) {
					$addChild = true;
				} else {
					$addChild = false;
				}
				
				if($addChild) {
					$trunkedFlag = isObjectTrunked($element['id'], $elementFace, $elementDepth, $qls) ? '*' : '';
					if($elementType == 'Insert') {
						if($elementFunction == 'Passive') {
							$insertNamePrefix = $element['name'].'.';
						} else {
							$insertNamePrefix = $element['name'];
						}
					} else {
						$insertNamePrefix = '';
					}
					$value = array(
						3,
						$elementID,
						$row['side'],
						$row['depth'],
						0
					);
					$portNameFormat = json_decode($row['portNameFormat'], true);
					$portTotal = $row['portLayoutX']*$row['portLayoutY'];
					$firstIndex = 0;
					$lastIndex = $portTotal - 1;
					$firstPortName = $qls->App->generatePortName($portNameFormat, $firstIndex, $portTotal);
					$lastPortName = $qls->App->generatePortName($portNameFormat, $lastIndex, $portTotal);
					$value = implode('-', $value);
					array_push($treeArray, array(
						'id' => $value,
						'text' => $insertNamePrefix.$row['portPrefix'].$firstPortName.'-'.$lastPortName.$trunkedFlag,
						'parent' => 'O'.$elementID,
						'type' => 'port',
						'data' => array('globalID' => $value)
					));
				}
			}
		
		// Partition is enclosure
		} else if($elementPartitionType == 'Enclosure') {
			
			$objectDetails = array(
				'id' => $objectID,
				'function' => $objectFunction,
				'portType' => $objectPortType,
				'mediaType' => $objectMediaType,
				'portTotal' => $objectPortTotal
			);
			
			$elementDetails = array(
				'id' => $elementID,
				'face' => $elementFace,
				'depth' => $elementDepth
			);
			
			findCompatibleInserts($qls, $objectDetails, $elementDetails, $treeArray);
			
		}
	}
	return $treeArray;
}

function findCompatibleInserts(&$qls, $objectDetails, $elementDetails, &$treeArray, $originalParentID=0){
	
	$objectID = $objectDetails['id'];
	$objectFunction = $objectDetails['function'];
	$objectPortType = $objectDetails['portType'];
	$objectMediaType = $objectDetails['mediaType'];
	$objectPortTotal = $objectDetails['portTotal'];
	$elementID = $elementDetails['id'];
	$elementFace = $elementDetails['face'];
	$elementDepth = $elementDetails['depth'];
	$element = $qls->App->objectArray[$elementID];
	$elementTemplateID = $element['template_id'];
	$elementTemplate = $qls->App->templateArray[$elementTemplateID];
	$elementFunction = $elementTemplate['templateFunction'];
	
	// Select all inserts that are installed in enclosure
	if(isset($qls->App->insertAddressArray[$elementID][$elementFace][$elementDepth])) {
		foreach($qls->App->insertAddressArray[$elementID][$elementFace][$elementDepth] as $enclosureRow) {
			foreach($enclosureRow as $insert) {
				$insertID = $insert['id'];
				$insertTemplateID = $insert['template_id'];
				if(isset($qls->App->compatibilityArray[$insertTemplateID])) {
					foreach($qls->App->compatibilityArray[$insertTemplateID] as $insertFace) {
						foreach($insertFace as $insertPartition) {
							$insertPortType = $insertPartition['portType'];
							$insertMediaType = $insertPartition['mediaType'];
							$insertPortLayoutX = $insertPartition['portLayoutX'];
							$insertPortLayoutY = $insertPartition['portLayoutY'];
							$insertPortTotal = $insertPortLayoutX * $insertPortLayoutY;
							$insertFace = $insertPartition['side'];
							$insertDepth = $insertPartition['depth'];
							$insertPartitionType = $insertPartition['partitionType'];
							$insertPortNameFormat = $insertPartition['portNameFormat'];
							
							if($insertPartitionType == 'Connectable') {
								if(($objectFunction == 'Endpoint' and $elementFunction == 'Passive') or ($objectFunction == 'Passive' and $elementFunction == 'Endpoint')) {
									if(($objectPortType == 1 or $objectPortType == 4) and ($insertPortType == 1 or $insertPortType == 4)) {
										$addChild = true;
									} else {
										$addChild = false;
									}
								} else if($objectFunction == 'Passive' and $elementFunction == 'Passive') {
									if($insertMediaType == $objectMediaType) {
										$addChild = true;
									} else {
										$addChild = false;
									}
								}
								
								if($addChild and $insertPortTotal == $objectPortTotal) {
									if($insertID != $objectID) {
										$addChild = true;
									} else {
										$addChild = false;
									}
								} else {
									$addChild = false;
								}
								
								if($addChild) {
									// Check if insert is already peered
									$trunkedFlag = (isset($qls->App->peerArray[$insertID][$insertFace][$insertDepth])) ? '*' : '';
								}
								
								if($addChild) {
									$value = array(
										3,
										$insertID,
										0,
										$insertDepth,
										0
									);
									
									$portNameFormat = json_decode($insertPortNameFormat, true);
									$firstIndex = 0;
									$lastIndex = $insertPortTotal - 1;
									$firstPortName = $qls->App->generatePortName($portNameFormat, $firstIndex, $insertPortTotal);
									$lastPortName = $qls->App->generatePortName($portNameFormat, $lastIndex, $insertPortTotal);
									$value = implode('-', $value);
									$includeTree = false;
									$includeInsertParentName = false;
									$insertName = $qls->App->generateObjectName($insertID, $includeTree, $includeInsertParentName);
									$separator = ($elementFunction == 'Endpoint') ? '' : '.';
									array_push($treeArray, array(
										'id' => $value,
										'text' => $insertName.$separator.$firstPortName.'-'.$lastPortName.$trunkedFlag,
										'parent' => ($originalParentID != 0) ? 'O'.$originalParentID : 'O'.$elementID,
										'type' => 'port',
										'data' => array('globalID' => $value)
									));
								}
							} else if($insertPartitionType == 'Enclosure'){
								$elementDetails = array(
									'id' => $insertID,
									'face' => $insertFace,
									'depth' => $insertDepth
								);
								$originalParentID = $elementID;
								findCompatibleInserts($qls, $objectDetails, $elementDetails, $treeArray, $originalParentID);
							}
						}
					}
				}
			}
		}
	}
	return;
}

function buildPathFull($path){
	$htmlPathFull = '';
	$htmlPathFull .= '<table>';
	foreach($path as $objectIndex => $object) {
		
		// First path object
		if($objectIndex == 0) {
			if($object[1][0] != '') {
				$htmlPathFull .= '<tr>';
				$htmlPathFull .= buildObject($object[0]);
				$htmlPathFull .= buildCable($object[1][0], $object[1][1], $connectorCode39, $object[1][2]);
				$htmlPathFull .= '</tr>';
				$htmlPathFull .= '<tr>';
				$htmlPathFull .= buildObject($object[2]);
				$htmlPathFull .= '</tr>';
			} else {
				$firstObject = count($path) == 1 ? $object[0] : $object[2];
				$htmlPathFull .= '<tr>';
				$htmlPathFull .= buildObject($firstObject);
				$htmlPathFull .= '</tr>';
			}
		// Last path object
		} else if($objectIndex == count($path)-1) {
			$htmlPathFull .= '<tr>';
			$htmlPathFull .= buildObject($object[0]);
			if($object[1][0] != '') {
				$htmlPathFull .= buildCable($object[1][0], $object[1][1], $connectorCode39, $object[1][2]);
				$htmlPathFull .= '</tr>';
				$htmlPathFull .= '<tr>';
				$htmlPathFull .= buildObject($object[2]);
				$htmlPathFull .= '</tr>';
			} else {
				$htmlPathFull .= '</tr>';
			}
		// Neither first nor last path object
		} else {
			$htmlPathFull .= '<tr>';
			$htmlPathFull .= buildObject($object[0]);
			$htmlPathFull .= buildCable($object[1][0], $object[1][1], $connectorCode39, $object[1][2]);
			$htmlPathFull .= '</tr>';
			$htmlPathFull .= '<tr>';
			$htmlPathFull .= buildObject($object[2]);
			$htmlPathFull .= '</tr>';
		}
		if ($objectIndex < count($path)-1) {
			$htmlPathFull .= '<tr>';
				$htmlPathFull .= '<td style="text-align:center;">';
				$htmlPathFull .= displayTrunk();
				$htmlPathFull .= '</td>';
			$htmlPathFull .= '</tr>';
		}
	}
	$htmlPathFull .= '</table>';
	
	return $htmlPathFull;
}
?>
