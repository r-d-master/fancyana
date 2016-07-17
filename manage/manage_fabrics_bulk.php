<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getallfabricsandclothing", data)
      .then(function (response) {
        $scope.dataset_fabrics = response.data.fabrics;
        $scope.dataset_clothing_men = response.data.clothing_men;
        $scope.dataset_clothing_women = response.data.clothing_women;
        $scope.genders=["Male", "Female"];
      });
  });

  function newFabricNameChange() {
  	// var fabricName = $("#add_new_fabric_name").val();
  	// var fabricNameString = fabricName.toLowerCase();
  	var genderChecked = $('input[name="add_new_fabric_gender"]:checked', '#add_new_fabric_form').val();
  	var dressIdSelected = 0
  	if (genderChecked == 0){
  		$("#add_new_fabric_for_men_group").show();
  		$("#add_new_fabric_for_women_group").hide();
  		dressIdSelected = $("#add_new_fabric_for_men").val()
  	} else {
  		$("#add_new_fabric_for_men_group").hide();
  		$("#add_new_fabric_for_women_group").show();
  		dressIdSelected = $("#add_new_fabric_for_women").val()
  	}
  	var fabricImageName = "fabric_"+dressIdSelected+"_";
  	// fabricImageName = fabricImageName.replace(/[^a-zA-Z0-9_]/g,'-');
  	// fabricImageName = fabricImageName + ".jpg";
  	$("#add_new_fabric_image_name").val(fabricImageName);
  	$("#add_new_fabric_clothing_id").val(dressIdSelected);
  }

  function display_data(itemId)
  {
    var fabricIdString = itemId.slice(14);
    $("#data_fabric_"+fabricIdString+"_row").hide(); 
    $("#"+itemId+"_row").show();
  }

  function updateRow(itemId) {
    var fabricIdString = itemId.slice(14);
    var updateNameString = $("#update_fabric_name_"+fabricIdString).val();
    var updatePriceString = $("#update_fabric_price_"+fabricIdString).val();

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "fabric_id": fabricIdString,
        "fabric_name": updateNameString,
        "fabric_price": updatePriceString,
        "action_function": "update_fabric",
        "callback_url": "manage_fabrics_bulk.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function deleteRow(itemId) {
    var fabricIdString = itemId.slice(14);

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "fabric_id": fabricIdString,
        "action_function": "void_fabric",
        "callback_url": "manage_fabrics_bulk.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function makeFileList() {
    var input = document.getElementById("add_new_fabric_upload_image");
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
    newFabricNameChange();
  }

</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Manage Fabrics
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Add New Fabric</span>
					<span class="caption-helper">use this form to add a new fabric</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="add_new_fabric_form" class="form-horizontal" action="uploadbulk.php" method="post" enctype="multipart/form-data">
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Gender</label>
							<div class="col-md-4">
								<div class="radio-list">
									<label class="radio-inline">
									<input type="radio" name="add_new_fabric_gender" id="add_new_fabric_gender_0" value="0" onchange="newFabricNameChange()" checked > For Men </label>
									<label class="radio-inline">
									<input type="radio" name="add_new_fabric_gender" id="add_new_fabric_gender_1" value="1" onchange="newFabricNameChange()"> For Women </label>
								</div>
							</div>
						</div>
						<div id="add_new_fabric_for_men_group" class="form-group">
							<label class="col-md-3 control-label">Dress</label>
							<div class="col-md-4">
								<select id="add_new_fabric_for_men" class="form-control input-medium" onchange="newFabricNameChange()">
									<option ng-repeat="y in dataset_clothing_men" value="{{ y.clothing_id }}">{{ y.clothing_name }}</option>
								</select>
							</div>
						</div>
						<div id="add_new_fabric_for_women_group" class="form-group" style="display: none;">
							<label class="col-md-3 control-label">Dress</label>
							<div class="col-md-4">
								<select id="add_new_fabric_for_women" class="form-control input-medium" onchange="newFabricNameChange()">
									<option ng-repeat="z in dataset_clothing_women" value="{{ z.clothing_id }}">{{ z.clothing_name }}</option>
								</select>
							</div>
						</div>
			            <div class="form-group">
			              <label class="col-md-3 control-label">Price</label>
			              <div class="col-md-4">
			                <input type="number" id="add_new_fabric_price" name="add_new_fabric_price" onchange="newFabricNameChange()" class="form-control" required/>
			              </div>
			            </div>
						<div class="form-group">
							<label class="col-md-3 control-label">Upload Image</label>
							<div class="col-md-4">
								<span class="btn btn-success fileinput-button">
								    <input type="file" name="filesToUpload[]" id="add_new_fabric_upload_image" onchange="makeFileList()" accept=".jpg" multiple="" required><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</input>
							    </span>
							</div>
						</div>
						<div id="add_new_fabric_upload_image_group" class="form-group">
							<label class="col-md-3 control-label">Preview</label>
							<div class="col-md-4">
				                <ul id="fileList"><li>No Files Selected</li></ul>
								<!-- <img id="add_new_fabric_upload_image_preview" src="" alt="Please Select an Image File" class="backinputimg" /> -->
							</div>
						</div>
						<div class="form-group last">
							<label class="col-md-3 control-label">Base Name</label>
							<div class="col-md-4">
								<input type="text" id="add_new_fabric_image_name" name="image_name" class="form-control" readonly />
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
				  <input type="hidden" id="add_new_fabric_clothing_id" name="add_new_fabric_clothing_id" value="1" />
				  <input type="hidden" name="folder" value="fabric" />
				  <input type="hidden" name="action_function" value="add_fabric" />
				  <input type="hidden" name="callback_url" value="manage_fabrics_bulk.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Fabrics linked to Dresses
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
							<th>Fabric ID</th>
							<th>Dress</th>
							<th>Gender</th>
							<th>Fabric Name</th>
							<th>Fabric Image</th>
				            <th>Price</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat-start="x in dataset_fabrics" id="data_fabric_{{ x.fabric_id }}_row">
							<td>{{ x.fabric_id }}</td>
							<td>{{ x.clothing_name }}</td>
							<td>{{ genders[x.is_for_women] }}</td>
							<td>{{ x.fabric_name }}</td>
							<td><img src="../uploadedimages/fabric/{{ x.fabric_image }}.jpg" class="backtableimg" /></td>
				            <td>{{ x.fabric_price }}</td>
							<td><button type="button" id="update_fabric_{{ x.fabric_id }}" class="btn red" onclick="display_data(this.id)"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit</button></td>
							<td><button type="button" id="delete_fabric_{{ x.fabric_id }}" class="btn red" onclick="deleteRow(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete</button></td>
						</tr>
						<tr ng-repeat-end id="update_fabric_{{ x.fabric_id }}_row" style="display:none">
				            <td>{{ x.fabric_id }}</td>
				            <td>{{ x.clothing_name }}</td>
				            <td>{{ genders[x.is_for_women] }}</td>
							<td><input type="text" id="update_fabric_name_{{ x.fabric_id }}" name="fabric_name" value="{{ x.fabric_name }}"/></td>
            				<td><img src="../uploadedimages/fabric/{{ x.fabric_image }}.jpg" class="backtableimg" /></td>
				            <td><input type="number" id="update_fabric_price_{{ x.fabric_id }}" name="fabric_price" value="{{ x.fabric_price }}"/></td>
							<td><button type="button" id="update_fabric_{{ x.fabric_id }}" class="btn red" onclick="updateRow(this.id)"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Save</button></td>
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
