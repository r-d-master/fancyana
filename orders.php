<?php include 'commonhead.php';?>

    <script>
      var ordersDataApp = angular.module('ordersDataApp', []);
      ordersDataApp.controller("ordersDataCtrl", function ($scope, $http) {
       // use $.param jQuery function to serialize data from JSON 
        var data = $.param({
          user_id: localStorage.user_id
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('api/v1/getordersandextrasbyuser', data, config)
        .success(function (data, status, headers, config) {
          $scope.dataset_orders = data.results;

          $scope.orderTypeString = {
            "1": "Stitching of ",
            "2": "Alteration of "
          }
          $scope.genderString= {
            0: "Men's ",
            1: "Women's "
          }
          $scope.pickupString = {
            "0": "No",
            "1": "Fabric",
            "2": "Fabric from E-Commerce", 
            "4": "Measurement Garment",
            "5": "Fabric & Measurement Garment",
            "6": "Fabric from E-Commerce & Measurement Garment"
          }
        })
        .error(function (data, status, header, config) {
          $scope.ResponseDetails = "Data: " + data +
            "<hr />status: " + status +
            "<hr />headers: " + header +
            "<hr />config: " + config;
        });

        $scope.getPrettyOrderDate = function(timestamp) {
            var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            var dayNames = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
            var date = new Date(timestamp.replace(/-/g, '/'));
            var dyi = date.getDay();
            var dy = dayNames[dyi];
            var d = date.getDate();
            var mi = date.getMonth();
            var y = date.getFullYear();
            var m = monthNames[mi];
            var prettyDate = dy + ", " + d + "-" + m + "-" + y;
            return prettyDate;
        };

        $scope.getPrettyPickupDate = function(timestamp) {

            var tY = timestamp.substr(6,4);
            var tM = timestamp.substr(3,2);
            var tD = timestamp.substr(0,2);

            if (timestamp.substr(4,1) == "-") {
              tY = timestamp.substr(0,4);
              tM = timestamp.substr(5,2);
              tD = timestamp.substr(8,2);
            }

            var timestampStr = tY + '/' + tM + '/' + tD;
            var tTime = timestamp.slice(11);
            var tMin = timestamp.substr(-5,2);

            if(tTime.length > 0) {
              var tAP = timestamp.substr(-2,1);
              var tDouble = tTime.length;
              var tHourStr = timestamp.substr(8,1);
              if (tDouble == 8) {
                tHourStr = timestamp.substr(8,2);
              }
              var tHour = parseInt(tHourStr);
              if (tAP == "a" && tHourStr == "12") {
                tHour = 0;
              }
              if (tAP == "p" && tHourStr == "12") {
                if (tHourStr == "12") {
                  tHour = 12;
                } else {
                  tHour += 12;                  
                }
              }
              var tH = tHour;
              if (tHour < 10) {
                tH = "0" + tHour;
              }
              timestampStr += ' ' + tH + ':' + tMin + ':00'; 
            } else {
              timestampStr += ' 12:00:00';
            }

            var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            var dayNames = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
            var date = new Date(timestampStr);
            var dyi = date.getDay();
            var dy = dayNames[dyi];
            var d = date.getDate();
            var mi = date.getMonth();
            var y = date.getFullYear();
            var m = monthNames[mi];
            var prettyDate = dy + ", " + d + "-" + m + "-" + y;
            return prettyDate;
        };
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
      <div class="col-md-2">
        <ul class="nav nav-pills nav-stacked">
          <li role="presentation"><a href="dashboard.php">Dashboard</a></li>
          <li role="presentation" class="active"><a href="orders.php">My Orders</a></li>
          <li role="presentation"><a href="addresses.php">Manage Addresses</a></li>
          <li role="presentation"><a href="profile.php">Profile</a></li>
          <li role="presentation"><a href="changepassword.php">Change Password</a></li>
        </ul>
      </div>
      <div class="col-md-10 align-to-center" ng-app="ordersDataApp" ng-controller="ordersDataCtrl">
        <h3 class="clients-title">My Orders</h3>
        <hr />
        <div class="row" ng-repeat="x in dataset_orders">
          <div class="col-md-12">
            <div class="portlet box red">
              <div class="portlet-title">
                <div>
                  <span class="myOrdersTitle">Order # {{x.order_code}}</span>
                  <a href="orderdetails.php?order={{x.order_code}}"><span class="myOrdersButton btn btn-default"><span class="glyphicon glyphicon-info-sign"></span><span class="adaptiveTitle"> Details</span></span></a>
                </div>
              </div>
              <div class="portlet-body myOrdersContent">
                <img id="dress_img_{{ x.clothing_id }}" src="uploadedimages/dress/{{ x.clothing_image }}.jpg" alt="{{ x.clothing_name }}" class="myOrdersContentImg">
                <div class="myOrdersContentTable">
                  <table>
                    <tr>
                      <td class="myOrdersContentTitle">{{ orderTypeString[x.order_type] + genderString[x.is_for_women] + x.clothing_name }}</td>
                    </tr>
                    <tr>
                      <td class="myOrdersContentData">Placed On: {{ getPrettyOrderDate(x.order_date) }}</td>
                    </tr>
                    <tr>
                      <td class="myOrdersContentSpecialData">{{ x.status_text }}</span></td>
                    </tr>
                    <tr class="adaptiveShow">
                      <td class="myOrdersContentData">Delivery at: {{ x.delivery_address.address_name }}</td>
                    </tr>
                    <tr class="adaptiveShow">
                      <td class="myOrdersContentData">Pickup Needed: {{ pickupString[x.pickup_required] }}</td>
                    </tr>
                    <tr class="adaptiveShow" ng-if="x.pickup_required != 0">
                      <td class="myOrdersContentData">Pickup At: {{ x.pickup_address.address_name }}</td>
                    </tr>
                    <tr class="adaptiveShow" ng-if="x.pickup_required != 0">
                      <td class="myOrdersContentData">Pickup On: {{ getPrettyPickupDate(x.pickup_date) }}</td>
                    </tr>
                  </table>                    
                </div>
                <div class="myOrdersContentTable2">
                  <table>                    
                    <tr>
                      <td class="myOrdersContentData">Delivery at: {{ x.delivery_address.address_name }}</td>
                    </tr>
                    <tr>
                      <td class="myOrdersContentData">Pickup Needed: {{ pickupString[x.pickup_required] }}</td>
                    </tr>
                    <tr ng-if="x.pickup_required != 0">
                      <td class="myOrdersContentData">Pickup At: {{ x.pickup_address.address_name }}</td>
                    </tr>
                    <tr ng-if="x.pickup_required != 0">
                      <td class="myOrdersContentData">Pickup On: {{ getPrettyPickupDate(x.pickup_date) }}</td>
                    </tr>
                  </table>
                </div>
                <div class="myOrdersContentFooter">
                  <div class="row orderPriceTotalCell">
                    <div class="orderPriceVal">&#8377; {{ x.final_total }}.00 <span ng-if="x.discount != 0" class="discountFromTotal">(&#8377; {{ x.discount }}.00 off)</span></div>
                  </div>
                </div>
                <div class="myOrdersClear"></div>
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