<?php
/**
 * Plugin Admin Page
 *
 * @package  Easy_Video_Playlist
 * @since    1.0.0
 */

use Easy_Video_Playlist\Helper\Functions\Markup;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$tabs = array(
    'playlist' => __( 'Playlist Manager', 'easy-video-playlist' ),
    'settings' => __( 'Settings', 'easy-video-playlist' ),
    'support'  => __( 'Help & Support', 'easy-video-playlist' ),
);
$admin_url = admin_url( 'admin.php?page=evp_settings' );
$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $tabs ) ? sanitize_text_field( $_GET['tab'] ) : 'playlist';
?>

<article class="easy-video-playlist" id="easy-video-playlist">
    <?php include_once Markup::get_admin_partial('header'); ?>
    <div class="evp-content" id="evp-content">
        <main class="evp-primary">
            <?php include_once Markup::get_admin_partial( $active_tab ); ?>
        </main>
    </div>
    <div class="evp-action-feedback" id="evp-action-feedback">
		<span class="dashicons dashicons-update"></span>
		<span class="dashicons dashicons-no"></span>
		<span class="dashicons dashicons-yes"></span>
		<span class="evp-feedback"></span>
		<span class="evp-error-close"><span class="dashicons dashicons-no"></span></span>
	</div>
</article>
