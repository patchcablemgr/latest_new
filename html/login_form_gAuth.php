<?php
/* DO NOT REMOVE */
if (!defined('QUADODO_IN_SYSTEM')) {
exit;
}
/*****************/
?>
<?php require 'includes/header_account.php'; ?>

    <div class="account-pages"></div>
    <div class="clearfix"></div>
    <div class="wrapper-page">

        <div class="account-bg">
            <div class="card-box m-b-0">
                <div class="text-xs-center m-t-20">
                    <a href="/" class="logo">
                        <i class="zmdi zmdi-group-work icon-c-logo"></i>
                        <span>PatchCableMgr</span>
                    </a>
                </div>
                <div class="m-t-10 p-20">
                    <div class="row">
                        <div class="col-xs-12 text-xs-center">
                            <h6 class="text-muted text-uppercase m-b-0 m-t-0">Sign In</h6>
                        </div>
                    </div>
                    <form class="m-t-20" action="login_process.php" method="post">
						<input type="hidden" name="processGAuth" value="true" />
						<input type="hidden" name="username" value="<?php echo $_GET['username'];?>" />

                        <div class="form-group row">
                            <div class="col-xs-12">
                                <input class="form-control" type="text" required="" name="gAuthCode" maxlength="6" placeholder="<?php echo MFA_LABEL; ?>" autofocus>
                            </div>
                        </div>
						
						<div class="form-group text-center row m-t-10">
                            <div class="col-xs-12">
									<button class="btn btn-success btn-block waves-effect waves-light" type="submit">Log In</button>
                            </div>
                        </div>
						
                    </form>

                </div>

                <div class="clearfix"></div>
            </div>
        </div>
        <!-- end card-box-->

        <div class="m-t-20">
            <div class="text-xs-center">
                <p class="text-white">Don't have an account? <a href="register.php" class="text-white m-l-5"><b>Sign Up</b></a></p>
            </div>
        </div>

    </div>
    <!-- end wrapper page -->


<?php require 'includes/footer_account.php'; ?>
