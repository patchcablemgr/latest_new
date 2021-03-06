<?php
	$page = basename($_SERVER['PHP_SELF']);
?>

<!-- Object Tree Modal -->
<div id="objectTreeModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="objectTreeModal" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="objectTreeModalLabel"></h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgObjTree" class="m-t-15"></div>
							</div>
						</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="card-box">
							<div id="objTree" class="navTree"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button id="buttonObjectTreeModalCancel" type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
				<button id="buttonObjectTreeModalSave" type="button" class="btn btn-primary waves-effect waves-light">Save</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Path Finder Modal -->
<div id="modalPathFinder" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="myModalLabel">Find Path</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgModal"></div>
							</div>
						</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="card-box">
							<div class="row">
								<div class="col-sm-6">
									<dl class="dl-horizontal row">
										<dt class="col-sm-3">Local Port:</dt>
										<dd id="pathFinderLocalPort" class="col-sm-9"></dd>
										<dt class="col-sm-3">Remote Port:</dt>
										<dd class="col-sm-9"><div id="pathFinderRemotePort"></div><div id="pathFinderTree" class="navTree m-b-30"></div></dd>
									</dl>
								</div>
								<div class="col-sm-6">
									<?php
									echo '<input id="pathFinderMaxResults" type="hidden" value="'.PATH_FINDER_MAX_RESULTS.'">';
									echo '<input id="pathFinderMaxResultsDefault" type="hidden" value="'.PATH_FINDER_MAX_RESULTS_DEFAULT.'">';
									echo '<input id="pathFinderMaxDepth" type="hidden" value="'.PATH_FINDER_MAX_DEPTH.'">';
									echo '<input id="pathFinderMaxDepthDefault" type="hidden" value="'.PATH_FINDER_MAX_DEPTH_DEFAULT.'">';
									?>
									<form class="form-horizontal">
										<div class="form-group row">
											<label for="rangeResults" class="col-sm-2 control-label"><b>Max Results</b><i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Maximum number of paths to return for each media type."></i></label>
											<div class="col-sm-10">
												<input type="text" id="rangeResults">
											</div>
										</div>
										<div class="form-group row">
											<label for="rangeDepth" class="col-sm-2 control-label"><b>Max Depth</b><i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Maximum number of cable jumpers for each path returned."></i></label>
											<div class="col-sm-10">
												<input type="text" id="rangeDepth">
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="row">
								<div title="Run">
									<button id="buttonPathFinderRun" class="btn btn-sm waves-effect waves-light btn-primary" type="button" disabled>
										<span class="btn-label"><i class="fa fa-cogs"></i></span>
										Find Paths
									</button>
								</div>
							</div>
						</div>
					</div>
					
				</div>
				<div class="row">
					<div class="col-sm-6">
						<div class="card-box">
							<div class="card">
								<div class="card-header">Path
								</div>
								<div class="card-block">
									<blockquote class="card-blockquote">
									<div class="table-responsive">
										<table id="cablePathTable" class="table table-striped table-bordered">
											<thead>
											<tr>
												<th>MediaType</th>
												<th>Local</th>
												<th>Adj.</th>
												<th>Path</th>
												<th>Total</th>
												<!--th></th-->
											</tr>
											</thead>
											<tbody id="cablePathTableBody">
											</tbody>
										</table>
									</div>
									</blockquote>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="card-box">
							<div class="card">
								<div class="card-header">Path
									<span>
										<div class="btn-group pull-right">
											<button type="button" class="btn btn-sm btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions <span class="m-l-5"><i class="fa fa-cog"></i></span></button>
											<div class="dropdown-menu">
												<a id="printPathFinder" class="dropdown-item" href="#" ><i class="ion-map"></i></span> Print</a>
											</div>
										</div>
									</span>
								</div>
								<div class="card-block">
									<blockquote class="card-blockquote">
										<canvas id="canvasPathFinder" style="z-index:1000;position:absolute; pointer-events:none;"></canvas>
										<div id="containerCablePath"></div>
									</blockquote>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Scan Modal -->
