<?php
/**
 * Handle admin specific functionality of the plugin.
 *
 * @package Easy_Video_Playlist
 * @link    https://easypodcastpro.com/
 * @since   1.1.0
 */

namespace Easy_Video_Playlist\Backend\Inc;

use Easy_Video_Playlist\Helper\Core\Singleton;
use Easy_Video_Playlist\Helper\Functions\Getters;
use Easy_Video_Playlist\Helper\Store\StoreManager;
use Easy_Video_Playlist\Helper\Playlist\Get_Playlist;
use Easy_Video_Playlist\Helper\Store\PlaylistData;
use Easy_Video_Playlist\Helper\Store\VideoData;
use Easy_Video_Playlist\Helper\Store\SourceData;
use Easy_Video_Playlist\Helper\Playlist\Fetch_YouTube;
use Easy_Video_Playlist\Helper\Playlist\Fetch_Vimeo;

/**
 * Handle admin specific functionality of the plugin.
 *
 * Admin specific methods.
 *
 * @package  Easy_Video_Playlist
 * @since    1.1.0
 */
class Core extends Singleton {
	/**
	 * Ajax Callback to create a new playlist.
	 *
	 * @since 1.0.0
	 */
	public function add_new_playlist() {
		// Nounce Verification.
		check_ajax_referer( 'evp-admin-ajax-nonce', 'security' );

		// Get Playlist Name.
		$label   = isset( $_POST['playlist'] ) ? sanitize_text_field( wp_unslash( $_POST['playlist'] ) ) : '';
		$key     = strtolower( str_replace( ' ', '-', $label ) );
		$success = false;
		$data    = false;
		$message = false;

		// Video playlist already exists.
		$store_manager = StoreManager::get_instance();
		$object_id     = $store_manager->get_data_index( $key, 'object_id' );
		if ( $object_id ) {
			$success = false;
			$data    = $object_id;
			$message = __( 'Video playlist already exists.', 'evp_video_player' );
		} else {
			$object_id = $store_manager->create_bucket( $key, $label );
			if ( is_wp_error( $object_id ) ) {
				$message = $object_id->get_error_message();
			} else {
				$success = true;
				$data    = $key;
			}
		}

		echo wp_json_encode(
			array(
				'success' => $success,
				'data'    => $data,
				'error'   => $message,
			)
		);
		wp_die();
	}

	/**
	 * Ajax Callback to delete an existing playlist.
	 *
	 * @since 1.0.0
	 */
	public function delete_playlist() {
		// Nounce Verification.
		check_ajax_referer( 'evp-admin-ajax-nonce', 'security' );

		// Get Playlist Name.
		$playlist = isset( $_POST['playlist'] ) ? sanitize_text_field( wp_unslash( $_POST['playlist'] ) ) : '';
		$success  = false;
		$data     = false;
		$message  = false;

		$store_manager = StoreManager::get_instance();
		$object_id     = $store_manager->get_data_index( $playlist, 'object_id' );
		if ( $object_id ) {
			$success = $store_manager->delete_bucket( $playlist );
			$data    = Getters::get_playlists();
		} else {
			$message = __( 'Playlist does not exists.', 'evp_video_player' );
		}

		echo wp_json_encode(
			array(
				'success' => $success,
				'data'    => $data,
				'error'   => $message,
			)
		);
		wp_die();
	}

