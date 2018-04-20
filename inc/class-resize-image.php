<?php

// Exit if accessed directly
! defined( 'ABSPATH' ) && exit;

// Check if class already exists
if ( ! class_exists( 'OIR_Resize_Image' ) ) :

  final class OIR_Resize_Image {

    // Will hold the only instance of our main plugin class
    private static $instance;

    // Instantiate the class and set up stuff
    public static function instantiate() {

      if ( ! isset( self::$instance ) && ! ( self::$instance instanceof OIR_Resize_Image ) ) {

        self::$instance = new OIR_Resize_Image();

      }

      return self::$instance;

    }

    public function __construct() {

      add_filter( 'image_downsize', array( $this, 'image_downsize' ), 10, 3 );

    }

    // we hook into the filter, check if image size exists, generate it if not and then bail out
    public function image_downsize( $out, $id, $size ) {

      // we don't handle this
      if ( is_array( $size ) ) return false;

      $meta = wp_get_attachment_metadata( $id );
      $wanted_width = $wanted_height = 0;

      if ( empty( $meta['file'] ) ) {
		
        // Check if we can replace with a placeholder image
        if ( defined('OIR_PLACEHOLDER_IMAGE_ID') ) {
          $meta = wp_get_attachment_metadata( OIR_PLACEHOLDER_IMAGE_ID );
          if ( empty( $meta['file'] ) ) return false;
          $id = OIR_PLACEHOLDER_IMAGE_ID;
        } else {
          return false;	
        }
      }

      // get $size dimensions
      global $_wp_additional_image_sizes;

      if ( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $size ] ) ) {

        $wanted_width = $_wp_additional_image_sizes[ $size ]['width'];
        $wanted_height = $_wp_additional_image_sizes[ $size ]['height'];
        $wanted_crop = isset( $_wp_additional_image_sizes[ $size ]['crop'] ) ? $_wp_additional_image_sizes[ $size ]['crop'] : false;

      } else if ( in_array( $size, array( 'thumbnail', 'medium', 'large' ) ) ) {

        $wanted_width  = get_option( $size . '_size_w' );
        $wanted_height = get_option( $size . '_size_h' );
        $wanted_crop   = ( 'thumbnail' === $size ) ? (bool) get_option( 'thumbnail_crop' ) : false;

      } else {

        // unknown size, bail out
        return false;

      }

      if ( 0 === absint( $wanted_width ) && 0 === absint( $wanted_height ) ) {

        return false;

      }

      $intermediate = image_get_intermediate_size( $id, $size );
	    
      // check if the image size was changed and needs to regenerate
	    if ( $intermediate && ( $intermediate['width'] == $wanted_width && $intermediate['height'] ==  $wanted_height ) ) {

        $img_url = wp_get_attachment_url( $id );
        $img_url_basename = wp_basename( $img_url );

        $img_url = str_replace( $img_url_basename, $intermediate['file'], $img_url );
        $result_width = $intermediate['width'];
        $result_height = $intermediate['height'];

        return array(
          $img_url,
          $result_width,
          $result_height,
          true,
        );

      } else {

        // image size not found, create it
        $attachment_path = get_attached_file( $id );

        $image_editor = wp_get_image_editor( $attachment_path );

        if ( ! is_wp_error( $image_editor ) ) {

          $image_editor->resize( $wanted_width, $wanted_height, $wanted_crop );
          $result_image_size = $image_editor->get_size();

          $result_width = $result_image_size['width'];
          $result_height = $result_image_size['height'];

          $suffix = $result_width . 'x' . $result_height;
          $filename = $image_editor->generate_filename( $suffix );

          $image_editor->save( $filename );

          $result_filename = wp_basename( $filename );

          $meta['sizes'][ $size ] = array(
            'file'      => $result_filename,
            'width'     => $result_width,
            'height'    => $result_height,
            'mime-type' => get_post_mime_type( $id ),
          );

          wp_update_attachment_metadata( $id, $meta );

          $img_url = wp_get_attachment_url( $id );
          $img_url_basename = wp_basename( $img_url );

          $img_url = str_replace( $img_url_basename, $result_filename, $img_url );

          return array(
            $img_url,
            $result_width,
            $result_height,
            true,
          );

        } else {

          return false;

        }

      }

      return false;

    }

  }

endif;

OIR_Resize_Image::instantiate();
