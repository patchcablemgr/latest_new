<?php

//Retreive categories
$category = array();
$categoryInfo = $qls->SQL->select('*', 'app_object_category');
while ($categoryRow = $qls->SQL->fetch_assoc($categoryInfo)){
	$category[$categoryRow['id']]['name'] = $categoryRow['name'];
}

//Retreive port orientation
$portOrientation = array();
$results = $qls->SQL->select('*', 'shared_object_portOrientation');
while ($row = $qls->SQL->fetch_assoc($results)){
	$portOrientation[$row['id']]['name'] = $row['name'];
}

//Retreive port type
$portType = array();
$results = $qls->SQL->select('*', 'shared_object_portType');
while ($row = $qls->SQL->fetch_assoc($results)){
	$portType[$row['id']]['name'] = $row['name'];
}

//Retreive media type
$mediaType = array();
$results = $qls->SQL->select('*', 'shared_mediaType');
while ($row = $qls->SQL->fetch_assoc($results)){
	$mediaType[$row['value']]['name'] = $row['name'];
}

//Retreive rackable objects
$objectProperties = array();
$results = $qls->SQL->select('*', 'table_object_properties', false, 'objType_id ASC, name ASC');
while ($row = $qls->SQL->fetch_assoc($results)){
	$objectProperties[$row['id']]['id'] = $row['id'];
	$objectProperties[$row['id']]['name'] = $row['name'];
	$objectProperties[$row['id']]['categoryName'] = $category[$row['category_id']]['name'];
	$objectProperties[$row['id']]['categoryColor'] = $category[$row['category_id']]['color'];
	$objectProperties[$row['id']]['RUSize'] = $row['RUSize'];
	$objectProperties[$row['id']]['objType_id'] = $row['objType_id'];
	$objectProperties[$row['id']]['encLayoutX'] = $row['encLayoutX'];
	$objectProperties[$row['id']]['encLayoutY'] = $row['encLayoutY'];
	$objectProperties[$row['id']]['portLayoutX'] = $row['portLayoutX'];
	$objectProperties[$row['id']]['portLayoutY'] = $row['portLayoutY'];
	$objectProperties[$row['id']]['portOrientation'] = $portOrientation[$row['portOrientation_id']]['name'];
	$objectProperties[$row['id']]['portType'] = $portType[$row['portOrientation_id']]['name'];
	$objectProperties[$row['id']]['mediaType'] = $mediaType[$row['mediaType_id']]['name'];
}
?>
<!--
/////////////////////////////
//Object Details
/////////////////////////////
-->
		<div class="card-box">
			<h4 class="header-title m-t-0 m-b-20">Cabinet</h4>
			<div id="objectCardBox" class="card" style="display:none;">
				<div class="card-header">
					Object Details
				</div>
				<div class="card-block">
					<blockquote class="card-blockquote">
						<div id="objectDetails">
							<input id="selectedObjectID" type="hidden">
							<table>
								<tr>
									<td class="objectDetailAlignRight">
										<strong>Name:&nbsp&nbsp</strong>
									</td>
									<td>
										<span id="detailObjName"><a href="#" id="inline-name" data-type="text">-</a></span>
									</td>
								</tr>
								<tr>
									<td class="objectDetailAlignRight">
										<strong>Trunked To:&nbsp&nbsp</strong>
									</td>
									<td>
										<span id="detailTrunkedTo">-</span>
									</td>
								</tr>
								<tr>
									<td class="objectDetailAlignRight">
										<strong>Type:&nbsp&nbsp</strong>
									</td>
									<td>
										<span id="detailObjType">-</span>
									</td>
								</tr>
								<tr>
									<td class="objectDetailAlignRight">
										<strong>Category:&nbsp&nbsp</strong>
									</td>
									<td>
										<span id="detailCategoryName">-</span>
									</td>
								</tr>
								<tr>
									<td class="objectDetailAlignRight">
										<strong>RU Size:&nbsp&nbsp</strong>
									</td>
									<td>
										<span id="detailRUSize">-</span>
									</td>
								</tr>
								<tr>
									<td class="objectDetailAlignRight">
										<strong>Port Count:&nbsp&nbsp</strong>
									</td>
									<td>
										<span id="detailPortCount">-</span>
									</td>
								</tr>
								<tr>
									<td class="objectDetailAlignRight">
										<strong>Port Type:&nbsp&nbsp</strong>
									</td>
									<td>
										<span id="detailPortType">-</span>
									</td>
									
								</tr>
								<tr>
									<td class="objectDetailAlignRight">
										<strong>Media Type:&nbsp&nbsp</strong>
									</td>
									<td>
										<span id="detailMediaType">-</span>
									</td>
									
								</tr>
							</table>
						</div>
						<button id="objDelete" type="button" class="btn btn-sm btn-danger waves-effect waves-light">
							<span class="btn-label"><i class="fa fa-times"></i></span>Delete
						</button>
					</blockquote>
				</div>
			</div>
