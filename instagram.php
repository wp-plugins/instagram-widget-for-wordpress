<?php
/*
Plugin Name: Instagram-Widget-for-WordPress
Plugin URI: http://davidmregister.com/instagram-widget-for-wordpress
Description: This plugin get a users recent images, up to 10, and displays them in a Wordpress Widget. It will also display likes and comments if uplaoded with the images.
Version: 1.3.1
Author: David Register
Author URI: http://davidmregister.com
License: GPL2
*/

/**
 * Instagrm_Feed_Widget Class
 */
class Instagrm_Feed_Widget extends WP_Widget {
	/** constructor */
	function __construct() {
		parent::WP_Widget( /* Base ID */'instagrm_widget', /* Name */'Instagram Widget for WordPress', array( 'description' => 'A widget to display a users instagrm feed' ) );
	}

	/* WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		//get widget information to display on page
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		$user_id = apply_filters( 'widget_title', $instance['user_id'] );
		$access_token = apply_filters( 'widget_title', $instance['access_token'] );
		
		$picture_number = apply_filters( 'widget_title', $instance['picture_number'] );
		$picture_size = apply_filters( 'widget_title', $instance['picture_size'] );
		$link_images = apply_filters( 'widget_title', $instance['link_images'] );
		
		$show_likes = apply_filters( 'widget_title', $instance['show_likes'] );
		$show_caption = apply_filters( 'widget_title', $instance['show_caption'] );
		
		$debug_mode = apply_filters( 'widget_title', $instance['debug_mode'] );
		
		echo $before_widget;
		if ( $title ){
			echo $before_username ."<p id='instagram_widget_title'>". $title ."</p>". $after_username; 
		};
		
		if($debug_mode){
			
			// Check requirements
        	if (extension_loaded('curl')){
				$curl_ver = curl_version();
				echo '<p>Curl is <b>Enabled</b></p>'; 
				echo '<p>Curl Version Number:<br />'.$curl_ver['version_number'].'</p>';
				echo '<p>User ID:<br />'.$user_id.'</p>'; 
				echo '<p>Access Token:<br /><span style="word-wrap:break-word;width:100px;">'.$access_token.'</span></p>'; 
				$results = $this->get_recent_data($user_id,$access_token);
				echo '<p><b>Results</b>:</p>'; 
				foreach($results->meta as $key => $val){
					echo "<p>".$key.": ".$val."</p>";
				}
			}else{
				echo '<p>Curl is <b>NOT</b> Enabled</p>'; 
			}
			return;
		}
		
		?>
		<style>
			.instagram_likes,.instagram_caption{
				margin-bottom: 0px !important;
			}
			#instagram_widget li{
				margin-bottom: 10px;
			}
		</style>
		<?php
		$results = $this->get_recent_data($user_id,$access_token);
		$i=1;
		echo "<ul id='instagram_widget'>";
		if(!empty($results->data)){
			foreach($results->data as $item){
				if($picture_number == 0){
					echo "<strong>Please set the Number of images to show within the widget</strong>";
					break;
				}
				
				echo "<li>";
				if(!empty($link_images)){
					echo "<a href='".$item->link."' target='_blank'><img src='".$item->images->$picture_size->url."' alt='".$title." image'/></a>";
				}else{
					echo "<img src='".$item->images->$picture_size->url."' alt=''/>";
				}
				if($show_likes){
					if(!empty($item->likes->count)){
						echo "<p class='instagram_likes'>Likes: <span class='likes_count'>".$item->likes->count."</span></p>";
					}
				}
				if($show_caption){
					if(!empty($item->caption->text)){
						echo "<p class='instagram_caption'>".$item->caption->text."</p>";
					}
				}
				echo "</li>";
				if($i == $picture_number){
					echo "</ul>";
					break;
				}else{
					$i++;
				}
			}
		}else{
			echo "<strong>The user currently does not have any images...</strong>";			
		}
		echo $after_widget;
	}

	/* WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		//update setting with information form widget form
		$instance['title'] = strip_tags($new_instance['title']);
		
		$instance['access_token'] = strip_tags($new_instance['access_token']);
		$instance['user_id'] = strip_tags($new_instance['user_id']);
		
		
		$instance['picture_number'] = strip_tags($new_instance['picture_number']);
		$instance['picture_size'] = strip_tags($new_instance['picture_size']);
		$instance['link_images'] = strip_tags($new_instance['link_images']);
		
		$instance['show_likes'] = strip_tags($new_instance['show_likes']);
		$instance['show_caption'] = strip_tags($new_instance['show_caption']);
		
		$instance['debug_mode'] = strip_tags($new_instance['debug_mode']);
		
		return $instance;
	}

	/* WP_Widget::form */
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
			
