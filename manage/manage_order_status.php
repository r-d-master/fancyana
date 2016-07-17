<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var order_id_param = getUrlParameter('order_id');
      var order_type_param = getUrlParameter('order_type');
      var order_type_redirect_string = "manage_orders_stitching.php"
      if (order_type_param == 2) {
        order_type_redirect_string = "manage_orders_alteration.php"
      }
        var data = $.param({
          order_id: order_id_param
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('../api/v1/getstatusbyorder', data, config)
        .success(function (data, status, headers, config) {
          $scope.dataset_order_status = data.order_status;
          $scope.dataset_status_types = data.status_types;
        })
        .error(function (data, status, header, config) {
          $scope.ResponseDetails = "Data: " + data +
            "<hr />status: " + status +
            "<hr />headers: " + header +
            "<hr />config: " + config;
        });
        $scope.order_id = order_id_param;
        $scope.order_type_redirect_string = order_type_redirect_string;
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
Update Order Status
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
    <div class="portlet light bg-inverse">
      <div class="portlet-title">
        <div class="caption">
          <span class="caption-subject font-red-sunglo bold uppercase">Add New Status</span>
          <span class="caption-helper">use this form to add a new status for this order</span>
        </div>
        <div class="tools">
          <a href="javascript:;" class="collapse">
          </a>
        </div>
      </div>
      <div class="portlet-body form">
        <!-- BEGIN FORM-->
        <form id="add_new_order_status" class="form-horizontal" action="uploadplain.php" method="post">
          <div class="form-body">
            <div class="form-group">
              <label class="col-md-3 control-label">Status Type</label>
              <div class="col-md-8">
                <select class="form-control input-xlarge" name="status_text_id">
                  <option ng-repeat="y in dataset_status_types" ng-if="$index > 0" value="{{ y.status_text_id }}">{{ y.status_text }}</option>
                </select>
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
          <input type="hidden" name="order_id" value="{{ order_id }}" />
          <input type="hidden" name="action_function" value="add_order_status" />
          <input type="hidden" name="callback_url" value="{{ order_type_redirect_string }}" />
        </form>
        <!-- END FORM-->
      </div>
    </div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Order Status Records
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
							<th>Status</th>
              <th>Updated On</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat="x in dataset_order_status">
							<td>{{ x.status_text }}</td>
              <td>{{ x.status_create_date }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
