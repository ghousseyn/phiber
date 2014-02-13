$(document).ready(function() {
	$('.bbtn span').hide();
 $( document ).tooltip({
	items: "div,[ttip]",
	position:{my:"top",
		  of:"div .active",
		  collision:"fit",
		  within:"body"},
	content: function() {
		return $(this).find('span').html();
	}	
});
});
