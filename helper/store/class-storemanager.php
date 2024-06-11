<?php
/**
 * Base class to store playlist data.
 *
 * @since   1.1.0
 *
 * @package Easy_Video_Playlist
 */

namespace Easy_Video_Playlist\Helper\Store;

use Easy_Video_Playlist\Helper\Core\Singleton;

/**
 * Base class to store playlist data.
 *
 * @since 1.1.0
 */
class StoreManager extends Singleton {

	/**
	 * Create custom post type to store playlist data.
	 *
	 * @since 1.1.0
	 */
	public function register() {
		register_post_type(
			'evp_storage',
			array(
				'labels' => array(
					'name'          => __( 'Easy Video Playlist', 'easy-video-playlist' ),
					'singular_name' => __( 'Easy Video Playlist', 'easy-video-playlist' ),
				),
				'query_var' => false,
			)
		);
	}

	/**
	 * Get database register.
	 *
	 * @since 1.0.0
	 */
	public function get_register() {
		$register = get_option( 'evp-register' );
		return false !== $register ? $register : array();
	}

	/**
	 * Create a new storage bucket.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key     ID or key of the storage bucket.
	 * @param string $label   Label for the new bucket.
	 */
	public function create_bucket( $key, $label = '' ) {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'evp_storage',
				'post_status' => 'publish',
			)
		);
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		// Index the bucket.
		return $this->add_to_index( $key, $post_id, $label );
	}

	/**
	 * Delete a stored data bucket.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Bucket's unique ID or feed URL.
	 */
	public function delete_bucket( $key ) {
		$object_id = $this->get_data_index( $key, 'object_id' );
		if ( $object_id ) {
			wp_delete_post( $object_id, true );
			$this->delete_from_index( $key );
			return true;
		}
		return false;
	}

	/**
	 * Add a new data or update an existing data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   ID or key of the storage bucket.
	 * @param mixed  $data  Data to store.
	 */
	public function update_data( $key, $data ) {
		$object_id = $this->get_data_index( $key, 'object_id' );
		if ( $object_id ) {
			update_post_meta( $object_id, 'default_data', $data );
			return true;
		}
		return false;
	}

	/**
	 * Get a stored data from the post object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key     ID or key of the storage bucket.
	 * @param bool   $escape  Escape or not.
	 */
	public function get_data( $key, $escape = true ) {
		$object_id = $this->get_data_index( $key, 'object_id' );
		if ( $object_id ) {
			return get_post_meta( $object_id, 'default_data', true );
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
	public function add_to_index( $key, $post_id, $label ) {
		$register         = $this->get_register();
		$key              = sanitize_text_field( $key );
		$register[ $key ] = array(
			'bucket_key'   => $key,
			'bucket_title' => sanitize_text_field( $label ),
			'object_id'    => absint( $post_id ),
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
	public function delete_from_index( $key ) {
		$register = $this->get_register();
		if ( ! $key || ! isset( $register[ $key ] ) ) {
			return false;
		}
		unset( $register[ $key ] );
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
	public function get_data_index( $key = '', $field = '' ) {
		$register = $this->get_register();
		$index    = isset( $register[ $key ] ) ? $register[ $key ] : false;
		return $index && isset( $index[ $field ] ) ? $index[ $field ] : false;
	}
}
