<?php
/**
 * Widget API: Display Video Playlist
 *
 * @package Easy_Video_Playlist
 * @link    https://easypodcastpro.com/
 * @since   1.0.0
 */

namespace Easy_Video_Playlist\Backend\Inc;

use Easy_Video_Playlist\Frontend\Inc\Display;
use Easy_Video_Playlist\Helper\Functions\Getters;

/**
 * Class used to display video playlist.
 *
 * @since 1.0.0
 *
 * @see WP_Widget
 */
class Widget extends \WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var array
	 */
	protected $defaults = array();

	/**
	 * Sets up a new Blank widget instance.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Set widget instance settings default values.
		$this->defaults = array(
            'title'    => '',
			'playlist' => '',
		);

		// Set the widget options.
		$widget_ops = array(
			'classname'                   => 'easy_video_playlist',
			'description'                 => esc_html__( 'Display Video Playlist Widget.', 'easy-video-playlist' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'easy_video_playlist_widget', esc_html__( 'Easy Video Playlist', 'easy-video-playlist' ), $widget_ops );
	}

	/**
	 * Outputs the content for the current widget instance.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current widget instance.
	 */
	public function widget( $args, $instance ) {

		$args['widget_id'] = isset( $args['widget_id'] ) ? $args['widget_id'] : $this->id;

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$display = new Display( $instance );
        $return  = false;
		$display->render( $return );
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get array of all widget options.
	 *
	 * @param array $settings Array of settings for current widget instance.
	 *
	 * @since 1.0.0
	 */
	public function get_widget_options( $settings ) {
		$widget    = $this;
		$playlists = Getters::get_playlist_index();

		return apply_filters(
			'easy_video_playlist_widget_options',
			array(
				'default'   => array(
					'title' => esc_html__( 'General Options', 'easy-video-playlist' ),
					'items' => array(
						'title' => array(
							'setting' => 'title',
							'label'   => esc_html__( 'Title', 'easy-video-playlist' ),
							'type'    => 'text',
						),
                        'playlist' => array(
							'setting' => 'playlist',
							'label'   => esc_html__( 'Select Playlist to Display', 'easy-video-playlist' ),
							'type'    => 'select',
							'choices' => $playlists,
						),
					),
				),
			),
			$widget,
			$settings
		);
	}

	/**
	 * Outputs the settings form for the widget.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$options  = $this->get_widget_options( $instance );

		$default_markup = '';
		$options_markup = '';
		foreach ( $options as $option => $args ) {
			$items  = $args['items'];
			$showop = isset( $args['op_callback'] ) && is_callable( $args['op_callback'] ) ? call_user_func( $args['op_callback'] ) : true;
			$markup = '';
			foreach ( $items as $item => $attr ) {
				$dcall = isset( $attr['display_callback'] ) && is_callable( $attr['display_callback'] ) ? call_user_func( $attr['display_callback'] ) : true;
				if ( ! $dcall ) {
					continue;
				}

				$set   = $attr['setting'];
				$id    = esc_attr( $this->get_field_id( $set ) );
				$name  = esc_attr( $this->get_field_name( $set ) );
				$type  = $attr['type'];
				$label = isset( $attr['label'] ) ? $attr['label'] : '';
				$desc  = isset( $attr['desc'] ) ? $attr['desc'] : '';
				$iatt  = isset( $attr['input_attrs'] ) ? $attr['input_attrs'] : array();
				$hcal  = isset( $attr['hide_callback'] ) && is_callable( $attr['hide_callback'] ) ? call_user_func( $attr['hide_callback'] ) : false;

				$inputattr = '';
				foreach ( $iatt as $att => $val ) {
					$inputattr .= esc_html( $att ) . '="' . esc_attr( $val ) . '" ';
				}

				switch ( $type ) {
					case 'select':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= $this->select( $set, $attr['choices'], $instance[ $set ], array(), false );
						break;
					case 'checkbox':
						$optmar  = sprintf( '<input name="%s" id="%s" type="checkbox" value="yes" %s />', $name, $id, checked( $instance[ $set ], 'yes', false ) );
						$optmar .= $this->label( $set, $label, false );
						break;
					case 'text':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= sprintf( '<input class="widefat" name="%1$s" id="%2$s" type="text" value="%3$s" />', $name, $id, esc_attr( $instance[ $set ] ) );
						$optmar .= sprintf( '<div class="evp-desc">%s</div>', $desc );
						break;
					case 'url':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= sprintf( '<input class="widefat" name="%1$s" id="%2$s" type="url" value="%3$s" %4$s />', $name, $id, esc_attr( $instance[ $set ] ), $inputattr );
						$optmar .= sprintf( '<div class="evp-desc">%s</div>', $desc );
						break;
					case 'number':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= sprintf( '<input class="widefat" name="%1$s" id="%2$s" type="number" value="%3$s" %4$s />', $name, $id, absint( $instance[ $set ] ), $inputattr );
						$optmar .= sprintf( '<div class="evp-desc">%s</div>', $desc );
						break;
					case 'textarea':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= sprintf( '<textarea class="widefat" name="%1$s" id="%2$s" %3$s >%4$s</textarea>', $name, $id, $inputattr, esc_attr( $instance[ $set ] ) );
						break;
					case 'image_upload':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= $this->image_upload( $id, $name, $instance[ $set ] );
						break;
					case 'color':
						$optmar  = $this->label( $set, $label, false );
						$optmar .= sprintf( '<input class="evp-color-picker" name="%1$s" id="%2$s" type="text" value="%3$s" />', $name, $id, esc_attr( sanitize_hex_color( $instance[ $set ] ) ) );
						break;
					default:
						$optmar = apply_filters( 'easy_video_playlist_custom_option_field', false, $item, $attr, $this, $instance );
						break;
				}
				$style   = $hcal ? 'style="display: none;"' : '';
				$markup .= $optmar ? sprintf( '<div class="%1$s evp-widget-option" %2$s>%3$s</div>', $set, $style, $optmar ) : '';
			}
			if ( 'default' === $option ) {
				$default_markup = $markup;
			} else {
				$opstyle         = $showop ? '' : 'style="display: none;"';
				$section         = sprintf( '<a class="evp-settings-toggle evp-%1$s-toggle" %2$s>%3$s</a>', $option, $opstyle, $args['title'] );
				$section        .= sprintf( '<div class="pp_settings-content evp-%1$s-content">%2$s</div>', $option, $markup );
				$options_markup .= $section;
			}
		}
		printf( '%1$s<div class="evp-options-wrapper">%2$s</div>', $default_markup, $options_markup ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Handles updating the settings for the current widget instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {

		// Merge with defaults.
		$new_instance = wp_parse_args( (array) $new_instance, $this->defaults );
		$instance = $old_instance;

		$sanitize_text = array(
			'playlist',
		);
		foreach ( $sanitize_text as $setting ) {
			$instance[ $setting ] = sanitize_text_field( $new_instance[ $setting ] );
		}

		return apply_filters( 'easy_video_playlist_widget_update', $instance, $new_instance, $this );
	}

	/**
	 * Check if widget setting contains a particular value.
	 *
	 * @param str   $setting Setting to be checked.
	 * @param str   $val Setting value to be matched.
	 * @param array $settings Array of settings for current widget instance.
	 * @return bool
	 */
	public function is_option_equal( $setting, $val, $settings ) {
		return isset( $settings[ $setting ] ) && $val === $settings[ $setting ];
	}

	/**
	 * Check if widget setting doen not contains a particular value.
	 *
	 * @param str   $setting Setting to be checked.
	 * @param str   $val Setting value to be matched.
	 * @param array $settings Array of settings for current widget instance.
	 * @return bool
	 */
	public function is_option_not_equal( $setting, $val, $settings ) {
		return ! isset( $settings[ $setting ] ) || $val !== $settings[ $setting ];
	}

	/**
	 * Markup for 'label' for widget input options.
	 *
	 * @param str  $for  Label for which ID.
	 * @param str  $text Label text.
	 * @param bool $echo Display or Return.
	 * @return void|string
	 */
	public function label( $for, $text, $echo = true ) {
		$label = '';
		if ( $for && $text ) {
			$label = sprintf( '<label for="%s">%s</label>', esc_attr( $this->get_field_id( $for ) ), esc_html( $text ) );
		}
		if ( $echo ) {
			echo $label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return $label;
		}
	}

	/**
	 * Markup for Select dropdown lists for widget options.
	 *
	 * @param str   $for      Select for which ID.
	 * @param array $options  Select options as 'value => label' pair.
	 * @param str   $selected selected option.
	 * @param array $classes  Options HTML classes.
	 * @param bool  $echo     Display or return.
	 * @return void|string
	 */
	public function select( $for, $options, $selected, $classes = array(), $echo = true ) {
		$select      = '';
		$final_class = '';
		foreach ( $options as $value => $label ) {
			if ( isset( $classes[ $value ] ) ) {
				$option_classes = (array) $classes[ $value ];
				$option_classes = array_map( 'esc_attr', $option_classes );
				$final_class    = 'class="' . join( ' ', $option_classes ) . '"';
			}
			$select .= sprintf( '<option value="%1$s" %2$s %3$s>%4$s</option>', esc_attr( $value ), $final_class, selected( $value, $selected, false ), esc_html( $label ) );
		}

		$select = sprintf(
			'<select id="%1$s" name="%2$s" class="easy-video-playlist-%3$s widefat">%4$s</select>',
			esc_attr( $this->get_field_id( $for ) ),
			esc_attr( $this->get_field_name( $for ) ),
			esc_attr( str_replace( '_', '-', $for ) ),
			$select
		);

		if ( $echo ) {
			echo $select; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return $select;
		}
	}

	/**
	 * Image upload option markup.
	 *
	 * @since 1.0.0
	 *
	 * @param str $id      Field ID.
	 * @param str $name    Field Name.
	 * @param int $value   Uploaded image id.
	 * @return str Widget form image upload markup.
	 */
	public function image_upload( $id, $name, $value ) {

		$value          = absint( $value );
		$uploader_class = '';
		$class          = 'easy-video-playlist-hidden';

		if ( $value ) {
			$image_src = wp_get_attachment_image_src( $value, 'large' );
			if ( $image_src ) {
				$featured_markup = sprintf( '<img class="custom-widget-thumbnail" src="%s">', esc_url( $image_src[0] ) );
				$class           = '';
				$uploader_class  = 'has-image';
			} else {
				$featured_markup = esc_html__( 'Podcast Cover Image', 'easy-video-playlist' );
			}
		} else {
			$featured_markup = esc_html__( 'Podcast Cover Image', 'easy-video-playlist' );
		}

		$markup  = sprintf( '<a class="easy-video-playlist-widget-img-uploader %s">%s</a>', $uploader_class, $featured_markup );
		$markup .= sprintf( '<span class="easy-video-playlist-widget-img-instruct %s">%s</span>', $class, esc_html__( 'Click the image to edit/update', 'easy-video-playlist' ) );
		$markup .= sprintf( '<a class="easy-video-playlist-widget-img-remover %s">%s</a>', $class, esc_html__( 'Remove Featured Image', 'easy-video-playlist' ) );
		$markup .= sprintf( '<input class="easy-video-playlist-widget-img-id" name="%s" id="%s" value="%s" type="hidden" />', $name, $id, $value );
		return $markup;
	}

	/**
	 * Markup for multiple checkbox for widget options.
	 *
	 * @param str   $for      Select for which ID.
	 * @param array $options  Select options as 'value => label' pair.
	 * @param str   $selected selected option.
	 * @param array $classes  Checkbox input HTML classes.
	 * @param bool  $echo     Display or return.
	 * @return void|string
	 */
	public function mu_checkbox( $for, $options, $selected = array(), $classes = array(), $echo = true ) {

		$final_class = '';

		$mu_checkbox = '<div class="' . esc_attr( $for ) . '-checklist"><ul id="' . esc_attr( $this->get_field_id( $for ) ) . '">';

		$selected    = array_map( 'strval', $selected );
		$rev_options = $options;

		// Moving selected items on top of the array.
		foreach ( $options as $id => $label ) {
			if ( in_array( strval( $id ), $selected, true ) ) {
				$rev_options = array( $id => $label ) + $rev_options;
			}
		}

		// Bring default option at top.
		if ( isset( $rev_options[''] ) ) {
			$rev_options = array( '' => $rev_options[''] ) + $rev_options;
		}

		foreach ( $rev_options as $id => $label ) {
			if ( isset( $classes[ $id ] ) ) {
				$final_class = ' class="' . esc_attr( $classes[ $id ] ) . '"';
			}
			$mu_checkbox .= "\n<li$final_class>" . '<label class="selectit"><input value="' . esc_attr( $id ) . '" type="checkbox" name="' . esc_attr( $this->get_field_name( $for ) ) . '[]"' . checked( in_array( strval( $id ), $selected, true ), true, false ) . ' /><span class="cblabel">' . esc_html( $label ) . "</span></label></li>\n";
		}
		$mu_checkbox .= "</ul></div>\n";

		if ( $echo ) {
			echo $mu_checkbox; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return $mu_checkbox;
		}
	}
}
