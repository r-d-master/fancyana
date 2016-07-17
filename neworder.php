<?php include 'commonhead.php';?>
<script src="js/orders.js"></script>
<script>
  var newOrderDataApp = angular.module('newOrderDataApp', []);
  newOrderDataApp.controller('newOrderDataCtrl', function($scope, $http) {
      var data;
      $http.post("api/v1/getallclothing", data)
      .then(function (response) {
        $scope.dataset_clothing_men = response.data.men;
        $scope.dataset_clothing_women = response.data.women;
      });
      $scope.user_name = localStorage.user_name;
      $scope.user_mobile = "";
      $scope.user_email = "";
      if (!!localStorage.user_mobile) {
        $scope.user_mobile = localStorage.user_mobile;
      }
      if (!!localStorage.user_email) {
        $scope.user_email = localStorage.user_email;
      }
  });
</script>
<script>
  function tapMenButton() {
    $("#tabMen").fadeIn(800);
    $("#tabWomen").fadeOut(100);
    $("#tabMenButton").addClass("btn-danger");
    $("#tabWomenButton").removeClass("btn-danger");
  }
  
  function tapWomenButton() {
    $("#tabMen").fadeOut(100);
    $("#tabWomen").fadeIn(800);
    $("#tabMenButton").removeClass("btn-danger");
    $("#tabWomenButton").addClass("btn-danger");
  }
  
  function selectDress(dress_img_tag) {
    var dressIdVal = dress_img_tag.id.slice(10);
    var dressNameVal = dress_img_tag.alt;
    $(".tabContentImgBigActive").removeClass("tabContentImgBigActive");      
    $("#"+dress_img_tag.id).addClass("tabContentImgBigActive");
    $("#dress_"+dressIdVal).prop("checked", true);
  }
  
  function tapNewOrderNext1() {
    if(!!$("#tabMenButton").hasClass("btn-danger")){
    console.log("men");
      dressIdVal = $('input[name="dress_0"]:checked').val();
    } else {
    console.log("women");
      dressIdVal = $('input[name="dress_1"]:checked').val();
    }
    if (!!dressIdVal){
      console.log(dressIdVal);
      dressNameVal = $("#dress_img_"+dressIdVal).attr("alt");
      dressPriceVal = $("#dress_price_"+dressIdVal).val();
      localStorage.setItem('order_dress_id', dressIdVal);
      localStorage.setItem('order_dress_string', dressNameVal);
      localStorage.setItem('order_dress_price', dressPriceVal);
      window.location.href="neworder2.php";
    } else {
      alert("Please select a Dress");
    }
  }

  $(document).ready(function() {
    var request;
    $("#specialorderform").submit(function(event){
        $("#specialorderform").fadeOut(200);
        setTimeout(function(){ $("#specialordermessage").fadeIn(); }, 200);
        if (request) {
            request.abort();
        }
        var $form = $(this);
        var $inputs = $form.find("input, select, button, textarea");
        var serializedData = $form.serialize();
        $inputs.prop("disabled", true);
        request = $.ajax({
            url: "api/v1/placespecialorderrequest",
            type: "post",
            data: serializedData
        });
        request.done(function (response, textStatus, jqXHR){
            // Log a message to the console
            if(response.error){
              console.error("The following error occurred: " + response.message);
              // $("#specialorder_error_1").fadeIn();
            } else{
              console.log("Successfully Placed Request!");
              window.location.href = "specialorderplaced.php";                
            }
            console.log(response);
        });
        request.fail(function (jqXHR, textStatus, errorThrown){
            console.error("The following error occurred: "+textStatus, errorThrown);
            // $("#specialorder_error_1").fadeIn();
        });
        request.always(function () {
            $inputs.prop("disabled", false);
            $("#specialorderform").show();
            $("#specialordermessage").hide();
        });
        event.preventDefault();
    });
  });
