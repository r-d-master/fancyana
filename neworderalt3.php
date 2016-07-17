<?php include 'commonhead.php';?>
  <script src="js/orders.js"></script>
  <script>
      var newOrder3DataApp = angular.module('newOrder3DataApp', []);
      newOrder3DataApp.controller('newOrder3DataCtrl', function($scope, $http) {
        var data = $.param({
          clothing_id: localStorage.order_alt_dress_id
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('api/v1/getalterationtypesbyclothing', data, config)
        .success(function (data, status, headers, config) {
          $scope.dataset_alteration_types = data.alteration_types;
          for (x in data.alteration_types) {
            var atp_item = data.alteration_types[x];
            var atp_id = atp_item.alteration_type_id;
            TSAlter.AlterationData[atp_id] = atp_item;
            TSAlter.AlterationIdToString[atp_id] = atp_item.alteration_type_title;
            TSAlter.AlterationIdToPrice[atp_id] = atp_item.alteration_type_price;
            TSAlter.AlterationIds.push(atp_id);
          }
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
    var TSAlter = {}
    $(document).ready(function() {
      TSAlter.AlterationData = {};
      TSAlter.AlterationIds = [];
      TSAlter.AlterationIdToString = {};
      TSAlter.AlterationIdToPrice = {};

      layoutOrderTabs();
    });

    function tapNewOrderBack3() {
        window.location.href="neworderalt2.php";
    }

    function tapNewOrderNext3() {
      if (TSAlter.AlterationIds.length == 0) {
        localStorage.setItem("order_alt_alteration_method", 0);
        localStorage.setItem("order_alt_alteration_method_string", "Unspecified Alteration");
        localStorage.setItem("order_alt_alteration_method_price", 0);
        window.location.href="neworderalt4.php";
      } else {
        var selectedAlterationMethod = $('input[name="alteration_type"]:checked').val();
        if(!!selectedAlterationMethod) {
          localStorage.setItem("order_alt_alteration_method", selectedAlterationMethod);
          localStorage.setItem("order_alt_alteration_method_string", TSAlter.AlterationIdToString[selectedAlterationMethod]);
          localStorage.setItem("order_alt_alteration_method_price", TSAlter.AlterationIdToPrice[selectedAlterationMethod]);
          window.location.href="neworderalt4.php";
        } else {
          alert("Please select an Alteration Type");
        }
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
  <section id="alteration3" ng-app="newOrder3DataApp" ng-controller="newOrder3DataCtrl">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <div id="container">
          <div id="parentHorizontalTab">
            <div class="resp-tabs-container hor_1">
              <div>
                <div class="alt-checkout-wrap">
                  <ul class="alt-checkout-bar">
                    <li class="visited"><a href="neworderalt.php">Dress</a></li>
                    <li class="visited"><a href="neworderalt2.php">Garment</a></li>
                    <li class="active"><a href="neworderalt3.php">Alteration</a></li>
                    <li class="">Measurements</li>
                    <li class="">Pickup/Delivery</li>
                    <li class="">Confirm</li>
                  </ul>
                </div>

                <div class="row">
                  <div class="orderContentStart"></div>
                  <div id="neworder_alteration_tabs">
                    <ul class="tabs">
                      <li class="active" rel="tab1">Alteration</li>
                    </ul>

                    <div class="tab_container">
                      <h3 class="d_active tab_drawer_heading" rel="tab1">Alteration</h3>
                      <div id="tab1" class="tab_content">
                        <div class="row">
                          <div class="col-lg-12" style="margin-bottom:10px;">
                            Please select the type of Alteration that you need:
                          </div>
                        </div>
                        <div class="row" ng-repeat="x in dataset_alteration_types">
                          <div class="col-lg-3"></div>
                          <div class="col-lg-6" style="text-align:left;">
                            <div class="row" style="margin-bottom:10px;">
                              <div class="col-lg-12">
                                <input type="radio" id="alteration_type_{{ x.alteration_type_id }}" name="alteration_type" value="{{ x.alteration_type_id }}" class="redradiocheckbox" />
                                <label for="alteration_type_{{ x.alteration_type_id }}" class="redradiochecklabel">{{ x.alteration_type_title }} (<span style="color:#DD0B0C">&#8377; {{ x.alteration_type_price }}/-</span>)</label>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-3"></div>
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
      <input type="submit" name="neworder_back_3" value="Back" id="neworder_back_3" class="btn btn-danger" onclick="tapNewOrderBack3()" />
      <input type="submit" name="neworder_next_3" value="Next" id="neworder_next_3" class="btn btn-danger" onclick="tapNewOrderNext3()" />
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