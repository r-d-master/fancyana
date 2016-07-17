<?php include 'backdash_header.php';?>
<script>
  var backdashDataApp = angular.module('backdashDataApp', []);
  backdashDataApp.controller('backdashDataCtrl', function($scope, $http) {
    var data = $.param({
      authorized_access_identifier: "xWyZub6DCA4ARGWB"
    });
    var config = {
      headers : {
        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
      }
    }
    $http.post('../api/v1/getalluseremails', data, config)
    .success(function (data, status, headers, config) {
	    $scope.dataset_emails = data.results;
	    for (x in data.results) {
			var emailObject = data.results[x];
	        var emailId = emailObject.email;
	        TSEmails.emails.push({
	        	"email" : emailId
	        });
	    }
    })
    .error(function (data, status, header, config) {
      $scope.ResponseDetails = "Data: " + data +
        "<hr />status: " + status +
        "<hr />headers: " + header +
        "<hr />config: " + config;
    });
  });

    var TSEmails = {}
    $(document).ready(function() {
        TSEmails.emails = [];
    });

    function convertArrayOfObjectsToCSV(args) {
        var result, ctr, keys, columnDelimiter, lineDelimiter, data;

        data = args.data || null;
        if (data == null || !data.length) {
            return null;
        }

        columnDelimiter = args.columnDelimiter || ',';
        lineDelimiter = args.lineDelimiter || '\n';

        keys = Object.keys(data[0]);

        result = '';
        result += keys.join(columnDelimiter);
        result += lineDelimiter;

        data.forEach(function(item) {
            ctr = 0;
            keys.forEach(function(key) {
                if (ctr > 0) result += columnDelimiter;

                result += item[key];
                ctr++;
            });
            result += lineDelimiter;
        });

        return result;
    }

    function convertArrayOfObjectsToCSList(args) {
        var result, ctr, keys, columnDelimiter, lineDelimiter, data;

        data = args.data || null;
        if (data == null || !data.length) {
            return null;
        }

        columnDelimiter = ', ';
        lineDelimiter = ', ';

        keys = Object.keys(data[0]);

        result = '';

        data.forEach(function(item) {
            ctr = 0;
            keys.forEach(function(key) {
                if (ctr > 0) result += columnDelimiter;

                result += item[key];
                ctr++;
            });
            result += lineDelimiter;
        });
		result = result.slice(0, -2);
        result += '\n';

        return result;
    }

    function downloadCSV(args) {
        var data, filename, link;

        var csv = convertArrayOfObjectsToCSV({
            data: TSEmails.emails
        });
        if (csv == null) return;

        filename = args.filename || 'export.csv';

        if (!csv.match(/^data:text\/csv/i)) {
            csv = 'data:text/csv;charset=utf-8,' + csv;
        }
        data = encodeURI(csv);

        link = document.createElement('a');
        link.setAttribute('href', data);
        link.setAttribute('download', filename);
        link.click();
    }

    function downloadCSList(args) {
        var data, filename, link;

        var csv = convertArrayOfObjectsToCSList({
            data: TSEmails.emails
        });
        if (csv == null) return;

        filename = args.filename || 'export.csv';

        if (!csv.match(/^data:text\/csv/i)) {
            csv = 'data:text/csv;charset=utf-8,' + csv;
        }
        data = encodeURI(csv);

        link = document.createElement('a');
        link.setAttribute('href', data);
        link.setAttribute('download', filename);
        link.click();
    }

</script>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
Manage Email List - Users
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row" ng-app="backdashDataApp" ng-controller="backdashDataCtrl">
	<div class="col-md-12">
		<div class="portlet downloadlistblock">
			<a href='#' onclick='downloadCSV({ filename: "tailorsquare_users.csv" });' class="btn green tooltips" data-container="body" data-placement="bottom" data-original-title="Export as the popular CSV format. Use this for importing in an Email program"><i class="fa fa-download"></i> Export as CSV</a>
			<a href='#' onclick='downloadCSList({ filename: "tailorsquare_users.txt" });' class="btn green tooltips" data-container="body" data-placement="bottom" data-original-title="Export as a comma seperated text file. Use this to copy paste in an Email program's to or bcc Field"><i class="fa fa-download"></i> Export as List</a>
		</div>
		<div class="portlet box red">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-cogs"></i>Users
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
							<th>#</th>
							<th>Email</th>
						</tr>
						</thead>
					<tbody>
						<tr ng-repeat="x in dataset_emails">
							<td>{{ $index + 1 }}</td>
							<td>{{ x.email }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<?php include 'backdash_footer.php';?>
