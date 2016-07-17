<?php include 'commonhead.php';?>
<script src="js/fblogin.js"></script>
<script>
  $(document).ready(function() {
    var sender = getUrlParameter('sender');
    var returnURL = "index.php";
    if (!!sender) {
      if (sender == "orderdetails") {
        $("#unauthorized_div").show();
        $("#register_div").hide();
        $("#filler_div_1").show();
        $("#filler_div_2").show();
        returnURL = "orders.php";
      }
    }
    // Variable to hold request
    var request;
    var password = document.getElementById("register_password");
    var confirm_password = document.getElementById("confirm_password");
  
    function validatePassword(){
      if(password.value != confirm_password.value) {
        confirm_password.setCustomValidity("Passwords Don't Match");
      } else {
        confirm_password.setCustomValidity('');
      }
    }
    
    password.onchange = validatePassword;
    confirm_password.onkeyup = validatePassword;

    // Bind to the submit event of our form
    $("#signup-form").submit(function(event){
        $("#register_error_1").fadeOut(50);
        $("#register_error_2").fadeOut(50);
        $("#register_success").fadeOut(50);
  
        // Abort any pending request
        if (request) {
            request.abort();
        }
        // setup some local variables
  
        // Callback handler that will be called on success
        var $form = $(this);
        // Let's select and cache all the fields
        var $inputs = $form.find("input, select, button, textarea");
        // Serialize the data in the form
        var serializedData = $form.serialize();
        // Let's disable the inputs for the duration of the Ajax request.
        // Note: we disable elements AFTER the form data has been serialized.
        // Disabled form elements will not be serialized.
        $inputs.prop("disabled", true);
        // Fire off the request to /form.php
        request = $.ajax({
            url: "api/v1/register",
            type: "post",
            data: serializedData
        });
        // Callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // Log a message to the console
            console.log(response);
            if (!response.error) {
              $("#register_success").show();
              window.location.href = "registered.php?user_id="+response.user_id;
            } else {
              if (response.error_code == 2) {
                $("#register_error_2").fadeIn();
              } else {
                $("#register_error_1").fadeIn();                
              }
            }
        });
        // Callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // Log the error to the console
            console.error(
                "The following error occurred: "+
                textStatus, errorThrown
            );
        });
        // Callback handler that will be called regardless
        // if the request failed or succeeded
        request.always(function () {
            // Reenable the inputs
            $inputs.prop("disabled", false);
        });
        // Prevent default posting of form
        event.preventDefault();
    });
  
    // Bind to the submit event of our form
    $("#signin-form").submit(function(event){
        $("#signin_error_1").fadeOut(50);
        // Abort any pending request
        if (request) {
            request.abort();
        }
        // setup some local variables
        var $form = $(this);
        // Let's select and cache all the fields
        var $inputs = $form.find("input, select, button, textarea");
        // Serialize the data in the form
        var serializedData = $form.serialize();
        // Let's disable the inputs for the duration of the Ajax request.
        // Note: we disable elements AFTER the form data has been serialized.
        // Disabled form elements will not be serialized.
        $inputs.prop("disabled", true);
        // Fire off the request to /form.php
        request = $.ajax({
            url: "api/v1/login",
            type: "post",
            data: serializedData
        });
        // Callback handler that will be called on success
        request.done(function (response, textStatus, jqXHR){
            // Log a message to the console
            if(response.error){
              console.error(
                "The following error occurred: " + response.message
              );
              $("#signin_error_1").fadeIn();
            } else{
              console.log("Successfully Logged In!");
              if (response.active == 0){
                window.location.href = "activation_pending.php?user_id="+response.user_id;
              } else {
                localStorage.setItem("user_id", response.user_id);
                localStorage.setItem("user_mobile", response.mobile);
                localStorage.setItem("user_email", response.email);
                localStorage.setItem("user_name", response.name);
                localStorage.setItem("user_apikey", response.apiKey);
                var usercode0 = response.name.split(' ')[0];
                var usercode1 = usercode0.toUpperCase();
                var usercode2 = response.user_id;
                var usercode = usercode1 + " [TS" + usercode2 + "]";
                localStorage.setItem("user_code", usercode);
                window.location.href = returnURL;
              }
            }
            console.log(response);
        });
        // Callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // Log the error to the console
            console.error(
                "The following error occurred: "+
                textStatus, errorThrown
            );
            $("#signin_error_1").fadeIn();
        });
        // Callback handler that will be called regardless
        // if the request failed or succeeded
        request.always(function () {
            // Reenable the inputs
            $inputs.prop("disabled", false);
        });
        // Prevent default posting of form
        event.preventDefault();
    });
  });
  
  function toggleSubscription() {
    if($("#register_subscribe_box").prop("checked") == true){
      $("#register_subscribe").val("true");
    }
    else if($("#register_subscribe_box").prop("checked") == false){
      $("#register_subscribe").val("false");
    }
  }