	/**
	 * Ajax Callback to add video to an existing playlist.
	 *
	 * @since 1.0.0
	 */
	public function add_new_video() {
		// Nounce Verification.
		check_ajax_referer( 'evp-admin-ajax-nonce', 'security' );

		// Get Playlist Name.
		$playlist    = isset( $_POST['playlist'] ) ? sanitize_text_field( wp_unslash( $_POST['playlist'] ) ) : '';
		$url         = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		$source_type = isset( $_POST['sourcetype'] ) ? sanitize_text_field( wp_unslash( $_POST['sourcetype'] ) ) : '';
		$source_id   = isset( $_POST['sourceid'] ) ? sanitize_text_field( wp_unslash( $_POST['sourceid'] ) ) : '';
		$provider    = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
		$success     = false;
		$data        = false;
		$message     = false;

		$store_manager = StoreManager::get_instance();
		if ( ! $url ) {
			$message = __( 'Video URL is required.', 'evp_video_player' );
		} else {
			$object_id = $store_manager->get_data_index( $playlist, 'object_id' );
			if ( ! $object_id ) {
				$message = __( 'Video playlist does not exists.', 'evp_video_player' );
			} else {
				$playlist_data = $store_manager->get_data( $playlist ); // TODO: Here we will use the get_playlist class.
				$playlist_data = $playlist_data ? $playlist_data : array();
				$videos  = isset( $playlist_data['videos'] ) ? $playlist_data['videos'] : array();
				$sources = isset( $playlist_data['sources'] ) ? $playlist_data['sources'] : array();
				$is_video_exists = false;
				foreach ( $videos as $key => $video_data ) {
					if ( isset( $video_data['url'] ) && $video_data['url'] == $url ) {
						$is_video_exists = true;
						break;
					}
				}
				if ( $is_video_exists ) {
					$message = __('Video already exists.', 'evp_video_player');
				} else {
					$video_data = Getters::get_oembed_data( $url );
					if ( $video_data ) {
						$video_list = isset( $video_data['video_list'] ) ? $video_data['video_list'] : array();
						$source     = isset( $video_data['source'] ) ? $video_data['source'] : array();
						$videos  = array_merge( $videos, $video_list );
						$sources = array_merge_recursive( $sources, array( $source ) );
						$sources = array_map( 'array_unique', $sources );
						$playlist_data['videos']  = $videos;
						$playlist_data['sources'] = $sources;
						$store_manager->update_data( $playlist, $playlist_data );
						$success = true;
						$data    = Getters::get_playlists();
					}
				}
			}
		}

		echo wp_json_encode(
			array(
				'success' => $success,
				'data'    => $data,
				'error'   => $message,
			)
		);
		wp_die();
	}

	/**
	 * Ajax Callback to add video to an existing playlist.
	 *
	 * @since 1.0.0
	 */
	public function add_new_video_new() {
		// Nounce Verification.
		check_ajax_referer( 'evp-admin-ajax-nonce', 'security' );

		// Get Playlist Name.
		$playlist    = isset( $_POST['playlist'] ) ? sanitize_text_field( wp_unslash( $_POST['playlist'] ) ) : '';
		$url         = isset( $_POST['url'] ) ? untrailingslashit( esc_url_raw( wp_unslash( $_POST['url'] ) ) ) : '';
		$source_type = isset( $_POST['sourcetype'] ) ? sanitize_text_field( wp_unslash( $_POST['sourcetype'] ) ) : '';
		$source_id   = isset( $_POST['sourceid'] ) ? sanitize_text_field( wp_unslash( $_POST['sourceid'] ) ) : '';
		$provider    = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
		$success     = false;
		$data        = false;
		$message     = false;

		if ( 'url' === $provider ) {
			$source_id = md5( $url );
		}

		$store_manager = StoreManager::get_instance();
		if ( ! $url ) {
			$message = __( 'Video URL is required.', 'evp_video_player' );
		} else {
			$obj    = new Get_Playlist( $playlist );
			$p_data = $obj->init();
			if ( ! $p_data || ! $p_data instanceof PlaylistData ) {
				$message = __( 'Incorrect Playlist data available.', 'evp_video_player' );
				// TODO: Probably delete playlist data object.
			} else {
				$sources    = $p_data->get( 'sources' );
				$videos     = $p_data->get( 'videos' );
				$video_arr  = array();
				$source_arr = array();

				foreach ( $sources as $source ) {
					if ( ! $source || ! $source instanceof SourceData ) {
						continue;
					}

					$id                = $source->get( 'id' );
					$source_arr[ $id ] = $source;
				}
				foreach ( $videos as $video ) {
					if ( ! $video || ! $video instanceof VideoData ) {
						continue;
					}
					$id               = $video->get( 'id' );
					$video_arr[ $id ] = $video;
				}
				$video_data = array();
				if ( 'youtube' === $provider ) {
					$yt_obj     = Fetch_YouTube::get_instance();
					$video_data = $yt_obj->get_data( $source_id, $source_type );
				} elseif ( 'vimeo' === $provider ) {
					$vm_obj     = Fetch_Vimeo::get_instance();
					$video_data = $vm_obj->get_data( $source_id, $source_type );
				} elseif ( 'url' === $provider ) {
					$url_obj    = Fetch_Url::get_instance();
					$video_data = $url_obj->get_data( $url );
				}
				if ( ! empty( $video_data ) && is_array( $video_data ) ) { 
					foreach ( $video_data as $vdata ) {
						if ( ! $vdata || ! $vdata instanceof VideoData ) {
							continue;
						}
						$id = $vdata->get( 'id' );
						$video_arr[ $id ] = $vdata;
					}

					if ( 'video' !== $source_type ) {
						$new_source = new SourceData();
						$new_source->set( 'prvider', $provider );
						$new_source->set( 'id', $source_id );
						$new_source->set( 'type', $source_type );
						$source_arr[ $source_id ] = $new_source;
					}

					$p_data->set( 'sources', array_values( $sources ) );
					$p_data->set( 'videos', array_values( $video_arr ) );
					$store_manager->update_data( $playlist, $p_data );
					$success = true;
					$data    = Getters::get_playlists();
				}
			}
		}

		echo wp_json_encode(
			array(
				'success' => $success,
				'data'    => $data,
				'error'   => $message,
			)
		);
		wp_die();
	}

