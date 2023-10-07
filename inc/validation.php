<?php
/**
 * The validation functionality of this plugin.
 *
 * @link    https://easypodcastpro.com
 * @since   1.0.0
 *
 * @package Easy_Video_Playlist
 */

/**
 * Check if we are on the edit page.
 *
 * @since 1.0.0
 */
function evp_is_edit_screen() {
    global $pagenow;

    // Check if we are on customizer screen.
    if (is_customize_preview()) {
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

    //make sure we are on the backend
    if (!is_admin()) {
        return false;
    }

    return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
}
