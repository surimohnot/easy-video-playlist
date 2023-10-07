<?php
/**
 * The admin functionality of the plugin.
 *
 * @link    https://easypodcastpro.com
 * @since   1.0.0
 *
 * @package Easy_Video_Playlist
 */

/**
 * Add and manage post type to store podcast data.
 *
 * @since 1.0.0
 */
function evp_register_storage_posttype() {
    register_post_type(
        'evp_storage',
        array(
            'labels' => array(
                'name' => __('Easy Video Playlist', 'easy-video-playlist'),
                'singular_name' => __('Easy Video Playlist', 'easy-video-playlist'),
            ),
            'query_var' => false,
        )
    );
}
add_action('init', 'evp_register_storage_posttype');

/**
 * Create a new storage bucket.
 *
 * @since 1.0.0
 *
 * @param string $key     ID or key of the storage bucket.
 * @param string $label   Label for the new bucket.
 */
function evp_create_bucket($key, $label = '') {
    $post_id = wp_insert_post(
        array( 'post_type' => 'evp_storage', 'post_status' => 'publish' )
    );
    if ( is_wp_error( $post_id ) ) {
        return $post_id;
    }
    // Index the bucket.
    return evp_add_to_index($key, $post_id, $label);
}

/**
 * Delete a stored data bucket.
 *
 * @since 1.0.0
 *
 * @param string $key Bucket's unique ID or feed URL.
 */
function evp_delete_bucket($key) {
    $object_id = evp_get_data_index( $key, 'object_id' );
    if ( $object_id) {
        wp_delete_post( $object_id, true);
        evp_delete_from_index($key);
        return true;
    }
    return false;
}

/**
 * Index saved podcast.
 *
 * Add a new podcast to the index.
 *
 * @since 1.0.0
 *
 * @param string $key     ID or key of the storage bucket.
 * @param int    $post_id post ID for the bucket.
 * @param string $label   Label for the data.
 */
function evp_add_to_index( $key, $post_id, $label ) {
    $register = evp_get_register();
    $register[ $key ] = array(
        'bucket_key'   => $key,
        'bucket_title' => $label,
        'object_id'    => $post_id
    );
    update_option( 'evp-register', $register, false );
    return true;
}

/**
 * Remove a data bucket from the Index.
 *
 * @since 1.0.0
 *
 * @param string $key Bucket Identification Key.
 */
function evp_delete_from_index($key) {
    $register = evp_get_register();
    if (! $key || ! isset( $register[$key])) {
        return false;
    }
    unset($register[$key]);
    return update_option( 'evp-register', $register, false );
}

/**
 * Get stored data bucket index object.
 *
 * @since 1.0.0
 *
 * @param string $key   Data unique ID or feed URL.
 * @param mixed  $field Field(s) to return.
 */
function evp_get_data_index($key = '', $field = '') {
    $register = evp_get_register();
    $index = isset($register[$key]) ? $register[$key] : false;
    return $index && isset( $index[$field] ) ? $index[$field] : false;
}

/**
 * Get a stored data from the post object.
 *
 * @since 1.0.0
 *
 * @param string $key  ID or key of the storage bucket.
 * @param string $name Data to retrieve from the bucket.
 */
function evp_get_data( $key, $name = 'default_data' ) {
    $object_id = evp_get_data_index( $key, 'object_id' );
    if ( $object_id ) {
        return get_post_meta( $object_id, $name, true );
    }
    return false;
}

/**
 * Add a new data or update an existing data.
 *
 * @since 1.0.0
 *
 * @param string  $key   ID or key of the storage bucket.
 * @param mixed   $data  Data to store.
 */
function evp_update_data($key, $data) {
    $object_id = evp_get_data_index($key, 'object_id');
    if ($object_id) {
        update_post_meta($object_id, 'default_data', $data);
        return true;
    }
    return false;
}

/**
 * Get database register.
 *
 * @since 1.0.0
 */
function evp_get_register() {
    $register = get_option( 'evp-register' );
    return false !== $register ? $register : array();
}

