<?php
/**
 * Fetch Video Data from URL.
 *
 * @since   1.2.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Playlist;

use Easy_Video_Playlist\Helper\Core\Singleton;
use Easy_Video_Playlist\Helper\Store\VideoData;
use Easy_Video_Playlist\Helper\Functions\Validation as Validation_Fn;
use Easy_Video_Playlist\Helper\Functions\Getters as Get_Fn;

/**
 * Fetch Video Data from URL.
 *
 * @since 1.2.0
 */
class Fetch_Url extends Singleton {

	/**
	 * Constructor method.
	 *
	 * @since  1.2.0
	 */
	public function __construct() {}

	/**
	 * Get Vimeo video data from the video IDs.
	 *
	 * @since  1.2.0
	 *
	 * @param string $url Video URL.
	 */
	public function get_data( $url ) {
		$data = $this->get_url_data_object( $url );
		if ( ! $data || ! $data instanceof VideoData ) {
			return array();
		}
		return array( $data );
	}

	/**
	 * Get video data object from URL.
	 *
	 * @since  1.2.0
	 *
	 * @param string $url Url.
	 */
	private function get_url_data_object( $url ) {
		if ( ! $url || ! Validation_Fn::is_video_url( $url ) ) {
			return false;
		}

		$data = array(
			'title'         => 'Local Untitled Video',
			'url'           => esc_url_raw( $url ),
			'thumbnail_url' => array(),
			'id'            => md5( $url ),
			'provider'      => 'url',
			'type'          => 'video',
		);
		$user = wp_get_current_user();
		$author_name = $user ? $user->display_name : '';
		$author_url  = $user ? $user->user_url : '';


		$obj = new VideoData();
		$obj->set( 'id', md5( $url ) );
		$obj->set( 'title', 'Local Untitled Video' );
		$obj->set( 'url', $url );
		$obj->set( 'author_name', $author_name );
		$obj->set( 'author_url', $author_url );
		$obj->set( 'source_id', md5( $url ) );
		$obj->set( 'source_type', 'video' );
		$obj->set( 'provider', 'url' );
		$obj->set( 'thumbnail_url', array() );

		return $obj;
	}
}
