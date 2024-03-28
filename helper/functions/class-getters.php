<?php
/**
 * Base class to get various playlist information.
 *
 * @since   1.1.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Functions;

use Easy_Video_Playlist\Helper\Store\StoreManager;

/**
 * Base class to get various playlist information.
 *
 * @since 1.1.0
 */
class Getters {

    /**
     * Get all playlists from the storage.
     *
     * @since 1.0.0
     */
    public static function get_playlists() {
        $store_manager = StoreManager::get_instance();
        $playlists = array();
        $pl_index = $store_manager->get_register();
        foreach ($pl_index as $key => $value) {
            $title = $value['bucket_title'];
            $key = sanitize_text_field( $key );
            $data = $store_manager->get_data( $key );
            $playlists[$key] = array(
                'title'  => esc_html( $title ),
                'videos' => isset($data['videos']) && $data['videos'] ? $data['videos'] : array(),
            );
        }
        return $playlists;
    }

    /**
     * Get important data from the video URL.
     *
     * @since 1.0.0
     */
    public static function get_oembed_data( $url ) {
        $provider = self::get_oembed_providers( $url );
        if ( ! $provider ) {
            return false;
        }

        $provider_url  = $provider[0];
        $provider_type = $provider[1];
        $service       = $provider[2];
        $id            = isset( $provider[3] ) ? $provider[3] : '';

        // If the URL is a video URL, return the video data.
        if ( 'url' === $service ) {
            $data = array(
                'title'         => 'Local Untitled Video',
                'url'           => esc_url_raw( $url ),
                'thumbnail_url' => array(),
                'id'            => md5( $url ),
                'provider'      => $service,
                'type'          => $provider_type,
            );
            $user = wp_get_current_user();
            if ( $user ) {
                $data['author_name'] = $user->display_name;
                $data['author_url']  = $user->user_url;
            }
            return array(
                'video_list' => $data,
                'source'     => array( 'url' => $id ),
            );
        }

        // If API keys are available, let's use them to get the video data.
        $api = get_option( 'evp_settings_api' );
        $api = $api && is_array( $api ) ? $api : array();
        if ( isset( $api[ $service ] ) && ! empty( $api[ $service ] ) ) {
            $api_key = $api[ $service ];
            $items = array();
            if ( 'youtube' === $service ) {
                $api_url = "https://www.googleapis.com/youtube/v3/";
                if ( 'video' === $provider_type ) {
                    $api_url .= "videos?part=snippet&key=$api_key&id=$id";
                } else if ( 'playlist' === $provider_type ) {
                    $api_url .= "playlistItems?part=snippet&maxResults=40&key=$api_key&playlistId=$id";
                } else if ( 'channel' === $provider_type ) {
                    $api_url .= "search?part=id&maxResults=40&key=$api_key&type=video&channelId=$id";
                } else if ( 'channelUser' === $provider_type ) {
                    $get_id_url = "https://www.googleapis.com/youtube/v3/channels?part=id&key=$api_key&forHandle=$id";
                    $dataid = self::get_remote_data($get_id_url);
                    if ( ! $dataid || ! is_array( $dataid ) || ! isset( $dataid['items'] ) || ! isset( $dataid['items'][0]['id'] ) ) {
                        return false;
                    }
                    $id = $dataid['items'][0]['id'];
                    $api_url .= "search?part=id&maxResults=40&key=$api_key&type=video&channelId=$id";
                }
                $items = self::get_youtube_video_items( $api_url, $provider_type, $api_key, $id );
            }
            if ( ! $items || empty( $items ) ) {
                return false;
            }

            return array(
                'video_list' => $items,
                'source'     => array( $provider_type => $id ),
            );
        }

        // If API key is not available. We can only process video links.
        if ( 'video' !== $provider_type ) {
            return false;
        }

        // Add query parameters.
        $query_params = array(
            'url'    => $url,
            'format' => 'json',
        );

        $final_url = add_query_arg($query_params, $provider_url);
        $data = self::get_remote_data($final_url);
        $data = $data ? Utility::format_oembed_data($data, $url, $provider_type, $service, $id) : false;

        if ( ! $data ) {
            return false;
        }

        return array(
            'video_list' => $data,
            'source'     => array( $provider_type => $id ),
        );
    }

