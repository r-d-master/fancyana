<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getallvendors", data)
      .then(function (response) {
        $scope.dataset_vendors = response.data.results;
      });
  });
	backdashDataApp.directive('unveil', function () {
	  return {
	    link: function (scope, element, attrs) {
			setTimeout(function(){
				$(element).unveil(200);
			}, 1000);
	    }
	  };
	});

  function newVendorNameChange() {
  	var vendorName = $("#add_new_vendor_name").val();
  	var vendorNameString = vendorName.toLowerCase();
  	var vendorImageName = "vendor_"+vendorNameString;
  	vendorImageName = vendorImageName.replace(/[^a-zA-Z0-9_]/g,'-');
  	vendorImageName = vendorImageName + ".jpg";

  	$("#add_new_vendor_image_name").val(vendorImageName);
  }

  function display_data(itemId)
  {
    var vendorIdString = itemId.slice(14);
    $("#data_vendor_"+vendorIdString+"_row").hide(); 
    $("#"+itemId+"_row").show();
  }

  function updateRow(itemId) {
    var vendorIdString = itemId.slice(14);
    var updateNameString = $("#update_vendor_name_"+vendorIdString).val();
    var updateURLString = $("#update_vendor_url_"+vendorIdString).val();

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "vendor_id": vendorIdString,
        "vendor_name": updateNameString,
        "vendor_url": updateURLString,
        "action_function": "update_vendor",
        "callback_url": "manage_vendors.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function deleteRow(itemId) {
    var vendorIdString = itemId.slice(14);

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "vendor_id": vendorIdString,
        "action_function": "void_vendor",
        "callback_url": "manage_vendors.php"
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
Manage Vendors
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Add New Vendor</span>
					<span class="caption-helper">use this form to add a vendor for online fabric purchase</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="add_new_dress_form" class="form-horizontal" action="upload.php" method="post" enctype="multipart/form-data">
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Vendor Name</label>
							<div class="col-md-4">
								<input type="text" id="add_new_vendor_name" name="add_new_vendor_name" class="form-control" placeholder="Eg: Amazon" oninput="newVendorNameChange()" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Website URL</label>
							<div class="col-md-4">
								<input type="text" id="add_new_vendor_url" name="add_new_vendor_url" class="form-control" placeholder="Eg: amazon.in" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Upload Image</label>
							<div class="col-md-4">
								<span class="btn btn-success fileinput-button">
								    <input type="file" name="fileToUpload" id="add_new_vendor_upload_image" onchange="generateImagePreview(this)" accept=".jpg" required><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</input>
							    </span>
							</div>
						</div>
						<div id="add_new_vendor_upload_image_group" class="form-group hide-group">
							<label class="col-md-3 control-label">Image Preview</label>
							<div class="col-md-4">
								<img id="add_new_vendor_upload_image_preview" src="" alt="Please Select an Image File" class="backinputimg" />
							</div>
						</div>
						<div class="form-group last">
							<label class="col-md-3 control-label">Image Name</label>
							<div class="col-md-4">
								<input type="text" id="add_new_vendor_image_name" name="image_name" class="form-control" readonly />
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
				  <input type="hidden" name="folder" value="vendor" />
				  <input type="hidden" name="action_function" value="add_vendor" />
				  <input type="hidden" name="callback_url" value="manage_vendors.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>

		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Vendors for Online Fabric Purchase
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
							<th>Vendor ID</th>
							<th>Vendor Name</th>
							<th>Vendor URL</th>
							<th>Vendor Image</th>
				            <th>Edit</th>
							<th>Delete</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat-start="x in dataset_vendors" id="data_vendor_{{ x.vendor_id }}_row">
							<td>{{ x.vendor_id }}</td>
							<td>{{ x.vendor_name }}</td>
							<td>{{ x.vendor_url }}</td>
							<td><img unveil lazy-src="../uploadedimages/vendor/{{ x.vendor_image }}.jpg" data-src="../uploadedimages/vendor/{{ x.vendor_image }}.jpg" class="backtableimg" /></td>
			                <td><button type="button" id="update_vendor_{{ x.vendor_id }}" class="btn red" onclick="display_data(this.id)"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit</button></td>
							<td><button type="button" id="delete_vendor_{{ x.vendor_id }}" class="btn red" onclick="deleteRow(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete</button></td>
						</tr>
			            <tr ng-repeat-end id="update_vendor_{{ x.vendor_id }}_row" style="display:none;">
			              <td>{{ x.vendor_id }}</td>
			              <td><input type="text" id="update_vendor_name_{{ x.vendor_id }}" name="vendor_name" value="{{ x.vendor_name }}"/></td>
			              <td><input type="text" id="update_vendor_url_{{ x.vendor_id }}" name="vendor_url" value="{{ x.vendor_url }}"/></td>
			              <td><img unveil lazy-src="../uploadedimages/vendor/{{ x.vendor_image }}.jpg" data-src="../uploadedimages/vendor/{{ x.vendor_image }}.jpg" class="backtableimg" /></td>
			              <td><button type="button" id="update_vendor_{{ x.vendor_id }}" class="btn red" onclick="updateRow(this.id)"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Save</button></td>
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
