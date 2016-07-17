<?php include 'commonhead.php';?>
  <script src="js/orders.js"></script>
  <script>
      var newOrder3DataApp = angular.module('newOrder3DataApp', []);
      newOrder3DataApp.controller('newOrder3DataCtrl', function($scope, $http) {
       // use $.param jQuery function to serialize data from JSON 
        var data = $.param({
          clothing_id: localStorage.order_dress_id
        });
        var config = {
          headers : {
            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
          }
        }
        $http.post('api/v1/getalldesignsanddesigngroupsbyclothing', data, config)
        .success(function (data, status, headers, config) {
          $scope.dataset_design_groups = data.design_groups;
          $scope.dataset_designs = data.designs;
          $scope.dataset_designs_in_group = {};
            for (x in data.design_groups){
              TSDesign.designIds[data.design_groups[x].design_group_id] = "";
              TSDesign.designStrings[data.design_groups[x].design_group_id] = "";
              TSDesign.designImgs[data.design_groups[x].design_group_id] = "";
              TSDesign.designGroupIds.push(data.design_groups[x].design_group_id);
              TSDesign.designGroupStrings[data.design_groups[x].design_group_id] = data.design_groups[x].design_group_name;
              $scope.dataset_designs_in_group[data.design_groups[x].design_group_id] = [];
            }
            for (y in data.designs){
              TSDesign.designIdToString[data.designs[y].design_id] = data.designs[y].design_name;
              TSDesign.designIdToImg[data.designs[y].design_id] = "uploadedimages/design/"+data.designs[y].design_image+".jpg";
              $scope.dataset_designs_in_group[data.designs[y].design_group_id].push(data.designs[y]);
            }

            console.log($scope.dataset_designs_in_group);

          setTimeout(function(){
            layoutOrderTabs();            
          }, 1000);
        })
        .error(function (data, status, header, config) {
          $scope.ResponseDetails = "Data: " + data +
            "<hr />status: " + status +
            "<hr />headers: " + header +
            "<hr />config: " + config;
        });
      });
  </script>
  <script>
    var TSDesign = {}
    $(document).ready(function() {
        TSDesign.designIds = {};
        TSDesign.designStrings = {};
        TSDesign.designImgs = {};
        TSDesign.designGroupIds = [];
        TSDesign.designGroupStrings = {};
        TSDesign.designIdToString = {};
        TSDesign.designIdToImg = {};
    });

    function tapNewOrderBack3() {
        window.location.href="neworder2.php";
    }
    function tapNewOrderNext3() {
      var missingDesign = false;
      for ( x in TSDesign.designGroupIds) {
        var dg_id = TSDesign.designGroupIds[x];
        if(!TSDesign.designIds[dg_id]){
          missingDesign = true;
        }
      }

      if (missingDesign){
        alert("Please select a design for all categories");
      } else {
        localStorage.setItem("order_design_ids", JSON.stringify(TSDesign.designIds));
        localStorage.setItem("order_design_strings", JSON.stringify(TSDesign.designStrings));
        localStorage.setItem("order_design_imgs", JSON.stringify(TSDesign.designImgs));
        localStorage.setItem("order_design_group_ids", JSON.stringify(TSDesign.designGroupIds));
        localStorage.setItem("order_design_group_strings", JSON.stringify(TSDesign.designGroupStrings));
        window.location.href="neworder4.php";          
      }
    }
    function selectDesign(designObject){
      var designObjectId = designObject.id;
      var designGroupId = designObject.alt;
      var designIdVal = designObjectId.slice(7);
      if(designIdVal.slice(0,2) == "0_") {
        designIdVal = "0";
        TSDesign.designStrings[designGroupId] = "Copy From My Garment";
        TSDesign.designImgs[designGroupId] = "uploadedimages/design/design_0.jpg";
      } else {
        TSDesign.designStrings[designGroupId] = TSDesign.designIdToString[designIdVal];
        TSDesign.designImgs[designGroupId] = TSDesign.designIdToImg[designIdVal];
      }
      TSDesign.designIds[designGroupId] = designIdVal;
      $("#tab_"+designGroupId+" .tabContentImgActive").removeClass("tabContentImgActive");      
      $("#"+designObjectId).addClass("tabContentImgActive");
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

  <!-- Begin Content -->
  <section id="design" ng-app="newOrder3DataApp" ng-controller="newOrder3DataCtrl">
    <div class="container align-to-center">
      <div class="col-lg-12">
        <div id="container">
          <div id="parentHorizontalTab">
            <div class="resp-tabs-container hor_1">
              <div>
                <div class="checkout-wrap">
                  <ul class="checkout-bar">
                     <li class="visited"><a href="neworder.php">Dress</a></li>
                     <li class="visited"><a href="neworder2.php">Fabric</a></li>
                     <li class="active"><a href="neworder3.php">Design</a></li>
                    <li class="">Addons</li>
                    <li class="">Measurements</li>
                    <li class="">Pickup/Delivery</li>
                    <li class="">Confirm</li>
                  </ul>
                </div>

                <div class="row">
                  <div class="orderContentStart"></div>
                  <div id="neworder_fabric_online_tabs">
                    <ul class="tabs">
                      <li ng-repeat="x in dataset_design_groups" ng-class="{active: $index == 0}" rel="tab_{{x.design_group_id}}" id="new_order_design_group_{{ x.design_group_id }}">{{ x.design_group_name }}</li>
                    </ul>

                    <div class="tab_container">
                      <div ng-repeat="x in dataset_design_groups">
                        <h3 class="tab_drawer_heading" ng-class="{d_active: $index == 0}" rel="tab_{{x.design_group_id}}" >{{ x.design_group_name }}</h3>
                        <div id="tab_{{x.design_group_id}}" class="tab_content">
                          <div class="row" align="left">
                            <div class="imageFigureHolder">
                              <figure>
                                <img id="design_0_{{ x.design_group_id }}" alt="{{ x.design_group_id }}" src="uploadedimages/design/design_0.jpg" class="tabContentImg" height="200px" width="170px" onclick="selectDesign(this)" />
                                <figcaption style="text-align:center">Copy From My Garment</figcaption>
                              </figure>
                            </div>
                            <div ng-repeat="y in dataset_designs_in_group[x.design_group_id]" class="imageFigureHolder">
                              <figure>
                                <img id="design_{{ y.design_id }}" alt="{{ y.design_group_id }}" src="uploadedimages/design/{{ y.design_image }}.jpg" class="tabContentImg" height="200px" width="170px" onclick="selectDesign(this)" />
                                <figcaption style="text-align:center">{{ y.design_name }}</figcaption>
                              </figure>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
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
      <input type="submit" name="neworder_back_3" value="Back" id="neworder_back_3" class="btn btn-danger" onclick="tapNewOrderBack3()" />
      <input type="submit" name="neworder_next_3" value="Next" id="neworder_next_3" class="btn btn-danger" onclick="tapNewOrderNext3()" />
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