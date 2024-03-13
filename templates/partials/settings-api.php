<?php
/**
 * Plugin Admin Page Partial
 *
 * @package  Easy_Video_Playlist
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$api_key = get_option( 'evp_settings_api' );
$api_key = $api_key && is_array( $api_key ) ? $api_key : array();
$gapi = 'youtube';
$yt_api_key = isset( $api_key[ $gapi ] ) ? $api_key[ $gapi ] : '';
?>

<div class="evp-settings-api-container">
    <div class="evp-settings-api-key evp-settings-api-key-google">
        <div class="evp-settings-api-field-wrapper">
            <p class="evp-settings-key-label"><?php esc_html_e( 'Google API Key', 'easy-video-playlist' ); ?></p>
            <div class="evp-settings-key-input">
                <div class="evp-settings-api-input-wrapper">
                    <input class="evp-settings-api-input" data-attr="<?php echo esc_attr( $gapi ); ?>" type="password" value="<?php echo esc_attr( $yt_api_key ); ?>" placeholder="Enter your Google API key"/>
                    <button class="evp-settings-toggle-visibility" type="submit"><span class="dashicons dashicons-visibility"></span><span class="dashicons dashicons-hidden"></span></button>
                </div>
                <button class="evp-settings-api-submit" type="submit"><?php $yt_api_key ? esc_html_e( 'Update', 'easy-video-playlist' ) : esc_html_e( 'Save', 'easy-video-playlist' ); ?></button>
            </div>
        </div>
        <div class="evp-settings-key-info"><span><?php esc_html_e( 'Required only if you would like to fetch video list from your channel or playlist. ', 'easy-video-playlist' ); ?><a href="#"><?php esc_html_e( 'How to get your Google API key?', 'easy-video-playlist' ); ?></a></span></div>
    </div>
</div>