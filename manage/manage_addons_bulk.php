<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getalladdonsandclothing", data)
      .then(function (response) {
        $scope.dataset_addons = response.data.addons;
        $scope.dataset_clothing_men = response.data.clothing_men;
        $scope.dataset_clothing_women = response.data.clothing_women;
        $scope.genders=["Male", "Female"];
      });
  });

  function newAddonNameChange() {
  	// var addonName = $("#add_new_addon_name").val();
  	// var addonNameString = addonName.toLowerCase();
  	var genderChecked = $('input[name="add_new_addon_gender"]:checked', '#add_new_addon_form').val();
  	var dressIdSelected = 0
  	if (genderChecked == 0){
  		$("#add_new_addon_for_men_group").show();
  		$("#add_new_addon_for_women_group").hide();
  		dressIdSelected = $("#add_new_addon_for_men").val()
  	} else {
  		$("#add_new_addon_for_men_group").hide();
  		$("#add_new_addon_for_women_group").show();
  		dressIdSelected = $("#add_new_addon_for_women").val()
  	}
  	var addonImageName = "addon_"+dressIdSelected+"_";
  	// addonImageName = addonImageName.replace(/[^a-zA-Z0-9_]/g,'-');
  	// addonImageName = addonImageName + ".jpg";
  	$("#add_new_addon_image_name").val(addonImageName);
  	$("#add_new_addon_clothing_id").val(dressIdSelected);
  }

  function display_data(itemId)
  {
    var addonIdString = itemId.slice(13);
    $("#data_addon_"+addonIdString+"_row").hide(); 
    $("#"+itemId+"_row").show();
  }

  function updateRow(itemId) {
    var addonIdString = itemId.slice(13);
    var updateNameString = $("#update_addon_name_"+addonIdString).val();
    var updatePriceString = $("#update_addon_price_"+addonIdString).val();

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "addon_id": addonIdString,
        "addon_name": updateNameString,
        "addon_price": updatePriceString,
        "action_function": "update_addon",
        "callback_url": "manage_addons_bulk.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function deleteRow(itemId) {
    var addonIdString = itemId.slice(13);

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "addon_id": addonIdString,
        "action_function": "void_addon",
        "callback_url": "manage_addons_bulk.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function makeFileList() {
    var input = document.getElementById("add_new_addon_upload_image");
    var ul = document.getElementById("fileList");
    while (ul.hasChildNodes()) {
      ul.removeChild(ul.firstChild);
    }
    for (var i = 0; i < input.files.length; i++) {
      var li = document.createElement("li");
      li.innerHTML = input.files[i].name;
      ul.appendChild(li);
    }
    if(!ul.hasChildNodes()) {
      var li = document.createElement("li");
      li.innerHTML = 'No Files Selected';
      ul.appendChild(li);
    }
    newAddonNameChange();
  }

