<?php
/**
 * Get playlist data from the database or fetch fresh data.
 *
 * @since   1.2.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Playlist;

use Easy_Video_Playlist\Helper\Store\StoreManager;
use Easy_Video_Playlist\Helper\Store\PlaylistData;
use Easy_Video_Playlist\Helper\Store\VideoData;
use Easy_Video_Playlist\Helper\Store\SourceData;
use Easy_Video_Playlist\Helper\Functions\Utility as Utility_Fn;

/**
 * Get Playlist Data from Database OR from external original source.
 *
 * @package Easy_Video_Playlist
 */
class Get_Playlist {

	/**
	 * Holds the playlist key.
	 *
	 * @since  1.2.0
	 * @access private
	 * @var    string $playlist_key
	 */
	private $playlist_key = null;

	/**
	 * Holds the storemanager instance.
	 *
	 * @since  1.2.0
	 * @access private
	 * @var    object $storemanager
	 */
	private $storemanager = null;

	/**
	 * Constructor method.
	 *
	 * @since  1.2.0
	 *
	 * @param string $playlist_key Playlist Key.
	 */
	public function __construct( $playlist_key = '' ) {
		// Set Object Properties.
		if ( ! empty( $playlist_key ) ) {
			$this->playlist_key = $playlist_key;
		}
		$this->storemanager = StoreManager::get_instance();
	}

	/**
	 * Init method.
	 *
	 * @since  1.2.0
	 */
	public function init() {

		// Get playlist data from DB or from feed url.
		$fdata = $this->get_playlist_data();
		if ( is_wp_error( $fdata ) ) {
			return $fdata;
		}

		// TODO: Additional processing of the fetched data.
		// For now, just return it.
		return $fdata;
	}

	/**
	 * Get feed data from DB or from feed url.
	 *
	 * @since  1.2.0
	 */
	private function get_playlist_data() {
		if ( ! $this->playlist_key ) {
			return new \WP_Error( 'no_key', 'No playlist key provided.' );
		}

		$playlist_data = $this->storemanager->get_data( $this->playlist_key );
		if ( ! $playlist_data ) {
			return new \WP_Error( 'no_data', 'No playlist data found.' );
		}

		// Convert to new playlist data format.
		if ( ! $playlist_data instanceof PlaylistData ) {
			$playlist_data = $this->convert_to_object( $playlist_data );
		}

		if ( ! $playlist_data ) {
			return new \WP_Error( 'no_data', 'No playlist data found.' );
		}

		// Fetch fresh data, if required.
		// if ( $playlist_data->isUpdateRequired() ) {
		// 	$playlist_data = $this->fetch_new_data( $playlist_data );
		// }

		return $playlist_data;
	}

	/**
	 * Convert old array format data to object.
	 *
	 * @since  1.2.0
	 *
	 * @param array $data Array of playlist data to be converted.
	 */
	private function convert_to_object( $data ) {
		if ( ! $data || ! is_array( $data ) || ! isset( $data['videos'] ) || ! isset( $data['sources'] ) ) {
			return false;
		}

		// Create video data objects.
		$videos      = $data['videos'];
		$sources     = $data['sources'];
		$vid_objects = array();
		$source_objs = array();
		foreach ( $videos as $key => $video ) {
			$video = array_combine( array_map( array( $this, 'comptible_vdata_keys' ), array_keys( $video ) ), $video );
			
			// Vimeo has numerical keys for videos. This can create problems with storing and sorting of data.
			// Convert to string keys.
			if ( isset( $video['provider'] ) && 'vimeo' === $video['provider'] && isset( $video['id'] ) ) {
				$video['id'] = 'vimeo_' . $video['id'];
			}
			$vid_object = new VideoData();
			$vid_data   = wp_parse_args( $video, $vid_object->get_defaults() );
			$vid_object->set( $vid_data, false, 'none' );
			$vid_objects[ $key ] = $vid_object;
		}

		$filtered_vids = array_filter( $videos, array( $this, 'is_from_valid_source' ) );
		$source_data   = array_column( $filtered_vids, 'source' );

		foreach ( $sources as $source_type => $source_ids ) {
			if ( in_array( $source_type, array( 'video', 'url' ) ) ) {
				continue;
			}

			foreach ( $source_ids as $source_id ) {
				if ( ! in_array( $source_id, $source_data ) ) {
					continue;
				}
				$source_obj = new SourceData();
				$source_obj->set( 'provider', 'youtube' );
				$source_obj->set( 'id', $source_id );
				$source_obj->set( 'type', $source_type );
				$source_objs[] = $source_obj;
			}
		}

		// Create playlist data object.
		$pl_object = new PlaylistData();
		$pl_object->set( $pl_object->get_defaults(), false, 'none' );
		$pl_object->set( 'title', $this->storemanager->get_data_index( $this->playlist_key, 'bucket_title' ) );
		$pl_object->set( 'videos', $vid_objects );
		$pl_object->set( 'sources', $source_objs );

		// Update playlist data in DB.
		$this->storemanager->update_data( $this->playlist_key, $pl_object );

		// Return the playlist object.
		return $pl_object;
	}

	/**
	 * Check if video has a valid source.
	 *
	 * @since  1.2.0
	 *
	 * @param array $video Video data.
	 */
	private function is_from_valid_source( $video ) {
		return isset( $video['provider'] ) && 'youtube' === $video['provider'] && isset( $video['type'] ) && 'video' !== $video['type'] && isset( $video['source'] );
	}

	/**
	 * Process old video data array keys to new format for compatibility.
	 *
	 * @since  1.2.0
	 *
	 * @param string $key Video data keys to be converted.
	 */
	private function comptible_vdata_keys( $key ) {
		$new_key = Utility_Fn::to_snake_case( $key );
		if ( 'type' === $new_key ) {
			$new_key = 'source_type';
		} elseif ( 'source' === $new_key ) {
			$new_key = 'source_id';
		}
		return $new_key;
	}

	/**
	 * Fetch New Data.
	 *
	 * @since  1.2.0
	 *
	 * @param object $playlist_data Playlist Data.
	 */
	private function fetch_new_data( $playlist_data ) {
		// TODO: Update functionality should only work if youtube, vimeo API are available.
		$obj  = new Refresh_Playlist( $this->playlist_key, $playlist_data );
		$data = $obj->init();
	}
}
