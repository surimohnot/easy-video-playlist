<?php
/**
 * Plugin Object Cache.
 *
 * @package Easy_Video_Playlist
 * @link    https://easypodcastpro.com/
 * @since   1.0.0
 */

namespace Easy_Video_Playlist\Lib;

/**
 * Object Cache.
 *
 * @package    Podcast_Player
 * @author     vedathemes <contact@vedathemes.com>
 */
class Object_Cache extends Singleton {
	/**
	 * Holds the cached data.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    array
	 */
    private $cache = array();

    /**
     * Add data to the cache object.
     *
     * @since 1.0.0
     * @access public
     * @param string $key
     * @param mixed  $value
     */
    public function set( $key, $value ) {
        $this->cache[ $key ] = $value;
    }

    /**
     * Get data from the cache object.
     * 
     * @since 1.0.0
     */
    public function get( $key ) {
        return isset( $this->cache[ $key ] ) ? $this->cache[ $key ] : false;
    }

    /**
     * Remove data from the cache object.
     * 
     * @since 1.0.0
     * @access public
     * @param  string $key
     */
    public function remove( $key ) {
        unset( $this->cache[ $key ] );
    }
}
