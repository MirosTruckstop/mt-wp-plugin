<?php
class MT_Widget_Random_Photo extends WP_Widget {

	function __construct() {
		parent::__construct('mt_random_photo', __('MT Zufallsphoto', MT_NAME), array(
			'description' => __('Zeigt ein zufälliges Bild an', MT_NAME)
		));
	}

	function widget( $args, $instance ) {
		$photo = MT_Photo::getRandom(array('id', 'path', 'gallery', 'description'));
		if (!$photo) {
			return;
		}
		
		$output = '';
		
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'News', MT_NAME );

		$output .= $args['before_widget'];
		if ( $title ) {
			$output .= $args['before_title'] . $title . $args['after_title'];
		}
		$output .= '<a href="'.MT_Photo::GALLERY_PATH_ABS.'/'.$photo->gallery.'">';
		$output .= '<img width="100%" alt="'.$photo->description.'" src="/bilder/thumb/'.$photo->path.'">';
		$output .= '</a>';
		
		$output .= $args['after_widget'];
		echo $output;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
		<?php
	}
}