			$access_token = esc_attr( $instance[ 'access_token' ] );
			$user_id = esc_attr( $instance[ 'user_id' ] );
			
			$picture_number = esc_attr( $instance[ 'picture_number' ] );
			$picture_size = esc_attr( $instance[ 'picture_size' ] );
			
			$show_likes = esc_attr( $instance[ 'show_likes' ] );
			$show_caption = esc_attr( $instance[ 'show_caption' ] );
			
			$link_images = esc_attr( $instance[ 'link_images' ] );
			
			$debug_mode = esc_attr( $instance['debug_mode'] );
			
		}
		else {
			$title = __( 'Title', 'text_domain' );
			$username = __( 'Username', 'text_domain' );
			$access_token = __( 'Access Token', 'text_domain' );
			$user_id = __( 'User ID', 'text_domain' );
		}
		
		$picture_sizes = array('thumbnail' => 'Thumbnail', 'low_resolution' => 'Low Resolution','standard_resolution' => 'Standard Resolution');
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('user_id'); ?>"><?php _e('User ID:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('user_id'); ?>" name="<?php echo $this->get_field_name('user_id'); ?>" type="text" value="<?php echo $user_id; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('access_token'); ?>"><?php _e('Access Token:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('access_token'); ?>" name="<?php echo $this->get_field_name('access_token'); ?>" type="text" value="<?php echo $access_token; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('picture_number'); ?>"><?php _e('Number of Images:'); ?></label> 
			<select id="<?php echo $this->get_field_id('picture_number'); ?>" name="<?php echo $this->get_field_name('picture_number'); ?>">
					<option value="0">Select Number</option>
				<?php for($i=1;$i<11;$i++):?>
					<option value="<?php echo $i;?>" <?php if($i == $picture_number){echo 'selected="selected"';};?>><?php echo $i;?></option>
				<?php endfor;?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('picture_size'); ?>"><?php _e('Picture Size:'); ?></label> 
			<select id="<?php echo $this->get_field_id('picture_size'); ?>" name="<?php echo $this->get_field_name('picture_size'); ?>">
					<?php foreach($picture_sizes as $item => $val):?>
						<option value="<?php echo $item;?>" <?php if($item == $picture_size){echo 'selected="selected"';};?>><?php echo $val;?></option>
					<?php endforeach;?>
			</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('link_images'); ?>"><?php _e('Link images to full image:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('link_images'); ?>" name="<?php echo $this->get_field_name('link_images'); ?>" type="checkbox" <?php echo (($link_images)? "CHECKED":''); ?> />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('show_likes'); ?>"><?php _e('Show Likes:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('show_likes'); ?>" name="<?php echo $this->get_field_name('show_likes'); ?>" type="checkbox" <?php echo (($show_likes)? "CHECKED":''); ?> />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('show_caption'); ?>"><?php _e('Show Caption:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('show_caption'); ?>" name="<?php echo $this->get_field_name('show_caption'); ?>" type="checkbox" <?php echo (($show_caption)? "CHECKED":''); ?> />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('debug_mode'); ?>"><?php _e('Debug Mode:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('debug_mode'); ?>" name="<?php echo $this->get_field_name('debug_mode'); ?>" type="checkbox" <?php echo (($debug_mode)? "CHECKED":''); ?> />
		</p>
		<p>If you do not have a ID or access token, please visit <a href="http://instagram.davidmregister.com/" target="_blank">Get Access token</a> to receive a valid token</p>
		<?php 
	}
	
	function get_recent_data($user_id, $access_token){
		//CURL REST API to get users recent photos
		$ch = curl_init();
	
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,'https://api.instagram.com/v1/users/'.$user_id.'/media/recent/?access_token='.$access_token);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//execute post
		$result = curl_exec($ch);
		
		//close connection
		curl_close($ch);
		$result = json_decode($result);
		return $result;
	}

} // class Instagrm_Feed_Widget

// register Instagrm widget
add_action( 'widgets_init', create_function( '', 'register_widget("Instagrm_Feed_Widget");' ) );

?>