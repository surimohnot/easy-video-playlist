<?php
/**
 * Load admin specific resources to the page.
 *
 * @package Easy_Video_Playlist
 * @link    https://easypodcastpro.com/
 * @since   1.1.0
 */

namespace Easy_Video_Playlist\Backend\Inc;

use Easy_Video_Playlist\Helper\Core\Singleton;
use Easy_Video_Playlist\Helper\Functions\Getters;
use Easy_Video_Playlist\Frontend\Inc\Loader as Frontend_Loader;

/**
 * Load admin specific resources to the page.
 * 
 * Enqueue admin area scripts, styles and other resources.
 *
 * @package  Easy_Video_Playlist
 * @since    1.1.0
 */
class Loader extends Singleton {
    /**
	 * Register the JavaScript for the editor screen.
	 *
	 * @since 1.1.0
	 */
	public function enqueue_editor_assets() {
        // Load Editor Scripts.
		wp_enqueue_script(
            'evp-block-js',
            EVP_URL . 'backend/js/block/block.build.js',
            array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor', 'wp-api-fetch', 'wp-block-editor', 'wp-server-side-render', 'jquery' ),
            EVP_VERSION,
            true
        );

        Frontend_Loader::get_instance()->enqueue_frontend_assets();
	}

    /**
     * Register scripts for the plugin's admin page.
     *
     * @since 1.1.0
     */
    public function enqueue_admin_scripts() {
        $api = get_option( 'evp_settings_api' );
        $api = $api && is_array( $api ) ? $api : array();
        $yt_api_key = isset( $api[ 'youtube' ] ) && $api[ 'youtube' ] ? true : false;
        $i18n = array(
            'vidurl'      => __( 'Enter URL of the Video, Playlist or Channel', 'easy-video-playlist' ),
            'videourl'    => __( 'Video URL', 'easy-video-playlist' ),
            'title'       => __( 'Title', 'easy-video-playlist' ),
            'author'      => __( 'Channel', 'easy-video-playlist' ),
            'authorurl'   => __( 'Channel URL', 'easy-video-playlist' ),
            'thumbnail'   => __( 'Thumbnail Image URL', 'easy-video-playlist' ),
            'exshorts'    => __( 'Exclude Shorts (Videos shorter than 60 Seconds)', 'easy-video-playlist' ),
            'addvid'      => __( 'Add Video', 'easy-video-playlist' ),
            'update'      => __( 'Update', 'easy-video-playlist' ),
            'cancel'      => __( 'Cancel', 'easy-video-playlist' ),
            'editvidinfo' => __( 'Edit Video Information', 'easy-video-playlist' ),
            'createfirst' => __( 'Create Your First Playlist', 'easy-video-playlist' ),
            'createnew'   => __( 'Create New Playlist', 'easy-video-playlist' ),
        );
        wp_enqueue_script(
            'evp-admin',
            EVP_URL . 'backend/js/admin/admin.build.js',
            array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'),
            EVP_VERSION,
            true
        );

        // Load inline script data on the plugin admin page.
        wp_localize_script(
            'evp-admin',
            'EVP_Admin_Data',
            apply_filters(
                'evp_admin_script_data',
                array(
                    'ajaxurl'       => admin_url('admin-ajax.php'),
                    'security'      => wp_create_nonce( 'evp-admin-ajax-nonce' ),
                    'videoPlaylist' => Getters::get_playlists(),
                    'i18n'          => $i18n,
                    'api'           => array( 'youtube' => $yt_api_key ),
                    'setpage'       => esc_url( add_query_arg( 'tab', 'settings', admin_url( 'admin.php?page=evp_settings' ) ) ),
                )
            )
        );
    }

    /**
     * Register styles for the plugin's admin page.
     *
     * @since 1.1.0
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style(
            'evp-admin',
            EVP_URL . 'backend/css/admin/admin.css',
            array(),
            EVP_VERSION
        );
    }

    /**
     * Register the Video Playlist REST API routes for the block.
     *
     * @since 1.1.0
     */
    public function register_routes() {
        register_rest_route(
            'evp/v1',
            '/lIndex',
            array(
                'methods'             => 'GET',
                'callback'            => function() {
                    return Getters::get_playlist_index();
                },
                'permission_callback' => function () {
                    return current_user_can( 'edit_posts' );
                },
            )
        );
    }

    /**
     * Add the Video Playlist SVG icon to the page.
     *
     * @since 1.1.0
     */
    public function add_icons() {
        if ( file_exists( EVP_DIR . 'helper/assets/images/plyr.svg' ) ) {
            include_once EVP_DIR . 'helper/assets/images/plyr.svg';
        }
    }
}