<div id="scanModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="myModalLabel">Scan Cable</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgScan"></div>
							</div>
						</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="card-box">
							<?php
								if($qls->user_info['scanMethod']) {
									$labelText = 'Manual Scan:';
									$manualDisplay = 'style="display:none;"';
									$barcodeDisplay = '';
								} else {
									$labelText = 'Barcode Scan:';
									$manualDisplay = '';
									$barcodeDisplay = 'style="display:none;"';
								}
							?>
						
							<input id="scanMethod" type="hidden" value="<?php echo $qls->user_info['scanMethod']; ?>">
						
							<div class="m-b-20">
								<label><?php echo $labelText; ?></label>
								<input id="manualCheckbox" type="checkbox" data-plugin="switchery" data-color="#f1b53d"/>
							</div>
							
							<div id="scannerContainer" <?php echo $barcodeDisplay; ?>>
								<div id="flashContainer" style="display:none;" class="m-b-20">
									<label>Flash:</label>
									<input id="torchCheckbox" type="checkbox" data-plugin="switchery" data-color="#f1b53d"/>
								</div>
								
								<div id="scanner" style="position: relative; width: 100%; height: auto; overflow: hidden; text-align: center;" class="viewport"></div>
							</div>
							
							<div id="manualEntry" <?php echo $manualDisplay; ?>>
								<form action="#">
								<input id="manualEntryInput" type="text" class="form-control m-b-10" placeholder="Enter cable ID">
								<button id="manualEntrySubmit" type="submit" class="btn btn-primary">Submit</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- image upload modal -->
<div id="modalImageUpload" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabelImageUpload" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="modalLabelImageUpload">Floorplan Image</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
					<div class="row">
						<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
							<div id="alertMsgImageUpload"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="card-box">
							<div class="p-20">
								<div class="form-group clearfix">
									<div id="containerFloorplanImage" class="col-sm-12 padding-left-0 padding-right-0">
										<input type="file" name="files[]" id="fileFloorplanImage" multiple="multiple">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- combined template modal -->
<div id="modalCreateCombinedTemplate" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabelCreateCombinedTemplate" aria-hidden="true">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="modalLabelImageUpload">Create Combined Template</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
					<div class="row">
						<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
							<div id="alertMsgCreateCombinedTemplate"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-body">
			
				<!-- Name -->
				<fieldset class="form-group">
					<label>Name <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Alphanumeric characters as well as hyphens (-), underscores (_), forward slashes (\/), and backslashes (\)."></i></label>
					<input id="inputCreateCombinedTemplateName" class="form-control" type="text" name="name" placeholder="New_Template" value="New_Template">
				</fieldset>
				
				<!-- Category -->
				<fieldset class="form-group">
					<label>Category <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Select a template category."></i></label>
					<select id="inputCreateCombinedTemplateCategory" name="category" class="form-control">
						<?php $qls->App->generateCategoryOptions(); ?>
					</select>
				</fieldset>
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
				<button id="buttonCreateCombinedTemplateModalSave" type="button" class="btn btn-primary waves-effect waves-light">Save</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- port name modal -->
<div id="portNameModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="portNameModal" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title">Port ID</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgPortName" class="m-t-15"></div>
							</div>
						</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="card-box">
						
							<fieldset class="m-b-20">
								<label>Port ID Fields:</label>
								<div id="portNameFieldContainer" class="row"></div>
							</fieldset>
							
							<fieldset class="m-b-20">
								<button id="buttonAddPortNameField" type="button" class="btn btn-success waves-effect waves-light">
								   <span class="btn-label"><i class="fa fa-plus"></i>
								   </span>Add Field
							   </button>
							   <button id="buttonDeletePortNameField" type="button" class="btn btn-danger waves-effect waves-light">
								   <span class="btn-label"><i class="fa fa-minus"></i>
								   </span>Remove Field
							   </button>
						   </fieldset>
						   
							<fieldset class="m-b-20">
								<label>Field Properties:</label>
								<table>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Type:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailPortNameType">
												<select id="selectPortNameFieldType" class="form-control">
													<option value="static">Static</option>
													<option value="incremental">Incremental</option>
													<option value="series">Series</option>
												</select>
											</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Count:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailPortNameCount">
												<input  id="inputPortNameFieldCount" class="form-control" type="number" min="0">
											</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Order:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailPortNameOrder">
												<select  id="selectPortNameFieldOrder" class="form-control">
													<option value="1">1st</option>
													<option value="2">2nd</option>
												</select>
											</span>
										</td>
									</tr>
								</table>
							</fieldset>
							
							<fieldset class="m-b-20">
								<label>Results:</label><br>
								<div id="portNameDisplayConfig"></div>
							</fieldset>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button id="buttonPortNameModalClose" type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- template category modal -->
