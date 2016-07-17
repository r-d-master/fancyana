<?php include 'commonhead.php';?>
  <script src="js/orders.js"></script>
  <script>
      var newOrder7DataApp = angular.module('newOrder7DataApp', []);
      newOrder7DataApp.controller('newOrder7DataCtrl', function($scope, $http) {
        var orderDetailsObject = {};
        orderDetailsObject["user_id"] = localStorage.user_id;
        orderDetailsObject["dress_id"] = localStorage.order_dress_id;
        orderDetailsObject["dress_string"] = localStorage.order_dress_string;
        orderDetailsObject["dress_price"] = localStorage.order_dress_price;
        orderDetailsObject["fabric_method"] = localStorage.order_fabric_method;
        orderDetailsObject["fabric_method_string"] = localStorage.order_fabric_method_string;
        if (localStorage.order_fabric_method == "1") {
          orderDetailsObject["fabric_method_is_ts"] = true;
          orderDetailsObject["fabric_id"] = localStorage.order_fabric_id;
          orderDetailsObject["fabric_img"] = localStorage.order_fabric_img;
          orderDetailsObject["fabric_price"] = localStorage.order_fabric_price;
        } else {
          orderDetailsObject["fabric_id"] = "0";
          orderDetailsObject["fabric_method_is_ts"] = false;          
          orderDetailsObject["fabric_price"] = "0";
        }
        orderDetailsObject["design_group_ids"] = JSON.parse(localStorage.order_design_group_ids);
        orderDetailsObject["design_group_strings"] = JSON.parse(localStorage.order_design_group_strings);
        orderDetailsObject["design_ids"] = JSON.parse(localStorage.order_design_ids);
        orderDetailsObject["design_imgs"] = JSON.parse(localStorage.order_design_imgs);
        orderDetailsObject["design_strings"] = JSON.parse(localStorage.order_design_strings);

        orderDetailsObject["addon_ids"] = JSON.parse(localStorage.order_addon_ids);
        orderDetailsObject["addon_strings"] = JSON.parse(localStorage.order_addon_strings);
        orderDetailsObject["addon_imgs"] = JSON.parse(localStorage.order_addon_imgs);
        orderDetailsObject["addon_prices"] = JSON.parse(localStorage.order_addon_prices);

        var hasSelectedAddons = false;
        var numOfAddons = 0;
        if (orderDetailsObject["addon_ids"].length > 0) {
          hasSelectedAddons = true;
          numOfAddons = orderDetailsObject["addon_ids"].length;
        }
        orderDetailsObject["has_selected_addons"] = hasSelectedAddons;

        orderDetailsObject["order_measurement_type_ids"] = JSON.parse(localStorage.order_measurement_type_ids);
        orderDetailsObject["order_measurement_type_names"] = JSON.parse(localStorage.order_measurement_type_names);
        orderDetailsObject["order_measurement_method"] = localStorage.order_measurement_method;
        if (localStorage.order_measurement_method == "1") {
          orderDetailsObject["order_measurement_method_string"] = "Existing TS Standard";
        } else if (localStorage.order_measurement_method == "2") {
          orderDetailsObject["order_measurement_method_string"] = "Garment Pickup";
        } else if (localStorage.order_measurement_method == "3") {
          orderDetailsObject["order_measurement_method_string"] = "Custom Measurements";
        } else if (localStorage.order_measurement_method == "4") {
          orderDetailsObject["order_measurement_method_string"] = "New TS Standard";
        } else if (localStorage.order_measurement_method == "5") {
          orderDetailsObject["order_measurement_method_string"] = "New TS Standard (Free)";
        } else if (localStorage.order_measurement_method == "6") {
          orderDetailsObject["order_measurement_method_string"] = "Previous Measurement Garment";
        }
        if (localStorage.order_measurement_method != "2") {
          orderDetailsObject["order_measurements_available"] = true;
          orderDetailsObject["order_measurements"] = JSON.parse(localStorage.order_measurements);
        } else {
          orderDetailsObject["order_measurements_available"] = false;
          orderDetailsObject["order_measurements"] = "0";
        }
        orderDetailsObject["order_measurement_set_id"] = localStorage.order_measurement_set_id;
        if (!localStorage.order_measurement_set_id) {
          orderDetailsObject["order_measurement_set_id"] = 0;
        }

        orderDetailsObject["order_pickup_required"] = parseInt(localStorage.order_pickup_required);
        orderDetailsObject["order_pickup_date"] = localStorage.order_pickup_date;
        if (orderDetailsObject["order_pickup_required"] > 0) {
          orderDetailsObject["order_pickup_required_bool"] = true;
          orderDetailsObject["order_pickup_address_details"] = JSON.parse(localStorage.order_pickup_address_details);
          orderDetailsObject["order_pickup_address_id"] = orderDetailsObject["order_pickup_address_details"].address_id;
        } else {
          orderDetailsObject["order_pickup_required_bool"] = false;
          orderDetailsObject["order_pickup_address_details"] = "0";
          orderDetailsObject["order_pickup_address_id"] = "0";          
        }
        orderDetailsObject["order_delivery_address_details"] = JSON.parse(localStorage.order_delivery_address_details);
        orderDetailsObject["order_delivery_address_id"] = orderDetailsObject["order_delivery_address_details"].address_id;


        var order_price_dress = parseInt(localStorage.order_dress_price);
        var order_price_total = order_price_dress;

        var order_price_fabric_show = false;
        if(localStorage.order_fabric_method == "1") {
          order_price_fabric_show = true;
          var order_price_fabric = parseInt(orderDetailsObject["fabric_price"]);
          order_price_total += order_price_fabric;
        }

        var order_price_ts_show = false;
        if(localStorage.order_measurement_method == "4") {
          order_price_ts_show = true;
          order_price_total += 100;
        }

        var order_price_addon_total = 0;
        var addons_object = {}
        addons_object["num"] = numOfAddons;
        if (hasSelectedAddons) {
          for (x in orderDetailsObject["addon_ids"]) {
            var ad_id = orderDetailsObject["addon_ids"][x];
            var ad_price = parseInt(orderDetailsObject["addon_prices"][ad_id]);
            order_price_addon_total += ad_price;
            addons_object[x] = ad_id;
          }
        }

        order_price_total += order_price_addon_total;
        
        $scope.dataset_order_price_fabric_show = order_price_fabric_show;
        $scope.dataset_order_price_ts_show = order_price_ts_show;
        $scope.dataset_order_price_total = order_price_total;

        orderDetailsObject["order_addons"] = addons_object;
        orderDetailsObject["order_price_total"] = order_price_total;
        orderDetailsObject["order_promo_id"] = 0;
        orderDetailsObject["order_discount"] = 0;
        orderDetailsObject["order_final_total"] = order_price_total;

        $scope.dataset_order_details = orderDetailsObject;
        setTimeout(function(){ TSOrder.orderDetails = orderDetailsObject; }, 400);
      });
  </script>
  <script>

    var TSOrder = {}
    var request;

    $(document).ready(function() {
      TSOrder.orderDetails = {};
    });

    function applyPromo() {
      var applyingPromoCode = $("#orderpromo").val();
      if (!applyingPromoCode) {
        return
      } else {
        checkPromo(applyingPromoCode);
      }
    }

    function checkPromo(promo_code_val) {
      if (request) {
          request.abort();
      }
      $("#promo_success_msg").fadeOut(50);
      $("#promo_failed_msg_1").fadeOut(50);
      $("#promo_failed_msg_2").fadeOut(50);

      var serializedData = $.param({
        promo_code: promo_code_val
      });
      var config = {
        headers : {
          'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
        }
      }
      request = $.ajax({
          url: "api/v1/getpromobycode",
          type: "post",
          data: serializedData
      });
      request.done(function (response, textStatus, jqXHR){
          if (!response.error) {
            console.log(response);
            var pre_total = TSOrder.orderDetails.order_price_total
            if (response.promo_minimum_amount > pre_total) {
              TSOrder.orderDetails["order_promo_id"] = 0;
              TSOrder.orderDetails["order_discount"] = 0;
              TSOrder.orderDetails["order_final_total"] = TSOrder.orderDetails["order_price_total"];
              $("#promo_failed_msg_2_amount").html(response.promo_minimum_amount);
              $("#promo_failed_msg_2").fadeIn();
            } else {
              var discountType = response.promo_type;
              var discountAmount = response.promo_discount;

              if (discountType == "1") {
                discountAmount = (discountAmount * pre_total)/100;
                discountAmount = Math.floor(discountAmount);
              }
              var post_total = pre_total - discountAmount;
              if (post_total < 0) {
                post_total = 0;
              }
              TSOrder.orderDetails["order_promo_id"] = response.promo_id;
              TSOrder.orderDetails["order_discount"] = discountAmount;
              TSOrder.orderDetails["order_final_total"] = post_total;
              $("#order_discount_row").fadeIn();
              $("#promo_success_msg").fadeIn();
            }
          } else {
            TSOrder.orderDetails["order_promo_id"] = 0;
            TSOrder.orderDetails["order_discount"] = 0;
            TSOrder.orderDetails["order_final_total"] = TSOrder.orderDetails["order_price_total"];
            $("#order_discount_row").fadeOut(50);
            $("#promo_failed_msg_1").fadeIn();
          }

          $("#order_final_discount_amount").html(TSOrder.orderDetails["order_discount"]);
          $("#order_final_total_amount").html(TSOrder.orderDetails["order_final_total"]);
      });
      request.fail(function (jqXHR, textStatus, errorThrown){
          console.error(
              "The following error occurred: "+
              textStatus, errorThrown
          );
      });
    }

    function tapNewOrderBack7() {
      window.location.href="neworder6.php";          
    }

    function tapNewOrderNext7() {
      var orderRemarksVal = $("#orderremarks").val();
      if (!orderRemarksVal) {
        orderRemarksVal = "None";
      }
      orderDetailsParams = {
        "order_type" : "1",
        "user_id" : TSOrder.orderDetails["user_id"],
        "clothing_id" : TSOrder.orderDetails["dress_id"],
        "fabric_method" : TSOrder.orderDetails["fabric_method"],
        "fabric_id" : TSOrder.orderDetails["fabric_id"],
        "designs" : JSON.stringify(TSOrder.orderDetails["design_ids"]),
        "addons" : JSON.stringify(TSOrder.orderDetails["order_addons"]),
        "measurement_method" : TSOrder.orderDetails["order_measurement_method"],
        "measurements" : JSON.stringify(TSOrder.orderDetails["order_measurements"]),
        "measurement_set_id" : TSOrder.orderDetails["order_measurement_set_id"],
        "delivery_address_id" : JSON.stringify(TSOrder.orderDetails["order_delivery_address_id"]),
        "pickup_required" : JSON.stringify(TSOrder.orderDetails["order_pickup_required"]),
        "pickup_address_id" : JSON.stringify(TSOrder.orderDetails["order_pickup_address_id"]),
        "pickup_date" : TSOrder.orderDetails["order_pickup_date"],
        "total_price" : TSOrder.orderDetails["order_price_total"],
        "remarks" : orderRemarksVal,
        "promo_id" : TSOrder.orderDetails["order_promo_id"],
        "discount" : TSOrder.orderDetails["order_discount"],
        "final_total" : TSOrder.orderDetails["order_final_total"]
      }
      console.log(orderDetailsParams);
      placeOrder(orderDetailsParams);
      // window.location.href="index.php";          
    }

    function placeOrder(orderDetailsParams) {
      $.ajax({
        url: "api/v1/addorder",
        type: "POST",
        contentType: "application/x-www-form-urlencoded",
        data: $.param(orderDetailsParams),
      }).done(function (data, status, jqXHR) {
        window.location.href="neworder8.php";
      }).fail(function (jqXHR, status, err) {
        console.log(err);
      }).always(function() {

      })      
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
  <section id="fabric" ng-app="newOrder7DataApp" ng-controller="newOrder7DataCtrl">
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
                     <li class="visited"><a href="neworder4.php">Addons</a></li>
                     <li class="visited"><a href="neworder5.php">Measurements</a></li>
                     <li class="visited"><a href="neworder6.php">Pickup/Delivery</a></li>
                     <li class="active"><a href="neworder7.php">Confirm</a></li>
                  </ul>
                </div>

                <div class="row">
                  <div class="orderContentStart"></div>
                  <div class="row">
                    <div class="col-lg-3"></div>
                    <div class="panel panel-default">
                      <div class="panel-heading"><h3 style="font-style: italic;">ORDER SUMMARY | {{ dataset_order_details.dress_string.toUpperCase() }}</h3></div>
                      <div class="panel-body">
                        <p>Please verify the order details and then click on the confirm button to place your order.</p>
                      </div>
                      <div class="container">
                        <div class="col-lg-3 orderDesignColumn">
                          <div class="row orderDesignCell">
                            <div class="col-lg-12">
                            <h6>DESIGN</h6>
                            </div>
                          </div>
                          <div ng-repeat="x in dataset_order_details.design_group_ids" class="row orderDesignCell">
                            <div class="col-lg-4">{{ dataset_order_details.design_group_strings[x] }}</div>
                            <div class="col-lg-8">
                              <figure>
                                <img src="{{ dataset_order_details.design_imgs[x] }}" class="orderDesignImage" />
                                <figcaption>{{ dataset_order_details.design_strings[x] }}</figcaption>
                              </figure>
                              <br />
                            </div>
                          </div>
                          <div ng-if="dataset_order_details.has_selected_addons" class="row orderDesignCell">
                            <div class="col-lg-12">
                            <h6>ADDONS</h6>
                            </div>
                          </div>
                          <div ng-if="dataset_order_details.has_selected_addons" ng-repeat="x in dataset_order_details.addon_ids" class="row orderDesignCell">
                            <div class="col-lg-4">{{ dataset_order_details.addon_strings[x] }}</div>
                            <div class="col-lg-8">
                              <figure>
                                <img src="{{ dataset_order_details.addon_imgs[x] }}" class="orderDesignImage" />
                                <figcaption>&#8377; {{ dataset_order_details.addon_prices[x] }}</figcaption>
                              </figure>
                              <br />
                            </div>
                          </div>
                        </div>
                        <div class="col-lg-5 orderDesignColumn">
                          <div class="row orderDesignCell">
                            <div class="col-lg-12">
                            <h6>DETAILS</h6>
                            </div>
                          </div>
                          <table class="orderDetailsTable">
                            <tbody>
                              <tr>
                                <td class="tdLabel">Fabric Source</td>
                                <td class="tdVal">{{ dataset_order_details.fabric_method_string }}</td>
                              </tr>
                              <tr ng-show="{{ dataset_order_details.fabric_method_is_ts }}">
                                <td class="tdLabel">Fabric</td>
                                <td class="tdVal"><img src="{{ dataset_order_details.fabric_img }}" class="frontTableContentImg" /></td>
                              </tr>
                              <tr>
                                <td class="tdLabel">Measurements Source</td>
                                <td class="tdVal">{{ dataset_order_details.order_measurement_method_string }}</td>
                              </tr>
                              <tr ng-show="dataset_order_details.order_pickup_required_bool">
                                <td class="tdLabel">Pickup Date</td>
                                <td class="tdVal">{{ dataset_order_details.order_pickup_date }}</td>
                              </tr>
                              <tr ng-show="dataset_order_details.order_pickup_required_bool">
                                <td class="tdLabel">Pickup Address</td>
                                <td class="tdVal">
                                      <h4>{{ dataset_order_details.order_pickup_address_details.address_name }}</h4>
                                      <h6>{{ dataset_order_details.order_pickup_address_details.address_person_name }}</h6>
                                      <h6>{{ dataset_order_details.order_pickup_address_details.address_person_mobile }}</h6>
                                      <p>{{ dataset_order_details.order_pickup_address_details.address_line1 }} <br />
                                      {{ dataset_order_details.order_pickup_address_details.address_line2 }} <br />
                                      {{ dataset_order_details.order_pickup_address_details.address_city }} <br />
                                      {{ dataset_order_details.order_pickup_address_details.state_name }} - {{ dataset_order_details.order_pickup_address_details.address_pincode }} <br />
                                      {{ dataset_order_details.order_pickup_address_details.country_name }}<br />
                                      {{ dataset_order_details.order_pickup_address_details.address_mobile }}</p>
                                </td>
                              </tr>
                              <tr>
                                <td class="tdLabel">Delivery Address</td>
                                <td class="tdVal">
                                      <h4>{{ dataset_order_details.order_delivery_address_details.address_name }}</h4>
                                      <h6>{{ dataset_order_details.order_delivery_address_details.address_person_name }}</h6>
                                      <h6>{{ dataset_order_details.order_delivery_address_details.address_person_mobile }}</h6>
                                      <p>{{ dataset_order_details.order_delivery_address_details.address_line1 }} <br />
                                      {{ dataset_order_details.order_delivery_address_details.address_line2 }} <br />
                                      {{ dataset_order_details.order_delivery_address_details.address_city }} <br />
                                      {{ dataset_order_details.order_delivery_address_details.state_name }} - {{ dataset_order_details.order_delivery_address_details.address_pincode }} <br />
                                      {{ dataset_order_details.order_delivery_address_details.country_name }}<br />
                                      {{ dataset_order_details.order_delivery_address_details.address_mobile }}</p>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <div class="col-lg-4">
                          <div class="row orderDesignCell">
                            <div class="col-lg-12">
                            <h6>PRICE</h6>
                            </div>
                          </div>
                          <div class="row orderPriceCell">
                            <div class="orderPriceTitle">Basic Stitching: </div>
                            <div class="orderPriceVal">&#8377; {{ dataset_order_details.dress_price }}/-</div>
                          </div>
                          <div ng-if="dataset_order_price_fabric_show" class="row orderPriceCell">
                            <div class="orderPriceTitle">Fabric: </div>
                            <div class="orderPriceVal">&#8377; {{ dataset_order_details.fabric_price }}/-</div>
                          </div>
                          <div ng-if="dataset_order_details.has_selected_addons" ng-repeat="x in dataset_order_details.addon_ids" class="row orderPriceCell">
                            <div class="orderPriceTitle">{{dataset_order_details.addon_strings[x]}}: </div>
                            <div class="orderPriceVal">&#8377; {{dataset_order_details.addon_prices[x]}}/-</div>
                          </div>
                          <div ng-if="dataset_order_price_ts_show" class="row orderPriceCell">
                            <div class="orderPriceTitle">TS Standard: </div>
                            <div class="orderPriceVal">&#8377; 100/-</div>
                          </div>
                          <div class="row orderPriceCell" id="order_discount_row" style="display:none;">
                            <div class="orderPriceTitle">Discount: </div>
                            <div class="orderPriceVal" style="color:#449d44">- &#8377; <span id="order_final_discount_amount">0</span>/-</div>
                          </div>
                          <div class="row orderPriceTotalCell">
                            <div class="orderPriceTitle">Total: </div>
                            <div class="orderPriceVal">&#8377; <span id="order_final_total_amount">{{ dataset_order_price_total }}</span>/-</div>
                          </div>
                          <div class="row orderPriceCell">
                            <form id="orderpromoform" class="tsformintable">
                              <div class="input-group">
                                <input type="text" class="form-control" maxlength="64" id="orderpromo" name="orderpromo" class="tsforminput" placeholder="DISCOUNT CODE" oninput="this.value=this.value.toUpperCase();">
                                <span class="input-group-btn">
                                  <button class="btn btn-success" type="button" onclick="applyPromo()">Apply</button>
                                </span>
                              </div>
                            </form>                          
                          </div>
                          <div class="row orderPriceCell">
                              <div id="promo_success_msg" style="color:#449d44; display:none;">Successfully Applied Discount!</div>
                              <div id="promo_failed_msg_1" style="color:#c9302c; display:none;">Discount Code Invalid/Expired!</div>
                              <div id="promo_failed_msg_2" style="color:#c9302c; display:none;">Minimum Order Total should be &#8377;<span id="promo_failed_msg_2_amount"></span>/-</div>
                          </div>
                          <div class="row orderPriceCell">
                            <h6>SPECIAL INSTRUCTIONS</h6>
                            <form id="orderremarksform" class="tsformintable">
                              <textarea rows="8" maxlength="1024" id="orderremarks" name="orderremarks" class="tsforminput" placeholder="Please enter any additional remarks here"></textarea>
                            </form>                          
                          </div>
                        </div>
                    </div>
                    <div class="col-lg-3"></div>
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
      <input type="submit" name="neworder_back_7" value="Back" id="neworder_back_7" class="btn btn-danger" onclick="tapNewOrderBack7()" />
      <input type="submit" name="neworder_next_7" value="Confirm" id="neworder_next_7" class="btn btn-danger" onclick="tapNewOrderNext7()" />
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