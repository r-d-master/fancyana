<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getalldesignsanddesigngroupsandclothing", data)
      .then(function (response) {
        var dgName = {};
        var allDesignGroups = [];
        for (x in response.data.design_groups) {
          var dg = response.data.design_groups[x];
          dgName[dg.design_group_id] = dg.design_group_name;
          allDesignGroups.push(dg.design_group_id);
        }
        var dName = {};
        var dImage = {};
        dName[0] = "Copy From My Garment";
        dImage[0] = "design_0";
        for (x in response.data.designs) {
          var d = response.data.designs[x];
          dName[d.design_id] = d.design_name;
          dImage[d.design_id] = d.design_image;
        }
  	    var designs = getUrlParameter('design_set');
  	    var designsObj = JSON.parse(designs);
        var designsArray = [];
        console.log(designsObj);
        for(x in allDesignGroups) {
          // console.log(allDesignGroups[x]);
          if(!!designsObj[allDesignGroups[x]]){
            designsArray.push({
              "design_group_id" : allDesignGroups[x],
              "design_group_name": dgName[allDesignGroups[x]],
              "design_id": designsObj[allDesignGroups[x]],
              "design_name": dName[designsObj[allDesignGroups[x]]],
              "design_image": dImage[designsObj[allDesignGroups[x]]]
            });
          }
        }
        $scope.dataset_designs = designsArray;
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
							<th>Design Group</th>
              <th>Design ID</th>
              <th>Design Name</th>
              <th>Design Image</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat="x in dataset_designs">
							<td>{{ x.design_group_name }}</td>
              <td>{{ x.design_id }}</td>
              <td>{{ x.design_name }}</td>
              <td><img src="../uploadedimages/design/{{ x.design_image }}.jpg" class="backtableimg" /></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
