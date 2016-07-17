<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getalldesignsanddesigngroupsandclothing", data)
      .then(function (response) {
        $scope.dataset_designs = response.data.designs;
        $scope.dataset_design_groups = response.data.design_groups;
        $scope.dataset_clothing_men = response.data.clothing_men;
        $scope.dataset_clothing_women = response.data.clothing_women;
        $scope.genders=["Male", "Female"];
        $scope.isEqualTo = function(prop, val){
          return function(item){
            if (item[prop] == val) return true;
          }
        }

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

  function newDesignNameChange() {
  	// var designName = "randomname";
  	// var designNameString = designName.toLowerCase();
    var designNames = [];
    for (x in document.getElementById("add_new_design_upload_image").files) {
      var filename = document.getElementById("add_new_design_upload_image").files[x].name;
      if(!!filename && filename!="item"){
        designNames.push(document.getElementById("add_new_design_upload_image").files[x].name)
      }
    }
  	var genderChecked = $('input[name="add_new_design_gender"]:checked', '#add_new_design_form').val();
  	if (genderChecked == 0){
  		$("#add_new_design_for_men_group").show();
  		$("#add_new_design_for_women_group").hide();
  		dressIdSelected = $("#add_new_design_for_women").val()
  		$("#add_new_design_design_group_men_group").show();
  		$("#add_new_design_design_group_women_group").hide();
  		designGroupIdSelected = $("#add_new_design_design_group_men").val()
  	} else {
  		$("#add_new_design_for_men_group").hide();
  		$("#add_new_design_for_women_group").show();
  		dressIdSelected = $("#add_new_design_for_women").val()
  		$("#add_new_design_design_group_men_group").hide();
  		$("#add_new_design_design_group_women_group").show();
  		designGroupIdSelected = $("#add_new_design_design_group_women").val()
  	}
  	var designImageName = "design_"+designGroupIdSelected+"_";

  	$("#add_new_design_image_name").val(designImageName);
  	$("#add_new_design_design_group_id").val(designGroupIdSelected);
  }

  function display_data(itemId)
  {
    var designIdString = itemId.slice(14);
    $("#data_design_"+designIdString+"_row").hide(); 
    $("#"+itemId+"_row").show();
  }

  function updateRow(itemId) {
    var designIdString = itemId.slice(14);
    var updateNameString = $("#update_design_name_"+designIdString).val();

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "design_id": designIdString,
        "design_name": updateNameString,
        "action_function": "update_design",
        "callback_url": "manage_designs_bulk.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function deleteRow(itemId) {
  	var designIdString = itemId.slice(14);

		$.ajax({
		  url: "uploadplain.php",
		  type: "POST",
		  contentType: "application/x-www-form-urlencoded",
		  data: $.param({
		  	"design_id": designIdString,
		  	"action_function": "void_design",
		  	"callback_url": "manage_designs_bulk.php"
		  }),
		}).done(function (data, status, jqXHR) {
			location.reload();
		}).fail(function (jqXHR, status, err) {
			console.log(err);
		}).always(function() {

		})
	}

  function makeFileList() {
    var input = document.getElementById("add_new_design_upload_image");
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
    newDesignNameChange();
  }

</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Manage Designs
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
				<form id="add_new_design_form" class="form-horizontal" action="uploadbulk.php" method="post" enctype="multipart/form-data">
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Gender</label>
							<div class="col-md-4">
								<div class="radio-list">
									<label class="radio-inline">
									<input type="radio" name="add_new_design_gender" id="add_new_design_gender_0" value="0" onchange="newDesignNameChange()" checked > For Men </label>
									<label class="radio-inline">
									<input type="radio" name="add_new_design_gender" id="add_new_design_gender_1" value="1" onchange="newDesignNameChange()"> For Women </label>
								</div>
							</div>
						</div>
						<div id="add_new_design_for_men_group" class="form-group">
							<label class="col-md-3 control-label">Dress</label>
							<div class="col-md-4">
								<select id="add_new_design_for_men" class="form-control input-medium" onchange="newDesignNameChange()" ng-model="clothing_men" ng-options="c.clothing_name for c in dataset_clothing_men">
								</select>
							</div>
						</div>
						<div id="add_new_design_for_women_group" class="form-group" style="display: none;">
							<label class="col-md-3 control-label">Dress</label>
							<div class="col-md-4">
								<select id="add_new_design_for_women" class="form-control input-medium" onchange="newDesignNameChange()" ng-model="clothing_women" ng-options="c.clothing_name for c in dataset_clothing_women">
								</select>
							</div>
						</div>
						<div id="add_new_design_design_group_men_group" class="form-group" style="display: none;">
							<label class="col-md-3 control-label">Design Group</label>
							<div class="col-md-4">
								<select id="add_new_design_design_group_men" class="form-control input-medium" onchange="newDesignNameChange()" ng-model="design_group_men" ng-options="dg.design_group_name for dg in dataset_design_groups | filter: isEqualTo('clothing_id', clothing_men.clothing_id) track by dg.design_group_id">
								</select>
							</div>
						</div>
						<div id="add_new_design_design_group_women_group" class="form-group" style="display: none;">
							<label class="col-md-3 control-label">Design Group</label>
							<div class="col-md-4">
								<select id="add_new_design_design_group_women" class="form-control input-medium" onchange="newDesignNameChange()" ng-model="design_group_women" ng-options="dg.design_group_name for dg in dataset_design_groups | filter: isEqualTo('clothing_id', clothing_women.clothing_id) track by dg.design_group_id">
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Upload Image</label>
							<div class="col-md-4">
								<span class="btn btn-success fileinput-button">
								    <input type="file" name="filesToUpload[]" id="add_new_design_upload_image" onchange="makeFileList()" accept=".jpg" multiple="" required><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Upload</input>
							    </span>
							</div>
						</div>
						<div id="add_new_design_upload_image_group" class="form-group">
							<label class="col-md-3 control-label">Preview</label>
							<div class="col-md-4">
                <ul id="fileList"><li>No Files Selected</li></ul>
								<!-- <img id="add_new_design_upload_image_preview" src="" alt="Please Select an Image File" class="backinputimg" /> -->
							</div>
						</div>
						<div class="form-group last">
							<label class="col-md-3 control-label">Base Name</label>
							<div class="col-md-4">
								<input type="text" id="add_new_design_image_name" name="image_name" class="form-control" readonly />
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
				  <input type="hidden" id="add_new_design_design_group_id" name="add_new_design_design_group_id" value="1" required/>
				  <input type="hidden" name="folder" value="design" />
				  <input type="hidden" name="action_function" value="add_design" />
				  <input type="hidden" name="callback_url" value="manage_designs_bulk.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Designs
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
							<th>Design ID</th>
							<th>Design Name</th>
							<th>Design Group</th>
							<th>Clothing</th>
							<th>Gender</th>
							<th>Dress Image</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody>
						<tr ng-repeat-start="x in dataset_designs" id="data_design_{{ x.design_id }}_row">
							<td>{{ x.design_id }}</td>
							<td>{{ x.design_name }}</td>
							<td>{{ x.design_group_name }}</td>
							<td>{{ x.clothing_name }}</td>
							<td>{{ genders[x.is_for_women] }}</td>
							<td><img unveil lazy-src="../uploadedimages/design/{{ x.design_image }}.jpg" data-src="../uploadedimages/design/{{ x.design_image }}.jpg" class="backtableimg" /></td>
							<td><button type="button" id="update_design_{{ x.design_id }}" class="btn red" onclick="display_data(this.id)"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit</button></td>
							<td><button type="button" id="delete_design_{{ x.design_id }}" class="btn red" onclick="deleteRow(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Delete</button></td>
						</tr>
						<tr ng-repeat-end id="update_design_{{ x.design_id }}_row" style="display:none">
				            <td>{{ x.design_id }}</td>
				            <td><input type="text" id="update_design_name_{{ x.design_id }}" name="design_name" value="{{ x.design_name }}"/></td>
				            <td>{{ x.design_group_name }}</td>
				            <td>{{ x.clothing_name }}</td>
				            <td>{{ genders[x.is_for_women] }}</td>
				            <td><img unveil lazy-src="../uploadedimages/design/{{ x.design_image }}.jpg" data-src="../uploadedimages/design/{{ x.design_image }}.jpg" class="backtableimg" /></td>
							<td><button type="button" id="update_design_{{ x.design_id }}" class="btn red" onclick="updateRow(this.id)"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Save</button></td>
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
