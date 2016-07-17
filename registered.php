<?php include 'commonhead.php';?>
<script>
  var user_id_val = null;
  var linkClicked = false;
  var request;

  $(document).ready(function() {
    var user_id_param = getUrlParameter('user_id');
    user_id_val = user_id_param;
  });

  function ajaxForResendVerificationEmail(user_id_val) {
    $("#registration_successful").fadeOut(50);
    $("#sending_email").fadeIn();
    if (request) {
        request.abort();
    }
    var serializedData = $.param({
      user_id: user_id_val
    });
    var config = {
      headers : {
        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
      }
    }
    request = $.ajax({
        url: "api/v1/resendverificationemail",
        type: "post",
        data: serializedData
    });
    request.done(function (response, textStatus, jqXHR){
        $("#sending_email").fadeOut(50);
        $("#email_resent").fadeIn();
    });
    request.fail(function (jqXHR, textStatus, errorThrown){
        console.error(
            "The following error occurred: "+
            textStatus, errorThrown
        );
    });
  }

  function resendVerificationEmail() {
    console.log(!linkClicked);
    if (!linkClicked) {
      linkClicked = true;
      console.log(!!user_id_val);
      if (!!user_id_val){
        ajaxForResendVerificationEmail(user_id_val);
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
  <section id="registration_successful">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <h3 class="clients-title">Registration Successful!</h3>
        <p style="margin-bottom:10px;">Thank you for signing up at TailorSquare - One stop for all your tailoring needs!</p>
        <p style="margin-bottom:20px;">Your account is still unverified. Please check your email and click on the activation link to verify your account.</p>
        <hr />
        <p style="font-size: 0.9em; margin-bottom:10px;">Can't find the email? Wait a few minutes, check your spam folder or click the button below to resend the activation link.</p>
        <p style="font-size: 0.9em; margin-bottom:20px;">Note: Please wait at least 5 minutes before using the button below.</p>
        <div class="row">
          <div class="col-lg-4"></div>
          <div class="col-lg-4">
            <button id="resend_verification_email_button" class="btn btn-danger btn-block" type="button" value="Resend" onclick="resendVerificationEmail()">Resend Activation Link</button>
          </div>
          <div class="col-lg-4"></div>
        </div>
      </div>
    </div>
  </section>

  <section id="sending_email" style="display:none">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <h3 class="clients-title">Sending Email...</h3>
    </div>
  </section>

  <section id="email_resent" style="display:none">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <h3 class="clients-title">Email Sent!</h3>
        <p style="margin-bottom:10px;">We have sent the verification email again to your registered email address.</p>
        <p style="margin-bottom:20px;">Please check your email and click on the activation link to verify your account.</p>
        <hr />
        <p style="font-size: 0.9em; margin-bottom:10px;">Still can't find the email? Our servers could be overloaded, please wait for up to 4 hours, and if you still don't get the email, then contact the Tailor Square Support Team for help.</p>
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