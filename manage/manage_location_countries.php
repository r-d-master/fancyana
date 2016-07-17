<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getallcountries", data)
      .then(function (response) {
        $scope.dataset_location_countries = response.data.results;
      });
  });

  function display_data(itemId)
  {
    var countryIdString = itemId.slice(15);
    $("#data_country_"+countryIdString+"_row").hide(); 
    $("#"+itemId+"_row").show();
  }

  function updateRow(itemId) {
    var countryIdString = itemId.slice(15);
    var updateNameString = $("#update_country_name_"+countryIdString).val();

    console.log(countryIdString)
    console.log(updateNameString)

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "country_id": countryIdString,
        "country_name": updateNameString,
        "action_function": "update_country",
        "callback_url": "manage_location_countries.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function deleteRow(itemId) {
    var countryIdString = itemId.slice(15);

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "country_id": countryIdString,
        "action_function": "void_country",
        "callback_url": "manage_location_countries.php"
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
Manage Countries
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Add New Country</span>
					<span class="caption-helper">use this form to add a new state</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="add_new_country_form" class="form-horizontal" action="uploadplain.php" method="post">
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Country Name</label>
							<div class="col-md-4">
								<input type="text" id="add_new_country_name" name="add_new_country_name" class="form-control" placeholder="Eg: Sri Lanka" required>
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
					<input type="hidden" name="action_function" value="add_country" />
					<input type="hidden" name="callback_url" value="manage_location_countries.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Countries for Address
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
							<th>Country ID</th>
							<th>Country Name</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
						</thead>
					<tbody>
            <tr ng-repeat-start="x in dataset_location_countries" id="data_country_{{ x.country_id }}_row">
              <td>{{ x.country_id }}</td>
              <td>{{ x.country_name }}</td>
              <td><button type="button" id="update_country_{{ x.country_id }}" class="btn red" onclick="display_data(this.id)"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit</button></td>
              <td><button type="button" id="delete_country_{{ x.country_id }}" class="btn red" onclick="deleteRow(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete</button></td>
            </tr>
            <tr ng-repeat-end id="update_country_{{ x.country_id }}_row" style="display:none">
              <td>{{ x.country_id }}</td>
              <td><input type="text" id="update_country_name_{{ x.country_id }}" name="country_name" value="{{ x.country_name }}"/></td>
              <td><button type="button" id="update_country_{{ x.country_id }}" class="btn red" onclick="updateRow(this.id)"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Save</button></td>
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
