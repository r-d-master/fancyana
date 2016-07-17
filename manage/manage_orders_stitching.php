<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var status_param = getUrlParameter('status');
      if (!status_param) {
        status_param = "0"
      }
      var data = $.param({
        status: status_param
      });
      var config = {
        headers : {
          'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
        }
      }
      $http.post('../api/v1/getordersandextrasfors', data, config)
      .success(function (data, status, headers, config) {
        $scope.dataset_orders = data.results;
        console.log(data);
        $scope.dataset_measurement_types = data.measurement_types;
        $scope.dataset_design_groups = data.design_groups;
        $scope.dataset_status_types = data.status_types;
        $scope.orderTypeString = {
          "1": "Stitching",
          "2": "Alteration"
        }
        $scope.genderString= {
          0: "M",
          1: "F"
        }
        $scope.pickupString = {
          "0": "No",
          "1": "Fabric",
          "2": "Fabric from E-Commerce", 
          "4": "Measurement Garment",
          "5": "Fabric & Measurement Garment",
          "6": "Fabric from E-Commerce & Measurement Garment"
        }
        $scope.fabricSourceString = {
          "0" : "Already Purchased",
          "1" : "Tailor Square",
          "2" : "Sent to TailorSquare",
          "3" : "Ordered from E-Commerce"
        }
        $scope.measurementSourceString = {
          "1" : "TS Standard",
          "2" : "Measurement Garment",
          "3" : "Custom Measurement",
          "4" : "Pending TS Standard",
          "5" : "Pending TS Standard (free)",
          "6" : "Previous Measurement Garment"
        }
        var statusStringObj = {};
        for (x in data.status_types){
          var st = data.status_types[x];
          statusStringObj[st.status_text_id] = st.status_text;
        }
        $scope.statusString = statusStringObj;
        console.log("ready")
      })
      .error(function (data, status, header, config) {
        $scope.ResponseDetails = "Data: " + data +
          "<hr />status: " + status +
          "<hr />headers: " + header +
          "<hr />config: " + config;
      });
  });

    $(document).ready(function() {
      //triggered when modal is about to be shown
      $('#address_modal').on('show.bs.modal', function(e) {

          //get data-id attribute of the clicked element
          var addressNameVal = $(e.relatedTarget).data('address-name');
          var addressPersonNameVal = $(e.relatedTarget).data('address-person-name');
          var addressLine1Val = $(e.relatedTarget).data('address-line1');
          var addressLine2Val = $(e.relatedTarget).data('address-line2');
          var addressCityVal = $(e.relatedTarget).data('address-city');
          var addressStateVal = $(e.relatedTarget).data('address-state');
          var addressPincodeVal = $(e.relatedTarget).data('address-pincode');
          var addressCountryVal = $(e.relatedTarget).data('address-country');
          var addressMobileVal = $(e.relatedTarget).data('address-mobile');

          //populate the textbox
          $(e.currentTarget).find('input[name="address-name"]').val(addressNameVal);
          $(e.currentTarget).find('input[name="address-person-name"]').val(addressPersonNameVal);
          $(e.currentTarget).find('input[name="address-line1"]').val(addressLine1Val);
          $(e.currentTarget).find('input[name="address-line2"]').val(addressLine2Val);
          $(e.currentTarget).find('input[name="address-city"]').val(addressCityVal);
          $(e.currentTarget).find('input[name="address-state"]').val(addressStateVal);
          $(e.currentTarget).find('input[name="address-pincode"]').val(addressPincodeVal);
          $(e.currentTarget).find('input[name="address-country"]').val(addressCountryVal);
          $(e.currentTarget).find('input[name="address-mobile"]').val(addressMobileVal);
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

    function applyFilter(filterId) {
      console.log(filterId);
      var filterString = "";
        switch (filterId) {
          case "filter_0" : filterString = "0"; break;
          case "filter_1" : filterString = "1,2,3,4,5,7,9,10"; break;
          case "filter_2" : filterString = "6,8,11,12"; break;
          case "filter_3" : filterString = "1"; break;
          case "filter_4" : filterString = "2,4"; break;
          case "filter_5" : filterString = "3"; break;
          case "filter_6" : filterString = "5"; break;
          case "filter_7" : filterString = "6,12"; break;
          case "filter_8" : filterString = "8"; break;
          case "filter_9" : filterString = "7,10"; break;
          case "filter_10" : filterString = "11"; break;
          default : filterString = "0";
        }
        console.log(filterString)
      window.location.href="manage_orders_stitching.php?status="+filterString;
    };

</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Manage Stitching Orders
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="filterBlock">
    <h4>Filter Orders</h4>
    <button type="button" id="filter_0" onclick="applyFilter(this.id)" class="btn default">All</button>
    <button type="button" id="filter_1" onclick="applyFilter(this.id)" class="btn yellow-crusta">Active</button>
    <button type="button" id="filter_9" onclick="applyFilter(this.id)" class="btn red-thunderbird">Decision Needed</button>
    <button type="button" id="filter_2" onclick="applyFilter(this.id)" class="btn grey-cascade">Closed</button>
    <button type="button" id="filter_3" onclick="applyFilter(this.id)" class="btn blue">Just Placed</button>
    <button type="button" id="filter_4" onclick="applyFilter(this.id)" class="btn yellow">Pending Pickup</button>
    <button type="button" id="filter_5" onclick="applyFilter(this.id)" class="btn purple">Incoming Fabric</button>
    <button type="button" id="filter_6" onclick="applyFilter(this.id)" class="btn yellow-gold">Processing</button>
    <button type="button" id="filter_7" onclick="applyFilter(this.id)" class="btn green">Delivered</button>
    <button type="button" id="filter_8" onclick="applyFilter(this.id)" class="btn red">Cancelled</button>
    <button type="button" id="filter_10" onclick="applyFilter(this.id)" class="btn blue-chambray">Returned</button>
</div>
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Stitching Orders
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
							<th>ID</th>
							<th>Customer</th>
              <th>Mobile</th>
							<th>Dress</th>
							<th>Fabric Source</th>
							<th>Fabric</th>
              <th>Designs</th>
              <th>Addons</th>
							<th>Measurement Source</th>
							<th>Measurements</th>
							<th>Delivery Address</th>
							<th>Pickup Required</th>
							<th>Pickup Address</th>
              <th>Pickup Date</th>
              <th>Total Price</th>
              <th>Remarks</th>
              <th>Status</th>
              <th>Update Status</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat="x in dataset_orders">
							<td>{{ x.order_id }}</td>
              <td>{{ x.name }}</td>
              <td>{{ x.mobile }}</td>
							<td>{{ x.clothing_name +' '+genderString[x.is_for_women]}}</td>
							<td>{{ fabricSourceString[x.fabric_method] }}</td>
              <td ng-show="{{ x.fabric_method == 1 }}">{{ x.fabric_id }}</td>
              <td ng-hide="{{ x.fabric_method == 1 }}">N/A</td>
              <td><a href="manage_order_designs.php?design_set={{ x.designs }}" target="_blank">Get Details</a></td></td>
              <td><a href="manage_order_addons.php?addon_set={{ x.addons }}" target="_blank">Get Details</a></td></td>
							<td>{{ measurementSourceString[x.measurement_method] }}</td>
              <td ng-show="{{ x.measurement_method == 1 || x.measurement_method == 3 || x.measurement_method == 6 }}"><a href="manage_order_measurements.php?measurement_set={{ x.measurements }}" target="_blank">Get Details</a></td>
              <td ng-hide="{{ x.measurement_method == 1 || x.measurement_method == 3 || x.measurement_method == 6 }}">Pending</td>
							<td><a href="#address_modal" data-toggle="modal" data-address-name="{{ x.delivery_address.address_name }}" data-address-person-name="{{ x.delivery_address.address_person_name }}" data-address-line1="{{ x.delivery_address.address_line1 }}" data-address-line2="{{ x.delivery_address.address_line2 }}" data-address-city="{{ x.delivery_address.address_city }}" data-address-state="{{ x.delivery_address.state_name }}" data-address-pincode="{{ x.delivery_address.address_pincode }}" data-address-country="{{ x.delivery_address.country_name }}" data-address-mobile="{{ x.delivery_address.address_mobile }}" >{{x.delivery_address.address_city +" - "+x.delivery_address.address_pincode }}</a></td>
							<td>{{ pickupString[x.pickup_required] }}</td>
              <td ng-hide="{{ x.pickup_required == 0 }}"><a href="#address_modal" data-toggle="modal" data-address-name="{{ x.pickup_address.address_name }}" data-address-person-name="{{ x.pickup_address.address_person_name }}" data-address-line1="{{ x.pickup_address.address_line1 }}" data-address-line2="{{ x.pickup_address.address_line2 }}" data-address-city="{{ x.pickup_address.address_city }}" data-address-state="{{ x.pickup_address.state_name }}" data-address-pincode="{{ x.pickup_address.address_pincode }}" data-address-country="{{ x.pickup_address.country_name }}" data-address-mobile="{{ x.pickup_address.address_mobile }}" >{{x.pickup_address.address_city +" - "+x.pickup_address.address_pincode }}</a></td>
							<td ng-show="{{ x.pickup_required == 0 }}">N/A</td>
							<td ng-hide="{{ x.pickup_required == 0 }}">{{ x.pickup_date }}</td>
              <td ng-show="{{ x.pickup_required == 0 }}">N/A</td>
              <td>{{ x.total_price }}</td>
              <td style="max-width: 100px; word-wrap: break-word;">{{ x.remarks }}</td>
              <td>{{ statusString[x.status] }}</td>
              <td><a href="manage_order_status.php?order_id={{ x.order_id }}&order_type=1" class="btn red"><span class="glyphicon glyphicon-edit"></span> Update</a></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<div id="address_modal" class="modal fade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Address</h4>
      </div>
      <div class="modal-body">
        <div class="scroller" style="height:350px" data-always-visible="1" data-rail-visible1="1">
          <div class="row">
            <div class="col-md-4">
              <label class="col-md-12 control-label">Address Name</label>
            </div>
            <div class="col-md-8">
              <input name="address-name" type="text" class="col-md-12 form-control" readonly="true">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <label class="col-md-12 control-label">Person Name</label>
            </div>
            <div class="col-md-8">
              <input name="address-person-name" type="text" class="col-md-12 form-control" readonly="true">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <label class="col-md-12 control-label">Address Line1</label>
            </div>
            <div class="col-md-8">
              <input name="address-line1" type="text" class="col-md-12 form-control" readonly="true">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <label class="col-md-12 control-label">Address Line2</label>
            </div>
            <div class="col-md-8">
              <input name="address-line2" type="text" class="col-md-12 form-control" readonly="true">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <label class="col-md-12 control-label">City</label>
            </div>
            <div class="col-md-8">
              <input name="address-city" type="text" class="col-md-12 form-control" readonly="true">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <label class="col-md-12 control-label">State</label>
            </div>
            <div class="col-md-8">
              <input name="address-state" type="text" class="col-md-12 form-control" readonly="true">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <label class="col-md-12 control-label">Pincode</label>
            </div>
            <div class="col-md-8">
              <input name="address-pincode" type="text" class="col-md-12 form-control" readonly="true">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <label class="col-md-12 control-label">Country</label>
            </div>
            <div class="col-md-8">
              <input name="address-country" type="text" class="col-md-12 form-control" readonly="true">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <label class="col-md-12 control-label">Mobile</label>
            </div>
            <div class="col-md-8">
              <input name="address-mobile" type="text" class="col-md-12 form-control" readonly="true">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn default">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
