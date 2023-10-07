<?php
/**
 * Add plugin required core functionality.
 *
 * @link    https://easypodcastpro.com
 * @since   1.0.0
 *
 * @package Easy_Video_Playlist
 */

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
        $data = evp_get_data($key);
        $playlists[$key] = array(
            'title'  => $title,
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
        'vidurl'      => __( 'Enter URL of the Video', 'easy-video-playlist' ),
        'videourl'    => __( 'Video URL', 'easy-video-playlist' ),
        'title'       => __( 'Title', 'easy-video-playlist' ),
        'author'      => __( 'Author', 'easy-video-playlist' ),
        'authorurl'   => __( 'Author URL', 'easy-video-playlist' ),
        'thumbnail'   => __( 'Thumbnail Image URL', 'easy-video-playlist' ),
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
        return $data;
    }

    // Add query parameters.
    $query_params = array(
        'url'    => $url,
        'format' => 'json',
    );

    $final_url = add_query_arg($query_params, $provider_url);
    $response = wp_safe_remote_request($final_url, array('timeout' => 10));

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

	return $data ? evp_format_oembed_data($data, $url, $provider_type, $service) : false;
}

function evp_format_oembed_data($data, $url, $provider_type, $service) {
    if (! $data || ! is_array($data)) {
        return false;
    }

    $id = '';
    $thumb_url = array();
    if ('youtube' === $service) {
        // Try to fetch video ID from the youtube URL.
        $id_regex = '%(?:youtube(?:-nocookie)?.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=|shorts/|playlist?list=)|youtu.be/)([^"&?/ ]{11})%i';
        if (preg_match($id_regex, $url, $matches)) {
            $id = $matches[1];
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

    return array(
        'title'         => isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '',
        'url'           => esc_url_raw( $url ),
        'type'          => $provider_type,
        'provider'      => $service,
        'author_name'   => isset( $data['author_name'] ) ? sanitize_text_field( $data['author_name'] ) : '',
        'author_url'    => isset( $data['author_url'] ) ? esc_url_raw( $data['author_url'] ) : '',
        'thumbnail_url' => $thumb_url,
        'id'            => $id,
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
    $providers = array(
        '#https?://((m|www)\.)?youtube\.com/watch.*#i'    => array( 'https://www.youtube.com/oembed', 'video', 'youtube' ),
		'#https?://((m|www)\.)?youtube\.com/playlist.*#i' => array( 'https://www.youtube.com/oembed', 'playlist', 'youtube' ),
		'#https?://((m|www)\.)?youtube\.com/shorts/*#i'   => array( 'https://www.youtube.com/oembed', 'short', 'youtube' ),
		'#https?://((m|www)\.)?youtube\.com/live/*#i'     => array( 'https://www.youtube.com/oembed', 'live', 'youtube' ),
		'#https?://youtu\.be/.*#i'                        => array( 'https://www.youtube.com/oembed', 'video', 'youtube' ),
        '#https?://(.+\.)?vimeo\.com/.*#i'                => array( 'https://vimeo.com/api/oembed.json', 'video', 'vimeo' ),
    );

    foreach ($providers as $pattern => $provider) {
        if (preg_match($pattern, $url, $matches)) {
            return $provider;
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
    $plIndex = array('' => __('Select Playlist'));
    foreach ($playlists as $key => $value) {
        $plIndex[$key] = isset($value['title']) ? $value['title'] : '';
    }
    return array_filter($plIndex);
}
