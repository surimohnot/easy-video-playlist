<?php
/**
 * Plugin Admin Page Header Template
 *
 * @package  Easy_Video_Playlist
 * @since    1.0.0
 */

?>

<header class="evp-header">
    <hgroup class="evp-branding">
        <h1 class="evp-title"><?php esc_html_e( 'Easy Video Playlists', 'easy-video-playlist' ); ?></h1>
    </hgroup>
    <nav class="evp-nav">
        <ul>
            <?php
            foreach ( $tabs as $tab => $name ) {
                $class = $tab === $active_tab ? 'evp-nav-item evp-nav-item-active' : 'evp-nav-item';
                echo '<li class="' . esc_attr( $class ) . '"><a href="' . esc_url( add_query_arg( 'tab', $tab, $admin_url ) ) . '">' . esc_html( $name ) . '</a></li>';
            }
            ?>
        </ul>
    </nav>
</header>