<?php include 'commonhead.php';?>

  <script>
      var homeDataApp = angular.module('homeDataApp', []);
      homeDataApp.controller('homeDataCtrl', function($scope, $http) {
          var data;
          $http.post("api/v1/getallbanners", data)
          .then(function (response) {
            $scope.dataset_banners = response.data.results;
          });
      });
  </script>
  <script>
    $(document).ready(function() {
      $('body').scrollspy({ target: '#navbar' });
    });

    function tapNewOrder() {
      if(!!localStorage.user_name){
        window.location.href = "neworder.php";
      } else {
        window.location.href = "login.php";
      }
    }
    function tapNewOrderAlt() {
      if(!!localStorage.user_name){
        window.location.href = "neworderalt.php";
      } else {
        window.location.href = "login.php";
      }
    }
  </script>
 </head>

<body>
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">New Dress</h4>
      </div>
      <div class="modal-body">
        <p>Some text in the modal.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

  <!-- Header -->
  <?php include 'header.php';?>

  <!-- Carousel
    ================================================== -->
    <div ng-app="homeDataApp" ng-controller="homeDataCtrl" class="container-fluid">
      <div class="row lightGreyBg">
        <div id="home-banners" class="carousel slide col-lg-9" data-ride="carousel" data-interval="5000">
          <div class="carousel-inner" role="listbox">
            <div ng-repeat="x in dataset_banners" class="item" ng-class={active:(activeIndex?activeIndex==$index:$first)}>
              <img src="uploadedimages/banner/{{x.banner_image}}.jpg" class="bannerImage" alt="{{x.banner_image}}">
            </div>
          </div>
        </div>
        <div class="carousel-adjacent-options col-lg-3" style="min-height:300px;">
          <div class="carousel-adjacent-options-table-wrapper">
            <table class="table carousel-adjacent-options-table"><br>
              <caption style="color:#000000">Choose From Our Services</caption>
              <tbody>
                <tr>
                  <td>
                    <a onclick="tapNewOrder()" class="btn btn-danger" style="color:white; width:100%;"><img src="img/servicebutton2.png" style="float:left;" /> Create a Dress
                      <br />
                      <span style="font-size:x-small;">Choose from latest designs and we will <br /> do the rest</span>
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a onclick="tapNewOrderAlt()" class="btn btn-danger" style="color:white; width:100%;"><img src="img/servicebutton1.png" style="float:left;" />Shop Online with Perfect Fit
                      <br />
                      <span style="font-size:x-small;">Alteration and design changes during <br /> online purchase or existing dress</span>
                    </a>
                  </td>
                </tr>
                <tr>
                  <td><br>
                  <b><p style="text-align:center;">For live support call on +91-9990934932</p></b><br>
                  <b><p style="font-size:medium;text-align:center;color:white;background-color:#c9302c;margin-bottom:0px;position:relative;padding:5px;">Currently available in Delhi/ NCR</p></b>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  <!-- /.carousel -->

  <section id="what" class="featured-content">
    <div class="wrap-content">

      <h3 class="clients-title" style="text-align:center;">What We Offer</h3>

      <div class="row">
        <div class="col-lg-3">
          <div class="service-box">
            <div class="hi-icon-effect-3 hi-icon-effect-3a">
              <a class="hi-icon"><img src="img/servicebutton1.png" alt="" /></a>
            </div>
            <h3>Design & Stitching</h3>
            <ul style="text-align:justify">
              <li>&#187; Custom stitching with wide range of designs to choose from</li>
              <li>&#187; Provide your fabric or choose from our collection</li>
            </ul>
          </div>
        </div>
        <!-- /.col-lg-3 -->
        <div class="col-lg-3">
          <div class="service-box">
            <div class="hi-icon-effect-3 hi-icon-effect-3a">
              <a class="hi-icon"><img src="img/servicebutton2.png" alt="" /></a>
            </div>
            <h3>Alteration & Design Change</h3>
            <ul style="text-align:justify">  
              <li>&#187; Get alteration during online shopping<br />
              <li>&#187; Pick & Drop service for other clothes
            </ul>
          </div>
        </div>
        <!-- /.col-lg-3 -->
        <div class="col-lg-3">
          <div class="service-box">
            <div class="hi-icon-effect-3 hi-icon-effect-3a">
              <a class="hi-icon"><i class="fa fa-cogs"></i></a>
            </div>
            <h3>Alteration for E-Commerce</h3>
            <ul style="text-align:justify">  
              <li>&#187; Alteration services for assured fitness on all online clothing purchases to enhance customer satisfaction</li>
            </ul>
          </div>
        </div>
        <!-- /.col-lg-3 -->
        <div class="col-lg-3">
          <div class="service-box">
            <div class="hi-icon-effect-3 hi-icon-effect-3a">
              <a class="hi-icon"><img src="img/servicebutton4.png" alt="" /></a>
            </div>
            <h3>Wardrobe Management</h3>
            <ul>      
              <li>&#187; Call 9990934932 to know more</li>
            </ul>

          </div>
        </div>
        <!-- /.col-lg-3 -->
      </div>
      <!-- /.row -->
    </div>
  </section>

  <section id="how" class="featured-content color-bg">
    <div class="wrap-content">
      <div class="row">
        <h3 class="clients-title" style="text-align:center;">How We Do It </h3>
    <br>
        <div class="col-lg-3">
          <div class="service-box">
            <div class="hi-icon-effect-3 hi-icon-effect-3a">
              <a class="hi-icon"><i class="fa fa-check-square"></i></a>
            </div>
            <h3>Order Online</h3>
            <ul style="text-align:justify">     
              <li>&#187; Place your order by filling the simple form</li>
              <li>&#187; Our sales team confirms your order & books an appointment</li>
            </ul>
          </div>
        </div>
        <!-- /.col-lg-3 -->
        <div class="col-lg-3">
          <div class="service-box">
            <div class="hi-icon-effect-3 hi-icon-effect-3a">
              <a class="hi-icon"><i class="fa fa-female"></i></a>
            </div>
            <h3>Designing, Fabric/Cloth Pickup</h3>
            <ul style="text-align:justify">     
              <li>&#187; Our Tailor/ representative visits your address to show huge collection of designs to select</li>
              <li>&#187; He collects fabric or unstitched/ semistitched cloth</li>
              <li>&#187; He also collects the best fit cloth for measurement reference and if required takes body measurements</li>
            </ul>
          </div>
        </div>
        <!-- /.col-lg-3 -->
        <div class="col-lg-3">
          <div class="service-box">
            <div class="hi-icon-effect-3 hi-icon-effect-3a">
              <a class="hi-icon"><i class="fa fa-clock-o"></i></a>
            </div>
            <h3>Stitching/ Alteration</h3>
              <ul style="text-align:justify">  
                <li>&#187; Our fashion designer calls to confirm design & guidelines for the dress</li>
                <li>&#187; Our Expert stitches/ Alter your dress</li>
              </ul>
          </div>
        </div>
        <!-- /.col-lg-3 -->
        <div class="col-lg-3">
          <div class="service-box">
            <div class="hi-icon-effect-3 hi-icon-effect-3a">
              <a class="hi-icon"><i class="fa fa-money"></i></a>
            </div>
            <h3>Cash on Delivery</h3>
              <ul style="text-align:justify">  
                <li>&#187; We offer you the reliability of paying once you receive the final dress</li>
              </ul>
          </div>
        </div>
        <!-- /.col-lg-3 -->
      </div>
      <!-- /.row -->
    </div>
  </section>

  <!-- Reviews -->
  <?php include 'reviews.php';?>

  <section id="about" class="featured-content color-bg">
    <div class="wrap-content">
      <h3 class="clients-title" >About Us</h3>
      <p>
        With a team of fashion designers, expert tailors, and managers of different industries, Tailor Square promises to bring unmatchable customer satisfaction by providing excellent quality in Tailoring/Boutique services.
        The company not only provides all kinds of clothes designing and stitching services but also design changes, and alteration of newly shopped or existing clothes.
        Independence of calling the tailor at home, and option to choose from more than 200 designs help the customers to avail hassle free excellent services at their doorstep in the day to day busy life.
      </p>
    </div>
  </section>

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