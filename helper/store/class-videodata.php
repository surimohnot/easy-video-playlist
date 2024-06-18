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
class VideoData extends StoreBase {

	/**
	 * Holds video title.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $title;

	/**
	 * Holds video description.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $description;

	/**
	 * Holds video url.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $url;

	/**
	 * Holds source type.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $source_type;

	/**
	 * Holds video provider.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array
	 */
	protected $provider;

	/**
	 * Holds video author name.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $author_name;

	/**
	 * Holds video author url.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $author_url;

	/**
	 * Holds video author ID.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $author_id;

	/**
	 * Holds video thumbnail URL.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $thumbnail_url;

	/**
	 * Holds video ID.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $id;

	/**
	 * Holds video release date.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $date;

	/**
	 * Holds Video Tags.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $tags;

	/**
	 * Holds Video Duration.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $duration;

	/**
	 * Holds Video upload status.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $upload_status;

	/**
	 * Holds Video privacy status.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $privacy_status;

	/**
	 * Holds Video view count.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    int
	 */
	protected $view_count;

	/**
	 * Holds Video like count.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    int
	 */
	protected $like_count;

	/**
	 * Holds Video comment count.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    int
	 */
	protected $comment_count;

	/**
	 * Holds Id of the Video Source.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $source_id;

	/**
	 * Holds modified data of the properties.
	 *
	 * @since  1.2.0
	 * @access protected
	 * @var    array
	 */
	protected $custom_values = array();

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
			'url'            => 'url',
			'provider'       => 'string',
			'author_name'    => 'string',
			'author_url'     => 'url',
			'author_id'      => 'string',
			'thumbnail_url'  => 'arr_url',
			'id'             => 'string',
			'date'           => 'date',
			'tags'           => 'string',
			'duration'       => 'dur',
			'upload_status'  => 'string',
			'privacy_status' => 'string',
			'view_count'     => 'int',
			'like_count'     => 'int',
			'comment_count'  => 'int',
			'source_type'    => 'string',
			'source_id'      => 'string',
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
		// Data type declaration for safe and proper data output.
		return $this->get(
			array(
				'title',
				'description',
				'url',
				'provider',
				'author_name',
				'author_url',
				'author_id',
				'thumbnail_url',
				'id',
				'date',
				'tags',
				'duration',
				'upload_status',
				'privacy_status',
				'view_count',
				'like_count',
				'comment_count',
				'source_type',
				'source_id',
			),
			$context
		);
	}

	/**
	 * Video data defaults.
	 *
	 * @since 1.2.0
	 */
	public function get_defaults() {
		return array(
			'title'          => '',
			'description'    => '',
			'url'            => '',
			'provider'       => '',
			'author_name'    => '',
			'author_url'     => '',
			'author_id'      => '',
			'thumbnail_url'  => array(),
			'id'             => '',
			'date'           => '',
			'tags'           => '',
			'duration'       => '',
			'upload_status'  => '',
			'privacy_status' => '',
			'view_count'     => 0,
			'like_count'     => 0,
			'comment_count'  => 0,
			'source_type'    => '',
			'source_id'      => '',
		);
	}

	/**
	 * Set magic method.
	 *
	 * Do not allow adding any new properties to this object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Name of the property.
	 * @param mixed  $value Value of the property.
	 *
	 * @throws Exception Setting property is not allowed.
	 */
	public function __set( $name, $value ) {
		throw new Exception( esc_html__( 'Cannot add new property to instance of ', 'easy-video-playlist' ) . __CLASS__ );
	}
}
