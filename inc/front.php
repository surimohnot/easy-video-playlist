<?php
/**
 * The front-end functionality of the plugin.
 *
 * @link    https://easypodcastpro.com
 * @since   1.0.0
 *
 * @package WP_Video_Player
 */

/**
 * Enqueue front-end scripts and styles for the playlist.
 *
 * @since 1.0.0
 */
function evp_enqueue_scripts($playlists = array()) {
    $ob_cache = evp_object_cache();
    $playlists = $ob_cache->get('playlist');
    $playlists = $playlists ? $playlists : array();
    $pl_data = array();

    if (evp_is_edit_screen()) {
        $playlists = array_keys(evp_get_playlists());
    }

    if (empty($playlists)) {
        return;
    }

    foreach ($playlists as $playlist) {
        $data = evp_get_data($playlist);
        $pl_data[$playlist] = $data ? $data : array();
    }

    wp_enqueue_script(
        'evp-front',
        EVP_URL . 'assets/scripts/front/front.build.js',
        array('jquery'),
        EVP_VERSION,
        true
    );
    wp_localize_script(
        'evp-front',
        'EVP_Front_Data',
        apply_filters(
            'evp_front_script_data',
            array(
                'data' => $pl_data,
                'url'  => home_url(),
            )
        )
    );

    wp_enqueue_style(
        'evp-front',
        EVP_URL . 'assets/styles/front/front.css',
        array(),
        EVP_VERSION
    );
}
add_action('wp_footer', 'evp_enqueue_scripts');
add_action('elementor/editor/before_enqueue_scripts', 'evp_enqueue_scripts');

/**
 * Render the player.
 *
 * @since 1.0.0
 */
function evp_render_player($atts) {
    $playlist = isset($atts['playlist']) ? $atts['playlist'] : '';
    if (! $playlist) {
        return __('No playlist provided.', 'easy-video-playlist');
    }

    $data = evp_get_data($playlist);
    if (! $data) {
        return __('This playlist does not exist any more.', 'easy-video-playlist');
    }

    $ob_cache = evp_object_cache();
    $pl_arr = $ob_cache->get('playlist');
    $pl_arr = $pl_arr ? $pl_arr : array();
    $pl_arr[] = $playlist;
    $ob_cache->set('playlist', array_filter(array_unique($pl_arr)));

    return sprintf('
        <div class="evp-video-player" data-playlist="%1$s">
            <div class="evp-playlist__wrapper">
                <div class="evp-playlist__container">
                    <div class="evp-single-video"></div>
                    <div class=evp-playlist-wrapper>
                        <div class="evp-playlist-video">
                            <div class="evp-playlist-video-index"></div>
                            <div class="evp-playlist-video-more-wrapper" style="display: none;">
                                <button class="evp-playlist-load-more">
                                    <span>%2$s</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="evp-playlist__loading"></div>
        </div>',
        $playlist,
        __( 'Load More', 'easy-video-playlist' ),
    );
}
