<?php
/**
 * Add function to widgets_init that'll load our widget.
 */
add_action('widgets_init','st_daily_tip_load_widget');
 
 
 /**
 * Register our widget.
 * 'st_daily_tip_load_widget' is the widget class used below.
 */
 function st_daily_tip_load_widget()
 {
	register_widget('st_daily_tip_widget'); 
 }
 
 class st_daily_tip_widget extends WP_Widget
 {
 
	/**
	 * Widget setup.
	 */
	 
	 function st_daily_tip_widget()
	 {
		/* Widget settings. */
		$widget_ops=array('classname'=>'daily_tip','description'=>__('An Widget that display Daily Tip','daily_tip'));
	
		/* Widget control settings. */
		$control_ops=array('width'=>300,'Height'=>350,'id_base'=>'st-daily-tip-widget');
		
		/* Create the widget. */
		parent::__construct('st-daily-tip-widget',__('Daily Tip Widget','daily_tip'),$widget_ops,$control_ops);
	}	
	
	/**
	 * How to display the widget on the screen.
	 */
	 
	 function widget($args,$instance)
	 {
		extract($args);
		
		/* Our variables from the widget settings. */
		$title=apply_filters('widget_title',$instance['title']);
		if ( $title )
		{
			echo $before_title . $title . $after_title;
		}
		$group=apply_filters('group_name',$instance['group']);
		if($group==null)
		{
			$group="Tip";
		}
		
		$date=apply_filters('date',$instance['date']);
		$showtitle=apply_filters('showtitle',$instance['showtitle']);
		$datetitle=apply_filters('datetitle',$instance['datetitle']);
		
		if( $showtitle AND $showtitle == '1' ){
			$showtitle = "show";
		}
		if( $datetitle AND $datetitle == '1' ){
			$datetitle = "show";
		}
		
		if( $date AND $date == '1' )
		{
			$date="show";
			echo '<div class="st_tip">';
			$today_tip = select_today_tip($group,$date,$datetitle,$showtitle);
			$today_tip=explode("Last Shown Date:",$today_tip);
			echo $today_tip[0]."</br> Last Shown Date:".$today_tip[1];
			echo '</div>';
		}
		else
		{
			$date="Not Show";
			echo '<div class="st_tip">';
			$today_tip = select_today_tip($group,$date,$datetitle,$showtitle);
			echo $today_tip;
			echo '</div>';
		}
		
		
	 }
	 
	 function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['group'] = strip_tags( $new_instance['group'] );
		$instance['date'] = strip_tags($new_instance['date']);
		$instance['showtitle'] = strip_tags($new_instance['showtitle']);
		$instance['datetitle'] = strip_tags($new_instance['datetitle']);
		return $instance;
	}
	
	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	 
	function form( $instance ) 
	{
		/* Set up some default widget settings. */
		$defaults = array(  'title' => __('Daily Tip', 'Daily Tip') ,
							'group' => __('Tip', 'Tip') ,
							'date' => __('0', '0') ,
							'showtitle' => __('0', '0') ,
							'datetitle' => __('0', '0') ,
							);
		
		
		
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		$title = esc_attr($instance['title']);
		$group = esc_attr($instance['group']);
		$date = esc_attr($instance['date']);
		$showtitle = esc_attr($instance['showtitle']);
		$datetitle = esc_attr($instance['datetitle']);
		
	?>
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'stdailytip'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'group' ); ?>"><?php _e('Group Name:', 'stdailytip'); ?></label>
			<input id="<?php echo $this->get_field_id( 'group' ); ?>" name="<?php echo $this->get_field_name( 'group' ); ?>" value="<?php echo $group; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php _e('Show last date:', 'stdailytip'); ?></label>
			<input id="<?php echo $this->get_field_id('date'); ?>" name="<?php echo $this->get_field_name('date'); ?>" type="checkbox" value="1" <?php checked( '1', $date); ?> />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'datetitle' ); ?>"><?php _e('Show Date title:', 'stdailytip'); ?></label>
			<input id="<?php echo $this->get_field_id('datetitle'); ?>" name="<?php echo $this->get_field_name('datetitle'); ?>" type="checkbox" value="1" <?php checked( '1', $datetitle); ?> />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'showtitle' ); ?>"><?php _e('Show title:', 'stdailytip'); ?></label>
			<input id="<?php echo $this->get_field_id('showtitle'); ?>" name="<?php echo $this->get_field_name('showtitle'); ?>" type="checkbox" value="1" <?php checked( '1', $showtitle); ?> />
		</p>
	<?php
	}
}?>