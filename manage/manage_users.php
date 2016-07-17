<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getalluserprofiles", data)
      .then(function (response) {
        $scope.dataset_user_profiles = response.data.results;
      });
  });

  function activateUserViaAdmin(itemId) {
    var userEmail = itemId.slice(14);

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "email": userEmail,
        "action_function": "activate_user_via_admin",
        "callback_url": "manage_users.php"
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
Manage Users
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
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
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Users
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
							<th>#</th>
							<th>Name</th>
							<th>Mobile</th>
							<th>Email / Facebook ID</th>
							<th>Create Date</th>
							<th>Activation</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat="x in dataset_user_profiles">
							<td>{{ $index + 1 }}</td>
							<td>{{ x.name }}</td>
							<td>{{ x.mobile }}</td>
							<td>{{ x.email }}</td>
							<td>{{ x.user_create_date }}</td>
							<td ng-if="x.active == 0"><button type="button" id="activate_user_{{ x.email }}" class="btn green" onclick="activateUserViaAdmin(this.id)"><i class="fa fa-unlock-alt"></i> Activate</button></td>
							<td ng-if="x.active == 1"><i class="fa fa-unlock"></i> Activated</td>
							<td ng-if="x.active == 2"><i class="fa fa-facebook-official"></i> Facebook</td>
							<td ng-if="x.active == 3"><i class="fa fa-google-plus"></i> Google</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
