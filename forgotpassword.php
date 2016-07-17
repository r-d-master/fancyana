<?php include 'commonhead.php';?>
<script>
  $(document).ready(function() {
    var request;
    $("#password_reset_form").submit(function(event){
        $("#password_reset_form_section").fadeOut(50);
        $("#sending_email").fadeIn();
        if (request) {
            request.abort();
        }
        var $form = $(this);
        var $inputs = $form.find("input, select, button, textarea");
        var serializedData = $form.serialize();
        $inputs.prop("disabled", true);
        request = $.ajax({
            url: "api/v1/sendpasswordresetemail",
            type: "post",
            data: serializedData
        });
        request.done(function (response, textStatus, jqXHR){
            $("#sending_email").fadeOut(50);
            console.log(response);
            if (!response.error) {
              $("#email_sent").fadeIn();
            } else if (response.error_code == 2) {
              $("#password_reset_form_section").fadeIn();
              $("#password_reset_form_error_2").fadeIn();
            } else {
              $("#password_reset_form_section").fadeIn();
              $("#password_reset_form_error_1").fadeIn();              
            }
        });
        request.fail(function (jqXHR, textStatus, errorThrown){
            console.error("The following error occurred: "+ textStatus, errorThrown);
        });
        request.always(function () {
            $inputs.prop("disabled", false);
        });
        event.preventDefault();
    });
  });
</script>
</head>

<body>

  <!-- Header -->
  <?php include 'header.php';?>
    <br />

  <!-- Begin Content -->
  <section id="password_reset_form_section">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <h3 class="clients-title">Reset Your Password</h3>
        <p style="margin-bottom:10px;">Please enter your registered email address below, to reset your password.</p>
        <p style="margin-bottom:20px;">You will receive an email with further instructions.</p>
        <div class="row">
          <div class="col-lg-4"></div>
          <div class="col-lg-4">
            <form id="password_reset_form">
              <input type="email" id="password_reset_email" name="email" class="form-control" placeholder="Email Address" required>
              <button id="reset_password_button" class="btn btn-danger btn-block" type="submit" value="Reset Password" style="margin:10px 0;">Reset Password</button>
            </form>
            <div id="password_reset_form_error_1" class="alert alert-danger align-to-center" style="display:none;"><strong>Server Error!</strong> Please try again.</div>
            <div id="password_reset_form_error_2" class="alert alert-danger align-to-center" style="display:none;"><strong>Error!</strong> No such account exists!</div>
          </div>
          <div class="col-lg-4"></div>
        </div>
        <hr />
        <p style="font-size: 0.9em; margin-bottom:20px;">Note: If after clicking the button, you are not able to find the email, check your spam folder or wait for at least 15 minutes before trying again.</p>
      </div>
    </div>
  </section>

  <section id="sending_email" style="display:none">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <h3 class="clients-title">Sending Email...</h3>
    </div>
  </section>

  <section id="email_sent" style="display:none">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <h3 class="clients-title">Email Sent!</h3>
        <p style="margin-bottom:10px;">We have sent an email with further instructions to your registered email address.</p>
        <p style="margin-bottom:20px;">Please check your email and click on the reset link to access your account.</p>
        <hr />
        <p style="font-size: 0.9em; margin-bottom:10px;">Can't find the email? Did you check the spam folder? Our servers could be overloaded, please wait for up to 4 hours, and if you still don't get the email, then contact the Tailor Square Support Team for help.</p>
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