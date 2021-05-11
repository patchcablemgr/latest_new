<?php
/*** *** *** *** *** ***
* @package Quadodo Login Script
* @file    User.class.php
* @start   July 15th, 2007
* @author  Douglas Rennehan
* @license http://www.opensource.org/licenses/gpl-license.php
* @version 1.1.5
* @link    http://www.quadodo.net
*** *** *** *** *** ***
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*** *** *** *** *** ***
* Comments are always before the code they are commenting.
*** *** *** *** *** ***/
if (!defined('QUADODO_IN_SYSTEM')) {
    exit;
}

/**
 * Contains all update functions
 */
class Update {

/**
 * @var object $qls - Will contain everything else
 */
var $qls;

	/**
	 * Construct class
	 * @param object $qls - Contains all other classes
	 * @return void
	 */
	function __construct(&$qls) {
	    $this->qls = &$qls;
		
		// Store current and running versions
		$this->currentVersion = $this->getVersion();
		$this->runningVersion = PCM_VERSION;
	}

	/**
	 * Determines what update needs to be applied and applies it
	 * @return Boolean
	 */
	function determineUpdate() {
		if($this->currentVersion == '0.1.0') {
			$this->update_010_to_011();
		} else if($this->currentVersion == '0.1.1') {
			$this->update_011_to_012();
		} else if($this->currentVersion == '0.1.2') {
			$this->update_012_to_013();
		} else if($this->currentVersion == '0.1.3') {
			$this->update_013_to_020();
		} else if($this->currentVersion == '0.2.0') {
			$this->update_020_to_021();
		} else if($this->currentVersion == '0.2.1') {
			$this->update_021_to_022();
		} else if($this->currentVersion == '0.2.2') {
			$this->update_022_to_023();
		} else if($this->currentVersion == '0.2.3') {
			$this->update_023_to_024();
		} else if($this->currentVersion == '0.2.4') {
			$this->update_024_to_030();
		} else if($this->currentVersion == '0.3.0') {
			$this->update_030_to_031();
		} else if($this->currentVersion == '0.3.1') {
			$this->update_031_to_032();
		} else if($this->currentVersion == '0.3.2') {
			$this->update_032_to_033();
		} else if($this->currentVersion == '0.3.3') {
			$this->update_033_to_034();
		} else if($this->currentVersion == '0.3.4') {
			$this->update_034_to_035();
		} else if($this->currentVersion == '0.3.5') {
			$this->update_035_to_036();
		} else if($this->currentVersion == '0.3.6') {
			$this->update_036_to_037();
		} else if($this->currentVersion == '0.3.7') {
			$this->update_037_to_038();
		} else if($this->currentVersion == '0.3.8') {
			$this->update_038_to_039();
		} else if($this->currentVersion == '0.3.9') {
			$this->update_039_to_0310();
		} else if($this->currentVersion == '0.3.10') {
			$this->update_0310_to_0311();
		} else if($this->currentVersion == '0.3.11') {
			$this->update_0311_to_0312();
		} else if($this->currentVersion == '0.3.12') {
			$this->update_0312_to_0313();
		} else if($this->currentVersion == '0.3.13') {
			$this->update_0313_to_0314();
		} else if($this->currentVersion == '0.3.14') {
			$this->update_0314_to_0315();
		} else if($this->currentVersion == '0.3.15') {
			$this->update_0315_to_0316();
		} else {
			return true;
		}
		
		$this->currentVersion = $this->getVersion();
		return false;
	}
	
	
	/**
	 * Update from version 0.3.15 to 0.3.16
	 * @return Boolean
	 */
	function update_0315_to_0316() {
		$incrementalVersion = '0.3.16';
		
		// Set app version to 0.3.16
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Update password hash
		$query = $this->qls->SQL->query("SHOW COLUMNS FROM `{$this->qls->config['sql_prefix']}users` LIKE 'pwl'");
		if(!$this->qls->SQL->num_rows($query)) {
			
			// Add pwl column
			$this->qls->SQL->alter('users', 'add', 'pwl', 'tinyint', true, 0);
			
			// Grow password field to support changes in password_hash()
			$this->qls->SQL->query("ALTER TABLE `{$this->qls->config['sql_prefix']}users` CHANGE `password` `password` varchar(255)");
			
			// Convert password hash
			$query = $this->qls->SQL->select('*', 'users');
			while($row = $this->qls->SQL->fetch_assoc($query)) {
				
				$rowID = $row['id'];
				$password = $row['password'];
				$passwordHash = password_hash($password, PASSWORD_DEFAULT);
				
				$this->qls->SQL->update('users', array('password' => $passwordHash, 'pwl' => true), array('id' => array('=', $rowID)));
			}
		}
		
		// Add object port type
		$objectPortTypeColumns = array('value', 'name', 'category_type_id', 'defaultOption');
		$objectPortTypeValues = array(8, 'ST', 2, 0);
		$this->qls->SQL->insert('shared_object_portType', $objectPortTypeColumns, $objectPortTypeValues);
		
		// Add cable connector type
		$connectorPortTypeColumns = array('value', 'name', 'defaultOption');
		$connectorPortTypeValues = array(8, 'ST', 0);
		$this->qls->SQL->insert('shared_cable_connectorType', $connectorPortTypeColumns, $connectorPortTypeValues);
		
		// Add port description table
		$query = '';
		$query .= "CREATE TABLE `{$this->qls->config['sql_prefix']}app_port_description` (";
		$query .= '`id` int(11) NOT NULL AUTO_INCREMENT,';
		$query .= '`object_id` int(11) NOT NULL,';
		$query .= '`object_face` int(11) NOT NULL,';
		$query .= '`object_depth` int(11) NOT NULL,';
		$query .= '`port_id` int(11) NOT NULL,';
		$query .= '`description` varchar(255) NOT NULL,';
		$query .= 'PRIMARY KEY(`id`))';
		$this->qls->SQL->query($query);
	}
	
	/**
	 * Update from version 0.3.14 to 0.3.15
	 * @return Boolean
	 */
	function update_0314_to_0315() {
		$incrementalVersion = '0.3.15';
		
		// Set app version to 0.3.15
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
	}
	
	/**
	 * Update from version 0.3.13 to 0.3.14
	 * @return Boolean
	 */
	function update_0313_to_0314() {
		$incrementalVersion = '0.3.14';
		
		// Set app version to 0.3.14
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
	}
	
