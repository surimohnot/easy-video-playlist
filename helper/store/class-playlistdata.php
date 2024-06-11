<?php
/**
 * Object to store individual video data.
 *
 * Object will save video level data.
 *
 * @link       https://easypodcastpro.com
 * @since      1.0.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Store;

/**
 * Store video data in an object.
 *
 * @package Easy_Video_Playlist
 */
class PlaylistData extends StoreBase {

	/**
	 * Holds playlist title.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $title;

	/**
	 * Holds playlist description.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $description;

	/**
	 * Holds playlist videos data
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array
	 */
	protected $videos = array();

	/**
	 * Holds order of the videos.
	 *
	 * @since  1.2.0
	 * @access protected
	 * @var    array
	 */
	protected $sort_order = array();

	/**
	 * Holds video IDs exlcuded from the playlist and its source if not video itself.
	 *
	 * @since  1.2.0
	 * @access protected
	 * @var    array
	 */
	protected $excluded = array();

	/**
	 * Holds playlist sources.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array
	 */
	protected $sources = array();

	/**
	 * Holds playlist last updated on.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $last_updated = '';

	/**
	 * Playlist cache duration.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    int
	 */
	protected $cache_duration = 3600;

	/**
	 * Get escape functions.
	 *
	 * @since 1.0.0
	 */
	protected function typeDeclaration() {
		// Data type declaration for safe and proper data output.
		return array(
			'title'          => 'title',
			'description'    => 'desc',
			'videos'         => 'none',
			'sources'        => 'arr_arr_string',
			'last_updated'   => 'date',
			'cache_duration' => 'int',
		);
	}

	/**
	 * Retrieve video data as an array.
	 *
	 * @since 1.0.0
	 *
	 * @param string $context Retrieve Context.
	 */
	public function retrieve( $context = 'echo' ) {
		$videos = $this->get_videos();
		if ( empty( $videos ) ) {
			return new \WP_Error(
				'no-videos-error',
				esc_html__( 'No Videos available.', 'easy-video-playlist' )
			);
		}

		// Data type declaration for safe and proper data output.
		$retrieve = $this->get(
			array(
				'title',
				'description',
				'sources',
				'last_updated',
				'cache_duration',
			),
			$context
		);

		$retrieve['videos'] = $videos;
		return $retrieve;
	}

	/**
	 * Get videos from the playlist in proper format.
	 *
	 * @since 1.0.0
	 */
	public function get_videos() {
		$videos = $this->get( 'videos' );
		if ( empty( $videos ) ) {
			return array();
		}
		$vid_arr = array();
		foreach ( $videos as $video ) {
			$video_data = $video->retrieve();
			if ( ! isset( $video_data['id'] ) ) {
				continue;
			}
			$id             = $video_data['id'];
			$vid_arr[ $id ] = $video_data;
		}
		return $vid_arr; // TODO: send videos after sorting.
	}

	/**
	 * Playlist data defaults.
	 *
	 * @since 1.2.0
	 */
	public function get_defaults() {
		return array(
			'title'          => '',
			'description'    => '',
			'videos'         => array(),
			'sources'        => array(),
			'last_updated'   => '',
			'cache_duration' => 3600,
		);
	}

	/**
	 * Check if playlist data needs to be updated.
	 *
	 * @since 1.1.0
	 */
	public function isUpdateRequired() {
		if ( $this->last_updated && $this->last_updated > ( time() - $this->cache_duration ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Set magic method.
	 *
	 * Do not allow adding any new properties to this object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name  Name of the property.
	 * @param mixed  $value Value of the property.
	 *
	 * @throws Exception Setting property is not allowed.
	 */
	public function __set( $name, $value ) {
		throw new Exception( esc_html__( 'Cannot add new property to instance of ', 'easy-video-playlist' ) . __CLASS__ );
	}
}
