<?php
/**
 * Fetch Video Data from Vimeo.
 *
 * @since   1.1.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Playlist;

use Easy_Video_Playlist\Helper\Core\Singleton;
use Easy_Video_Playlist\Helper\Store\VideoData;
use Easy_Video_Playlist\Helper\Functions\Getters as Get_Fn;

/**
 * Fetch Video Data from Vimeo.
 *
 * @since 1.1.0
 */
class Fetch_Vimeo extends Singleton {

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
	 * @param string $source_id   Source ID.
	 * @param string $source_type Source type.
	 */
	public function get_data( $source_id, $source_type ) {
		// Presently we only support video type for vimeo.
		if ( $source_type === 'video' ) {
			return $this->get_data_without_api_key( $source_id );
		}
		return array();
	}

	/**
	 * Get Vimeo video data from the video ID without API key.
	 *
	 * @since  1.2.0
	 *
	 * @param string $video_id Video ID.
	 */
	public function get_data_without_api_key( $video_id ) {
		$data = array();

		$vimeo_video_url = 'https://vimeo.com/' . $video_id;
		$query_params    = array(
			'format' => 'json',
			'url'    => $vimeo_video_url,
		);

		// Final URL with query parameters.
		$final_url    = add_query_arg( $query_params, 'https://vimeo.com/api/oembed.json' );
		$vid_data     = Get_Fn::get_remote_data( $final_url );
		$video_object = $vid_data ? $this->get_no_api_data_object( $vid_data, $video_id ) : false;
		if ( ! $video_object || ! $video_object instanceof VideoData ) {
			return array();
		}
		$data[] = $video_object;
		return $data;
	}

	/**
	 * Get YouTube video data object.
	 *
	 * @since  1.2.0
	 *
	 * @param array  $data Item.
	 * @param string $id ID.
	 */
	private function get_no_api_data_object( $data, $id ) {
		if ( ! $data || ! is_array( $data ) ) {
			return false;
		}

		if ( isset( $data['thumbnail_url'] ) ) {
			$thumb_url = array( $data['thumbnail_url'] );
		} else {
			$thumb_url = array();
		}

		$obj = new VideoData();
		$obj->set( 'id', 'vimeo_' . $id );
		$obj->set( 'date', isset( $data['upload_date'] ) ? $data['upload_date'] : '' );
		$obj->set( 'title', isset( $data['title'] ) ? $data['title'] : '' );
		$obj->set( 'url', 'https://vimeo.com/' . $id );
		$obj->set( 'author_name', isset( $data['author_name'] ) ? $data['author_name'] : '' );
		$obj->set( 'author_url', isset( $data['author_url'] ) ? $data['author_url'] : '' );
		$obj->set( 'duration', isset( $data['duration'] ) ? $data['duration'] : '' );
		$obj->set( 'thumbnail_url', $thumb_url );
		$obj->set( 'source_id', $id );
		$obj->set( 'source_type', 'video' );
		$obj->set( 'provider', 'vimeo' );

		return $obj;
	}
}
