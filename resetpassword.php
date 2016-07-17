<?php include 'commonhead.php';?>
<script>
  var email_val = null;
  var key_val = null;
  var linkClicked = false;
  var request;

  $(document).ready(function() {
    var email_in_url = getUrlParameter('email');
    var key_in_url = getUrlParameter('key');
    email_val = email_in_url;
    key_val = key_in_url;
  });

  function ajaxForPasswordReset(email_param, key_param) {
    $("#password_reset_form_error_0").fadeOut(50);
    $("#password_reset_form_error_1").fadeOut(50);
    var newpass = $("#new_password").val();
    if (!newpass) {
      $("#password_reset_form_error_0").fadeIn();
      return;
    }

    $("#password_change_form_section").fadeOut(50);
    $("#sending_request").fadeIn();

    if (request) {
        request.abort();
    }
    var serializedData = $.param({
      email: email_val,
      api_key: key_val,
      password: newpass
    });
    var config = {
      headers : {
        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
      }
    }
    request = $.ajax({
        url: "api/v1/changeuserpassword",
        type: "post",
        data: serializedData
    });
    request.done(function (response, textStatus, jqXHR){
        $("#sending_request").fadeOut(50);
        if (!response.error) {
          $("#password_changed").fadeIn();
        } else {
          $("#password_change_form_section").fadeIn();
          $("#password_reset_form_error_1").fadeIn();
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
      console.log(!!email_val);
      if (!!email_val){
        if (!!key_val){
          ajaxForPasswordReset(email_val, key_val);
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

  <!-- Begin Content -->
  <section id="password_change_form_section">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <h3 class="clients-title">Reset Password</h3>
        <p style="margin-bottom:20px;">Please enter a new password below:</p>
        <div class="row">
          <div class="col-lg-4"></div>
          <div class="col-lg-4">
            <form id="password_change_form">
              <input type="password" id="new_password" name="password" class="form-control" style="margin:10px 0;" placeholder="Password" required>
              <button id="change_password_button" class="btn btn-danger btn-block" type="button" value="Change Password" style="margin:10px 0;" onclick="passwordReset()">Change Password</button>
            </form>
            <div id="password_reset_form_error_0" class="alert alert-danger align-to-center" style="display:none;"><strong>Error!</strong> Password can't be empty!</div>
            <div id="password_reset_form_error_1" class="alert alert-danger align-to-center" style="display:none;"><strong>Server Error!</strong> Please try again.</div>
          </div>
          <div class="col-lg-4"></div>
        </div>
      </div>
    </div>
  </section>

  <section id="sending_request" style="display:none">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <h3 class="clients-title">Saving New Password...</h3>
    </div>
  </section>

  <section id="password_changed" style="display:none">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <h3 class="clients-title">Password Changed!</h3>
        <p style="margin-bottom:20px;">Congratulations. Your password has been successfully changed!</p>
        <h6 class="bodyRedLink">Please <a href="login.php">Login</a> to continue</h6>
      </div>
    </div>
  </section>

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