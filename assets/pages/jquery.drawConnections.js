// ### Cabinet Functions ###
function drawCabinet(){
	
	resizeCabinetCanvas();
	clearCabinetConnections();
	crawlCabinet();
	drawCabinetConnections();
	drawCabinetTrunks();
}

function crawlCabinet(){
	
	var pathSourceArray = {
		'selected': $(document).data('selectedPort'),
		'hovered': $(document).data('hoveredPort')
	};
	
	var sourceTypeMap = {
		'selected': ['cabinetSelectedConnections', 'cabinetSelectedTrunks'],
		'hovered': ['cabinetHoveredConnections', 'cabinetHoveredTrunks']
	}
	
	$.each(pathSourceArray, function(pathSourceType, pathSource){
		var connectionArray = [];
		var trunkArray = [];
		
		if(pathSource !== undefined && pathSource !== false) {
			var selectedPort = $(pathSource);
			
			for(x=0; x<2; x++) {
				if(x == 1) {
					// Crawl trunk peer
					var selectedPartition = $(pathSource).closest('.partition');
					var selectedPartitionPeerID = $(selectedPartition).data('peerGlobalId');
					
					if($('#'+selectedPartitionPeerID).length) {
						var selectedPartitionPeer = $('#'+selectedPartitionPeerID);
						trunkArray.push([selectedPartition, selectedPartitionPeer]);
						
						var selectedPartitionPeerIDArray = selectedPartitionPeerID.split('-');
						var peerID = selectedPartitionPeerIDArray[2];
						var peerFace = selectedPartitionPeerIDArray[3];
						var peerDepth = selectedPartitionPeerIDArray[4];
						var peerPort = $(pathSource).data('portIndex');
						
						var selectedPort = $('#port-4-'+peerID+'-'+peerFace+'-'+peerDepth+'-'+peerPort);
					} else {
						if(selectedPartitionPeerID != 'none') {
							trunkArray.push([selectedPartition, selectedPartitionPeerID]);
						}
						var selectedPort = false;
					}
				}
				
				while($(selectedPort).length) {
					
					// Crawl connection peer
					var connectedPortIDString = $(selectedPort).data('connectedGlobalId');
					var connectedPortIDArray = JSON.parse(atob(connectedPortIDString));
					
					if(connectedPortIDArray.length) {
						var peerPortFound = false;
						$.each(connectedPortIDArray, function(index, connectedPortID){
							var connectedPort = $('#'+connectedPortID);
							if($(connectedPort).length) {
								
								connectionArray.push([selectedPort, connectedPort]);
								
								var connectedPartition = $(connectedPort).closest('.partition');
								var connectedPartitionPeerID = $(connectedPartition).data('peerGlobalId');
								
								if($('#'+connectedPartitionPeerID).length) {
									
									var connectedPartitionPeer = $('#'+connectedPartitionPeerID);
									trunkArray.push([connectedPartition, connectedPartitionPeer]);
									
									var connectedPartitionPeerIDArray = connectedPartitionPeerID.split('-');
									var peerID = connectedPartitionPeerIDArray[2];
									var peerFace = connectedPartitionPeerIDArray[3];
									var peerDepth = connectedPartitionPeerIDArray[4];
									
									var connectedPortIDArray = connectedPortID.split('-');
									var peerPort = connectedPortIDArray[5];
									selectedPort = $('#port-4-'+peerID+'-'+peerFace+'-'+peerDepth+'-'+peerPort);
									peerPortFound = true;
								} else {
									if(connectedPartitionPeerID != 'none') {
										trunkArray.push([connectedPartition, connectedPartitionPeerID]);
									}
								}
								
							} else {
								connectionArray.push([selectedPort, connectedPortID]);
							}
						});
						if(peerPortFound == false) {
							selectedPort = false;
						}
					} else {
						selectedPort = false;
					}
				}
			}
		}
		
		$(document).data(sourceTypeMap[pathSourceType][0], connectionArray);
		$(document).data(sourceTypeMap[pathSourceType][1], trunkArray);
	});
}

