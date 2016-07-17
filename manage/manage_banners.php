<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getallbanners", data)
      .then(function (response) {
        $scope.dataset_banners = response.data.results;
      });
  });

  function bannerUploaded(that) {
  	generateImagePreview(that);

    var filename = $(that).val();
    var lastIndex = filename.lastIndexOf("\\");
    if (lastIndex >= 0) {
        filename = filename.substring(lastIndex + 1);
    }
    filename = filename.toLowerCase();
    filename = filename.replace(/[^a-zA-Z0-9_]/g,'-');
    filename = filename.slice(0, -4);
    var bannerImageName = "banner_"+filename+".jpg";
    $('#add_new_banner_image_name').val(bannerImageName);  	
  }

  function deleteRow(itemId) {
    var bannerIdString = itemId.slice(14);

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "banner_id": bannerIdString,
        "action_function": "void_banner",
        "callback_url": "manage_banners.php"
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
Manage Banners
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Add New Banner</span>
					<span class="caption-helper">use this form to add a banner to be displayed on the site home page</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="add_new_banner_form" class="form-horizontal" action="upload.php" method="post" enctype="multipart/form-data">
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Upload Image</label>
							<div class="col-md-4">
								<span class="btn btn-success fileinput-button">
								    <input type="file" name="fileToUpload" id="add_new_banner_upload_image" onchange="bannerUploaded(this)" accept=".jpg" required><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</input>
							    </span>
							</div>
						</div>
						<div id="add_new_banner_upload_image_group" class="form-group hide-group">
							<label class="col-md-3 control-label">Image Preview</label>
							<div class="col-md-4">
								<img id="add_new_banner_upload_image_preview" src="" alt="Please Select an Image File" class="backinputimg" />
							</div>
						</div>
						<div class="form-group last">
							<label class="col-md-3 control-label">Image Name</label>
							<div class="col-md-4">
								<input type="text" id="add_new_banner_image_name" name="image_name" class="form-control" readonly />
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
				  <input type="hidden" name="folder" value="banner" />
				  <input type="hidden" name="action_function" value="add_banner" />
				  <input type="hidden" name="callback_url" value="manage_banners.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>

		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Banners being displayed on the site home page
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
							<th>Banner ID</th>
							<th>Banner Image Name</th>
							<th>Banner Image</th>
							<th>Delete</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat="x in dataset_banners" id="data_banner_{{ x.banner_id }}_row">
							<td>{{ x.banner_id }}</td>
							<td>{{ x.banner_image }}.jpg</td>
							<td><img src="../uploadedimages/banner/{{ x.banner_image }}.jpg" class="backtableimg" /></td>
							<td><button type="button" id="delete_banner_{{ x.banner_id }}" class="btn red" onclick="deleteRow(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete</button></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
