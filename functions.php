<?php

$functions_path = TEMPLATEPATH . '/functions/';
$widgets_path = TEMPLATEPATH . '/functions/widgets/';

add_action( 'after_setup_theme', 'shaken_setup' );

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * To override shaken_setup() in a child theme, add your own shaken_setup to your child theme's
 * functions.php file.
 *
 * @since 1.0
 * @alter 1.1.1
 */
function shaken_setup() {
	// Set the $content_width for things such as video embeds
	if ( ! isset( $content_width ) )
	$content_width = 550;
	
	// Theme support
		
		add_custom_background('shaken_custom_background_cb');
		
	// Actions
		
		/* Add your nav menus function to the 'init' action hook. */
		add_action( 'init', 'shaken_register_menus' );
		
		/* Add your sidebars function to the 'widgets_init' action hook. */
		add_action( 'widgets_init', 'shaken_register_sidebars' );
		
		/* Remove the Posts and Comments admin tabs */
		add_action( 'admin_menu', 'shaken_remove_admin_links' );
		
		/* Remove some built-in widgets */
		add_action('widgets_init','shaken_remove_widgets');
		
		/* Add custom Dashboard widgets */
		add_action('wp_dashboard_setup', 'shaken_dashboard_widgets');
		
	// Filters
		add_filter('pre_get_posts','shaken_refine_search');
		
	/* Make theme available for translation
	 * Translations can be filed in the /languages/ directory */
	load_theme_textdomain( 'shaken', TEMPLATEPATH . '/languages' );
	$locale = get_locale();
	$locale_file = TEMPLATEPATH . "/languages/$locale.php";
	if ( is_readable( $locale_file ) )
		require_once( $locale_file );
}

/** 
 * shaken_custom_background_cb()
 * Create a callback for custom backgrounds
 * Removes the old background so the user defined background can display.
 *
 * @since 1.0
**/
function shaken_custom_background_cb() {
	$background = get_background_image();
	$color = get_background_color();
	if ( ! $background && ! $color )
		return;
 
	$style = $color ? "background-color: #$color;" : '';
 
	if ( $background ) {
		$image = " background-image: url('$background');";
 
		$repeat = get_theme_mod( 'background_repeat', 'repeat' );
		if ( ! in_array( $repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) )
			$repeat = 'repeat';
		$repeat = " background-repeat: $repeat;";
 
		$position = get_theme_mod( 'background_position_x', 'left' );
		if ( ! in_array( $position, array( 'center', 'right', 'left' ) ) )
			$position = 'left';
		$position = " background-position: top $position;";
 
		$attachment = get_theme_mod( 'background_attachment', 'scroll' );
		if ( ! in_array( $attachment, array( 'fixed', 'scroll' ) ) )
			$attachment = 'scroll';
		$attachment = " background-attachment: $attachment;";
 
		$style .= $image . $repeat . $position . $attachment;
	}
?>
<style type="text/css">
body {background:none; <?php echo trim( $style ); ?> }
</style>
<?php }

// --------------  Register Sidebars -------------- 
function shaken_register_sidebars(){
	register_sidebar(array(
		'name'=> __('Sidebar'),
		'id' => 'page-sidebar',
		'description' => __('Displayed on the basic pages'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	));
}

// --------------  Register Menus -------------- 
function shaken_register_menus(){
	register_nav_menus( array(
		'main_menu' => __( 'Main Menu'),
	) );	
}

// smart jquery inclusion
function shaken_jquery(){
    if (!is_admin()) {
    	wp_enqueue_script('jquery');
    }
}
add_action( 'wp_enqueue_scripts', 'shaken_jquery' );

// Only display pages in searches
function shaken_refine_search($query) {
	if ($query->is_search) {
		$query->set('post_type', 'page');
	}
	return $query;
}

// Remove Posts and Comments tab in wp-admin
function shaken_remove_admin_links(){
	remove_menu_page('edit.php');
	remove_menu_page('edit-comments.php');
}

// Remove certain built-in widgets
function shaken_remove_widgets(){
	unregister_widget('WP_Widget_Calendar');
	unregister_widget('WP_Widget_Archives');
	unregister_widget('WP_Widget_Recent_Comments');
	unregister_widget('WP_Widget_Recent_Posts');
	unregister_widget('WP_Widget_Tag_Cloud');
	unregister_widget('WP_Widget_Categories');
}

// Title Tag Function
function title_tag() {
	if (is_home() || is_front_page()) {
		echo bloginfo('name');
	} elseif (is_404()) {
		_e('404 Not Found','shaken');
	} elseif (is_category()) {
		_e('Category:','shaken'); wp_title('');
	} elseif (is_tag()) {
		_e('Tag:','shaken'); wp_title('');
	} elseif (is_search()) {
		_e('Search Results','shaken');
	} elseif ( is_day() || is_month() || is_year() ) {
		_e('Archives:','shaken'); wp_title('');
	} else {
		echo wp_title('');
	}
}

// Add S&S RSS feed to dashboard
function shaken_rss_output(){
    echo '<div class="rss-widget">';
     
       wp_widget_rss_output(array(
            'url' => 'http://feeds.feedburner.com/shakenandstirredweb/MLnE',  //put your feed URL here
            'title' => 'Latest News from Shaken &amp; Stirred', // Your feed title
            'items' => 5, //how many posts to show
            'show_summary' => 1, // 0 = false and 1 = true 
            'show_author' => 0,
            'show_date' => 0
       ));
       
       echo "</div>";
}

function shaken_twitter_dash_output(){
    echo '<div class="text-widget">';
     
	echo '<p>Follow Shaken and Stirred on <strong><a href="http://twitter.com/shakenweb" target="_blank">Twitter (@shakenweb)</a></strong> to stay up to date with the latest theme updates and new releases. You can also <strong><a href="http://shakenandstirredweb.com" target="_blank">visit our website</a></strong> to read our Tips &amp; Tricks to get the most out of your theme. We hope you enjoy our theme!</p>';
       
    echo "</div>";
}

function shaken_dashboard_widgets(){
	// Add custom widgets
	wp_add_dashboard_widget( 'shaken-twitter', 'Stay Updated!', 'shaken_twitter_dash_output');
  	wp_add_dashboard_widget( 'shaken-rss', 'Latest News from Shaken &amp; Stirred', 'shaken_rss_output');
	
	// Remove built-in dashboard widgets
  	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
}

// --------------  Custom Functions --------------
require_once ($functions_path . 'invoice/invoice-init.php');
require_once ($functions_path . 'invoice/duplicate-invoice.php');
require_once ($functions_path . 'framework-init.php');

?>