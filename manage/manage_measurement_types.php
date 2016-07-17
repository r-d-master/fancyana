<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getallmeasurementtypes", data)
      .then(function (response) {
        $scope.dataset_measurement_types = response.data.results;
      });
  });
</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Measurement Types
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse" style="display:none;">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Add New Measurement Type</span>
					<span class="caption-helper">use this form to add a new measurement type</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="add_new_measurement_type_form" class="form-horizontal" action="uploadplain.php" method="post">
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Measurement Type Name</label>
							<div class="col-md-4">
								<input type="text" id="add_new_measurement_type_name" name="add_new_measurement_type_name" class="form-control" placeholder="Eg: Waist">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Measurement Type Max</label>
							<div class="col-md-4">
								<input type="number" id="add_new_measurement_type_max" name="add_new_measurement_type_max" class="form-control" placeholder="Eg: 250">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Measurement Type Unit</label>
							<div class="col-md-4">
								<div class="radio-list">
									<label class="radio-inline">
									<input type="radio" name="add_new_measurement_type_unit" id="add_new_measurement_type_unit_0" value="cm" checked > cm </label>
									<label class="radio-inline">
									<input type="radio" name="add_new_measurement_type_unit" id="add_new_measurement_type_unit_1" value="kg"> kg </label>
								</div>
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
					<input type="hidden" name="action_function" value="add_measurement_type" />
					<input type="hidden" name="callback_url" value="manage_measurement_types.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Measurement Types for Measurement Sets
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
					<a href="javascript:;" class="reload">
					</a>
				</div>
			</div>
			<div class="portlet-body table-scrollable">
				<table class="table table-bordered table-striped table-condensed flip-content backtable-centered">
					<thead>
						<tr>
							<th>Measurement Type ID</th>
							<th>Measurement Type Name</th>
							<th>Measurement Type Max</th>
							<th>Measurement Type Unit</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat="x in dataset_measurement_types">
							<td>{{ x.measurement_type_id }}</td>
							<td>{{ x.measurement_type_name }}</td>
							<td>{{ x.measurement_type_max }}</td>
							<td>{{ x.measurement_type_unit }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
