<!--
/////////////////////////////
//Cabinet Details
/////////////////////////////
-->

<div id="cabinetCardBox" class="card">
	<div class="card-header">Cabinet</div>
	<div class="card-block">
		<blockquote class="card-blockquote">
			<table>
				<tr>
					<td class="objectDetailAlignRight">
						<strong>RU Size:&nbsp&nbsp</strong>
					</td>
					<td>
						<a href="#" id="cabinetSizeInput" data-type="number" data-pk="" data-value=""></a>
					</td>
				</tr>
			</table>
			<!-- Cable path table -->
			<h4 class="header-title m-t-20">Cable Paths:</h4>

			<div class="p-20">
				<div class="table-responsive">
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
				</div>
				<button id="pathAdd" type="button" class="btn btn-sm btn-success waves-effect waves-light">+ Add Path</button>
			</div>
			<!-- Cable Adjacencies -->
			<h4 class="header-title m-t-20">Cabinet Adjacencies:</h4>

			<div class="p-20">
				<div class="table-responsive">
					<table class="table table-sm">
						<thead>
						<tr>
							<th>Side</th>
							<th>Cabinet</th>
						</tr>
						</thead>
						<tbody id="cablePathTableBody">
							<tr>
								<td>Left</td>
								<td><a href="javascript:void(0)" id="adjCabinetSelectL" class="adjCabinetSelect" data-type="select" data-pk="" data-value=""></a></td>
							</tr>
							<tr>
								<td>Right</td>
								<td><a href="javascript:void(0)" id="adjCabinetSelectR" class="adjCabinetSelect" data-type="select" data-pk="" data-value=""></a></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</blockquote>
	</div>
</div>
