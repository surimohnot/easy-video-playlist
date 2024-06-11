<?php
/**
 * Easy Video Playlist Validation Functions.
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
 * Easy Video Playlist Validation Functions.
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
	 * @param array $video_ids Video IDs.
	 */
	public function video_data( $video_ids ) {
		return $this->video_data_without_api_key( $video_ids );
	}

	/**
	 * Get YouTube video data from the video IDs without API key.
	 *
	 * @since  1.2.0
	 *
	 * @param array $video_ids Video IDs.
	 */
	public function video_data_without_api_key( $video_ids ) {
		$data = array();

		foreach ( $video_ids as $video_id => $source ) {
			$vimeo_video_url = 'https://vimeo.com/' . $video_id;
			$query_params    = array(
				'format' => 'json',
				'url'    => $vimeo_video_url,
			);

			// Final URL with query parameters.
			$final_url    = add_query_args( $query_params, 'https://vimeo.com/api/oembed.json' );
			$vid_data     = Get_Fn::get_remote_data( $final_url );
			$video_object = $vid_data ? $this->video_data_object( $vid_data, $video_id ) : false;
			if ( ! $video_object || ! $video_object instanceof VideoData ) {
				continue;
			}
			$data[] = $video_object;
		}

		return $data;
	}

	/**
	 * Get YouTube video data object.
	 *
	 * @since  1.2.0
	 *
	 * @param array  $item Item.
	 * @param string $id ID.
	 */
	private function video_data_object( $item, $id ) {
		if ( ! $item || ! is_array( $item ) ) {
			return false;
		}

		if ( isset( $data['thumbnail_url'] ) ) {
			$thumb_url = array( $data['thumbnail_url'] );
		}

		$obj = new VideoData();
		$obj->set( 'id', $id );
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
