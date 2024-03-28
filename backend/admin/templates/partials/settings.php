<?php
/**
 * Plugin Admin Page Partial
 *
 * @package  Easy_Video_Playlist
 * @since    1.0.0
 */

use Easy_Video_Playlist\Helper\Functions\Markup;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="evp-settings" id="evp-settings">
    <h2 class="evp-playlists-no-content-title"><?php esc_html_e( 'Settings', 'easy-video-playlist' ); ?></h2>
    <div class="evp-settings-wrapper">
        <div class="evp-settings-sidebar">
            <ul class="evp-settings-list">
                <li class="evp-settings-item evp-settings-item-active" data-content="evp-settings-api">
                    <?php esc_html_e( 'API & Keys', 'easy-video-playlist' ); ?>
                </li>
            </ul>
        </div>
        <div class="evp-settings-content">
            <div id="evp-settings-api" class="evp-settings-content-item evp-settings-content-item-active">
                <?php include Markup::get_partial('settings-api'); ?>
            </div>
        </div>
    </div>
</div>