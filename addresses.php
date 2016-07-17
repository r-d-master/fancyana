<?php include 'commonhead.php';?>

    <script>
      var addressDataApp = angular.module('addressDataApp', []);
      addressDataApp.controller("addressDataCtrl", function ($scope, $http) {
       // use $.param jQuery function to serialize data from JSON 
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
          $scope.dataset_addresses = data.results;
          $scope.dataset_states = data.states;
          $scope.dataset_countries = data.countries;
          $scope.dataset_user_id = localStorage.user_id;
          $scope.dataset_user_name = localStorage.user_name;
        })
        .error(function (data, status, header, config) {
          $scope.ResponseDetails = "Data: " + data +
            "<hr />status: " + status +
            "<hr />headers: " + header +
            "<hr />config: " + config;
        });
      });

    $(document).ready(function() {
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

    function pickedAddress(panelId) {
        pickedAddressVal = panelId.id.slice(19);
        $(".activePickedAddress").removeClass("activePickedAddress");
        $(".activeDeleteButton").hide();
        $('#'+panelId.id).addClass("activePickedAddress");
        $("#delete_address_footer_"+pickedAddressVal).show();
        $("#delete_address_footer_"+pickedAddressVal).addClass("activeDeleteButton");
    }

    function deleteAddress() {
        if($(".activePickedAddress").length>0) {
          var pickedAddressVal = $(".activePickedAddress")[0].id.slice(19);
          console.log(pickedAddressVal);
          $.ajax({
            url: "api/v1/voiduseraddress",
            type: "POST",
            contentType: "application/x-www-form-urlencoded",
            data: $.param({
              "address_id": pickedAddressVal
            }),
          }).done(function (data, status, jqXHR) {
            location.reload();
          }).fail(function (jqXHR, status, err) {
            console.log(err);
          }).always(function() {

          })

        }
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
          scrollTop: $("#addresses_page_title").offset().top - 70
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
    <br />
    <br />
    <div class="container">
      <div class="col-md-2">
        <ul class="nav nav-pills nav-stacked">
          <li role="presentation"><a href="dashboard.php">Dashboard</a></li>
          <li role="presentation"><a href="orders.php">My Orders</a></li>
          <li role="presentation" class="active"><a href="addresses.php">Manage Addresses</a></li>
          <li role="presentation"><a href="profile.php">Profile</a></li>
          <li role="presentation"><a href="changepassword.php">Change Password</a></li>
        </ul>
      </div>
      <div id="addresses_page_title" class="col-md-10 align-to-center">
        <h3 class="clients-title">Addresses</h3>
        <div ng-app="addressDataApp" ng-controller="addressDataCtrl">
          <hr />
          <div ng-repeat="x in dataset_addresses" class="panel panel-default col-md-3" id="user_address_panel_{{ x.address_id }}" onclick="pickedAddress(this)">
            <div class="panel-body addressPanelBody">
              <input type="radio" name="user_address" id="user_address_{{ x.address_id }}" class="hideRadioCircle" value="{{ x.address_id }}" />
              <label for="user_address_{{ x.address_id }}">
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
            <div class="panel-footer" id="delete_address_footer_{{x.address_id}}" style="display:none; background-color:#ffffff;">
              <button type="button" id="delete_address_{{x.address_id}}" onclick="deleteAddress()" class="btn btn-danger"><i class="fa fa-trash-o"></i> Delete Address</button>
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
          <div class="col-lg-12">
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
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <hr>
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