<?php include 'backdash_header.php';?>
<script>
  $(document).ready(function() {
    var request;
    $("#measurement_garment_search_user_form").submit(function(event){
        $("#search_result_dialog_group").fadeOut(50);
        if (request) {
            request.abort();
        }
        var $form = $(this);
        var $inputs = $form.find("input, select, button, textarea");
        var serializedData = $form.serialize();
        $inputs.prop("disabled", true);
        request = $.ajax({
            url: "../api/v1/getmeasurementgarmentsuserbyemail",
            type: "post",
            data: serializedData
        });
        request.done(function (response, textStatus, jqXHR){
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
      console.log('Redirecting')
      localStorage.measurement_garment_search_user_id = response.user_id;
      localStorage.measurement_garment_search_user_email = response.email;
      localStorage.measurement_garment_search_user_name = response.name;
      window.location.href="manage_measurement_garment_user.php";
    } else {
      $("#search_result_dialog_group").fadeIn();
    }
  }
</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Measurement Garments
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<div class="portlet light bg-inverse">
			<div class="portlet-title">
				<div class="caption">
					<span class="caption-subject font-red-sunglo bold uppercase">Select User</span>
					<span class="caption-helper">use this form to find a user to add Measurement Garment to</span>
				</div>
				<div class="tools">
					<a href="javascript:;" class="collapse">
					</a>
				</div>
			</div>
			<div class="portlet-body form">
				<form id="measurement_garment_search_user_form" class="form-horizontal">
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
          <div id="search_result_dialog_group" class="form-actions" style="display:none;">
            <div class="row">
              <div class="col-md-offset-3 col-md-4">
                <div id="search_result_dialog_alert" class="alert alert-dismissible alert-danger" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <span id="search_result_dialog_text">No such user. Please try again</span>
                </div>
              </div>
            </div>
          </div>          
				</form>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