	/**
	 * Update from version 0.3.12 to 0.3.13
	 * @return Boolean
	 */
	function update_0312_to_0313() {
		$incrementalVersion = '0.3.13';
		
		// Set app version to 0.3.13
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
	}
	
	/**
	 * Update from version 0.3.11 to 0.3.12
	 * @return Boolean
	 */
	function update_0311_to_0312() {
		$incrementalVersion = '0.3.12';
		
		// Set app version to 0.3.12
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
	}
	
	/**
	 * Update from version 0.3.10 to 0.3.11
	 * @return Boolean
	 */
	function update_0310_to_0311() {
		$incrementalVersion = '0.3.11';
		
		// Set app version to 0.3.11
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add nested insert columns to "template" table
		$this->qls->SQL->alter('app_object_templates', 'add', 'nestedParentHUnits', 'int(11)', true, 'NULL');
		$this->qls->SQL->alter('app_object_templates', 'add', 'nestedParentVUnits', 'int(11)', true, 'NULL');
		$this->qls->SQL->alter('app_object_templates', 'add', 'nestedParentEncLayoutX', 'int(11)', true, 'NULL');
		$this->qls->SQL->alter('app_object_templates', 'add', 'nestedParentEncLayoutY', 'int(11)', true, 'NULL');
		
		// Add "objSort" column to "users" table
		$this->qls->SQL->alter('users', 'add', 'objSort', 'tinyint(4)', false, 0);
		
		// Validate trunked endpoints don't have connections
		$peerSideArray = array('a', 'b');
		$queryPeer = $this->qls->SQL->select('*', 'app_object_peer');
		while($peer = $this->qls->SQL->fetch_assoc($queryPeer)) {
			$connectedEndpointFound = false;
			$rowID = $peer['id'];
			$floorplanPeer = $peer['floorplan_peer'];
			$peerArray = array(
				array(
					'id' => $peer['a_id'],
					'face' => $peer['a_face'],
					'depth' => $peer['a_depth'],
					'port' => $peer['a_port']
				),
				array(
					'id' => $peer['b_id'],
					'face' => $peer['b_face'],
					'depth' => $peer['b_depth'],
					'port' => $peer['b_port']
				)
			);
			
			foreach($peerArray as $peer) {
				$peerID = $peer['id'];
				$peerFace = $peer['face'];
				$peerDepth = $peer['depth'];
				$peerPort = $peer['port'];
				$peerEndpoint = $peer['endpoint'];
				
				// Collect object
				$queryObject = $this->qls->SQL->select('*', 'app_object', array('id' => array('=', $peerID)));
				$object = $this->qls->SQL->fetch_assoc($queryObject);
				$templateID = $object['template_id'];
				
				// Collect template
				$templateQuery = $this->qls->SQL->select('*', 'app_object_templates', array('id' => array('=', $templateID)));
				$template = $this->qls->SQL->fetch_assoc($templateQuery);
				$templateFunction = $template['templateFunction'];
				
				if($templateFunction == 'Endpoint') {
					if($floorplanPeer) {
						
						// Check for connected ports
						foreach($peerSideArray as $peerSide) {
							$inventoryQuery = $this->qls->SQL->select('*', 'app_inventory',array(
								$peerSide.'_id' => array('=', $peerID),
								'AND',
								$peerSide.'_face' => array('=', $peerFace),
								'AND',
								$peerSide.'_depth' => array('=', $peerDepth),
								'AND',
								$peerSide.'_port' => array('=', $peerPort)
							));
							$inventoryNumRows = $this->qls->SQL->num_rows($inventoryQuery);
							if($inventoryNumRows) {
								$connectedEndpointFound = true;
							}
						}
						
						// Check for populated ports
						$populatedQuery = $this->qls->SQL->select('*', 'app_populated_port',array(
							'object_id' => array('=', $peerID),
							'AND',
							'object_face' => array('=', $peerFace),
							'AND',
							'object_depth' => array('=', $peerDepth),
							'AND',
							'port_id' => array('=', $peerPort)
						));
						$populatedNumRows = $this->qls->SQL->num_rows($populatedQuery);
						if($inventoryNumRows) {
							$connectedEndpointFound = true;
						}
					} else {
						// Check for connected ports
						foreach($peerSideArray as $peerSide) {
							$inventoryQuery = $this->qls->SQL->select('*', 'app_inventory',array(
								$peerSide.'_id' => array('=', $peerID),
								'AND',
								$peerSide.'_face' => array('=', $peerFace),
								'AND',
								$peerSide.'_depth' => array('=', $peerDepth)
							));
							$inventoryNumRows = $this->qls->SQL->num_rows($inventoryQuery);
							if($inventoryNumRows) {
								$connectedEndpointFound = true;
							}
						}
						
						// Check for populated ports
						$populatedQuery = $this->qls->SQL->select('*', 'app_populated_port',array(
							'object_id' => array('=', $peerID),
							'AND',
							'object_face' => array('=', $peerFace),
							'AND',
							'object_depth' => array('=', $peerDepth)
						));
						$populatedNumRows = $this->qls->SQL->num_rows($populatedQuery);
						if($inventoryNumRows) {
							$connectedEndpointFound = true;
						}
					}
				}
			}
			
			if($connectedEndpointFound) {
				$this->qls->SQL->delete('app_object_peer', array('id' => array('=', $rowID)));
			}
		}
	}
	
	/**
	 * Update from version 0.3.9 to 0.3.10
	 * @return Boolean
	 */
	function update_039_to_0310() {
		$incrementalVersion = '0.3.10';
		
		// Set app version to 0.3.10
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Update portOrientation names
		$portOrientationNameArray = array(
			1 => 'Top-Left to Right',
			2 => 'Top-Left to Bottom',
			3 => 'Top-Right to Left',
			4 => 'Bottom-Left to Right'
		);
		foreach($portOrientationNameArray as $portOrientationID => $portOrientationName) {
			$this->qls->SQL->update('shared_object_portOrientation', array('name' => $portOrientationName), array('id' => array('=', $portOrientationID)));
		}
		
		// Add portOrientation "Bottom-Left to Top"
		$objectPortOrientationColumns = array('id', 'value', 'name', 'defaultOption');
		$objectPortOrientationValuesArray = array(5, 5, 'Bottom-Left to Top', 0);
		$this->qls->SQL->insert('shared_object_portOrientation', $objectPortOrientationColumns, $objectPortOrientationValuesArray);
		
	}
	
