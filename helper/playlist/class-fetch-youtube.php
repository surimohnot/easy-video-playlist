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
use Easy_Video_Playlist\Helper\Functions\Validation;

/**
 * Easy Video Playlist Validation Functions.
 *
 * @since 1.1.0
 */
class Fetch_Youtube extends Singleton {

	/**
	 * Holds YouTube API key.
	 *
	 * @since  1.2.0
	 * @access private
	 * @var    string
	 */
	private $youtube_api_key = false;

	/**
	 * Maximum number of results required.
	 *
	 * @since  1.2.0
	 * @access private
	 * @var    int
	 */
	private $max_results = 500;

	/**
	 * Constructor method.
	 *
	 * @since  1.2.0
	 */
	public function __construct() {
		$settings_api          = get_option( 'evp_settings_api' );
		$settings_api          = $settings_api && is_array( $settings_api ) ? $settings_api : array();
		$this->youtube_api_key = isset( $settings_api['youtube'] ) ? $settings_api['youtube'] : false;
	}

	/**
	 * Get YouTube video IDs from the playlist.
	 *
	 * @since  1.1.0
	 *
	 * @param string $playlist_id Playlist ID.
	 */
	public function video_ids_from_playlist( $playlist_id ) {
		if ( ! $this->youtube_api_key ) {
			return array(); // TODO: Some sort of error handling is required here.
		}

		$request_url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=50&playlistId=' . $playlist_id . '&key=' . $this->youtube_api_key;
		return $this->multi_fetch_yt_video_ids_from_url( $request_url, 'playlist' );
	}

	/**
	 * Get YouTube video IDs from the channel.
	 *
	 * @since  1.1.0
	 *
	 * @param string $channel_id Channel ID.
	 */
	public function video_ids_from_channel( $channel_id ) {
		if ( ! $this->youtube_api_key ) {
			return array(); // TODO: Some sort of error handling is required here.
		}

		$request_url = 'https://www.googleapis.com/youtube/v3/search?part=id&channelId=' . $channel_id . '&maxResults=50&type=video&key=' . $this->youtube_api_key;
		return $this->multi_fetch_yt_video_ids_from_url( $request_url, 'channel' );
	}

	/**
	 * Get YouTube video IDs from the user ID.
	 *
	 * @since  1.1.0
	 *
	 * @param string $username User Name.
	 */
	public function video_ids_from_user( $username ) {
		if ( ! $this->youtube_api_key ) {
			return array(); // TODO: Some sort of error handling is required here.
		}

		$request_url  = 'https://www.googleapis.com/youtube/v3/channels?part=id&forUsername=' . $username . '&key=' . $this->youtube_api_key;
		$all_channels = Get_Fn::get_remote_data( $request_url );
		if ( ! $all_channels || ! is_array( $all_channels ) || ! isset( $all_channels['items'] ) || ! isset( $all_channels['items'][0]['id'] ) ) {
			return array(); // TODO: Some sort of error handling is required here.
		}

		// Get first channel ID of the channel user.
		$first_channel_id = $all_channels['items'][0]['id'];

		// Use available method to fetch video IDs from a given channel ID.
		return $this->get_yt_video_ids_from_channel( $first_channel_id );
	}

	/**
	 * Get YouTube video IDs from the channel user ID.
	 *
	 * @since  1.1.0
	 *
	 * @param string $handle Channel Handle ID.
	 */
	public function video_ids_from_channeluser( $handle ) {
		if ( ! $this->youtube_api_key ) {
			return array(); // TODO: Some sort of error handling is required here.
		}

		$request_url  = 'https://www.googleapis.com/youtube/v3/channels?part=id&forHandle=' . $handle . '&key=' . $this->youtube_api_key;
		$all_channels = Get_Fn::get_remote_data( $request_url );
		if ( ! $all_channels || ! is_array( $all_channels ) || ! isset( $all_channels['items'] ) || ! isset( $all_channels['items'][0]['id'] ) ) {
			return array(); // TODO: Some sort of error handling is required here.
		}

		// Get first channel ID of the channel user.
		$first_channel_id = $all_channels['items'][0]['id'];

		// Use available method to fetch video IDs from a given channel ID.
		return $this->get_yt_video_ids_from_channel( $first_channel_id );
	}

