jQuery( function( $ ) {

  "use strict";

  var $body = $( 'body' ),
    $button_new_cleanup = $( '#oir-remove-image-sizes' ),
    $button_old_cleanup = $( '#oir-resume-remove-image-sizes' ),
    $buttons = $( '#oir-buttons' ),
    $message = $( '#oir-status-message' ),
    $log = $( '#oir-log' ),
    record_log = false;

  $button_new_cleanup.on( 'click', function( e ) {

    e.preventDefault();

    $buttons.hide();
    $message
      .html( oir_plugin.l10n.cleanup_progress )
      .show();

    record_log = $( '#oir-keep-the-log' ).is( ':checked' );

    ajax_request();

  });

  $button_old_cleanup.on( 'click', function( e ) {

    e.preventDefault();

    $buttons.hide();
    $message
      .html( oir_plugin.l10n.cleanup_progress )
      .show();

    record_log = $( '#oir-keep-the-log' ).is( ':checked' );

    var page = parseInt( $button_old_cleanup.attr( 'data-page' ), 10 );

    ajax_request( page );

  });

  $body.on( 'click', '.js-oir-show-log', function( e ) {

    e.preventDefault();

    $log.stop().slideToggle();

  });

  function ajax_request( paged, removed ) {

    paged = 'undefined' == typeof paged ? 1 : parseInt( paged, 10 );
    removed = 'undefined' == typeof removed ? 0 : parseInt( removed, 10 );

    $.post(
      ajaxurl,
      {
        action: 'oir_remove_image_sizes',
        nonce: oir_plugin.nonce,
        paged: paged,
        removed: removed,
        record_log: record_log
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

          if ( record_log && 0 !== parseInt( response.removed, 10 ) && response.removed_log.length ) {

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

        ajax_request( ++response.paged, response.removed );

      },
      'json'
    );

  }

});
