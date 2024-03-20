<?php
/**
 * Add plugin required core functionality.
 *
 * @link    https://easypodcastpro.com
 * @since   1.0.0
 *
 * @package Easy_Video_Playlist
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Get all playlists from the storage.
 *
 * @since 1.0.0
 */
function evp_get_playlists() {
    $playlists = array();
    $pl_index = evp_get_register();
    foreach ($pl_index as $key => $value) {
        $title = $value['bucket_title'];
        $key = sanitize_text_field( $key );
        $data = evp_get_data( $key );
        $playlists[$key] = array(
            'title'  => esc_html( $title ),
            'videos' => isset($data['videos']) && $data['videos'] ? $data['videos'] : array(),
        );
    }
    return $playlists;
}

/**
 * Admin i18n strings for JS rendering.
 *
 * @since 1.0.0
 */
function evp_get_admin_i18n() {
    return array(
        'vidurl'      => __( 'Enter URL of the Video, Playlist or Channel', 'easy-video-playlist' ),
        'videourl'    => __( 'Video URL', 'easy-video-playlist' ),
        'title'       => __( 'Title', 'easy-video-playlist' ),
        'author'      => __( 'Channel', 'easy-video-playlist' ),
        'authorurl'   => __( 'Channel URL', 'easy-video-playlist' ),
        'thumbnail'   => __( 'Thumbnail Image URL', 'easy-video-playlist' ),
        'exshorts'    => __( 'Exclude Shorts (Videos shorter than 60 Seconds)', 'easy-video-playlist' ),
        'addvid'      => __( 'Add Video', 'easy-video-playlist' ),
        'update'      => __( 'Update', 'easy-video-playlist' ),
        'cancel'      => __( 'Cancel', 'easy-video-playlist' ),
        'editvidinfo' => __( 'Edit Video Information', 'easy-video-playlist' ),
        'createfirst' => __( 'Create Your First Playlist', 'easy-video-playlist' ),
        'createnew'   => __( 'Create New Playlist', 'easy-video-playlist' ),
    );
}

/**
 * Get important data from the video URL.
 *
 * @since 1.0.0
 */
