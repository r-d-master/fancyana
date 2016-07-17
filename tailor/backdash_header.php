<?php
ob_start();
?>
<!DOCTYPE html>
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
<link href="../manage/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN THEME STYLES -->
<link href="../manage/assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="../manage/assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="../manage/assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link id="style_color" href="../manage/assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
<link href="../manage/assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<link href="../manage/assets/css/backcss.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="../favicon.png"/>
<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
<script src="../manage/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script>
	function logoutTapped() {
		console.log("logging out");
	}

	function generateImagePreview(input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
          $("#"+input.id+"_preview").attr('src', e.target.result);
          $("#"+input.id+"_group").removeClass("hide-group");
      }
      reader.readAsDataURL(input.files[0]);
    }
	}

  $.urlParam = function(name){
      var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
      if (results==null){
         return null;
      }
      else{
         return results[1] || 0;
      }
  }

  $(document).ready(function(){
    var msg = $.urlParam("message");
    var msgText = $.urlParam("message_text");
    if(!!msg) {
      if(!!msgText) {
        if(msg == "0") {
          $('#upload_result_dialog_alert').addClass("alert-danger");
          $('#upload_result_dialog_alert').removeClass("alert-success");
        } else {
          $('#upload_result_dialog_alert').removeClass("alert-danger");
          $('#upload_result_dialog_alert').addClass("alert-success");
        }
        $('#upload_result_dialog_text').html(decodeURIComponent(msgText));
        $('#upload_result_dialog_group').show();
      }
    }
    if(!!$.urlParam("message_text")){
      $('#upload_result_dialog_group').show();
    }
  });

</script>

</head>
<!-- BEGIN BODY -->
<!-- DOC: Apply "page-header-fixed-mobile" and "page-footer-fixed-mobile" class to body element to force fixed header or footer in mobile devices -->
<!-- DOC: Apply "page-sidebar-closed" class to the body and "page-sidebar-menu-closed" class to the sidebar menu element to hide the sidebar by default -->
<!-- DOC: Apply "page-sidebar-hide" class to the body to make the sidebar completely hidden on toggle -->
<!-- DOC: Apply "page-sidebar-closed-hide-logo" class to the body element to make the logo hidden on sidebar toggle -->
<!-- DOC: Apply "page-sidebar-hide" class to body element to completely hide the sidebar on sidebar toggle -->
<!-- DOC: Apply "page-sidebar-fixed" class to have fixed sidebar -->
<!-- DOC: Apply "page-footer-fixed" class to the body element to have fixed footer -->
<!-- DOC: Apply "page-sidebar-reversed" class to put the sidebar on the right side -->
<!-- DOC: Apply "page-full-width" class to the body element to have full width page without the sidebar menu -->
<body class="page-header-fixed page-quick-sidebar-over-content page-sidebar-closed-hide-logo">
<!-- BEGIN HEADER -->
<div class="page-header -i navbar navbar-fixed-top">
	<!-- BEGIN HEADER INNER -->
	<div class="page-header-inner">
		<!-- BEGIN LOGO -->
		<div class="page-logo">
			<a href="backdash.php">
			<img src="../img/logo_title.png" height="36px" = alt="logo" class="logo-default"/>
			</a>
			<div class="menu-toggler sidebar-toggler">
			</div>
		</div>
		<!-- END LOGO -->
		<!-- BEGIN RESPONSIVE MENU TOGGLER -->
		<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
		</a>
		<!-- END RESPONSIVE MENU TOGGLER -->
			
		<div style="float:right; margin-top:15px; margin-right:15px;">
			<a href="logout.php" >
			<i class="icon-logout"></i> LOGOUT
			</a>
		</div>

		<div style="float:right; margin-top:15px; margin-right:15px; color: white;" >Welcome, 
		<?php
			session_start(); 
			if(!isset($_SESSION["user_name"])) 
			{
				header("Location: index.php");
			} 
			echo $_SESSION["user_name"]; 
		?>
		</div>
		
		<!-- END TOP NAVIGATION MENU -->
	</div>
	<!-- END HEADER INNER -->
</div>
<!-- END HEADER -->
<div class="clearfix">
</div>
<!-- BEGIN CONTAINER -->
<div class="page-container">
	<!-- BEGIN SIDEBAR -->
	<div class="page-sidebar navbar-collapse collapse">
		<!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
		<!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
		<!-- BEGIN SIDEBAR MENU1 -->
		<ul class="page-sidebar-menu hidden-sm hidden-xs" data-auto-scroll="true" data-slide-speed="200">
		  <?php include 'backdash_menu.php';?>
		</ul>
		<!-- END SIDEBAR MENU1 -->
		<!-- BEGIN RESPONSIVE MENU FOR HORIZONTAL & SIDEBAR MENU -->
		<ul class="page-sidebar-menu visible-sm visible-xs" data-slide-speed="200" data-auto-scroll="true">
		  <?php include 'backdash_menu.php';?>
		</ul>
		<!-- END RESPONSIVE MENU FOR HORIZONTAL & SIDEBAR MENU -->
	</div>
	<!-- END SIDEBAR -->
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<div class="page-content-body">

