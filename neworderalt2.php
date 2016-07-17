<?php include 'commonhead.php';?>
  <script src="js/orders.js"></script>
  <script>
      var newOrder2DataApp = angular.module('newOrder2DataApp', []);
      newOrder2DataApp.controller('newOrder2DataCtrl', function($scope, $http) {
       setGarmentBuyMethod(0);
       // use $.param jQuery function to serialize data from JSON 
        var data = $.param({
          clothing_id: localStorage.order_alt_dress_id
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('api/v1/getvendorsandfabricsbyclothing', data, config)
        .success(function (data, status, headers, config) {
          $scope.dataset_vendors = data.vendors;
          $scope.dataset_user_code = localStorage.user_code;
          layoutOrderTabs();
        })
        .error(function (data, status, header, config) {
          $scope.ResponseDetails = "Data: " + data +
            "<hr />status: " + status +
            "<hr />headers: " + header +
            "<hr />config: " + config;
        });
      });
  </script>
  <script>
    function tapNewOrderBack2() {
      window.location.href="neworderalt.php";
    }

    function tapNewOrderNext2() {
      window.location.href="neworderalt3.php";
    }

    function tapGarmentBuySwitch(garmentBuyMethod) {
      if (garmentBuyMethod == 0){
        $("#neworder_garment_online_tabs").fadeOut(400);
        $("#tabGarmentButton0").addClass("btn-danger");
        $("#tabGarmentButton1").removeClass("btn-danger");
      } else {
        $("#neworder_garment_online_tabs").fadeIn(800);
        $("#tabGarmentButton0").removeClass("btn-danger");
        $("#tabGarmentButton1").addClass("btn-danger");
      }
      setGarmentBuyMethod(garmentBuyMethod)
    }

    function setGarmentBuyMethod(garmentBuyMethod) {
      if (garmentBuyMethod == 2) {
        $("#garment_other_delivery_option").addClass("btn-danger");
        $("#garment_other_pickup_option").removeClass("btn-danger");
      } else if (garmentBuyMethod == 3) {
        $("#garment_other_delivery_option").removeClass("btn-danger");
        $("#garment_other_pickup_option").addClass("btn-danger");        
      }
      localStorage.setItem("order_alt_garment_method", garmentBuyMethod);
      var garmentBuyMethodString = {
        0 : "Pickup of Existing Garment",
        2 : "Sending Garment to TailorSquare",
        3 : "Pickup of Garment bought online"
      }
      var garmentPickupRequired = {
        0 : "1",
        2 : "0",
        3 : "2"
      }
      localStorage.setItem("order_alt_pickup_required_garment", garmentPickupRequired[garmentBuyMethod]);
      localStorage.setItem("order_alt_garment_method_string", garmentBuyMethodString[garmentBuyMethod]);
    }
  </script>

  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

  <!-- Header -->
  <?php include 'header.php';?>

  <!-- Begin Content -->
  <section id="alteration2" ng-app="newOrder2DataApp" ng-controller="newOrder2DataCtrl">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <div id="container">
          <div id="parentHorizontalTab">
            <div class="resp-tabs-container hor_1">
              <div>
                <div class="alt-checkout-wrap">
                  <ul class="alt-checkout-bar">
                    <li class="visited"><a href="neworderalt.php">Dress</a></li>
                    <li class="active"><a href="neworderalt2.php">Garment</a></li>
                    <li class="">Alteration</li>
                    <li class="">Measurements</li>
                    <li class="">Pickup/Delivery</li>
                    <li class="">Confirm</li>
                  </ul>
                </div>

                <div class="row">
                  <div class="orderContentStart"></div>
                  <h3 class="clients-title">Have you Bought the Garment?</h3>
                  <form id="neworder_garment_buy_mode_form">
                    <div class="row">
                      <div class="col-md-1"></div>
                      <div class="col-md-5">
                        <button type="button" id="tabGarmentButton0" value="0" class="tabHorizontalTitleBig btn btn-danger" onclick="tapGarmentBuySwitch(0)">I have already bought my Garment<br /> 
                                  (Provide during Pickup)</button>
                      </div>
                      <div class="col-md-5">
                        <button type="button" id="tabGarmentButton1" value="1" class="tabHorizontalTitleBig btn" onclick="tapGarmentBuySwitch(3)" >I want to buy it online</button>
                      </div>
                      <div class="col-md-1"></div>
                    </div>
                  </form>
                  <br />
                  <div id="neworder_garment_online_tabs" class="tabGroup" style="display:none;">
                    <ul class="tabs">
                      <li class="active" rel="tab1" onclick="setGarmentBuyMethod(3)">Buy From Other E-Commerce Websites</li>
                    </ul>

                    <div class="tab_container">
                      <h3 class="d_active tab_drawer_heading" rel="tab2" onclick="setGarmentBuyMethod(3)">Buy From Other E-Commerce Websites</h3>
                      <div id="tab1" class="tab_content">
                        <h5>Follow these links to purchase your garment and then select any option given below:</h5>
                        <div class="row">
                          <div ng-repeat="y in dataset_vendors" class="col-lg-2">
                            <a href="http://www.{{ y.vendor_url }}" target="_blank"><img src="uploadedimages/vendor/{{ y.vendor_image }}.jpg" width="140px" height="70px" /></a>
                          </div>
                        </div>
                        <button id="garment_other_delivery_option" class="tabInnerButtonBig btn" onclick="setGarmentBuyMethod(2)">
                          Purchase your garment online and deliver it to Tailor Square directly at this address:<br />
                          {{ dataset_user_code }}<br />Tailor Square<br />382, Sector 10A, Gurgaon-122001, Haryana
                        </button>
                        <button id="garment_other_pickup_option" class="tabInnerButtonBig btn btn-danger" onclick="setGarmentBuyMethod(3)">
                          Purchase garment online, deliver it your address and provide during pickup as per next steps.
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <br />
                <br />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

<div class="order-nav-parent" style="border:none">
    <div id="order-nav">
      <input type="submit" name="neworder_back_2" value="Back" id="neworder_back_2" class="btn btn-danger" onclick="tapNewOrderBack2()" />
      <input type="submit" name="neworder_next_2" value="Next" id="neworder_next_2" class="btn btn-danger" onclick="tapNewOrderNext2()" />
    </div>
</div>

  <!-- Footer -->
  <?php include 'footerthin.php';?>
  
  <!-- Bootstrap core JavaScript
    ================================================== -->
  <!-- Placed at the end of the document so the pages load faster -->
  <script src="js/bootstrap.min.js"></script>
  <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
  <script src="js/ie10-viewport-bug-workaround.js"></script>
</body>

</html>