</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Manage Addons
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Add New Addon</span>
					<span class="caption-helper">use this form to add a new addon</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="add_new_addon_form" class="form-horizontal" action="uploadbulk.php" method="post" enctype="multipart/form-data">
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Gender</label>
							<div class="col-md-4">
								<div class="radio-list">
									<label class="radio-inline">
									<input type="radio" name="add_new_addon_gender" id="add_new_addon_gender_0" value="0" onchange="newAddonNameChange()" checked > For Men </label>
									<label class="radio-inline">
									<input type="radio" name="add_new_addon_gender" id="add_new_addon_gender_1" value="1" onchange="newAddonNameChange()"> For Women </label>
								</div>
							</div>
						</div>
						<div id="add_new_addon_for_men_group" class="form-group">
							<label class="col-md-3 control-label">Dress</label>
							<div class="col-md-4">
								<select id="add_new_addon_for_men" class="form-control input-medium" onchange="newAddonNameChange()">
									<option ng-repeat="y in dataset_clothing_men" value="{{ y.clothing_id }}">{{ y.clothing_name }}</option>
								</select>
							</div>
						</div>
						<div id="add_new_addon_for_women_group" class="form-group" style="display: none;">
							<label class="col-md-3 control-label">Dress</label>
							<div class="col-md-4">
								<select id="add_new_addon_for_women" class="form-control input-medium" onchange="newAddonNameChange()">
									<option ng-repeat="z in dataset_clothing_women" value="{{ z.clothing_id }}">{{ z.clothing_name }}</option>
								</select>
							</div>
						</div>
            <div class="form-group">
              <label class="col-md-3 control-label">Price</label>
              <div class="col-md-4">
                <input type="number" id="add_new_addon_price" name="add_new_addon_price" onchange="newAddonNameChange()" class="form-control" required/>
              </div>
            </div>
						<div class="form-group">
							<label class="col-md-3 control-label">Upload Image</label>
							<div class="col-md-4">
								<span class="btn btn-success fileinput-button">
								    <input type="file" name="filesToUpload[]" id="add_new_addon_upload_image" onchange="makeFileList()" accept=".jpg" multiple="" required><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</input>
							    </span>
							</div>
						</div>
						<div id="add_new_addon_upload_image_group" class="form-group">
							<label class="col-md-3 control-label">Preview</label>
							<div class="col-md-4">
				                <ul id="fileList"><li>No Files Selected</li></ul>
								<!-- <img id="add_new_addon_upload_image_preview" src="" alt="Please Select an Image File" class="backinputimg" /> -->
							</div>
						</div>
						<div class="form-group last">
							<label class="col-md-3 control-label">Base Name</label>
							<div class="col-md-4">
								<input type="text" id="add_new_addon_image_name" name="image_name" class="form-control" readonly />
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
				  <input type="hidden" id="add_new_addon_clothing_id" name="add_new_addon_clothing_id" value="1" />
				  <input type="hidden" name="folder" value="addon" />
				  <input type="hidden" name="action_function" value="add_addon" />
				  <input type="hidden" name="callback_url" value="manage_addons_bulk.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Addons linked to Dresses
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
					<a href="javascript:;" class="reload">
					</a>
				</div>
			</div>
			<div class="portlet-body table-scrollable">
				<table id="addon_table" class="table table-bordered table-striped table-condensed flip-content backtable-centered">
					<thead>
						<tr>
							<th>Addon ID</th>
							<th>Dress</th>
							<th>Gender</th>
							<th>Addon Name</th>
              <th>Addon Image</th>
              <th>Price</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat-start="x in dataset_addons" id="data_addon_{{ x.addon_id }}_row">
							<td>{{ x.addon_id }}</td>
							<td>{{ x.clothing_name }}</td>
							<td>{{ genders[x.is_for_women] }}</td>
							<td>{{ x.addon_name }}</td>
							<td><img src="../uploadedimages/addon/{{ x.addon_image }}.jpg" class="backtableimg" /></td>
              <td>{{ x.addon_price }}</td>
							<td><button type="button" id="update_addon_{{ x.addon_id }}" class="btn red" onclick="display_data(this.id)"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit</button></td>
							<td><button type="button" id="delete_addon_{{ x.addon_id }}" class="btn red" onclick="deleteRow(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete</button></td>
						</tr>
						<tr ng-repeat-end id="update_addon_{{ x.addon_id }}_row" style="display:none">
              <td>{{ x.addon_id }}</td>
              <td>{{ x.clothing_name }}</td>
              <td>{{ genders[x.is_for_women] }}</td>
							<td><input type="text" id="update_addon_name_{{ x.addon_id }}" name="addon_name" value="{{ x.addon_name }}"/></td>
              <td><img src="../uploadedimages/addon/{{ x.addon_image }}.jpg" class="backtableimg" /></td>
              <td><input type="number" id="update_addon_price_{{ x.addon_id }}" name="addon_price" value="{{ x.addon_price }}"/></td>
							<td><button type="button" id="update_addon_{{ x.addon_id }}" class="btn red" onclick="updateRow(this.id)"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Save</button></td>
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
