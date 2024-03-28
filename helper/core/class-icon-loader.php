<?php
/**
 * Instance counter class.
 *
 * @since   1.0.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Core;

/**
 * Load required font icons.
 *
 * @since 1.0.0
 */
class Icon_Loader extends Singleton {
	/**
	 * Holds all required font icons.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    array
	 */
	private $icons = array();

	/**
	 * Adds a font icon to icons array.
	 *
	 * @since  1.0.0
	 *
	 * @param str $icon Icon to be added.
	 */
	public function add( $icon ) {
		if ( ! in_array( $icon, $this->icons, true ) ) {
			$this->icons[] = $icon;
		}
	}

	/**
	 * Adds a font icon to footer the web page.
	 *
	 * @since  1.0.0
	 */
	public function add_icons() {
		if ( empty( $this->icons ) ) {
			return;
		}

		$icons = '<svg style="position: absolute; width: 0; height: 0; overflow: hidden;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><defs>';

		$icons_def = $this->get_font_icons_def();
		foreach ( $this->icons as $icon ) {
			if ( isset( $icons_def[ $icon ] ) ) {
				$icons .= $icons_def[ $icon ];
			}
		}

		$icons .= '</defs></svg>';
		echo $icons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * SVG icons definition.
	 *
	 * @since 1.0.0
	 */
	public function get_font_icons_def() {
		/*
		 * Icons Licensing Information.
		 * 1. Material design svg icons. [https://github.com/google/material-design-icons] Google - Apache-2.0
		 */
		return apply_filters(
			'evp_icon_fonts_def',
			array(
				'evp-search' => '<symbol id="icon-evp-search" viewBox="0 0 30 32"><path d="M20.571 14.857c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8 8-3.589 8-8zM29.714 29.714c0 1.25-1.036 2.286-2.286 2.286-0.607 0-1.196-0.25-1.607-0.679l-6.125-6.107c-2.089 1.446-4.589 2.214-7.125 2.214-6.946 0-12.571-5.625-12.571-12.571s5.625-12.571 12.571-12.571 12.571 5.625 12.571 12.571c0 2.536-0.768 5.036-2.214 7.125l6.125 6.125c0.411 0.411 0.661 1 0.661 1.607z"></path></symbol>',
				'evp-close'  => '<symbol id="icon-evp-close" viewBox="0 0 32 32"><path d="M25.313 8.563l-7.438 7.438 7.438 7.438-1.875 1.875-7.438-7.438-7.438 7.438-1.875-1.875 7.438-7.438-7.438-7.438 1.875-1.875 7.438 7.438 7.438-7.438z"></path></symbol>',
			)
		);
	}
}
