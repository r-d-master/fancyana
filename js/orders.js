function checkOffset() {
	var mq = window.matchMedia("(min-width: 768px)").matches;

    if($('#order-nav').offset().top + $('#order-nav').height() >= $('#footer').offset().top) {
        $('#order-nav').css('position', 'absolute');
        $('#order-nav').css('padding-bottom', '5px');
    }
    if($(document).scrollTop() + window.innerHeight < $('#footer').offset().top) {
        $('#order-nav').css('position', 'fixed'); // restore when you scroll up
        if (mq) {
	        $('#order-nav').css('padding-bottom', '5px');
        } else {
	        $('#order-nav').css('padding-bottom', '45px');
        }
    }
}
$(document).scroll(function() {
    checkOffset();
});
$(document).load(function(){
    if($('#order-nav').offset().top + $('#order-nav').height() >= $('#footer').offset().top) {
        $('#order-nav').css('position', 'absolute');
        $('#order-nav').css('padding-bottom', '5px');
    }
});