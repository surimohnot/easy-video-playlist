<?php
/**
 * Fetch fresh playlist data from the original source.
 *
 * @since   1.2.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Playlist;

use Easy_Video_Playlist\Helper\Functions\Getters;
use Easy_Video_Playlist\Helper\Store\StoreManager;
use Easy_Video_Playlist\Helper\Store\PlaylistData;
use Easy_Video_Playlist\Helper\Store\VideoData;
use Easy_Video_Playlist\Helper\Store\SourceData;

/**
 * Fetch fresh playlist data from the original source.
 *
 * @package Easy_Video_Playlist
 */
class Refresh_Playlist {

	/**
	 * Holds the playlist key.
	 *
	 * @since  1.2.0
	 * @access private
	 * @var    string $playlist_key
	 */
	private $playlist_key = null;

	/**
	 * Constructor method.
	 *
	 * @since  1.2.0
	 *
	 * @param string $playlist_key Playlist Key.
	 */
	public function __construct( $playlist_key ) {

		// Return if playlist key is not provided.
		if ( empty( $playlist_key ) ) {
			return new \WP_Error( 'no_key', 'No playlist key or proper data provided.' );
		}

		// Set Object Properties.
		$this->playlist_key  = $playlist_key;
	}

	/**
	 * Initiate the update methods.
	 *
	 * @since  1.2.0
	 */
	public function init() {
		$obj    = new Get_Playlist( $this->playlist_key, false );
		$p_data = $obj->init();
		if ( ! $p_data || ! $p_data instanceof PlaylistData ) {
			$message = __( 'Incorrect Playlist data available.', 'evp_video_player' );
			return new \WP_Error( 'no_data', $message );
		}

		$sources = $p_data->get( 'sources' );
		$videos  = $p_data->get( 'videos' );

		$video_data = $this->fetch_video_data( $sources );
		$videos     = $this->merge_video_data( $videos, $video_data );
		$p_data->set( 'videos', $videos );
		$p_data->set( 'last_updated', time() );

		$store_manager = StoreManager::get_instance();
		$store_manager->update_data( $this->playlist_key, $p_data );
		return $p_data;
	}

	/**
	 * Fetch video data for each source of the playlist.
	 *
	 * @since  1.2.0
	 *
	 * @param array $sources Array of sources.
	 */
	private function fetch_video_data( $sources ) {
		$vid_data = array();
		foreach ( $sources as $source ) {
			if ( ! $source instanceof SourceData ) {
				continue;
			}
			$source_id   = $source->get( 'id' );
			$source_type = $source->set( 'type' );
			$url         = $source->get( 'url' );
			$provider    = $source->get( 'provider' );
			$video_data  = Getters::get_data_from_source( $source_id, $source_type, $url, $provider );
			$video_data  = $video_data && is_array( $video_data ) ? $video_data : array();
			$vid_data    = array_merge( $vid_data, $video_data );
		}
		return $vid_data;
	}

	/**
	 * Merge video data with the playlist data.
	 *
	 * @since  1.2.0
	 *
	 * @param array $videos  Array of videos.
	 * @param array $vid_data Array of video data.
	 */
	private function merge_video_data( $videos, $vid_data ) {
		$video_arr = array();
		foreach ( $videos as $video ) {
			if ( ! $video || ! $video instanceof VideoData ) {
				continue;
			}
			$id               = $video->get( 'id' );
			$video_arr[ $id ] = $video;
		}

		if ( ! empty( $vid_data ) && is_array( $vid_data ) ) {
			foreach ( $vid_data as $vdata ) {
				if ( ! $vdata || ! $vdata instanceof VideoData ) {
					continue;
				}
				$id = $vdata->get( 'id' );
				$video_arr[ $id ] = $vdata;
			}
		}

		return array_values( $video_arr );
	}
}
