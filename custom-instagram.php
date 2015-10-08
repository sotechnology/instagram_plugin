<?php

/*
Plugin Name: Instagram Api
Plugin URI: Nil
Description: A plugin with shortcodes to retrieve instagram endpoints
Version: 1.0
Author: Samuel Roberts
Author URI: http://cuttingedgeweb.co.uk
*/
 
    add_action( 'http_request_args', 'no_ssl_http_request_args', 10, 2 );
    function no_ssl_http_request_args( $args, $url ) {
        $args['sslverify'] = false;
        return $args;
    }

    add_shortcode( 'instagramWidget', 'instagradam_widget' );
     
    function instagradam_widget( $atts, $content = null ) {
        $feed = wp_remote_get( "https://api.instagram.com/v1/users/".get_option('user_id')."/media/recent/?access_token=".get_option('access_token')."");
	    $user = wp_remote_get( "https://api.instagram.com/v1/users/".get_option('user_id')."/?access_token=".get_option('access_token')."" );
		$user = json_decode($user['body'], true);
		$feed = json_decode($feed['body'], true);
        ob_start();
?>
		
		<div class='instagram-api'>
			<div class='head'>
				<a target="_blank" href='https://instagram.com/<?php echo $user['data']['username']; ?>' class='user-image'>
					<img src="<?php echo $user['data']['profile_picture']; ?>">
				</a>
				<div class='user-info'>
					<div class='name'>
						<h3><?php echo $user['data']['username']; ?></h3>
					</div>
					<div class='posts'>
						<span><?php echo $user['data']['counts']['media']; ?></span> posts
					</div>
					<div class='followers'>
						<span><?php echo $user['data']['counts']['follows']; ?></span> followers
					</div>
					<a target="_blank" class='follow' href='https://instagram.com/<?php echo $user['data']['username']; ?>'>FOLLOW</a>
				</div>
			</div>
			<div class='feed'>
				<?php 
					$count = 1;
					foreach($feed['data'] as $image):
						if($count < 15):
				?>
						<a target="_blank" href='<?php echo $image["link"]; ?>' class='image'>
							<img src="<?php echo $image['images']['low_resolution']['url']; ?>">
							<div class='cover'></div>
						</a>
				<?php 
						endif;
						$count ++;
					endforeach; 
				?>
				<div style='clear:both'></div>
			</div>
		</div>
<?php
		return ob_get_clean();
    }

	function load_plugin_css() {
	    $plugin_url = plugin_dir_url( __FILE__ );
	    wp_enqueue_style( 'plugin_style', $plugin_url . 'plugin.css' );
	}
	add_action( 'wp_enqueue_scripts', 'load_plugin_css' );

    /*=======================================================================================*/

    /*================================== ADMIN AREA =========================================*/

    /*=======================================================================================*/

	// create custom plugin settings menu
	add_action('admin_menu', 'instagram_api_create_menu');

	function instagram_api_create_menu() {

		//create new top-level menu
		add_menu_page('Instagram Api Settings', 'Instagram Api', 'administrator', __FILE__, 'instagram_api_settings_page');

		//call register settings function
		add_action( 'admin_init', 'register_instagram_api_settings' );
	}


	function register_instagram_api_settings() {
		//register our settings
		register_setting( 'instagram-api-settings-group', 'access_token' );
		register_setting( 'instagram-api-settings-group', 'user_id' );
	}

	function instagram_api_settings_page() {
	?>
		<div class="wrap">
			<h2>Instagram Api</h2>

			<form method="post" action="options.php">
			    <?php settings_fields( 'instagram-api-settings-group' ); ?>
			    <?php do_settings_sections( 'instagram-api-settings-group' ); ?>
			    <table class="form-table">
			        <tr valign="top">
			        <th scope="row">Access Token</th>
			        <td><input type="text" name="access_token" value="<?php echo esc_attr( get_option('access_token') ); ?>" /></td>
			        </tr>
			         
			        <tr valign="top">
			        <th scope="row">User id</th>
			        <td><input type="text" name="user_id" value="<?php echo esc_attr( get_option('user_id') ); ?>" /></td>
			        </tr>
			    </table>
			    
			    <?php submit_button(); ?>

			</form>
		</div>
<?php } ?>