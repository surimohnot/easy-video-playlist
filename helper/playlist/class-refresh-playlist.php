<?php
/**
 * Fetch fresh playlist data from the original source.
 *
 * @since   1.2.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Playlist;

use Easy_Video_Playlist\Helper\Store\StoreManager;
use Easy_Video_Playlist\Helper\Store\PlaylistData;
use Easy_Video_Playlist\Helper\Store\VideoData;

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
	 * Holds old the playlist data.
	 *
	 * @since  1.2.0
	 * @access private
	 * @var    string $prev_data
	 */
	private $prev_data = null;

	/**
	 * Holds the storemanager instance.
	 *
	 * @since  1.2.0
	 * @access private
	 * @var    object $storemanager
	 */
	private $storemanager = null;

	/**
	 * Holds YouTube video Ids.
	 *
	 * @since  1.2.0
	 * @access private
	 * @var    array $videos
	 */
	private $youtube_videos = array();

	/**
	 * Holds Vimeo video Ids.
	 *
	 * @since  1.2.0
	 * @access private
	 * @var    array $videos
	 */
	private $vimeo_videos = array();

	/**
	 * Holds instance of Fetch YouTube class.
	 *
	 * @since  1.2.0
	 * @access private
	 * @var    object $fetch_youtube
	 */
	private $fetch_youtube = null;

	/**
	 * Holds instance of Fetch Vimeo class.
	 *
	 * @since  1.2.0
	 * @access private
	 * @var    object $fetch_vimeo
	 */
	private $fetch_vimeo = null;

	/**
	 * Constructor method.
	 *
	 * @since  1.2.0
	 *
	 * @param string $playlist_key Playlist Key.
	 * @param string $prev_data    Previous Data.
	 */
	public function __construct( $playlist_key, $prev_data ) {

		// Return if playlist key is not provided.
		if ( empty( $playlist_key ) || ! $prev_data instanceof PlaylistData ) {
			return new \WP_Error( 'no_key', 'No playlist key or proper data provided.' );
		}

		// Set Object Properties.
		$this->playlist_key  = $playlist_key;
		$this->prev_data     = $prev_data;
		$this->storemanager  = StoreManager::get_instance();
		$this->fetch_youtube = Fetch_Youtube::get_instance();
		$this->fetch_vimeo   = Fetch_Vimeo::get_instance();
	}

	/**
	 * Initiate the update methods.
	 *
	 * @since  1.2.0
	 */
	public function init() {
		// Retrieve updated list of video ids from all sources of the playlist.
		$this->retrieve_video_ids();

		// Fetch Video Data.
		$video_data = $this->fetch_video_data();

		// Update playlist data.
		$this->prev_data->set( 'videos', $video_data );
	}

	/**
	 * Init method.
	 *
	 * @since  1.2.0
	 */
	public function retrieve_video_ids() {
		$prev_sources = $this->prev_data->get( 'sources', 'sanitize' );
		$vid_list     = $this->prev_data->get_videos();
		$videos       = array();
		foreach ( $prev_sources as $source => $ids ) {
			$ids = $ids && is_array( $ids ) ? $ids : array();
			switch ( $source ) {
				case 'video':
					foreach ( $ids as $id ) {
						if ( isset( $vid_list[ $id ] ) && is_array( $vid_list[ $id ] ) && isset( $vid_list[ $id ]['provider'] ) ) {
							if ( 'youtube' === $vid_list[ $id ]['provider'] ) {
								$this->youtube_videos[ $id ] = array(
									'type' => 'video',
									'id'   => $id,
								);
							} elseif ( 'vimeo' === $vid_list[ $id ]['provider'] ) {
								$this->vimeo_videos[ $id ] = array(
									'type' => 'video',
									'id'   => $id,
								);
							}
						}
					}
					break;
				case 'youtube_video':
					foreach ( $ids as $id ) {
						$this->youtube_videos[ $id ] = array(
							'type' => 'video',
							'id'   => $id,
						);
					}
					break;
				case 'playlist':
				case 'youtube_playlist':
					foreach ( $ids as $id ) {
						$video_ids = $this->fetch_youtube->video_ids_from_playlist( $id );
						foreach ( $video_ids as $vid ) {
							$this->youtube_videos[ $vid ] = array(
								'type' => 'playlist',
								'id'   => $id,
							);
						}
					}
					break;
				case 'channel':
				case 'youtube_channel':
					foreach ( $ids as $id ) {
						$video_ids = $this->fetch_youtube->video_ids_from_channel( $id );
						foreach ( $video_ids as $vid ) {
							$this->youtube_videos[ $vid ] = array(
								'type' => 'channel',
								'id'   => $id,
							);
						}
					}
					break;
				case 'user':
				case 'youtube_user':
					foreach ( $ids as $id ) {
						$video_ids = $this->fetch_youtube->video_ids_from_user( $id );
						foreach ( $video_ids as $vid ) {
							$this->youtube_videos[ $vid ] = array(
								'type' => 'user',
								'id'   => $id,
							);
						}
					}
					break;
				case 'youtube_channelUser':
					foreach ( $ids as $id ) {
						$video_ids = $this->fetch_youtube->video_ids_from_channeluser( $id );
						foreach ( $video_ids as $vid ) {
							$this->youtube_videos[ $vid ] = array(
								'type' => 'channelUser',
								'id'   => $id,
							);
						}
					}
					break;
				case 'vimeo_video':
					foreach ( $ids as $id ) {
						$this->vimeo_videos[ $id ] = array(
							'type' => 'video',
							'id'   => $id,
						);
					}
					break;
				case 'vimeo_user':
					foreach ( $ids as $id ) {
						$video_ids = $this->get_vimeo_videos( $id, 'user' );
						foreach ( $video_ids as $vid ) {
							$this->vimeo_videos[ $vid ] = array(
								'type' => 'user',
								'id'   => $id,
							);
						}
					}
					break;
				case 'vimeo_channel':
					foreach ( $ids as $id ) {
						$video_ids = $this->get_vimeo_videos( $id, 'channel' );
						foreach ( $video_ids as $vid ) {
							$this->vimeo_videos[ $vid ] = array(
								'type' => 'channel',
								'id'   => $id,
							);
						}
					}
					break;
				case 'vimeo_album':
					foreach ( $ids as $id ) {
						$video_ids = $this->get_vimeo_videos( $id, 'album' );
						foreach ( $video_ids as $vid ) {
							$this->vimeo_videos[ $vid ] = array(
								'type' => 'channel',
								'id'   => $id,
							);
						}
					}
					break;
				case 'vimeo_showcase':
					foreach ( $ids as $id ) {
						$video_ids = $this->get_vimeo_videos( $id, 'showcase' );
						foreach ( $video_ids as $vid ) {
							$this->vimeo_videos[ $vid ] = array(
								'type' => 'showcase',
								'id'   => $id,
							);
						}
					}
					break;
				case 'vimeo_group':
					foreach ( $ids as $id ) {
						$video_ids = $this->get_vimeo_videos( $id, 'group' );
						foreach ( $video_ids as $vid ) {
							$this->vimeo_videos[ $vid ] = array(
								'type' => 'group',
								'id'   => $id,
							);
						}
					}
					break;
				default:
					break;
			}
		}
	}

	/**
	 * Get vimeo videos Ids.
	 *
	 * This is just a placeholder function for now. Will be useful when we support Vimeo API.
	 *
	 * @since  1.2.0
	 *
	 * @param  array  $ids  Ids.
	 * @param  string $type Type.
	 */
	private function get_vimeo_videos( $ids, $type ) {
		// TODO: Add Vimeo API.
		return array();
	}

	/**
	 * Fetch Video Data.
	 *
	 * @since  1.2.0
	 */
	private function fetch_video_data() {
		// Fetch video data for YouTube videos.
		$yt_video_data  = $this->fetch_youtube->video_data( $this->youtube_videos );
		$vimeo_vid_data = $this->fetch_vimeo->video_data( $this->youtube_videos );
		return array_merge( $yt_video_data, $vimeo_vid_data );
	}
}