    public static function get_youtube_video_items( $url, $provider_type, $api_key, $id, $max = 500 ) {
        $items    = array();
        $vidsInfo = array();
        $data = self::get_remote_data( $url );
        if ( ! $data || ! is_array( $data ) || ! isset( $data['pageInfo'] ) || ! isset( $data['items'] ) ) {
            return false;
        }
        $itemsinfo = self::get_yt_video_information( $data['items'], $provider_type, $api_key );
        $items = array_merge( $items, $itemsinfo );
        $next_page_token = isset( $data['nextPageToken'] ) && $data['nextPageToken'] ? $data['nextPageToken'] : false;
        while ( count( $items ) < $max && $next_page_token ) {
            $url  .= '&pageToken=' . $next_page_token;
            $data = self::get_remote_data( $url );
            if ( ! $data || ! is_array( $data ) || ! isset( $data['pageInfo'] ) || ! isset( $data['items'] ) ) {
                break;
            }
            $itemsinfo = self::get_yt_video_information( $data['items'], $provider_type, $api_key );
            $items = array_merge( $items, $itemsinfo );
            $next_page_token = isset( $data['nextPageToken'] ) && $data['nextPageToken'] ? $data['nextPageToken'] : false;
        }
        foreach ( $items as $item ) {
            $item_id = isset( $item['id'] ) ? sanitize_text_field( $item['id'] ) : false;
            if ( ! $item_id ) {
                continue;
            }
            $vidsInfo[ $item_id ] = array(
                'title'         => isset( $item['snippet']['title'] ) ? sanitize_text_field( $item['snippet']['title'] ) : '',
                'date'          => isset( $item['snippet']['publishedAt'] ) ? sanitize_text_field( $item['snippet']['publishedAt'] ) : '',
                'thumbnail_url' => isset( $item['snippet']['thumbnails']['high']['url'] ) ? array( esc_url_raw( $item['snippet']['thumbnails']['high']['url'] ) ) : array(),
                'tags'          => isset( $item['snippet']['tags'] ) ? sanitize_text_field( $item['snippet']['tags'] ) : '',
                'author_name'   => isset( $item['snippet']['channelTitle'] ) ? sanitize_text_field( $item['snippet']['channelTitle'] ) : '',
                'author_url'    => isset( $item['snippet']['channelId'] ) ? 'https://www.youtube.com/channel/' . sanitize_text_field( $item['snippet']['channelId'] ) : '',
                'author_id'     => isset( $item['snippet']['channelId'] ) ? sanitize_text_field( $item['snippet']['channelId'] ) : '',
                'duration'      => isset( $item['contentDetails']['duration'] ) ? sanitize_text_field( $item['contentDetails']['duration'] ) : '',
                'uploadStatus'  => isset( $item['status']['uploadStatus'] ) ? sanitize_text_field( $item['status']['uploadStatus'] ) : '',
                'privacyStatus' => isset( $item['status']['privacyStatus'] ) ? sanitize_text_field( $item['status']['privacyStatus'] ) : '',
                'viewCount'     => isset( $item['statistics']['viewCount'] ) ? absint( $item['statistics']['viewCount'] ) : '',
                'likeCount'     => isset( $item['statistics']['likeCount'] ) ? absint( $item['statistics']['likeCount'] ) : '',
                'commentCount'  => isset( $item['statistics']['commentCount'] ) ? absint( $item['statistics']['commentCount'] ) : '',
                'url'           => isset( $item['id'] ) ? 'https://www.youtube.com/watch?v=' . sanitize_text_field( $item['id'] ) : '',
                'provider'      => 'youtube',
                'type'          => sanitize_text_field( $provider_type ),
                'source'        => sanitize_text_field( $id ),
                'id'            => isset( $item['id'] ) ? sanitize_text_field( $item['id'] ) : '',
            );
        }
        return $vidsInfo;
    }

