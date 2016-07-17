<?php include 'commonhead.php';?>
  <script src="js/orders.js"></script>
  <script>
      var newOrder2DataApp = angular.module('newOrder2DataApp', []);
      newOrder2DataApp.controller('newOrder2DataCtrl', function($scope, $http) {
       setFabricBuyMethod(0);
       // use $.param jQuery function to serialize data from JSON 
        var data = $.param({
          clothing_id: localStorage.order_dress_id
        });
         var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('api/v1/getvendorsandfabricsbyclothing', data, config)
        .success(function (data, status, headers, config) {
          $scope.dataset_fabrics = data.fabrics;
          $scope.dataset_vendors = data.vendors;
          $scope.dataset_user_code = localStorage.user_code;
          layoutOrderTabs();
          for (x in data.fabrics) {
            var fbr_item = data.fabrics[x];
            var fbr_id = fbr_item.fabric_id;
            TSFabric.fabricIds.push(fbr_id);
            TSFabric.fabricIdToString[fbr_id] = fbr_item.fabric_name;
            TSFabric.fabricIdToImg[fbr_id] = "uploadedimages/fabric/"+fbr_item.fabric_image+".jpg";
            TSFabric.fabricIdToPrice[fbr_id] = fbr_item.fabric_price;
          }
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
    var TSFabric = {}
    $(document).ready(function() {
        TSFabric.fabricIds = [];
        TSFabric.fabricIdToString = {};
        TSFabric.fabricIdToImg = {};
        TSFabric.fabricIdToPrice = {};
    });


    function tapNewOrderBack2() {
        window.location.href="neworder.php";
    }

    function tapNewOrderNext2() {
      if(localStorage.order_fabric_method == 1){
        if($(".tabContentImgActive").length!=0){
          window.location.href="neworder3.php";          
        } else {
          alert("You haven't selected a fabric")
        }
      } else {
        window.location.href="neworder3.php";
      }
    }

    function tapFabricBuySwitch(fabricBuyMethod) {
      if (fabricBuyMethod == 0){
        $("#neworder_fabric_online_tabs").fadeOut(400);
        $("#tabFabricButton0").addClass("btn-danger");
        $("#tabFabricButton1").removeClass("btn-danger");
      } else {
        $("#neworder_fabric_online_tabs").fadeIn(800);
        $("#tabFabricButton0").removeClass("btn-danger");
        $("#tabFabricButton1").addClass("btn-danger");
      }
      setFabricBuyMethod(fabricBuyMethod)
    }

    function setFabricBuyMethod(fabricBuyMethod) {
      if (fabricBuyMethod == 2) {
        $("#fabric_other_delivery_option").addClass("btn-danger");
        $("#fabric_other_pickup_option").removeClass("btn-danger");
      } else if (fabricBuyMethod == 3) {
        $("#fabric_other_delivery_option").removeClass("btn-danger");
        $("#fabric_other_pickup_option").addClass("btn-danger");        
      }
      localStorage.setItem("order_fabric_method", fabricBuyMethod);
      var fabricBuyMethodString = {
        0 : "Pickup of Existing Fabric",
        1 : "Purchasing from Tailor Square",
        2 : "Sending Fabric to TailorSquare",
        3 : "Pickup of Fabric bought online"
      }
      var fabricPickupRequired = {
        0 : "1",
        1 : "0",
        2 : "0",
        3 : "2"
      }
      localStorage.setItem("order_pickup_required_fabric", fabricPickupRequired[fabricBuyMethod]);
      localStorage.setItem("order_fabric_method_string", fabricBuyMethodString[fabricBuyMethod]);
    }

    function selectFabric(fabric_img_tag) {
      var fabricVal = fabric_img_tag.id.slice(7);
      var fabricImgVal = fabric_img_tag.alt;
      var fabricPrice = TSFabric.fabricIdToPrice[fabricVal];
      $(".tabContentImgActive").removeClass("tabContentImgActive");
      $("#"+fabric_img_tag.id).addClass("tabContentImgActive");
      localStorage.setItem("order_fabric_id", fabricVal);
      localStorage.setItem("order_fabric_img", fabricImgVal);
      localStorage.setItem("order_fabric_price", fabricPrice);
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
  <section id="fabric" ng-app="newOrder2DataApp" ng-controller="newOrder2DataCtrl">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <div id="container">
          <div id="parentHorizontalTab">
            <div class="resp-tabs-container hor_1">
              <div>
                <div class="checkout-wrap">
                  <ul class="checkout-bar">
                    <li class="visited"><a href="neworder.php">Dress</a></li>
                    <li class="active"><a href="neworder2.php">Fabric</a></li>
                    <li class="">Design</li>
                    <li class="">Addons</li>
                    <li class="">Measurements</li>
                    <li class="">Pickup/Delivery</li>
                    <li class="">Confirm</li>
                  </ul>
                </div>

                <div class="row">
                  <div class="orderContentStart"></div>
                  <h3 class="clients-title">Have you Bought the Fabric?</h3>
                  <form id="neworder_fabric_buy_mode_form">
                  <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-5">
                      <button type="button" id="tabFabricButton0" value="0" class="tabHorizontalTitleBig btn btn-danger" onclick="tapFabricBuySwitch(0)">I have already bought my Fabric<br /> 
                                (Provide during Pickup)</button>
                    </div>
                    <div class="col-md-5">
                      <button type="button" id="tabFabricButton1" value="1" class="tabHorizontalTitleBig btn" onclick="tapFabricBuySwitch(1)" >I want to buy it online</button>
                    </div>
                    <div class="col-md-1"></div>
                  </div>



                    <div class="row" style="display:none">
                      <div class="col-md-6">
                        <input id="neworder_fabric_buy_offline" type="radio" name="neworder_fabric_buy_mode" value="0" onchange="tapFabricBuySwitch(0)" checked/>
                         <label for="neworder_fabric_buy_offline" style="display:block !important;">
                          <div class="alert alert-danger">
                             <p style="padding:5%;">I have already bought my Fabric<br /> 
                                (Provide during Pickup)
                             </p>
                          </div>
                         </label>
                      </div>
                      <div class="col-md-6">
                        <input id="neworder_fabric_buy_online" type="radio" name="neworder_fabric_buy_mode" value="1" onchange="tapFabricBuySwitch(1)" />
                         <label for="neworder_fabric_buy_online" style="display:block !important;">
                            <div class="alert alert-danger">
                               <p style="padding:7%;">I want to buy it online</p>
                            </div>
                         </label>
                      </div>
                    </div>
                  </form>
                  <br />
                  <div id="neworder_fabric_online_tabs" class="tabGroup" style="display:none;">
                    <ul class="tabs">
                      <li class="active" rel="tab1" onclick="setFabricBuyMethod(1)">Buy From Tailor Square</li>
                      <li rel="tab2" onclick="setFabricBuyMethod(3)">Buy From Other E-Commerce Websites</li>
                    </ul>

                    <div class="tab_container">
                      <h3 class="d_active tab_drawer_heading" rel="tab1" onclick="setFabricBuyMethod(1)">Buy From Tailor Square</h3>
                      <div id="tab1" class="tab_content">
                        <div class="row">
            
                          <div  ng-repeat="x in dataset_fabrics" class="col-lg-2">
                            <figure>
                              <img id="fabric_{{ x.fabric_id }}" src="uploadedimages/fabric/{{ x.fabric_image }}.jpg" alt="uploadedimages/fabric/{{ x.fabric_image }}.jpg" class="tabContentImg" onclick="selectFabric(this)" />
                              <figcaption>{{ x.fabric_name }}<br/>
                                <span style="color:#DD0B0C">&#8377; {{ x.fabric_price }}/-</span>
                              </figcaption>
                            </figure>
                          </div>
            
              <div ng-show="dataset_fabrics==0" class="col-lg-12">
                              <p style="text-align:center">Coming soon</p>
                        </div>
            
                        </div>
                      </div>
                      <h3 class="tab_drawer_heading" rel="tab2" onclick="setFabricBuyMethod(3)">Buy From Other E-Commerce Websites</h3>
                      <div id="tab2" class="tab_content">
                        <h5>Follow these links to purchase your fabric and then select any option given below:</h5>
                        <div class="row">
                          <div ng-repeat="y in dataset_vendors" class="col-lg-2">
                            <a href="http://www.{{ y.vendor_url }}" target="_blank"><img src="uploadedimages/vendor/{{ y.vendor_image }}.jpg" width="140px" height="70px" /></a>
                          </div>
                        </div>
                        <button id="fabric_other_delivery_option" class="tabInnerButtonBig btn" onclick="setFabricBuyMethod(2)">
                          Purchase your fabric or cloth online and deliver it to Tailor Square directly at this address:<br />
                          {{ dataset_user_code }}<br />Tailor Square<br />382, Sector 10A, Gurgaon-122001, Haryana
                        </button>
                        <button id="fabric_other_pickup_option" class="tabInnerButtonBig btn btn-danger" onclick="setFabricBuyMethod(3)">
                          Purchase material online, deliver it your address and provide during pickup as per next steps.
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