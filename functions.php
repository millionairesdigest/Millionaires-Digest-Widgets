<?php
/*
Plugin Name: Millionaire's Digest Widgets
Description: Add widgets specifically created for the Millionaire's Digest made by the Founder & CEO of the Millionaire's Digest
Version: 1.0.0
Author: K&L (Founder of the Millionaire's Digest)
Author URI: https://millionairedigest.com/
*/

/* Register All Widgets */
function register_widgets() {
	register_widget( 'BuddyPress_User_Info_Widget' );
}
add_action( 'widgets_init', 'register_widgets' );

/* BuddyPress User Info */
class BuddyPress_User_Info_Widget extends WP_Widget {
	public function __construct() {
		$widget_ops = array(
			'description' => __( 'Display user\'s profile fields as a widget. Note: This respects the privacy preference set by the user too.', 'bp-user-info-widget' ),
		);
		parent::__construct( false, _x( 'BuddyPress User Info', 'bp-user-info-widget' ), $widget_ops );
	}
	public function widget( $args, $instance ) {
		echo $before_widget;
		echo $before_title
		     . $after_title;
		self::show_blog_profile( $instance );
		echo $after_widget;
			}
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		foreach ( $new_instance as $key => $val ) {
			$instance[ $key ] = $val;//update the instance
		}
		return $instance;
	}
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title'       => __( '', 'bp-user-info-widget' )
		) );
		$title = strip_tags( $instance['title'] );
		extract( $instance, EXTR_SKIP );
		?>
		<p>
			<label for="bp-user-info-widget-title">
				<?php _e( 'Title:', 'bp-user-info-widget' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( stripslashes( $title ) ); ?>"/>
			</label>
		</p>
		<?php
		//get all xprofile fields and ask user whether to show them or not
		?>
		<h3><?php _e( 'Profile Fields Visibility', 'bp-user-info-widget' ); ?></h3>
		<table>
			<?php if ( function_exists( 'bp_has_profile' ) ) : if ( bp_has_profile() ) : while ( bp_profile_groups() ) : bp_the_profile_group(); ?>
				<?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>
					<?php $fld_name = bp_get_the_profile_field_input_name();
						$fld_val        = isset( ${$fld_name} ) ? ${$fld_name} : 'no';
					?>
					<tr>
						<td>
							<label for="<?php echo $fld_name; ?>"><?php bp_the_profile_field_name() ?></label>
						</td>
						<td>
							<input type="radio" id="<?php echo $this->get_field_id( $fld_name ); ?>" name="<?php echo $this->get_field_name( $fld_name ); ?>" value="yes" <?php checked( $fld_val, 'yes' ); ?> >Show
							<input type="radio" id="<?php echo $this->get_field_id( $fld_name ); ?>" name="<?php echo $this->get_field_name( $fld_name ); ?>" value="no" <?php checked( $fld_val, 'no' ); ?>>Hide
						</td>
					</tr>
				<?php endwhile;
			endwhile;
			endif;
			endif; ?>
		</table>
		<?php
	}
	public static function get_users( $user_role = null ) {
		$bp_displayed_user_id = bp_displayed_user_id();
		return $bp_displayed_user_id;
	}
	public static function show_blog_profile( $instance ) {
		//if buddypress is not active, return
		if ( ! function_exists( 'buddypress' ) ) {
			return;
		}
		unset( $instance['title'] );//unset the title of the widget,because we will be iterating over the instance fields
		if ( bp_is_user() ) {
			$bp_displayed_user_id = array( bp_displayed_user_id() );
		}
		if ( empty( $bp_displayed_user_id ) ) {
			return;
		//Do not display the widget if profile field is empty
		}
		foreach ( $bp_displayed_user_id as $user ) {
			$user_id = $user;//["user_id"];
			$op = "<table class='bp-user-info-{$user}'>";
			//bad approach, because buddypress does not allow to fetch the field name from field key
			if ( function_exists( 'bp_has_profile' ) ) :
				if ( bp_has_profile( 'user_id=' . $user_id ) ) :
					while ( bp_profile_groups() ) : bp_the_profile_group();
						while ( bp_profile_fields() ) : bp_the_profile_field();
							$fld_name = bp_get_the_profile_field_input_name();
							if ( array_key_exists( $fld_name, $instance ) && $instance[ $fld_name ] == 'yes' ) {
								$op .= '<tr><h4 class="bp_user_info_title">' . bp_get_the_profile_field_name() . '</h4><p class="bp_user_info_data">' .xprofile_get_field_data( bp_get_the_profile_field_id(),$user_id, 'comma' ) . '</p></tr>';
							}
						endwhile;
					endwhile;
				endif;
			endif;
			$op .= "</table>";
			echo $op;
		}
	}
}
