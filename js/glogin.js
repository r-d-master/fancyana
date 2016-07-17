  function onGLoad() {
    gapi.load('auth2', function() {
      gapi.auth2.init();
      console.log('gapi loaded on its own');
    });
  }

  function gSignOut() {
    if (!gapi.auth2) {
      getReadyToGSignOut();
    } else {
      readyToGSignOut();      
    }
  }

  function getReadyToGSignOut() {
    gapi.load('auth2', function() {
      gapi.auth2.init();
      console.log('gapi loaded to sign out');
      readyToGSignOut();
    });
  }

  function readyToGSignOut() {
    var auth2 = gapi.auth2.getAuthInstance();
    auth2.signOut().then(function () {
      console.log('Google User signed out.');
    });      
  }

  function gSignIn() {
    if (!gapi.auth2) {
      getReadyToGSignIn();
    } else {
      readyToGSignIn();
    }
  }

  function getReadyToGSignIn() {
    gapi.load('auth2', function() {
      gapi.auth2.init();
      console.log('gapi loaded to sign in');
      readyToGSignIn();      
    });
  }

  function readyToGSignIn() {
    var auth2 = gapi.auth2.getAuthInstance();
    // auth2 is initialized with gapi.auth2.init() and a user is signed in.
    if (auth2.isSignedIn.get()) {
      var profile = googleUser.getBasicProfile();
      tsGRegisterOrLogin(profile.getName(), profile.getEmail(), profile.getId());
    } else {
      auth2.signIn().then(function() {
        var autheduserprofile = auth2.currentUser.get().getBasicProfile();
        console.log(autheduserprofile);
        tsGRegisterOrLogin(autheduserprofile.getName(), autheduserprofile.getEmail(), autheduserprofile.getId());
      });
    }    
  }

  var grlrequest;

  function tsGRegisterOrLogin(name_val, email_val, gid_val) {
    $("#signin_error_1").fadeOut(50);

    if (grlrequest) {
        grlrequest.abort();
    }
    var serializedData = $.param({
      name: name_val,
      email: email_val,
      gid: gid_val
    });
    var config = {
      headers : {
        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
      }
    }
    request = $.ajax({
        url: "api/v1/googleregisterorlogin",
        type: "post",
        data: serializedData
    });
    request.done(function (response, textStatus, jqXHR){
        if (!!response && !response.error) {
          localStorage.setItem("user_id", response.user_id);
          localStorage.setItem("user_email", response.email);
          localStorage.setItem("user_name", response.name);
          localStorage.setItem("user_apikey", response.api_key);
          localStorage.setItem("user_isguser", response.is_g_user);
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
