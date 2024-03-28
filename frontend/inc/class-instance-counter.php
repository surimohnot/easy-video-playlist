<?php
/**
 * Instance counter class.
 *
 * @since   1.0.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Frontend\Inc;

use Easy_Video_Playlist\Helper\Core\Singleton;

/**
 * Instance counter.
 *
 * @package    Podcast_Player
 * @author     vedathemes <contact@vedathemes.com>
 */
class Instance_Counter extends Singleton {
	/**
	 * Podcast instance counter.
	 *
	 * @since  1.1.0
	 * @access private
	 * @var    int
	 */
	private $counter = null;

	/**
	 * Check if there is at least one instance of playlist.
	 *
	 * @since  1.1.0
	 * @access private
	 * @var    bool
	 */
	private $has_playlist = false;

	/**
	 * Holds list of the playlists on the current page.
	 *
	 * @since  1.1.0
	 * @access private
	 * @var    array
	 */
	private $playlist = array();

	/**
	 * Class cannot be instantiated directly.
	 *
	 * @since  1.1.0
	 */
	protected function __construct() {
		$this->counter = wp_rand( 1, 10000 );
	}

	/**
	 * Return current instance of a key.
	 *
	 * @since  1.1.0
	 *
	 * @return int
	 */
	public function get() {
		$this->has_playlist    = true;
		return $this->counter += 1;
	}

	/**
	 * Check if there is at least one instance of podcast player.
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	public function has_playlist() {
		global $pagenow;

	    // Check if we are on customizer screen.
		if ( is_customize_preview() ) {
			return true;
		}

		if ( is_admin() && in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			return true;
		}

		//Check if we are on Elementor edit screen.
		if (class_exists('Elementor\Plugin')) {
			if (isset(\Elementor\Plugin::$instance->preview) && is_object(\Elementor\Plugin::$instance->preview)) {
				if (method_exists(\Elementor\Plugin::$instance->preview, 'is_preview_mode') && is_callable(array(\Elementor\Plugin::$instance->preview, 'is_preview_mode'))) {
					if( \Elementor\Plugin::$instance->preview->is_preview_mode()) {
						return true;
					}
				}
			}
		}

		return apply_filters( 'easy_video_playlist_has_playlist', $this->has_playlist );
	}

	/**
	 * Add a playlist to the list.
	 *
	 * @since  1.1.0
	 *
	 * @param string $playlist
	 * @param string $title
	 */
	public function add_playlist( $playlist, $title ) {
		$this->playlist[ $playlist ] = $title;
	}

	/**
	 * Get list of the playlists on the current page.
	 *
	 * @since  1.1.0
	 */
	public function get_playlist() {
		return $this->playlist;
	}
}
