<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getallclothing", data)
      .then(function (response) {
        $scope.dataset_clothing_men = response.data.men;
        $scope.dataset_clothing_women = response.data.women;
        $scope.genders=["Male", "Female"];
      });
  });

  function newDressNameChange() {
  	// var dressName = $("#add_new_dress_name").val();
  	// var dressNameString = dressName.toLowerCase();
  	var genderChecked = $('input[name="add_new_dress_gender"]:checked', '#add_new_dress_form').val();
  	var genderCheckedString = "m"
  	if (genderChecked == 1){
  		genderCheckedString = "f"
  	}
  	var dressImageName = "dress_"+genderCheckedString+"_";
  	// dressImageName = dressImageName.replace(/[^a-zA-Z0-9_]/g,'-');
  	// dressImageName = dressImageName + ".jpg";

  	$("#add_new_dress_image_name").val(dressImageName);
  }

	function display_data(itemId)
	{
		var dressIdString = itemId.slice(13);
		$("#data_dress_"+dressIdString+"_row").hide();	
		$("#"+itemId+"_row").show();
	}

  function updateRow(itemId) {
    var dressIdString = itemId.slice(13);
    var updateNameString = $("#update_dress_name_"+dressIdString).val();
    var updatePriceString = $("#update_dress_price_"+dressIdString).val();

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "clothing_id": dressIdString,
        "clothing_name": updateNameString,
        "price": updatePriceString,
        "action_function": "update_clothing",
        "callback_url": "manage_dresses_bulk.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function deleteRow(itemId) {
  	var dressIdString = itemId.slice(13);

		$.ajax({
		  url: "uploadplain.php",
		  type: "POST",
		  contentType: "application/x-www-form-urlencoded",
		  data: $.param({
		  	"clothing_id": dressIdString,
		  	"action_function": "void_clothing",
		  	"callback_url": "manage_dresses_bulk.php"
		  }),
		}).done(function (data, status, jqXHR) {
			location.reload();
		}).fail(function (jqXHR, status, err) {
			console.log(err);
		}).always(function() {

		})
	}

  function makeFileList() {
    var input = document.getElementById("add_new_dress_upload_image");
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
    newDressNameChange();
  }

