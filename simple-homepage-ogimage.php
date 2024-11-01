<?php

/**
 * Plugin Name: Simple Homepage Facebook Image
 * Plugin URI: 
 * Description: A very simple plugin to enable image preview for Facebook and Twitter on the homepage.
 * Version: 1.0.8
 * License: GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: shfbi
 * Domain Path: /languages
 * Author: Lapzor
 * Author URI: https://lapzor.com
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

define('shfbi_PLUGIN_TITLE', __('Simple Homepage Facebook Image', 'shfbi'));


if (!function_exists( 'shfbi_wp_head')) {

	function shfbi_wp_head() {

	    try {

            // Attach only to single posts
            if (is_front_page() || is_home()) {

                $og_image = get_option('shfbi_default_image');
                $og_title = ( get_option('shfbi_title') != "" ? get_option('shfbi_title') : get_bloginfo( 'name' ));
                $og_description = ( get_option('shfbi_description') != "" ? get_option('shfbi_description') : get_bloginfo( 'description' ));

                // Found an image? Good. Display it.
                if (!empty($og_image)) {
                    $image = shfbi_prepare_image_url($og_image);
                    $image_secure = shfbi_get_secure_url($og_image);
                    echo '<meta property="og:image" itemprop="image" content="' . $image . '">' . "\n";
                    echo '<meta property="og:image:url" content="' . $image . '">' . "\n";
                    echo '<meta property="og:image:secure_url" content="' . $image_secure . '">' . "\n";
                    echo '<meta property="twitter:card" content="summary_large_image">' . "\n";
                    echo '<meta property="twitter:image" content="' . $image . '">' . "\n";
                    echo '<link rel="image_src" href="' . $image . '">' . "\n";
                }
                echo '<meta property="og:title" content="' . str_replace('"', "'", $og_title) . '">' . "\n";
                echo '<meta property="og:description" content="' . trim(preg_replace('/\s+/', ' ', str_replace('"', "'", $og_description))) . '">' . "\n";
                echo '<meta property="og:url" content="' . get_site_url() . "\n";
                echo '<meta property="twitter:title" content="' . str_replace('"', "'", $og_title) . '">' . "\n";
                echo '<meta property="twitter:description" content="' . trim(preg_replace('/\s+/', ' ', str_replace('"', "'", $og_description))) . '">' . "\n";

            }
        } catch (Exception $ex) {
	        do_action('shfbi_handle_exception', $ex);
        }
	}

}

if (!function_exists( 'shfbi_prepare_image_url')) {
	function shfbi_prepare_image_url($url) {
		$site_url = get_site_url();
		if (is_array($url)) {
		    throw new Exception("URL must be string.");
        }
		// Image path is relative and not an absolute one - apply site url
		if (strpos($url, $site_url) === false) {
			// The $url comes from an external URL
			if (preg_match('{https*://}', $url)) {
				return $url;
			}
			// Make sure there is no double /
			if (substr( $site_url, -1) === '/' && $url[0] === '/') {
				$site_url = rtrim( $site_url, '/' );
			}
			$url = $site_url . $url;
		}	
		return $url;
	}
}

if (!function_exists( 'shfbi_get_secure_url')) {
	function shfbi_get_secure_url($url) {
		return str_replace('http://', 'https://', $url);
	}
}

if (!function_exists( 'shfbi_admin_menu')) {
	function shfbi_admin_menu() {

		add_submenu_page('options-general.php', shfbi_PLUGIN_TITLE, shfbi_PLUGIN_TITLE, 'manage_options', 'shfbi', 'shfbi_options_page');
	}
}

if (!function_exists( 'shfbi_options_page')) {
	// Create options page in Settings
	function shfbi_options_page() {


                $og_title = ( get_option('shfbi_title') != "" ? get_option('shfbi_title') : get_bloginfo( 'name' ));
                $og_description = ( get_option('shfbi_description') != "" ? get_option('shfbi_description') : get_bloginfo( 'description' ));


		?>
<form method="post" action="options.php">
	<?php settings_fields( 'shfbi' ); ?>
	<?php do_settings_sections( 'shfbi' ); ?>

	<script>
	jQuery(function() {
		$.wpMediaUploader({target: '.image-uploader'});
<? if(get_option('shfbi_default_image') != "") { ?>
		$('.image-uploader').find( 'img' ).attr( 'src', '<?php echo esc_attr(get_option('shfbi_default_image')) ?>').show();
<? } ?>

	});
	</script>

	<h1><?php echo shfbi_PLUGIN_TITLE ?></h1>
This plugin let's you setup what image is displayed when sharing your homepage URL to Facebook and Twitter. It will ONLY work for the homepage since many themes already support showing featured image on social media shares for posts, but not for the homepage. 
<table class="form-table">

<tbody><tr>
<th scope="row">
			    <label for="shfbi_default_image">Title</label>
</th><td>
			    <input type="text" name="shfbi_title" class="regular-text" value="<?php echo esc_attr($og_title) ?>" />
			    <p class="description" id="tagline-description">Title to display on social media.</p>
			
</td></tr><tr>
<th scope="row">
			    <label for="shfbi_default_image">Description</label>
</th><td>
			    <textarea name="shfbi_description" rows="3" class="regular-text"><?php echo esc_attr($og_description) ?></textarea>
			    <p class="description" id="tagline-description">Required for Twitter cards.</p>
</td></tr><tr>
<th scope="row">
                <label for="shfbi_default_image">Image URL</label>
</th><td>
			    <div class="form-group image-uploader">
			    <input type="text" name="shfbi_default_image" class="regular-text" value="<?php echo esc_attr(get_option('shfbi_default_image')) ?>" />
			</div>
</td></tr><tr>
<th scope="row">				
	<?php submit_button() ?>
</th><td></td></tr></tbody></table>
The newly set image will not show right away! Go to the <a href="https://developers.facebook.com/tools/debug/sharing/?q=<?=get_site_url();?>" target="_blank">Facebook Debugger</a> and click "Scrape Again' to force Facebook to pull the latest version. For Twitter you can <a href="https://cards-dev.twitter.com/validator" target="_blank">Validate the Twitter card</a>.
</form>
		<?php
	}

}

function shfbi_add_action_links ( $links ) {
 $mylinks = array(
 '<a href="' . admin_url( 'options-general.php?page=shfbi' ) . '">Settings</a>',
 );
return array_merge( $mylinks, $links );
}

if (!function_exists( 'shfbi_register_settings')) {
	function shfbi_register_settings() {
		register_setting('shfbi', 'shfbi_default_image');
		register_setting('shfbi', 'shfbi_title');
		register_setting('shfbi', 'shfbi_description');
	}

}

if (!function_exists( 'shfbi_admin_scripts')) {
	function shfbi_admin_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_media();
		wp_enqueue_script( 'wp-media-uploader', plugin_dir_url( __FILE__ ) . 'wp_media_uploader.js', array( 'jquery' ), 1.0 ); 	}

}



if (!function_exists( 'shfbi_admin_init')) {
	function shfbi_admin_init() {
		shfbi_register_settings();
	}
}

add_action('wp_head', 'shfbi_wp_head');

if (is_admin()) {
	add_action('admin_menu', 'shfbi_admin_menu');
	add_action('admin_init', 'shfbi_admin_init');
	add_action('admin_print_scripts', 'shfbi_admin_scripts');
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'shfbi_add_action_links' );
}
