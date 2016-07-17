$(document).ready(function(){
	$(".navbarMenuItem").click(function(){
        $(".navbarMenuItem.active").removeClass("active");
        $(this).addClass("active");
    });
});
