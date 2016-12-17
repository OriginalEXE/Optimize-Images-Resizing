<?php

// Exit if accessed directly
! defined( 'ABSPATH' ) && exit;

// Check if class already exists
if ( ! class_exists( 'OIR_Remove_Image_Sizes' ) ) :

  final class OIR_Remove_Image_Sizes {

    // Will hold the only instance of our main plugin class
    private static $instance;

    // Instantiate the class and set up stuff
    public static function instantiate() {

      if ( ! isset( self::$instance ) && ! ( self::$instance instanceof OIR_Remove_Image_Sizes ) ) {

        self::$instance = new OIR_Remove_Image_Sizes();

      }

      return self::$instance;

    }

    public function __construct() {

      add_action( 'admin_menu', array( $this, 'add_tools_subpage' ) );
      add_action( 'wp_ajax_oir_remove_image_sizes', array( $this, 'remove_image_sizes' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

      // Upon image upload, only generate default sizes
      add_filter( 'intermediate_image_sizes_advanced', array( $this, 'remove_intermediate_sizes' ), 10, 1 );

    }

    // Returns image sizes that we don't want to remove
    public function get_ignored_image_sizes() {

      $ignored_sizes = apply_filters( 'image_size_names_choose', array() );

      $ignored_sizes = array_merge( array_keys( $ignored_sizes ), array( 'thumbnail', 'medium', 'large' ) );

      return apply_filters( 'oir_ignored_sizes', $ignored_sizes );

    }

    // Filter the sizes that get created initially
    public function remove_intermediate_sizes( $sizes ) {

      return array_intersect_key( $sizes, $this->get_ignored_image_sizes() );

    }

    public function add_tools_subpage() {

      add_submenu_page(
        'tools.php',
        __( 'Remove image sizes', 'optimize-images-resizing' ),
        __( 'Remove image sizes', 'optimize-images-resizing' ),
        'manage_options',
        'optimize-images-resizing',
        array( $this, 'tools_subpage_output' )
      );

    }

    // add a small output to media settings screen
    public function tools_subpage_output() {

      $cleanup_in_progress = get_option( 'oir_cleanup_progress_page', false );

      if ( false !== $cleanup_in_progress ) {

        $cleanup_in_progress = absint( $cleanup_in_progress );

      }

      ?>

      <div class="wrap">
        <h1><?php _e( 'Remove image sizes', 'optimize-images-resizing' ); ?></h1>
        <p>
          <label>
            <input type="checkbox" id="oir-keep-the-log" value="1">
            <?php _e( 'Keep a record of removed image sizes (not recommended for huge media libraries)' ); ?>
          </label>
        </p>

        <div id="oir-buttons">

          <?php if ( $cleanup_in_progress ) : ?>

            <button
            id="oir-resume-remove-image-sizes"
            class="button button-primary"
            data-page="<?php echo esc_attr( $cleanup_in_progress ); ?>"
            ><?php _e( 'Resume old cleanup', 'optimize-images-resizing' ); ?></button>

          <?php endif; ?>

          <button id="oir-remove-image-sizes" class="button"><?php _e( 'Start new cleanup', 'optimize-images-resizing' ); ?></button>

        </div>
        <p id="oir-status-message"></p>
        <p class="description"><?php _e( 'Click the button above to remove redundant image sizes. You only need to do this once.', 'optimize-images-resizing' ); ?></p>
        <div id="oir-log"></div>
      </div>

      <?php

    }

    // cleans up extra image sizes when called via ajax
    public function remove_image_sizes( $__attachment_id ) {

      $paged = ! empty( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
      $removed = ! empty( $_POST['removed'] ) ? absint( $_POST['removed'] ) : 0;
      $record_log = ! empty( $_POST['record_log'] ) ? 'true' === $_POST['record_log'] : false;
      $removed_in_current_request = array();

      update_option( 'oir_cleanup_progress_page', $paged );

      if ( ! $__attachment_id ) {

        check_ajax_referer( 'oir-nonce', 'nonce' );

        $args = array(
          'fields'         => 'ids',
          'paged'          => $paged,
          'post_mime_type' => 'image',
          'post_status'    => 'any',
          'post_type'      => 'attachment',
          'posts_per_page' => 10,
        );

      } else {

        $args = array(
          'fields'         => 'ids',
          'post_mime_type' => 'image',
          'post_status'    => 'any',
          'post_type'      => 'attachment',
          'post__in'       => array( absint( $__attachment_id ) ),
        );

      }

      $query = new WP_Query( $args );

      $found = absint( $query->found_posts );
      $finished = empty( $query->posts ) ? true : false;

      if ( ! $finished ) {

        $upload_dir = wp_upload_dir();

        foreach ( $query->posts as $attachment_id ) {

          $meta = wp_get_attachment_metadata( $attachment_id );

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

          $ignored_sizes = $this->get_ignored_image_sizes();

          foreach ( $meta['sizes'] as $size => $params ) {

            $file = realpath( $file_path . $params['file'] );

            // we don't want to delete thumbnails, they are used in admin area
            if ( in_array( $size, $ignored_sizes ) ) {

              $do_not_delete[] = $file;

              continue;

            }

            if ( ! in_array( $file, $do_not_delete ) && is_readable( $file ) ) {

              unlink( $file );

              unset( $meta['sizes'][ $size ] );

              $removed++;

              $removed_in_current_request[] = $file;

            }

          }

          wp_update_attachment_metadata( $attachment_id, $meta );

        }

        if ( $record_log ) {

          $removed_so_far = get_option( 'oir_removed_log', array() );

          $removed_log = array_merge( $removed_so_far, $removed_in_current_request );

          update_option( 'oir_removed_log', $removed_log );

        }

      } else {

        delete_option( 'oir_cleanup_progress_page' );

      }

      if ( ! $__attachment_id ) {

        $response = array(
          'finished' => $finished,
          'found' => $found,
          'paged' => $paged,
          'removed' => $removed,
          'success' => true,
        );

        if ( $record_log && $finished ) {

          $removed_so_far = get_option( 'oir_removed_log', array() );

          $response['removed_log'] = $removed_so_far;

        }

        wp_send_json( $response );

      }

    }

    // add js and css needed on media settings screen
    public function enqueue_assets( $hook ) {

      if ( 'tools_page_optimize-images-resizing' !== $hook ) {

        // we only need this script in media settings
        return;

      }

      wp_enqueue_script( 'oir_remove_image_sizes', OIR_JS_URL . 'remove-image-sizes.js' );

      $localize = array(
        'l10n'  => array(
          'something_wrong'  => __( 'Something went wrong, please try again or contact the developer!', 'optimize-images-resizing' ),
          'process_finished' => __( 'Cleanup was successfully completed. Number of images removed: %d.', 'optimize-images-resizing' ),
          'nothing_to_remove' => __( 'There was nothing to clean up, looks like you have no redundant image sizes. Good job!', 'optimize-images-resizing' ),
          'cleanup_progress' => __( 'Cleanup in progress, leave this page open!', 'optimize-images-resizing' ),
        ),
        'nonce' => wp_create_nonce( 'oir-nonce' ),
      );

      wp_localize_script( 'oir_remove_image_sizes', 'oir_plugin', $localize );

      wp_enqueue_style( 'oir_remove_image_sizes', OIR_CSS_URL . 'remove-image-sizes.css' );

    }

  }

endif;

OIR_Remove_Image_Sizes::instantiate();

/**
 * WP CLI Commands
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {

  require_once 'inc/class-cli-command.php';

}
