<?php
/**
 * Object to create basic structure of the data storage.
 *
 * @link       https://easypodcastpro.com
 * @since      1.0.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Store;

/**
 * Provide base functionality to store data.
 *
 * @package Easy_Video_Playlist
 */
class StoreBase {

	/**
	 * Create initial state of the object
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Escape String
	 *
	 * @since 1.0.0
	 *
	 * @param string $val     Value to escape.
	 * @param string $context Context.
	 */
	protected function string( $val, $context ) {
		if ( 'sanitize' === $context ) {
			return sanitize_text_field( $val );
		} else {
			return esc_html( $val );
		}
	}

	/**
	 * Escape Attributes
	 *
	 * @since 1.0.0
	 *
	 * @param string $val     Value to escape.
	 * @param string $context Context.
	 */
	protected function attr( $val, $context ) {
		if ( 'sanitize' === $context ) {
			return sanitize_text_field( $val );
		} else {
			return esc_attr( $val );
		}
	}

	/**
	 * Escape URL
	 *
	 * @since 1.0.0
	 *
	 * @param string $val     Value to escape.
	 * @param string $context Context.
	 */
	protected function url( $val, $context ) {
		if ( 'sanitize' === $context ) {
			return esc_url_raw( $val );
		} else {
			return esc_attr( esc_url( $val ) );
		}
	}

	/**
	 * HTML Title
	 *
	 * @since 1.0.0
	 *
	 * @param string $val     Value to escape.
	 * @param string $context Context.
	 */
	protected function title( $val, $context ) {
		if ( ! $val ) {
			return '';
		}
		if ( 'sanitize' === $context ) {
			return wp_kses_post( wp_check_invalid_utf8( htmlspecialchars_decode( $val ) ) );
		} else {
			return trim( convert_chars( wptexturize( str_replace( '&quot;', '&#8221;', $val ) ) ) );
		}
	}

	/**
	 * HTML Content
	 *
	 * @since 1.0.0
	 *
	 * @param string $val     Value to escape.
	 * @param string $context Context.
	 */
	protected function desc( $val, $context ) {
		if ( ! $val ) {
			return '';
		}
		if ( 'sanitize' === $context ) {
			return wp_kses_post( wp_check_invalid_utf8( $val ) );
		} else {
			return wpautop( wptexturize( str_replace( '&quot;', '&#8221;', trim( $val ) ) ) );
		}
	}

	/**
	 * Integer
	 *
	 * @since 1.0.0
	 *
	 * @param integer $val    Value to escape.
	 * @param string  $context Context.
	 */
	protected function int( $val, $context ) {
		return absint( $val );
	}

	/**
	 * Array of strings
	 *
	 * @since 1.0.0
	 *
	 * @param array  $val      Value to escape.
	 * @param string $context Context.
	 */
	protected function arr_string( $val, $context ) {
		if ( 'sanitize' === $context ) {
			return array_map( 'sanitize_text_field', $val );
		} else {
			return array_map( 'esc_html', $val );
		}
	}

	/**
	 * Array of Arrays with strings
	 *
	 * @since 1.0.0
	 *
	 * @param array  $val      Value to escape.
	 * @param string $context Context.
	 */
	protected function arr_arr_string( $val, $context ) {
		$value = array();
		if ( 'sanitize' === $context ) {
			return sanitize_array_recursive( $val );
		} else {
			return esc_array_recursive( $val );
		}
	}

	/**
	 * Sanitize Array Recursively.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Value to escape.
	 */
	private function sanitize_array_recursive( $data ) {
		if (is_array($data)) {
			$sanitized_data = array();
			foreach ($data as $key => $value) {
				$sanitized_key   = sanitize_text_field($key);
				$sanitized_value = $this->sanitize_array_recursive($value);
				$sanitized_data[$sanitized_key] = $sanitized_value;
			}
			return $sanitized_data;
		} else {
			return sanitize_text_field($data);
		}
	}

	/**
	 * Escape Array Recursively.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Value to escape.
	 */
	private function esc_array_recursive( $data ) {
		if (is_array($data)) {
			$escaped_data = array();
			foreach ($data as $key => $value) {
				$escaped_key   = esc_html($key);
				$escaped_value = $this->esc_array_recursive($value);
				$escaped_data[$escaped_key] = $escaped_value;
			}
			return $escaped_data;
		} else {
			return esc_html($data);
		}
	}

	/**
	 * Array of URLs
	 *
	 * @since 1.0.0
	 *
	 * @param array  $val      Value to escape.
	 * @param string $context Context.
	 */
	protected function arr_url( $val, $context ) {
		if ( 'sanitize' === $context ) {
			return array_map( 'esc_url_raw', $val );
		} else {
			if ( ! $val ) {
				return $val;
			}
			return array_map( 'esc_url', $val );
		}
	}

