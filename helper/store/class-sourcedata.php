<?php
/**
 * Object to store playlist source data.
 *
 * Object will save source level data.
 *
 * @link       https://easypodcastpro.com
 * @since      1.2.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Store;

/**
 * Store playlist source data in an object.
 *
 * @package Easy_Video_Playlist
 */
class SourceData extends StoreBase {

	/**
	 * Holds Source Provider.
	 *
	 * Source provider name like YouTube, Vimeo or RSS feed.
	 *
	 * @since  1.2.0
	 * @access protected
	 * @var    string
	 */
	protected $provider;

	/**
	 * Holds Source Type.
	 * 
	 * Source type like video, playlist, podcast, collection etc.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $type;

	/**
	 * Holds Source ID.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $id;

	/**
	 * Holds source information.
	 *
	 * @since  1.2.0
	 * @access protected
	 * @var    object
	 */
	protected $info = array();

	/**
	 * Get escape functions.
	 *
	 * @since 1.0.0
	 */
	protected function typeDeclaration() {
		// Data type declaration for safe and proper data output.
		return array(
			'provider' => 'string',
			'type'     => 'string',
			'id'       => 'string',
			'info'     => 'none',
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
		$info = $this->get_source_info();

		// Data type declaration for safe and proper data output.
		$retrieve = $this->get(
			array(
				'provider',
				'type',
				'id',
			),
			$context
		);

		$retrieve['info'] = $info;
		return $retrieve;
	}

	/**
	 * Get source info in proper format if available.
	 *
	 * @since 1.2.0
	 */
	public function get_source_info() {
		$info = $this->get( 'info' );
		if ( empty( $info ) ) {
			return array();
		}

		return $info->retrieve();
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