    public static function get_yt_video_information( $items, $provider_type, $api_key ) {
        $id_string = '';
        foreach ( $items as $item ) {
            if ( strpos( $provider_type, 'channel' ) !== false ) {
                $vid_id = isset( $item['id']['videoId'] ) ? $item['id']['videoId'] : '';
            } else if ( strpos( $provider_type, 'video' ) !== false ) {
                $vid_id = isset( $item['id'] ) ? $item['id'] : '';
            } else {
                $snippet = $item['snippet'] ? $item['snippet'] : array();
                $vid_id  = isset( $snippet['resourceId']['videoId'] ) ? $snippet['resourceId']['videoId'] : '';
            }
            if ( ! $vid_id ) {
                continue;
            }
            $id_string .= $vid_id . ',';
        }
        if ( ! $id_string ) {
            return array();
        }
        $video_api_url = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,status,statistics&key=$api_key&id=$id_string";
        $video_data = self::get_remote_data($video_api_url);
        return isset( $video_data['items'] ) ? $video_data['items'] : array();
    }

    public static function get_remote_data( $url ) {
        $response = wp_safe_remote_request( $url, array( 'timeout' => 10 ) );
        if ( 501 === wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }
        $response_body = wp_remote_retrieve_body( $response );
        if ( ! $response_body ) {
            return false;
        }
        $data = json_decode( trim( $response_body ), true );
        if (! $data) {
            return false;
        }
        return $data;
    }

    /**
     * Get proper oEmbed providers.
     *
     * @since 1.0.0
     */
    public static function get_oembed_providers($url) {

        // Check if the URL provider is youtube.
        $patterns = array(
            '/youtube\.com\/(?:watch\?v=|embed\/|v\/)([\w-]+)(?:$|&(?!list=))/i' => 'video', // Single video URL without list attribute
            '/youtu\.be\/([\w-]+)/i' => 'video',
            '/youtube\.com\/playlist\?list=([\w-]+)/i' => 'playlist', // Playlist URL format 1
            '/youtube\.com\/watch\?v=[\w-]+&list=([\w-]+)/i' => 'playlist', // Playlist URL format 2
            '/youtube\.com\/(?:channel|c)\/([\w-]+)/i' => 'channel', // Channel URL
            '/youtube\.com\/user\/([\w-]+)/i' => 'user', // User page URL
            '/youtube\.com\/@([\w-]+)/i' => 'channelUser' // Channel page URL with '@'
        );
        foreach ($patterns as $pattern => $type) {
            if (preg_match($pattern, $url, $matches)) {
                $id = isset($matches[1]) ? $matches[1] : '';
                return array('https://www.youtube.com/oembed', $type, 'youtube', $id);
            }
        }

        $patterns = array(
            '/vimeo\.com\/(\d+)/i' => 'video', // Vimeo video URL
            '/vimeo\.com\/channels\/([\w-]+)/i' => 'channel', // Vimeo channel URL
            '/vimeo\.com\/album\/(\d+)/i' => 'album', // Vimeo album URL
            '/vimeo\.com\/showcase\/(\d+)/i' => 'showcase', // Vimeo showcase URL
            '/vimeo\.com\/user\/([\w-]+)/i' => 'user', // Vimeo user URL
            '/vimeo\.com\/groups\/([\w-]+)/i' => 'group', // Vimeo group URL
        );
        foreach ($patterns as $pattern => $type) {
            if (preg_match($pattern, $url, $matches)) {
                $id = isset($matches[1]) ? $matches[1] : '';
                return array('https://vimeo.com/api/oembed.json', $type, 'vimeo', $id);
            }
        }

        $is_video_url = Validation::is_video_url($url);
        if ($is_video_url) {
            return array( $url, $is_video_url, 'url' );
        }
        return false;
    }

    public static function get_playlist_index() {
        $playlists = self::get_playlists();
        $plIndex = array( '' => __( 'Select Playlist' ) );
        foreach ($playlists as $key => $value) {
            $key = esc_html( $key );
            $plIndex[ $key ] = isset( $value['title'] ) ? esc_html( $value['title'] ) : '';
        }
        return array_filter($plIndex);
    }
}
