<?php include 'commonhead.php';?>
  <script src="js/orders.js"></script>
  <script>
      var newOrder4DataApp = angular.module('newOrder4DataApp', []);
      newOrder4DataApp.controller('newOrder4DataCtrl', function($scope, $http) {
       // use $.param jQuery function to serialize data from JSON 
        var data = $.param({
          clothing_id: localStorage.order_dress_id
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('api/v1/getaddonsbyclothing', data, config)
        .success(function (data, status, headers, config) {
          $scope.dataset_addons = data.addons;
          for (x in data.addons) {
            var ad_item = data.addons[x];
            var ad_id = ad_item.addon_id;
            TSAddon.addonIds.push(ad_id);
            TSAddon.addonIdToString[ad_id] = ad_item.addon_name;
            TSAddon.addonIdToImg[ad_id] = "uploadedimages/addon/"+ad_item.addon_image+".jpg";
            TSAddon.addonIdToPrice[ad_id] = ad_item.addon_price;
          }
          TSAddon.addonData = data.addons;
          layoutOrderTabs();

          var showNoAddonsMessageVar = false;
          if (TSAddon.addonIds.length == 0){
            showNoAddonsMessageVar = true;
          }

          $scope.showNoAddonsMessage = showNoAddonsMessageVar;

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
    var TSAddon = {}
    $(document).ready(function() {
        TSAddon.addonIds = [];
        TSAddon.addonIdToString = {};
        TSAddon.addonIdToImg = {};
        TSAddon.addonIdToPrice = {};
        TSAddon.selectedAddonIds = [];
        TSAddon.selectedAddonStrings = {};
        TSAddon.selectedAddonImgs = {};
        TSAddon.selectedAddonPrices = {};
    });

    function tapNewOrderBack4() {
        window.location.href="neworder3.php";
    }

    function tapNewOrderNext4() {
      for (x in TSAddon.addonIds) {
        var ad_id = TSAddon.addonIds[x];
        var isSelected = $("#addon_"+ad_id).hasClass("tabContentImgActive");
        if (isSelected) {
          TSAddon.selectedAddonIds.push(ad_id);
          TSAddon.selectedAddonStrings[ad_id] = TSAddon.addonIdToString[ad_id];
          TSAddon.selectedAddonImgs[ad_id] = TSAddon.addonIdToImg[ad_id];
          TSAddon.selectedAddonPrices[ad_id] = TSAddon.addonIdToPrice[ad_id];
        }
        localStorage.setItem("order_addon_ids", JSON.stringify(TSAddon.selectedAddonIds));
        localStorage.setItem("order_addon_strings", JSON.stringify(TSAddon.selectedAddonStrings));
        localStorage.setItem("order_addon_imgs", JSON.stringify(TSAddon.selectedAddonImgs));
        localStorage.setItem("order_addon_prices", JSON.stringify(TSAddon.selectedAddonPrices));  
      }
      if (TSAddon.addonIds.length == 0){
        var selectedAddonIdsBlank = [];
        var selectedAddonStringsBlank = {};
        var selectedAddonImgsBlank = {};
        var selectedAddonPricesBlank = {};
        localStorage.setItem("order_addon_ids", JSON.stringify(selectedAddonIdsBlank));
        localStorage.setItem("order_addon_strings", JSON.stringify(selectedAddonStringsBlank));
        localStorage.setItem("order_addon_imgs", JSON.stringify(selectedAddonImgsBlank));
        localStorage.setItem("order_addon_prices", JSON.stringify(selectedAddonPricesBlank));        
      }
      window.location.href="neworder5.php";          
    }

    function selectAddon(addonObject){
      var addonObjectId = addonObject.id;
      if ($("#"+addonObjectId).hasClass("tabContentImgActive")){
        $("#"+addonObjectId).removeClass("tabContentImgActive");
      } else {
        $("#"+addonObjectId).addClass("tabContentImgActive");
      }
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
  <section id="addons" ng-app="newOrder4DataApp" ng-controller="newOrder4DataCtrl">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <div id="container">
          <div id="parentHorizontalTab">
            <div class="resp-tabs-container hor_1">
              <div>
                <div class="checkout-wrap">
                  <ul class="checkout-bar">
                     <li class="visited"><a href="neworder.php">Dress</a></li>
                     <li class="visited"><a href="neworder2.php">Fabric</a></li>
                     <li class="visited"><a href="neworder3.php">Design</a></li>
                     <li class="active"><a href="neworder4.php">Addons</a></li>
                    <li class="">Measurements</li>
                    <li class="">Pickup/Delivery</li>
                    <li class="">Confirm</li>
                  </ul>
                </div>

                <div class="row">
                  <div class="orderContentStart"></div>
                  <div id="neworder_addon_tabs">
                    <ul class="tabs">
                      <li class="active" rel="tab1">Add-ons</li>
                    </ul>

                    <div class="tab_container">
                      <h3 class="d_active tab_drawer_heading" rel="tab1">Add-ons</h3>
                      <div id="tab1" class="tab_content">
                        <div class="row">
                          <div ng-if="!showNoAddonsMessage" ng-repeat="x in dataset_addons" class="col-lg-2">
                            <figure>
                              <img id="addon_{{ x.addon_id }}" alt="{{ x.addon_name }}" src="uploadedimages/addon/{{ x.addon_image }}.jpg" class="tabContentImg" onclick="selectAddon(this)" />
                              <figcaption style="color:#DD0B0C">&#8377; {{ x.addon_price }}/-</figcaption>
                            </figure>                            
                          </div>
                          <div ng-if="showNoAddonsMessage" class="col-lg-12">
                              <p>No addons available for this dress. Please proceed to the Next step.
                          </div>
                        </div>
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
      <input type="submit" name="neworder_back_4" value="Back" id="neworder_back_4" class="btn btn-danger" onclick="tapNewOrderBack4()" />
      <input type="submit" name="neworder_next_4" value="Next" id="neworder_next_4" class="btn btn-danger" onclick="tapNewOrderNext4()" />
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