	/**
	 * Fetch all video IDs from multiple requests until required number of results are fetched.
	 *
	 * @since  1.1.0
	 *
	 * @param string $request_url Request URL.
	 * @param string $context Context.
	 */
	private function multi_fetch_yt_video_ids_from_url( $request_url, $context ) {
		// Fetch first batch of video IDs from the request URL.
		list( $ids, $next_page_token ) = $this->fetch_yt_video_ids_from_url( $request_url, $context );

		// If no video IDs are found, return empty array.
		if ( ! $ids ) {
			return array();
		}

		// Run loop to fetch remaining batches of video IDs.
		$total = count( $ids );
		while ( $total < $this->max_results && $next_page_token ) {
			$request_url                   .= '&pageToken=' . $next_page_token;
			list( $nids, $next_page_token ) = $this->fetch_yt_video_ids_from_url( $request_url, $context );
			$ids                            = array_merge( $ids, $nids );
			$total                          = count( $ids );
		}

		return $ids;
	}

	/**
	 * Fetch YouTube video IDs from the request URL.
	 *
	 * @since  1.1.0
	 *
	 * @param string $request_url Request URL.
	 * @param string $context Context.
	 */
	private function fetch_yt_video_ids_from_url( $request_url, $context ) {
		$ids  = array();
		$data = Get_Fn::get_remote_data( $request_url );
		if ( ! $data || ! is_array( $data ) || ! isset( $data['pageInfo'] ) || ! isset( $data['items'] ) ) {
			return $ids;
		}
		$ids             = array_merge( $ids, $this->get_yt_video_ids_from_array( $data['items'], $context ) );
		$next_page_token = isset( $data['nextPageToken'] ) && $data['nextPageToken'] ? $data['nextPageToken'] : false;
		return array( $ids, $next_page_token );
	}

	/**
	 * Get YouTube video IDs from the array.
	 *
	 * @since  1.1.0
	 *
	 * @param array  $items Items.
	 * @param string $context Context.
	 */
	private function get_yt_video_ids_from_array( $items, $context ) {
		$ids = array();
		foreach ( $items as $item ) {
			if ( strpos( $context, 'channel' ) !== false ) {
				$vid_id = isset( $item['id']['videoId'] ) ? $item['id']['videoId'] : '';
			} else {
				$snippet = $item['snippet'] ? $item['snippet'] : array();
				$vid_id  = isset( $snippet['resourceId']['videoId'] ) ? $snippet['resourceId']['videoId'] : '';
			}
			if ( ! $vid_id ) {
				continue;
			}
			$ids = array_merge( $ids, array( trim( $vid_id ) ) );
		}
		return $ids;
	}

	/**
	 * Get YouTube video data from the video IDs.
	 *
	 * @since  1.2.0
	 *
	 * @param array $video_ids Video IDs.
	 */
	public function video_data( $video_ids ) {
		if ( ! $this->youtube_api_key ) {
			return $this->video_data_without_api_key( $video_ids );
		}
		return $this->video_data_with_api_key( $video_ids );
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
			$yt_video_url = 'https://www.youtube.com/watch?v=' . $video_id;
			$query_params = array(
				'format' => 'json',
				'url'    => $yt_video_url,
			);

			// Final URL with query parameters.
			$final_url    = add_query_args( $query_params, 'https://www.youtube.com/oembed' );
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

		$video_url = 'https://www.youtube.com/watch?v=' . $id;
		$img_url   = sprintf( 'https://img.youtube.com/vi/%s/%s', $id, 'mqdefault.jpg' );
		if ( Validation::is_image_exists( $img_url ) ) {
			$thumb_url[] = $img_url;
		}
		if ( isset( $data['thumbnail_url'] ) ) {
			$thumb_url[] = $data['thumbnail_url'];
		}
		$title  = isset( $data['title'] ) ? $data['title'] : '';
		$a_name = isset( $data['author_name'] ) ? $data['author_name'] : '';
		$a_url  = isset( $data['author_url'] ) ? $data['author_url'] : '';

		$obj = new VideoData();
		$obj->set( 'id', $id );
		$obj->set( 'title', $title );
		$obj->set( 'url', $video_url );
		$obj->set( 'author_name', $a_name );
		$obj->set( 'author_url', $a_url );
		$obj->set( 'thumbnail_url', $thumb_url );
		$obj->set( 'source_id', $id );
		$obj->set( 'source_type', 'video' );
		$obj->set( 'provider', 'youtube' );

		return $obj;
	}

