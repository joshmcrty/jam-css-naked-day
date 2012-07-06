// Insert the CSS Naked Day message at the top of the page
;( function( $ ) {
	$( document ).ready( function() {
		if ( jamCssNakedDayOptions.displayMessage === 'yes' ) {
			$( 'body' ).prepend( jamCssNakedDayOptions.message );
		}
		if ( jamCssNakedDayOptions.scriptMode === 'js' ) {
			$( 'style' ).remove();
			$( 'link[rel="stylesheet"]' ).remove();
			$( document ).find( '*' ).removeAttr( 'style' );
		}
	});
})( jQuery );