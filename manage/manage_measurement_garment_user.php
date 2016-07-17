<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data = $.param({
        user_id: localStorage.measurement_garment_search_user_id
      });
      var config = {
        headers : {
          'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
        }
      }
      $http.post('../api/v1/getallmeasurementmatricesandmgarments', data, config)
      .success(function (data, status, headers, config) {
        $scope.dataset_measurement_types = data.measurement_types;
        $scope.dataset_clothing_men = data.clothing_men;
        $scope.dataset_clothing_women = data.clothing_women;
        $scope.dataset_measurement_garments = data.measurement_garments;
        var measurement_matrices = data.measurement_matrices;
        $scope.genders=["Male", "Female"];
        for (x in data.measurement_types) {
          TSMMatrix.measurementTypeIds.push(data.measurement_types[x].measurement_type_id);
        }
        for (x in measurement_matrices) {
          var measurement_types = JSON.parse(measurement_matrices[x].measurement_types);
          TSMMatrix.matrix[measurement_matrices[x].clothing_id] = measurement_types;
        }
        $scope.dataset_measurement_matrices = TSMMatrix.matrix;
      })
      .error(function (data, status, header, config) {
        $scope.ResponseDetails = "Data: " + data +
          "<hr />status: " + status +
          "<hr />headers: " + header +
          "<hr />config: " + config;
      });
      $scope.user_id = localStorage.measurement_garment_search_user_id;
      $scope.user_name = localStorage.measurement_garment_search_user_name;
      $scope.user_email = localStorage.measurement_garment_search_user_email;
      $scope.getPrettyDateTimeAngular = getPrettyDateTime;
  });

  var TSMMatrix = {}
  $(document).ready(function() {
      TSMMatrix.matrix = {};
      TSMMatrix.measurementTypeIds = [];
      $("#add_measurement_garment_user_id").val(localStorage.measurement_garment_search_user_id);
  });

  function updateMatrixBoxes(clothing_id) {
    for (x in TSMMatrix.measurementTypeIds) {
      var boolVal = false;
      if (!!TSMMatrix.matrix[clothing_id]) {
        var measurement_type_id = TSMMatrix.measurementTypeIds[x];
        if (!!TSMMatrix.matrix[clothing_id][measurement_type_id]){
          var boolString = TSMMatrix.matrix[clothing_id][measurement_type_id];
          if (boolString == "1"){
            boolVal = true;
          }
        }
      }
      if (boolVal) {
        $("#measurement_type_input_group_"+TSMMatrix.measurementTypeIds[x]).fadeIn();
        $("#measurement_type_"+TSMMatrix.measurementTypeIds[x]).attr('required', '');
      } else {
        $("#measurement_type_input_group_"+TSMMatrix.measurementTypeIds[x]).fadeOut();        
        $("#measurement_type_"+TSMMatrix.measurementTypeIds[x]).removeAttr('required');        
      }
    }
  }

  function dressSwitch() {
    var genderChecked = $('input[name="add_measurement_garment_gender"]:checked', '#add_measurement_garment_form').val();
    var dressIdSelected = 0
    if (genderChecked == 0) {
      $("#add_measurement_garment_for_men_group").show();
      $("#add_measurement_garment_for_women_group").hide();
      dressIdSelected = $("#add_measurement_garment_for_men").val();
    } else if (genderChecked == 1){
      $("#add_measurement_garment_for_men_group").hide();
      $("#add_measurement_garment_for_women_group").show();
      dressIdSelected = $("#add_measurement_garment_for_women").val();
    }
    updateMatrixBoxes(dressIdSelected);
    $("#add_measurement_garment_clothing_id").val(dressIdSelected);
    newMGarmentNameChange();
  }

  function newMGarmentNameChange() {
    var mgarmentName = $("#add_measurement_garment_garment_name").val();
    var mgarmentNameString = mgarmentName.toLowerCase();
    var mgarmentUserId = localStorage.measurement_garment_search_user_id;
    var mgarmentClothingId = $("#add_measurement_garment_clothing_id").val();
    var mgarmentImageName = "mgarment_"+mgarmentUserId+"_"+mgarmentClothingId+"_"+mgarmentNameString;
    mgarmentImageName = mgarmentImageName.replace(/[^a-zA-Z0-9_]/g,'-');
    mgarmentImageName = mgarmentImageName + ".jpg";

    $("#add_measurement_garment_image_name").val(mgarmentImageName);
  }

  function prepareFormSubmit() {
    var mgarment_measurements_object = {};
    var clothing_id = $("#add_measurement_garment_clothing_id").val();
    if (!!TSMMatrix.matrix[clothing_id]) {
      for (x in TSMMatrix.measurementTypeIds) {
        var measurement_type_id = TSMMatrix.measurementTypeIds[x];
        if (!!TSMMatrix.matrix[clothing_id][measurement_type_id]){
          var boolString = TSMMatrix.matrix[clothing_id][measurement_type_id];
          if (boolString == "1"){
            var measurement_val = $("#measurement_type_"+measurement_type_id).val();
            mgarment_measurements_object[measurement_type_id] = measurement_val;
          }
        }
      }
      var mgarment_measurements_string = JSON.stringify(mgarment_measurements_object);
      $("#add_measurement_garment_measurements").val(mgarment_measurements_string);
      console.log(mgarment_measurements_object);
      return true;
    } else {
      alert("This dress has no measurement matrix. Please select another dress or setup a measurement matrix for this first");
      return false;
    }
  }



