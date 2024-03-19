<?php
/**
 * The admin functionality of the plugin.
 *
 * @link    https://easypodcastpro.com
 * @since   1.0.0
 *
 * @package Easy_Video_Playlist
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Add and manage admin page for the plugin.
 *
 * @since 1.0.0
 */
function evp_register_admin_page() {

    // Add the admin page.
    $suffix = add_menu_page(
        esc_html__('Easy Video Playlist', 'easy-video-playlist'),
		esc_html__('Easy Video Playlist', 'easy-video-playlist'),
        'manage_options',
        'evp_settings',
        function() {
            include_once EVP_DIR . 'templates/admin-page.php';
        },
        'dashicons-playlist-video',
    );

    // Add script on the plugin admin page.
    add_action(
        'admin_print_scripts-' . $suffix,
        function() {
            $api = get_option( 'evp_settings_api' );
            $api = $api && is_array( $api ) ? $api : array();
            $yt_api_key = isset( $api[ 'youtube' ] ) && $api[ 'youtube' ] ? true : false;
            wp_enqueue_script(
                'evp-admin',
                EVP_URL . 'assets/scripts/admin/admin.build.js',
                array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'),
                EVP_VERSION,
                true
            );

            // Load inline script data on the plugin admin page.
            wp_localize_script(
                'evp-admin',
                'EVP_Admin_Data',
                apply_filters(
                    'evp_admin_script_data',
                    array(
                        'ajaxurl'       => admin_url('admin-ajax.php'),
                        'security'      => wp_create_nonce( 'evp-admin-ajax-nonce' ),
                        'videoPlaylist' => evp_get_playlists(),
                        'i18n'          => evp_get_admin_i18n(),
                        'api'           => array( 'youtube' => $yt_api_key ),
                        'setpage'       => esc_url( add_query_arg( 'tab', 'settings', admin_url( 'admin.php?page=evp_settings' ) ) ),
                    )
                )
            );
        }
    );

    // Add styles on the plugin admin page.
    add_action(
        'admin_print_styles-' . $suffix,
        function() {
            wp_enqueue_style(
                'evp-admin',
                EVP_URL . 'assets/styles/admin/admin.css',
                array(),
                EVP_VERSION
            );
        }
    );
}
add_action('admin_menu', 'evp_register_admin_page');

/**
 * Ajax Callback to create a new playlist.
 *
 * @since 1.0.0
 */
function evp_add_new_playlist() {
    // Nounce Verification.
	check_ajax_referer('evp-admin-ajax-nonce', 'security');

    // Get Playlist Name.
    $label   = isset($_POST['playlist']) ? sanitize_text_field(wp_unslash($_POST['playlist'])) : '';
    $key     = strtolower(str_replace(' ', '-', $label));
    $success = false;
    $data    = false;
    $message = false;

    // Video playlist already exists.
    $object_id = evp_get_data_index( $key, 'object_id' );
    if ( $object_id ) {
        $success = false;
        $data    = $object_id;
        $message = __('Video playlist already exists.', 'evp_video_player');
    } else {
        $object_id = evp_create_bucket($key, $label);
        if ( is_wp_error( $object_id ) ) {
            $message = $object_id->get_error_message();
        } else {
            $success = true;
            $data    = $key;
        }
    }

    echo wp_json_encode(
        array(
            'success' => $success,
            'data'    => $data,
            'error'   => $message,
        )
    );
    wp_die();
}
add_action('wp_ajax_evp_add_new_playlist', 'evp_add_new_playlist');

/**
 * Ajax Callback to delete an existing playlist.
 *
 * @since 1.0.0
 */
function evp_delete_playlist() {
    // Nounce Verification.
    check_ajax_referer('evp-admin-ajax-nonce', 'security');

    // Get Playlist Name.
    $playlist = isset($_POST['playlist']) ? sanitize_text_field(wp_unslash($_POST['playlist'])) : '';
    $success = false;
    $data    = false;
    $message = false;

    $object_id = evp_get_data_index( $playlist, 'object_id' );
    if ( $object_id ) {
        $success = evp_delete_bucket($playlist);
        $data    = evp_get_playlists();
    } else {
        $message = __('Playlist does not exists.', 'evp_video_player');
    }

    echo wp_json_encode(
        array(
            'success' => $success,
            'data'    => $data,
            'error'   => $message,
        )
    );
    wp_die();
}
add_action('wp_ajax_evp_delete_playlist', 'evp_delete_playlist');

/**
 * Ajax Callback to add video to an existing playlist.
 *
 * @since 1.0.0
 */