</script>
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
  <!-- Header -->
  <?php include 'header.php';?>
  <!-- Begin Content -->
  <section id="stitching" ng-app="newOrderDataApp" ng-controller="newOrderDataCtrl">
    <div id="specialOrderModal" class="modal fade" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content" style="width:400px">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="text-align:center;">Special Order</h4>
          </div>
          <div class="modal-body">
            <form id="specialorderform" class="tsforminmodal">
              <label for="register_name" class="sr-only">Name</label>
              <input type="text" id="special_order_name" name="name" class="tsforminput" placeholder="Name" value="{{ user_name }}" required>
              <label for="register_mobile" class="sr-only">Mobile Number</label>
              <input type="text" id="special_order_mobile" name="mobile" class="tsforminput" placeholder="Mobile Number (only 10 digits)" value="{{ user_mobile }}" pattern="(\d{10})" title="Please enter only the 10 digit number Example: 9876543210" required>
              <label for="register_email" class="sr-only">Email Address</label>
              <input type="email" id="special_order_email" name="email" class="tsforminput" placeholder="Email Address" value="{{ user_email }}" required>
              <div style="margin-top:5px;"></div>
              <label class="radio-inline">
              <input type="radio" id="special_order_gender_m" name="gender" value="For Men" required> For Men</label>
              <label class="radio-inline">
              <input type="radio" id="special_order_gender_w" name="gender" value="For Women" required> For Women</label>
              <div style="margin-top:5px;"></div>
              <label for="new_dress" class="sr-only">Dress</label>
              <textarea rows="4" id="dress_info" name="dress_info" class="tsforminput" placeholder="Dress Info" required></textarea>
              <input type="hidden" id="special_order_order_type" name="order_type" value="Stitching">
              <button class="btn btn-lg btn-danger btn-block" type="submit" value="Submit">Submit</button>
            </form>
            <p id="specialordermessage" style="text-align:center; font-size:1.2em; margin-top:127px; margin-bottom:127px;" hidden>Placing Request...</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <div class="container align-to-center">
      <div class="col-lg-12">
        <div id="container">
          <div id="parentHorizontalTab">
            <div class="resp-tabs-container hor_1">
              <div>
                <div class="checkout-wrap">
                  <ul class="checkout-bar">
                    <a href="neworder.php">
                      <li class="active">Dress</li>
                    </a>
                    <li class="">Fabric</li>
                    <li class="">Design</li>
                    <li class="">Addons</li>
                    <li class="">Measurements</li>
                    <li class="">Pickup/Delivery</li>
                    <li class="">Confirm</li>
                  </ul>
                </div>
                <div class="row">
                  <div class="orderContentStart"></div>
                  <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-5">
                      <input type="button" id="tabMenButton" value="I AM A MAN" class="tabHorizontalTitleBig btn btn-danger" onclick="tapMenButton()" />
                    </div>
                    <div class="col-md-5">
                      <input type="button" id="tabWomenButton" value="I AM A WOMAN" class="tabHorizontalTitleBig btn" onclick="tapWomenButton()" />
                    </div>
                    <div class="col-md-1"></div>
                  </div>
                  <div class="row">
                    <div class="col-md-12 bodyRedLink" style="margin-top:10px; font-size: 1.8rem;">
                      Can't find your dress? <a data-toggle="modal" data-target="#specialOrderModal">Click here</a> to request a special order!
                    </div>
                  </div>
                  <br />
                  <div id="tabMen">
                    <form id="neworder_men_dress">
                      <label ng-repeat="x in dataset_clothing_men" for="dress_{{ x.clothing_id }}">
                        <input id="dress_{{ x.clothing_id }}" type="radio" name="dress_{{ x.is_for_women }}" class="hideRadioCircle" value="{{ x.clothing_id }}" />
                        <div class="upperLabel">{{ x.clothing_name }}</div>
                        <figure>
                          <img id="dress_img_{{ x.clothing_id }}" src='uploadedimages/dress/{{ x.clothing_image }}.jpg' alt="{{ x.clothing_name }}" class="tabContentImgBig" onclick="selectDress(this)">
                          <figcaption style="color:#DD0B0C">&#8377; {{ x.price }}/-</figcaption>
                        </figure>
                        <br />
                        <input type="hidden" id="dress_price_{{ x.clothing_id }}" value="{{ x.price }}" />
                      </label>
                    </form>
                  </div>
                  <div id="tabWomen" style="display: none;">
                    <p>
                    <form id="neworder_women_dress">
                      <label ng-repeat="y in dataset_clothing_women" for="dress_{{ y.clothing_id }}">
                        <input id="dress_{{ y.clothing_id }}" type="radio" name="dress_{{ y.is_for_women }}" class="hideRadioCircle"  value="{{ y.clothing_id }}" />
                        <div class="upperLabel">{{ y.clothing_name }}</div>
                        <figure>
                          <img id="dress_img_{{ y.clothing_id }}" src="uploadedimages/dress/{{ y.clothing_image }}.jpg" alt="{{ y.clothing_name }}" class="tabContentImgBig" onclick="selectDress(this)">
                          <figcaption style="color:#DD0B0C">&#8377; {{ y.price }}/-</figcaption>
                        </figure>
                        <input type="hidden" id="dress_price_{{ y.clothing_id }}" value="{{ y.price }}" />
                        <br />
                      </label>
                    </form>
                    </p>
                  </div>
                </div>
                <br />
                <br />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <div class="order-nav-parent" style="border:none">
    <div id="order-nav">
      <input type="submit" name="neworder_next_1" value="Next" id="neworder_next_1" class="btn btn-danger" onclick="tapNewOrderNext1()" />
    </div>
  </div>
  <!-- Footer -->
  <?php include 'footerthin.php';?>  
  <!-- Bootstrap core JavaScript
    ================================================== -->
  <!-- Placed at the end of the document so the pages load faster -->
  <script src="js/bootstrap.min.js"></script>
  <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
  <script src="js/ie10-viewport-bug-workaround.js"></script>
</body>
</html>
