<?php
/*
Plugin Name: Instagram-Widget-for-WordPress
Plugin URI: http://instagram.davdimregister.com
Description: This plugin get a users recent images, up to 10, and displays them in a Wordpress Widget. It will also display likes and comments if uplaoded with the images.
Version: 1.0
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
		$username = apply_filters( 'widget_title', $instance['username'] );
		$password = apply_filters( 'widget_title', $instance['password'] );
		
		$user_id = apply_filters( 'widget_title', $instance['user_id'] );
		$access_token = apply_filters( 'widget_title', $instance['access_token'] );
		
		$picture_number = apply_filters( 'widget_title', $instance['picture_number'] );
		
		/*
		For later use when login can be CURLed ----DO NOT UNCOMMENT THESE LINE----
		$user_id = get_option("my_instagram_userID", true);
		$access_token = get_option("my_instagram_accesstoken", true);
		*/
		
		echo $before_widget;
		if ( $title ){
			echo $before_username ."<p id='instagram_widget_title'>". $title ."</p>". $after_username; 
		};
		
		/*
		For later use when login can be CURLed ----DO NOT UNCOMMENT THESE LINE----
		if(get_option("my_instagram_error_message")){
			echo get_option("my_instagram_error_message", true);
		}else{
		
		}
		*/	
		
		$results = $this->get_recent_data($user_id,$access_token);
		$i=1;
		echo "<ul id='instagram_widget'>";
		foreach($results->data as $item){
			if($picture_number == 0){
				echo "<strong>Please set the Number of images to show within the widget</strong>";
				break;
			}
			echo "<li><img src='".$item->images->thumbnail->url."' alt=''/></li>";
			if($i == $picture_number){
				echo "</ul>";
				break;
			}else{
				$i++;
			}
		}
		
		echo $after_widget;
	}

	/* WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		//update setting with information form widget form
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['username'] = strip_tags($new_instance['username']);
		$instance['password'] = strip_tags($new_instance['password']);
		$instance['access_token'] = strip_tags($new_instance['access_token']);
		$instance['user_id'] = strip_tags($new_instance['user_id']);
		$instance['picture_number'] = strip_tags($new_instance['picture_number']);
		
		/*
		Automatic CURL Login for instagram authencation, Wating to upgrade Instagram App gateway
		this will elimiate the step of getting your User_ID and access token manually
		
		$ch = curl_init('https://api.instagram.com/oauth/access_token');
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, "username=".$instance['username']."&password=".$instance['password']."&grant_type=password&client_id=314982808dbd45fa8e9d519780adead3&client_secret=7755f3f394154c7ea899b07dbdb7ffca");
		$result = curl_exec ($ch);
		curl_close ($ch);
					
		// convert output to an array from JSON
			$result = json_decode($result);
			
		// Error detection
			if(($result->error_message) != ""){ 
				update_option("my_instagram_error_message", $result->error_message);
			}else{		
				
				// get user_id and accesss_token from curl response
					$userID = $result->user->id;
					$token = $result->access_token;
					
				// save to DB for later use
					update_option("my_instagram_userID", $userID);
					update_option("my_instagram_accesstoken", $token);
					update_option("my_instagram_error_message", $token);
			}
		*/
		return $instance;
	}

	/* WP_Widget::form */
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
			$username = esc_attr( $instance[ 'username' ] );
			$password = esc_attr( $instance[ 'password' ] );
			$access_token = esc_attr( $instance[ 'access_token' ] );
			$user_id = esc_attr( $instance[ 'user_id' ] );
			$picture_number = esc_attr( $instance[ 'picture_number' ] );
		}
		else {
			$title = __( 'Title', 'text_domain' );
			$username = __( 'Username', 'text_domain' );
			$access_token = __( 'Access Token', 'text_domain' );
			$user_id = __( 'User ID', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<!--<p>
		<label for="<?php echo $this->get_field_id('username'); ?>"><?php _e('Username:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo $username; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('password'); ?>"><?php _e('Password:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('password'); ?>" name="<?php echo $this->get_field_name('password'); ?>" type="password" value="<?php echo $password; ?>" />
		</p>-->
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