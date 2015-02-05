jQuery( function( $ ) {

	"use strict";

	var $body    = $( 'body' ),
		$button  = $( '#oir-remove-image-sizes' ),
		$message = $( '#oir-status-message' );

	$button.on( 'click', function( e ) {

		e.preventDefault();

		$button.hide();
		$message
			.html( 'Cleanup in progress, leave this page open!' )
			.show();

		ajax_request();

	});

	function ajax_request( paged ) {

		paged = 'undefined' == typeof paged ? 1 : parseInt( paged, 10 );

		$.post(
			ajaxurl,
			{
				action : 'oir_remove_image_sizes',
				nonce  : oir_plugin.nonce,
				paged  : paged
			},
			function( response ) {
				
				if ( false === response.success ) {

					$message
						.html( oir_plugin.l10n.something_wrong )
						.show();

				} else if ( true === response.success ) {

					if ( true === response.finished ) {

						$message
							.html( oir_plugin.l10n.process_finished );

					} else {

						var completed = ( response.paged * 10 > response.found ) ? response.found : response.paged * 10;

						$message
							.html( oir_plugin.l10n.cleanup_progress + ' ' + completed + ' / ' + response.found );

						ajax_request( ++response.paged );


					}					

				} else {

					$message
						.html( oir_plugin.l10n.something_wrong )
						.show();

				}

			},
			'json'
		);

	}

});