function drawCabinetConnections(){
	
	pathDataTypeArray = [
		'cabinetSelectedConnections',
		'cabinetHoveredConnections'
	];
	
	cabinetCtx.strokeStyle = connectionLineColor;
	cabinetCtx.lineWidth = connectionLineWidth;
	cabinetCtx.beginPath();
	
	$.each(pathDataTypeArray, function(pathDataTypeIndex, pathDataType){
		
		var pathData = $(document).data(pathDataType);
		
		$.each(pathData, function(index, element){
			var elemA = element[0];
			var elemB = element[1];
			
			var connectionStyle = $('#connectionStyle').val();
			
			var elemACabinet = $(elemA).closest('.cabinetContainer');
			var elemACabinetID = $(elemACabinet).data('cabinetId');
			var elemACabinetDimensions = getDimensions(elemACabinet);
			var elemADimensions = getDimensions(elemA);
			var elemAPartition = $(elemA).closest('.partition');
			var elemAPartitionDimensions = getDimensions(elemAPartition);
			
			cabinetCtx.moveTo(elemADimensions.centerX, elemADimensions.centerY);
			
			if(typeof elemB == 'object') {
				var elemBCabinet = $(elemB).closest('.cabinetContainer');
				var elemBCabinetID = $(elemBCabinet).data('cabinetId');
				var elemBCabinetDimensions = getDimensions(elemBCabinet);
				var elemBDimensions = getDimensions(elemB);
				var elemBPartition = $(elemB).closest('.partition');
				var elemBPartitionDimensions = getDimensions(elemBPartition);

				if(elemBDimensions.top >= elemADimensions.top) {
					var elemAPartHBoundary = elemAPartitionDimensions.bottom;
					var elemBPartHBoundary = elemBPartitionDimensions.top;
				} else {
					var elemAPartHBoundary = elemAPartitionDimensions.top;
					var elemBPartHBoundary = elemBPartitionDimensions.bottom;
				}
				
				if(connectionStyle == 0) {
					cabinetCtx.lineTo(elemADimensions.centerX, elemAPartHBoundary);
					cabinetCtx.lineTo(elemACabinetDimensions.left - canvasInset, elemAPartHBoundary);
					
					if(elemACabinetID != elemBCabinetID) {
						
						// Ports are in different cabinets
						if(elemACabinetDimensions.top >= elemBCabinetDimensions.top) {
							
							// Connection should be routed up
							var elemACabinetHBoundary = elemACabinetDimensions.top - canvasInset;
						} else {
							
							// Connection should be routed down
							var elemACabinetHBoundary = elemACabinetDimensions.bottom + canvasInset;
						}
						
						cabinetCtx.lineTo(elemACabinetDimensions.left - canvasInset, elemACabinetHBoundary);
						cabinetCtx.lineTo(elemBCabinetDimensions.left - canvasInset, elemACabinetHBoundary);

					}
					cabinetCtx.lineTo(elemBCabinetDimensions.left - canvasInset, elemBPartHBoundary);
					cabinetCtx.lineTo(elemBDimensions.centerX, elemBPartHBoundary);
					cabinetCtx.lineTo(elemBDimensions.centerX, elemBDimensions.centerY);
				} else if(connectionStyle == 1) {
					cabinetCtx.lineTo(elemBDimensions.centerX, elemBDimensions.centerY);
				} else if(connectionStyle == 2) {
					var arcSize = 30;
					cabinetCtx.bezierCurveTo((elemADimensions.centerX - arcSize), elemADimensions.centerY, (elemBDimensions.centerX - arcSize), elemBDimensions.centerY, elemBDimensions.centerX, elemBDimensions.centerY);
				} else {
					cabinetCtx.lineTo(elemBDimensions.centerX, elemBDimensions.centerY);
				}
			} else {
				cabinetCtx.lineTo(elemADimensions.centerX, elemAPartitionDimensions.top);
				cabinetCtx.lineTo(elemACabinetDimensions.left - canvasInset, elemAPartitionDimensions.top);
				cabinetCtx.lineTo(elemACabinetDimensions.left - canvasInset, elemACabinetDimensions.top - canvasInset);
				
				var left = elemACabinetDimensions.leftOrig - canvasInset;
				var top = elemACabinetDimensions.topOrig - canvasInset;
				addCabButton(left, top, elemB);
			}
			
		});
	});
	cabinetCtx.stroke();
}

