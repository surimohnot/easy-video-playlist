<?php
/**
 * Base class to generate various playlist markup.
 *
 * @since   1.1.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Functions;

use Easy_Video_Playlist\Helper\Core\Icon_Loader;

/**
 * Base class to generate playlist markup.
 *
 * @since 1.1.0
 */
class Markup {
    /**
     * Display font icon SVG markup.
     *
     * @param array $args {
     *     Parameters needed to display an SVG.
     *
     *     @type string $icon  Required SVG icon filename.
     *     @type string $title Optional SVG title.
     *     @type string $desc  Optional SVG description.
     * }
     */
    public static function the_icon( $args = array() ) {
        echo self::get_icon( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Return font icon SVG markup.
     *
     * This function incorporates code from Twenty Seventeen WordPress Theme,
     * Copyright 2016-2017 WordPress.org. Twenty Seventeen is distributed
     * under the terms of the GNU GPL.
     *
     * @param array $args {
     *     Parameters needed to display an SVG.
     *
     *     @type string $icon  Required SVG icon filename.
     *     @type string $title Optional SVG title.
     *     @type string $desc  Optional SVG description.
     * }
     * @return string Font icon SVG markup.
     */
    public static function get_icon( $args = array() ) {
        // Make sure $args are an array.
        if ( empty( $args ) ) {
            return esc_html__( 'Please define default parameters in the form of an array.', 'podcast-player' );
        }

        // Define an icon.
        if ( false === array_key_exists( 'icon', $args ) ) {
            return esc_html__( 'Please define an SVG icon filename.', 'podcast-player' );
        }

        // Add icon to icon loader array.
        $loader = Icon_Loader::get_instance();
        $loader->add( $args['icon'] );

        // Set defaults.
        $defaults = array(
            'icon'     => '',
            'title'    => '',
            'desc'     => '',
            'fallback' => false,
        );

        // Parse args.
        $args = wp_parse_args( $args, $defaults );

        // Set aria hidden.
        $aria_hidden = ' aria-hidden="true"';

        // Set ARIA.
        $aria_labelledby = '';

        /*
        * Podcast Player doesn't use the SVG title or description attributes; non-decorative icons are
        * described with .ppjs__offscreen. However, child themes can use the title and description
        * to add information to non-decorative SVG icons to improve accessibility.
        *
        * Example 1 with title: <?php echo podcast_player_get_svg( [ 'icon' => 'arrow-right', 'title' => __( 'This is the title', 'textdomain' ) ] ); ?>
        *
        * Example 2 with title and description: <?php echo podcast_player_get_svg( [ 'icon' => 'arrow-right', 'title' => __( 'This is the title', 'textdomain' ), 'desc' => __( 'This is the description', 'textdomain' ) ] ); ?>
        *
        * See https://www.paciellogroup.com/blog/2013/12/using-aria-enhance-svg-accessibility/.
        */
        if ( $args['title'] ) {
            $aria_hidden     = '';
            $unique_id       = uniqid();
            $aria_labelledby = ' aria-labelledby="title-' . $unique_id . '"';

            if ( $args['desc'] ) {
                $aria_labelledby = ' aria-labelledby="title-' . $unique_id . ' desc-' . $unique_id . '"';
            }
        }

        // Begin SVG markup.
        $svg = '<svg class="icon icon-' . esc_attr( $args['icon'] ) . '"' . $aria_hidden . $aria_labelledby . ' role="img" focusable="false">';

        // Display the title.
        if ( $args['title'] ) {
            $svg .= '<title id="title-' . $unique_id . '">' . esc_html( $args['title'] ) . '</title>';

            // Display the desc only if the title is already set.
            if ( $args['desc'] ) {
                $svg .= '<desc id="desc-' . $unique_id . '">' . esc_html( $args['desc'] ) . '</desc>';
            }
        }

        /*
        * Display the icon.
        *
        * The whitespace around `<use>` is intentional - it is a work around to a keyboard navigation bug in Safari 10.
        *
        * See https://core.trac.wordpress.org/ticket/38387.
        */
        $svg .= ' <use href="#icon-' . esc_attr( $args['icon'] ) . '" xlink:href="#icon-' . esc_attr( $args['icon'] ) . '"></use> ';

        // Add some markup to use as a fallback for browsers that do not support SVGs.
        if ( $args['fallback'] ) {
            $svg .= '<span class="svg-fallback icon-' . esc_attr( $args['icon'] ) . '"></span>';
        }

        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Get template partial path.
     */
    public static function get_partial($partial) {
        $path = EVP_DIR . 'helper/templates/partials/' . $partial . '.php';
        return apply_filters( 'evp_get_partial', $partial, $path );
    }
}