</script>
<!--
  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
  <!-- Header -->
  <?php include 'header.php';?>
  <br/>
  <br/>
  <div class="container">
    <div class="col-md-12 align-to-center" id="unauthorized_div" style="display:none;">
        <h6><span class="bodyRedText">Unauthorized!</span> Please sign in to continue</h6>
    </div>
    <div class="col-md-6" id="register_div">
      <form id="signup-form" class="form-signup">
        <h2 class="form-signup-heading">Register New Account</h2>
        <label for="register_name" class="sr-only">Name</label>
        <input type="text" id="register_name" name="name" class="form-control" placeholder="Name" required>
        <label for="register_mobile" class="sr-only">Mobile Number</label>
        <input type="text" id="register_mobile" name="mobile" class="form-control" placeholder="Mobile Number (only 10 digits)" pattern="(\d{10})" title="Please enter only the 10 digit number Example: 9876543210" required>
        <label for="register_email" class="sr-only">Email Address</label>
        <input type="email" id="register_email" name="email" class="form-control" placeholder="Email Address" required>
        <label for="register_password" class="sr-only">Password</label>
        <input type="password" id="register_password" name="password" class="form-control" placeholder="Password" required>
        <label for="confirm_password" class="sr-only">Confirm Password</label>
        <input type="password" id="confirm_password" class="form-control" placeholder="Confirm Password" required>
        <input type="checkbox" id="register_subscribe_box" onclick="toggleSubscription()"> I want to subscribe to the Newsletter<br/>
        <input type="hidden" id="register_subscribe" name="subscribe" value="false">
        <input type="checkbox" required> I have read the <a href="terms_n_conditions.php">Terms & Conditions</a><br/><br/>
        <button class="btn btn-lg btn-danger btn-block" type="submit" value="Register">Register</button>
      </form>
      <div id="register_error_1" class="alert alert-danger align-to-center" style="display:none;"><strong>Error!</strong> Invalid Details.</div>
      <div id="register_error_2" class="alert alert-danger align-to-center" style="display:none;"><strong>Error!</strong> Already Registered. Try Signing in.</div>
      <div id="register_success" class="alert alert-success align-to-center" style="display:none;"><strong>Successfully Registered!</strong> Please verify account.</div>
    </div>
    <div class="col-md-3" id="filler_div_1" style="display:none;"></div>
    <div class="col-md-6">
      <form id="signin-form" class="form-signin">
        <h2 class="form-signin-heading">Sign In</h2>
        <label for="login_email" class="sr-only">Email address</label>
        <input type="email" id="login_email" name="email" class="form-control" placeholder="Email address" required autofocus>
        <label for="login_password" class="sr-only">Password</label>
        <input type="password" id="login_password" name="password" class="form-control" placeholder="Password" required>
        <div class="checkbox">
          <label>
          <input type="checkbox" value="remember-me"> Remember me
          </label>
          <label>
          <a href="forgotpassword.php">Forgot password?</a>
          </label>
        </div>
        <button class="btn btn-lg btn-danger btn-block" type="submit" value="SignIn">Sign in</button>
        <br />
        <div class="row">
          <div class="col-xs-6">
            <fb:login-button size="xlarge" scope="public_profile,email" onlogin="checkLoginState();"></fb:login-button>
          </div>
          <div class="col-xs-6">
            <div id="googleSignInButton" class="googleSignInButtonClass" onclick="gSignIn()"></div>
          </div>
        </div>
        <div id="status" style="display:none;"></div>
        <div id="signin-alertbox" class="alert alert-warning hide-alert-box">
        </div>
      </form>
      <div id="signin_error_1" class="alert alert-danger align-to-center" style="display:none;"><strong>Error!</strong> Invalid Credentials.</div>
    </div>
    <div class="col-md-3" id="filler_div_2" style="display:none;"></div>
  </div>
  <!-- /container -->
  <div class="clear"></div>
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
