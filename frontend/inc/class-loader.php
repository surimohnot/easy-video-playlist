<?php
/**
 * Load frontend specific resources to the page.
 *
 * @package Easy_Video_Playlist
 * @link    https://easypodcastpro.com/
 * @since   1.1.0
 */

namespace Easy_Video_Playlist\Frontend\Inc;

use Easy_Video_Playlist\Helper\Store\StoreManager;
use Easy_Video_Playlist\Helper\Core\Singleton;
use Easy_Video_Playlist\Helper\Functions\Getters;
use Easy_Video_Playlist\Helper\Core\Icon_Loader;
use Easy_Video_Playlist\Helper\Playlist\Get_Playlist;
use Easy_Video_Playlist\Helper\Store\PlaylistData;

/**
 * Load frontend specific resources to the page.
 * 
 * Enqueue front area scripts, styles and other resources.
 *
 * @package  Easy_Video_Playlist
 * @since    1.1.0
 */
class Loader extends Singleton {

    /**
	 * Register the assets for the front screen.
	 *
	 * @since 1.1.0
	 */
	public function enqueue_frontend_assets() {
        $instance = Instance_Counter::get_instance();
        if ( ! $instance->has_playlist() ) {
            return;
        }
        $this->enqueue_frontend_scripts();
        $this->enqueue_frontend_styles();
	}

    /**
     * Register the javaScript for the front screen.
     *
     * @since 1.1.0
     */
    public function enqueue_frontend_scripts() {
        $instance  = Instance_Counter::get_instance();
        $playlists = $instance->get_playlist();
        $pl_data   = array();

        if ( empty( $playlists ) ) {
            $playlists = Getters::get_playlist_index();
        }

        if ( empty( $playlists ) ) {
            return;
        }

        foreach ( $playlists as $key => $playlist ) {
            $obj    = new Get_Playlist( $key );
		    $p_data = $obj->init();
            if ( ! $p_data || ! $p_data instanceof PlaylistData ) {
                continue;
            }
            $pl_data[ $key ] = $p_data->retrieve();
        }

        wp_enqueue_script(
            'evp-front',
            EVP_URL . 'frontend/js/front.build.js',
            array('jquery'),
            EVP_VERSION,
            true
        );
        wp_localize_script(
            'evp-front',
            'EVP_Front_Data',
            apply_filters(
                'evp_front_script_data',
                array(
                    'data' => $pl_data,
                    'url'  => home_url(),
                )
            )
        );
    }

    /**
     * Register the styles for the front screen.
     *
     * @since 1.1.0
     */
    public function enqueue_frontend_styles() {
        wp_enqueue_style(
            'evp-front',
            EVP_URL . 'frontend/css/front.css',
            array(),
            EVP_VERSION
        );
    }

    /**
     * Add SVG icons for the playlist.
     *
     * @since 1.1.0
     */
    public function add_playlist_svg_icons() {
        Icon_Loader::get_instance()->add_icons();
        if ( file_exists( EVP_DIR . 'helper/assets/images/plyr.svg' ) ) {
            include_once EVP_DIR . 'helper/assets/images/plyr.svg';
        }
    }
}
