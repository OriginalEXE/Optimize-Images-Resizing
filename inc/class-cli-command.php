<?php

// Exit if accessed directly
! defined( 'ABSPATH' ) && exit;

WP_CLI::add_command( 'optimize-images-resizing', 'OIR_CLI_Command' );

/**
* CLI Command for cleaning up image library.
*/
class OIR_CLI_Command extends WP_CLI_Command {

  /**
  * Bulk clean up extra image sizes
  *
  * @synopsis [--posts-per-page] [--offset]
  * @subcommand clean-library
  *
  * @param array $args
  * @param array $assoc_args
  */
  public function clean_library( $args, $assoc_args ) {

    global $wpdb, $wp_object_cache;

    if ( ! empty( $assoc_args['posts-per-page'] ) ) {

      $posts_per_page = absint( $assoc_args['posts-per-page'] );

    } else {

      $posts_per_page = 10;

    }

    if ( ! empty( $assoc_args['offset'] ) ) {

      $offset = absint( $assoc_args['offset'] );

    } else {

      $offset = 0;

    }

    $upload_dir = wp_upload_dir();

    while (true) {

      $args = array(
        'fields'         => 'ids',
        'offset'         => $offset,
        'post_mime_type' => 'image',
        'post_status'    => 'any',
        'post_type'      => 'attachment',
        'posts_per_page' => $posts_per_page
      );

      $query = new WP_Query( $args );

      if ( $query->have_posts() ) {

        foreach ($query->posts as $attachment_id) {

          $meta = wp_get_attachment_metadata($attachment_id);

          if ( empty( $meta['file'] ) ) {

            continue;

          }

          $file_path = str_replace( basename( $meta['file'] ), '', trailingslashit( $upload_dir['basedir'] ) . $meta['file'] );

          if ( empty( $meta['sizes'] ) || ! is_array( $meta['sizes'] ) ) {

            continue;

          }

          // Don't remove images if they are used in default image sizes
          // (happens when custom image size matches the dimensions of the default ones)
          $do_not_delete = array();

          $allowed_sizes = apply_filters( 'image_size_names_choose', array() );

          $allowed_sizes = array_merge( array_keys( $allowed_sizes ), array(
            'thumbnail',
            'medium',
            'large'
          ));

          foreach ( $meta['sizes'] as $size => $params ) {

            // we don't want to delete thumbnails, they are used in admin area
            if ( in_array( $size, $allowed_sizes ) ) {

              $file = realpath( $file_path . $params['file'] );
              $do_not_delete[] = $file;

              continue;

            }

            $file = realpath( $file_path . $params['file'] );

            if ( ! in_array( $file, $do_not_delete ) && is_readable( $file ) ) {

              unlink( $file );
              unset( $meta['sizes'][$size] );

            }

          }

          wp_update_attachment_metadata( $attachment_id, $meta );
        }
      }

      else {

        break;

      }

      WP_CLI::log( 'Processed ' . ( $query->post_count + $offset ) . '/' . $query->found_posts . ' images' );

      $offset += $posts_per_page;

      usleep( 500 );

      // Avoid running out of memory
      $wpdb->queries = array();

      if ( is_object( $wp_object_cache ) ) {

        $wp_object_cache->group_ops = array();
        $wp_object_cache->stats = array();
        $wp_object_cache->memcache_debug = array();
        $wp_object_cache->cache = array();

        if ( is_callable( $wp_object_cache, '__remoteset' ) ) {

          call_user_func( array( $wp_object_cache, '__remoteset' ) ); // important

        }

      }

    }

    // Print a success message
    WP_CLI::success( "Done!" );

  }

}
