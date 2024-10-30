<?php
/*
Plugin Name: Image Preloading
Plugin URI:  https://www.pandasilk.com/wordpress-image-preloading-plugin/
Description:  Similar to DNS prefetching, image preloading/ prefetching with JavaScript to get faster page loading experience.
Version: 1.0
Author: pandasilk
Author URI:  https://www.pandasilk.com/wordpress-image-preloading-plugin/
Text Domain: image-preloading
Domain Path: /languages
License: GPLv2 or later
*/

// Load text domain
function image_preloading_text_domain() {
	load_plugin_textdomain('image-preloading', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'image_preloading_text_domain' );

class ImagePreloading {
	private $image_preloading_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'image_preloading_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'image_preloading_page_init' ) );
	}

	public function image_preloading_add_plugin_page() {
		add_options_page(
			'Image Preloading', // page_title
			'Image Preloading', // menu_title
			'manage_options', // capability
			'image-preloading', // menu_slug
			array( $this, 'image_preloading_create_admin_page' ) // function
		);
	}

	public function image_preloading_create_admin_page() {
		$this->image_preloading_options = get_option( 'image_preloading_option_name' ); ?>

		<div class="wrap">
			<h2><?php _e('Image Preloading', 'image-preloading' ); ?></h2>
			<p> 
			<ul> 	
				<li>
				  <a href="https://www.pandasilk.com/wordpress-image-preloading-plugin/" target="_blank"><?php _e('Documents and Troubleshooting', 'image-preloading' ); ?>
				  </a>
				</li> 	
				<li>
				  <a href="https://wordpress.org/support/plugin/image-preloading" target="_blank" ><?php _e('Support Forum on WordPress.org', 'image-preloading' ); ?>
				  </a>
				</li>
			  </ul>  
			  </p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'image_preloading_option_group' );
					do_settings_sections( 'image-preloading-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function image_preloading_page_init() {
		register_setting(
			'image_preloading_option_group', // option_group
			'image_preloading_option_name', // option_name
			array( $this, 'image_preloading_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'image_preloading_setting_section', // id
			'Settings', // title
			array( $this, 'image_preloading_section_info' ), // callback
			'image-preloading-admin' // page
		);

		add_settings_field(
			'image_urls_0', // id
			'Image URLs', // title
			array( $this, 'image_urls_0_callback' ), // callback
			'image-preloading-admin', // page
			'image_preloading_setting_section' // section
		);
	}

	public function image_preloading_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['image_urls_0'] ) ) {
			$sanitary_values['image_urls_0'] = esc_textarea( $input['image_urls_0'] );
		}

		return $sanitary_values;
	}

	public function image_preloading_section_info() {
		
	}

	public function image_urls_0_callback() {
		printf(
			'<textarea class="large-text" rows="5" name="image_preloading_option_name[image_urls_0]" id="image_urls_0">%s</textarea><br><strong>One URL per line.</strong> ',
			isset( $this->image_preloading_options['image_urls_0'] ) ? esc_attr( $this->image_preloading_options['image_urls_0']) : ''
		);
	}

}
if ( is_admin() )
	$image_preloading = new ImagePreloading();

// Add settings link on plugins page
function image_preloading_action_links($links) { 
	$settings_link = '<a href="options-general.php?page=image-preloading">'.__('Settings', 'image-preloading' ).'</a>'; 
	array_unshift($links, $settings_link); 
	return $links; 
}

$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'image_preloading_action_links' );

//plugin script function
function image_preloading_scripts() {
	//Retrieve options value
	$image_preloading_options = get_option( 'image_preloading_option_name' ); // Array of All Options
	$image_urls_0 = $image_preloading_options['image_urls_0'];  // Image URLs
	$urls = explode("\n", $image_urls_0); // Break a string into an array: explode(separator,string,limit) 

	if (!empty($urls)) {
		$urls = array_map('esc_url', $urls);  
		foreach ($urls as $imageurl) {
			$result .= '"'.$imageurl.'",'."\n";
		}
	}
		?>
	<script type="text/javascript">
	/* <![CDATA[ */
	var images = new Array()
	function preload() {
		for (i = 0; i < preload.arguments.length; i++) {
			images[i] = new Image()
			images[i].src = preload.arguments[i]
		}
	}
	preload(<?php echo $result;?>)
	/* ]]> */
	</script>
		<?php
}
add_action('wp_footer', 'image_preloading_scripts', 100);