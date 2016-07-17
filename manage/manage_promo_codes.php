<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
      var data;
      $http.post("../api/v1/getallpromos", data)
      .then(function (response) {
        $scope.dataset_promos = response.data.results;
        $scope.promoTypeString = {
          "1": "Percentage",
          "2": "Flat Amount"
        }
        $scope.promoStatusString = {
          "0": "Disabled",
          "1": "Active"
        }
      });
  });

  function editRow(itemId)
  {
    var promoIdString = itemId.slice(11);
    $("#data_promo_"+promoIdString+"_row").hide(); 
    $("#update_promo_"+promoIdString+"_row").show();
  }

  function reloadWithMessage(msg_type, msg_text) {
	window.location.href="manage_promo_codes.php?message="+msg_type+"&message_text="+msg_text;
  }

  function updateRow(itemId) {
    var promoIdString = itemId.slice(13);
    var updateDiscount = $("#update_promo_discount_"+promoIdString).val();
    var updateMinimumAmount = $("#update_promo_minimum_amount_"+promoIdString).val();
    var updatePromoType = $("#update_promo_type_"+promoIdString).val();

    if (updatePromoType == "1" && updateDiscount > 100) {
		reloadWithMessage(0, "Discount%20can%20not%20be%20more%20than%20100%25");
    } else {
	    $.ajax({
	      url: "uploadplain.php",
	      type: "POST",
	      contentType: "application/x-www-form-urlencoded",
	      data: $.param({
	        "promo_id": promoIdString,
	        "promo_discount": updateDiscount,
	        "promo_minimum_amount": updateMinimumAmount,
	        "action_function": "update_promo",
	        "callback_url": "manage_promo_codes.php"
	      }),
	    }).done(function (data, status, jqXHR) {
			reloadWithMessage(1, "Successfully%20updated%21");
	    }).fail(function (jqXHR, status, err) {
	      console.log(err);
	    }).always(function() {

	    })
    }
  }

  function activatePromo(itemId) {
    var promoIdString = itemId.slice(15);

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "promo_id": promoIdString,
        "action_function": "activate_promo",
        "callback_url": "manage_promo_codes.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }

  function deactivatePromo(itemId) {
    var promoIdString = itemId.slice(17);

    $.ajax({
      url: "uploadplain.php",
      type: "POST",
      contentType: "application/x-www-form-urlencoded",
      data: $.param({
        "promo_id": promoIdString,
        "action_function": "deactivate_promo",
        "callback_url": "manage_promo_codes.php"
      }),
    }).done(function (data, status, jqXHR) {
      location.reload();
    }).fail(function (jqXHR, status, err) {
      console.log(err);
    }).always(function() {

    })
  }
  function promoDiscountChange() {
  	var promoTypeSelected = $("#add_promo_type").val();
  	if (promoTypeSelected == "1") {
  		discountVal = $("#add_promo_discount_percentage").val();
  		$("#add_promo_discount").val(discountVal);
  		$("#add_promo_minimum_amount").prop("min", 0);
  	} else if (promoTypeSelected == "2") {
  		discountVal = $("#add_promo_discount_amount").val();
  		$("#add_promo_discount").val(discountVal);
  		$("#add_promo_minimum_amount").prop("min", discountVal);
  	}
  }

  function promoTypeChange() {
  	var promoTypeSelected = $("#add_promo_type").val();
  	if (promoTypeSelected == "1") {
  		$("#add_promo_discount_percentage_group").show();
  		$("#add_promo_discount_amount_group").hide();
  	} else if (promoTypeSelected == "2") {
  		$("#add_promo_discount_percentage_group").hide();
  		$("#add_promo_discount_amount_group").show();
  	}
  }

</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Manage Promo Codes
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Add New Promo Code</span>
					<span class="caption-helper">use this form to add a new promo code</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form id="add_promo_form" class="form-horizontal" action="uploadplain.php" method="post">
					<div class="form-body">
						<div class="form-group">
							<label class="col-md-3 control-label">Code</label>
							<div class="col-md-4">
								<input type="text" id="add_promo_code" name="promo_code" maxlength="64" class="form-control" placeholder="Eg: DIWALI30" oninput="this.value=this.value.toUpperCase();">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Type</label>
							<div class="col-md-4">
								<select id="add_promo_type" name="promo_type" class="form-control input-medium" onchange="promoTypeChange()">
									<option value="1">Percentage Discount</option>
									<option value="2">Flat Discount</option>
								</select>
							</div>
						</div>
						<div id="add_promo_discount_percentage_group" class="form-group">
							<label class="col-md-3 control-label">Discount Percentage</label>
							<div class="col-md-4">
								<input type="number" step="1" id="add_promo_discount_percentage" class="form-control" placeholder="Percentage Value | Example: 30%" onchange="promoDiscountChange()">
							</div>
						</div>
						<div id="add_promo_discount_amount_group" class="form-group" style="display:none;">
							<label class="col-md-3 control-label">Discount Amount</label>
							<div class="col-md-4">
								<input type="number" step="1" id="add_promo_discount_amount" class="form-control" placeholder="Amount in INR | Example: &#8377;200" onchange="promoDiscountChange()">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Minimum Amount</label>
							<div class="col-md-4">
								<input type="number" step="1" min="0" id="add_promo_minimum_amount" name="promo_minimum_amount" class="form-control" placeholder="Minimum Order Total for Discout to Work | Example: &#8377;500">
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
					<input type="hidden" id="add_promo_discount" name="promo_discount" value="0" />
					<input type="hidden" name="action_function" value="add_promo" />
					<input type="hidden" name="callback_url" value="manage_promo_codes.php" />
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Promo Codes
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
							<th>Code</th>
							<th>Type</th>
							<th>Discount</th>
							<th>Minimum Amount</th>
							<th>Status</th>
							<th>Action</th>
							<th>Edit</th>
						</tr>
						</thead>
					<tbody>
			            <tr ng-repeat-start="x in dataset_promos" id="data_promo_{{ x.promo_id }}_row">
			              <td>{{ x.promo_id }}</td>
			              <td>{{ x.promo_code }}</td>
			              <td>{{ promoTypeString[x.promo_type] }}</td>
			              <td><span ng-if="x.promo_type==2">&#8377;</span>{{ x.promo_discount }}<span ng-if="x.promo_type==1">%</span></td>
			              <td>&#8377; {{ x.promo_minimum_amount }}</td>
			              <td>{{ promoStatusString[x.active] }}</td>
			              <td ng-if="x.active==0"><button type="button" id="activate_promo_{{ x.promo_id }}" class="btn green" onclick="activatePromo(this.id)"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Activate</button></td>
			              <td ng-if="x.active==1"><button type="button" id="deactivate_promo_{{ x.promo_id }}" class="btn red" onclick="deactivatePromo(this.id)"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Deactivate</button></td>
			              <td><button type="button" id="edit_promo_{{ x.promo_id }}" class="btn red" onclick="editRow(this.id)"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit</button></td>
			            </tr>
			            <tr ng-repeat-end id="update_promo_{{ x.promo_id }}_row" style="display:none">
			              <td>{{ x.promo_id }}</td>
			              <td>{{ x.promo_code }}</td>
			              <td>{{ promoTypeString[x.promo_type] }}<input type="hidden" id="update_promo_type_{{ x.promo_id }}" name="promo_type" value="{{ x.promo_type }}"/></td>
				          <td ng-if="x.promo_type==1"><input type="number" step="1" min="0" max="100" id="update_promo_discount_{{ x.promo_id }}" name="promo_discount" value="{{ x.promo_discount }}"/></td>
				          <td ng-if="x.promo_type==2"><input type="number" step="1" min="0" id="update_promo_discount_{{ x.promo_id }}" name="promo_discount" value="{{ x.promo_discount }}"/></td>
				          <td><input type="number" step="1" min="0" id="update_promo_minimum_amount_{{ x.promo_id }}" name="promo_minimum_amount" value="{{ x.promo_minimum_amount }}"/></td>
			              <td>{{ promoStatusString[x.promo_active] }}</td>
			              <td ng-if="x.active==0"><button type="button" disabled class="btn green disabled"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Activate</button></td>
			              <td ng-if="x.active==1"><button type="button" disabled class="btn red disabled"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Deactivate</button></td>
			              <td><button type="button" id="update_promo_{{ x.promo_id }}" class="btn green" onclick="updateRow(this.id)"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Save</button></td>
			            </tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