function drawCabinetTrunks(){
	
	pathDataTypeArray = [
		'cabinetSelectedTrunks',
		'cabinetHoveredTrunks'
	];
	
	cabinetCtx.strokeStyle = trunkLineColor;
	cabinetCtx.lineWidth = trunkLineWidth;
	cabinetCtx.beginPath();
	
	$.each(pathDataTypeArray, function(pathDataTypeIndex, pathDataType){
		
		var pathData = $(document).data(pathDataType);
		
		$.each(pathData, function(index, element){
			
			var vertical = canvasInset + (5*index);
			
			var elemA = element[0];
			var elemB = element[1];
		
			var canvasDimensions = getDimensions($('#canvasCabinet'));
			var elemACabinet = $(elemA).closest('.cabinetContainer');
			var elemACabinetID = $(elemACabinet).data('cabinetId');
			var elemACabinetDimensions = getDimensions($(elemA).closest('.cabinetContainer'));
			var elemADimensions = getDimensions(elemA);
			
			cabinetCtx.moveTo(elemADimensions.right, elemADimensions.centerY);
			
			if(typeof elemB == 'object') {
				
				var elemBCabinet = $(elemB).closest('.cabinetContainer');
				var elemBCabinetID = $(elemBCabinet).data('cabinetId');
				var elemBCabinetDimensions = getDimensions($(elemB).closest('.cabinetContainer'));
				var elemBDimensions = getDimensions(elemB);
				
				cabinetCtx.lineTo(elemACabinetDimensions.right + vertical, elemADimensions.centerY);
				
				if(elemACabinetID != elemBCabinetID) {
					if(elemACabinetDimensions.top <= elemBCabinetDimensions.top) {
						elemBCabinetHBoundary = elemBCabinetDimensions.top - vertical;
					} else {
						elemBCabinetHBoundary = elemBCabinetDimensions.bottom + vertical;
					}
					cabinetCtx.lineTo(elemACabinetDimensions.right + vertical, elemBCabinetHBoundary);
					cabinetCtx.lineTo(elemBCabinetDimensions.right + vertical, elemBCabinetHBoundary);
				}
				cabinetCtx.lineTo(elemBCabinetDimensions.right + vertical, elemBDimensions.centerY);
				cabinetCtx.lineTo(elemBDimensions.right, elemBDimensions.centerY);

			} else {
				cabinetCtx.lineTo(elemACabinetDimensions.right + vertical, elemADimensions.centerY);
				cabinetCtx.lineTo(elemACabinetDimensions.right + vertical, elemACabinetDimensions.top - vertical);
				cabinetCtx.strokeRect(elemADimensions.left, elemADimensions.top, elemADimensions.width, elemADimensions.height);
				
				var left = elemACabinetDimensions.rightOrig + vertical;
				var top = elemACabinetDimensions.topOrig - vertical;
				addCabButton(left, top, elemB);
			}
			
		});
	});
	cabinetCtx.stroke();
}

function clearCabinetConnections(){
	
	var canvasHeight = $('#canvasCabinet').height();
	var canvasWidth = $('#canvasCabinet').width();
	cabinetCtx.clearRect(0, 0, canvasWidth, canvasHeight);
	$('a.addCabButton').remove();
}


