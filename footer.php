<footer id="footer">
  <script>
    $(document).ready(function() {
      var request;
      $("#add_subscriber_form").submit(function(event){
          if (request) {
              request.abort();
          }
          var $form = $(this);
          var $inputs = $form.find("input, select, button, textarea");
          var serializedData = $form.serialize();
          $inputs.prop("disabled", true);
          request = $.ajax({
              url: "api/v1/addsubscriber",
              type: "post",
              data: serializedData
          });
          request.done(function (response, textStatus, jqXHR){
              if(response.error){
                console.error("The following error occurred: " + response.message);
              } else{
                console.log("Successfully Subscribed");
                alert("You have successfully subscribed to our Newsletter. Watch out for styling tips, news and great offers coming soon to your inbox!")
              }
          });
          request.fail(function (jqXHR, textStatus, errorThrown){
              console.error("The following error occurred: "+textStatus, errorThrown);
          });
          request.always(function () {
              $inputs.prop("disabled", false);
          });
          event.preventDefault();
      });
    });
  </script>
  <div class="container">
    <div class="col-lg-4">
      <br />
      <br />
      <p>
      <div style="display:inline-block;">Follow us on:</div>
      <div class="footerSocialLinks">
        <ul style="vertical-align: text-top;">
          <li><a href="https://twitter.com/thetailorsquare"><i class="fa fa-twitter"></i></a></li>
          <li><a href="http://facebook.com/tailorsquare.in "><i class="fa fa-facebook-f"></i></a></li>
          <li><a href="https://plus.google.com/u/1/101833697168696363719/posts "><i class="fa fa-google-plus"></i></a></li>
          <li><a href="https://www.youtube.com/channel/UCzeOHqIKrf4dZR3K3q9LHXw "><i class="fa fa-youtube"></i></a></li>
        </ul>
      </div>
      </p>
      <br />
      <p> Subscribe to our Newsletter and get styling tips, news and great offers to your inbox
      </p>
      <br>
      <span>
        <form id="add_subscriber_form" method="post">
          <input type="email" id="subscriber_email" name="email" placeholder="Your Email" style="color:black; line-height: 28px;" />
          <input type="submit" class="btn btn-default" style="padding:3px" value="Subscribe">
        </form>
      </span>
    </div>
    <div class="col-lg-4 center-block">
      <br />
      <br />
      <p style="display:block; text-align:center;">Office Address</p>
      <br />
      <p style="display:inline-block;">Corporate Address:<br />
        Shop 06, Style Plaza Sector 15 Gurgaon - 122001 Haryana
      </p>
      <p style="display:inline-block;">Branch Office:<br />
        Lower Ground Floor, W-5/2, DLF City Phase-III, Gurgaon-122002
      </p>
    </div>
    <div class="col-lg-4 ">
      <br />
      <br />
      <p style="display:block; margin-left:30px">Customer Care</p>
      <br />
      <div style="display:block; text-align:left;">
        <div class="col-lg-2" style="float:left;text-align:left">
          <img src="img/telephone.png" />
        </div>
        <div class="col-lg-5" >
          +91-9990934932<br />
          +91-9650563038
        </div>
        <br />
        <p style="display:inline-block;">Whatsapp &nbsp;&nbsp;<i class="fa fa-whatsapp"></i> 9015536778</p>
        <br />
        <p style="display:inline-block;">Email &nbsp;&nbsp;<i class="fa fa-envelope"></i> <a class="footerurl" href="mailto:hello@tailorsquare.in">hello@tailorsquare.in</a></p>
        <br />
        <p style="display:inline-block;"><a class="footerurl" href="terms_n_conditions.php" target="_blank">Privacy Policy &amp; Terms of Use</a></p>
      </div>
    </div>
    <div style="clear:both;">
      <br />
      <br />
    </div>
  </div>
</footer>
<div style="width:100%; display:inline-block; text-align:center;">&copy; Copyright TailorSquare.in 2015 | All Rights Reserved.</div>
