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

				add_action( 'admin_init', array( self::$instance, 'add_media_settings' ) );
				add_action( 'wp_ajax_oir_remove_image_sizes', array( self::$instance, 'remove_image_sizes' ) );
				add_action( 'admin_enqueue_scripts', array( self::$instance, 'enqueue_assets' ) );

				// Upon image upload, only generate default sizes
				add_filter( 'intermediate_image_sizes_advanced', array( self::$instance, 'remove_intermediate_sizes' ), 10, 1 );

			}

			return self::$instance;

		}

		// Filter the sizes that get created initially		
		public function remove_intermediate_sizes( $sizes ) {

			$allowed_sizes = apply_filters( 'image_size_names_choose', array() );

			$allowed_sizes = array_merge( array_keys( $allowed_sizes ), array( 'thumbnail', 'medium', 'large' ) );

			return array_intersect_key( $sizes, array_flip( $allowed_sizes ) );

		}

		// register our media settings
		public function add_media_settings() {

			add_settings_field( 'oir_media_settings', __( 'Remove image sizes', 'oir_plugin' ), array( $this, 'media_settings_output' ), 'media', 'default' );

		}

		// add a small output to media settings screen
		public function media_settings_output() {

			echo '<button id="oir-remove-image-sizes" class="button">' . __( 'Start cleanup', 'oir_plugin' ) . '</button>';
			echo '<p id="oir-status-message"></p>';
			echo '<p class="description">' . __( 'Use this to clean up all of the image sizes that were generated for your existing media. They will later be generated only when needed, preserving the space.', 'oir_plugin' ) . '</p>';

		}

		// cleans up extra image sizes when called via ajax
		public function remove_image_sizes( $__attachment_id ) {

			$paged = ! empty( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;

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

					$allowed_sizes = apply_filters( 'image_size_names_choose', array() );

					$allowed_sizes = array_merge( array_keys( $allowed_sizes ), array( 'thumbnail', 'medium', 'large' ) );

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

							unset( $meta['sizes'][ $size ] );

						}

					}

					wp_update_attachment_metadata( $attachment_id, $meta );

				}

			}

			if ( ! $__attachment_id ) {

				$response = array(
					'finished' => $finished,
					'found'    => $found,
					'paged'    => $paged,
					'success'  => true,
				);

				wp_send_json( $response );

			}

		}

		// add js and css needed on media settings screen
		public function enqueue_assets( $hook ) {

			if ( 'options-media.php' !== $hook ) {

				// we only need this script in media settings
				return;

			}

			wp_enqueue_script( 'oir_remove_image_sizes', OIR_JS_URL . 'remove-image-sizes.js' );

			$localize = array(
				'l10n'  => array(
					'something_wrong'  => __( 'Something went wrong, please try again or contact the developer!', 'oir_plugin' ),
					'process_finished' => __( 'Process finished, your media folder should now be much more lighter.', 'oir_plugin' ),
					'cleanup_progress' => __( 'Cleanup in progress, leave this page open!', 'oir_plugin' ),
				),
				'nonce' => wp_create_nonce( 'oir-nonce' ),
			);

			wp_localize_script( 'oir_remove_image_sizes', 'oir_plugin', $localize );

			wp_enqueue_style( 'oir_remove_image_sizes', OIR_CSS_URL . 'remove-image-sizes.css' );

		}

	}

endif;

OIR_Remove_Image_Sizes::instantiate();