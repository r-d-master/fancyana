<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getallalterationtypesandclothing", data)
      .then(function (response) {
        $scope.dataset_alteration_types = response.data.alteration_types;
        $scope.dataset_clothing_men = response.data.clothing_men;
        $scope.dataset_clothing_women = response.data.clothing_women;
        $scope.genders=["Male", "Female"];
      });
  });

  function newAlterationTypeNameChange() {
  	var alterationTypeTitle = $("#add_new_alteration_type_title").val();
  	var alterationTypeTitleString = alterationTypeTitle.toLowerCase();
  	var genderChecked = $('input[name="add_new_alteration_type_gender"]:checked', '#add_new_alteration_type_form').val();
  	var dressIdSelected = 0;
  	if (genderChecked == 0){
  		$("#add_new_alteration_type_for_men_group").show();
  		$("#add_new_alteration_type_for_women_group").hide();
  		dressIdSelected = $("#add_new_alteration_type_for_men").val();
  	} else {
  		$("#add_new_alteration_type_for_men_group").hide();
  		$("#add_new_alteration_type_for_women_group").show();
  		dressIdSelected = $("#add_new_alteration_type_for_women").val();
  	}
  	$("#add_new_alteration_type_clothing_id").val(dressIdSelected);
  }

  function display_data(itemId)
  {
    var alterationTypeIdString = itemId.slice(23);
    $("#data_alteration_type_"+alterationTypeIdString+"_row").hide(); 
    $("#"+itemId+"_row").show();
  }

  function updateRow(itemId) {
  	console.log(itemId);
    var alterationTypeIdString = itemId.slice(23);
    var updateTitleString = $("#update_alteration_type_title_"+alterationTypeIdString).val();
    var updatePriceString = $("#update_alteration_type_price_"+alterationTypeIdString).val();

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "alteration_type_id": alterationTypeIdString,
        "alteration_type_title": updateTitleString,
        "alteration_type_price": updatePriceString,
        "action_function": "update_alteration_type",
        "callback_url": "manage_alteration_types.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function deleteRow(itemId) {
    var alterationTypeIdString = itemId.slice(23);

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "alteration_type_id": alterationTypeIdString,
        "action_function": "void_alteration_type",
        "callback_url": "manage_alteration_types.php"
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
Manage Alteration Types
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Add New Alteration Type</span>
					<span class="caption-helper">use this form to add a new alteration type</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="add_new_alteration_type_form" class="form-horizontal" action="uploadplain.php" method="post" >
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Alteration Type Title</label>
							<div class="col-md-4">
								<input type="text" id="add_new_alteration_type_title" name="add_new_alteration_type_title" class="form-control" placeholder="Eg: Length Alter" oninput="newAlterationTypeNameChange()" required>
							</div>
						</div>
            <div class="form-group">
              <label class="col-md-3 control-label">Price</label>
              <div class="col-md-4">
                <input type="number" id="add_new_alteration_type_price" name="add_new_alteration_type_price" onchange="newAlterationTypeNameChange()" class="form-control" required/>
              </div>
            </div>
						<div class="form-group">
							<label class="col-md-3 control-label">Gender</label>
							<div class="col-md-4">
								<div class="radio-list">
									<label class="radio-inline">
									<input type="radio" name="add_new_alteration_type_gender" id="add_new_alteration_type_gender_0" value="0" onchange="newAlterationTypeNameChange()" checked > For Men </label>
									<label class="radio-inline">
									<input type="radio" name="add_new_alteration_type_gender" id="add_new_alteration_type_gender_1" value="1" onchange="newAlterationTypeNameChange()"> For Women </label>
								</div>
							</div>
						</div>
						<div id="add_new_alteration_type_for_men_group" class="form-group last">
							<label class="col-md-3 control-label">Dress</label>
							<div class="col-md-4">
								<select id="add_new_alteration_type_for_men" class="form-control input-medium" onchange="newAlterationTypeNameChange()">
									<option ng-repeat="y in dataset_clothing_men" value="{{ y.clothing_id }}">{{ y.clothing_name }}</option>
								</select>
							</div>
						</div>
						<div id="add_new_alteration_type_for_women_group" class="form-group last" style="display: none;">
							<label class="col-md-3 control-label">Dress</label>
							<div class="col-md-4">
								<select id="add_new_alteration_type_for_women" class="form-control input-medium" onchange="newAlterationTypeNameChange()">
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
					<input type="hidden" id="add_new_alteration_type_clothing_id" name="add_new_alteration_type_clothing_id" value="1" />
					<input type="hidden" name="action_function" value="add_alteration_type" />
					<input type="hidden" name="callback_url" value="manage_alteration_types.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Alteration Types are associated with a Dress
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
							<th>ID</th>
							<th>Alteration Type Title</th>
							<th>Price</th>
							<th>Clothing</th>
							<th>Gender</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat-start="x in dataset_alteration_types" id="data_alteration_type_{{ x.alteration_type_id }}_row">
							<td>{{ x.alteration_type_id }}</td>
							<td>{{ x.alteration_type_title }}</td>
							<td>{{ x.alteration_type_price }}</td>
							<td>{{ x.clothing_name }}</td>
							<td>{{ genders[x.is_for_women] }}</td>
							<td><button type="button" id="update_alteration_type_{{ x.alteration_type_id }}" class="btn red" onclick="display_data(this.id)"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit</button></td>
							<td><button type="button" id="delete_alteration_type_{{ x.alteration_type_id }}" class="btn red" onclick="deleteRow(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete</button></td>
						</tr>
						<tr ng-repeat-end id="update_alteration_type_{{ x.alteration_type_id }}_row" style="display:none">
							<td>{{ x.alteration_type_id }}</td>
	            <td><input type="text" id="update_alteration_type_title_{{ x.alteration_type_id }}" name="alteration_type_title" value="{{ x.alteration_type_title }}"/></td>
              <td><input type="number" id="update_alteration_type_price_{{ x.alteration_type_id }}" name="alteration_type_price" value="{{ x.alteration_type_price }}"/></td>	            
							<td>{{ x.clothing_name }}</td>
							<td>{{ genders[x.is_for_women] }}</td>
							<td><button type="button" id="update_alteration_type_{{ x.alteration_type_id }}" class="btn red" onclick="updateRow(this.id)"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Save</button></td>
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
