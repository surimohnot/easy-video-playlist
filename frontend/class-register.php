<?php
/**
 * The front-specific functionality of the plugin.
 *
 * @package Easy_Video_Playlist
 * @link    https://easypodcastpro.com/
 * @since   1.0.0
 */

namespace Easy_Video_Playlist\Frontend;

use Easy_Video_Playlist\Frontend\Inc\Loader;

/**
 * The front-specific functionality of the plugin.
 * 
 * Register frontend functionality to display the playlist.
 *
 * @package  Easy_Video_Playlist
 * @since    1.0.0
 */
class Register {

    /**
	 * Register hooked functions.
	 *
	 * @since 1.1.0
	 */
	public static function init() {

        // Load plugin front-end resources.
        self::load_resources();
	}

    /**
     * Load admin specific resources.
     *
     * @since 1.1.0
     */
    public static function load_resources() {
        $loader = Loader::get_instance();
        add_action( 'wp_footer', array( $loader, 'enqueue_frontend_assets' ) );
        add_action( 'elementor/editor/before_enqueue_scripts', array( $loader, 'enqueue_frontend_assets' ) );
        add_action( 'admin_footer', array( $loader, 'add_playlist_svg_icons' ) );
    }
}
