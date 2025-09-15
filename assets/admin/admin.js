/**
 * Admin settings scripts
 *
 * @package    WordPress
 * @subpackage Previous page redirect for WooCommerce
 * @since      4.0
 */

(function ($) {

	/**
	 * Using global variable
	 *
	 * @param ppredirect_admin_data
	 */

	// sticky header/menu.
	$( window ).on( 'scroll', function () {
		if ( $( window ).scrollTop() > 40 ) {
			$( '.ppredirect-wrap' ).addClass( 'ppredirect-sticky-top' );
		} else {
			if ( $( '.ppredirect-wrap' ).hasClass( 'ppredirect-sticky-top' ) ) {
				$( '.ppredirect-wrap' ).removeClass( 'ppredirect-sticky-top' );
			}
		}
	});

	function handle_custom_field( element ){
		var val  = element.find( 'option:selected' ).val();
		var name = element.attr( 'name' );

		if ( val === 'custom' ) {
			$( '.custom_' + name ).show();
		} else {
			$( '.custom_' + name ).hide();
		}
	}
	$( document ).ready( function () {
		// is dismissible
		$(document).on( 'click', '.notice.is-dismissible', function(){
			$(this).hide( 'slow' ).remove();
		});

		$( '.redirect-type' ).on( 'change', function () {
			handle_custom_field( $(this) );
		});
		
		$( '.redirect-type' ).each( function () {
			handle_custom_field( $(this) );
		});
	});
})( jQuery );