	/**
	 * Get YouTube video data from the video IDs with API key.
	 *
	 * @since  1.2.0
	 *
	 * @param array $video_ids Video IDs.
	 */
	public function video_data_with_api_key( $video_ids ) {

		// Convert $video_ids array to small chunks of 40 IDs.
		$chunked_video_ids = array_chunk( array_unique( $video_ids ), 40 );

		$data = array();
		foreach ( $chunked_video_ids as $chunk ) {
			$request_url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,status,statistics&id=' . implode( ',', array_keys( $chunk ) ) . '&key=' . $this->youtube_api_key;
			$chunk_data  = Get_Fn::get_remote_data( $request_url );
			if ( ! $chunk_data || ! is_array( $chunk_data ) || ! isset( $chunk_data['items'] ) ) {
				continue;
			}
			$items = $chunk_data['items'];
			foreach ( $items as $item ) {
				$item_id = isset( $item['id'] ) ? sanitize_text_field( $item['id'] ) : false;
				if ( ! $item_id || ! isset( $video_ids[ $item_id ] ) ) {
					continue;
				}
				$video_api_data = $this->video_api_data_object( $item, $video_ids[ $item_id ] );
				if ( ! $video_api_data || ! $video_api_data instanceof VideoData ) {
					continue;
				}
				$data[] = $video_api_data;
			}
		}

		return $data;
	}

	/**
	 * Get YouTube video api data objects.
	 *
	 * @since  1.2.0
	 *
	 * @param array $item   Item.
	 * @param array $source Source Information.
	 */
	private function video_api_data_object( $item, $source ) {
		$item_id = isset( $item['id'] ) ? $item['id'] : false;
		if ( ! $item_id ) {
			return false;
		}
		$source_type = isset( $source['type'] ) ? $source['type'] : false;
		$source_id   = isset( $source['id'] ) ? $source['id'] : false;
		$obj         = new VideoData();
		$obj->set( 'title', isset( $item['snippet']['title'] ) ? $item['snippet']['title'] : '' );
		$obj->set( 'date', isset( $item['snippet']['publishedAt'] ) ? $item['snippet']['publishedAt'] : '' );
		$obj->set( 'thumbnail_url', isset( $item['snippet']['thumbnails']['high']['url'] ) ? array( $item['snippet']['thumbnails']['high']['url'] ) : array() );
		$obj->set( 'tags', isset( $item['snippet']['tags'] ) ? $item['snippet']['tags'] : '' );
		$obj->set( 'author_name', isset( $item['snippet']['channelTitle'] ) ? $item['snippet']['channelTitle'] : '' );
		$obj->set( 'author_url', isset( $item['snippet']['channelId'] ) ? 'https://www.youtube.com/channel/' . $item['snippet']['channelId'] : '' );
		$obj->set( 'author_id', isset( $item['snippet']['channelId'] ) ? $item['snippet']['channelId'] : '' );
		$obj->set( 'duration', isset( $item['contentDetails']['duration'] ) ? $item['contentDetails']['duration'] : '' );
		$obj->set( 'upload_status', isset( $item['status']['uploadStatus'] ) ? $item['status']['uploadStatus'] : '' );
		$obj->set( 'privacy_status', isset( $item['status']['privacyStatus'] ) ? $item['status']['privacyStatus'] : '' );
		$obj->set( 'view_count', isset( $item['statistics']['viewCount'] ) ? $item['statistics']['viewCount'] : '' );
		$obj->set( 'comment_count', isset( $item['statistics']['commentCount'] ) ? $item['statistics']['commentCount'] : '' );
		$obj->set( 'like_count', isset( $item['statistics']['likeCount'] ) ? $item['statistics']['likeCount'] : '' );
		$obj->set( 'url', 'https://www.youtube.com/watch?v=' . $item_id );
		$obj->set( 'provider', 'youtube' );
		$obj->set( 'source_type', $source_type );
		$obj->set( 'source_id', $source_id );
		$obj->set( 'id', $item_id );

		return $obj;
	}
}
