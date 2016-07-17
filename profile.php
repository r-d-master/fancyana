<?php include 'commonhead.php';?>

    <script>
      var profileDataApp = angular.module('profileDataApp', []);
      profileDataApp.controller("profileDataCtrl", function ($scope, $http) {
       // use $.param jQuery function to serialize data from JSON 
        var data = $.param({
          user_id: localStorage.user_id
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('api/v1/getuserprofile', data, config)
        .success(function (data, status, headers, config) {
          $scope.dataset_profile = data.results[0];
          $scope.dataset_user_code = localStorage.user_code;
          var user_create_date_pretty = getPrettyDateTime(data.results[0].user_create_date);
          $scope.dataset_user_create_date_pretty = user_create_date_pretty;
          TSUserEmail = data.results[0].email;
        })
        .error(function (data, status, header, config) {
          $scope.ResponseDetails = "Data: " + data +
            "<hr />status: " + status +
            "<hr />headers: " + header +
            "<hr />config: " + config;
        });
      });

      var TSUserEmail = "";
      var linkClicked = false;
      var request;

      $( document ).ready(function() {
        $("#unsubscribe_button").hover(function(){
          $(this).removeClass('btn-success');
          $(this).addClass('btn-danger');
          $(this).html('<i class="fa fa-chain-broken"></i> Unsubscribe');
        }, function(){
          $(this).removeClass('btn-danger');
          $(this).addClass('btn-success');
          $(this).html('<i class="fa fa-chain"></i> Subscribed');
        });

        $("#subscribe_button").hover(function(){
          $(this).removeClass('btn-danger');
          $(this).addClass('btn-success');
          $(this).html('<i class="fa fa-chain"></i> Subscribe');
        }, function(){
          $(this).removeClass('btn-success');
          $(this).addClass('btn-danger');
          $(this).html('<i class="fa fa-chain-broken"></i> Unsubscribed');
        });
      });

      function ajaxForSubscription(endpoint) {
        if (request) {
            request.abort();
        }
        var serializedData = $.param({
          email: TSUserEmail
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        var urlforsubscription = "";
        if (endpoint == "subscribe") {
          urlforsubscription = "api/v1/addsubscriber";
        } else if (endpoint == "unsubscribe") {
          urlforsubscription = "api/v1/voidsubscriber";      
        } else {
          return;
        }
        request = $.ajax({
          url: urlforsubscription,
          type: "post",
          data: serializedData
        });
        request.done(function (response, textStatus, jqXHR){
          if (!response.error) {
            console.log("Subscription Updated");
          } else {
            console.log("Server Error");
          }
        });
        request.fail(function (jqXHR, textStatus, errorThrown){
          console.error(
              "The following error occurred: "+
              textStatus, errorThrown
          );
        });
        request.always(function () {
          location.reload();
        });
      }

      function subscribe() {
        if (!linkClicked) {
          linkClicked = true;
          ajaxForSubscription("subscribe");
          linkClicked = false;
        }
      }

      function unsubscribe() {
        if (!linkClicked) {
          linkClicked = true;
          ajaxForSubscription("unsubscribe");
          linkClicked = false;
        }
      }

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
    <br />
    <br />
    <div class="container">
      <div class="col-md-2">
        <ul class="nav nav-pills nav-stacked">
          <li role="presentation"><a href="dashboard.php">Dashboard</a></li>
          <li role="presentation"><a href="orders.php">My Orders</a></li>
          <li role="presentation"><a href="addresses.php">Manage Addresses</a></li>
          <li role="presentation" class="active"><a href="profile.php">Profile</a></li>
          <li role="presentation"><a href="changepassword.php">Change Password</a></li>
        </ul>
      </div>
      <div class="col-md-10 align-to-center">
        <h3 class="clients-title">Profile</h3>
        <div ng-app="profileDataApp" ng-controller="profileDataCtrl">
          <hr />
          <div class="panel panel-default">
            <table class="table">
              <tr>
                <td>Name</td>
                <td><h6>{{ dataset_profile.name }}</h6></td>
                <td></td>
              </tr>
              <tr>
                <td>User Code</td>
                <td><h6>{{ dataset_user_code }}</h6></td>
                <td></td>
              </tr>
              <tr>
                <td>Mobile</td>
                <td><h6>{{ dataset_profile.mobile }}</h6>
                <td></td>
              </tr>
              <tr>
                <td>Email</td>
                <td><h6>{{ dataset_profile.email }}</h6>
                <td></td>
              </tr>
              <tr>
                <td>Account Created at</td>
                <td><p>{{ dataset_user_create_date_pretty }}</p>
                <td></td>
              </tr>
              <tr ng-show="dataset_profile.is_subscribed">
                <td>Newsletter Subscription</td>
                <td><button id="unsubscribe_button" class="btn btn-success" style="min-width:150px;" type="button" value="unsubscribe" onclick="unsubscribe()"><i class="fa fa-chain"></i> Subscribed</button>
                <td></td>
              </tr>
              <tr ng-hide="dataset_profile.is_subscribed">
                <td>Newsletter Subscription</td>
                <td><button id="subscribe_button" class="btn btn-danger" style="min-width:150px;" type="button" value="subscribe" onclick="subscribe()"><i class="fa fa-chain-broken"></i> Unsubscribed</button>
                <td></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
    <hr>
    <!-- Footer -->
    <?php include 'footer.php';?>
    <!-- Bootstrap core JavaScript
      ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>