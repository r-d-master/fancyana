<?php include 'commonhead.php';?>
  <script src="js/orders.js"></script>
  <script>
      var newOrder5DataApp = angular.module('newOrder5DataApp', []);
      newOrder5DataApp.controller('newOrder5DataCtrl', function($scope, $http) {
       // use $.param jQuery function to serialize data from JSON 
        var data = $.param({
          clothing_id: localStorage.order_dress_id,
          user_id: localStorage.user_id
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('api/v1/getmeasurementmatrixbyclothinganduser', data, config)
        .success(function (data, status, headers, config) {
            if (!!data.measurement_matrix) {
              TSMeasurement.matrixObject = JSON.parse(data.measurement_matrix);
            }
            for (x in data.measurement_types) {
              var mt_id = data.measurement_types[x].measurement_type_id;
              if (TSMeasurement.matrixObject[mt_id] == 1) {
                TSMeasurement.typeIdsArray.push(mt_id);
                TSMeasurement.typesArray.push(data.measurement_types[x]);
                var mt_id_int = parseInt(mt_id);
                if(mt_id_int > 25){
                  TSMeasurement.extraTypesPresent = true;
                  TSMeasurement.typeIdsArrayCS.push(mt_id);
                  TSMeasurement.typesArrayCS.push(data.measurement_types[x]);
                } else {
                  TSMeasurement.typeIdsArrayTS.push(mt_id);
                  TSMeasurement.typesArrayTS.push(data.measurement_types[x]);                  
                }
              }
            }
            $scope.dataset_garment_measurement_sets = data.garment_measurement_sets;
            var mgarments_exist = false;
            if (data.garment_measurement_sets.length > 0) {
              mgarments_exist = true;
            }
            $scope.mgarments_found = mgarments_exist;
            TSMeasurement.mgarmentsFound = mgarments_exist;
            for (x in data.garment_measurement_sets) {
              var mgset_item = data.garment_measurement_sets[x];
              var mgset_id = mgset_item.measurement_set_id;
              TSMeasurement.mgarmentsMeasurementsObject[mgset_id] = JSON.parse(mgset_item.measurements);
            }

            $scope.dataset_measurement_types = TSMeasurement.typesArray;
            $scope.dataset_measurement_types_ts = TSMeasurement.typesArrayTS;
            $scope.dataset_measurement_types_cs = TSMeasurement.typesArrayCS;
            $scope.extraTypesPresent = TSMeasurement.extraTypesPresent;
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
    var TSStan = {}
    var TSMeasurement = {}
    var TSFetchedMeasurements = {}

    $(document).ready(function() {
        localStorage.removeItem("order_measurement_set_id");

        TSMeasurement.matrixObject = {};
        TSMeasurement.typesArray = [];
        TSMeasurement.typeIdsArray = [];
        TSMeasurement.typesArrayTS = [];
        TSMeasurement.typeIdsArrayTS = [];
        TSMeasurement.typesArrayCS = [];
        TSMeasurement.typeIdsArrayCS = [];
        TSMeasurement.extraTypesPresent = false;
        TSMeasurement.measurementsObject = {};
        TSMeasurement.mgarmentsFound = false;
        TSMeasurement.mgarmentsMeasurementsObject = {};
        TSMeasurement.customSetType0Count = 0;
        TSMeasurement.customSetType2Count = 0;
        TSMeasurement.customSetSelectedType = -1;

        TSStan.measurementTypeIds = [];
        TSStan.measurementSetIds = [];

        var ajaxdata = $.param({
          user_id: localStorage.user_id,
          clothing_id: localStorage.order_dress_id
        });
        var ajaxconfig = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $.ajax({
            url: "api/v1/getusermeasurementsbyclothing",
            type: "post",
            data: ajaxdata,
            config: ajaxconfig
        }).done(function (response, textStatus, jqXHR){
            console.log("Measurements Loaded");
            setTimeout(function(){
              fetchedAjaxMeasurements(response);
            }, 100);
            setTimeout(function(){
              loadTSStandardVals();
            }, 200);
        }).fail(function (jqXHR, textStatus, errorThrown){
            console.error(
                "The following error occurred: "+
                textStatus, errorThrown
            );
        }).always(function () {
        });
    });

    $(document).ready(function() {
      $("#custom_measurements_user_id").val(localStorage.user_id);
      $("#custom_measurements_clothing_id").val(localStorage.order_dress_id);
      var request;
      $("#new_custom_measurements_form").submit(function(event){
        var allFilled = true;
        for (x in TSMeasurement.typeIdsArray) {
          var mt_id = TSMeasurement.typeIdsArray[x];
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
              url: "api/v1/addusermeasurementsetbyclothing",
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

    function fetchedAjaxMeasurements(data) {
          TSFetchedMeasurements.dataset_measurement_types = {};
            for (x in data.measurement_types) {
              var mt = data.measurement_types[x];
              if(mt.measurement_type_description_present) {
                TSFetchedMeasurements.dataset_measurement_types[x] = mt;
                TSStan.measurementTypeIds.push(mt.measurement_type_id);
              }
            }
            TSFetchedMeasurements.dataset_ts_standard = {};
            TSFetchedMeasurements.dataset_measurement_sets = [];
            if(!!data.ts_standard){
              TSStan.tsStandardFound = true;
              var ts_standard_values = JSON.parse(data.ts_standard);
              for (y in TSStan.measurementTypeIds) {
                var mt_id = TSStan.measurementTypeIds[y];
                TSFetchedMeasurements.dataset_ts_standard[mt_id] = {
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
                if(data.measurement_sets[x].measurement_set_type == 0){
                  TSMeasurement.customSetType0Count++;
                  for (y in TSStan.measurementTypeIds) {
                    var mt_id = TSStan.measurementTypeIds[y];
                    measurements_val_object[mt_id] = {
                      "name" : data.measurement_types[y].measurement_type_name,
                      "val" : measurements_parsed[mt_id],
                      "id" : mt_id
                    }
                  }
                } else {
                  TSMeasurement.customSetType2Count++;
                  for (y in TSMeasurement.typeIdsArray) {
                    var mt_id = TSMeasurement.typeIdsArray[y];
                    measurements_val_object[mt_id] = {
                      "name" : data.measurement_types[y].measurement_type_name,
                      "val" : measurements_parsed[mt_id],
                      "id" : mt_id
                    }
                  }
                }
                var measurement_set_object = {
                  "measurement_set_type" : data.measurement_sets[x].measurement_set_type,
                  "measurement_set_id" : data.measurement_sets[x].measurement_set_id,
                  "measurement_set_name" : data.measurement_sets[x].measurement_set_name,
                  "measurement_set_create_date" : data.measurement_sets[x].measurement_set_create_date,
                  "measurements": measurements_val_object
                }
                TSFetchedMeasurements.dataset_measurement_sets.push(measurement_set_object);
                TSStan.measurementSetIds.push(data.measurement_sets[x].measurement_set_id);
              }
              createCustomMeasurementsSavedList();
            } else {
              TSStan.measurementSetsFound = false;
              createCustomMeasurementsSavedListEmpty();
            }
    }

    function createCustomMeasurementsSavedList() {
      for (x in TSFetchedMeasurements.dataset_measurement_sets) {
        var mset = TSFetchedMeasurements.dataset_measurement_sets[x];
        var msettype = mset.measurement_set_type;
        var nameToPrint = mset.measurement_set_name;
        if(!nameToPrint) {
          var indexToPrint = parseInt(x) + 1;
          nameToPrint = "Custom #" + indexToPrint;
        }
        var radioBtn = $('<input type="radio" name="custom_measurement_set_saved" id="custom_measurement_set_saved_'+mset.measurement_set_id+'" class="redradiocheckbox" onchange="loadCustomMeasurementVals('+x+', '+msettype+')"/>');
        var radioLabel = $('<label for="custom_measurement_set_saved_'+mset.measurement_set_id+'" class="redradiochecklabel">'+ nameToPrint + '</label><br />');
        radioBtn.appendTo('#custom_measurements_saved_list');
        radioLabel.appendTo('#custom_measurements_saved_list');
      }
    }

    function createCustomMeasurementsSavedListEmpty() {
      var emptyDiv = $('<div class="align-to-center"> Sorry! No custom measurement sets found.<br />You can create them from My Profile or from this page.</div>');
      emptyDiv.appendTo('#custom_measurements_saved_list');
    }

    function loadCustomMeasurementVals(msetIndex, msetType) {
      var baseid = "#measurement_c" + msetType;
      TSMeasurement.customSetSelectedType = msetType;
      if ( msetType == 0 ) {
        $("#custom_measurements_saved_dash_div").fadeIn();
        $("#custom_measurements_saved_clothing_div").hide();       
        for (x in TSMeasurement.typeIdsArray) {
          var mt_id = TSMeasurement.typeIdsArray[x];
          if(mt_id<=25) {
            $(baseid+"_t_"+mt_id).val(TSFetchedMeasurements.dataset_measurement_sets[msetIndex].measurements[mt_id].val);
          }
        }
      } else if ( msetType == 2 ) {
        $("#custom_measurements_saved_dash_div").hide();
        $("#custom_measurements_saved_clothing_div").fadeIn();       
        for (x in TSMeasurement.typeIdsArray) {
          var mt_id = TSMeasurement.typeIdsArray[x];
          $(baseid+"_t_"+mt_id).val(TSFetchedMeasurements.dataset_measurement_sets[msetIndex].measurements[mt_id].val);
        }
      }
    }

    function loadTSStandardVals() {
      if (TSStan.tsStandardFound) {
        for (x in TSMeasurement.typeIdsArray) {
          var mt_id = TSMeasurement.typeIdsArray[x];
          if(mt_id!=26&&mt_id!=27&&mt_id!=28) {
            $("#measurement_ts_t_"+mt_id).val(TSFetchedMeasurements.dataset_ts_standard[mt_id].val);
          }
        }
        setTimeout(function(){
          $("#ts_standard_found_div").show();
          $("#ts_standard_not_found_div").hide();
          selectMeasurementMethodTab(1);
        }, 400);
      } else {
        setTimeout(function(){
          $("#ts_standard_found_div").hide();
          $("#ts_standard_not_found_div").show();        
          selectMeasurementMethodTab(1);
        }, 400);
      }
    }

    function clearTSStandardVals() {
      if (TSStan.tsStandardFound) {
        for (x in TSMeasurement.typeIdsArray) {
          var mt_id = TSMeasurement.typeIdsArray[x];
          if(mt_id!=26&&mt_id!=27&&mt_id!=28) {
            $("#measurement_ts_t_"+mt_id).val("");
          }
        }
        setTimeout(function(){
          $("#ts_standard_found_div").show();
          $("#ts_standard_not_found_div").hide();        
          selectMeasurementMethodTab(1);
        }, 400);
      } else {
        setTimeout(function(){
          $("#ts_standard_found_div").hide();
          $("#ts_standard_not_found_div").show();        
          selectMeasurementMethodTab(1);
        }, 400);
      }
    }

    function tsStandardPresentUseChange() {
      var tspuChecked = $('input[name="ts_standard_present_use"]:checked').val();
      if(tspuChecked == "0") {
        loadTSStandardVals();
      } else {
        clearTSStandardVals();
      }
    }

    function tapNewOrderBack5() {
        window.location.href="neworder4.php";          
    }
    function tapNewOrderNext5() {
      var measurementMethodChecked = localStorage.order_measurement_method;
      var measurementPickupRequired = {
        1 : "0",
        2 : "4",
        3 : "0",
        4 : "0",
        5 : "0",
        6 : "0"
      }
      localStorage.order_pickup_required_measurement = measurementPickupRequired[measurementMethodChecked];

      var order_measurement_type_names = {}
      var order_measurement_type_ids = []
      for (x in TSMeasurement.typesArray) {
        order_measurement_type_names[TSMeasurement.typesArray[x].measurement_type_id] = TSMeasurement.typesArray[x].measurement_type_name;
        order_measurement_type_ids.push(TSMeasurement.typesArray[x].measurement_type_id);
      }
      localStorage.order_measurement_type_names = JSON.stringify(order_measurement_type_names);
      localStorage.order_measurement_type_ids = JSON.stringify(order_measurement_type_ids);
      var measurementMissing = false;
      if(measurementMethodChecked == 1) {
        var order_measurements = {}
        for (x in TSMeasurement.typeIdsArray) {
          var mt_id = TSMeasurement.typeIdsArray[x];
          var mval = $("#measurement_ts_t_"+mt_id).val();
          if (!!mval) {
            order_measurements[mt_id] = mval;
          } else {
            measurementMissing = true;
          }
        }
        localStorage.order_measurements = JSON.stringify(order_measurements);
      } else if (measurementMethodChecked == 4 || measurementMethodChecked == 5) {
        var order_measurements = {}
        for (x in TSMeasurement.typeIdsArrayCS) {
          var mt_id = TSMeasurement.typeIdsArrayCS[x];
          var mval = $("#measurement_ts_t_"+mt_id).val();
          if (!!mval) {
            order_measurements[mt_id] = mval;
          } else {
            measurementMissing = true;
          }
        }
        localStorage.order_measurements = JSON.stringify(order_measurements);          
      } else if (measurementMethodChecked == 3) {
        if (TSMeasurement.customSetSelectedType == 0) {
          var order_measurements = {}
          for (x in TSMeasurement.typeIdsArray) {
            var mt_id = TSMeasurement.typeIdsArray[x];
            var mval = $("#measurement_c0_t_"+mt_id).val();
            if (!!mval) {
              order_measurements[mt_id] = mval;
            } else {
              measurementMissing = true;
            }
          }
          localStorage.order_measurements = JSON.stringify(order_measurements);
        } else if (TSMeasurement.customSetSelectedType == 2) {
          var order_measurements = {}
          for (x in TSMeasurement.typeIdsArray) {
            var mt_id = TSMeasurement.typeIdsArray[x];
            var mval = $("#measurement_c2_t_"+mt_id).val();
            if (!!mval) {
              order_measurements[mt_id] = mval;
            } else {
              measurementMissing = true;
            }
          }
          localStorage.order_measurements = JSON.stringify(order_measurements);
        } else {
          measurementMissing = true
        }
      }
      if (measurementMethodChecked == 6) {
        if($(".tabContentImgActive").length == 0) {
          alert("Please select a garment!");
        } else {
          window.location.href="neworder6.php";          
        }
      } else {
        if (measurementMissing) {
          alert("Please fill all fields!");
        } else {
          window.location.href="neworder6.php";
        }
      }
    }

    function measurementInfoTapped(infoId) {
      if(infoId<=25){
        $("#measurement_info_panel_"+infoId).show();        
      }
    }

    function measurementUnInfoTapped(infoId) {
      if(infoId<=25){
        $("#measurement_info_panel_"+infoId).hide();
      }
    }

    function setValue(rangeId) {
      var rangeIdBase = rangeId.slice(0,15);
      var rangeIdVal = rangeId.slice(17);
      $("#"+rangeIdBase+"t_"+rangeIdVal).val($("#"+rangeId).val());
    }

    function setValueReverse(boxId) {
      var boxIdBase = boxId.slice(0,15);
      var boxIdVal = boxId.slice(17);
      $("#"+boxIdBase+"r_"+boxIdVal).val($("#"+boxId).val());
    }

    function setCSValue(rangeId) {
      setValue(rangeId);
      changedNewCustomMeasurement();
    }

    function setCSValueReverse(boxId) {
      setValueReverse(boxId);
      changedNewCustomMeasurement();
    }

    function changedNewCustomMeasurement() {
      var new_custom_measurements = {}
      for (x in TSMeasurement.typeIdsArray) {
        var mt_id = TSMeasurement.typeIdsArray[x];
        new_custom_measurements[mt_id] = $("#measurement_cs_t_"+mt_id).val();
      }
      var new_custom_measurements_string = JSON.stringify(new_custom_measurements);
      $("#custom_measurements_set_values").val(new_custom_measurements_string);
      // console.log(measurements_object);
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

    function selectGarment(garment_img_tag) {
      var mgarmentIdVal = garment_img_tag.id.slice(13);
      var mgarmentNameVal = garment_img_tag.alt;
      $(".tabContentImgActive").removeClass("tabContentImgActive");
      $("#"+garment_img_tag.id).addClass("tabContentImgActive");
      $('#mgarments_present_use_new').removeAttr("checked");
      $('#mgarments_present_use_old').prop("checked", true)
      mgarmentsPresentUseChange();
      loadGarmentMeasurementVals(mgarmentIdVal);
      localStorage.order_measurement_set_id = mgarmentIdVal;
    }

    function mgarmentsPresentUseChange() {
      var mgpuChecked = $('input[name="mgarments_present_use"]:checked').val();
      console.log(mgpuChecked);
      if(mgpuChecked == "0") {
        clearGarmentMeasurementVals();
      } else {

      }
      selectMeasurementMethodTab(2);
    }

    function clearGarmentMeasurementVals() {
      if (TSMeasurement.mgarmentsFound) {
        for (x in TSMeasurement.typeIdsArray) {
          var mt_id = TSMeasurement.typeIdsArray[x];
          $("#measurement_mg_t_"+mt_id).val("");
        }
        $(".tabContentImgActive").removeClass("tabContentImgActive");        
      }
    }

    function loadGarmentMeasurementVals(mgset_id) {
      if (TSMeasurement.mgarmentsFound) {
        var mvals = TSMeasurement.mgarmentsMeasurementsObject[mgset_id];
        if (!!mvals) {
          for (x in TSMeasurement.typeIdsArray) {
            var mt_id = TSMeasurement.typeIdsArray[x];
            $("#measurement_mg_t_"+mt_id).val(mvals[mt_id]);
          }
          localStorage.order_measurements = JSON.stringify(mvals);          
        }
      }
    }

    function selectMeasurementMethodTab(measurementMethodTab) {
      var measurementMethodChecked = 1;
      switch (measurementMethodTab) {
        case 1 :  measurementMethodChecked = 1;
                  if (TSStan.tsStandardFound) {
                    var tspuChecked = $('input[name="ts_standard_present_use"]:checked').val();
                    if(tspuChecked == "0") {
                      measurementMethodChecked = 1;
                    } else {
                      measurementMethodChecked = 4;
                    }
                  } else {
                    measurementMethodChecked = 5;
                  }
                  break;
        case 2 :  if (TSMeasurement.mgarmentsFound) {
                    var mgpuChecked = $('input[name="mgarments_present_use"]:checked').val();
                    if(mgpuChecked == "0") {
                      measurementMethodChecked = 2;
                    } else {
                      measurementMethodChecked = 6;
                    }
                  } else {
                    measurementMethodChecked = 2;
                  }        
                  break;
        case 3 :  measurementMethodChecked = 3;
                  break;
        default  :  console.log('error: unknown measurement method selected');                    
      }
      localStorage.order_measurement_method = measurementMethodChecked;
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
  <section id="measurement" ng-app="newOrder5DataApp" ng-controller="newOrder5DataCtrl">
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
                     <li class="active"><a href="neworder5.php">Measurements</a></li>
                    <li class="">Pickup/Delivery</li>
                    <li class="">Confirm</li>
                  </ul>
                </div>

                <div class="row">
                  <div class="orderContentStart"></div>
                  <div id="new_order_measurement_methods_tabs">
                    <ul class="tabs">
                      <li class="active" rel="tab1" onclick="selectMeasurementMethodTab(1)">TS Standard</li>
                      <li rel="tab2" onclick="selectMeasurementMethodTab(2)">My Measurement Garment</li>
                      <li rel="tab3" onclick="selectMeasurementMethodTab(3)">Custom Meaurement</li>
                    </ul>

                    <div class="tab_container">
                      <h3 class="d_active tab_drawer_heading" rel="tab1" onclick="selectMeasurementMethodTab(1)">TS Standard</h3>
                      <div id="tab1" class="tab_content">
                        <div id="ts_standard_not_found_div" class="row" style="display:none;">
                          <div class="col-lg-12" style="margin-bottom:5px;">
                            TS standard measurement is taken by our experts with great care and precision to ensure highest quality standard in all your services.
                          </div>
                          <div class="col-lg-12" style="margin-bottom:5px;">
                            It seems you don’t have a TS Standard measurement.
                          </div>
                          <div class="col-lg-12" style="margin-bottom:10px;">
                            Select a time in the next step and our expert will come to your pickup address to take your measurements.
                          </div>
                          <div class="col-lg-3"></div>
                          <div class="col-lg-6" style="text-align:left;">
                            <div class="row" style="margin-bottom:50px;">
                              <div class="col-lg-12">
                                <input type="radio" id="ts_standard_not_present_use_new" name="ts_standard_not_present_use" value="1" class="redradiocheckbox" checked="checked" />
                                <label for="ts_standard_not_present_use_new" class="redradiochecklabel">Get a new TS standard measurement<br>(First service is <span style="color:#DD0B0C">free</span> of cost)</label>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-3"></div>
                        </div>
                        <div id="ts_standard_found_div" class="row" style="display:none; margin-bottom:20px;">
                          <div class="col-lg-12" style="margin-bottom:5px;">
                            <p>TS standard measurement is taken by our experts with great care and precision to ensure highest quality standard in all your services.</p>
                          </div>
                          <div class="col-lg-3"></div>
                          <div class="col-lg-6" style="text-align:left;">
                            <div class="row" style="margin-bottom:5px;">
                              <div class="col-lg-12">
                                <input type="radio" id="ts_standard_present_use_new" name="ts_standard_present_use" value="1" onchange="tsStandardPresentUseChange()" class="redradiocheckbox" />
                                <label for="ts_standard_present_use_new" class="redradiochecklabel">Get a new TS standard measurement (<span style="color:#DD0B0C">&#8377; 100/-</span>)</label>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col-lg-12">
                                <input type="radio" id="ts_standard_present_use_old" name="ts_standard_present_use" value="0" onchange="tsStandardPresentUseChange()" class="redradiocheckbox" checked="checked" />
                                <label for="ts_standard_present_use_old" class="redradiochecklabel"> Use existing TS standard measurement</label>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-3"></div>
                        </div>
                        <div id="ts_standard_extra_div" class="row">
                          <div class="col-lg-6">
                            <table class="table orderDetailsTable">
                              <tbody>
                                <tr ng-repeat="x in dataset_measurement_types_ts" >
                                  <td class="tdLabel">{{ x.measurement_type_name }}</td>
                                  <td class="tdVal"><input type="text" style="display:inline; width:60px; margin-left:40px; text-align:center;" id="measurement_ts_t_{{ x.measurement_type_id }}" readonly="true"></td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                          <div ng-show="extraTypesPresent" class="col-lg-6">
                            Please enter the following measurements:
                            <div ng-repeat="x in dataset_measurement_types_cs" class="sliderbox">
                              <h4>{{ x.measurement_type_name }}</h4>
                              <input type="range" id="measurement_ts_r_{{ x.measurement_type_id }}" value="0" min="1" step="0.05" max="{{ x.measurement_type_max }}" oninput="setValue(this.id);"/>
                              <input type="number" step="0.01" style="display:inline;width:60px;" id="measurement_ts_t_{{ x.measurement_type_id }}" onchange="setValueReverse(this.id);" />
                              <hr />
                            </div>
                          </div>
                        </div>
                      </div>
                      <h3 class="tab_drawer_heading" rel="tab2" onclick="selectMeasurementMethodTab(2)">My Measurement Garment</h3>
                      <div id="tab2" class="tab_content">
                        <div id="mgarments_not_found_div" class="row" ng-if="!mgarments_found">
                          <div class="col-lg-12" style="margin-bottom:10px;">
                            Prepare your favourite fitting garment ready for pickup or select from one of your previously sent garments.<br />Your order will be made to fit just like it.
                          </div>
                          <div class="col-lg-3"></div>
                          <div class="col-lg-6" style="text-align:left;">
                            <div class="row" style="margin-bottom:50px;">
                              <div class="col-lg-12">
                                <input type="radio" id="mgarments_not_present_use_new" name="mgarments_not_present_use" value="0" class="redradiocheckbox" checked="checked" />
                                <label for="mgarmets_not_present_use_new" class="redradiochecklabel"> Send a new garment for measurement copy</label>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-3"></div>
                        </div>
                        <div id="mgarments_found_div" class="row" style="margin-bottom:20px;" ng-if="mgarments_found">
                          <div class="col-lg-12" style="margin-bottom:5px;">
                            Prepare your favourite fitting garment ready for pickup or select from one of your previously sent garments.<br />Your order will be made to fit just like it.
                          </div>
                          <div class="col-lg-3"></div>
                          <div class="col-lg-6" style="text-align:left;">
                            <div class="row" style="margin-bottom:5px;">
                              <div class="col-lg-12">
                                <input type="radio" id="mgarments_present_use_new" name="mgarments_present_use" value="0" onchange="mgarmentsPresentUseChange()" class="redradiocheckbox" checked="checked"/>
                                <label for="mgarments_present_use_new" class="redradiochecklabel"> Send a new garment for measurement copy</label>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col-lg-12">
                                <input type="radio" id="mgarments_present_use_old" name="mgarments_present_use" value="1" onchange="mgarmentsPresentUseChange()" class="redradiocheckbox" />
                                <label for="mgarments_present_use_old" class="redradiochecklabel"> Use a previously sent garment</label>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-3"></div>
                          <div class="col-md-12" style="margin-bottom:10px;">
                            <div ng-repeat="x in dataset_garment_measurement_sets" class="col-lg-2">
                              <figure>
                                <img id="mgarment_img_{{ x.measurement_set_id }}" src='uploadedimages/mgarment/{{ x.measurement_set_image }}.jpg' alt="{{ x.measurement_set_name }}" class="tabContentImg" onclick="selectGarment(this)">
                                <figcaption>{{ x.measurement_set_name }}</figcaption>
                              </figure>
                            </div>
                          </div>
                          <div class="col-lg-12">
                            <table class="table orderDetailsTable">
                              <tbody>
                                <tr ng-repeat="x in dataset_measurement_types" >
                                  <td class="tdLabel">{{ x.measurement_type_name }}</td>
                                  <td class="tdVal"><input type="text" style="display:inline; width:60px; margin-left:40px; text-align:center;" id="measurement_mg_t_{{ x.measurement_type_id }}" readonly="true"></td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                      <h3 class="tab_drawer_heading" rel="tab3" onclick="selectMeasurementMethodTab(3)">Custom Meaurement</h3>
                      <div id="tab3" class="tab_content">
                        <div class="row">
                          <div class="col-md-12">
                            <div class="row">
                              <div class="col-md-12" style="margin-bottom:10px;">
                                Customize your measurements right here and we will tailor your order to them. Use one of our existing custom measurement sets:
                              </div>
                              <div class="col-md-3"></div>
                              <div class="col-md-6" style="margin-bottom:10px; text-align:left;">
                                <div id="custom_measurements_saved_list" style="margin-top:10px; text-align: left !important;">
                                </div>
                              </div>
                              <div class="col-md-3"></div>                              
                            </div>
                          </div>
                          <div id="custom_measurements_saved_dash_div" class="row" style="display:none;">
                            <div class="col-lg-6">
                              <table class="table orderDetailsTable">
                                <tbody>
                                  <tr ng-repeat="x in dataset_measurement_types_ts" >
                                    <td class="tdLabel">{{ x.measurement_type_name }}</td>
                                    <td class="tdVal"><input type="text" style="display:inline; width:60px; margin-left:40px; text-align:center;" id="measurement_c0_t_{{ x.measurement_type_id }}" readonly="true"></td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                            <div ng-show="extraTypesPresent" class="col-lg-6">
                              Please enter the following measurements:
                              <div ng-repeat="x in dataset_measurement_types_cs" class="sliderbox">
                                <h4>{{ x.measurement_type_name }}</h4>
                                <input type="range" id="measurement_c0_r_{{ x.measurement_type_id }}" value="0" min="1" step="0.05" max="{{ x.measurement_type_max }}" oninput="setValue(this.id);"/>
                                <input type="number" step="0.01" style="display:inline;width:60px;" id="measurement_c0_t_{{ x.measurement_type_id }}" onchange="setValueReverse(this.id);" />
                                <hr />
                              </div>
                            </div>
                          </div>
                          <div id="custom_measurements_saved_clothing_div" class="row" style="display:none;">
                            <div class="col-lg-6">
                              <table class="table orderDetailsTable">
                                <tbody>
                                  <tr ng-repeat="x in dataset_measurement_types" >
                                    <td class="tdLabel">{{ x.measurement_type_name }}</td>
                                    <td class="tdVal"><input type="text" style="display:inline; width:60px; margin-left:40px; text-align:center;" id="measurement_c2_t_{{ x.measurement_type_id }}" readonly="true"></td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
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
                                    <td class="tsTableText"><input type="text" id="new_measurement_set_name" name="measurement_set_name" required="required"></td>
                                  </tr>
                                </table>                
                                <div ng-repeat="x in dataset_measurement_types" class="sliderbox">
                                  <h4>{{ x.measurement_type_name }}</h4>
                                  <input type="range" id="measurement_cs_r_{{ x.measurement_type_id }}" value="0" min="1" step="0.05" max="{{ x.measurement_type_max }}" oninput="setCSValue(this.id);" alt="{{ x.measurement_type_id }}" onfocus="measurementInfoTapped(this.alt)" onblur="measurementUnInfoTapped(this.alt)" />
                                  <input type="number" step="0.01" style="display:inline;width:60px;text-align:center;" id="measurement_cs_t_{{ x.measurement_type_id }}" onchange="setCSValueReverse(this.id);" alt="{{ x.measurement_type_id }}" onfocus="measurementInfoTapped(this.alt)" onblur="measurementUnInfoTapped(this.alt)" />
                                  <hr />
                                  <div ng-if="x.measurement_type_id <= 25" id="measurement_info_panel_{{ x.measurement_type_id }}" style="display:none">
                                    <div class="row">
                                      <div class="col-md-12">
                                        <div class="col-md-12 sliderInfoPanel" aria-labelledby="info">
                                          <img src="uploadedimages/measurement_type/measurement_type_{{ x.measurement_type_id }}.jpg" />
                                        </div>
                                      </div>
                                    </div>
                                    <div class="row">
                                      <div class="col-md-12 align-to-center">
                                        <span class="sliderTitle">{{ x.measurement_type_name }}</span>
                                        <p>{{ x.measurement_type_description }}</p>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <button id="add_new_custom_measurements_save" type="submit" class="btn btn-danger">Save</button>
                                <input type="hidden" id="custom_measurements_user_id" name="user_id" />
                                <input type="hidden" id="custom_measurements_clothing_id" name="clothing_id"/>
                                <input type="hidden" id="custom_measurements_set_values" name="measurements" />
                              </form>
                            </div>
                            <div class="col-md-1"></div>
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
      <input type="submit" name="neworder_back_5" value="Back" id="neworder_back_5" class="btn btn-danger" onclick="tapNewOrderBack5()" />
      <input type="submit" name="neworder_next_5" value="Next" id="neworder_next_5" class="btn btn-danger" onclick="tapNewOrderNext5()" />
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