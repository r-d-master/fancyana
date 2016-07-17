<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta content="utf-8" http-equiv="encoding">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta name="HandheldFriendly" content="true">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="google-signin-client_id" content="199372681453-semlib8h0rv5fl1hvop2otfd022mh590.apps.googleusercontent.com">
    <meta name="description" content="One stop for Alteration/Stitching Services at your Doorstep">
    <meta name="author" content="Tailor Square">
    <link rel="icon" href="favicon.png">
    <title>Tailor Square - One stop for Alteration/Stitching Services at your Doorstep</title>
    <link href="css/components.css" rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/tsquare.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">
    <link href="css/header.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
    <link href="css/imagetabs.css" rel="stylesheet">
    <link href="css/ordertabs.css" rel="stylesheet">
    <link href="css/myorders.css" rel="stylesheet">
    <link href="css/footer.css" rel="stylesheet">
    <link href="css/slider.css" rel="stylesheet">   
    <link href="css/toggle.css" rel="stylesheet">
    <link href="css/reviews.css" rel="stylesheet"> 
    <link href="css/home.css" rel="stylesheet"> 
    <link href="css/tsforms.css" rel="stylesheet"> 
    <link href="css/redradiocheck.css" rel="stylesheet"> 
    <link href="css/bootstrap-datetimepicker.css" rel="stylesheet"> 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js"></script>
    <script src="js/tsquare.js"></script>
    <script src="js/ordertabs.js"></script>
    <script src="js/metronic.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
    <script src="https://apis.google.com/js/platform.js?onload=onGLoad"></script>
    <script src="js/glogin.js"></script>
    
    <script type="text/javascript">
        function getPrettyDate(timestamp) {
            var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            var date = new Date(timestamp.replace(/-/g, '/'));
            var d = date.getDate();
            var mi = date.getMonth();
            var y = date.getFullYear();
            var m = monthNames[mi];
            var prettyDate = d + "-" + m + "-" + y;
            return prettyDate;
        }

        function getPrettyDateTime(timestamp) {
            var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            var date = new Date(timestamp.replace(/-/g, '/'));
            var d = date.getDate();
            var mi = date.getMonth();
            var y = date.getFullYear();
            var m = monthNames[mi];
            var hh = date.getHours();
            var mm = date.getMinutes();
            var h = hh % 12;
            var tt = "am";
            if (hh >= 12) {
                tt = "pm"
            }
            var prettyDateTime = h + ":" + mm + " " + tt + " | " + d + "-" + m + "-" + y;
            return prettyDateTime;
        }

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

    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
    var $_Tawk_API={},$_Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/565d615031c15b6e1f07a982/default';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
    })();
    </script>
    <!--End of Tawk.to Script-->