// ### Path Functions ###
function drawPath(){
	
	resizePathCanvas();
	clearPathConnections();
	crawlPathConnections();
	crawlPathTrunks();
	drawPathConnections();
	drawPathTrunks();
}

function crawlPathConnections(){
	
	$.each(pathArray, function(pathName, path){
		var pathConnections = [];
		var workingPathConnections = {};
		var connectorElementArray = $(path['container']).find('.port');
		
		// Group connection peers
		$.each(connectorElementArray, function(index, element){
			var connectionPairID = $(element).data('connectionPairId');
			if(workingPathConnections[connectionPairID] === undefined) {
				workingPathConnections[connectionPairID] = [];
			}
			workingPathConnections[connectionPairID].push($(element));
		});
		
		// Filter out singles
		$.each(workingPathConnections, function(index, element){
			if(element.length == 2) {
				pathConnections.push(element);
			}
		});
		
		// Store path connection data
		pathArray[pathName]['connections'] = pathConnections;
	});
}

function crawlPathTrunks(){
	
	$.each(pathArray, function(pathName, path){
		var pathTrunks = [];
		var workingPathTrunks = {};
		var connectorElementArray = $(path['container']).find('.objectBox');
		
		// Group trunk peers
		$.each(connectorElementArray, function(index, element){
			var trunkPairID = $(element).data('trunkPairId');
			if(workingPathTrunks[trunkPairID] === undefined) {
				workingPathTrunks[trunkPairID] = [];
			}
			workingPathTrunks[trunkPairID].push($(element));
		});
		
		// Filter out singles
		$.each(workingPathTrunks, function(index, element){
			if(element.length == 2) {
				pathTrunks.push(element);
			}
		});
		
		// Store path trunk data
		pathArray[pathName]['trunks'] = pathTrunks;
	});
}

function drawPathConnections(){
	
	$.each(pathArray, function(pathName, path){
		
		var canvas = path['canvas'];
		var connections = path['connections'];
		pathArray[pathName]['context'].strokeStyle = connectionLineColor;
		pathArray[pathName]['context'].lineWidth = connectionLineWidth;
		pathArray[pathName]['context'].beginPath();
		
		$.each(connections, function(index, element){
			var elemA = element[0];
			var elemB = element[1];
			
			var elemADimensions = getDimensions(elemA, canvas);
			var elemBDimensions = getDimensions(elemB, canvas);
			
			pathArray[pathName]['context'].moveTo(elemADimensions.centerX, elemADimensions.bottom);
			pathArray[pathName]['context'].bezierCurveTo(elemADimensions.centerX + 20, elemADimensions.bottom, elemBDimensions.centerX + 20, elemBDimensions.top, elemBDimensions.centerX, elemBDimensions.top);
			
		});
		pathArray[pathName]['context'].stroke();
	});
}

function drawPathTrunks(){
	
	$.each(pathArray, function(pathName, path){
		var pathTrunks = path['trunks'];
		var canvas = path['canvas'];
		pathArray[pathName]['context'].strokeStyle = trunkLineColor;
		pathArray[pathName]['context'].lineWidth = trunkLineWidth;
		pathArray[pathName]['context'].beginPath();
		
		$.each(pathTrunks, function(index, element){
			
			var pathOrientation = $('#pathOrientation').val();
			
			var elemA = element[0];
			var elemB = element[1];
			
			var elemADimensions = getDimensions(elemA, canvas);
			var elemBDimensions = getDimensions(elemB, canvas);
			
			if(elemADimensions.right > elemBDimensions.right) {
				var rightBoundary = elemADimensions.right;
			} else {
				var rightBoundary = elemBDimensions.right;
			}
			
			if(pathOrientation == "0") {
				
				// Patch Cable Adjacent
				pathArray[pathName]['context'].moveTo(elemADimensions.centerX, elemADimensions.bottom);
				pathArray[pathName]['context'].lineTo(elemADimensions.centerX, elemBDimensions.top);
				
			} else {
				
				// Patch Cable Inline
				pathArray[pathName]['context'].moveTo(elemADimensions.right, elemADimensions.centerY);
				pathArray[pathName]['context'].lineTo((rightBoundary + 20), elemADimensions.centerY);
				pathArray[pathName]['context'].lineTo((rightBoundary + 20), elemBDimensions.centerY);
				pathArray[pathName]['context'].lineTo(elemBDimensions.right, elemBDimensions.centerY);
				
			}
			
		});
		pathArray[pathName]['context'].stroke();
	});
}

