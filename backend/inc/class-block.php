<?php
/**
 * Block API: Display Easy Video Playlist
 *
 * @package Easy_Video_Playlist
 * @link    https://easypodcastpro.com/
 * @since   1.0.0
 */

namespace Easy_Video_Playlist\Backend\Inc;

use Easy_Video_Playlist\Helper\Core\Singleton;
use Easy_Video_Playlist\Frontend\Inc\Display;

/**
 * Manage Easy Video Playlist Block.
 *
 * @package  Easy_Video_Playlist
 * @since    1.1.0
 */
class Block extends Singleton {
	/**
	 * Register editor block for featured content.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		// Check if the register function exists.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
            'evp-block/evp-block',
            array(
                'render_callback' => array( $this, 'render_block' ),
                'attributes'      => apply_filters(
                    'evp_block_attr',
                    array(
                        'playlist' => array(
                            'type'    => 'string',
                            'default' => '',
                        ),
                    )
                ),
            )
        );
	}

	/**
	 * Render editor block for Easy Video Playlist.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Display attributes.
	 */
	public function render_block( $atts ) {
		$display = new Display( $atts );
        $return  = true;
		return $display->render( $return );
	}
}