<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modelLabelTemplateCategory" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="modelLabelTemplateCategory">Manage Categories</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgCategory"></div>
							</div>
						</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-6">
						<div class="card-box">
							<h4 class="header-title m-t-0 m-b-30">Create/Edit</h4>
							<form id="manageCategories-Form">
								<input id="inputCategoryID" type="hidden" name="id" value="0">
								<input type="hidden" name="action" value="add">
								<fieldset class="m-b-20">
									<label>Name</label>
									<input id="inputCategoryName" type="text" name="name" placeholder="Category Name" class="form-control">
								</fieldset>
								<fieldset class="m-b-20">
									<label>Category Color</label>
									<br><input id="color-picker" type='text' name='color'>
								</fieldset>
								<fieldset class="m-b-20">
									<div class="checkbox">
										<input id="inputCategoryDefault" type="checkbox" name="defaultOption">
										<label for="inputCategoryDefault">
											Default
										</label>
									</div>
								</fieldset>
								<fieldset>
									<button id="manageCategories-Save" type="button" class="btn btn-success waves-effect waves-light">
										<span class="btn-label"><i class="fa fa-check"></i></span>
										Save
									</button>
								</fieldset>
							</form>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="card-box">
							<h4 class="header-title m-t-0 m-b-30">Current</h4>
							<form id="manageCategoriesCurrent-Form">
								<input id="inputCategoryCurrentID" type="hidden" name="id" value="0">
								<input type="hidden" name="action" value="delete">
								<fieldset>
									<div id="categoryList">
									<?php echo $categoryList; ?>
									</div>
								</fieldset>
								<fieldset class="m-t-20">
									<button id="manageCategories-Delete" type="button" class="btn btn-danger waves-effect waves-light">
										<span class="btn-label"><i class="fa fa-times"></i></span>
										Delete
									</button>
								</fieldset>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- template catalog modal -->
<div id="modalTemplateCatalog" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabelTemplateCatalog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="modalLabelTemplateCatalog">Template Catalog</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgCatalog"></div>
							</div>
						</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-6">
						<div class="card-box">
							<h4 class="header-title m-t-0 m-b-30">Available Templates</h4>
							
							<div id="templateCatalogAvailableContainer">
								<h6>Name Filter:</h6>
								<select id="templateCatalogFilter" multiple data-role="tagsinput"></select>
								<div id="containerTemplateCatalog"></div>
							</div>
							<p class="m-t-10">
								<mark>Don't see what you're looking for?</mark><br>
								Email <ins>support@patchcablemgr.com</ins> with a link or description<br>
								of the item and we'll add it to the catalog.
							</p>
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-6">
						<div class="card-box">
							<h4 class="header-title m-t-0 m-b-30">Selected Template</h4>
							<button id="buttonTemplateCatalogImport" type="button" class="btn btn-primary waves-effect waves-light m-b-10" disabled>
								<span class="btn-label"><i class="fa fa-plus"></i>
							</span>Import</button>
							<div id="detailsContainer">
								<table>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Object Name:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogObjName" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Template Name:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogTemplateName" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Category:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogCategory" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight" valign="top">
											<strong>Trunked To:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogTrunkedTo" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Type:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogObjType" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Function:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogObjFunction" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>RU Size:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogRUSize" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Mount Config:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogMountConfig" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Port Range:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogPortRange" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Port Type:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogPortType" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Media Type:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogMediaType" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Image:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogImage" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- image upload modal -->
<div id="modalTemplateImageUpload" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabelImageUpload" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="modalLabelImageUpload">Template Image</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
					<div class="row">
						<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
							<div id="alertMsgImageUpload"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="card-box">
							<div class="p-20">
								<div class="form-group clearfix">
									<div id="containerTemplateImage" class="col-sm-12 padding-left-0 padding-right-0">
										<input type="file" name="files[]" id="fileTemplateImage" multiple="multiple">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- confirm modal -->
