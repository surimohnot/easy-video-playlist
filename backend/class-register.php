<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package Easy_Video_Playlist
 * @link    https://easypodcastpro.com/
 * @since   1.0.0
 */

namespace Easy_Video_Playlist\Backend;

use Easy_Video_Playlist\Backend\Inc\Loader;
use Easy_Video_Playlist\Backend\Inc\Core;
use Easy_Video_Playlist\Backend\Admin\Admin as Admin;
use Easy_Video_Playlist\Backend\Inc\Block;
use Easy_Video_Playlist\Backend\Inc\Shortcode;
use Easy_Video_Playlist\Helper\Store\StoreManager;

/**
 * The admin-specific functionality of the plugin.
 * 
 * Register custom widget and custom shortcode functionality. Enqueue admin area
 * scripts and styles.
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

        // Load plugin admin resources.
        self::load_resources();

        // Register REST API functions.
        self::register_rest_api();

		// Register Easy Video Playlist widget.
		self::register_widget();

		// Register Easy Video Playlist block.
		self::register_block();

		// Register Easy Video Playlist shortcode display method.
		self::register_shortcode();

        // Support AJAX functionality.
        self::support_ajax_functionality();

        // Register Custom Post Types Storage.
        self::register_storage();

        // Initiate plugin's admin page.
		Admin::init();
	}

    /**
     * Load admin specific resources.
     *
     * @since 1.1.0
     */
    public static function load_resources() {
        $loader = Loader::get_instance();
        add_action( 'enqueue_block_editor_assets', array( $loader, 'enqueue_editor_assets' ) );
        add_action('admin_footer', array( $loader, 'add_icons' ), 9999);
    }

    /**
     * Load admin specific resources.
     *
     * @since 1.1.0
     */
    public static function register_rest_api() {
        $loader = Loader::get_instance();
        add_action('rest_api_init', array( $loader, 'register_routes' ) );
    }

    /**
     * Register Easy Video Playlist widget.
     *
     * @since 1.1.0
     */
    public static function register_widget() {
        add_action(
            'widgets_init',
            function() {
                register_widget( 'Easy_Video_Playlist\Backend\Inc\Widget' );
            }
        );
    }

    /**
     * Register Easy Video Playlist block.
     *
     * @since 1.1.0
     */
    public static function register_block() {
        $block = Block::get_instance();
		add_action( 'init', array( $block, 'register' ) );
    }

    /**
     * Register Easy Video Playlist shortcode display method.
     *
     * @since 1.1.0
     */
    public static function register_shortcode() {
        $shortcode = Shortcode::get_instance();
        add_shortcode( 'evpvideoplaylist', array( $shortcode, 'render' ) );
    }

    /**
     * Register Storage.
     *
     * @since 1.1.0
     */
    public static function register_storage() {
        $store_manager = StoreManager::get_instance();
        add_action( 'init', array( $store_manager, 'register' ) );
    }

    /**
	 * Support podcast player Ajax functionality scripts.
	 *
	 * @since 1.1.0
	 */
	public static function support_ajax_functionality() {
        $core = Core::get_instance();
        add_action( 'wp_ajax_evp_add_new_playlist', array( $core, 'add_new_playlist' ) );
        add_action( 'wp_ajax_evp_delete_playlist', array( $core, 'delete_playlist' ) );
        add_action( 'wp_ajax_evp_add_new_video', array( $core, 'add_new_video' ) );
        add_action( 'wp_ajax_evp_delete_video', array( $core, 'delete_video' ) );
        add_action( 'wp_ajax_evp_edit_video_info', array( $core, 'edit_video_info' ) );
        add_action( 'wp_ajax_evp_save_playlist_sorting', array( $core, 'save_playlist_sorting' ) );
        add_action( 'wp_ajax_evp_save_api_key', array( $core, 'save_api_key' ) );
    }
}