</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Manage Dresses
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Add New Dress</span>
					<span class="caption-helper">use this form to add a new dress type</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="add_new_dress_form" class="form-horizontal" action="uploadbulk.php" method="post" enctype="multipart/form-data">
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Gender</label>
							<div class="col-md-4">
								<div class="radio-list">
									<label class="radio-inline">
									<input type="radio" name="add_new_dress_gender" id="add_new_dress_gender_0" value="0" onchange="newDressNameChange()" checked > For Men </label>
									<label class="radio-inline">
									<input type="radio" name="add_new_dress_gender" id="add_new_dress_gender_1" value="1" onchange="newDressNameChange()"> For Women </label>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Price</label>
							<div class="col-md-4">
								<input type="text" id="add_new_dress_price" name="add_new_dress_price" class="form-control"/>
							</div>
						</div>
						<div class="form-group">
						<label class="col-md-3 control-label">Upload Image</label>
							<div class="col-md-4">
								<span class="btn btn-success fileinput-button">
								    <input type="file" name="filesToUpload[]" id="add_new_dress_upload_image" onchange="makeFileList()" accept=".jpg" multiple="" required><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</input>
							    </span>
							</div>
						</div>
						<div id="add_new_dress_upload_image_group" class="form-group">
							<label class="col-md-3 control-label">Preview</label>
							<div class="col-md-4">
				                <ul id="fileList"><li>No Files Selected</li></ul>
								<!-- <img id="add_new_dress_upload_image_preview" src="" alt="Please Select an Image File" class="backinputimg" /> -->
							</div>
						</div>
						<div class="form-group last">
							<label class="col-md-3 control-label">Base Name</label>
							<div class="col-md-4">
								<input type="text" id="add_new_dress_image_name" name="image_name" class="form-control" readonly />
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
				  <input type="hidden" name="folder" value="dress" />
				  <input type="hidden" name="action_function" value="add_clothing" />
				  <input type="hidden" name="callback_url" value="manage_dresses_bulk.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Dresses
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
					<a href="javascript:;" class="reload">
					</a>
				</div>
			</div>
			<div class="portlet-body table-scrollable">
				<table id="dress_table" class="table table-bordered table-striped table-condensed flip-content backtable-centered">
					<thead>
					<tr>
						<th>Dress ID</th>
						<th>Dress Name</th>
						<th>Dress Gender</th>
						<th>Dress Image</th>
						<th>Dress Price</th>
						<th>Edit</th>
						<th>Delete</th>
					</tr>
					</thead>
					<tbody>
						<tr ng-repeat-start="x in dataset_clothing_men" id="data_dress_{{ x.clothing_id }}_row">
							<td>{{ x.clothing_id }}</td>
							<td>{{ x.clothing_name }}</td>
							<td>{{ genders[x.is_for_women] }}</td>
							<td><img src="../uploadedimages/dress/{{ x.clothing_image }}.jpg" class="backtableimg" /></td>
							<td>{{ x.price }}</td>
							<td><button type="button" id="update_dress_{{ x.clothing_id }}" class="btn red" onclick="display_data(this.id)"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit</button></td>
							<td><button type="button" id="delete_dress_{{ x.clothing_id }}" class="btn red" onclick="deleteRow(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete</button></td>
						</tr>
						<tr ng-repeat-end id="update_dress_{{ x.clothing_id }}_row" style="display:none">
					    <td>{{ x.clothing_id }}</td>
							<td><input type="text" id="update_dress_name_{{ x.clothing_id }}" name="clothing_name" value="{{ x.clothing_name }}"/></td>
							<td>{{ genders[x.is_for_women] }}</td>
							<td><img src="../uploadedimages/dress/{{ x.clothing_image }}.jpg" class="backtableimg" /></td>
							<td><input type="number" id="update_dress_price_{{ x.clothing_id }}" name="price" value="{{ x.price }}"/></td>
							<td><button type="button" id="update_dress_{{ x.clothing_id }}" class="btn red" onclick="updateRow(this.id)"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Save</button></td>
							<td></td>
						</tr>
						
						<tr ng-repeat-start="x in dataset_clothing_women" id="data_dress_{{ x.clothing_id }}_row">
							<td>{{ x.clothing_id }}</td>
							<td>{{ x.clothing_name }}</td>
							<td>{{ genders[x.is_for_women] }}</td>
							<td><img src="../uploadedimages/dress/{{ x.clothing_image }}.jpg" class="backtableimg" /></td>
							<td>{{ x.price }}</td>
							<td><button type="button" id="update_dress_{{ x.clothing_id }}" class="btn red" onclick="display_data(this.id)"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit</button></td>
							<td><button type="button" id="delete_dress_{{ x.clothing_id }}" class="btn red" onclick="deleteRow(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete</button></td>
						</tr>
						<tr ng-repeat-end id="update_dress_{{ x.clothing_id }}_row" style="display:none">
					    <td>{{ x.clothing_id }}</td>
	    	      <td><input type="text" id="update_dress_name_{{ x.clothing_id }}" name="clothing_name" value="{{ x.clothing_name }}"/></td>
							<td>{{ genders[x.is_for_women] }}</td>
							<td><img src="../uploadedimages/dress/{{ x.clothing_image }}.jpg" class="backtableimg" /></td>
      				<td><input type="number" id="update_dress_price_{{ x.clothing_id }}" name="price" value="{{ x.price }}"/></td>
      				<td><button type="button" id="update_dress_{{ x.clothing_id }}" class="btn red" onclick="updateRow(this.id)"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Save</button></td>
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
