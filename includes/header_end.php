	
	<!-- Custom CSS -->
	<style id="customStyle"><?php require_once('includes/content-custom_style.php'); ?></style>
	
    <!-- App CSS -->
    <link href="assets/css/style.css" rel="stylesheet" type="text/css" />
    <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <!-- Modernizr js -->
    <script src="assets/js/modernizr.min.js"></script>

</head>

<body>

<!-- Canvas for drawing connections -->
<canvas id="canvasBuildSpace" style="z-index:1000;position:absolute; pointer-events:none;"></canvas>

<?php require 'topbar.php'; ?>

<!-- User Settings -->
<input id="connectionStyle" type="hidden" value="<?php echo $qls->user_info['connectionStyle']; ?>">
	
<!-- Error Messages -->
<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
		<div class="row">
			<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
				<div id="alertMsg" class="m-t-15"></div>
			</div>
		</div>
	</div>
</div>
