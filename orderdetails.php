<?php include 'commonhead.php';?>

    <script>
      var orderdetailsDataApp = angular.module('orderdetailsDataApp', []);
      orderdetailsDataApp.controller("orderdetailsDataCtrl", function ($scope, $http) {
        var order_code_param = getUrlParameter('order');
        var data = $.param({
          user_id: localStorage.user_id,
          order_code: order_code_param
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('api/v1/getorderandextrasbyuserandordercode', data, config)
        .success(function (data, status, headers, config) {
          $scope.dataset_order = data.results;
          $scope.dataset_is_stitching = true;

          if (data.results.order_type == "1") {
            $scope.dataset_designs = data.designs;
            $scope.dataset_addons = data.addons;
            $scope.dataset_addons_exist = data.addons_exist;
            $scope.dataset_addons_count = data.addons_count;
            $scope.dataset_fabric = data.fabric;
            $scope.dataset_fabric_exists = data.fabric_exists;
          } else if (data.results.order_type == "2") {
            $scope.dataset_is_stitching = false;
            $scope.dataset_addons_exist = false;
            $scope.dataset_fabric_exists = false;
            $scope.dataset_alteration = data.alteration;
          }
          switch (data.results.status) {
            case 1  :
            case 2  :
            case 3  :
            case 4  : $scope.cancel_allowed = true; $scope.return_allowed = false; break;
            case 5  : $scope.cancel_allowed = false; $scope.return_allowed = false; break;
            case 6  : $scope.cancel_allowed = false; $scope.return_allowed = true; break;
            case 7  :
            case 8  :
            case 9  :
            case 10 :
            case 11 :
            case 12 : $scope.cancel_allowed = false; $scope.return_allowed = false; break;
            default : $scope.cancel_allowed = false; $scope.return_allowed = false;
          }

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
          $scope.fabricSourceString = {
            "0" : "Already Purchased",
            "1" : "Tailor Square",
            "2" : "Sent to TailorSquare",
            "3" : "Ordered from E-Commerce"
          }
          $scope.garmentSourceString = {
            "0" : "Pickup of Existing Garment",
            "2" : "Sending Garment to TailorSquare",
            "3" : "Pickup of Garment bought online"
          }          
          $scope.measurementSourceString = {
            "1" : "TS Standard",
            "2" : "Measurement Garment",
            "3" : "Custom Measurement",
            "4" : "Pending TS Standard",
            "5" : "Pending TS Standard (free)",
            "6" : "Previous Measurement Garment"
          }
        })
        .error(function (data, status, header, config) {
          $scope.ResponseDetails = "Data: " + data +
            "<hr />status: " + status +
            "<hr />headers: " + header +
            "<hr />config: " + config;
        });

        $scope.capitalizeFirstLetter = function(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        $scope.getPrettyOrderDate = function(timestamp) {
            if (!timestamp) {
              return ""
            }

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
            if (!timestamp) {
              return ""
            }

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

      $(document).ready(function() {
        if (!localStorage.user_id) {
          window.location.href = "login.php?sender=orderdetails"
        }
        var action_param = getUrlParameter('action');
        if (!!action_param) {
          if (action_param == "cancel") {
            $("#msg_div_cancel").fadeIn();
          } else if (action_param == "return") {
            $("#msg_div_return").fadeIn();
          }
        }

        var request;
        $("#cancelorderform").submit(function(event){
            $("#cancelorderform").fadeOut(200);
            setTimeout(function(){ $("#cancelordermessage").fadeIn(); }, 200);
            if (request) {
                request.abort();
            }
            var $form = $(this);
            var $inputs = $form.find("input, select, button, textarea");
            var serializedData = $form.serialize();
            $inputs.prop("disabled", true);
            request = $.ajax({
                url: "api/v1/requestordercancelorreturn",
                type: "post",
                data: serializedData
            });
            request.done(function (response, textStatus, jqXHR){
                if(response.error){
                  console.error("The following error occurred: " + response.message);
                } else {
                  console.log("Successfully Placed Request!");
                  var order_code_param = getUrlParameter('order');
                  window.location.href = "orderdetails.php?order=" + order_code_param + "&action=cancel";
                }
                console.log(response);
            });
            request.fail(function (jqXHR, textStatus, errorThrown){
                console.error("The following error occurred: "+textStatus, errorThrown);
            });
            request.always(function () {
                $inputs.prop("disabled", false);
                $("#cancelorderform").show();
                $("#cancelordermessage").hide();
            });
            event.preventDefault();
        });

        $("#returnorderform").submit(function(event){
            $("#returnorderform").fadeOut(200);
            setTimeout(function(){ $("#returnordermessage").fadeIn(); }, 200);
            if (request) {
                request.abort();
            }
            var $form = $(this);
            var $inputs = $form.find("input, select, button, textarea");
            var serializedData = $form.serialize();
            $inputs.prop("disabled", true);
            request = $.ajax({
                url: "api/v1/requestordercancelorreturn",
                type: "post",
                data: serializedData
            });
            request.done(function (response, textStatus, jqXHR){
                if(response.error){
                  console.error("The following error occurred: " + response.message);
                } else {
                  console.log("Successfully Placed Request!");
                  var order_code_param = getUrlParameter('order');
                  window.location.href = "orderdetails.php?order=" + order_code_param + "&action=return";
                }
                console.log(response);
            });
            request.fail(function (jqXHR, textStatus, errorThrown){
                console.error("The following error occurred: "+textStatus, errorThrown);
            });
            request.always(function () {
                $inputs.prop("disabled", false);
                $("#returnorderform").show();
                $("#returnordermessage").hide();
            });
            event.preventDefault();
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

    <div class="container" ng-app="orderdetailsDataApp" ng-controller="orderdetailsDataCtrl">
      <div id="cancelOrderModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
          <!-- Modal content-->
          <div class="modal-content" style="width:400px">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title" style="text-align:center;">Cancel Order</h4>
            </div>
            <div class="modal-body">
              <form id="cancelorderform" class="tsforminmodal">
                <label for="cancel_reason">Why do you want to Cancel the order?</label>
                <textarea rows="4" id="cancel_reason" name="reason_text" class="tsforminput" placeholder="Reason for Cancellation" required></textarea>
                <input type="hidden" name="order_id" value="{{ dataset_order.order_id }}">
                <input type="hidden" name="reason_type" value="0">
                <button class="btn btn-lg btn-danger btn-block" type="submit" value="Submit">Submit</button>
              </form>
              <p id="cancelordermessage" style="text-align:center; font-size:1.2em; margin-top:127px; margin-bottom:127px;" hidden>Placing Request...</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

      <div id="returnOrderModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
          <!-- Modal content-->
          <div class="modal-content" style="width:400px">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title" style="text-align:center;">Return Order</h4>
            </div>
            <div class="modal-body">
              <form id="returnorderform" class="tsforminmodal">
                <label for="return_reason">Why do you want to Return the product?</label>
                <textarea rows="4" id="return_reason" name="reason_text" class="tsforminput" placeholder="Reason for Return" required></textarea>
                <input type="hidden" name="order_id" value="{{ dataset_order.order_id }}">
                <input type="hidden" name="reason_type" value="1">
                <button class="btn btn-lg btn-danger btn-block" type="submit" value="Submit">Submit</button>
              </form>
              <p id="returnordermessage" style="text-align:center; font-size:1.2em; margin-top:127px; margin-bottom:127px;" hidden>Placing Request...</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

      <div id="msg_div_cancel" style="display: none;">
        <div class="row">
          <div class="col-md-offset-2 col-md-10">
            <div class="alert alert-dismissible alert-info" role="alert">
              <span>We have taken your request to Cancel the order. We will get back to you shortly.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
          </div>
        </div>
      </div>

      <div id="msg_div_return" style="display: none;">
        <div class="row">
          <div class="col-md-offset-2 col-md-10">
            <div class="alert alert-dismissible alert-info" role="alert">
              <span>We have taken your request to Return the order. We will get back to you shortly.</span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-2">
        <ul class="nav nav-pills nav-stacked">
          <li role="presentation"><a href="dashboard.php">Dashboard</a></li>
          <li role="presentation" class="active"><a href="orders.php">My Orders</a></li>
          <li role="presentation"><a href="addresses.php">Manage Addresses</a></li>
          <li role="presentation"><a href="profile.php">Profile</a></li>
          <li role="presentation"><a href="changepassword.php">Change Password</a></li>
        </ul>
      </div>
      <div class="col-md-10 align-to-center">
        <h3 class="clients-title">Order Details</h3>
        <hr />
        <div class="row">
          <div class="col-md-12">
            <div class="portlet box red">
              <div class="portlet-title">
                <div>
                  <span class="myOrdersTitle">Order # {{dataset_order.order_code}}</span>
                </div>
              </div>
              <div class="portlet-body myOrdersContent">
                <img id="dress_img_{{ dataset_order.clothing_id }}" src="uploadedimages/dress/{{ dataset_order.clothing_image }}.jpg" alt="{{ dataset_order.clothing_name }}" class="myOrdersContentImg">
                <div class="myOrdersContentTable">
                  <table>
                    <tr>
                      <td class="myOrdersContentTitle">{{ orderTypeString[dataset_order.order_type] + genderString[dataset_order.is_for_women] + dataset_order.clothing_name }}</td>
                    </tr>
                    <tr>
                      <td class="myOrdersContentData">Placed On: {{ getPrettyOrderDate(dataset_order.order_date) }}</td>
                    </tr>
                    <tr>
                      <td class="myOrdersContentSpecialData">{{ dataset_order.status_text }}</span></td>
                    </tr>
                    <tr class="adaptiveShow">
                      <td class="myOrdersContentData">Delivery at: {{ dataset_order.delivery_address.address_name }}</td>
                    </tr>
                    <tr class="adaptiveShow">
                      <td class="myOrdersContentData">Pickup Needed: {{ pickupString[dataset_order.pickup_required] }}</td>
                    </tr>
                    <tr class="adaptiveShow" ng-if="dataset_order.pickup_required != 0">
                      <td class="myOrdersContentData">Pickup At: {{ dataset_order.pickup_address.address_name }}</td>
                    </tr>
                    <tr class="adaptiveShow" ng-if="dataset_order.pickup_required != 0">
                      <td class="myOrdersContentData">Pickup On: {{ getPrettyPickupDate(dataset_order.pickup_date) }}</td>
                    </tr>
                  </table>                    
                </div>
                <div class="myOrdersContentTable2">
                  <table>                    
                    <tr>
                      <td class="myOrdersContentData">Delivery at: {{ dataset_order.delivery_address.address_name }}</td>
                    </tr>
                    <tr>
                      <td class="myOrdersContentData">Pickup Needed: {{ pickupString[dataset_order.pickup_required] }}</td>
                    </tr>
                    <tr ng-if="dataset_order.pickup_required != 0">
                      <td class="myOrdersContentData">Pickup At: {{ dataset_order.pickup_address.address_name }}</td>
                    </tr>
                    <tr ng-if="dataset_order.pickup_required != 0">
                      <td class="myOrdersContentData">Pickup On: {{ getPrettyPickupDate(dataset_order.pickup_date) }}</td>
                    </tr>
                  </table>
                </div>
                <div class="myOrdersContentFooter">
                  <div class="row orderPriceTotalCell">
                    <button ng-if="cancel_allowed" class="myOrdersReturnButton btn btn-danger" data-toggle="modal" data-target="#cancelOrderModal"><span class="glyphicon glyphicon-remove"></span> Cancel Order</button>
                    <button ng-if="return_allowed" class="myOrdersReturnButton btn btn-danger" data-toggle="modal" data-target="#returnOrderModal"><i class="fa fa-undo"></i> Request Return</button>
                    <div class="orderPriceVal">&#8377; {{ dataset_order.final_total }}.00 <span ng-if="dataset_order.discount != 0" class="discountFromTotal">(&#8377; {{ dataset_order.discount }}.00 off)</span></div>
                  </div>
                </div>
                <div class="myOrdersClear"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
           <div class="panel panel-default">
              <div class="panel-heading">
                 <h3 style="font-style: italic;">ORDER SUMMARY</h3>
              </div>
              <div class="container col-md-12">
                 <div ng-if="!dataset_is_stitching" class="col-md-1"></div>
                 <div ng-if="dataset_is_stitching" class="col-md-3 orderDesignColumn">
                    <div class="row orderDesignCell">
                       <div class="col-md-12">
                          <h6>DESIGN</h6>
                       </div>
                    </div>
                    <div ng-repeat="x in dataset_designs" class="row orderDesignCell">
                       <div class="col-md-4">{{ x.design_group_name }}</div>
                       <div class="col-md-8">
                          <figure>
                             <img src="uploadedimages/design/{{ x.design_image }}.jpg" class="orderDesignImage" />
                             <figcaption>{{ x.design_name }}</figcaption>
                          </figure>
                          <br />
                       </div>
                    </div>
                    <div ng-if="dataset_addons_exist" class="row orderDesignCell">
                       <div class="col-md-12">
                          <h6>ADDONS</h6>
                       </div>
                    </div>
                    <div ng-if="dataset_addons_exist" ng-repeat="x in dataset_addons" class="row orderDesignCell">
                       <div class="col-md-4">{{ capitalizeFirstLetter(x.addon_name) }}</div>
                       <div class="col-md-8">
                          <figure>
                             <img src="uploadedimages/addon/{{ x.addon_image }}.jpg" class="orderDesignImage" />
                             <figcaption>&#8377; {{ x.addon_price }}</figcaption>
                          </figure>
                          <br />
                       </div>
                    </div>
                 </div>
                 <div ng-class="(dataset_is_stitching) ? 'col-md-5' : 'col-md-6'" class="orderDesignColumn">
                    <div class="row orderDesignCell">
                       <div class="col-md-12">
                          <h6>DETAILS</h6>
                       </div>
                    </div>
                    <table class="orderDetailsTable">
                       <tbody>
                          <tr ng-if="dataset_is_stitching">
                             <td class="tdLabel">Fabric Source</td>
                             <td class="tdVal">{{ fabricSourceString[dataset_order.fabric_method] }}</td>
                          </tr>
                          <tr ng-if="dataset_fabric_exists">
                             <td class="tdLabel">Fabric</td>
                             <td class="tdVal"><img src="uploadedimages/fabric/{{ dataset_fabric.fabric_image }}.jpg" class="frontTableContentImg" /></td>
                          </tr>
                          <tr ng-if="!dataset_is_stitching">
                             <td class="tdLabel">Garment Source</td>
                             <td class="tdVal">{{ garmentSourceString[dataset_order.fabric_method] }}</td>
                          </tr>
                          <tr ng-if="!dataset_is_stitching">
                             <td class="tdLabel">Alteration Type</td>
                             <td class="tdVal">{{ dataset_alteration.alteration_type_title }}</td>
                          </tr>
                          <tr>
                             <td class="tdLabel">Measurements Source</td>
                             <td class="tdVal">{{ measurementSourceString[dataset_order.measurement_method] }}</td>
                          </tr>
                          <tr ng-if="dataset_order.pickup_required != 0">
                             <td class="tdLabel">Pickup Date</td>
                             <td class="tdVal">{{ getPrettyPickupDate(dataset_order.pickup_date) }}</td>
                          </tr>
                          <tr ng-if="dataset_order.pickup_required != 0">
                             <td class="tdLabel">Pickup Address</td>
                             <td class="tdVal">
                                <h4>{{ dataset_order.pickup_address.address_name }}</h4>
                                <h6>{{ dataset_order.pickup_address.address_person_name }}</h6>
                                <h6>{{ dataset_order.pickup_address.address_person_mobile }}</h6>
                                <p>{{ dataset_order.pickup_address.address_line1 }} <br />
                                   {{ dataset_order.pickup_address.address_line2 }} <br />
                                   {{ dataset_order.pickup_address.address_city }} <br />
                                   {{ dataset_order.pickup_address.state_name }} - {{ dataset_order.pickup_address.address_pincode }} <br />
                                   {{ dataset_order.pickup_address.country_name }}<br />
                                   {{ dataset_order.pickup_address.address_mobile }}
                                </p>
                             </td>
                          </tr>
                          <tr>
                             <td class="tdLabel">Delivery Address</td>
                             <td class="tdVal">
                                <h4>{{ dataset_order.delivery_address.address_name }}</h4>
                                <h6>{{ dataset_order.delivery_address.address_person_name }}</h6>
                                <h6>{{ dataset_order.delivery_address.address_person_mobile }}</h6>
                                <p>{{ dataset_order.delivery_address.address_line1 }} <br />
                                   {{ dataset_order.delivery_address.address_line2 }} <br />
                                   {{ dataset_order.delivery_address.address_city }} <br />
                                   {{ dataset_order.delivery_address.state_name }} - {{ dataset_order.delivery_address.address_pincode }} <br />
                                   {{ dataset_order.delivery_address.country_name }}<br />
                                   {{ dataset_order.delivery_address.address_mobile }}
                                </p>
                             </td>
                          </tr>
                       </tbody>
                    </table>
                 </div>
                 <div class="col-md-4">
                    <div class="row orderDesignCell">
                       <div class="col-md-12">
                          <h6>PRICE</h6>
                       </div>
                    </div>
                    <div ng-if="dataset_is_stitching" class="row orderPriceCell">
                       <div class="orderPriceTitle">Basic Stitching: </div>
                       <div class="orderPriceVal">&#8377; {{ dataset_order.clothing_price }}/-</div>
                    </div>
                    <div ng-if="!dataset_is_stitching" class="row orderPriceCell">
                      <div class="orderPriceTitle">Alteration Charges: </div>
                      <div class="orderPriceVal">&#8377; {{ dataset_alteration.alteration_type_price }}/-</div>
                    </div>
                    <div ng-if="dataset_fabric_exists" class="row orderPriceCell">
                       <div class="orderPriceTitle">Fabric: </div>
                       <div class="orderPriceVal">&#8377; {{ dataset_fabric.fabric_price }}/-</div>
                    </div>
                    <div ng-if="dataset_addons_exist" ng-repeat="x in dataset_addons" class="row orderPriceCell">
                       <div class="orderPriceTitle">{{ capitalizeFirstLetter(x.addon_name) }}: </div>
                       <div class="orderPriceVal">&#8377; {{x.addon_price}}/-</div>
                    </div>
                    <div ng-if="dataset_order.measurement_method == 4" class="row orderPriceCell">
                       <div class="orderPriceTitle">TS Standard: </div>
                       <div class="orderPriceVal">&#8377; 100/-</div>
                    </div>
                    <div ng-if="dataset_order.promo_id != 0" class="row orderPriceSubTotalCell" id="order_discount_row">
                       <div class="orderPriceTitle">Sub Total: </div>
                       <div class="orderPriceVal">&#8377; {{ dataset_order.final_total + dataset_order.discount }}/-</div>
                    </div>
                    <div ng-if="dataset_order.promo_id != 0" class="row orderPriceCell" id="order_discount_row">
                       <div class="orderPriceTitle">Discount: </div>
                       <div class="orderPriceVal" style="color:#449d44">- &#8377; {{ dataset_order.discount }}/-</div>
                    </div>
                    <div class="row orderPriceTotalCell">
                       <div class="orderPriceTitle">Total: </div>
                       <div class="orderPriceVal">&#8377; {{ dataset_order.final_total }}/-</div>
                    </div>
                    <div class="row orderPriceCell">
                       <h6>SPECIAL INSTRUCTIONS</h6>
                        <p>{{ dataset_order.remarks }}</p>
                    </div>
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