	/**
	 * Update from version 0.3.8 to 0.3.9
	 * @return Boolean
	 */
	function update_038_to_039() {
		$incrementalVersion = '0.3.9';
		
		// Set app version to 0.3.9
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add "treeSort" column to "users" table
		$this->qls->SQL->alter('users', 'add', 'treeSort', 'tinyint(4)', false, 0);
		
		// Add "treeSortAdj" column to "users" table
		$this->qls->SQL->alter('users', 'add', 'treeSortAdj', 'tinyint(4)', false, 0);
		
		// Add "order" column to "app_env_tree" table
		$this->qls->SQL->alter('app_env_tree', 'add', 'order', 'int(11)', false, 0);
		
		$counter = 1;
		$neighborNodeNameArray = array();
		$query = $this->qls->SQL->select('*', 'app_env_tree', false, array('name', 'ASC'));
		while($row = $this->qls->SQL->fetch_assoc($query)) {
			$rowID = $row['id'];
			$parentID = $row['parent'];
			$nodeName = $row['name'];
			if(!isset($neighborNodeNameArray[$parentID])) {
				$neighborNodeNameArray[$parentID] = array();
			}
			
			// Add order
			$this->qls->SQL->update('app_env_tree', array('order' => $counter), array('id' => array('=', $rowID)));
			$counter++;

			// Resolve duplicate neighbor names
			$duplicateFound = false;
			foreach($neighborNodeNameArray[$parentID] as $neighborNodeName) {
				if(strtolower($neighborNodeName) == strtolower($nodeName)) {
					$uniqueStr = $this->generateUniqueNameValue();
					$newName = $nodeName.'_'.$uniqueStr;
					$this->qls->SQL->update('app_env_tree', array('name' => $newName), array('id' => array('=', $rowID)));
					$duplicateFound = true;
					break;
				}
			}
			
			array_push($neighborNodeNameArray[$parentID], $nodeName);
		}
		
	}
	
	/**
	 * Update from version 0.3.7 to 0.3.8
	 * @return Boolean
	 */
	function update_037_to_038() {
		$incrementalVersion = '0.3.8';
		
		// Set app version to 0.3.8
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add cable connector types
		$connectorTypeColumns = array('value', 'name', 'defaultOption');
		$connectorTypeValuesArray = array(
			array(5, 'MPO-12', 0),
			array(6, 'MPO-24', 0)
		);
		foreach($connectorTypeValuesArray as $connectorTypeValues) {
			$this->qls->SQL->insert('shared_cable_connectorType', $connectorTypeColumns, $connectorTypeValues);
		}
		
		// Add object port type
		$objectPortTypeColumns = array('value', 'name', 'category_type_id', 'defaultOption');
		$objectPortTypeValuesArray = array(
			array(5, 'QSFP', 4, 0),
			array(6, 'MPO-12', 2, 0),
			array(7, 'MPO-24', 2, 0)
		);
		foreach($objectPortTypeValuesArray as $objectPortTypeValues) {
			$this->qls->SQL->insert('shared_object_portType', $objectPortTypeColumns, $objectPortTypeValues);
		}
		
	}
	
	/**
	 * Update from version 0.3.6 to 0.3.7
	 * @return Boolean
	 */
	function update_036_to_037() {
		$incrementalVersion = '0.3.7';
		
		// Set app version to 0.3.7
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Create combined templates table
		$this->qls->SQL->query("CREATE TABLE `{$this->qls->config['sql_prefix']}app_combined_templates` (`id` int(11) NOT NULL AUTO_INCREMENT, `templateName` varchar(255) NOT NULL, `template_id` int(11) DEFAULT NULL, `templateCategory_id` int(11) DEFAULT NULL, `childTemplateData` text, PRIMARY KEY(`id`))");
		
		// Add "treeSize" column to "user" table
		$this->qls->SQL->alter('users', 'add', 'treeSize', 'tinyint(4)', false, 0);
		
		// Correct previously added user table fields
		$this->qls->SQL->alter('users', 'alter', 'scrollLock', false, false, 1);
		$this->qls->SQL->alter('users', 'alter', 'connectionStyle', false, false, 0);
		$this->qls->SQL->alter('users', 'alter', 'pathOrientation', false, false, 0);
		
	}
	
	/**
	 * Update from version 0.3.5 to 0.3.6
	 * @return Boolean
	 */
	function update_035_to_036() {
		$incrementalVersion = '0.3.6';
		
		// Set app version to 0.3.6
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Object Template Values
		$objectTemplateValues = array('Camera', 'camera', 'Endpoint');
		
		// Object template columns
		$objectTemplateColumns = array(
			'templateName',
			'templateType',
			'templateFunction'
		);
		
		// Add object templates
		$this->qls->SQL->insert('app_object_templates', $objectTemplateColumns, $objectTemplateValues);
		
		$cameraTemplateID = $this->qls->SQL->insert_id();
		
		// Floorplan object compatibility values
		$objectCompatibilityValues = array($cameraTemplateID, '1', '1', '1', 'camera', 'Connectable', 'Endpoint', '1', '8', '1', '1', '[{\"type\":\"static\",\"value\":\"NIC\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]');
		
		// Object compatibility columns
		$objectCompatibilityColumns = array(
			'template_id',
			'portLayoutX',
			'portLayoutY',
			'portTotal',
			'templateType',
			'partitionType',
			'partitionFunction',
			'portType',
			'mediaType',
			'mediaCategory',
			'mediaCategoryType',
			'portNameFormat'
		);
		
		// Add object compatibility
		$this->qls->SQL->insert('app_object_compatibility', $objectCompatibilityColumns, $objectCompatibilityValues);
	}
	
	/**
	 * Update from version 0.3.4 to 0.3.5
	 * @return Boolean
	 */
	function update_034_to_035() {
		$incrementalVersion = '0.3.5';
		
		// Set app version to 0.3.5
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
	}
	
	/**
	 * Update from version 0.3.3 to 0.3.4
	 * @return Boolean
	 */
	function update_033_to_034() {
		$incrementalVersion = '0.3.4';
		
		// Set app version to 0.3.4
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
	}
	