function evp_add_new_video() {
    // Nounce Verification.
	check_ajax_referer('evp-admin-ajax-nonce', 'security');

    // Get Playlist Name.
    $playlist = isset($_POST['playlist']) ? sanitize_text_field(wp_unslash($_POST['playlist'])) : '';
    $video    = isset($_POST['videourl']) ? esc_url_raw(wp_unslash($_POST['videourl'])) : '';
    $success = false;
    $data    = false;
    $message = false;

    if ( ! $video ) {
        $message = __('Video URL is required.', 'evp_video_player');
    } else {
        $object_id = evp_get_data_index( $playlist, 'object_id' );
        if ( ! $object_id ) {
            $message = __('Video playlist does not exists.', 'evp_video_player');
        } else {
            $playlist_data = evp_get_data($playlist, false);
            $playlist_data = $playlist_data ? $playlist_data : array();
            $videos  = isset($playlist_data['videos']) ? $playlist_data['videos'] : array();
            $sources = isset($playlist_data['sources']) ? $playlist_data['sources'] : array();
            $is_video_exists = false;
            foreach ($videos as $key => $video_data) {
                if ( isset($video_data['url']) && $video_data['url'] == $video ) {
                    $is_video_exists = true;
                    break;
                }
            }
            if ( $is_video_exists ) {
                $message = __('Video already exists.', 'evp_video_player');
            } else {
                $video_data = evp_get_oembed_data($video);
                if ( $video_data ) {
                    $video_list = isset( $video_data['video_list'] ) ? $video_data['video_list'] : array();
                    $source     = isset( $video_data['source'] ) ? $video_data['source'] : array();
                    $videos  = array_merge( $videos, $video_list );
                    $sources = array_merge_recursive( $sources, array( $source ) );
                    $sources = array_map( 'array_unique', $sources );
                    $playlist_data['videos']  = $videos;
                    $playlist_data['sources'] = $sources;
                    evp_update_data($playlist, $playlist_data);
                    $success = true;
                    $data    = evp_get_playlists();
                }
            }
        }
    }

    echo wp_json_encode(
        array(
            'success' => $success,
            'data'    => $data,
            'error'   => $message,
        )
    );
    wp_die();
}
add_action('wp_ajax_evp_add_new_video', 'evp_add_new_video');

/**
 * Ajax Callback to delete video from an existing playlist.
 *
 * @since 1.0.0
 */
function evp_delete_video() {
    // Nounce Verification.
    check_ajax_referer('evp-admin-ajax-nonce', 'security');

    $playlist = isset($_POST['playlist']) ? sanitize_text_field(wp_unslash($_POST['playlist'])) : '';
    $video = isset($_POST['video']) ? esc_url_raw(wp_unslash($_POST['video'])) : '';
    $video_id = isset($_POST['video_id']) ? sanitize_text_field(wp_unslash($_POST['video_id'])) : '';

    $success = false;

    $data = evp_get_data($playlist, false);
    $videos = isset($data['videos']) ? $data['videos'] : array();
    if ( isset( $videos[ $video_id ] ) ) {
        unset($videos[ $video_id ]);
        $data['videos'] = $videos;
        evp_update_data($playlist, $data);
        $success = true;
    }

    echo wp_json_encode(
        array(
            'success' => $success,
        )
    );
    wp_die();
}
add_action('wp_ajax_evp_delete_video', 'evp_delete_video');

/**
 * Ajax Callback to edit information about an existing video.
 *
 * @since 1.0.0
 */
function evp_edit_video_info() {
    // Nounce Verification.
    check_ajax_referer('evp-admin-ajax-nonce', 'security');

    $playlist = isset($_POST['playlist']) ? sanitize_text_field(wp_unslash($_POST['playlist'])) : '';
    $video = isset($_POST['video']) ? esc_url_raw(wp_unslash($_POST['video'])) : '';
    $video_id = isset($_POST['video_id']) ? sanitize_text_field(wp_unslash($_POST['video_id'])) : '';
    $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
    $thumbnail = isset($_POST['thumb']) ? esc_url_raw(wp_unslash($_POST['thumb'])) : '';
    $author = isset($_POST['author']) ? sanitize_text_field(wp_unslash($_POST['author'])) : '';
    $author_url = isset($_POST['author_url']) ? esc_url_raw(wp_unslash($_POST['author_url'])) : '';

    $success = false;
    $data = false;

    $ndata = evp_get_data($playlist, false);
    $videos = isset($ndata['videos']) ? $ndata['videos'] : array();
    if ( isset( $videos[ $video_id ] ) ) {
        $video_data = $videos[ $video_id ];
        $thumb_url = isset($video_data['thumbnail_url']) && is_array($video_data['thumbnail_url']) ? $video_data['thumbnail_url'] : array();
        array_unshift($thumb_url, $thumbnail);
        $video_data['thumbnail_url'] = $thumb_url;
        $video_data['title'] = sanitize_text_field($title);
        $video_data['author_name'] = sanitize_text_field($author);
        $video_data['author_url'] = esc_url_raw($author_url);
        $videos[ $video_id ] = $video_data;
        $ndata['videos'] = $videos;
        evp_update_data($playlist, $ndata);
        $success = true;
        $data = evp_get_playlists();
    }

    echo wp_json_encode(
        array(
            'success' => $success,
            'data'    => $data,
        )
    );
    wp_die();
}
add_action('wp_ajax_evp_edit_video_info', 'evp_edit_video_info');