</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Measurement Garments
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
    <div class="portlet light bg-inverse">
      <div class="portlet-title">
        <div class="caption">
          <span class="caption-subject font-red-sunglo bold uppercase">Add A Measurement Garment for {{ user_name +" (" + user_email +")" }}</span>
          <span class="caption-helper">use this form to add a measurement garment for the selected user</span>
        </div>
        <div class="tools">
          <a href="javascript:;" class="collapse">
          </a>
        </div>
      </div>
      <div class="portlet-body form">
        <!-- BEGIN FORM-->
        <form id="add_measurement_garment_form" class="form-horizontal" onsubmit="return prepareFormSubmit()" action="upload.php" method="post" enctype="multipart/form-data">
          <div class="form-body">
            <div class="form-group">
              <label class="col-md-3 control-label">Gender</label>
              <div class="col-md-4">
                <div class="radio-list">
                  <label class="radio-inline">
                  <input type="radio" name="add_measurement_garment_gender" id="add_measurement_garment_gender_0" value="0" onchange="dressSwitch()" required> For Men </label>
                  <label class="radio-inline">
                  <input type="radio" name="add_measurement_garment_gender" id="add_measurement_garment_gender_1" value="1" onchange="dressSwitch()" required> For Women </label>
                </div>
              </div>
            </div>
            <div id="add_measurement_garment_for_men_group" class="form-group" style="display: none;">
              <label class="col-md-3 control-label">Dress</label>
              <div class="col-md-4">
                <select id="add_measurement_garment_for_men" class="form-control input-medium" onchange="dressSwitch()">
                  <option ng-repeat="y in dataset_clothing_men" value="{{ y.clothing_id }}">{{ y.clothing_name }}</option>
                </select>
              </div>
            </div>
            <div id="add_measurement_garment_for_women_group" class="form-group" style="display: none;">
              <label class="col-md-3 control-label">Dress</label>
              <div class="col-md-4">
                <select id="add_measurement_garment_for_women" class="form-control input-medium" onchange="dressSwitch()">
                  <option ng-repeat="z in dataset_clothing_women" value="{{ z.clothing_id }}">{{ z.clothing_name }}</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-3 control-label">Garment Name</label>
            <div class="col-md-4">
              <input type="text" id="add_measurement_garment_garment_name" name="mgarment_name" class="form-control" placeholder="Eg: Blue Shirt 3" onchange="newMGarmentNameChange()" required>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-3 control-label">Upload Image</label>
            <div class="col-md-4">
              <span class="btn btn-success fileinput-button">
                  <input type="file" name="fileToUpload" id="add_measurement_garment_upload_image" onchange="generateImagePreview(this)" accept=".jpg" required><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</input>
                </span>
            </div>
          </div>
          <div id="add_measurement_garment_upload_image_group" class="form-group hide-group">
            <label class="col-md-3 control-label">Image Preview</label>
            <div class="col-md-4">
              <img id="add_measurement_garment_upload_image_preview" src="" alt="Please Select an Image File" class="backinputimg" />
            </div>
          </div>
          <div class="form-group last">
            <label class="col-md-3 control-label">Image Name</label>
            <div class="col-md-4">
              <input type="text" id="add_measurement_garment_image_name" name="image_name" class="form-control" readonly />
            </div>
          </div>          
          <div class="form-group" ng-repeat="x in dataset_measurement_types" id="measurement_type_input_group_{{ x.measurement_type_id }}" style="display:none;">
            <label class="col-md-3 control-label">{{ x.measurement_type_name }}</label>
            <div class="col-md-4">
              <input type="number" id="measurement_type_{{ x.measurement_type_id }}" class="form-control" style="width:100px;">
            </div>
          </div>
          <div class="form-actions">
            <div class="row">
              <div class="col-md-offset-3 col-md-4">
                <button type="submit" class="btn green">Submit</button>
              </div>
            </div>
          </div>
          <div id="upload_result_dialog_group" class="form-actions" hidden>
            <div class="row">
              <div class="col-md-offset-3 col-md-4">
                <div id="upload_result_dialog_alert" class="alert alert-dismissible alert-success" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <span id="upload_result_dialog_text"></span>
                </div>
              </div>
            </div>
          </div>
          <input type="hidden" id="add_measurement_garment_user_id" name="user_id" />
          <input type="hidden" id="add_measurement_garment_clothing_id" name="clothing_id" />
          <input type="hidden" id="add_measurement_garment_measurements" name="mgarment_measurements" />
          <input type="hidden" name="folder" value="mgarment" />
          <input type="hidden" name="action_function" value="add_measurement_garment" />
          <input type="hidden" name="callback_url" value="manage_measurement_garment_user.php" />
        </form>
        <!-- END FORM-->
      </div>
    </div>
    <div class="portlet box red">
      <div class="portlet-title">
        <div class="caption">
          <i class="fa fa-cogs"></i>Measurement Garments for {{ user_name +" (" + user_email +")" }} 
        </div>
        <div class="tools">
          <a href="javascript:;" class="collapse">
          </a>
          <a href="javascript:;" class="reload">
          </a>
        </div>
      </div>
      <div class="portlet-body table-scrollable">
        <table id="fabric_table" class="table table-bordered table-striped table-condensed flip-content backtable-centered">
          <thead>
            <tr>
              <th>#</th>
              <th>Dress</th>
              <th>Gender</th>
              <th>Garment Name</th>
              <th>Garment Image</th>
              <th>Added On</th>
            </tr>
            </thead>
          <tbody>
            <tr ng-repeat="x in dataset_measurement_garments" id="data_mgarment_{{ x.measurement_set_id }}_row">
              <td>{{ $index+1 }}</td>
              <td>{{ x.clothing_name }}</td>
              <td>{{ genders[x.is_for_women] }}</td>
              <td>{{ x.measurement_set_name }}</td>
              <td><img src="../uploadedimages/mgarment/{{ x.measurement_set_image }}.jpg" class="backtableimg" /></td>
              <td>{{ getPrettyDateTimeAngular(x.measurement_set_create_date) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