	/**
	 * Update from version 0.3.2 to 0.3.3
	 * @return Boolean
	 */
	function update_032_to_033() {
		$incrementalVersion = '0.3.3';
		
		// Set app version to 0.3.3
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
	}
	
	/**
	 * Update from version 0.3.1 to 0.3.2
	 * @return Boolean
	 */
	function update_031_to_032() {
		$incrementalVersion = '0.3.2';
		
		// Set app version to 0.3.2
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add "app_id" column to "organization_data" table
		$this->qls->SQL->alter('app_organization_data', 'add', 'app_id', 'VARCHAR(40)');
		
		// Generate unique appID
		$time = time();
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$length = 4;
		$charactersLength = strlen($characters);
		$salt = '';
		for($i = 0; $i < $length; $i++) {
			$salt .= $characters[rand(0, $charactersLength - 1)];
		}
		$appID = sha1($time.$salt);
		
		// Updata appID
		$this->qls->SQL->update('app_organization_data', array('app_id' => $appID), array('id' => array('=', 1)));
		
	}
	
	/**
	 * Update from version 0.3.0 to 0.3.1
	 * @return Boolean
	 */
	function update_030_to_031() {
		$incrementalVersion = '0.3.1';
		
		// Set app version to 0.3.1
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
	}
	
	/**
	 * Update from version 0.2.4 to 0.3.0
	 * @return Boolean
	 */
	function update_024_to_030() {
		$incrementalVersion = '0.3.0';
		
		// Set app version to 0.3.0
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add "connectionStyle" column to "users" table
		$this->qls->SQL->alter('users', 'add', 'connectionStyle', 'SMALLINT(6)', false, 0);
		
		// Add "pathOrientation" column to "users" table
		$this->qls->SQL->alter('users', 'add', 'pathOrientation', 'TINYINT(4)', false, 0);
		
		// Add "global_setting_path_orientation" column to "organization_data" table
		$this->qls->SQL->alter('app_organization_data', 'add', 'global_setting_path_orientation', 'TINYINT(4)', false, 0);
		
	}
	
	/**
	 * Update from version 0.2.3 to 0.2.4
	 * @return Boolean
	 */
	function update_023_to_024() {
		$incrementalVersion = '0.2.4';
		
		// Set app version to 0.2.4
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
	}
	
	/**
	 * Update from version 0.2.2 to 0.2.3
	 * @return Boolean
	 */
	function update_022_to_023() {
		$incrementalVersion = '0.2.3';
		
		// Set app version to 0.2.3
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Allow Administrator role to remove users
		$this->qls->SQL->update('masks', array('auth_admin_remove_user' => 1), array('name' => array('=', 'Administrator')));
		
		// Previous version did not include floorplan templates during install... so let's fix that
		$floorplanTemplateArray = array(
			1 => array(1, 'Walljack', NULL, 'walljack', NULL, 'Passive', NULL, NULL, NULL, NULL, NULL, '', 'NULL', NULL),
			2 => array(2, 'WAP', NULL, 'wap', NULL, 'Endpoint', NULL, NULL, NULL, NULL, NULL, '', 'NULL', NULL),
			3 => array(3, 'Device', NULL, 'device', NULL, 'Endpoint', NULL, NULL, NULL, NULL, NULL, '', 'NULL', NULL)
		);
		foreach($floorplanTemplateArray as $floorplanTemplateID => $floorplanTemplateValues) {
			$query = $this->qls->SQL->select('*', 'app_object_templates', array('id' => array('=', $floorplanTemplateID)));
			if(!$this->qls->SQL->num_rows($query)) {
				$columns = array('id', 'templateName', 'templateCategory_id', 'templateType', 'templateRUSize', 'templateFunction', 'templateMountConfig', 'templateEncLayoutX', 'templateEncLayoutY', 'templateHUnits', 'templateVUnits', 'templatePartitionData', 'frontImage', 'rearImage');
				$this->qls->SQL->insert('app_object_templates', $columns, $floorplanTemplateValues);
			}
		}
		
	}
	
	/**
	 * Update from version 0.2.1 to 0.2.2
	 * @return Boolean
	 */
	function update_021_to_022() {
		$incrementalVersion = '0.2.2';
		
		// Set app version to 0.2.2
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add "ru_orientation" column to "app_env_tree" table
		$this->qls->SQL->alter('app_env_tree', 'add', 'ru_orientation', 'tinyint', true, 0);
		
		$query = $this->qls->SQL->select('*', 'app_object_templates');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			if($row['templatePartitionData']) {
				
				$rowID = $row['id'];
				$partitionDataJSON = $row['templatePartitionData'];
				$partitionData = json_decode($partitionDataJSON, true);
				
				// Find and fix duplicate port names throughout the entire template
				foreach($partitionData as &$face) {
					$this->findAndFixDuplicatePortIDs($face);
				}
				unset($face);
				
				// Adjust template hUnits from max 10 to max 24
				foreach($partitionData as &$face) {
					$this->adjustPartitionHUnits($face);
				}
				unset($face);
				if($row['templateHUnits']) {
					$templateHUnits = $row['templateHUnits'];
					$templateHUnits = round(($templateHUnits/10)*24);
					$this->qls->SQL->update('app_object_templates', array('templateHUnits' => $templateHUnits), array('id' => array('=', $rowID)));
				}
				
				// Set enclosure tolerance if not already set
				foreach($partitionData as &$face) {
					$this->setEnclosureTolerance($face);
				}
				unset($face);
				
				// Update object templates table
				$partitionDataJSON = json_encode($partitionData);
				$this->qls->SQL->update('app_object_templates', array('templatePartitionData' => $partitionDataJSON), array('id' => array('=', $rowID)));
				
				// Update object compatibility
				foreach($partitionData as $side => $face) {
					$this->updateObjectCompatibility($face, $rowID, $side);
				}
			}
		}
		
		// Adjust compatibility table
		$query = $this->qls->SQL->select('*', 'app_object_compatibility');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			
			$rowID = $row['id'];
			
			// Adjust compatibility hUnits from max 10 to max 24
			if($row['hUnits']) {
				$hUnits = $row['hUnits'];
				$newHUnits = round(($hUnits/10)*24);
				$this->qls->SQL->update('app_object_compatibility', array('hUnits' => $newHUnits), array('id' => array('=', $rowID)));
			}
			