function evp_get_oembed_data($url) {
    $provider = evp_get_oembed_providers($url);
    if (! $provider) {
        return false;
    }

    $provider_url = $provider[0];
    $provider_type = $provider[1];
    $service = $provider[2];
    $id = isset( $provider[3] ) ? $provider[3] : '';

    // If the URL is a video URL, return the video data.
    if ('url' === $service) {
        $data = array(
            'title' => 'Local Untitled Video',
            'url' => esc_url_raw($url),
            'thumbnail_url' => array(),
            'id' => md5($url),
            'provider' => $service,
            'type' => $provider_type,
        );
        $user = wp_get_current_user();
        if ($user) {
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
                $dataid = evp_get_remote_data($get_id_url);
                if ( ! $dataid || ! is_array( $dataid ) || ! isset( $dataid['items'] ) || ! isset( $dataid['items'][0]['id'] ) ) {
                    return false;
                }
                $id = $dataid['items'][0]['id'];
                $api_url .= "search?part=id&maxResults=40&key=$api_key&type=video&channelId=$id";
            }
            $items = evp_get_youtube_video_items( $api_url, $provider_type, $api_key, $id );
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
    $data = evp_get_remote_data($final_url);
    $data = $data ? evp_format_oembed_data($data, $url, $provider_type, $service, $id) : false;

    if ( ! $data ) {
        return false;
    }

	return array(
        'video_list' => $data,
        'source'     => array( $provider_type => $id ),
    );
}

function evp_get_youtube_video_items( $url, $provider_type, $api_key, $id, $max = 500 ) {
    $items    = array();
    $vidsInfo = array();
    $data = evp_get_remote_data( $url );
    if ( ! $data || ! is_array( $data ) || ! isset( $data['pageInfo'] ) || ! isset( $data['items'] ) ) {
        return false;
    }
    $itemsinfo = evp_get_yt_video_information( $data['items'], $provider_type, $api_key );
    $items = array_merge( $items, $itemsinfo );
    $next_page_token = isset( $data['nextPageToken'] ) && $data['nextPageToken'] ? $data['nextPageToken'] : false;
    while ( count( $items ) < $max && $next_page_token ) {
        $url  .= '&pageToken=' . $next_page_token;
        $data = evp_get_remote_data( $url );
        if ( ! $data || ! is_array( $data ) || ! isset( $data['pageInfo'] ) || ! isset( $data['items'] ) ) {
            break;
        }
        $itemsinfo = evp_get_yt_video_information( $data['items'], $provider_type, $api_key );
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

function evp_get_yt_video_information( $items, $provider_type, $api_key ) {
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
    $video_data = evp_get_remote_data($video_api_url);
    return isset( $video_data['items'] ) ? $video_data['items'] : array();
}

function evp_get_remote_data($url) {
    $response = wp_safe_remote_request($url, array('timeout' => 10));
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

function evp_format_oembed_data($data, $url, $provider_type, $service, $id) {
    if (! $data || ! is_array($data)) {
        return false;
    }

    $thumb_url  = array();
    $channel_id = '';
    if ('youtube' === $service) {
        // Try to fetch video ID from the youtube URL.
        if ( ! $id ) {
            $id_regex = '%(?:youtube(?:-nocookie)?.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=|shorts/|playlist?list=)|youtu.be/)([^"&?/ ]{11})%i';
            if (preg_match($id_regex, $url, $matches)) {
                $id = $matches[1];
            }
        }

        // Get youtube image URL.
        if ($id) {
            $img_url = sprintf('https://img.youtube.com/vi/%s/%s', $id, 'mqdefault.jpg');
            if (evp_is_image_exists($img_url)) {
                $thumb_url[] = esc_url_raw($img_url);
            }
        }
        if (isset( $data['thumbnail_url'] )) {
            $thumb_url[] = esc_url_raw($data['thumbnail_url']);
        }

    } else if ('vimeo' === $service) {
        $id = isset( $data['video_id'] ) ? sanitize_text_field( $data['video_id'] ) : '';
        if (isset( $data['thumbnail_url'] )) {
            $thumb_url[] = esc_url_raw($data['thumbnail_url']);
        }
    }

    $id = sanitize_text_field( $id );
    return array(
        $id => array(
            'title'         => isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '',
            'date'          => isset( $data['upload_date'] ) ? esc_url_raw( $data['upload_date'] ) : '',
            'thumbnail_url' => $thumb_url,
            'url'           => esc_url_raw( $url ),
            'type'          => $provider_type,
            'provider'      => $service,
            'author_name'  => isset( $data['author_name'] ) ? sanitize_text_field( $data['author_name'] ) : '',
            'author_url'   => isset( $data['author_url'] ) ? esc_url_raw( $data['author_url'] ) : '',
            'id'            => $id,
            'source'        => esc_url_raw( $url ),
            'duration'      => isset( $data['duration'] ) ? absint( $data['duration'] ) : '',
        )
    );
}

function evp_is_image_exists($image_url) {
    // Check if the URL is empty or not a valid URL
    if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
        return false;
    }

    // Send a HEAD request to the image URL using wp_remote_head
    $response = wp_remote_head($image_url);

    // Check if the request was successful
    if (is_wp_error($response)) {
        return false; // Error occurred
    }

    // Get the HTTP response code from the response
    $response_code = wp_remote_retrieve_response_code($response);

    // Check if the HTTP response code is in the 200-299 range (image exists)
    if ($response_code >= 200 && $response_code < 300) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get proper oEmbed providers.
 *
 * @since 1.0.0
 */
function evp_get_oembed_providers($url) {

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

    $is_video_url = evp_is_video_url($url);
    if ($is_video_url) {
        return array( $url, $is_video_url, 'url' );
    }
    return false;
}

/**
 * Check if the URL is a video.
 *
 * @since 1.0.0
 */
function evp_is_video_url($url) {
    $video_ext  = wp_get_video_extensions();
	$mime_types = wp_get_mime_types();
	$media_url   = $url ? preg_replace( '/\?.*/', '', $url ) : false;
	if ( $media_url ) {
		$type = wp_check_filetype( $media_url, $mime_types );
		if ( in_array( strtolower( $type['ext'] ), $video_ext, true ) ) {
			return strtolower( $type['ext'] );
		}
	}
	return false;
}

function evp_get_partial($partial) {
    $path = EVP_DIR . 'templates/partials/' . $partial . '.php';
    return apply_filters('evp_get_partial', $path);
}

function evp_object_cache() {
    return Easy_Video_Playlist\Lib\Object_Cache::get_instance();
}

function evp_get_playlist_index() {
    $playlists = evp_get_playlists();
    $plIndex = array( '' => __( 'Select Playlist' ) );
    foreach ($playlists as $key => $value) {
        $key = esc_html( $key );
        $plIndex[ $key ] = isset( $value['title'] ) ? esc_html( $value['title'] ) : '';
    }
    return array_filter($plIndex);
}
