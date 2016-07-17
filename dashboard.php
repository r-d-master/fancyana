<?php include 'commonhead.php';?>

    <script>
      var dashDataApp = angular.module('dashDataApp', []);
      dashDataApp.controller('dashDataCtrl', function($scope, $http) {
        var data = $.param({
          user_id: localStorage.user_id
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('api/v1/getusermeasurements', data, config)
        .success(function (data, status, headers, config) {
            $scope.dataset_measurement_types = {};
            for (x in data.measurement_types) {
              var mt = data.measurement_types[x];
              if(mt.measurement_type_description_present) {
                $scope.dataset_measurement_types[x] = mt;
                TSStan.measurementTypeIds.push(mt.measurement_type_id);
              }
            }
            $scope.dataset_ts_standard = {};
            $scope.dataset_measurement_sets = [];
            if(!!data.ts_standard){
              TSStan.tsStandardFound = true;
              var ts_standard_values = JSON.parse(data.ts_standard);
              for (y in TSStan.measurementTypeIds) {
                var mt_id = TSStan.measurementTypeIds[y];
                $scope.dataset_ts_standard[mt_id] = {
                  "name" : data.measurement_types[y].measurement_type_name,
                  "val" : ts_standard_values[mt_id],
                  "id" : mt_id
                }
              }
            } else{
              TSStan.tsStandardFound = false;
            }
            if(!!data.measurement_sets) {
              TSStan.measurementSetsFound = true;
              for (x in data.measurement_sets) {
                var measurements_val_object = {}
                var measurements_parsed = JSON.parse(data.measurement_sets[x].measurements);
                for (y in TSStan.measurementTypeIds) {
                  var mt_id = TSStan.measurementTypeIds[y];
                  measurements_val_object[mt_id] = {
                    "name" : data.measurement_types[y].measurement_type_name,
                    "val" : measurements_parsed[mt_id],
                    "id" : mt_id
                  }
                }
                var nameToPrint = data.measurement_sets[x].measurement_set_name;
                if (!nameToPrint) {
                  var indexToPrint = parseInt(x) + 1;
                  nameToPrint = "Custom #"+indexToPrint;
                }
                var measurement_set_object = {
                  "measurement_set_id" : data.measurement_sets[x].measurement_set_id,
                  "measurement_set_name" : nameToPrint,
                  "measurement_set_create_date" : getPrettyDateTime(data.measurement_sets[x].measurement_set_create_date),
                  "measurements": measurements_val_object
                }
                $scope.dataset_measurement_sets.push(measurement_set_object);
                TSStan.measurementSetIds.push(data.measurement_sets[x].measurement_set_id);
              }
              console.log($scope.dataset_measurement_sets);
            } else {
              TSStan.measurementSetsFound = false;
            }
        })
        .error(function (data, status, header, config) {
          $scope.ResponseDetails = "Data: " + data +
            "<hr />status: " + status +
            "<hr />headers: " + header +
            "<hr />config: " + config;
        });
      });
      
      var TSStan = {}

      $(document).ready(function() {
        TSStan.measurementTypeIds = [];
        TSStan.measurementSetIds = [];
          setTimeout(function(){
            tapTSStandard();
          }, 500);
      });

      function setValue(rangeId) {
        var rangeIdBase = rangeId.slice(0,15);
        var rangeIdVal = rangeId.slice(17);
        $("#"+rangeIdBase+"t_"+rangeIdVal).val($("#"+rangeId).val());
        changedMeasurement();
      }

      function changedMeasurement() {
        var measurements_object = {};
        for (x in TSStan.measurementTypeIds) {
          var mt_id = TSStan.measurementTypeIds[x];
          measurements_object[mt_id] = $("#measurement_cs_t_"+mt_id).val();
        }
        var measurements_string = JSON.stringify(measurements_object);
        $("#custom_measurements_set_values").val(measurements_string);
        // console.log(measurements_object);
      }

      function measurementInfoTapped(infoId) {
        $("#measurement_info_panel_"+infoId).show();
      }

      function measurementUnInfoTapped(infoId) {
        $("#measurement_info_panel_"+infoId).hide();
      }

      function tapTSStandard() {
        $("#ts_standard_tab_content").show();
        $("#custom_measurements_tab_content").hide();
        $("#measurements_ts_standard").addClass("btn-danger");
        $("#measurements_custom").removeClass("btn-danger");
        if(TSStan.tsStandardFound) {
          $("#user_ts_standard_found").show(400);
          $("#user_ts_standard_not_found").hide();
        } else {          
          $("#user_ts_standard_found").hide(0);
          $("#user_ts_standard_not_found").show(400);
        }
      }

      function loadMeasurementSet() {
        var msetChecked = $('input[name="custom_measurement_sets_to_load"]:checked').val();
        for (x in TSStan.measurementSetIds) {
            $("#custom_measurement_sets_display_"+TSStan.measurementSetIds[x]).hide()
        }
        $("#custom_measurement_sets_display_"+msetChecked).show(400);
      }

      function tapCustomMeasurements() {
        $("#ts_standard_tab_content").hide();
        $("#custom_measurements_tab_content").show();
        $("#measurements_ts_standard").removeClass("btn-danger");
        $("#measurements_custom").addClass("btn-danger");
        if(TSStan.measurementSetsFound) {
          $("#custom_measurement_sets_found").show(400);
          $("#custom_measurement_sets_not_found").hide();
        } else {          
          $("#custom_measurement_sets_found").hide(0);
          $("#custom_measurement_sets_not_found").show(400);
        }
      }

      function tapAddNewCustomMeasurements() {
        $("#new_custom_measurements_form_div").show()
        $("#add_new_custom_measurements_button").attr("onclick", "tapCancelNewCustomMeasurements()");
        $("#add_new_custom_measurements_button").html("Cancel");
      }

      function tapCancelNewCustomMeasurements() {
        $("#new_custom_measurements_form_div").hide()
        $("#add_new_custom_measurements_button").attr("onclick", "tapAddNewCustomMeasurements()");
        $("#add_new_custom_measurements_button").html("+ Add New Custom Measurements");
      }

      function cmToInches(valInCm) {
        valInCm * 0.393701
      }

      $(document).ready(function() {
        $("#custom_measurements_user_id").val(localStorage.user_id);
        var request;
        $("#new_custom_measurements_form").submit(function(event){
          var allFilled = true;
          for (x in TSStan.measurementTypeIds) {
            var mt_id = TSStan.measurementTypeIds[x];
            if(!$("#measurement_cs_t_"+mt_id).val()){
              allFilled = false;
            }
          }
          if (!allFilled){
            alert("You must fill all fields");
          } else {
            if (request) {
                request.abort();
            }
            var $form = $(this);
            var $inputs = $form.find("input, select, button, textarea, range");
            var serializedData = $form.serialize();
            $inputs.prop("disabled", true);
            request = $.ajax({
                url: "api/v1/addusermeasurementset",
                type: "post",
                data: serializedData
            });
            request.done(function (response, textStatus, jqXHR){
                console.log("Got a response");
                console.log(response);
                location.reload();
            });
            request.fail(function (jqXHR, textStatus, errorThrown){
                console.error(
                    "The following error occurred: "+
                    textStatus, errorThrown
                );
            });
            request.always(function () {
                $inputs.prop("disabled", false);
                tapCancelNewCustomMeasurements();
            });
            event.preventDefault();
          }
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
    <div class="container" ng-app="dashDataApp" ng-controller="dashDataCtrl">
      <div class="col-md-2">
        <ul class="nav nav-pills nav-stacked">
          <li role="presentation" class="active"><a href="dashboard.php">Dashboard</a></li>
          <li role="presentation"><a href="orders.php">My Orders</a></li>
          <li role="presentation"><a href="addresses.php">Manage Addresses</a></li>
          <li role="presentation"><a href="profile.php">Profile</a></li>
          <li role="presentation"><a href="changepassword.php">Change Password</a></li>
        </ul>
      </div>
      <div class="col-md-10 align-to-center">
        <h3 class="clients-title">Measurements</h3>
        <div class="row">
          <div class="col-md-2"></div>
          <div class="col-md-4">
            <input type="button" id="measurements_ts_standard" value="TS Standard" class="tabHorizontalTitle btn btn-danger" onclick="tapTSStandard()" />
          </div>
          <div class="col-md-4">
            <input type="button" id="measurements_custom" value="Custom Measurements" class="tabHorizontalTitle btn" onclick="tapCustomMeasurements()" />
          </div>
          <div class="col-md-2"></div>
        </div>
        <br />
        <br />
        <div id="ts_standard_tab_content">
          <div id="user_ts_standard_not_found" class="row" style="display:none;">
            <div class="col-md-1"></div>
            <div class="col-md-10 align-to-center">
              <h6>TS standard measurement is taken by our experts with great care and precision to ensure highest quality standard in all your services.</h6>
              <p>Sorry, you don't have a TS Standard taken yet. Place an order and select TS Standard as the measurement type to schedule an appointment.</p>
            </div>
            <div class="col-md-1"></div>
          </div>
          <div id="user_ts_standard_found" class="row" style="display:none;">
            <div class="col-md-1"></div>
            <div class="col-md-10 align-to-center">
              <h6>TS standard measurement is taken by our experts with great care and precision to ensure highest quality standard in all your services.</h6>
              <p>Here are your latest TS Standard measurements.</p>
              <hr />
              <table class="tsTable">
                <tr ng-repeat="x in dataset_ts_standard" class="sliderbox">
                  <td class="tsTableTitle">{{ x.name }}</td>
                  <td class="tsTableSpacer"></td>
                  <td class="tsTableText"> {{ x.val }} cm </td>
                </tr>
              </table>
            </div>
            <div class="col-md-1"></div>
          </div>
        </div>
        <div id="custom_measurements_tab_content" style="display:none;">
          <div id="custom_measurement_sets_not_found" class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10 align-to-center">
              <p>You don't have any previous measurements saved. Click the button below to add one.</p>
            </div>
            <div class="col-md-1"></div>
          </div>
          <div id="custom_measurement_sets_found" class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">
              <p class="align-to-center" style="margin-bottom:10px;">Here are your saved custom measurements. These are used for quickly filling up custom measurements during an order</p>
              <div ng-repeat="x in dataset_measurement_sets" style="text-align:left !important;">
                <input type="radio" name="custom_measurement_sets_to_load" value="{{ x.measurement_set_id }}" id="custom_measurement_sets_to_load_{{ x.measurement_set_id }}" onchange="loadMeasurementSet()" class="redradiocheckbox" />
                <label for="custom_measurement_sets_to_load_{{ x.measurement_set_id }}" class="redradiochecklabel">{{ x.measurement_set_name }} (saved at {{ x.measurement_set_create_date }})</label>
              </div>
            </div>
            <div class="col-md-2"></div>
          </div>
          <div ng-repeat="x in dataset_measurement_sets" id="custom_measurement_sets_display_{{ x.measurement_set_id }}" class="row" style="display:none;">
            <div class="col-md-1"></div>
            <div class="col-md-10 align-to-center">
              <hr />
              <table class="tsTable">
                <tr ng-repeat="y in x.measurements" class="sliderbox">
                  <td class="tsTableTitle">{{ y.name }}</td>
                  <td class="tsTableSpacer"></td>
                  <td class="tsTableText"> {{ y.val }} cm </td>
                </tr>
              </table>
            </div>
            <div class="col-md-1"></div>
          </div>
          <hr />
          <button id="add_new_custom_measurements_button" type="button" class="btn btn-default" onclick="tapAddNewCustomMeasurements()">+ Add New Custom Measurements</button>
          <div id="new_custom_measurements_form_div" class="row" style="display:none;">
            <div class="col-md-1"></div>
            <div class="col-md-10">
              <hr />
              <form id="new_custom_measurements_form">
                <table class="tsTable">
                  <tr class="sliderbox">
                    <td class="tsTableTitle">Measurement Name</td>
                    <td class="tsTableSpacer"></td>
                    <td class="tsTableText"><input type="text" id="new_measurement_set_name" name="measurement_set_name"></td>
                  </tr>
                </table>                
                <div ng-repeat="x in dataset_measurement_types" class="sliderbox">
                  <span class="sliderTitle">{{ x.measurement_type_name }}</span>
                  <input type="range" id="measurement_cs_r_{{ x.measurement_type_id }}" value="0" min="1" max="{{ x.measurement_type_max }}" step="0.05" onchange="setValue(this.id);" alt="{{ x.measurement_type_id }}" onfocus="measurementInfoTapped(this.alt)" onblur="measurementUnInfoTapped(this.alt)" />
                  <input ng-show="{{ x.measurement_type_description_present }}" type="number" step="0.01" class="sliderText" id="measurement_cs_t_{{ x.measurement_type_id }}" alt="{{ x.measurement_type_id }}" onfocus="measurementInfoTapped(this.alt)" onblur="measurementUnInfoTapped(this.alt)">
                    <div ng-show="{{ x.measurement_type_description_present }}" id="measurement_info_panel_{{ x.measurement_type_id }}" class="col-md-12 sliderInfoPanel" style="display:none" aria-labelledby="info">
                      <div class="row">
                        <div class="col-md-1"></div>                      
                        <div class="col-md-10">
                          <img src="uploadedimages/measurement_type/measurement_type_{{ x.measurement_type_id }}.jpg" />
                        </div>
                        <div class="col-md-1"></div>
                      </div>
                      <div class="row" style="margin-bottom:10px;">
                        <div class="col-md-1"></div>
                        <div class="col-md-10 align-to-center">
                          <p>{{ x.measurement_type_description }}</p>
                        </div>
                        <div class="col-md-1"></div>
                      </div>
                    </div>
                  <hr />
                </div>
                <button id="add_new_custom_measurements_save" type="submit" class="btn btn-danger">Save</button>
                <input type="hidden" id="custom_measurements_user_id" name="user_id" />
                <input type="hidden" id="custom_measurements_clothing_id" name="clothing_id" value="0"/>
                <input type="hidden" id="custom_measurements_set_values" name="measurements" />
              </form>
            </div>
            <div class="col-md-1"></div>
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