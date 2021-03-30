<div id="pathCardBox" class="card">
<div class="card-header">Path
	<span>
		<div class="btn-group pull-right">
			<button type="button" class="btn btn-sm btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions <span class="m-l-5"><i class="fa fa-cog"></i></span></button>
			<div class="dropdown-menu">
				<a id="printFullPath" class="dropdown-item" href="#"><i class="ion-map"></i> Print</a>
			</div>
		</div>
	</span>
</div>
<div class="card-block">
	<blockquote class="card-blockquote">
		<div class="row">
			<div id="warningPathDiverges" class="alert alert-warning" style="display:none;" role="alert">
				<strong>Warning!</strong> Path diverges in a way that cannot be depicted below.  View full path in <a class="alert-link" href="diagram.php" target="_blank" rel="noopener noreferrer">Diagram</a>.
			</div>
		</div>
		<div class="row">
			<!-- Canvas for drawing cabinet connections -->
			<canvas id="canvasPath" style="z-index:1000;position:absolute; pointer-events:none;"></canvas>
			<div id="containerFullPath"></div>
		</div>
	</blockquote>
</div>
</div>