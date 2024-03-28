<?php
/**
 * Display the playlist to the page.
 *
 * @package Easy_Video_Playlist
 * @link    https://easypodcastpro.com/
 * @since   1.1.0
 */

namespace Easy_Video_Playlist\Frontend\Inc;

use Easy_Video_Playlist\Helper\Store\StoreManager;
use Easy_Video_Playlist\Helper\Functions\Markup;

/**
 * Display the playlist to the page.
 * 
 * Enqueue frontend scripts, styles and other resources.
 *
 * @package  Easy_Video_Playlist
 * @since    1.1.0
 */
class Display {

    /**
	 * Holds display attributes.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var array
	 */
	public $attrs = array();

    /**
	 * Holds current playlist ID.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var string
	 */
	public $playlist = false;

    /**
     * Holds the playlist title.
     *
     * @since  1.0.0
     * @access public
     * @var string
     */
    public $pl_title = '';

    /**
	 * Constructor method.
	 *
	 * @since  1.1.0
	 *
	 * @param array $attrs playlist display attributes.
	 */
	public function __construct( $attrs ) {
        $this->attrs    = $attrs;
        $this->playlist = $attrs && is_array( $attrs ) && isset( $attrs['playlist'] ) ? $attrs['playlist'] : false;
    }

    /**
     * Set current instance of the playlist.
     *
     * @since 1.1.0
     *
     * @param string $return Return or Display.
     */
    public function render( $return ) {

        if (! $this->playlist) {
            return __('No playlist provided.', 'easy-video-playlist');
        }

        $store_manager = StoreManager::get_instance();
        $register      = $store_manager->get_register();

        $pl_register = isset( $register[ $this->playlist ] ) ? $register[ $this->playlist ] : false;
        if (! $pl_register) {
            return __('This playlist does not exist any more.', 'easy-video-playlist');
        }
        $this->pl_title = isset( $pl_register['bucket_title'] ) ? $pl_register['bucket_title'] : '';

        $instance = Instance_Counter::get_instance();
        $instance->get();
        $instance->add_playlist( $this->playlist, $this->pl_title );
        if ( $return ) {
            return $this->markup();
        }

        echo $this->markup();
    }

    /**
     * Return playlist markup.
     *
     * @since 1.1.0
     */
    public function markup() {
        return sprintf('
            <div class="evp-video-player" data-playlist="%1$s">
                <div class="evp-playlist__wrapper">
                    <div class="evp-playlist__container">
                        <div class="evp-single-video"></div>
                        <div class=evp-playlist-wrapper>
                            <div class="evp-playlist-video">
                                <div class="evp-playlist-video-header">
                                    <div class="evp-playlist-video-header-title">%2$s</div>
                                    <div class="evp-list__search">
                                        <label class="label-evp-search">
                                            <span class="evp__offscreen">%3$s</span>
                                            <input type="text" placeholder="%4$s" title="%5$s"/>
                                        </label>
                                        <span class="evp-list__search-icon">%6$s</span>
                                        <button class="evp-search-close">
                                            <span>%7$s</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="evp-playlist-video-index"></div>
                                <div class="evp-playlist-video-more-wrapper" style="display: none;">
                                    <button class="evp-playlist-load-more">
                                        <span>%8$s</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="evp-playlist__loading"></div>
            </div>',
            esc_attr( $this->playlist ),
            esc_html( $this->pl_title ),
            esc_html( 'Search Episodes', 'podcast-player' ),
            esc_attr( 'Search Episodes', 'podcast-player' ),
            esc_attr( 'Search Podcast Episodes', 'podcast-player' ),
            Markup::get_icon( array( 'icon' => 'evp-search' ) ),
            Markup::get_icon( array( 'icon' => 'evp-close' ) ),
            __( 'Load More', 'easy-video-playlist' ),
        );
    }
}
