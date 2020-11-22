<?php
/* DO NOT REMOVE */
if (!defined('QUADODO_IN_SYSTEM')) {
exit;
}
?>
<?php require 'includes/header_account.php'; ?>

    <div class="account-pages"></div>
    <div class="clearfix"></div>
    <div class="wrapper-page">

        <div class="account-bg">
            <div class="card-box m-b-0">
                <div class="text-xs-center m-t-20">
                    <a href="index.php" class="logo">
                        <i class="zmdi zmdi-group-work icon-c-logo"></i>
                        <span>PatchCableMgr</span>
                    </a>
                </div>
                <div class="m-t-10 p-20">
					<?php
						if(isset($submitResponse)){
					?>
						<div class="row">
							<div class="col-xs-12 text-xs-center">
								<h6 class="text-muted text-uppercase m-b-0 m-t-0">Reset Password</h6>
								<p class="text-muted m-b-0 font-13 m-t-20"><?php echo $submitResponse; ?></p>
							</div>
						</div>
					<?php
						}else{
					?>
					<div class="row">
                        <div class="col-xs-12 text-xs-center">
                            <h6 class="text-muted text-uppercase m-b-0 m-t-0">Reset Password</h6>
                            <p class="text-muted m-b-0 font-13 m-t-20">Enter your email address and we'll send you an email with instructions to reset your password.</p>
                        </div>
                    </div>
                    <form class="m-t-30" action="change_password.php" method="post">
						<input type="hidden" name="process" value="true" />
                        <div class="form-group row">
                            <div class="col-xs-12">
                                <input class="form-control" type="email" name="username" maxlength="<?php echo $qls->config['max_username']; ?>" required="" placeholder="Enter email">
                            </div>
                        </div>

                        <div class="form-group row text-center m-t-20 m-b-0">
                            <div class="col-xs-12">
                                <button class="btn btn-success btn-block waves-effect waves-light" type="submit">Send Email</button>
                            </div>
                        </div>

                    </form>
					<?php
						}
					?>

                </div>
            </div>
        </div>
        <!-- end card-box-->

        <div class="m-t-20">
            <div class="text-xs-center">
                <p class="text-white">Return to<a href="login.php" class="text-white m-l-5"><b>Sign In</b></p>
            </div>
        </div>

    </div>
    <!-- end wrapper page -->



<?php require 'includes/footer_account.php'; ?>