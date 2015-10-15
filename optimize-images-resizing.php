<?php

/**
 * Plugin Name: Optimize Images Resizing
 * Plugin URI: https://twitter.com/Original_EXE
 * Description: Improve WordPress image sizes generation and save your hosting space
 * Author: OriginalEXE
 * Author URI: https://twitter.com/Original_EXE
 * Version: 1.1.0
 */

// Exit if accessed directly
! defined( 'ABSPATH' ) && exit;

// Check if class already exists
if ( ! class_exists( 'OIR_Init' ) ) :

	final class OIR_Init {

		// Will hold the only instance of our main plugin class
		private static $instance;

		// Instantiate the class and set up stuff
		public static function instantiate() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof OIR_Init ) ) {

				self::$instance = new OIR_Init();
				self::$instance->define_constants();
				self::$instance->include_files();

				// load textdomain
				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			}

			return self::$instance;

		}

		// Defines plugin constants
		private function define_constants() {

			// Plugin version
			if ( ! defined( 'OIR_VERSION' ) )
				define( 'OIR_VERSION', '1.1.0' );

			// Plugin Folder Path
			if ( ! defined( 'OIR_PLUGIN_DIR' ) )
				define( 'OIR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin Include Path
			if ( ! defined( 'OIR_PLUGIN_DIR_INC' ) )
				define( 'OIR_PLUGIN_DIR_INC', OIR_PLUGIN_DIR . 'inc/' );

			// Plugin Folder URL
			if ( ! defined( 'OIR_PLUGIN_URL' ) )
				define( 'OIR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

			// Plugin JS Folder URL
			if ( ! defined( 'OIR_JS_URL' ) )
				define( 'OIR_JS_URL', OIR_PLUGIN_URL . 'js/' );

			// Plugin CSS Folder URL
			if ( ! defined( 'OIR_CSS_URL' ) )
				define( 'OIR_CSS_URL', OIR_PLUGIN_URL . 'css/' );

			// Plugin Root File
			if ( ! defined( 'OIR_PLUGIN_FILE' ) )
				define( 'OIR_PLUGIN_FILE', __FILE__ );

		}

		// Includes necessary files
		private function include_files() {

			require_once OIR_PLUGIN_DIR_INC . 'class-resize-image.php';

			if ( is_admin() ) {

				require_once OIR_PLUGIN_DIR_INC . 'class-remove-image-sizes.php';

			}

		}

		// sets up textdomain
		public static function load_textdomain() {

			$lang_dir = dirname( plugin_basename( OIR_PLUGIN_FILE ) ) . '/languages/';

			$lang_dir = trailingslashit( apply_filters( 'oir_textdomain_location', $lang_dir ) );

			load_plugin_textdomain( 'oir_plugin', false, $lang_dir );	

		}

	}

endif;

OIR_Init::instantiate();