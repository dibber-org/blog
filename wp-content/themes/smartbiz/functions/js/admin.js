jQuery(document).ready(function($) {
	$( 'div.options-body' ).hide();
	$( 'h3' ).click(function() {
		$(this).toggleClass("open");
		$(this).next("div").slideToggle( '1000' );
		return false;
	});
});
