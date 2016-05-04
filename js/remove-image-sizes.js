jQuery( function( $ ) {

	"use strict";

	var $body = $( 'body' ),
		$button = $( '#oir-remove-image-sizes' ),
		$message = $( '#oir-status-message' ),
		$log = $( '#oir-log' );

	$button.on( 'click', function( e ) {

		e.preventDefault();

		$button.hide();
		$message
			.html( oir_plugin.l10n.cleanup_progress )
			.show();

		ajax_request();

	});

	$body.on( 'click', '.js-oir-show-log', function( e ) {

		e.preventDefault();

		$log.stop().slideToggle();

	});

	function ajax_request( paged, removed, removed_log ) {

		paged = 'undefined' == typeof paged ? 1 : parseInt( paged, 10 );
		removed = 'undefined' == typeof removed ? 0 : parseInt( removed, 10 );
		removed_log = 'undefined' == typeof removed_log ? [] : removed_log;

		$.post(
			ajaxurl,
			{
				action     : 'oir_remove_image_sizes',
				nonce      : oir_plugin.nonce,
				paged      : paged,
				removed    : removed,
				removed_log: removed_log
			},
			function( response ) {

				if ( true !== response.success ) {

					// Looks like something went wrong

					$message
						.html( oir_plugin.l10n.something_wrong )
						.show();

					return;

				}

				if ( true === response.finished ) {

					// Cleanup has finished

					var message = 0 === parseInt( response.removed, 10 ) ? oir_plugin.l10n.nothing_to_remove : oir_plugin.l10n.process_finished.replace( '%d', '<a href="#" class="js-oir-show-log">' + response.removed + '</a>' );

					$message
						.html( message );

					if ( 0 !== parseInt( response.removed, 10 ) && response.removed_log.length ) {

						var logHtml = '<pre>';

						$.each( response.removed_log, function( i, file ) {

							logHtml += file + '\n';

						});

						logHtml += '</pre>';

						$log.html( logHtml )

					}

					return;

				}

				// Cleanup still in progress

				var completed = ( response.paged * 10 > response.found ) ? response.found : response.paged * 10;

				$message
					.html( oir_plugin.l10n.cleanup_progress + ' ' + completed + ' / ' + response.found );

				ajax_request( ++response.paged, response.removed, response.removed_log );

			},
			'json'
		);

	}

});