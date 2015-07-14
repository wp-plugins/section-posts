<?php
/**
 * Plugin Name: Section Posts Widget
 * Description: Displays posts related to sections. 
 * Requires: <a target="_blank" href="http://archetyped.com/tools/cornerstone/">Cornerstone</a>.
 * Dependency: Cornerstone
 * Version: 0.1
 * Author: Andrew Eatherington
 * Author URI: http://andrew.eatherington.com
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action('widgets_init', 'section_posts_load_widget');

/**
 * Register widget.
 * 'Section_Posts_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function section_posts_load_widget() {
  register_widget('Section_Posts_Widget');
}


/**
 * Section Posts class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.
 *
 * @since 0.1
 */
class Section_Posts_Widget extends WP_Widget {
    /**
    * Widget setup.
    */
  function Section_Posts_Widget() {
    /* Widget settings. */
    $widget_ops = array( 'classname' => 'section-posts', 'description' => __('A widget that displays posts related to a section.', 'section-posts') );

    /* Widget control settings. */
    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'section-posts-widget' );

    /* Create the widget. */
    $this->WP_Widget( 'section-posts-widget', __('Section Posts Widget', 'section-posts'), $widget_ops, $control_ops );
  }


  /**
   * Widget display.
   */
  function widget( $args, $instance ) {
    extract( $args );
    
    /* Our variables from the widget settings. */
    $title = apply_filters('widget_title', $instance['title'] );

    $post_section = $instance['section'];

    $show_excerpt = isset( $instance['show_excerpt'] ) ? $instance['show_excerpt'] : false;

    $show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;

    $show_thumbnail = isset( $instance['show_thumbnail'] ) ? $instance['show_thumbnail'] : false;

    /* Before widget (defined by themes). */
    echo $before_widget;

    /* Display the widget title if one was input (before and after defined by themes). */

    if ( $title ) :
      echo $before_title . $title . $after_title;
    ?>
    <ul>
      <?php
        // Get the page ID.
        $page = get_page_by_title($post_section);
        // Construct the Wordpress query.
        $media_section_query = new WP_Query('post_parent='.$page->ID);
        // Get the posts.
        while ($media_section_query->have_posts()) : $media_section_query->the_post(); $do_not_duplicate = $post->ID;
      ?>
  
        <li>
          <?php
            if ( has_post_thumbnail() && $show_thumbnail ) { 
              // check if the post has a Post Thumbnail assigned to it and if the widget requires display.
              the_post_thumbnail( 'thumbnail' );
            }
          ?>
          <p class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></p>
          
          <?php if ($show_date) : ?>
            <p><?php the_time('F jS, Y'); ?></p>
          <?php endif; ?>
          
          <?php if ($show_excerpt) : ?>
            <div class="entry">
              <?php the_excerpt(); ?>
              <a href="<?php echo get_permalink(); ?>"> Read More...</a>
            </div>
          <?php endif; ?>  
        </li>
  
      <?php endwhile; ?>
      <?php wp_reset_postdata(); ?>
    </ul>

    <?php
    endif; // End have_posts()
    
    /* After widget (defined by themes). */
    echo $after_widget;
  }


  /**
   * Update the widget settings.
   */
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;

    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['section'] = $new_instance['section'];
    $instance['show_thumbnail'] = $new_instance['show_thumbnail'];
    $instance['show_date'] = $new_instance['show_date'];
    $instance['show_excerpt'] = $new_instance['show_excerpt'];

    return $instance;
  }


  /**
   * Displays the widget settings controls on the widget panel.
   * Make use of the get_field_id() and get_field_name() function
   * when creating your form elements. This handles the confusing stuff.
   */
  function form( $instance ) {

    /* Set up some default widget settings. */
    $defaults = array( 'title' => __('Section posts', 'section-posts'), 'section' => '', 'show_date' => false, 'show_excerpt' => false, 'show_thumbnail' => false);
    $instance = wp_parse_args( (array) $instance, $defaults );

    $title = $instance['title'];
    $section = $instance['section'];
    $show_date = $instance['show_date'];
    
    ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
    
    <!-- Section: Select form. Retrieves pages/sections. -->
    <p>
    <label for="<?php echo $this->get_field_id( 'section' ); ?>"><?php _e('Section:', 'section-posts'); ?></label>
    <select
        id="<?php echo $this->get_field_id( 'section' ); ?>"
          name="<?php echo $this->get_field_name( 'section' ); ?>"
            class="widefat"
              style="width:100%;">
     <option value="none">

    <?php echo esc_attr( __( 'Select section' ) ); ?></option>
    
    <?php
      $pages = get_pages();
      foreach ($pages as $page) {
        $post_section = $page->post_title;
        echo '<option value="' . $post_section . '" id="' . $page->post_name . '"', $post_section == $section ? ' selected="selected"' : '', '>', $post_section, '</option>';
      }
    ?>
    </select>
    </p>
    
    <!-- Options: thumbnail. -->
    <p>
			<input class="checkbox" type="checkbox" value="1" <?php checked( $instance['show_thumbnail'], 1 ); ?> id="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbnail' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>"><?php _e('Show thumbnail?', 'default'); ?></label>
			<p>The featured image.</p>
		</p>

    <!-- Options: date. -->
		<p>
			<input class="checkbox" type="checkbox" value="1" <?php checked( $instance['show_date'], 1 ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e('Show the date?', 'default'); ?></label>
		</p>
    
    <!-- Options: excerpt. -->
		<p>
			<input class="checkbox" type="checkbox" value="1" <?php checked( $instance['show_excerpt'], 1 ); ?> id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_excerpt' ); ?>"><?php _e('Show excerpt?', 'default'); ?></label>
			<p>When a post has no manual excerpt WordPress generates an excerpt automatically by selecting the first 55 words of the post.</p>
		</p>




	<?php
  }
}

?>