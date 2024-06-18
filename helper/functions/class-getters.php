<?php
/**
 * Base class to get various playlist information.
 *
 * @since   1.1.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Functions;

use Easy_Video_Playlist\Helper\Store\StoreManager;
use Easy_Video_Playlist\Helper\Playlist\Get_Playlist;
use Easy_Video_Playlist\Helper\Store\PlaylistData;

/**
 * Base class to get various playlist information.
 *
 * @since 1.1.0
 */
class Getters {

	/**
	 * Get playlist data from the database or fetch fresh data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Playlist ID.
	 */
	public static function get_playlist( $key ) {
		$obj  = new Get_Playlist( $key );
		$data = $obj->init();
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		// TODO: Do something with the data, like set autoupdate for it.
		// For now, just return it.
		return $data;
	}

	/**
	 * Get all playlists from the storage.
	 *
	 * @since 1.0.0
	 */
	public static function get_playlists() {
		$store_manager = StoreManager::get_instance();
		$playlists     = array();
		$pl_index      = $store_manager->get_register();
		foreach ( $pl_index as $key => $value ) {
			$title  = $value['bucket_title'];
			$key    = sanitize_text_field( $key );
			$obj    = new Get_Playlist( $key );
			$p_data = $obj->init();
			if ( ! $p_data || ! $p_data instanceof PlaylistData ) {
				continue;
			}
			$playlists[ $key ] = array(
				'title'  => esc_html( $title ),
				'videos' => $p_data->get_videos(),
			);
		}
		return $playlists;
	}

	/**
	 * Get playlist index.
	 *
	 * @since 1.0.0
	 */
	public static function get_playlist_index() {
		$store_manager = StoreManager::get_instance();
		$pl_index      = array( '' => __( 'Select Playlist' ) );
		$playlists     = $store_manager->get_register();
		foreach ( $playlists as $key => $value ) {
			$title            = isset( $value['bucket_title'] ) ? $value['bucket_title'] : 'Untitled Playlist';
			$key              = sanitize_text_field( $key );
			$pl_index[ $key ] = esc_html( $title );
		}
		return $pl_index;
	}

	/**
	 * Get remote data from URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url URL of the external source.
	 */
	public static function get_remote_data( $url ) {
		$response = wp_safe_remote_request( $url, array( 'timeout' => 10 ) );
		if ( 501 === wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}
		$response_body = wp_remote_retrieve_body( $response );
		if ( ! $response_body ) {
			return false;
		}
		$data = json_decode( trim( $response_body ), true );
		if ( ! $data ) {
			return false;
		}
		return $data;
	}
}
