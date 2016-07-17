<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
        var data;
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('../api/v1/getalladdons', data, config)
        .success(function (data, status, headers, config) {
          $scope.dataset_addons = data.addons;
          var addons = JSON.parse(getUrlParameter('addon_set'));
          var numOfAddons = addons["num"];
          var addonsSelectedIdsObject = {}
          for (i = 0; i < numOfAddons; i++) {
            addonsSelectedIdsObject[addons[i]] = true;
          }
          var addonsSelectedArray = [];
          for (x in data.addons) {
            var ad_item = data.addons[x];
            var ad_id = ad_item.addon_id;
            if(!!addonsSelectedIdsObject[ad_id]) {
              addonsSelectedArray.push({
                "addon_id": ad_id,
                "addon_name": ad_item.addon_name,
                "addon_image": ad_item.addon_image,
                "addon_price": ad_item.addon_price
              });
            }
          }
          $scope.dataset_addons_selected = addonsSelectedArray;

        })
        .error(function (data, status, header, config) {
          $scope.ResponseDetails = "Data: " + data +
            "<hr />status: " + status +
            "<hr />headers: " + header +
            "<hr />config: " + config;
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
Designs
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
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
							<th>Addon ID</th>
              <th>Addon Name</th>
              <th>Addon Price</th>
              <th>Design Image</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat="x in dataset_addons_selected">
							<td>{{ x.addon_id }}</td>
              <td>{{ x.addon_name }}</td>
              <td>{{ x.addon_price }}</td>
              <td><img src="../uploadedimages/addon/{{ x.addon_image }}.jpg" class="backtableimg" /></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
