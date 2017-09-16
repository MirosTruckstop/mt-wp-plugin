<?php
class MT_Widget_Recent_News extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'description' => __('Zeigt die letzten MT News', MT_NAME)
		);
		parent::__construct('mt_news', __('MT News', MT_NAME), $widget_ops);
	}

	function widget( $args, $instance ) {
		$output = '';
		
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'News', MT_NAME );
		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;

		$output .= $args['before_widget'];
		if ( $title ) {
			$output .= $args['before_title'] . $title . $args['after_title'];
		}
		
		$output .= '<ul>';
		$news = MT_News::getAll('*', 'date DESC', $number);
		foreach ($news as $item) {
			// News link
			if( empty( $item->gallery ) ) {
				$news_link = '../';
			} else {
				$news_link = MT_Photo::GALLERY_PATH_ABS.'/'.$item->gallery;
			}
			
			$title = explode(': ', $item->title);
			if (count($title) >= 2) {
				$shortTitle = str_replace('in der Galerie', 'in', $title[1]);
				$output .= '<li><a href="' . $news_link . '">' . $shortTitle . '</a></li>';
			}
		}
		$output .= '</ul>';
		$output .= $args['after_widget'];
		
		echo $output;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );
		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of comments to show:' ); ?></label>
		<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>
		<?php
	}
}