	/**
	 * Ajax Callback to delete video from an existing playlist.
	 *
	 * @since 1.0.0
	 */
	public function delete_video() {
		// Nounce Verification.
		check_ajax_referer( 'evp-admin-ajax-nonce', 'security' );

		$playlist = isset( $_POST['playlist'] ) ? sanitize_text_field( wp_unslash( $_POST['playlist'] ) ) : '';
		$video = isset( $_POST['video'] ) ? esc_url_raw( wp_unslash( $_POST['video'] ) ) : '';
		$video_id = isset( $_POST['video_id'] ) ? sanitize_text_field( wp_unslash( $_POST['video_id'] ) ) : '';

		$success = false;
		$store_manager = StoreManager::get_instance();

		$data = $store_manager->get_data( $playlist, false );
		$videos = isset( $data['videos'] ) ? $data['videos'] : array();
		if ( isset( $videos[ $video_id ] ) ) {
			unset( $videos[ $video_id ] );
			$data['videos'] = $videos;
			$store_manager->update_data( $playlist, $data );
			$success = true;
		}

		echo wp_json_encode(
			array(
				'success' => $success,
			)
		);
		wp_die();
	}

	/**
	 * Ajax Callback to delete video from an existing playlist.
	 *
	 * @since 1.0.0
	 */
	public function delete_video_new() {
		// Nounce Verification.
		check_ajax_referer( 'evp-admin-ajax-nonce', 'security' );

		$playlist = isset( $_POST['playlist'] ) ? sanitize_text_field( wp_unslash( $_POST['playlist'] ) ) : '';
		$video    = isset( $_POST['video'] ) ? esc_url_raw( wp_unslash( $_POST['video'] ) ) : '';
		$video_id = isset( $_POST['video_id'] ) ? sanitize_text_field( wp_unslash( $_POST['video_id'] ) ) : '';

		$success = false;
		$store_manager = StoreManager::get_instance();

		$obj  = new Get_Playlist( $playlist );
		$data = $obj->init();

		if ( $data && $data instanceof PlaylistData ) {
			$videos    = $data->get( 'videos' );
			$video_arr = array();
			foreach ( $videos as $video ) {
				if ( ! $video || ! $video instanceof VideoData ) {
					continue;
				}
				$id = $video->get( 'id' );
				if ( $id === $video_id ) {
					$success = true;
					continue;
				}
				$video_arr[ $id ] = $video;
			}

			$data->set( 'videos', array_values( $video_arr ) );
			$store_manager->update_data( $playlist, $data );
		}

		echo wp_json_encode(
			array(
				'success' => $success,
			)
		);
		wp_die();
	}

