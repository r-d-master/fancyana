<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getalldesigngroupsandclothing", data)
      .then(function (response) {
        $scope.dataset_design_groups = response.data.design_groups;
        $scope.dataset_clothing_men = response.data.clothing_men;
        $scope.dataset_clothing_women = response.data.clothing_women;
        $scope.genders=["Male", "Female"];
      });
  });

  function newDesignGroupNameChange() {
  	var designGroupName = $("#add_new_design_group_name").val();
  	var designGroupNameString = designGroupName.toLowerCase();
  	var genderChecked = $('input[name="add_new_design_group_gender"]:checked', '#add_new_design_group_form').val();
  	var dressIdSelected = 0;
  	if (genderChecked == 0){
  		$("#add_new_design_group_for_men_group").show();
  		$("#add_new_design_group_for_women_group").hide();
  		dressIdSelected = $("#add_new_design_group_for_men").val();
  	} else {
  		$("#add_new_design_group_for_men_group").hide();
  		$("#add_new_design_group_for_women_group").show();
  		dressIdSelected = $("#add_new_design_group_for_women").val();
  	}
  	$("#add_new_design_group_clothing_id").val(dressIdSelected);
  }

  function display_data(itemId)
  {
    var designGroupIdString = itemId.slice(20);
    $("#data_design_group_"+designGroupIdString+"_row").hide(); 
    $("#"+itemId+"_row").show();
  }

  function updateRow(itemId) {
    var designGroupIdString = itemId.slice(20);
    var updateNameString = $("#update_design_group_name_"+designGroupIdString).val();

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "design_group_id": designGroupIdString,
        "design_group_name": updateNameString,
        "action_function": "update_design_group",
        "callback_url": "manage_design_groups.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function deleteRow(itemId) {
    var designGroupIdString = itemId.slice(20);

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "design_group_id": designGroupIdString,
        "action_function": "void_design_group",
        "callback_url": "manage_design_groups.php"
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
Manage Design Groups
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Add New Design Group</span>
					<span class="caption-helper">use this form to add a new design group</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="add_new_design_group_form" class="form-horizontal" action="uploadplain.php" method="post" >
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Design Group Name</label>
							<div class="col-md-4">
								<input type="text" id="add_new_design_group_name" name="add_new_design_group_name" class="form-control" placeholder="Eg: Collar" oninput="newDesignGroupNameChange()" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Gender</label>
							<div class="col-md-4">
								<div class="radio-list">
									<label class="radio-inline">
									<input type="radio" name="add_new_design_group_gender" id="add_new_design_group_gender_0" value="0" onchange="newDesignGroupNameChange()" checked > For Men </label>
									<label class="radio-inline">
									<input type="radio" name="add_new_design_group_gender" id="add_new_design_group_gender_1" value="1" onchange="newDesignGroupNameChange()"> For Women </label>
								</div>
							</div>
						</div>
						<div id="add_new_design_group_for_men_group" class="form-group last">
							<label class="col-md-3 control-label">Dress</label>
							<div class="col-md-4">
								<select id="add_new_design_group_for_men" class="form-control input-medium" onchange="newDesignGroupNameChange()">
									<option ng-repeat="y in dataset_clothing_men" value="{{ y.clothing_id }}">{{ y.clothing_name }}</option>
								</select>
							</div>
						</div>
						<div id="add_new_design_group_for_women_group" class="form-group last" style="display: none;">
							<label class="col-md-3 control-label">Dress</label>
							<div class="col-md-4">
								<select id="add_new_design_group_for_women" class="form-control input-medium" onchange="newDesignGroupNameChange()">
									<option ng-repeat="z in dataset_clothing_women" value="{{ z.clothing_id }}">{{ z.clothing_name }}</option>
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
					<input type="hidden" id="add_new_design_group_clothing_id" name="add_new_design_group_clothing_id" value="1" />
					<input type="hidden" name="action_function" value="add_design_group" />
					<input type="hidden" name="callback_url" value="manage_design_groups.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Designs Groups are sets of Designs associated with a Dress
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
							<th>Design Group ID</th>
							<th>Design Group Name</th>
							<th>Clothing</th>
							<th>Gender</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat-start="x in dataset_design_groups" id="data_design_group_{{ x.design_group_id }}_row">
							<td>{{ x.design_group_id }}</td>
							<td>{{ x.design_group_name }}</td>
							<td>{{ x.clothing_name }}</td>
							<td>{{ genders[x.is_for_women] }}</td>
							<td><button type="button" id="update_design_group_{{ x.design_group_id }}" class="btn red" onclick="display_data(this.id)"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit</button></td>
							<td><button type="button" id="delete_design_group_{{ x.design_group_id }}" class="btn red" onclick="deleteRow(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete</button></td>
						</tr>
						<tr ng-repeat-end id="update_design_group_{{ x.design_group_id }}_row" style="display:none">
							<td>{{ x.design_group_id }}</td>
				            <td><input type="text" id="update_design_group_name_{{ x.design_group_id }}" name="design_group_name" value="{{ x.design_group_name }}"/></td>
							<td>{{ x.clothing_name }}</td>
							<td>{{ genders[x.is_for_women] }}</td>
							<td><button type="button" id="update_design_group_{{ x.design_group_id }}" class="btn red" onclick="updateRow(this.id)"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Save</button></td>
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
