<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin
 * and defines a function that starts the plugin.
 *
 * @link              https://easypodcastpro.com
 * @since             1.0.0
 * @package           Easy_Video_Playlist
 *
 * @wordpress-plugin
 * Plugin Name:       Easy Video Playlist
 * Plugin URI:        https://easypodcastpro.com
 * Description:       Create video gallery and playlist from Youtube, Vimeo or Self Hosted videos.
 * Version:           1.3.0
 * Author:            easypodcastpro
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       easy-video-playlist
 * Domain Path:       /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Currently plugin version.
define( 'EVP_VERSION', '1.3.0' );

// Plugin directory path.
define( 'EVP_DIR', plugin_dir_path( __FILE__ ) );

// Plugin directory URL.
define( 'EVP_URL', plugin_dir_url( __FILE__ ) );

// Plugin basename.
define( 'EVP_BASENAME', plugin_basename( __FILE__ ) );

spl_autoload_register(
	function( $class ) {
		$namespace = 'Easy_Video_Playlist\\';

		// Bail if the class is not in our namespace.
		if ( 0 !== strpos( $class, $namespace ) ) {
			return;
		}

		// Get classname without namespace.
		$carray = array_values( explode( '\\', $class ) );
		$clast  = count( $carray ) - 1;

		// Return if proper array is not available. (Just in case).
		if ( ! $clast ) {
			return;
		}

		// Prepend actual classname with 'class-' prefix.
		$carray[ $clast ] = 'class-' . $carray[ $clast ];
		$class            = implode( '\\', $carray );

		// Generate file path from classname.
		$path = strtolower(
			str_replace(
				array( $namespace, '_' ),
				array( '', '-' ),
				$class
			)
		);

		// Build full filepath.
		$file = EVP_DIR . DIRECTORY_SEPARATOR . str_replace( '\\', DIRECTORY_SEPARATOR, $path ) . '.php';

		// If the file exists for the class name, load it.
		if ( file_exists( $file ) ) {
			include $file;
		}
	}
);

add_action(
	'plugins_loaded',
	function() {
		// Load plugin's text domain.
		load_plugin_textdomain( 'easy-video-playlist', false, dirname( EVP_BASENAME ) . '/lang' );
		
		// Register Easy Video Playlist front-end hooks.
		Easy_Video_Playlist\Frontend\Register::init();

		// Register Easy Video Playlist back-end hooks.
		Easy_Video_Playlist\Backend\Register::init();
	},
	8
);