	/**
	 * Ajax Callback to edit information about an existing video.
	 *
	 * @since 1.0.0
	 */
	public function edit_video_info_new() {
		// Nounce Verification.
		check_ajax_referer( 'evp-admin-ajax-nonce', 'security' );

		$playlist = isset( $_POST['playlist'] ) ? sanitize_text_field( wp_unslash($_POST['playlist'] ) ) : '';
		$video = isset( $_POST['video'] ) ? esc_url_raw( wp_unslash( $_POST['video'] ) ) : '';
		$video_id = isset( $_POST['video_id'] ) ? sanitize_text_field( wp_unslash($_POST['video_id'] ) ) : '';

		$a_data = array(
			'title' => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash($_POST['title'] ) ) : '',
			'author' => isset( $_POST['author'] ) ? sanitize_text_field( wp_unslash( $_POST['author'] ) ) : '',
			'author_url' => isset( $_POST['author_url'] ) ? esc_url_raw( wp_unslash( $_POST['author_url'] ) ) : '',
			'thumbnail_url' => isset( $_POST['thumb'] ) ? esc_url_raw( wp_unslash($_POST['thumb'] ) ) : '',
		);

		$success       = false;
		$data          = false;
		$video_arr     = array();
		$store_manager = StoreManager::get_instance();

		$obj    = new Get_Playlist( $playlist );
		$p_data = $obj->init();

		if ( $p_data && $p_data instanceof PlaylistData ) {
			$videos   = $p_data->get( 'videos' );
			$curVideo = false;
			foreach ( $videos as $video ) {
				$id = $video->get( 'id' );
				$video_arr[ $id ] = $video;
			}
		}

		if ( isset( $video_arr[ $video_id ] ) ) {
			$cur_video = $video_arr[ $video_id ];
			foreach ( $a_data as $key => $value ) {
				if ( 'thumbnail_url' === $key ) {
					$thumb_url = $cur_video->get( 'thumbnail_url' );
					array_unshift( $thumb_url, $value );
					$value = array_unique( $thumb_url );
				}
				if ( $value && $cur_video->get( $key ) !== $value ) {
					$cur_video->set_custom( $key, $value );
				}
			}

			$video_arr[ $video_id ] = $cur_video;
			$p_data->set( 'videos', array_values( $video_arr ) );
			$store_manager->update_data( $playlist, $p_data );
			$success = true;
			$data = Getters::get_playlists();
		}

