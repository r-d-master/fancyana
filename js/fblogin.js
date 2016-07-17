  // This is called with the results from from FB.getLoginStatus().
  function statusChangeCallback(response) {
    console.log('statusChangeCallback');
    console.log(response);
    // The response object is returned with a status field that lets the
    // app know the current login status of the person.
    // Full docs on the response object can be found in the documentation
    // for FB.getLoginStatus().
    if (response.status === 'connected') {
      // Logged into your app and Facebook.
      testAPI();
    } else if (response.status === 'not_authorized') {
      // The person is logged into Facebook, but not your app.
      document.getElementById('status').innerHTML = 'Please log ' +
        'into this app.';
    } else {
      // The person is not logged into Facebook, so we're not sure if
      // they are logged into this app or not.
      document.getElementById('status').innerHTML = 'Please log ' +
        'into Facebook.';
    }
  }
  
  // This function is called when someone finishes with the Login
  // Button.  See the onlogin handler attached to it in the sample
  // code below.
  function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
      if(!!localStorage.facebook_name) {
        tsFBRegisterOrLogin(localStorage.facebook_name, localStorage.facebook_fbid);
      }
    });
  }
  
  window.fbAsyncInit = function() {
  FB.init({
    appId      : '1649497482006227',
    cookie     : true,  // enable cookies to allow the server to access 
                        // the session
    xfbml      : true,  // parse social plugins on this page
    version    : 'v2.2' // use version 2.2
  });
  
  // Now that we've initialized the JavaScript SDK, we call 
  // FB.getLoginStatus().  This function gets the state of the
  // person visiting this page and can return one of three states to
  // the callback you provide.  They can be:
  //
  // 1. Logged into your app ('connected')
  // 2. Logged into Facebook, but not your app ('not_authorized')
  // 3. Not logged into Facebook and can't tell if they are logged into
  //    your app or not.
  //
  // These three cases are handled in the callback function.
  
  FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
  });
  
  };
  
  // Load the SDK asynchronously
  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
  
  // Here we run a very simple test of the Graph API after login is
  // successful.  See statusChangeCallback() for when this call is made.
  function testAPI() {
    console.log('Welcome!  Fetching your information.... ');
    FB.api('/me', function(response) {
      console.log(response);
      console.log('Successful login for: ' + response.name);
      document.getElementById('status').innerHTML =
        'Thanks for logging in, ' + response.name + '!';
        if (!!response.name){
          localStorage.facebook_name = response.name;
          localStorage.facebook_fbid = response.id;
        }
    });
  }

  function facebookLogout() {
    FB.logout(function(response) {
       // Person is now logged out
    });
  }

  var fbrlrequest;

  function tsFBRegisterOrLogin(name_val, fbid_val) {
    $("#signin_error_1").fadeOut(50);

    if (fbrlrequest) {
        fbrlrequest.abort();
    }
    var serializedData = $.param({
      name: name_val,
      fbid: fbid_val
    });
    var config = {
      headers : {
        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
      }
    }
    request = $.ajax({
        url: "api/v1/facebookregisterorlogin",
        type: "post",
        data: serializedData
    });
    request.done(function (response, textStatus, jqXHR){
        if (!response.error) {
          localStorage.setItem("user_id", response.user_id);
          localStorage.setItem("user_email", response.email);
          localStorage.setItem("user_name", response.name);
          localStorage.setItem("user_apikey", response.api_key);
          localStorage.setItem("user_isfbuser", response.is_fb_user);
          var usercode0 = response.name.split(' ')[0];
          var usercode1 = usercode0.toUpperCase();
          var usercode2 = response.user_id;
          var usercode = usercode1 + " [TS" + usercode2 + "]";
          localStorage.setItem("user_code", usercode);
          window.location.href = "index.php";
        } else {
          $("#signin_error_1").fadeIn();
        }
    });
    request.fail(function (jqXHR, textStatus, errorThrown){
        console.error(
            "The following error occurred: "+
            textStatus, errorThrown
        );
    });
  }