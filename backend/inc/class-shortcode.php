<?php
/**
 * Shortcode API: Display Easy Video Playlist
 *
 * @package Easy_Video_Playlist
 * @link    https://easypodcastpro.com/
 * @since   1.0.0
 */

namespace Easy_Video_Playlist\Backend\Inc;

use Easy_Video_Playlist\Helper\Core\Singleton;
use Easy_Video_Playlist\Frontend\Inc\Display;

/**
 * Manage Easy Video Playlist Shortcode.
 *
 * @package  Easy_Video_Playlist
 * @since    1.1.0
 */
class Shortcode extends Singleton {
	/**
	 * Register editor block for featured content.
	 *
	 * @since 1.0.0
     *
     * @param array $atts User defined attributes in shortcode tag.
	 * @param str   $content Shortcode text content.
	 */
	public function render( $atts, $content = null ) {
		$display = new Display( $atts );
        $return  = true;
		return $display->render( $return );
	}
}