<div id="modalConfirm" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalConfirm" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="modalConfirmTitle"></h4>
			</div>
			<div class="modal-body" id="modalConfirmBody"></div>
			<div class="modal-footer">
				<button id="modalConfirmBtn" type="button" class="btn btn-secondary btn-danger waves-effect" data-toggle="modal" data-target="#modalConfirm">Confirm</button>
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- image upload modal -->
<div id="modalTemplateWhereUsed" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLableTemplateWhereUsed" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="modalLableTemplateWhereUsed">Where Used</h4>
			</div>
			<div class="modal-body col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
				<h4 id="whereUsedTemplateName"></h4>
				<div id="whereUsedResults" class="col-xs-6 col-sm-6 col-md-6 col-lg-6 col-xl-6"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="aboutModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="aboutModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
                <h4 class="modal-title" id="aboutModalLabel">About</h4>
            </div>
            <div id="aboutModalBody" class="modal-body">
				<span><strong>Version:</strong></span> <?php echo PCM_VERSION; ?><br><br>
				
				<strong>Changelog:</strong><br><br>
				<?php
					$handle = @fopen($_SERVER['DOCUMENT_ROOT'].'/CHANGELOG', 'r');
					if ($handle) {
						while (($buffer = fgets($handle, 4096)) !== false) {
							echo $buffer.'<br>';
						}
						if (!feof($handle)) {
							echo "Error: unexpected fgets() fail\n";
						}
						fclose($handle);
					}
				?>
			</div>
			<div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="confirmModal" style="z-index:2000;" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
                <h4 class="modal-title" id="messagesModalLabel">Confirm</h4>
            </div>
			<div class="modal-body">
				...
			</div>
			<div class="modal-footer">
				<button id="btnConfirm" type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Confirm</button>
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
            </div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="notificationsModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="notificationsModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
                <h4 class="modal-title" id="notificationsModalLabel">Notifications</h4>
            </div>
            <div id="notificationsModalBody" class="modal-body"></div>
			<div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="messagesModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="messagesModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
                <h4 class="modal-title" id="messagesModalLabel">Messages</h4>
            </div>
            <div class="modal-body">
				<div id="messagesModalBodyTable"></div>
				<div id="messagesModalBodyMessage" style="display:none;">
					<button id="messageModalButtonBack" type="button" class="btn btn-sm btn-secondary waves-effect">Back</button>
					&nbsp
					<div class="btn-group">
						<button type="button" class="btn btn-sm btn-secondary waves-effect">Prev</button>
						<button type="button" class="btn btn-sm btn-secondary waves-effect">Next</button>
					</div>
					&nbsp
					<button type="button" class="btn btn-sm btn-danger waves-effect waves-light">Delete</button>
					<hr>
					<div id="messagesModalBodyMessageContent"></div>
				</div>
			</div>
			<div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="removeUserModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="removeUserModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
                <h4 class="modal-title" id="removeUserModalLabel">Remove User</h4>
            </div>
            <div class="modal-body">
                Delete: username?
            </div>
			<div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary waves-effect waves-light">Ok</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="cancelEntitlementModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="cancelEntitlementModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
                <h4 class="modal-title" id="cancelEntitlementModalLabel">Cancel Entitlement</h4>
            </div>
            <div class="modal-body">
                Confirm cancelation.
            </div>
			<div class="modal-footer">
				<button id="confirmEntitlementCancellation" type="button" class="btn btn-secondary btn-danger waves-effect" data-toggle="modal" data-target="#cancelEntitlementModal">Confirm</button>
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
			</div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="importModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
                <h4 class="modal-title" id="importModalLabel">Import</h4>
            </div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgImport"></div>
							</div>
						</div>
				</div>
			</div>
            <div class="p-20">
				<div class="form-group clearfix">
					<div class="col-sm-12 padding-left-0 padding-right-0">
						<input type="file" name="files[]" id="fileDataImport" multiple="multiple">
					</div>
				</div>
			</div>
			<div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->