<!--
/////////////////////////////
//Cabinet Details
/////////////////////////////
-->
				<div id="cabinetCardBox" class="card" style="display:none;">
					<!-- Cabinet Details -->
					<div class="card-header">Cabinet Details</div>
					<div class="card-block">
						<blockquote class="card-blockquote">
							<input id="selectedCabinetID" type="hidden">
							<table>
								<tr>
									<td class="objectDetailAlignRight">
										<strong>RU:&nbsp&nbsp</strong>
									</td>
									<td>
										<input id="cabinetSizeInput" class="form-control form-control-sm" style="max-width:75px;" type="number" name="RU" min="1" max="50">
									</td>
								</tr>
							</table>
							<!-- Cable path table -->
							<h4 class="header-title m-t-20">Cable Paths:</h4>
							<!--
							<p class="text-muted font-13 m-b-10">
								Your awesome text goes here.Your awesome text goes here.
							</p>-->

							<div class="p-20">
								<table class="table table-sm">
									<thead>
									<tr>
										<th>Cabinet</th>
										<th>Distance (m)</th>
										<th>Notes</th>
										<th></th>
									</tr>
									</thead>
									<tbody id="cablePathTableBody">
									</tbody>
								</table>
								<button id="pathAdd" type="button" class="btn btn-sm btn-success waves-effect waves-light">+ Add Path</button>
							</div>
						</blockquote>
					</div>
				</div>
<!--
/////////////////////////////
//Placeable objects
/////////////////////////////
-->
			<div class="card">
				<div class="card-header">
					Available Objects
				</div>
				<div class="card-block">
					<?php
					foreach ($objectProperties as $object){
						
						//
						//Create 'Generic' objects with objType_id = 1
						//
						
						if ($object['objType_id'] == 1){
							?>
							<div class="object-wrapper">
								<h4 class="header-title m-t-0 m-b-15"><?php echo $object['name']; ?></h4>
								<div data-cabinet-objectID="" data-custom-objectID="<?php echo $object['id']; ?>" data-custom-objectTypeID="<?php echo $object['objType_id']; ?>" data-RUSize="<?php echo $object['RUSize'];?>" class="<?php echo 'category'.$object['categoryName'].' RU'.$object['RUSize'];?> obj-style obj-border draggable stockObj">
								</div>
							</div>
							
						<?php
						//
						//Create 'Connectable' objects with objType_id = 2
						//
						} elseif ($object['objType_id'] == 2){
							$xWidth = 100/$object['portLayoutX'];
							$yWidth = 100/$object['portLayoutY'];
							?>
							<div class="object-wrapper">
								<h4 class="header-title m-t-0 m-b-15"><?php echo $object['name']; ?></h4>
								<div data-cabinet-objectID="" data-custom-objectID="<?php echo $object['id']; ?>" data-custom-objectTypeID="<?php echo $object['objType_id']; ?>" data-RUSize="<?php echo $object['RUSize'];?>" class="<?php echo 'category'.$object['categoryName'].' RU'.$object['RUSize'];?> obj-style obj-border draggable stockObj">
									<table style="border-collapse: collapse;height:100%;width:100%;">
									<?php
										for ($y=0; $y<$object['portLayoutY']; $y++) {
											echo "<tr style='width:100%;height:".$yWidth."%;'>";
											for ($x=0; $x<$object['portLayoutX']; $x++){
												echo "<td style='width:".$xWidth."%;height:".$yWidth."%;'>";
												echo "<div class='port ".$object['portType']."'></div>";
												echo "</td>";
											}
											echo "</tr>";
										}
									?>
									</table>
								</div>
							</div>
						<?php
						
						//
						//Create 'Enclosure' objects with objType_id = 3
						//
						
						} elseif ($object['objType_id'] == 3){
							$width = 100/$object['encLayoutX'];
							$height = 100/$object['encLayoutY'];
							echo '<div class="object-wrapper">';
								echo '<h4 class="header-title m-t-0 m-b-15">'.$object['name'].'</h4>';
								echo '<div data-cabinet-objectID="" data-custom-objectID="'.$object['id'].'" data-custom-objectTypeID="'.$object['objType_id'].'" data-RUSize="'.$object['RUSize'].'" class="category'.$object['categoryName'].' RU'.$object['RUSize'].' obj-style draggable stockObj">';
									echo '<table style="border-collapse: collapse;height:100%;width:100%;">';
									for ($y=0; $y<$object['encLayoutY']; $y++) {
										echo '<tr style="width:100%;height:'.$height.'%;">';
										for ($x=0; $x<$object['encLayoutX']; $x++) {
											echo '<td data-encx="'.$x.'" data-ency="'.$y.'" class="encTable obj-border" style="width:'.$width.'%; height:'.$height.'%;"></td>';
										}
										echo '</tr>';
									}
									echo '</table>';
								echo '</div>';
							echo '</div>';
						
						//
						//Create 'Insert' objects with objType_id = 4
						//
						} elseif ($object['objType_id'] == 4){
							$usedInserts = array();
							$width = 100/$object['encLayoutX'];
							$height = (25*$object['RUSize'])/$object['encLayoutY'];
							echo '<div class="object-wrapper">';
							echo '<h4 class="header-title m-t-0 m-b-15">'.$object['name'].'</h4>';
							echo '<div data-cabinet-objectID="" data-custom-objectID="'.$object['id'].'" data-custom-objectTypeID="'.$object['objType_id'].'" data-RUSize="'.$object['RUSize'].'" class="insertDraggable stockObj category'.$object['categoryName'].' RU'.$object['RUSize'].'" style="width:'.$width.'%; height:'.$height.'px;">';
							echo '<table class="portTable" style="border-collapse: collapse;height:100%;width:100%;">';
							for ($portY=0; $portY<$object['portLayoutY']; $portY++) {
								echo '<tr style="width:100%;height:'.$portHeight.'%;">';
								for ($portX=0; $portX<$object['portLayoutX']; $portX++) {
									echo '<td style="width:'.$portWidth.'%;height:'.$portHeight.'%;">';
									echo '<div class="port '.$object['portType'].'"></div>';
									echo '</td>';
								}
								echo '</tr>';
							}
							echo '</table>';
							echo '</div>';
							echo '</div>';
						}
					}
					?>
				</div>
			</div>
		</div>