			// Set enclosure tolerance to "Loose" if it is not set
			if($row['partitionType'] == 'Enclosure') {
				if(!$row['encTolerance']) {
					$this->qls->SQL->update('app_object_compatibility', array('encTolerance' => 'Loose'), array('id' => array('=', $rowID)));
				}
			}
		}
	}
	
	/**
	 * Update from version 0.2.0 to 0.2.1
	 * @return Boolean
	 */
	function update_020_to_021() {
		$incrementalVersion = '0.2.1';
		
		// Set app version to 0.2.1
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add "entitlement_expiration" column to "app_organization_data" table
		$this->qls->SQL->alter('app_organization_data', 'add', 'entitlement_expiration', 'int(40)', false, 0);
		
		// Allow Administrator role to remove users
		$this->qls->SQL->update('masks', array('auth_admin_remove_user' => 1), array('name' => array('=', 'Administrator')));
	}
	
	/**
	 * Update from version 0.1.3 to 0.2.0
	 * @return Boolean
	 */
	function update_013_to_020() {
		$incrementalVersion = '0.2.0';
		
		// Set app version to 0.2.0
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add "encTolerance" column to "object_compatibility" table
		$this->qls->SQL->alter('app_object_compatibility', 'add', 'encTolerance', 'varchar(255)', true);
		
		// Add "scrollLock" column to "users" table
		$this->qls->SQL->alter('users', 'add', 'scrollLock', 'tinyint(4)', false, 1);
		
		// Rename "portLayoutX/Y" and "encLayoutX/Y" in partition data to "valueX/Y"
		$query = $this->qls->SQL->select('*', 'app_object_templates');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			if($row['templatePartitionData']) {
				$rowID = $row['id'];
				$partitionDataJSON = $row['templatePartitionData'];
				$partitionData = json_decode($partitionDataJSON, true);
				foreach($partitionData as &$face) {
					$this->alterTemplatePartitionDataLayoutName($face);
					$this->alterTemplatePartitionDataDimensionUnits($face);
				}
				$partitionDataJSON = json_encode($partitionData);
				$this->qls->SQL->update('app_object_templates', array('templatePartitionData' => $partitionDataJSON), array('id' => array('=', $rowID)));
			}
		}
		
		// Create /app directories
		if(!is_dir('/app/images/')) {
			$mkdirSuccess = mkdir('/app/images/', 0755);
		}
		if(!is_dir('/app/images/templateImages/')) {
			$mkdirSuccess = mkdir('/app/images/templateImages/', 0755);
		}
		if(!is_dir('/app/images/floorplanImages/')) {
			$mkdirSuccess = mkdir('/app/images/floorplanImages/', 0755);
		}
		
		// Create symbolic link
		if(!is_dir('/app/public/images')) {
			symlink('/app/images/', '/app/public/images');
		}
	}
	
	/**
	 * Update from version 0.1.2 to 0.1.3
	 * @return Boolean
	 */
	function update_012_to_013() {
		$incrementalVersion = '0.1.3';
		
		// Set app version to 0.1.2
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
	}
	
	/**
	 * Update from version 0.1.1 to 0.1.2
	 * @return Boolean
	 */
	function update_011_to_012() {
		$incrementalVersion = '0.1.2';
		
		// Set app version to 0.1.2
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
	}
	
	/**
	 * Update from version 0.1.0 to 0.1.1
	 * @return Boolean
	 */
	function update_010_to_011() {
		$incrementalVersion = '0.1.1';
		
		// Add bottomLeft-Right port orientation
		$this->qls->SQL->insert('shared_object_portOrientation', array('value', 'name', 'defaultOption'), array(4, 'BottomLeft-Right', 0));
		
		// Change mail method from sendmail to proxy
		$query = $this->qls->SQL->select('value', 'config', array('name' => array('=', 'mail_method')));
		$result = $row = $this->qls->SQL->fetch_assoc($query);
		$mailMethod = $result['value'];
		if($mailMethod == 'sendmail') {
			$this->qls->SQL->update('config', array('value' => 'proxy'), array('name' => array('=', 'mail_method')));
		}
		
		// Add "version" column to "app_organization_data" table
		$this->qls->SQL->alter('app_organization_data', 'add', 'version', 'VARCHAR(15)');
		
		// Set app version to 0.1.1
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add "entitlement_id" column to "app_organization_data" table
		$this->qls->SQL->alter('app_organization_data', 'add', 'entitlement_id', 'VARCHAR(40)');
		$this->qls->SQL->alter('app_organization_data', 'add', 'entitlement_last_checked', 'int(11)');
		$this->qls->SQL->alter('app_organization_data', 'add', 'entitlement_data', 'VARCHAR(255)');
		$this->qls->SQL->alter('app_organization_data', 'add', 'entitlement_comment', 'VARCHAR(10000)');
		
		$entitlementDataArray = array('cabinetCount' => 5, 'objectCount' => 20, 'connectionCount' => 40, 'userCount' => 2);
		$entitlementData = json_encode($entitlementDataArray);
		$updateValues = array(
			'entitlement_id' => 'None',
			'entitlement_last_checked' => 0,
			'entitlement_data' => $entitlementData,
			'entitlement_comment' => 'Never Checked.'
		);
		$this->qls->SQL->update('app_organization_data', $updateValues, array('id' => array('=', 1)));
		
		
		
		//
		// Correct duplicate template names
		//
		$foundArray = array();
		$query = $this->qls->SQL->select('*', 'app_inventory');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			$rowID = $row['id'];
			
			$aID = $row['a_object_id'];
			$aFace = $row['a_object_face'];
			$aDepth = $row['a_object_depth'];
			$aPort = $row['a_port_id'];
			
			$bID = $row['b_object_id'];
			$bFace = $row['b_object_face'];
			$bDepth = $row['b_object_depth'];
			$bPort = $row['b_port_id'];
			
			if($aID == $bID and $aFace == $bFace and $aDepth == $bDepth and $aPort == $bPort) {
				if($aID != 0) {
					if($row['a_id'] != 0 or $row['b_id'] != 0) {
						$updateValues = array(
							'a_object_id' => 0,
							'a_object_face' => 0,
							'a_object_depth' => 0,
							'a_port_id' => 0,
							'b_object_id' => 0,
							'b_object_face' => 0,
							'b_object_depth' => 0,
							'b_port_id' => 0
						);
						$this->qls->SQL->update('app_inventory', $updateValues, array('id' => array('=', $rowID)));
					} else {
						$this->qls->SQL->delete('app_inventory', array('id' => array('=', $rowID)));
					}
				}
			}
		}
		
		
		
		//
		// Correct duplicate template names
		//
		$templateNameArray = array();
		$query = $this->qls->SQL->select('*', 'app_object_templates');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			$templateID = $row['id'];
			$templateName = $row['templateName'];
			if(in_array($templateName, $templateNameArray)) {
				$newTemplateName = $templateName.'_'.$this->generateUniqueNameValue();
				$this->qls->SQL->update('app_object_templates', array('templateName' => $newTemplateName), array('id' => array('=', $templateID)));
			}
			array_push($templateNameArray, $templateName);
		}
		
		
		
		//
		// Correct duplicate location names
		//
		$envTreeArray = array();
		$query = $this->qls->SQL->select('*', 'app_env_tree');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			if(!isset($envTreeArray[$row['parent']])) {
				$envTreeArray[$row['parent']] = array();
			}
			$workingArray = array($row['id'], $row['name']);
			array_push($envTreeArray[$row['parent']], $workingArray);
		}
		
		foreach($envTreeArray as $parentID => $parent) {
			$nameArray = array();
			foreach($parent as $child) {
				$nodeID = $child[0];
				$nodeName = $child[1];
				if(in_array($nodeName, $nameArray)) {
					$uniqueValue = $this->generateUniqueNameValue();
					$uniqueName = $nodeName.'_'.$uniqueValue;
					$this->qls->SQL->update('app_env_tree', array('name' => $uniqueName), array('id' => array('=', $nodeID)));
				}
				array_push($nameArray, $child[1]);
			}
		}
		
		
		
		//
		// Clear out orphaned cabinet adjacency entries
		//
		$envTreeIDArray = array();
		$query = $this->qls->SQL->select('*', 'app_env_tree');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			array_push($envTreeIDArray, $row['id']);
		}
		
		$query = $this->qls->SQL->select('*', 'app_cabinet_adj');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			
			// Gather entry details
			$rowID = $row['id'];
			$leftCabinetID = $row['left_cabinet_id'];
			$rightCabinetID = $row['right_cabinet_id'];
			
			// Delete entry if either of the cabinets does not exist
			if(!in_array($leftCabinetID, $envTreeIDArray) or !in_array($rightCabinetID, $envTreeIDArray)) {
				$this->qls->SQL->delete('app_cabinet_adj', array('id' => array('=', $rowID)));
			}
		}
		
		
		
		//
		// Clear out orphaned cable path entries
		//
		$query = $this->qls->SQL->select('*', 'app_cable_path');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			
			// Gather entry details
			$rowID = $row['id'];
			$cabinetAID = $row['cabinet_a_id'];
			$cabinetBID = $row['cabinet_b_id'];
			
			// Delete entry if either of the cabinets does not exist
			if(!isset($this->qls->envTreeArray[$cabinetAID]) or !isset($this->qls->envTreeArray[$cabinetBID])) {
				$this->qls->SQL->delete('app_cable_path', array('id' => array('=', $rowID)));
			}
		}
		
		
		
		//
		// Resolve duplicate cabinet adjacencies
		//
		$leftArray = array();
		$rightArray = array();
		$query = $this->qls->SQL->select('*', 'app_cabinet_adj');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			$rowID = $row['id'];
			$leftCabinetID = $row['left_cabinet_id'];
			$rightCabinetID = $row['right_cabinet_id'];
			
			if(in_array($leftCabinetID, $leftArray) or in_array($rightCabinetID, $rightArray)) {
				$this->qls->SQL->delete('app_cabinet_adj', array('id' => array('=', $rowID)));
			}
			
			array_push($leftArray, $leftCabinetID);
			array_push($rightArray, $rightCabinetID);
		}
		
		
		
		// Update current version
		$this->currentVersion = $incrementalVersion;
		return true;
	}

	/**
	 * Retrieves currently running version number from database
	 * @return string
	 */
	function getVersion() {
		$query = $this->qls->SQL->select('*', 'app_organization_data');
		$row = $this->qls->SQL->fetch_array($query);
		if(isset($row['version'])) {
			return $row['version'];
		} else {
			// Assume version is 0.1.0 if not set
			return '0.1.0';
		}
	}
	
	/**
	 * Generates unique string to prevent duplicate names
	 * @return string
	 */
	function generateUniqueNameValue(){
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$length = 4;
		$charactersLength = strlen($characters);
		$uniqueNameValue = '';
		for($i = 0; $i < $length; $i++) {
			$uniqueNameValue .= $characters[rand(0, $charactersLength - 1)];
		}
		return $uniqueNameValue;
	}
	
	/**
	 * 0.2.2 - Fix static port name field
	 * @return string
	 */
	function fixPortNameFieldStatic(&$data){
		$nameRegEx = '/^[a-zA-Z0-9-\/\\\_]$/';
		$fieldValueArray = str_split($data);
		
		foreach($fieldValueArray as &$fieldValueCharacter) {
			if(!preg_match($nameRegEx, $fieldValueCharacter)){
				$fieldValueCharacter = '_';
			}
		}
		
		$data = implode('', $fieldValueArray);
	}
	
	/**
	 * 0.2.2 - Fix incremental port name field
	 * @return string
	 */
	function fixPortNameFieldIncremental(&$data){
		$portNameFieldIncrementalRegEx = '/^[a-zA-Z]$|^[0-9]$|^[1-9][0-9]+$/';
		
		if(!preg_match($portNameFieldIncrementalRegEx, $data)){
			$data = 1;
		}
		
	}
	
	/**
	 * 0.2.2 - Fix series port name field
	 * @return string
	 */
	function fixPortNameFieldSeries(&$data){
		$portNameFieldSeriesRegEx = '/^[a-zA-Z0-9\/\\\_]{0,250}$/';
		
		if(is_array($data) and (count($data) >= 1 and count($data) <= 100)) {
			foreach($data as &$item) {
				if (!preg_match($portNameFieldSeriesRegEx, $item)){
					$item = '_';
				}
			}
		}
		
	}
	
	/**
	 * 0.2.2 - Generate port name
	 * @return string
	 */
	function generatePortName($portNameFormat, $index, $portTotal) {
		$portString = '';
		$incrementalCount = 0;
		
		// Create character arrays
		$lowercaseIncrementArray = array();
		$uppercaseIncrementArray = array();
		for($x=97; $x<=122; $x++) {
			array_push($lowercaseIncrementArray, chr($x));
		}
		for($x=65; $x<=90; $x++) {
			array_push($uppercaseIncrementArray, chr($x));
		}
		
		// Account for infinite count incrementals
		foreach($portNameFormat as &$itemA) {
			$type = $itemA['type'];
			
			if($type == 'incremental' or $type == 'series') {
				$incrementalCount++;
				if($itemA['count'] == 0) {
					$itemA['count'] = $portTotal;
				}
			}
		}
		
		foreach($portNameFormat as $itemB) {
			$type = $itemB['type'];
			$value = $itemB['value'];
			$order = $itemB['order'];
			$count = $itemB['count'];
			
			if($type == 'static') {
				$portString = $portString.$value;
			} else if($type == 'incremental' or $type == 'series') {
				$numerator = 1;
				if($order < $incrementalCount) {
					foreach($portNameFormat as $itemC) {
						$typeC = $itemC['type'];
						$orderC = $itemC['order'];
						$countC = $itemC['count'];
						
						if($typeC == 'incremental' or $typeC == 'series') {
							if($order < $orderC) {
								$numerator *= $countC;
							}
						}
					}
				}
				
				
				
				$howMuchToIncrement = floor($index / $numerator);
				if($howMuchToIncrement >= $count) {
					$rollOver = floor($howMuchToIncrement / $count);
					$howMuchToIncrement = $howMuchToIncrement - ($rollOver * $count);
				}
				
				if($type == 'incremental') {
					if(is_numeric($value)) {
						$value = $value + $howMuchToIncrement;
						$portString = $portString.$value;
					} else {
						$asciiValue = ord($value);
						$asciiIndex = $asciiValue + $howMuchToIncrement;
						if($asciiValue >= 65 && $asciiValue <= 90) {
							// Uppercase
							
							while($asciiIndex > 90) {
								$portString = $portString.$uppercaseIncrementArray[0];
								$asciiIndex -= 26;
							}
							$portString = $portString.$uppercaseIncrementArray[$asciiIndex-65];
						} else if($asciiValue >= 97 && $asciiValue <= 122) {
							// Lowercase
							while($asciiIndex > 122) {
								$portString = $portString.$lowercaseIncrementArray[0];
								$asciiIndex -= 26;
							}
							$portString = $portString.$lowercaseIncrementArray[$asciiIndex-97];
						}
					}
					
				} else if($type == 'series') {
					$portString = $portString.$value[$howMuchToIncrement];
				}
			}
		}
			
		return $portString;
	}
	
	/**
	 * 0.2.2 - Find all partitions with duplicate port IDs and make them unique
	 * @return boolean
	 */
	function findAndFixDuplicatePortIDs(&$partitionData){
		$portCollection = array();
		$fieldStatic = array(
			'type' => 'static',
			'value' => '',
			'count' => 0,
			'order' => 0
		);
		$fieldIncremental = array(
			'type' => 'incremental',
			'value' => 1,
			'count' => 0,
			'order' => 0
		);
		foreach($partitionData as &$partition) {
			if($partition['partitionType'] == 'Connectable') {
				
				// Reset duplicate found flag
				$duplicateFound = false;
				
				// Collect all port IDs for partition
				$portNameFormat = $partition['portNameFormat'];
				$portTotal = $partition['valueX'] * $partition['valueY'];
				for($x=0; $x<$portTotal; $x++) {
					$portName = $this->generatePortName($portNameFormat, $x, $portTotal);
					if(in_array($portName, $portCollection)) {
						$duplicateFound = true;
					}
					array_push($portCollection, $portName);
				}
				
				// Oh shit, duplicate found... implement evasive maneuvers!  Whatever you do, DON'T PANIC!!!
				if($duplicateFound) {
					
					// Get incremental count
					$incrementalCount = 1;
					foreach($portNameFormat as $field) {
						$fieldType = $field['type'];
						if($fieldType == 'series' or $fieldType == 'incremental') {
							$incrementalCount++;
						}
					}
					
					// Patch up the casualty
					$fieldStatic['value'] = '_'.$this->generateUniqueNameValue();
					$fieldIncremental['order'] = $incrementalCount;
					
					// Deploy counter measures!!!
					array_push($partition['portNameFormat'], $fieldStatic, $fieldIncremental);
				}
			} else if(isset($partition['children'])) {
				$this->findAndFixDuplicatePortIDs($partition['children']);
			}
		}
		unset($partition);
		return true;
	}
	
	/**
	 * 0.2.2 - Fix port name format
	 * @return string
	 */
	function fixPortNameFormat(&$data){
		foreach($data as &$partition) {
			$partitionType = $partition['partitionType'];
			if($partitionType == 'Connectable') {
				
				$portTotal = $partition['valueX'] * $partition['valueY'];
				$portNameData = &$partition['portNameFormat'];
				$success = true;
				$fieldLength = 1;
				$hasIncremental = false;
				$hasInfiniteIncremental = false;
				$incrementalCount = 0;
				
				foreach($portNameData as &$portNameField) {
					$type = $portNameField['type'];
					if($type == 'static') {
						
						$this->fixPortNameFieldStatic($portNameField['value']);
						
					} else if($type == 'incremental') {
						
						$incrementalCount++;
						$hasIncremental = true;
						$fieldLength *= $portNameField['count'];
						
						$this->fixPortNameFieldIncremental($portNameField['value']);
						
						if($portNameField['count'] == 0) {
							$hasInfiniteIncremental = true;
						}
						
					} else if($type == 'series') {
						
						$incrementalCount++;
						$hasIncremental = true;
						$fieldLength *= count($portNameField['value']);
						
						$this->fixPortNameFieldSeries($portNameField['value']);
						
					}
				}
				
				// Check for duplicate port IDs
				if($portTotal > 1) {
					if($hasIncremental) {
						if(!$hasInfiniteIncremental) {
							if($fieldLength < $portTotal) {
								$success = false;
							} else {
								// ... Could still be duplicates, better check 'em all.
								$workingArray = array();
								for($x = 0; $x < $portTotal; $x++) {
									$portName = $this->generatePortName($portNameData, $x, $portTotal);
									if(in_array($portName, $workingArray)) {
										$success = false;
									}
									array_push($workingArray, $portName);
								}
							}
						}
					} else {
						$success = false;
					}
				}
				
				if(!$success) {
					$newOrder = $incrementalCount + 1;
					
					$staticField = array(
						'type' => 'static',
						'value' => '_',
						'count' => 0,
						'order' => 0
					);
					
					$incrementalField = array(
						'type' => 'incremental',
						'value' => 1,
						'count' => 0,
						'order' => $newOrder
					);
					
					array_push($portNameData, $staticField);
					array_push($portNameData, $incrementalField);
				}
				
			}
			
			if(isset($partition['children'])) {
				$this->fixPortNameFormat($partition['children']);
			}
		}
		return true;
	}
	
	/**
	 * 0.2.2 - Find all partitions with duplicate port IDs and make them unique
	 * @return boolean
	 */
	function adjustPartitionHUnits(&$partitionData, $parentHUnits=24){
		foreach($partitionData as &$partition) {
			// Adjust hUnits
			$localHUnits = $partition['hUnits'];
			$newLocalHUnits = round(($localHUnits/10)*24);
			$partition['hUnits'] = $newLocalHUnits;
			
			// Adjust flex
			$localDirection = $partition['direction'];
			if($localDirection == 'column') {
				$newFlex = $newLocalHUnits/$parentHUnits;
				$parent['flex'] = $newFlex;
			}
			
			if(isset($partition['children'])) {
				$this->adjustPartitionHUnits($partition['children'], $localHUnits);
			}
		}
	}
	
	/**
	 * 0.2.2 - Find all enclosure partitions without tolerance set and set enclosure tolerance to "Loose"
	 * @return boolean
	 */
	function setEnclosureTolerance(&$partitionData){
		foreach($partitionData as &$partition) {
			
			if($partition['partitionType'] == 'Enclosure') {
				if(!isset($partition['encTolerance'])) {
					$partition['encTolerance'] = 'Loose';
				}
			}
			
			if(isset($partition['children'])) {
				$this->setEnclosureTolerance($partition['children']);
			}
		}
	}
	
	function updateObjectCompatibility($data, $templateID, $side, &$depthCounter=0){
		foreach($data as $partition) {
			$partitionType = $partition['partitionType'];
			if($partitionType == 'Connectable') {
				
				// Update object templates table
				$portNameFormatJSON = json_encode($partition['portNameFormat']);
				$this->qls->SQL->update('app_object_compatibility', array('portNameFormat' => $portNameFormatJSON), array('template_id' => array('=', $templateID), 'AND', 'side' => array('=', $side), 'AND', 'depth' => array('=', $depthCounter)));
				
				$depthCounter++;
			} else {
				
				$depthCounter++;
				if(isset($partition['children'])) {
					$this->updateObjectCompatibility($partition['children'], $templateID, $side, $depthCounter);
				}
				
			}
		}
	}
	
	function alterTemplatePartitionDataLayoutName(&$data){
		foreach($data as &$partition) {
			$partitionType = $partition['partitionType'];
			if($partitionType == 'Connectable' or $partitionType == 'Enclosure') {
				$layoutPrefix = ($partitionType == 'Connectable') ? 'port' : 'enc';
				
				// Change 'LayoutX' to 'valueX'
				if(isset($partition[$layoutPrefix.'LayoutX'])) {
					$valueX = $partition[$layoutPrefix.'LayoutX'];
					$partition['valueX'] = $valueX;
					unset($partition[$layoutPrefix.'LayoutX']);
				}
				
				// Change 'LayoutY' to 'valueY'
				if(isset($partition[$layoutPrefix.'LayoutY'])) {
					$valueY = $partition[$layoutPrefix.'LayoutY'];
					$partition['valueY'] = $valueY;
					unset($partition[$layoutPrefix.'LayoutY']);
				}
			}
			
			if(isset($partition['children'])) {
				$this->alterTemplatePartitionDataLayoutName($partition['children']);
			}
		}
		return true;
	}
	
	function alterTemplatePartitionDataDimensionUnits(&$data){
		foreach($data as &$partition) {
			
			// Change 'vunits' to 'vUnits'
			if(isset($partition['vunits'])) {
				$vUnitValue = $partition['vunits'];
				$partition['vUnits'] = $vUnitValue;
				unset($partition['vunits']);
			}
			
			// Change 'hunits' to 'hUnits'
			if(isset($partition['hunits'])) {
				$hUnitValue = $partition['hunits'];
				$partition['hUnits'] = $hUnitValue;
				unset($partition['hunits']);
			}
			
			if(isset($partition['children'])) {
				$this->alterTemplatePartitionDataDimensionUnits($partition['children']);
			}
		}
		return true;
	}

	/*
	function validateTrunkedEndpoint($input) {
		$trunkedEndpoint = false;
		foreach($input as $connectionPeer) {
			$objID = $connectionPeer[0];
			$objFace = $connectionPeer[1];
			$objDepth = $connectionPeer[2];
			$objPortID = $connectionPeer[3];
			if(isset($this->qls->App->peerArray[$objID][$objFace][$objDepth])) {
				// Partition is trunked
				$localPeerData = $this->qls->App->peerArray[$objID][$objFace][$objDepth];
				if($localPeerData['selfEndpoint']) {
					// Partition is an endpoint
					if($localPeerData['floorplanPeer']) {
						// Trunked relationship is floorplan object
						foreach($localPeerData['peerArray'] as $peerID => $levelObj) {
							foreach($levelObj as $peerFace => $levelFace) {
								foreach($levelFace as $peerDepth => $levelDepth) {
									foreach($levelDepth as $entry) {
										if($entry[0] == $objPortID) {
											$trunkedEndpoint = true;
										}
									}
								}
							}
						}
					} else {
						// Trunked relationship is entire partition which is not permitted
						$trunkedEndpoint = true;
					}
				}
			}
		}
		
		return $trunkedEndpoint;
	}
	*/
}
