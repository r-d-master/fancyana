<?php include 'commonhead.php';?>

    <script>
      var stitchingOrdersDataApp = angular.module('stitchingOrdersDataApp', []);
      stitchingOrdersDataApp.controller("stitchingOrdersDataCtrl", function ($scope, $http) {
       // use $.param jQuery function to serialize data from JSON 
        var data = $.param({
          user_id: localStorage.user_id
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('api/v1/getordersandextrasforabyuser', data, config)
        .success(function (data, status, headers, config) {
          $scope.dataset_orders = data.results;
          $scope.dataset_measurement_types = data.measurement_types;
          $scope.dataset_design_groups = data.design_groups;
          $scope.dataset_status_types = data.status_types;
          $scope.orderTypeString = {
            "1": "Stitching",
            "2": "Alteration"
          }
          $scope.genderString= {
            0: "M",
            1: "F"
          }
          $scope.pickupString = {
            "0": "No",
            "1": "Fabric",
            "2": "Fabric from E-Commerce", 
            "4": "Measurement Garment",
            "5": "Fabric & Measurement Garment",
            "6": "Fabric from E-Commerce & Measurement Garment"
          }
          $scope.fabricSourceString = {
            "0" : "Already Purchased",
            "1" : "Tailor Square",
            "2" : "Sent to TailorSquare",
            "3" : "Ordered from E-Commerce"
          }
          $scope.measurementSourceString = {
            "1" : "TS Standard",
            "2" : "Measurement Garment",
            "3" : "Custom Measurement",
            "4" : "Pending TS Standard",
            "5" : "Pending TS Standard (free)",
            "6" : "Previous Measurement Garment"
          }
          var statusStringObj = {};
          for (x in data.status_types){
            var st = data.status_types[x];
            statusStringObj[st.status_text_id] = st.status_text;
          }
          $scope.statusString = statusStringObj;
        })
        .error(function (data, status, header, config) {
          $scope.ResponseDetails = "Data: " + data +
            "<hr />status: " + status +
            "<hr />headers: " + header +
            "<hr />config: " + config;
        });
      });
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
    <br />
    <br />
    <div class="container">
      <div class="col-lg-2">
        <ul class="nav nav-pills nav-stacked">
          <li role="presentation"><a href="dashboard.php">Dashboard</a></li>
          <li role="presentation"><a href="orders_stitching.php">Stitching Orders</a></li>
          <li role="presentation" class="active"><a href="orders_alteration.php">Alteration Orders</a></li>
          <li role="presentation"><a href="addresses.php">Manage Addresses</a></li>
          <li role="presentation"><a href="profile.php">Profile</a></li>
          <li role="presentation"><a href="changepassword.php">Change Password</a></li>
        </ul>
      </div>
      <div class="col-lg-10 align-to-center">
        <h3 class="clients-title">Alteration Orders</h3>
        <hr />
        <div class="row" ng-app="stitchingOrdersDataApp" ng-controller="stitchingOrdersDataCtrl">
          <div class="col-lg-12">
            <div class="portlet box red">
              <div class="portlet-title">
                <div class="caption">
                  <i class="fa fa-cogs"></i>Alteration Orders
                </div>
              </div>
              <div class="portlet-body table-scrollable">
                <table class="table table-bordered table-striped table-condensed">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Status</th>
                      <th>Dress</th>
                      <th>Garment</th>
                      <th>Alteration Type</th>
                      <th>Measurements</th>
                      <!-- <th>Measurements</th> -->
                      <th>Delivery</th>
                      <th>Pickup</th>
                      <th>Pickup Address</th>
                      <th>Pickup Date</th>
                      <th>Total Price</th>
                    </tr>
                    </thead>
                  <tbody>
                    <tr ng-repeat="x in dataset_orders">
                      <td>{{ x.order_id }}</td>
                      <td>{{ statusString[x.status] }}</td>
                      <td>{{ x.clothing_name +' '+genderString[x.is_for_women]}}</td>
                      <td>{{ fabricSourceString[x.fabric_method] }}</td>
                      <td>{{ x.alteration_method_string }}</td>
                      <td>{{ measurementSourceString[x.measurement_method] }}</td>
<!--                       <td ng-show="{{ x.measurement_method == 1 || x.measurement_method == 3 }}"><a href="manage_order_measurements.php?measurement_set={{ x.measurements }}" target="_blank">Get Details</a></td>
                      <td ng-hide="{{ x.measurement_method == 1 || x.measurement_method == 3 }}">Pending</td>
 -->                      <td>{{ x.delivery_address.address_name }}</td>
                      <td>{{ pickupString[x.pickup_required] }}</td>
                      <td ng-hide="{{ x.pickup_required == 0 }}">{{ x.pickup_address.address_name }}</td>
                      <td ng-show="{{ x.pickup_required == 0 }}">N/A</td>
                      <td ng-hide="{{ x.pickup_required == 0 }}">{{ x.pickup_date }}</td>
                      <td ng-show="{{ x.pickup_required == 0 }}">N/A</td>
                      <td>{{ x.total_price }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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