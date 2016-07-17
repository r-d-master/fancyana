<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/gettsmeasurementtypes", data)
      .then(function (response) {
        $scope.dataset_measurement_types = response.data.results;
        for (x in response.data.results) {
          TSStan.measurementTypeIds.push(response.data.results[x].measurement_type_id);
        }
      });
  });

	var TSStan = {}

  $(document).ready(function() {
    TSStan.measurementTypeIds = [];
    var request;
    $("#ts_standard_search_user_form").submit(function(event){
        if (request) {
            request.abort();
        }
        var $form = $(this);
        var $inputs = $form.find("input, select, button, textarea");
        var serializedData = $form.serialize();
        $inputs.prop("disabled", true);
        request = $.ajax({
            url: "../api/v1/gettsstandardbyemail",
            type: "post",
            data: serializedData
        });
        request.done(function (response, textStatus, jqXHR){
            console.log("Got a response");
            console.log(response);
            updateUserSearchResult(response);
        });
        request.fail(function (jqXHR, textStatus, errorThrown){
            console.error(
                "The following error occurred: "+
                textStatus, errorThrown
            );
        });
        request.always(function () {
            $inputs.prop("disabled", false);
        });
        event.preventDefault();
    });
  });

  function updateUserSearchResult(response) {
    if(!!response.user_id) {
      $("#ts_standard_search_user_result").html(response.name + " (" + response.email + ")");
      $("#ts_standard_user_id").val(response.user_id)
      if(!!response.ts_standard){
        var ts_standard_values = JSON.parse(response.ts_standard);
        console.log(ts_standard_values);
        for(x in ts_standard_values){
          $("#measurement_type_"+x).val(ts_standard_values[x]);
        }
      }
      $("#ts_standard_portlet").show(300);
    } else {
      $("#ts_standard_search_user_result").html("No such user. Please try again");
      $("#ts_standard_portlet").hide(300);
    }
  }

  function changedMeasurement() {
    var ts_standard_object = {};
    for (x in TSStan.measurementTypeIds) {
      var mt_id = TSStan.measurementTypeIds[x];
      ts_standard_object[mt_id] = $("#measurement_type_"+mt_id).val();
    }
    var ts_standard_string = JSON.stringify(ts_standard_object);
    $("#ts_standard_ts_standard").val(ts_standard_string);
    console.log(ts_standard_string);
  }

</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
TS Standard
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Select User</span>
					<span class="caption-helper">use this form to modify TS Standard for a user</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<form id="ts_standard_search_user_form" class="form-horizontal">
					<div class="form-body">
						<div class="form-group last">
							<label class="col-md-3 control-label">Change User</label>
							<div class="col-md-4">
								<div class="input-group">
									<div class="input-icon">
										<i class="fa fa-search fa-fw"></i>
										<input type="email" class="form-control" name="email" placeholder="Search user by email / facebook id">
									</div>
									<span class="input-group-btn">
									<button id="ts_standard_search_user" class="btn green" type="submit"> Search</button>
									</span>
								</div>
							</div>
						</div>
					</div>
					<div class="form-actions">
						<div class="form-group">
							<label class="col-md-3 control-label">Selected User</label>
							<div class="col-md-4">
								<p id="ts_standard_search_user_result" class="form-control-static">
								</p>
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
				</form>
			</div>
		</div>
		<div class="portlet box red" id="ts_standard_portlet" style="display:none">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>TS STANDARD
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="ts_standard_edit_form" class="form-horizontal" action="uploadplain.php" method="post">
					<div class="form-body">
						<div class="form-group container" ng-repeat="x in dataset_measurement_types" ng-if="$even" >
              <div class="row">
  							<div class="col-md-4">
  								<label class="col-md-8 control-label">{{ x.measurement_type_name }}</label>
  								<div class="col-md-4">
  									<input type="number" step="0.01" id="measurement_type_{{ x.measurement_type_id }}" class="form-control" style="width:100px;" onchange="changedMeasurement()">
  								</div>
  							</div>
                <div class="col-md-4">
                  <label class="col-md-8 control-label">{{ dataset_measurement_types[$index+1].measurement_type_name }}</label>
                  <div class="col-md-4">
                    <input type="number" step="0.01" id="measurement_type_{{ dataset_measurement_types[$index+1].measurement_type_id }}" class="form-control" style="width:100px;" onchange="changedMeasurement()">
                  </div>
                </div>
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
			  	<input type="hidden" id="ts_standard_user_id" name="user_id" />
			  	<input type="hidden" id="ts_standard_ts_standard" name="ts_standard" />
					<input type="hidden" name="action_function" value="update_ts_standard" />
					<input type="hidden" name="callback_url" value="manage_ts_standard.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
