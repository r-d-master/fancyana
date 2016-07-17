<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getallstatustypes", data)
      .then(function (response) {
        $scope.dataset_status_types = response.data.results;
      });
  });

  function display_data(itemId)
  {
    var statusTypeIdString = itemId.slice(19);
    $("#data_status_type_"+statusTypeIdString+"_row").hide(); 
    $("#"+itemId+"_row").show();
  }

  function updateRow(itemId) {
    var statusTypeIdString = itemId.slice(19);
    var updateTextString = $("#update_status_type_text_"+statusTypeIdString).val();

    console.log(statusTypeIdString)
    console.log(updateTextString)

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "status_text_id": statusTypeIdString,
        "status_text": updateTextString,
        "action_function": "update_status_type",
        "callback_url": "manage_status_types.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function deleteRow(itemId) {
    var statusTypeIdString = itemId.slice(19);

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "status_text_id": statusTypeIdString,
        "action_function": "void_status_type",
        "callback_url": "manage_status_types.php"
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
Manage Status Types
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Add New Status Type</span>
					<span class="caption-helper">use this form to add a new status type</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="add_new_status_type_form" class="form-horizontal" action="uploadplain.php" method="post">
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Status Type</label>
							<div class="col-md-4">
								<input type="text" id="add_new_status_type_text" name="add_new_status_type_text" class="form-control" placeholder="Eg: Delivered">
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
					<input type="hidden" name="action_function" value="add_status_type" />
					<input type="hidden" name="callback_url" value="manage_status_types.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Status Types For Order Processing
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
							<th>Status ID</th>
							<th>Status Type</th>
							<th>Edit</th>
							<!-- <th>Delete</th> -->
						</tr>
						</thead>
					<tbody>
            <tr ng-repeat-start="x in dataset_status_types" id="data_status_type_{{ x.status_text_id }}_row">
              <td>{{ x.status_text_id }}</td>
              <td>{{ x.status_text }}</td>
              <td><button type="button" id="update_status_type_{{ x.status_text_id }}" class="btn red" onclick="display_data(this.id)"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit</button></td>
              <!-- <td><button type="button" id="delete_status_type_{{ x.status_text_id }}" class="btn red" onclick="deleteRow(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete</button></td> -->
            </tr>
            <tr ng-repeat-end id="update_status_type_{{ x.status_text_id }}_row" style="display:none">
              <td>{{ x.status_text_id }}</td>
              <td><input type="text" id="update_status_type_text_{{ x.status_text_id }}" name="status_text" value="{{ x.status_text }}"/></td>
              <td><button type="button" id="update_status_type_{{ x.status_text_id }}" class="btn red" onclick="updateRow(this.id)"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Save</button></td>
              <!-- <td></td> -->
            </tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
