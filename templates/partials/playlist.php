<?php
/**
 * Plugin Admin Page Partial
 *
 * @package  Easy_Video_Playlist
 * @since    1.0.0
 */
?>

<div class="evp-playlist-manager" id="evp-playlist-manager">
    <div class="evp-playlists-sidebar">
        <div class="evp-playlists-index" style="display: none;">
            <fieldset class="evp-playlist-index">
                <legend><?php _e( 'Your Playlists', 'easy-video-playlist' ); ?></legend>
                <ul class="evp-playlists-index-list"></ul>
            </fieldset>
        </div>
        <div class="evp-playlists-create">
            <fieldset class="evp-create-new">
                <legend><?php _e( 'Create New Playlist', 'easy-video-playlist' ); ?></legend>
                <input type="text" class="evp-playlist-name" placeholder="<?php _e( 'Enter Name of Your New Playlist', 'easy-video-playlist' ); ?>">
                <button class="evp-add-playlist-btn">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <span><?php _e( 'Add Playlist', 'easy-video-playlist' ); ?></span>
                </button>
            </fieldset>
        </div>
    </div>
    <div class="evp-playlists-main">
        <div class="evp-playlists-content" style="display: none;">
            <h2 class="evp-playlist-title">
                <span class="evp-playlist-title-text"></span>
                <button class="evp-delete-playlist">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </h2>
            <div class="evp-playlist-content">
                <ul class="evp-playlist-content-tabs">
                    <li class="evp-playlist-content-tabs-item evp-tab-active" data-attr="evp-playlist-content-video-list"><a href="#"><?php _e( 'Video List', 'easy-video-playlist' ); ?></a></li>
                    <li class="evp-playlist-content-tabs-item" data-attr="evp-playlist-content-info"><a href="#"><?php _e( 'Playlist Info', 'easy-video-playlist' ); ?></a></li>
                </ul>
                <div class="evp-playlist-content-video-list evp-playlist-tab-content">
                    <button class="evp-open-addvideo-modal">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <span><?php _e( 'Add Video to Playlist', 'easy-video-playlist' ); ?></span>
                    </button>
                    <div class="evp-playlist-video-index">
                        <ul class="evp-video-index-list"></ul>
                    </div>
                    <button class="evp-save-playlist-sorting">
                        <span class="dashicons dashicons-update"></span>
                        <span><?php _e( 'Update Order', 'easy-video-playlist' ); ?></span>
                    </button>
                </div>
                <div class="evp-playlist-content-info evp-playlist-tab-content" style="display: none;">
                    <div class="evp-playlist-shortcode-info">
                        <span class="evp-playlist-info-title"><?php _e( 'Shortcode for this playlist', 'easy-video-playlist' ); ?></span>
                        <pre class="evp-playlist-shortcode"></pre>
                    </div>
                </div>
            </div>
        </div>
        <div class="evp-video-modal" style="display: none;"></div>
        <div class="evp-playlists-no-content">
            <h2 class="evp-playlists-no-content-title"><?php _e( 'Welcome to Easy Video Playlist', 'easy-video-playlist' ); ?></h2>
            <p>Create your first video playlist from the section on the left sidebar of this page OR <a href="<?php echo( esc_url( add_query_arg( 'tab', 'support', $admin_url ) ) ); ?>">check how to get started</a>.</p>
        </div>
    </div>
</div>
