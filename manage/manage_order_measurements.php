<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getallmeasurementtypes", data)
      .then(function (response) {
        $scope.dataset_measurement_types = response.data.results;
        var mtName = {};
        var allMeasurementTypes = [];
        for (x in response.data.results) {
          var mt = response.data.results[x];
          mtName[mt.measurement_type_id] = mt.measurement_type_name;
          allMeasurementTypes.push(mt.measurement_type_id);
        }
  	    var measurements = getUrlParameter('measurement_set');
  	    var measurementsObj = JSON.parse(measurements);
        var measurementsArray = [];
        console.log(measurementsObj);
        for(x in allMeasurementTypes) {
          // console.log(allMeasurementTypes[x]);
          if(!!measurementsObj[allMeasurementTypes[x]]){
            measurementsArray.push({
              "id" : allMeasurementTypes[x],
              "name": mtName[allMeasurementTypes[x]],
              "val": measurementsObj[allMeasurementTypes[x]]
            });
          }
        }
        $scope.dataset_measurements = measurementsArray;
        $scope.dataset_measurement_name = mtName;
      });
  });

  var getUrlParameter = function getUrlParameter(sParam) {
      var sPageURL = decodeURIComponent(window.location.search.substring(1)),
          sURLVariables = sPageURL.split('&'),
          sParameterName,
          i;

      for (i = 0; i < sURLVariables.length; i++) {
          sParameterName = sURLVariables[i].split('=');

          if (sParameterName[0] === sParam) {
              return sParameterName[1] === undefined ? true : sParameterName[1];
          }
      }
  };

</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Measurements
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Measurements
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
							<th>Measurement Type</th>
							<th>Measurement Value (cm)</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat="x in dataset_measurements">
							<td>{{ x.name }}</td>
							<td>{{ x.val }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
