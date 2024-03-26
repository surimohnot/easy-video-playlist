<?php
/**
 * Load and manage admin page of this plugin.
 *
 * @package Easy_Video_Playlist
 * @link    https://easypodcastpro.com/
 * @since   1.1.0
 */

namespace Easy_Video_Playlist\Backend\Admin;

use Easy_Video_Playlist\Lib\Singleton;
use Easy_Video_Playlist\Backend\Inc\Loader;

/**
 * Load and manage admin page of this plugin.
 *
 * @package  Easy_Video_Playlist
 * @since    1.1.0
 */
class Admin extends Singleton {

    /**
	 * Register hooked functions.
	 *
	 * @since 1.1.0
	 */
	public static function init() {
        $inst = self::get_instance();
		add_action( 'admin_menu', array( $inst, 'register_admin_page' ) );
    }

    /**
     * Register admin page.
     *
     * @since 1.1.0
     */
    public function register_admin_page() {
        // Add the admin page.
        $suffix = add_menu_page(
            esc_html__('Easy Video Playlist', 'easy-video-playlist'),
            esc_html__('Easy Video Playlist', 'easy-video-playlist'),
            'manage_options',
            'evp_settings',
            function() {
                include_once EVP_DIR . 'backend/admin/templates/admin-page.php';
            },
            'dashicons-playlist-video',
        );

        $loader = Loader::get_instance();

        // Add script on the plugin admin page.
        add_action( 'admin_print_scripts-' . $suffix, array( $loader, 'enqueue_admin_scripts' ) );

        // Add styles on the plugin admin page.
        add_action( 'admin_print_styles-' . $suffix, array( $loader, 'enqueue_admin_styles' ) );
    }
}
