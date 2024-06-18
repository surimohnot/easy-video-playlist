<?php
/**
 * Easy Video Playlist Utility Functions.
 *
 * @since   1.1.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Functions;

/**
 * Easy Video Playlist Utility Functions.
 *
 * @since 1.1.0
 */
class Utility {

	/**
	 * Convert camelCase to snake_case
	 *
	 * @since 1.2.0
	 *
	 * @param string $str String to convert.
	 */
	public static function to_snake_case( $str ) {
		return strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $str ) );
	}
}
