<?php
/**
 * Easy Video Playlist Utility Functions.
 *
 * @since   1.1.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Functions;

/**
 * Easy Video Playlist Utility Functions.
 *
 * @since 1.1.0
 */
class Utility {
    public static function format_oembed_data( $data, $url, $provider_type, $service, $id ) {
        if ( ! $data || ! is_array( $data ) ) {
            return false;
        }
    
        $thumb_url  = array();
        $channel_id = '';
        if ( 'youtube' === $service ) {
            // Try to fetch video ID from the youtube URL.
            if ( ! $id ) {
                $id_regex = '%(?:youtube(?:-nocookie)?.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=|shorts/|playlist?list=)|youtu.be/)([^"&?/ ]{11})%i';
                if ( preg_match( $id_regex, $url, $matches ) ) {
                    $id = $matches[1];
                }
            }
    
            // Get youtube image URL.
            if ( $id ) {
                $img_url = sprintf( 'https://img.youtube.com/vi/%s/%s', $id, 'mqdefault.jpg' );
                if ( Validation::is_image_exists( $img_url ) ) {
                    $thumb_url[] = esc_url_raw( $img_url );
                }
            }
            if ( isset( $data['thumbnail_url'] ) ) {
                $thumb_url[] = esc_url_raw( $data['thumbnail_url'] );
            }
    
        } else if ( 'vimeo' === $service ) {
            $id = isset( $data['video_id'] ) ? sanitize_text_field( $data['video_id'] ) : '';
            if ( isset( $data['thumbnail_url'] ) ) {
                $thumb_url[] = esc_url_raw( $data['thumbnail_url'] );
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
}
