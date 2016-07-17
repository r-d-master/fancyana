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


</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Manage Users
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
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
			<div class="portlet-body flip-scroll">
				<table class="table table-bordered table-striped table-condensed flip-content backtable-centered">
					<thead class="flip-content">
						<tr>
							<th>#</th>
							<th>Name</th>
							<th>Mobile</th>
							<th>Email</th>
							<th>Create Date</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat="x in dataset_user_profiles">
							<td>{{ $index + 1 }}</td>
							<td>{{ x.name }}</td>
							<td>{{ x.mobile }}</td>
							<td>{{ x.email }}</td>
							<td>{{ x.user_create_date }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
