<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getallstatesandcountries", data)
      .then(function (response) {
        $scope.dataset_location_states = response.data.states;
        $scope.dataset_location_countries = response.data.countries;
        console.log(response.data.countries)
      });
  });

  function display_data(itemId)
  {
    var stateIdString = itemId.slice(13);
    $("#data_state_"+stateIdString+"_row").hide(); 
    $("#"+itemId+"_row").show();
  }

  function updateRow(itemId) {
    var stateIdString = itemId.slice(13);
    var updateNameString = $("#update_state_name_"+stateIdString).val();

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "state_id": stateIdString,
        "state_name": updateNameString,
        "action_function": "update_state",
        "callback_url": "manage_location_states.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function deleteRow(itemId) {
    var stateIdString = itemId.slice(13);

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "state_id": stateIdString,
        "action_function": "void_state",
        "callback_url": "manage_location_states.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Manage States
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Add New State</span>
					<span class="caption-helper">use this form to add a new state</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="add_new_state_form" class="form-horizontal" action="uploadplain.php" method="post">
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">State Name</label>
							<div class="col-md-4">
								<input type="text" id="add_new_state_name" name="add_new_state_name" class="form-control" placeholder="Eg: Punjab" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Country</label>
							<div class="col-md-4">
								<select class="form-control input-medium" name="add_new_state_country_id">
									<option ng-repeat="y in dataset_location_countries" value="{{ y.country_id }}">{{ y.country_name }}</option>
								</select>
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
					<input type="hidden" name="action_function" value="add_state" />
					<input type="hidden" name="callback_url" value="manage_location_states.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>States for Address
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
							<th>State ID</th>
							<th>State Name</th>
							<th>Country</th>
              <th>Edit</th>
              <th>Delete</th>
						</tr>
						</thead>
					<tbody>
            <tr ng-repeat-start="x in dataset_location_states" id="data_state_{{ x.state_id }}_row">
              <td>{{ x.state_id }}</td>
              <td>{{ x.state_name }}</td>
              <td>{{ x.country_name }}</td>
              <td><button type="button" id="update_state_{{ x.state_id }}" class="btn red" onclick="display_data(this.id)"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit</button></td>
              <td><button type="button" id="delete_state_{{ x.state_id }}" class="btn red" onclick="deleteRow(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete</button></td>
            </tr>
            <tr ng-repeat-end id="update_state_{{ x.state_id }}_row" style="display:none">
              <td>{{ x.state_id }}</td>
              <td><input type="text" id="update_state_name_{{ x.state_id }}" name="state_name" value="{{ x.state_name }}"/></td>
              <td>{{ x.country_name }}</td>
              <td><button type="button" id="update_state_{{ x.state_id }}" class="btn red" onclick="updateRow(this.id)"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Save</button></td>
              <td></td>
            </tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