	/**
	 * Properly format audio time.
	 *
	 * @since 1.0.0
	 *
	 * @param string $val     Value to escape.
	 * @param string $context Context.
	 */
	protected function dur( $val, $context ) {
		if ( 'sanitize' === $context ) {
			if ( ! $val ) {
				return false;
			}
			$time = sanitize_text_field( $val );
			$sec  = 0;
			$ta   = array_reverse( explode( ':', $time ) );
			foreach ( $ta as $key => $value ) {
				$sec += absint( $value ) * pow( 60, $key );
			}
			return $sec;
		} else {
			return absint( $val );
		}
	}

	/**
	 * Properly format episode date.
	 *
	 * @since 1.0.0
	 *
	 * @param string $val     Value to escape.
	 * @param string $context Context.
	 */
	protected function date( $val, $context ) {
		if ( 'sanitize' === $context ) {
			return sanitize_text_field( $val );
		} else {
			$unix = strtotime( $val );
			if ( $unix ) {
				return $unix;
			} else {
				if ( is_numeric( $val ) ) {
					return absint( $val );
				}
			}
			return absint( '' );
		}
	}

	/**
	 * Properly format emails.
	 *
	 * @since 1.0.0
	 *
	 * @param string $val     Value to escape.
	 * @param string $context Context.
	 */
	protected function email( $val, $context ) {
		return sanitize_email( $val );
	}

	/**
	 * No change.
	 *
	 * @since 1.0.0
	 *
	 * @param string $val     Value to escape.
	 * @param string $context Context.
	 */
	protected function none( $val, $context ) {
		return $val;
	}

	/**
	 * Get keys of all object properties.
	 *
	 * @since 6.5.0
	 */
	public function get_vars() {
		return get_object_vars( $this );
	}

	/**
	 * Actual set method to update object properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $name    Name or array of names.
	 * @param mixed        $value   Value to be set.
	 * @param string       $context Context.
	 */
	public function set( $name, $value = false, $context = 'sanitize' ) {
		if ( ! is_array( $name ) ) {
			if ( property_exists( $this, $name ) ) {
				if ( 'none' === $context ) {
					$this->$name = $value;
					return true;
				}
				$sanitize_arr = $this->typeDeclaration();
				$sanitize     = isset( $sanitize_arr[ $name ] ) ? $sanitize_arr[ $name ] : 'string';
				if ( method_exists( $this, $sanitize ) ) {
					$this->$name = $this->$sanitize( $value, $context );
				} else {
					return false;
				}
				return true;
			}
			return false;
		}
		foreach ( $name as $k => $v ) {
			$this->set( $k, $v, $context );
		}
	}

	/**
	 * Get method to access a single object property.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $name    Name or array of names.
	 * @param string       $context echo or db.
	 */
	public function get( $name, $context = 'echo' ) {
		if ( ! is_array( $name ) ) {
			if ( property_exists( $this, $name ) ) {
				if ( isset( $this->custom_values[ $name ] ) ) {
					$val = $this->custom_values[ $name ];
				} else {
					$val = $this->$name;
				}
				$esc_arr = $this->typeDeclaration();
				$esc     = isset( $esc_arr[ $name ] ) ? $esc_arr[ $name ] : 'string';
				return $this->$esc( $val, $context );
			}
			return '';
		}
		$return = array();
		foreach ( $name as $key ) {
			$return[ $key ] = $this->get( $key, $context );
		}
		return $return;
	}

	/**
	 * Set method specifically to store modified values of object properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $name    Name or array of names.
	 * @param mixed        $value   Value to be set.
	 * @param string       $context Context.
	 */
	public function set_custom( $name, $value = false, $context = 'sanitize' ) {
		if ( ! is_array( $name ) ) {
			if ( property_exists( $this, $name ) ) {
				if ( 'none' === $context ) {
					$this->custom_values[ $name ] = $value;
					return true;
				}
				$sanitize_arr = $this->typeDeclaration();
				$sanitize     = isset( $sanitize_arr[ $name ] ) ? $sanitize_arr[ $name ] : 'string';
				if ( method_exists( $this, $sanitize ) ) {
					$this->custom_values[ $name ] = $this->$sanitize( $value, $context );
				} else {
					return false;
				}
				return true;
			}
			return false;
		}
		foreach ( $name as $k => $v ) {
			$this->set_custom( $k, $v, $context );
		}
	}
}
