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
use Easy_Video_Playlist\Helper\Playlist\Fetch_YouTube;
use Easy_Video_Playlist\Helper\Playlist\Fetch_Vimeo;
use Easy_Video_Playlist\Helper\Playlist\Fetch_Url;

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

	/**
	 * Get video data from a given source.
	 *
	 * @since 1.2.0
	 *
	 * @param string $source_id Source ID.
	 * @param string $source_type Source Type.
	 * @param string $url Source URL.
	 * @param string $provider Source Provider.
	 */
	public static function get_data_from_source( $source_id, $source_type, $url, $provider ) {
		$valid_providers    = apply_filters(
			'evp_valid_providers',
			array( 'youtube', 'vimeo', 'url' )
		);

		$valid_source_types = apply_filters(
			'evp_valid_source_types',
			array( 'playlist', 'channel', 'user', 'video' )
		);
		
		// Return if provider is not valid.
		if ( ! in_array( $provider, $valid_providers, true ) ) {
			return array();
		}

		// Return if source type is not valid.
		if ( ! in_array( $source_type, $valid_source_types, true ) ) {
			return array();
		}
		
		$video_data = array();
		if ( 'youtube' === $provider ) {
			$yt_obj     = Fetch_YouTube::get_instance();
			$video_data = $yt_obj->get_data( $source_id, $source_type );
		} elseif ( 'vimeo' === $provider ) {
			$vm_obj     = Fetch_Vimeo::get_instance();
			$video_data = $vm_obj->get_data( $source_id, $source_type );
		} elseif ( 'url' === $provider ) {
			$url_obj    = Fetch_Url::get_instance();
			$video_data = $url_obj->get_data( $url );
		}

		return $video_data;
	}
}
