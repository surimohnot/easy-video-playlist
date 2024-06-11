<?php
/**
 * Easy Video Playlist Validation Functions.
 *
 * @since   1.1.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Functions;

/**
 * Easy Video Playlist Validation Functions.
 *
 * @since 1.1.0
 */
class Validation {

	/**
	 * Check if image exists.
	 *
	 * @since 1.1.0
	 */
	public static function is_image_exists( $image_url ) {
		// Check if the URL is empty or not a valid URL
		if ( empty( $image_url ) || !filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
			return false;
		}
	
		// Send a HEAD request to the image URL using wp_remote_head
		$response = wp_remote_head( $image_url );
	
		// Check if the request was successful
		if ( is_wp_error( $response ) ) {
			return false; // Error occurred
		}
	
		// Get the HTTP response code from the response
		$response_code = wp_remote_retrieve_response_code( $response );
	
		// Check if the HTTP response code is in the 200-299 range (image exists)
		if ( $response_code >= 200 && $response_code < 300 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if the URL is a video.
	 *
	 * @since 1.0.0
	 */
	public static function is_video_url( $url ) {
		$video_ext  = wp_get_video_extensions();
		$mime_types = wp_get_mime_types();
		$media_url   = $url ? preg_replace( '/\?.*/', '', $url ) : false;
		if ( $media_url ) {
			$type = wp_check_filetype( $media_url, $mime_types );
			if ( in_array( strtolower( $type['ext'] ), $video_ext, true ) ) {
				return strtolower( $type['ext'] );
			}
		}
		return false;
	}
}
