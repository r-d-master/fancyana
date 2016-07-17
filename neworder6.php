<?php include 'commonhead.php';?>
  <script src="js/orders.js"></script>
  <script>
      var newOrder6DataApp = angular.module('newOrder6DataApp', []);
      newOrder6DataApp.controller('newOrder6DataCtrl', function($scope, $http) {
        var data = $.param({
          user_id: localStorage.user_id
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('api/v1/getuseraddresses', data, config)
        .success(function (data, status, headers, config) {
          $scope.dataset_user_addresses = data.results;
          $scope.dataset_states = data.states;
          $scope.dataset_countries = data.countries;
          $scope.dataset_user_id = localStorage.user_id;
          $scope.dataset_user_name = localStorage.user_name;

          for (x in data.results) {
            TSAddresses.address[data.results[x].address_id] = data.results[x];
            TSAddresses.addressIds.push(data.results[x].address_id);
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

    var TSAddresses = {}

    $(document).ready(function() {
      TSAddresses.address = {};
      TSAddresses.addressIds = [];

      var fabricPickupRequired = parseInt(localStorage.order_pickup_required_fabric);
      var measurementPickupRequired = parseInt(localStorage.order_pickup_required_measurement);
      TSAddresses.pickupRequired = fabricPickupRequired + measurementPickupRequired;
      localStorage.order_pickup_required = TSAddresses.pickupRequired;
      var dateTimeNow = new Date();

      if(TSAddresses.pickupRequired > 0) {
        $("#neworder_pickup_date_div").show();
        $("#neworder_pickup_address_div").show();
      } else {
        $("#neworder_pickup_date_div").hide();
        $("#neworder_pickup_address_div").hide();        
      }

      $('#neworder_pickup_date').datetimepicker({
          format: 'DD-MM-YYYY h:mm a',
          inline: true,
          sideBySide: true,
          defaultDate: dateTimeNow,
          minDate: dateTimeNow,
          stepping: 15,
      });

      var request;
      $("#add_new_address_form").submit(function(event){
          if (request) {
              request.abort();
          }
          var $form = $(this);
          var $inputs = $form.find("input, select, button, textarea");
          var serializedData = $form.serialize();
          $inputs.prop("disabled", true);
          request = $.ajax({
              url: "api/v1/addaddress",
              type: "post",
              data: serializedData
          });
          request.done(function (response, textStatus, jqXHR){
              console.log("Successfully Added Address!");
              console.log(response);
          });
          request.fail(function (jqXHR, textStatus, errorThrown){
              console.error(
                  "The following error occurred: "+
                  textStatus, errorThrown
              );
          });
          request.always(function () {
              $inputs.prop("disabled", false);
              location.reload();
          });
          event.preventDefault();
      });

    });

    function tapNewOrderBack6() {
        window.location.href="neworder5.php";          
    }
    function tapNewOrderNext6() {
      if (TSAddresses.pickupRequired > 0) {
        var odate = $("#neworder_pickup_date").data().date;
        var opadd = $('input[name="neworder_pickup_address"]:checked').val();
        var odadd = $('input[name="neworder_delivery_address"]:checked').val();
      } else {
        var odate = "0";
        var opadd = "0";
        var odadd = $('input[name="neworder_delivery_address"]:checked').val();
      }
      if(!!odate && !!opadd && !!odadd) {
        localStorage.order_pickup_date = odate;
        localStorage.order_pickup_address = opadd;
        localStorage.order_pickup_address_details = JSON.stringify(TSAddresses.address[opadd]);
        localStorage.order_delivery_address = odadd;
        localStorage.order_delivery_address_details = JSON.stringify(TSAddresses.address[odadd]);
        window.location.href="neworder7.php";                  
      } else {
        alert("Please fill all fields!");
      }        
    } 
    function newOrderPickupAddressChange() {
        pickupAddressVal = $('input[name="neworder_pickup_address"]:checked').val();
        $(".activePickupAddress").removeClass("activePickupAddress");
        $('#neworder_pickup_address_panel_'+pickupAddressVal).addClass("activePickupAddress");
        localStorage.setItem('order_pickup_address', pickupAddressVal);
        console.log(pickupAddressVal);
    } 
    function newOrderDeliveryAddressChange() {
        deliveryAddressVal = $('input[name="neworder_delivery_address"]:checked').val();
        $(".activeDeliveryAddress").removeClass("activeDeliveryAddress");
        $('#neworder_delivery_address_panel_'+deliveryAddressVal).addClass("activeDeliveryAddress");
        localStorage.setItem('order_delivery_address', deliveryAddressVal);
        console.log(deliveryAddressVal);
    }

    function addAddressPortletBodyToggle() {
      $("#add_address_portlet").show();
      $('html, body').animate({
          scrollTop: $("#add_address_portlet").offset().top - 70
      }, 1000);
    }

    function addAddressPortletBodyHide() {
      $("#add_address_portlet").fadeOut();
      $('html, body').animate({
          scrollTop: $("#neworder_address").offset().top - 70
      }, 500);
      document.getElementById("add_new_address_form").reset();
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
  <section id="fabric" ng-app="newOrder6DataApp" ng-controller="newOrder6DataCtrl">
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
                    <li class="">Confirm</li>
                  </ul>
                </div>

                <div class="row">
                  <div class="orderContentStart"></div>
                  <!-- BEGIN FORM-->
                  <form id="neworder_address" class="form-horizontal">
                    <div class="form-body">
                      <div class="form-group">
                        <label class="col-md-3 control-label">Delivery Address</label>
                        <div class="col-md-9">
                          <div class="radio-list">
                            <div  ng-repeat="x in dataset_user_addresses" class="panel panel-default col-md-3" id="neworder_delivery_address_panel_{{ x.address_id }}" onclick="newOrderDeliveryAddressChange()" ng-class={activeDeliveryAddress:(activeIndex?activeIndex==$index:$first)}>
                              <div class="panel-body addressPanelBody">
                              <input type="radio" name="neworder_delivery_address" id="neworder_delivery_address_{{ x.address_id }}" class="hideRadioCircle" value="{{ x.address_id }}" onchange="newOrderDeliveryAddressChange()" ng-checked="$first" />
                                <label for="neworder_delivery_address_{{ x.address_id }}">
                                    <h4>{{ x.address_name }}</h4>
                                    <h6>{{ x.address_person_name }}</h6>
                                    <h6>{{ x.address_person_mobile }}</h6>
                                    <p>{{ x.address_line1 }} <br />
                                    {{ x.address_line2 }} <br />
                                    {{ x.address_city }} <br />
                                    {{ x.state_name }} - {{ x.address_pincode }} <br />
                                    {{ x.country_name }} <br />
                                    {{ x.address_mobile }}</p>
                                </label>
                              </div>
                            </div>
                            <div class="panel panel-default col-md-3" style="border: 1px dashed #7FB06F;">
                              <div class="panel-body addressPanelBody" style="height: 187px; line-height: 187px;" onclick="addAddressPortletBodyToggle()">
                                <label>
                                  <h3 style="color:#7FB06F;"><i class="fa fa-plus"></i></h3>
                                  <h3>ADD</h3>
                                </label>
                              </div>
                            </div>                            
                          </div>
                        </div>
                      </div>
                      <div class="form-group" id="neworder_pickup_address_div">
                        <label class="col-md-3 control-label">Pickup Address</label>
                        <div class="col-md-9">
                          <div class="radio-list">
                            <div  ng-repeat="x in dataset_user_addresses" class="panel panel-default col-md-3" id="neworder_pickup_address_panel_{{ x.address_id }}" onclick="newOrderPickupAddressChange()" ng-class={activePickupAddress:(activeIndex?activeIndex==$index:$first)}>
                              <div class="panel-body addressPanelBody">
                              <input type="radio" name="neworder_pickup_address" id="neworder_pickup_address_{{ x.address_id }}" class="hideRadioCircle" value="{{ x.address_id }}" onchange="newOrderPickupAddressChange()" ng-checked="$first" />
                                <label for="neworder_pickup_address_{{ x.address_id }}">
                                    <h4>{{ x.address_name }}</h4>
                                    <h6>{{ x.address_person_name }}</h6>
                                    <h6>{{ x.address_person_mobile }}</h6>
                                    <p>{{ x.address_line1 }} <br />
                                    {{ x.address_line2 }} <br />
                                    {{ x.address_city }} <br />
                                    {{ x.state_name }} - {{ x.address_pincode }} <br />
                                    {{ x.country_name }} <br />
                                    {{ x.address_mobile }}</p>
                                </label>
                              </div>
                            </div>
                            <div class="panel panel-default col-md-3" style="border: 1px dashed #7FB06F;">
                              <div class="panel-body addressPanelBody" style="height: 187px; line-height: 187px;" onclick="addAddressPortletBodyToggle()">
                                <label>
                                  <h3 style="color:#7FB06F;"><i class="fa fa-plus"></i></h3>
                                  <h3>ADD</h3>
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="form-group last" id="neworder_pickup_date_div">
                        <label class="col-md-3 control-label">Pickup Date and Time</label>
                        <div class="col-md-6">
                          <div id="neworder_pickup_date"></div>
                        </div>
                      </div>
                    </div>
                  </form>
                    <div id="add_address_portlet" class="portlet light bg-inverse" style="display:none;">
                      <div class="portlet-title">
                        <div class="align-to-center">
                          <div class="caption-subject font-red-sunglo bold uppercase"><i class="fa fa-plus"></i> Add New Address</div>
                        </div>
                      </div>
                      <div id="add_address_portlet_body" class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form id="add_new_address_form" class="form-horizontal">
                          <div class="form-body">
                            <div class="form-group">
                              <label class="col-md-4 control-label">Address Name</label>
                              <div class="col-md-4">
                                <input type="text" id="add_new_address_name" name="address_name" class="form-control input-xlarge" placeholder="Eg: Home, Office etc.">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="col-md-4 control-label">Contact Person Name</label>
                              <div class="col-md-4">
                                <input type="text" id="add_new_address_person_name" name="address_person_name" class="form-control input-xlarge" value="{{dataset_user_name}}">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="col-md-4 control-label">Address Line 1</label>
                              <div class="col-md-4">
                                <input type="text" id="add_new_address_line1" name="address_line1" class="form-control input-xlarge">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="col-md-4 control-label">Address Line 2</label>
                              <div class="col-md-4">
                                <input type="text" id="add_new_address_line2" name="address_line2" class="form-control input-xlarge">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="col-md-4 control-label">City</label>
                              <div class="col-md-4">
                                <input type="text" id="add_new_address_city" name="address_city" class="form-control input-xlarge">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="col-md-4 control-label">State</label>
                              <div class="col-md-4">
                                <select class="form-control input-xlarge" name="address_state_id">
                                  <option ng-repeat="x in dataset_states" value="{{ x.state_id }}">{{ x.state_name }}</option>
                                </select>
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="col-md-4 control-label">Pincode</label>
                              <div class="col-md-4">
                                <input type="number" id="add_new_address_pincode" name="address_pincode" class="form-control input-xlarge">
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="col-md-4 control-label">Country</label>
                              <div class="col-md-4">
                                <select class="form-control input-xlarge" name="address_country_id">
                                  <option ng-repeat="x in dataset_countries" value="{{ x.country_id }}">{{ x.country_name }}</option>
                                </select>
                              </div>
                            </div>
                            <div class="form-group">
                              <label class="col-md-4 control-label">Mobile</label>
                              <div class="col-md-4">
                                <input type="number" id="add_new_address_mobile" name="address_mobile" class="form-control input-xlarge">
                              </div>
                            </div>                            
                          </div>
                          <div class="form-actions">
                            <div class="row">
                              <div class="col-md-offset-4 col-md-4">
                                <button type="submit" class="btn green">Save</button>
                                <button type="button" class="btn red" onclick="addAddressPortletBodyHide()">Cancel</button>
                              </div>
                            </div>
                          </div>
                          <input type="hidden" name="user_id" value="{{ dataset_user_id }}" />
                        </form>
                        <!-- END FORM-->
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
      <input type="submit" name="neworder_back_6" value="Back" id="neworder_back_6" class="btn btn-danger" onclick="tapNewOrderBack6()" />
      <input type="submit" name="neworder_next_6" value="Next" id="neworder_next_6" class="btn btn-danger" onclick="tapNewOrderNext6()" />
    </div>
</div>

  <!-- Footer -->
  <?php include 'footerthin.php';?>

  <!-- Bootstrap core JavaScript
    ================================================== -->
  <!-- Placed at the end of the document so the pages load faster -->
  <script src="js/bootstrap.min.js"></script>
  <script src="js/bootstrap-datetimepicker.js"></script>
  <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
  <script src="js/ie10-viewport-bug-workaround.js"></script>
</body>

</html>