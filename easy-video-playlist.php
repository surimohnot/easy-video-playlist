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
 * Version:           1.1.0
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

// Define plugin constants.
define( 'EVP_VERSION', '1.1.0' );
define( 'EVP_DIR', plugin_dir_path( __FILE__ ) );
define( 'EVP_URL', plugin_dir_url( __FILE__ ) );
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
		load_plugin_textdomain( 'easy-video-playlist', false, dirname( EVP_BASENAME ) . '/lang' );
		require_once EVP_DIR . 'inc/validation.php';
		require_once EVP_DIR . 'inc/markup.php';
		require_once EVP_DIR . 'inc/storage.php';
		require_once EVP_DIR . 'inc/includes.php';
        require_once EVP_DIR . 'inc/admin.php';
		require_once EVP_DIR . 'inc/front.php';
	},
	8
);