function clearPathConnections(){
	
	$.each(pathArray, function(pathName, path){
		var canvas = path['canvas'];
		var canvasHeight = $(canvas).height();
		var canvasWidth = $(canvas).width();
		pathArray[pathName]['context'].clearRect(0, 0, canvasWidth, canvasHeight);
	});
}


// ### Common Functions ###
function resetConnectionData(){
	$(document).data('selectedPort', false);
	$(document).data('cabinetSelectedConnections', []);
	$(document).data('cabinetHoveredConnections', []);
	$(document).data('cabinetSelectedTrunks', []);
	$(document).data('cabinetHoveredTrunks', []);
	$.each(pathArray, function(pathName, path){
		pathArray[pathName]['connections'] = {};
		pathArray[pathName]['trunks'] = {};
	});
}

function getDimensions(elem, canvas=false){
	
	if(canvas == false) {
		var canvas = $('#canvasCabinet');
	}
	
	var canvasLeft = $(canvas).offset().left;
	var canvasTop = $(canvas).offset().top;
	var elemLeft = $(elem).offset().left;
	var elemTop = $(elem).offset().top;
	
	var elemLeftOrig = elemLeft;
	var elemTopOrig = elemTop;
	
	var elemWidth = $(elem).width();
	var elemHeight = $(elem).height();
	var elemCenterX = elemLeft - canvasLeft + (elemWidth / 2);
	var elemCenterY = elemTop - canvasTop  + (elemHeight / 2);
	var elemLeft = elemLeft - canvasLeft;
	var elemRight = elemLeft + elemWidth;
	var elemRightOrig = elemLeftOrig + elemWidth;
	var elemTop = elemTop - canvasTop;
	var elemBottom = elemTop + elemHeight;
	
	var dimensions = {
		leftOrig: elemLeftOrig,
		topOrig: elemTopOrig,
		rightOrig: elemRightOrig,
		left: elemLeft,
		right: elemRight,
		top: elemTop,
		bottom: elemBottom,
		centerX: elemCenterX,
		centerY: elemCenterY,
		width: elemWidth,
		height: elemHeight
	};
	return dimensions;
}

function addCabButton(left, top, globalID){
	var addCab = $('<a class="addCabButton" data-global-id="'+globalID+'" href="#"style="z-index:1001; position:absolute;"><i class="fa fa-plus" style="color:#039cfd; background-color:white;"></i></a>');
	$('#canvasCabinet').after(addCab);
	var left = left - ($(addCab).width()/2);
	var top = top - ($(addCab).height()/2);
	$(addCab).css({'left':left+'px', 'top':top+'px'});
	makeAddCabButtonClickable(addCab);
}

function highlightElement(elemArray, color){
	$.each(elemArray, function(index, elem){
		cabinetCtx.strokeStyle = color;
		cabinetCtx.beginPath();
		
		var elemDimensions = getDimensions(elem);
		
		cabinetCtx.strokeRect(elemDimensions.left, elemDimensions.top, elemDimensions.width, elemDimensions.height);
	});
}

