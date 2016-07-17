<?php
session_start();
$errorMessage = "";
$errorFlag = "";
if(count($_POST)>0) {
	require_once '../api/include/DbHandler.php';
	require_once '../api/include/PassHash.php';

	$email = $_POST["username"];
	$password = $_POST["password"];
	$db = new DbHandler();
    // check for correct email and password
    if ($db->checkTailorLogin($email, $password)) {
        $user = $db->getTailorUserByEmail($email);
        if ($user != NULL) {
            $_SESSION["user_id"] = $user["user_id"];
			$_SESSION["user_name"] = $user["name"];
			echo $user["name"];
        } else {
			$errorMessage = "Invalid Username or Password!";
        }
    } else {
			$errorMessage = "Invalid Username or Password!";
    }

    if(isset($_SESSION["user_id"])) {
		header("Location: backdash.php");
	}
}
if(strlen($errorMessage) == 0){
	$errorFlag = "display-hide";
}
?>

<!DOCTYPE html>
<!-- 
Template Name: Metronic - Responsive Admin Dashboard Template build with Twitter Bootstrap 3.3.2
Version: 3.7.0
Author: KeenThemes
Website: http://www.keenthemes.com/
Contact: support@keenthemes.com
Follow: www.twitter.com/keenthemes
Like: www.facebook.com/keenthemes
Purchase: http://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes
License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project.
-->
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>Tailor Square | Manage</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<meta content="" name="description"/>
<meta content="Tailor Square" name="author"/>
<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="../manage/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="../manage/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="../manage/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="../manage/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="../manage/assets/global/plugins/select2/select2.css" rel="stylesheet" type="text/css"/>
<link href="../manage/assets/admin/pages/css/login3.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL SCRIPTS -->
<!-- BEGIN THEME STYLES -->
<link href="../manage/assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="../manage/assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="../manage/assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link href="../manage/assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
<link href="../manage/assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="../favicon.png"/>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="login">
<!-- BEGIN LOGO -->
<div class="logo">
	<a href="index.php">
	<img src="../img/logo_title.png" width="200px" alt=""/>
	</a>
</div>
<!-- END LOGO -->
<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
<div class="menu-toggler sidebar-toggler">
</div>
<!-- END SIDEBAR TOGGLER BUTTON -->
<!-- BEGIN LOGIN -->
<div class="content">
	<!-- BEGIN LOGIN FORM -->
	<form class="login-form" action="" method="post">
		<h3 class="form-title">Login to manage the website</h3>

		<div class="alert alert-danger <?php echo $errorFlag; ?>">
			<button class="close" data-close="alert"></button>
			<span>
			<?php echo $errorMessage; ?> </span>
		</div>
		<div class="form-group">
			<!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
			<label class="control-label visible-ie8 visible-ie9">Username</label>
			<div class="input-icon">
				<i class="fa fa-user"></i>
				<input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="Username" name="username"/>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label visible-ie8 visible-ie9">Password</label>
			<div class="input-icon">
				<i class="fa fa-lock"></i>
				<input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="Password" name="password"/>
			</div>
		</div>
		<div class="form-actions">
			<label class="checkbox">
			<input type="checkbox" name="remember" value="1"/> Remember me </label>
			<button type="submit" class="btn green-haze pull-right">
			Login <i class="m-icon-swapright m-icon-white"></i>
			</button>
		</div>
	</form>
	<!-- END LOGIN FORM -->
</div>
<!-- END LOGIN -->
<!-- BEGIN COPYRIGHT -->
<div class="copyright">
	 2015 &copy; Tailor Square
</div>
<!-- END COPYRIGHT -->
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
<script src="../manage/assets/global/plugins/respond.min.js"></script>
<script src="../manage/assets/global/plugins/excanvas.min.js"></script> 
<![endif]-->
<script src="../manage/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="../manage/assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="../manage/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="../manage/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="../manage/assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="../manage/assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="../manage/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<script type="text/javascript" src="../manage/assets/global/plugins/select2/select2.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="../manage/assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="../manage/assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="../manage/assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="../manage/assets/admin/pages/scripts/login.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<script>
jQuery(document).ready(function() {     
  Metronic.init(); // init metronic core components
  Layout.init(); // init current layout
  Login.init();
  Demo.init();
});
</script>
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>
