<?php
/**
 * Singleton base class.
 *
 * @package Easy_Video_Playlist
 * @link    https://easypodcastpro.com/
 * @since   1.0.0
 */

namespace Easy_Video_Playlist\Lib;

/**
 * Singleton base class.
 *
 * @since 1.0.0
 */
class Singleton {
	/**
	 * Holds all singleton instances.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Singleton[] $instances
	 */
	private static $instances = array();

	/**
	 * Construct.
	 *
	 * The Singleton's constructor should always be private to prevent direct
	 * construction calls with the `new` operator.
	 *
	 * @since 1.0.0
	 */
	protected function __construct()
	{
	}

	/**
	 * Clone.
	 *
	 * Singletons should not be cloneable.
	 *
	 * @since 1.0.0
	 */
	public function __clone()
	{
		// Cloning instances of the class is forbidden.
		_doing_it_wrong(__FUNCTION__, __('Cannot clone a singleton.', 'easy-video-playlist'), '1.0.0');
	}

	/**
	 * Sleep.
	 *
	 * Disable serializing of the class.
	 *
	 * @since 1.0.0
	 */
	public function __sleep()
	{
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong(__FUNCTION__, __('Cannot serialize a singleton.', 'easy-video-playlist'), '1.0.0');
	}

	/**
	 * Wakeup.
	 *
	 * Disable unserializing of the class.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup()
	{
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong(__FUNCTION__, __('Cannot unserialize a singleton.', 'easy-video-playlist'), '1.0.0');
	}

	/**
	 * Get Instance.
	 *
	 * This is the static method that controls access to the singleton instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance()
	{
		$class = get_called_class();

		if (! isset(self::$instances[ $class ])) {
			self::$instances[ $class ] = new $class();
		}
		return self::$instances[ $class ];
	}
}