function makePortsHoverable(){
	
	resetConnectionData();
	clearPathConnections();
	clearCabinetConnections();
	
	$('#buildSpaceContent').find('.port').each(function(){
		$(this).unbind('mouseenter mouseleave click.drawConnections');
	});
	$('#buildSpaceContent').find('.port').each(function(){
		$(this).hover(function(){
			$(document).data('hoveredPort', this);
			drawCabinet();
		}, function(){
			$(document).data('hoveredPort', false);
			drawCabinet();
		});
		
		$(this).on('click.drawConnections', function(){
			
			
			if($(document).data('selectedPort') !== undefined && $(document).data('selectedPort') !== false) {
				// Gather previously and currently selected port IDs for comparison
				var selectedPort = $(document).data('selectedPort');
				var selectedPortID = $(selectedPort).attr('id');
				var thisPortID = $(this).attr('id');
				
				// Compare previously and currently selected port IDs
				if(selectedPortID === thisPortID) {
					// Unselect port
					$(document).data('selectedPort', false);
				} else {
					// Select port
					$(document).data('selectedPort', this);
				}
			} else {
				$(document).data('selectedPort', this);
			}
			
			drawCabinet();
		});
	});

}

function makeCabArrowsClickable(){
	$('.cabMoveArrow').unbind('click');
	
	$('.cabMoveArrow').click(function(){
		var direction = $(this).data('cabMoveDirection');
		var cabinet = $(this).closest('.diagramCabinetContainer');
		if(direction == 'left') {
			$(cabinet).insertBefore($(cabinet).prev()).animate();
		} else {
			$(cabinet).insertAfter($(cabinet).next());
		}
		drawCabinet();
		
	});
}

function makeCabCloseClickable(){
	$('.cabClose').unbind('click');
	
	$('.cabClose').click(function(){
		var cabinet = $(this).closest('.diagramCabinetContainer');
		var locationBoxes = $(cabinet).parents('.diagramLocationBox');
		
		// Delete cabinet
		$(cabinet).remove();
		
		// Clean up empty location boxes
		$(locationBoxes).each(function(){
			if(!$(this).find('.diagramCabinetContainer').length) {
				$(this).remove();
			}
		});
		
		drawCabinet();

	});
}

function resizePathCanvas() {
	
	$.each(pathArray, function(pathName, path){
		
		var canvas = path['canvas'];
		$(canvas).attr('width', $(canvas).parent().width());
		$(canvas).attr('height', $(canvas).parent().height());
	});
}

function resizeCabinetCanvas() {
	
	var canvas = $('#canvasCabinet');
	
	if($(canvas).length) {
		$(canvas).attr('width', $(document).width());
		$(canvas).attr('height', $(document).height());
	}
}

function drawPathAndCabinet() {
	drawPath();
	drawCabinet();
}

function initializeCanvas() {
	
	window.addEventListener('resize', drawPathAndCabinet, false);
	connectionLineWidth = 3;
	connectionLineColor = 'LightSkyBlue';
	trunkLineWidth = 6;
	trunkLineColor = 'MidnightBlue';
	
	// Cabinet connections
	if($('#canvasCabinet').length) {
		canvasCabinet = document.getElementById('canvasCabinet');
		cabinetCtx = canvasCabinet.getContext('2d');
		$(document).data('cabinetSelectedConnections', []);
		$(document).data('cabinetHoveredConnections', []);
		$(document).data('cabinetSelectedTrunks', []);
		$(document).data('cabinetHoveredTrunks', []);
		canvasInset = 10;
	}
	
	// Path connections
	pathArray = {};
	if($('#canvasPath').length) {
		var canvas = $('#canvasPath');
		pathArray['path'] = {
			'context': $(canvas)[0].getContext('2d'),
			'canvas': canvas,
			'container' : $('#containerFullPath'),
			'connections': {},
			'trunks': {}
		};
	}
	
}

function initializeCanvasPathFinder(container) {
	var canvas = $('#canvasPathFinder');
	pathArray['pathFinder'] = {
		'context': $(canvas)[0].getContext('2d'),
		'canvas': canvas,
		'container' : container,
		'connections': {},
		'trunks': {}
	};
}