/**
 * Ajax Callback to modify playlist video sort order.
 *
 * @since 1.0.0
 */
function evp_save_playlist_sorting() {
    // Nounce Verification.
    check_ajax_referer('evp-admin-ajax-nonce', 'security');

    $playlist = isset($_POST['playlist']) ? sanitize_text_field(wp_unslash($_POST['playlist'])) : '';
    $videos   = isset($_POST['videos']) ? array_map('esc_url_raw', wp_unslash($_POST['videos'])) : '';
    $ids      = isset($_POST['ids']) ? array_map('sanitize_text_field', wp_unslash($_POST['ids'])) : '';

    $data = evp_get_data($playlist, false);
    $old_videos = isset($data['videos']) ? $data['videos'] : array();
    $new_videos = array();

    foreach ($ids as $id) {
        if ( ! isset( $old_videos[ $id ] ) ) {
            continue;
        }
        $new_videos[ $id ] = $old_videos[ $id ];
    }

    $data['videos'] = $new_videos;
    evp_update_data($playlist, $data);
    echo wp_json_encode(
        array(
            'success' => true,
        )
    );
    wp_die();
}
add_action('wp_ajax_evp_save_playlist_sorting', 'evp_save_playlist_sorting');

/**
 * Save API Key to database.
 *
 * @since 1.0.0
 */
function evp_save_api_key() {
    // Nounce Verification.
    check_ajax_referer('evp-admin-ajax-nonce', 'security');
    $key = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
    $val = isset($_POST['api_val']) ? sanitize_text_field(wp_unslash($_POST['api_val'])) : '';
    $api = get_option( 'evp_settings_api' );
    $api = $api && is_array( $api ) ? $api : array();
    $api[ $key ] = $val;
    update_option( 'evp_settings_api', $api );
    echo wp_json_encode(
        array(
            'success' => true,
        )
    );
    wp_die();
}
add_action('wp_ajax_evp_save_api_key', 'evp_save_api_key');

/**
 * Register the Video Playlist block.
 *
 * @since 1.0.0
 */
function evp_register_block() {
	register_block_type(
        'evp-block/evp-block',
        array(
            'render_callback' => 'evp_render_player',
            'attributes'      => apply_filters(
                'evp_block_attr',
                array(
                    'playlist' => array(
                        'type'    => 'string',
                        'default' => '',
                    ),
                )
            ),
        )
    );

    add_shortcode( 'evpvideoplaylist', 'evp_render_player' );
}
add_action( 'init', 'evp_register_block' );

add_action(
    'widgets_init',
    function() {
        register_widget( 'Easy_Video_Playlist\Inc\Widget' );
    }
);

/**
 * Enqueue the Video Playlist block assets.
 *
 * @since 1.0.0
 */
function evp_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'evp-block-js',
        EVP_URL . 'assets/scripts/block/block.build.js',
        array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor', 'wp-api-fetch', 'wp-block-editor', 'wp-server-side-render', 'jquery' ),
        EVP_VERSION,
        true
    );
    evp_enqueue_scripts();
}
add_action( 'enqueue_block_editor_assets', 'evp_enqueue_block_editor_assets' );

/**
 * Register the Video Playlist REST API routes for the block.
 *
 * @since 1.0.0
 */
function evp_register_routes() {
    register_rest_route(
        'evp/v1',
        '/lIndex',
        array(
            'methods'             => 'GET',
            'callback'            => function() {
                return evp_get_playlist_index();
            },
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        )
    );
}
add_action('rest_api_init', 'evp_register_routes');

/**
 * Add the Video Playlist SVG icon to the page.
 *
 * @since 1.0.0
 */
function evp_add_icons() {
    if ( file_exists( EVP_DIR . 'assets/images/plyr.svg' ) ) {
        include_once EVP_DIR . 'assets/images/plyr.svg';
    }
}
add_action('admin_footer', 'evp_add_icons', 9999);
add_action('wp_footer', 'evp_add_icons', 9999);
