<script type="text/javascript">
  var isIndexPage = false;

  $(document).ready(function() {
    if(!!localStorage.user_name){
      $("#header-signin-item").hide();
      $("#header-signin-item-collapsed").hide();
      $("#header-signout-item").show();
      $("#header-signout-item-collapsed-1").show();
      $("#header-signout-item-collapsed-2").show();
      var uppercaseName = localStorage.user_name.toUpperCase()+" <span class='caret'></span>"
      $("#header-signout-button").html(uppercaseName);
    } else {
      $("#header-signin-item").show();
      $("#header-signin-item-collapsed").show();
      $("#header-signout-item").hide();
      $("#header-signout-item-collapsed-1").hide();
      $("#header-signout-item-collapsed-2").hide();
    }

    if(window.location.href.indexOf("index.php") > -1) {
      isIndexPage = true;
    } else {
      $(".navbarMenuItem.active").removeClass("active");      
    }

  });

  function headerMainButtonTapped(){
    console.log('logging out');
    logoutUser();
    window.location.href = "index.php"
  }

  function logoutUser() {
    if (localStorage.user_isguser) {
      gSignOut();
    }
    localStorage.clear();
    location.reload();    
  }

  function logoutUserAfterPasswordChange() {
    if (localStorage.user_isguser) {
      gSignOut();
    }
    localStorage.clear();
    window.location.href = "changepasswordsuccess.php"
  }

  function getDivId(linkID) {
    switch (linkID) {
      case 1 : return "home-banners";
      case 2 : return "what";
      case 3 : return "how";
      case 4 : return "reviews";
      case 5 : return "footer";
      default  : return "";
    }
  }

  function navLinkTapped(linkID) {
    console.log(linkID);
    if(isIndexPage) {
      var divId = getDivId(linkID);
      $('html, body').animate({
          scrollTop: $("#"+divId).offset().top - 70
      }, 1000);
    } else {
      window.location.href = "index.php"
    }
  }

</script>
<nav class="navbar navbar-default navbar-fixed-top headerHeightControl">
    <div>
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="index.php"><img src="img/logo.png" alt="logo" class="headLogo" height="50px" id="logo" /></a>
    </div>
    <div id="navbar" class="headerLinksContainer navbar-collapse collapse">
      <ul class="nav navbar-nav navbar-right">
        <li class="active navbarMenuItem"><a onclick="navLinkTapped(1)">HOME</a></li>
        <li class="navbarMenuItem"><a onclick="navLinkTapped(2)">SERVICES</a></li>
        <li class="navbarMenuItem"><a onclick="navLinkTapped(3)">HOW IT WORKS</a></li>
        <li class="navbarMenuItem"><a onclick="navLinkTapped(4)">REVIEWS</a></li>
        <li class="navbarMenuItem"><a onclick="navLinkTapped(5)">CONTACT US</a></li>
        <li class="navbarMenuItem"><a href="https://blogtailorsquare.wordpress.com/" target="_blank">BLOG</a></li>
        <li id="header-signin-item-collapsed" class="navbarMenuItem navbarMenuCollapsedItem"><a href="login.php"><i class="fa fa-sign-in"></i> SIGN UP/SIGN IN</a></li>
        <li id="header-signout-item-collapsed-1" class="navbarMenuItem navbarMenuCollapsedItem"><a href="dashboard.php"><i class="fa fa-user"></i> MY PROFILE</a></li>
        <li id="header-signout-item-collapsed-2" class="navbarMenuItem navbarMenuCollapsedItem" onclick="headerMainButtonTapped()"><a href="#"><i class="fa fa-sign-out"></i> LOGOUT</a></li>
        <li id="header-signin-item" class="navbarMenuUnCollapsedItem" ><a href="login.php"><button type="button" class="btn btn-danger headerMainButton">SIGN UP/SIGN IN</button></a></li>
        <li id="header-signout-item" class="navbarMenuUnCollapsedItem btn-group dropdown headerMainButtonDropGroup">
          <button id="header-signout-button" type="button" class="btn btn-danger headerMainButton headerMainButtonSignedIn dropdown-toggle"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
          <ul class="dropdown-menu">
            <li><a href="dashboard.php"><i class="fa fa-user"></i> My Profile</a></li>
            <li role="separator" class="divider"></li>
            <li onclick="headerMainButtonTapped()"><a href="#"><i class="fa fa-sign-out"></i> Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
    <!--/.nav-collapse -->
</nav>


<div style="height:70px;"></div>
<div class="headerTicker"><span class="boldFont">Season Offer</span>: Welcome this summer season with ₹100/- off on every Alteration and Stitching order. Use: <span class="boldFont">SUMMER100</span> to avail discount.</div>
