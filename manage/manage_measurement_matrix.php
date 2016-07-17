<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getallmeasurementmatrices", data)
      .then(function (response) {
        $scope.dataset_measurement_types = response.data.measurement_types;
        $scope.dataset_clothing_men = response.data.clothing_men;
        $scope.dataset_clothing_women = response.data.clothing_women;
        var measurement_matrices = response.data.measurement_matrices;
        $scope.genders=["M", "F"];
        for (x in response.data.measurement_types) {
          TSMMatrix.measurementTypeIds.push(response.data.measurement_types[x].measurement_type_id);
        }
        for (x in measurement_matrices) {
          var measurement_types = JSON.parse(measurement_matrices[x].measurement_types);
          TSMMatrix.matrix[measurement_matrices[x].clothing_id] = measurement_types;
        }
        $scope.dataset_measurement_matrices = TSMMatrix.matrix;
      });
  });

	var TSMMatrix = {}
	$(document).ready(function() {
      TSMMatrix.matrix = {};
      TSMMatrix.measurementTypeIds = [];
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
      $("#box_measurement_type_"+TSMMatrix.measurementTypeIds[x]).prop('checked', boolVal);
    }
  }

  function toggleMatrixBox() {
    var measurement_types_object = {};
    for (x in TSMMatrix.measurementTypeIds) {
      var isChecked = $("#box_measurement_type_"+TSMMatrix.measurementTypeIds[x]).is(":checked");
      var isCheckedString = "0";
      if (isChecked){
        isCheckedString = "1"
      }
      measurement_types_object[TSMMatrix.measurementTypeIds[x]] = isCheckedString;
    }
    var measurement_types_string = JSON.stringify(measurement_types_object);
    $("#measurement_matrix_measurement_types").val(measurement_types_string);
  }

  function dressSwitch() {
  	var genderChecked = $('input[name="measurement_matrix_gender"]:checked', '#measurement_matrix_form').val();
  	var dressIdSelected = 0
  	if (genderChecked == 0) {
  		$("#measurement_matrix_for_men_group").show();
  		$("#measurement_matrix_for_women_group").hide();
  		dressIdSelected = $("#measurement_matrix_for_men").val();
  	} else if (genderChecked == 1){
  		$("#measurement_matrix_for_men_group").hide();
  		$("#measurement_matrix_for_women_group").show();
  		dressIdSelected = $("#measurement_matrix_for_women").val();
  	}
    updateMatrixBoxes(dressIdSelected);
    toggleMatrixBox();
  	$("#measurement_matrix_clothing_id").val(dressIdSelected);
  }
</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Measurement Matrix
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Modify Measurement Types for a Dress</span>
					<span class="caption-helper">use this form to modify measurement types for a dress</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="measurement_matrix_form" class="form-horizontal" action="uploadplain.php" method="post">
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Gender</label>
							<div class="col-md-4">
								<div class="radio-list">
									<label class="radio-inline">
									<input type="radio" name="measurement_matrix_gender" id="measurement_matrix_gender_0" value="0" onchange="dressSwitch()" > For Men </label>
									<label class="radio-inline">
									<input type="radio" name="measurement_matrix_gender" id="measurement_matrix_gender_1" value="1" onchange="dressSwitch()"> For Women </label>
								</div>
							</div>
						</div>
						<div id="measurement_matrix_for_men_group" class="form-group" style="display: none;">
							<label class="col-md-3 control-label">Dress</label>
							<div class="col-md-4">
								<select id="measurement_matrix_for_men" class="form-control input-medium" onchange="dressSwitch()">
									<option ng-repeat="y in dataset_clothing_men" value="{{ y.clothing_id }}">{{ y.clothing_name }}</option>
								</select>
							</div>
						</div>
						<div id="measurement_matrix_for_women_group" class="form-group" style="display: none;">
							<label class="col-md-3 control-label">Dress</label>
							<div class="col-md-4">
								<select id="measurement_matrix_for_women" class="form-control input-medium" onchange="dressSwitch()">
									<option ng-repeat="z in dataset_clothing_women" value="{{ z.clothing_id }}">{{ z.clothing_name }}</option>
								</select>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">Measurement Types</label>
						<div class="col-md-4">
							<div ng-repeat="x in dataset_measurement_types" class="md-checkbox">
								<input type="checkbox" id="box_measurement_type_{{ x.measurement_type_id }}" class="md-check" value="{{ x.measurement_type_id }}" onchange="toggleMatrixBox()">
                <label for="box_measurement_type_{{ x.measurement_type_id }}"><span></span><span class="check"></span><span class="box"></span>{{ x.measurement_type_name }}</label>
							</div>
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
				  	<input type="hidden" id="measurement_matrix_clothing_id" name="measurement_matrix_clothing_id" />
				  	<input type="hidden" id="measurement_matrix_measurement_types" name="measurement_matrix_measurement_types" />
					<input type="hidden" name="action_function" value="update_measurement_matrix" />
					<input type="hidden" name="callback_url" value="manage_measurement_matrix.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Measurement Types needed for Dresses
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
					<a href="javascript:;" class="reload">
					</a>
				</div>
			</div>
			<div class="portlet-body">
        <div class="table-scrollable">
  				<table class="table table-bordered table-striped table-condensed backtable-centered">
            <thead>
              <tr>
                <th>Measurement Type</th>
                <th ng-repeat="x in dataset_clothing_men">{{ x.clothing_name + ' ' + genders[x.is_for_women] }}</th>
                <th ng-repeat="x in dataset_clothing_women">{{ x.clothing_name + ' ' + (genders[x.is_for_women]) }}</th>
              </tr>
              </thead>
            <tbody>
              <tr ng-repeat="x in dataset_measurement_types">
                <td>{{ x.measurement_type_name }}</td>
                <td ng-repeat="y in dataset_clothing_men">{{ dataset_measurement_matrices[y.clothing_id][x.measurement_type_id] }}</td>
                <td ng-repeat="y in dataset_clothing_women">{{ dataset_measurement_matrices[y.clothing_id][x.measurement_type_id] }}</td>
              </tr>
            </tbody>
  				</table>
        </div>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