		echo wp_json_encode(
			array(
				'success' => $success,
				'data'    => $data,
			)
		);
		wp_die();
	}

	/**
	 * Ajax Callback to edit information about an existing video.
	 *
	 * @since 1.0.0
	 */
	public function edit_video_info() {
		// Nounce Verification.
		check_ajax_referer( 'evp-admin-ajax-nonce', 'security' );

		$playlist = isset( $_POST['playlist'] ) ? sanitize_text_field( wp_unslash($_POST['playlist'] ) ) : '';
		$video = isset( $_POST['video'] ) ? esc_url_raw( wp_unslash( $_POST['video'] ) ) : '';
		$video_id = isset( $_POST['video_id'] ) ? sanitize_text_field( wp_unslash($_POST['video_id'] ) ) : '';
		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash($_POST['title'] ) ) : '';
		$thumbnail = isset( $_POST['thumb'] ) ? esc_url_raw( wp_unslash($_POST['thumb'] ) ) : '';
		$author = isset( $_POST['author'] ) ? sanitize_text_field( wp_unslash( $_POST['author'] ) ) : '';
		$author_url = isset( $_POST['author_url'] ) ? esc_url_raw( wp_unslash( $_POST['author_url'] ) ) : '';

		$success = false;
		$data = false;
		$store_manager = StoreManager::get_instance();

		$ndata = $store_manager->get_data( $playlist, false );
		$videos = isset( $ndata['videos'] ) ? $ndata['videos'] : array();
		if ( isset( $videos[ $video_id ] ) ) {
			$video_data = $videos[ $video_id ];
			$thumb_url = isset( $video_data['thumbnail_url'] ) && is_array( $video_data['thumbnail_url'] ) ? $video_data['thumbnail_url'] : array();
			array_unshift( $thumb_url, $thumbnail );
			$video_data['thumbnail_url'] = $thumb_url;
			$video_data['title'] = sanitize_text_field( $title );
			$video_data['author_name'] = sanitize_text_field( $author );
			$video_data['author_url'] = esc_url_raw( $author_url );
			$videos[ $video_id ] = $video_data;
			$ndata['videos'] = $videos;
			$store_manager->update_data( $playlist, $ndata );
			$success = true;
			$data = Getters::get_playlists();
		}

		echo wp_json_encode(
			array(
				'success' => $success,
				'data'    => $data,
			)
		);
		wp_die();
	}

	/**
	 * Ajax Callback to modify playlist video sort order.
	 *
	 * @since 1.0.0
	 */
	public function save_playlist_sorting() {
		// Nounce Verification.
		check_ajax_referer( 'evp-admin-ajax-nonce', 'security' );

		$playlist = isset( $_POST['playlist'] ) ? sanitize_text_field( wp_unslash( $_POST['playlist'] ) ) : '';
		$videos   = isset( $_POST['videos'] ) ? array_map( 'esc_url_raw', wp_unslash( $_POST['videos'] ) ) : '';
		$ids      = isset( $_POST['ids'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ids'] ) ) : '';

		$store_manager = StoreManager::get_instance();

		$data = $store_manager->get_data( $playlist, false );
		$old_videos = isset( $data['videos'] ) ? $data['videos'] : array();
		$new_videos = array();

		foreach ( $ids as $id ) {
			if ( ! isset( $old_videos[ $id ] ) ) {
				continue;
			}
			$new_videos[ $id ] = $old_videos[ $id ];
		}

		$data['videos'] = $new_videos;
		$store_manager->update_data( $playlist, $data );
		echo wp_json_encode(
			array(
				'success' => true,
			)
		);
		wp_die();
	}

	/**
	 * Ajax Callback to modify playlist video sort order.
	 *
	 * @since 1.0.0
	 */
	public function save_playlist_sorting_new() {
		// Nounce Verification.
		check_ajax_referer( 'evp-admin-ajax-nonce', 'security' );

		$playlist = isset( $_POST['playlist'] ) ? sanitize_text_field( wp_unslash( $_POST['playlist'] ) ) : '';
		$ids      = isset( $_POST['ids'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ids'] ) ) : '';
		$success  = false;

		$store_manager = StoreManager::get_instance();

		$obj    = new Get_Playlist( $playlist );
		$p_data = $obj->init();

		if ( $p_data && $p_data instanceof PlaylistData ) {
			$p_data->set( 'sort_order', $ids );
			$store_manager->update_data( $playlist, $p_data );
			$success = true;
		}
		echo wp_json_encode(
			array(
				'success' => $success,
			)
		);
		wp_die();
	}

	/**
	 * Save API Key to database.
	 *
	 * @since 1.0.0
	 */
	public function save_api_key() {
		// Nounce Verification.
		check_ajax_referer( 'evp-admin-ajax-nonce', 'security' );
		$key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
		$val = isset( $_POST['api_val'] ) ? sanitize_text_field( wp_unslash( $_POST['api_val'] ) ) : '';
		$api = get_option( 'evp_settings_api' );
		$api = $api && is_array( $api ) ? $api : array();
		$api[ $key ] = $val;
		update_option( 'evp_settings_api', $api );
		echo wp_json_encode(
			array(
				'success' => true,
			)
		);
		wp_die();
	}
}
