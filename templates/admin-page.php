<?php
/**
 * Plugin Admin Page
 *
 * @package  Easy_Video_Playlist
 * @since    1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$tabs = array(
    'playlist' => __( 'Playlist Manager', 'easy-video-playlist' ),
    'support' => __( 'Help & Support', 'easy-video-playlist' ),
);
$admin_url = admin_url( 'admin.php?page=evp_settings' );
$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $tabs ) ? sanitize_text_field( $_GET['tab'] ) : 'playlist';
?>

<article class="easy-video-playlist" id="easy-video-playlist">
    <?php include_once evp_get_partial('header'); ?>
    <div class="evp-content" id="evp-content">
        <main class="evp-primary">
            <?php include_once evp_get_partial( $active_tab ); ?>
        </main>
    </div>
</article>
