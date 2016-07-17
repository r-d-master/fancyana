<?php include 'commonhead.php';?>
<script>
  var user_id_val = null;
  var key_val = null;
  var linkClicked = false;
  var request;

  $(document).ready(function() {
    user_id_val = localStorage.user_id;
    key_val = localStorage.user_apikey;
  });

  function ajaxForPasswordReset() {
    $("#password_reset_form_error_0").fadeOut(50);
    $("#password_reset_form_error_1").fadeOut(50);
    $("#password_reset_form_error_2").fadeOut(50);
    $("#password_reset_form_error_3").fadeOut(50);
    $("#password_reset_form_error_4").fadeOut(50);
    var oldpass = $("#old_password").val();
    var newpass = $("#new_password").val();
    if (!oldpass) {
      $("#password_reset_form_error_0").fadeIn();
      return;
    }
    if (!newpass) {
      $("#password_reset_form_error_1").fadeIn();
      return;
    }
    if (newpass == oldpass) {
      $("#password_reset_form_error_2").fadeIn();
      return;
    }

    $("#password_change_form_section").fadeOut(50);
    $("#sending_request").fadeIn();

    if (request) {
        request.abort();
    }
    var serializedData = $.param({
      user_id: user_id_val,
      api_key: key_val,
      old_password: oldpass,
      new_password: newpass
    });
    var config = {
      headers : {
        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
      }
    }
    request = $.ajax({
        url: "api/v1/changeuserpasswordbyid",
        type: "post",
        data: serializedData
    });
    request.done(function (response, textStatus, jqXHR){
        $("#sending_request").fadeOut(50);
        if (!response.error) {
          logoutUserAfterPasswordChange()
        } else {
          $("#password_change_form_section").fadeIn();
          if (response.error_code == "2") {
            $("#password_reset_form_error_3").fadeIn();
          } else {
            $("#password_reset_form_error_4").fadeIn();
          }
        }
    });
    request.fail(function (jqXHR, textStatus, errorThrown){
        console.error(
            "The following error occurred: "+
            textStatus, errorThrown
        );
    });
  }

  function passwordReset() {
    console.log(!linkClicked);
    if (!linkClicked) {
      linkClicked = true;
      console.log(!!user_id_val);
      if (!!user_id_val){
        if (!!key_val){
          ajaxForPasswordReset();
          linkClicked = false;
        } else {
        linkClicked = false;
        }
      } else {
        linkClicked = false;
      }
    }
  }
</script>
</head>

<body>

  <!-- Header -->
  <?php include 'header.php';?>
    <br />
    <br />
    <div class="container">
      <div class="col-md-2">
        <ul class="nav nav-pills nav-stacked">
          <li role="presentation"><a href="dashboard.php">Dashboard</a></li>
          <li role="presentation"><a href="orders.php">My Orders</a></li>
          <li role="presentation"><a href="addresses.php">Manage Addresses</a></li>
          <li role="presentation"><a href="profile.php">Profile</a></li>
          <li role="presentation" class="active"><a href="changepassword.php">Change Password</a></li>
        </ul>
      </div>
      <div id="password_change_form_section" class="col-md-10 align-to-center">
        <h3 class="clients-title">Change Password</h3>
        <p style="margin-bottom:20px;">Please enter your current password and a new password below:</p>
        <div class="row">
          <div class="col-md-4"></div>
          <div class="col-md-4">
            <form id="password_change_form">
              <input type="password" id="old_password" name="old_password" class="form-control" style="margin:10px 0;" placeholder="Current Password" required>
              <input type="password" id="new_password" name="new_password" class="form-control" style="margin:10px 0;" placeholder="New Password" required>
              <button id="change_password_button" class="btn btn-danger btn-block" type="button" value="Change Password" style="margin:10px 0;" onclick="passwordReset()">Change Password</button>
            </form>
          </div>
          <div class="col-md-4"></div>
        </div>
        <div class="row">
          <div class="col-md-2"></div>
          <div class="col-md-8">
            <div id="password_reset_form_error_0" class="alert alert-danger align-to-center" style="display:none;"><strong>Error!</strong> Current Password can't be empty!</div>
            <div id="password_reset_form_error_1" class="alert alert-danger align-to-center" style="display:none;"><strong>Error!</strong> New Password can't be empty!</div>
            <div id="password_reset_form_error_2" class="alert alert-danger align-to-center" style="display:none;"><strong>Error!</strong> New Password can't be same as the Current Password!</div>
            <div id="password_reset_form_error_3" class="alert alert-danger align-to-center" style="display:none;"><strong>Error!</strong> Incorrect Current Password!</div>
            <div id="password_reset_form_error_4" class="alert alert-danger align-to-center" style="display:none;"><strong>Server Error!</strong> Please logout, login, and try again.</div>
          </div>
          <div class="col-md-2"></div>
        </div>
      </div>
      <div id="sending_request" class="col-lg-10 align-to-center" style="display:none">
        <h3 class="clients-title">Saving New Password...</h3>
      </div>
    </div>
    <hr>


  <hr>

  <!-- Footer -->
  <?php include 'footer.php';?>

  <!-- Bootstrap core JavaScript
    ================================================== -->
  <!-- Placed at the end of the document so the pages load faster -->
  <script src="js/bootstrap.min.js"></script>
  <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
  <script src="js/ie10-viewport-bug-workaround.js"></script>
</body>

</html>