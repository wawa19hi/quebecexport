<?php

defined('ABSPATH') or die;

/*
 *
 * Require the framework class before doing anything else, so we can use the defined urls and dirs
 *
 */
require_once( dirname( __FILE__ ) . '/options/options.php' );

/*
 * 
 * Add support tab
 *
 */
if ( ! defined('MTS_THEME_WHITE_LABEL') || ! MTS_THEME_WHITE_LABEL ) {
	require_once( dirname( __FILE__ ) . '/options/support.php' );
	$mts_options_tab_support = MTS_Options_Tab_Support::get_instance();
}

/*
 *
 * Custom function for filtering the sections array given by theme, good for child themes to override or add to the sections.
 * Simply include this function in the child themes functions.php file.
 *
 * NOTE: the defined constansts for urls, and dir will NOT be available at this point in a child theme, so you must use
 * get_template_directory_uri() if you want to use any of the built in icons
 *
 */
function add_another_section($sections){

	//$sections = array();
	$sections[] = array(
		'title' => __('A Section added by hook', 'bridge' ),
		'desc' => '<p class="description">' . __('This is a section created by adding a filter to the sections array, great to allow child themes, to add/remove sections from the options.', 'bridge' ) . '</p>',
		//all the glyphicons are included in the options folder, so you can hook into them, or link to your own custom ones.
		//You dont have to though, leave it blank for default.
		'icon' => trailingslashit(get_template_directory_uri()).'options/img/glyphicons/glyphicons_062_attach.png',
		//Lets leave this as a blank section, no options just some intro text set above.
		'fields' => array()
	);

	return $sections;

}//function
//add_filter('nhp-opts-sections-twenty_eleven', 'add_another_section');


/*
 *
 * Custom function for filtering the args array given by theme, good for child themes to override or add to the args array.
 *
 */
function change_framework_args($args){

	//$args['dev_mode'] = false;

	return $args;

}//function
//add_filter('nhp-opts-args-twenty_eleven', 'change_framework_args');

/*
 * This is the meat of creating the optons page
 *
 * Override some of the default values, uncomment the args and change the values
 * - no $args are required, but there there to be over ridden if needed.
 *
 *
 */

function setup_framework_options(){
	$args = array();

	//Set it to dev mode to view the class settings/info in the form - default is false
	$args['dev_mode'] = false;
	//Remove the default stylesheet? make sure you enqueue another one all the page will look whack!
	//$args['stylesheet_override'] = true;

	//Add HTML before the form
	//$args['intro_text'] = __('<p>This is the HTML which can be displayed before the form, it isnt required, but more info is always better. Anything goes in terms of markup here, any HTML.</p>', 'bridge' );

	if ( ! MTS_THEME_WHITE_LABEL ) {
		//Setup custom links in the footer for share icons
		$args['share_icons']['twitter'] = array(
			'link' => 'http://twitter.com/mythemeshopteam',
			'title' => __( 'Follow Us on Twitter', 'bridge' ),
			'img' => 'fa fa-twitter-square'
		);
		$args['share_icons']['facebook'] = array(
			'link' => 'http://www.facebook.com/mythemeshop',
			'title' => __( 'Like us on Facebook', 'bridge' ),
			'img' => 'fa fa-facebook-square'
		);
	}

	//Choose to disable the import/export feature
	//$args['show_import_export'] = false;

	//Choose a custom option name for your theme options, the default is the theme name in lowercase with spaces replaced by underscores
	$args['opt_name'] = MTS_THEME_NAME;

	//Custom menu icon
	//$args['menu_icon'] = '';

	//Custom menu title for options page - default is "Options"
	$args['menu_title'] = __('Theme Options', 'bridge' );

	//Custom Page Title for options page - default is "Options"
	$args['page_title'] = __('Theme Options', 'bridge' );

	//Custom page slug for options page (wp-admin/themes.php?page=***) - default is "nhp_theme_options"
	$args['page_slug'] = 'theme_options';

	//Custom page capability - default is set to "manage_options"
	//$args['page_cap'] = 'manage_options';

	//page type - "menu" (adds a top menu section) or "submenu" (adds a submenu) - default is set to "menu"
	//$args['page_type'] = 'submenu';

	//parent menu - default is set to "themes.php" (Appearance)
	//the list of available parent menus is available here: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
	//$args['page_parent'] = 'themes.php';

	//custom page location - default 100 - must be unique or will override other items
	$args['page_position'] = 62;

	//Custom page icon class (used to override the page icon next to heading)
	//$args['page_icon'] = 'icon-themes';

	if ( ! MTS_THEME_WHITE_LABEL ) {
		//Set ANY custom page help tabs - displayed using the new help tab API, show in order of definition
		$args['help_tabs'][] = array(
			'id' => 'nhp-opts-1',
			'title' => __('Support', 'bridge' ),
			'content' => '<p>' . sprintf( __('If you are facing any problem with our theme or theme option panel, head over to our %s.', 'bridge' ), '<a href="http://community.mythemeshop.com/">'. __( 'Support Forums', 'bridge' ) . '</a>' ) . '</p>'
		);
		$args['help_tabs'][] = array(
			'id' => 'nhp-opts-2',
			'title' => __('Earn Money', 'bridge' ),
			'content' => '<p>' . sprintf( __('Earn 70%% commision on every sale by refering your friends and readers. Join our %s.', 'bridge' ), '<a href="http://mythemeshop.com/affiliate-program/">' . __( 'Affiliate Program', 'bridge' ) . '</a>' ) . '</p>'
		);
	}

	//Set the Help Sidebar for the options page - no sidebar by default
	//$args['help_sidebar'] = __('<p>This is the sidebar content, HTML is allowed.</p>', 'bridge' );

	$mts_patterns = array(
		'nobg' => array('img' => NHP_OPTIONS_URL.'img/patterns/nobg.png'),
		'pattern38' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern38.png'),
		'pattern0' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern0.png'),
		'pattern1' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern1.png'),
		'pattern2' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern2.png'),
		'pattern3' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern3.png'),
		'pattern4' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern4.png'),
		'pattern5' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern5.png'),
		'pattern6' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern6.png'),
		'pattern7' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern7.png'),
		'pattern8' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern8.png'),
		'pattern9' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern9.png'),
		'pattern10' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern10.png'),
		'pattern11' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern11.png'),
		'pattern12' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern12.png'),
		'pattern13' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern13.png'),
		'pattern14' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern14.png'),
		'pattern15' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern15.png'),
		'pattern16' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern16.png'),
		'pattern17' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern17.png'),
		'pattern18' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern18.png'),
		'pattern19' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern19.png'),
		'pattern20' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern20.png'),
		'pattern21' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern21.png'),
		'pattern22' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern22.png'),
		'pattern23' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern23.png'),
		'pattern24' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern24.png'),
		'pattern25' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern25.png'),
		'pattern26' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern26.png'),
		'pattern27' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern27.png'),
		'pattern28' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern28.png'),
		'pattern29' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern29.png'),
		'pattern30' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern30.png'),
		'pattern31' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern31.png'),
		'pattern32' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern32.png'),
		'pattern33' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern33.png'),
		'pattern34' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern34.png'),
		'pattern35' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern35.png'),
		'pattern36' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern36.png'),
		'pattern37' => array('img' => NHP_OPTIONS_URL.'img/patterns/pattern37.png'),
		'hbg' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg.png'),
		'hbg2' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg2.png'),
		'hbg3' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg3.png'),
		'hbg4' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg4.png'),
		'hbg5' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg5.png'),
		'hbg6' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg6.png'),
		'hbg7' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg7.png'),
		'hbg8' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg8.png'),
		'hbg9' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg9.png'),
		'hbg10' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg10.png'),
		'hbg11' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg11.png'),
		'hbg12' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg12.png'),
		'hbg13' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg13.png'),
		'hbg14' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg14.png'),
		'hbg15' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg15.png'),
		'hbg16' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg16.png'),
		'hbg17' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg17.png'),
		'hbg18' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg18.png'),
		'hbg19' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg19.png'),
		'hbg20' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg20.png'),
		'hbg21' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg21.png'),
		'hbg22' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg22.png'),
		'hbg23' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg23.png'),
		'hbg24' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg24.png'),
		'hbg25' => array('img' => NHP_OPTIONS_URL.'img/patterns/hbg25.png')
	);

	$sections = array();

	$sections[] = array(
		'icon' => 'fa fa-cogs',
		'title' => __('General Settings', 'bridge' ),
		'desc' => '<p class="description">' . __('This tab contains common setting options which will be applied to the whole theme.', 'bridge' ) . '</p>',
		'fields' => array(
			array(
				'id' => 'mts_logo',
				'type' => 'upload',
				'title' => __('Logo Image', 'bridge' ),
				'sub_desc' => wp_kses( __('Upload your logo using the Upload Button or insert image URL. Recommended Size <strong>138X36px</strong>)', 'bridge' ), array( 'strong' => array() ) ),
				'return' => 'id'
			),
			array(
				'id' => 'mts_favicon',
				'type' => 'upload',
				'title' => __('Favicon', 'bridge' ),
				'sub_desc' => sprintf( __('Upload a %s image that will represent your website\'s favicon.', 'bridge' ), '<strong>32 x 32 px</strong>' ),
				'return' => 'id'
			),
			array(
				'id' => 'mts_touch_icon',
				'type' => 'upload',
				'title' => __('Touch icon', 'bridge' ),
				'sub_desc' => sprintf( __('Upload a %s image that will represent your website\'s touch icon for iOS 2.0+ and Android 2.1+ devices.', 'bridge' ), '<strong>152 x 152 px</strong>' ),
				'return' => 'id'
			),
			array(
				'id' => 'mts_metro_icon',
				'type' => 'upload',
				'title' => __('Metro icon', 'bridge' ),
				'sub_desc' => sprintf( __('Upload a %s image that will represent your website\'s IE 10 Metro tile icon.', 'bridge' ), '<strong>144 x 144 px</strong>' ),
				'return' => 'id'
			),
			array(
				'id' => 'mts_twitter_username',
				'type' => 'text',
				'title' => __('Twitter Username', 'bridge' ),
				'sub_desc' => __('Enter your Username here.', 'bridge' ),
			),
			array(
				'id' => 'mts_feedburner',
				'type' => 'text',
				'title' => __('FeedBurner URL', 'bridge' ),
				'sub_desc' => sprintf( __('Enter your FeedBurner\'s URL here, ex: %s and your main feed (http://example.com/feed) will get redirected to the FeedBurner ID entered here.)', 'bridge' ), '<strong>http://feeds.feedburner.com/mythemeshop</strong>' ),
				'validate' => 'url'
			),
			array(
				'id' => 'mts_header_code',
				'type' => 'textarea',
				'title' => __('Header Code', 'bridge' ),
				'sub_desc' => wp_kses( __('Enter the code which you need to place <strong>before closing &lt;/head&gt; tag</strong>. (ex: Google Webmaster Tools verification, Bing Webmaster Center, BuySellAds Script, Alexa verification etc.)', 'bridge' ), array( 'strong' => array() ) )
			),
			array(
				'id' => 'mts_analytics_code',
				'type' => 'textarea',
				'title' => __('Footer Code', 'bridge' ),
				'sub_desc' => wp_kses( __('Enter the codes which you need to place in your footer. <strong>(ex: Google Analytics, Clicky, STATCOUNTER, Woopra, Histats, etc.)</strong>.', 'bridge' ), array( 'strong' => array() ) )
			),
			array(
				'id' => 'mts_pagenavigation_type',
				'type' => 'radio',
				'title' => __('Pagination Type', 'bridge' ),
				'sub_desc' => __('Select pagination type.', 'bridge' ),
				'options' => array(
					'0'=> __('Next / Previous', 'bridge' ),
					'1' => __('Default Numbered (1 2 3 4...)', 'bridge' ),
					'2' => __( 'AJAX (Load More Button)', 'bridge' ),
					'3' => __( 'AJAX (Auto Infinite Scroll)', 'bridge' )
				),
				'std' => '1'
			),
			array(
				'id' => 'mts_ajax_search',
				'type' => 'button_set',
				'title' => __('AJAX Quick search', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('Enable or disable search results appearing instantly below the search form', 'bridge' ),
				'std' => '0'
			),
			array(
				'id' => 'mts_responsive',
				'type' => 'button_set',
				'title' => __('Responsiveness', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('MyThemeShop themes are responsive, which means they adapt to tablet and mobile devices, ensuring that your content is always displayed beautifully no matter what device visitors are using. Enable or disable responsiveness using this option.', 'bridge' ),
				'std' => '1'
			),
			array(
				'id' => 'mts_rtl',
				'type' => 'button_set',
				'title' => __('Right To Left Language Support', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('Enable this option for right-to-left sites.', 'bridge' ),
				'std' => '0'
			),
			array(
				'id' => 'mts_shop_products',
				'type' => 'text',
				'title' => __('No. of Products', 'bridge' ),
				'sub_desc' => __('Enter the total number of products which you want to show on shop page (WooCommerce plugin must be enabled).', 'bridge' ),
				'validate' => 'numeric',
				'std' => '9',
				'class' => 'small-text'
			),
		)
	);
	$sections[] = array(
		'icon' => 'fa fa-bolt',
		'title' => __('Performance', 'bridge' ),
		'desc' => '<p class="description">' . __('This tab contains performance-related options which can help speed up your website.', 'bridge' ) . '</p>',
		'fields' => array(
			array(
				'id' => 'mts_prefetching',
				'type' => 'button_set',
				'title' => __('Prefetching', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('Enable or disable prefetching. If user is on homepage, then single page will load faster and if user is on single page, homepage will load faster in modern browsers.', 'bridge' ),
				'std' => '0'
			),
			array(
				'id' => 'mts_lazy_load',
				'type' => 'button_set_hide_below',
				'title' => __('Lazy Load', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('Delay loading of images outside of viewport, until user scrolls to them.', 'bridge' ),
				'std' => '0',
				'args' => array('hide' => 2)
				),
				array(
					'id' => 'mts_lazy_load_thumbs',
					'type' => 'button_set',
					'title' => __('Lazy load featured images', 'bridge' ),
					'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
					'sub_desc' => __('Enable or disable Lazy load of featured images across site.', 'bridge' ),
					'std' => '0'
				),
				array(
					'id' => 'mts_lazy_load_content',
					'type' => 'button_set',
					'title' => __('Lazy load post content images', 'bridge' ),
					'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
					'sub_desc' => __('Enable or disable Lazy load of images inside post/page content.', 'bridge' ),
					'std' => '0'
			),
			array(
				'id' => 'mts_async_js',
				'type' => 'button_set',
				'title' => __('Async JavaScript', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => sprintf( __('Add %s attribute to script tags to improve page download speed.', 'bridge' ), '<code>async</code>' ),
				'std' => '1',
			),
			array(
				'id' => 'mts_remove_ver_params',
				'type' => 'button_set',
				'title' => __('Remove ver parameters', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => sprintf( __('Remove %s parameter from CSS and JS file calls. It may improve speed in some browsers which do not cache files having the parameter.', 'bridge' ), '<code>ver</code>' ),
				'std' => '1',
			),
			array(
				'id' => 'mts_optimize_wc',
				'type' => 'button_set',
				'title' => __('Optimize WooCommerce scripts', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('Load WooCommerce scripts and styles only on WooCommerce pages (WooCommerce plugin must be enabled).', 'bridge' ),
				'std' => '1'
			),
			'cache_message' => array(
				'id' => 'mts_cache_message',
				'type' => 'info',
				'title' => __('Use Cache', 'bridge' ),
				// Translators: %1$s = popup link to W3 Total Cache, %2$s = popup link to WP Super Cache
				'desc' => sprintf(
					__('A cache plugin can increase page download speed dramatically. We recommend using %1$s or %2$s.', 'bridge' ),
					'<a href="https://community.mythemeshop.com/tutorials/article/8-make-your-website-load-faster-using-w3-total-cache-plugin/" target="_blank" title="W3 Total Cache">W3 Total Cache</a>',
					'<a href="'.admin_url( 'plugin-install.php?tab=plugin-information&plugin=wp-super-cache&TB_iframe=true&width=772&height=574' ).'" class="thickbox" title="WP Super Cache">WP Super Cache</a>'
				),
			),
		)
	);

	// Hide cache message on multisite or if a chache plugin is active already
	if ( is_multisite() || strstr( join( ';', get_option( 'active_plugins' ) ), 'cache' ) ) {
		unset( $sections[1]['fields']['cache_message'] );
	}

	$sections[] = array(
		'icon' => 'fa fa-adjust',
		'title' => __('Styling Options', 'bridge' ),
		'desc' => '<p class="description">' . __('Control the visual appearance of your theme, such as colors, layout and patterns, from here.', 'bridge' ) . '</p>',
		'fields' => array(
			array(
				'id' => 'mts_color_scheme',
				'type' => 'color',
				'title' => __('Color Scheme', 'bridge' ),
				'sub_desc' => __('The theme comes with unlimited color schemes for your theme\'s styling.', 'bridge' ),
				'std' => '#68c573'
			),
			array(
				'id' => 'mts_layout',
				'type' => 'radio_img',
				'title' => __('Layout Style', 'bridge' ),
				'sub_desc' => wp_kses( __('Choose the <strong>default sidebar position</strong> for your site. The position of the sidebar for individual posts can be set in the post editor.', 'bridge' ), array( 'strong' => array() ) ),
				'options' => array(
					'cslayout' => array('img' => NHP_OPTIONS_URL.'img/layouts/cs.png'),
					'sclayout' => array('img' => NHP_OPTIONS_URL.'img/layouts/sc.png')
				),
				'std' => 'cslayout'
			),
			array(
				'id' => 'mts_background',
				'type' => 'background',
				'title' => __('Site Background', 'bridge' ),
				'sub_desc' => __('Set background color, pattern and image from here.', 'bridge' ),
				'options' => array(
					'color'		 => '',
					'image_pattern' => $mts_patterns,
					'image_upload'  => '',
					'repeat'		=> array(),
					'attachment'	=> array(),
					'position'	=> array(),
					'size'		=> array(),
					'gradient'	=> '',
					'parallax'	=> array(),
				),
				'std' => array(
					'color'		 => '#81ee8e',
					'use'		 => 'gradient',
					'image_pattern' => 'nobg',
					'image_upload'  => '',
					'repeat'		=> 'repeat',
					'attachment'	=> 'scroll',
					'position'	=> 'left top',
					'size'		=> 'cover',
					'gradient'	=> array('from' => '#00c9fd', 'to' => '#81ee8e', 'direction' => 'horizontal' ),
					'parallax'	=> '0',
				)
			),
			array(
				'id' => 'mts_custom_css',
				'type' => 'textarea',
				'title' => __('Custom CSS', 'bridge' ),
				'sub_desc' => __('You can enter custom CSS code here to further customize your theme. This will override the default CSS used on your site.', 'bridge' )
			),
			array(
				'id' => 'mts_lightbox',
				'type' => 'button_set',
				'title' => __('Lightbox', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('A lightbox is a stylized pop-up that allows your visitors to view larger versions of images without leaving the current page. You can enable or disable the lightbox here.', 'bridge' ),
				'std' => '0'
			),
		)
	);
	$sections[] = array(
		'icon' => 'fa fa-credit-card',
		'title' => __('Header', 'bridge' ),
		'desc' => '<p class="description">' . __('From here, you can control the elements of header section.', 'bridge' ) . '</p>',
		'fields' => array(
			array(
				'id' => 'mts_sticky_nav',
				'type' => 'button_set',
				'title' => __('Floating Navigation Menu', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => sprintf( __('Use this button to enable %s.', 'bridge' ), '<strong>' . __('Floating Navigation Menu', 'bridge' ) . '</strong>' ),
				'std' => '0'
			),
			array(
			    'id'     => 'mts_header_layout',
			    'type'   => 'layout2',
			    'title' => __('Header Layout', 'bridge' ),
			    'sub_desc' => __('Customize the look of header', 'bridge' ),
			    'options'  => array(
			        'enabled'  => array(
			        	'bottom-header'   => array(
			                'label'     => __('Menu Section', 'bridge' ),
			                'subfields' => array()
			            ),
			            'top-header'   => array(
			                'label'     => __('Logo Section', 'bridge' ),
			                'subfields' => array()
			            )
			        ),
			        'disabled' => array()
			    ),
			    'std' => array(
			        'enabled'  => array(
			        	'bottom-header'   => array(
			                'label'     => __('Menu Section', 'bridge' ),
			                'subfields' => array()
			            ),
			            'top-header'   => array(
			                'label'     => __('Logo Section', 'bridge' ),
			                'subfields' => array()
			            )
			        ),
			        'disabled' => array()
			    ),
			),
			array(
				'id' => 'mts_show_primary_nav',
				'type' => 'button_set',
				'title' => __('Show Primary Menu', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => sprintf( __('Use this button to enable %s.', 'bridge' ), '<strong>' . __( 'Primary Navigation Menu', 'bridge' ) . '</strong>' ),
				'std' => '1'
			),
			array(
				'id' => 'mts_header_search',
				'type' => 'button_set',
				'title' => __('Show Header Search', 'bridge' ), 
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => sprintf( __('Use this button to enable %s.', 'bridge' ), '<strong>' . __( 'Header Search', 'bridge' ) . '</strong>' ),
				'std' => '1'
			),
			array(
				'id' => 'mts_header_section2',
				'type' => 'button_set',
				'title' => __('Show Logo', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => wp_kses( __('Use this button to Show or Hide the <strong>Logo</strong> completely.', 'bridge' ), array( 'strong' => array() ) ),
				'std' => '1'
			),
			array(
				'id' => 'mts_social_icon_head',
				'type' => 'button_set_hide_below',
				'title' => __('Show Social Icons in Header','bridge'),
				'sub_desc' => sprintf( __('Use this button to show %s.', 'bridge' ), '<strong>' . __( 'Header Social Icons', 'bridge' ) . '</strong>' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'std' => '1',
				'args' => array('hide' => '1')
			),
			array(
			 	'id' => 'mts_header_social',
			 	'title' => __('Add Social Icons','bridge'), 
			 	'sub_desc' => __( 'Add Social Media icons in header.', 'bridge' ),
			 	'type' => 'group',
			 	'groupname' => __('Header Icons','bridge'), // Group name
			 	'subfields' => array(
					array(
						'id' => 'mts_header_icon_title',
						'type' => 'text',
						'title' => __('Title', 'bridge'), 
					),
					array(
						'id' => 'mts_header_icon',
						'type' => 'icon_select',
						'title' => __('Icon', 'bridge')
					),
					array(
						'id' => 'mts_header_icon_link',
						'type' => 'text',
						'title' => __('URL', 'bridge'), 
					),
					array(
						'id' => 'mts_header_icon_color',
						'type' => 'color',
						'title' => __('Color', 'bridge'),
						'std' => '#ffffff'
					)
				),
				'std' => array(
					'facebook' => array(
						'group_title' => 'Facebook',
						'group_sort' => '1',
						'mts_header_icon_title' => 'Facebook',
						'mts_header_icon' => 'facebook',
						'mts_header_icon_link' => '#',
						'mts_header_icon_color' => '#ffffff'
					),
					'twitter' => array(
						'group_title' => 'Twitter',
						'group_sort' => '2',
						'mts_header_icon_title' => 'Twitter',
						'mts_header_icon' => 'twitter',
						'mts_header_icon_link' => '#',
						'mts_header_icon_color' => '#ffffff'
					),
					'instagram' => array(
						'group_title' => 'Instagram',
						'group_sort' => '3',
						'mts_header_icon_title' => 'Instagram',
						'mts_header_icon' => 'instagram',
						'mts_header_icon_link' => '#',
						'mts_header_icon_color' => '#ffffff'
					),
					'youtube' => array(
						'group_title' => 'You Tube',
						'group_sort' => '4',
						'mts_header_icon_title' => 'You Tube',
						'mts_header_icon' => 'youtube',
						'mts_header_icon_link' => '#',
						'mts_header_icon_color' => '#ffffff'
					)
				)
			),
			array(
				'id' => 'mts_top_header_background',
				'type' => 'background',
				'title' => __('Logo Section Background', 'bridge' ),
				'sub_desc' => __('Set top header background color, pattern and image from here.', 'bridge' ),
				'options' => array(
					'color'		 => '',
					'image_pattern' => $mts_patterns,
					'image_upload'  => '',
					'repeat'		=> array(),
					'attachment'	=> array(),
					'position'	=> array(),
					'size'		=> array(),
					'gradient'	=> '',
					'parallax'	=> array(),
				),
				'std' => array(
					'color'		 => '#272d3a',
					'use'		 => 'pattern',
					'image_pattern' => 'nobg',
					'image_upload'  => '',
					'repeat'		=> 'repeat',
					'attachment'	=> 'scroll',
					'position'	=> 'left top',
					'size'		=> 'cover',
					'gradient'	=> array('from' => '#ffffff', 'to' => '#000000', 'direction' => 'horizontal' ),
					'parallax'	=> '0',
				)
			),
			array(
				'id' => 'mts_primary_nav_background',
				'type' => 'color',
				'title' => __('Primary Navigation Background', 'bridge' ),
				'sub_desc' => __('Choose background color for the primary navigation menu.', 'bridge' ),
				'std' => '#20252f'
			),
		)
	);
	$sections[] = array(
		'icon' => 'fa fa-table',
		'title' => __('Footer', 'bridge' ),
		'desc' => '<p class="description">' . __('From here, you can control the elements of Footer section.', 'bridge' ) . '</p>',
		'fields' => array(
			array(
				'id' => 'mts_first_footer',
				'type' => 'button_set_hide_below',
				'title' => __('Footer Widget', 'bridge' ),
				'sub_desc' => __('Enable or disable footer widget with this option.', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'std' => '0'
			),
			array(
				'id' => 'mts_first_footer_num',
				'type' => 'button_set',
				'class' => 'green',
				'title' => __('Footer Layout', 'bridge' ),
				'sub_desc' => wp_kses( __('Choose the number of widget areas in the <strong>footer</strong>', 'bridge' ), array( 'strong' => array() ) ),
				'options' => array(
					'3' => __( '3 Widgets', 'bridge' ),
					'4' => __( '4 Widgets', 'bridge' ),
				),
				'std' => '4'
			),
			array(
				'id' => 'mts_show_footer_nav',
				'type' => 'button_set',
				'title' => __('Show Footer Menu', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => sprintf( __('Use this button to enable %s.', 'bridge' ), '<strong>' . __( 'Footer Navigation Menu', 'bridge' ) . '</strong>' ),
				'std' => '1'
			),
			array(
				'id' => 'mts_copyrights',
				'type' => 'textarea',
				'title' => __('Copyrights Text', 'bridge' ),
				'sub_desc' => __( 'You can change or remove our link from footer and use your own custom text.', 'bridge' ) . ( MTS_THEME_WHITE_LABEL ? '' : wp_kses( __('(You can also use your affiliate link to <strong>earn 70% of sales</strong>. Ex: <a href="https://mythemeshop.com/go/aff/aff" target="_blank">https://mythemeshop.com/?ref=username</a>)', 'bridge' ), array( 'strong' => array(), 'a' => array( 'href' => array(), 'target' => array() ) ) ) ),
				'std' => MTS_THEME_WHITE_LABEL ? null : sprintf( __( 'Theme by %s', 'bridge' ), '<a href="http://mythemeshop.com/">MyThemeShop</a>' )
			),
			array(
				'id' => 'mts_subscribe_box_bg',
				'type' => 'background',
				'title' => __('Subscribe Area Background', 'bridge' ),
				'sub_desc' => __('Set primary menu background color, pattern and image from here.', 'bridge' ),
				'options' => array(
					'color'		 => '',
					'image_pattern' => $mts_patterns,
					'image_upload'  => '',
					'repeat'		=> array(),
					'attachment'	=> array(),
					'position'	=> array(),
					'size'		=> array(),
					'gradient'	=> '',
					'parallax'	=> array(),
				),
				'std' => array(
					'color'		 => '#272d3b',
					'use'		 => 'pattern',
					'image_pattern' => 'nobg',
					'image_upload'  => '',
					'repeat'		=> 'repeat',
					'attachment'	=> 'scroll',
					'position'	=> 'left top',
					'size'		=> 'cover',
					'gradient'	=> array('from' => '#ffffff', 'to' => '#000000', 'direction' => 'horizontal' ),
					'parallax'	=> '0',
				)
			),
			array(
				'id' => 'mts_footer_background',
				'type' => 'background',
				'title' => __('Footer Background', 'bridge' ),
				'sub_desc' => __('Set footer background color, pattern and image from here.', 'bridge' ),
				'options' => array(
					'color'		 => '',
					'image_pattern' => $mts_patterns,
					'image_upload'  => '',
					'repeat'		=> array(),
					'attachment'	=> array(),
					'position'	=> array(),
					'size'		=> array(),
					'gradient'	=> '',
					'parallax'	=> array(),
				),
				'std' => array(
					'color'		 => '#20252f',
					'use'		 => 'pattern',
					'image_pattern' => 'nobg',
					'image_upload'  => '',
					'repeat'		=> 'repeat',
					'attachment'	=> 'scroll',
					'position'	=> 'left top',
					'size'		=> 'cover',
					'gradient'	=> array('from' => '#ffffff', 'to' => '#000000', 'direction' => 'horizontal' ),
					'parallax'	=> '0',
				)
			),
		)
	);

	$sections[] = array(
		'icon' => '',
		'title' => __('Featured Area', 'bridge'),
		'desc' => '<p class="description">' . __('Control settings related to Featured Area.', 'bridge' ) . '</p>',
		'fields' => array(
			array(
				'id' => 'mts_featured_post',
				'type' => 'button_set_hide_below',
				'title' => __('Featured Area', 'bridge' ), 
				'options' => array('0' => __('Off','bridge'),'1' => __('On','bridge')),
				'sub_desc' => wp_kses( __('Enable or Disable <strong>Featured Area</strong> section with this button. Featured area will show 4 recent articles from the selected categories.', 'bridge' ), array( 'strong' => array() ) ),
				'std' => '0',
	            'args' => array('hide' => 1)
			),
			array(
				'id' => 'mts_featured_post_cat',
				'type' => 'cats_multi_select',
				'title' => __('Featured Category(s)', 'bridge' ), 
				'sub_desc' => wp_kses( __('Select a category from the drop-down menu, latest articles from this category will be shown in <strong>Featured Area</strong>.', 'bridge' ), array( 'strong' => array() ) ),
			),			
		),
	);

	$sections[] = array(
		'icon' => '',
		'title' => __('Secondary Navigation', 'bridge'),
		'desc' => '<p class="description">' . __('Control settings related to Secondary Navigation.', 'bridge' ) . '</p>',
		'fields' => array(
			array(
				'id' => 'mts_show_secondary_nav',
				'type' => 'button_set',
				'title' => __('Show secondary menu', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => sprintf( __('Use this button to enable %s.', 'bridge' ), '<strong>' . __( 'Secondary Navigation Menu', 'bridge' ) . '</strong>' ),
				'std' => '1'
			),
			array(
				'id' => 'mts_secondary_menu_background',
				'type' => 'background',
				'title' => __('Secondary Menu Background', 'bridge' ),
				'sub_desc' => __('Set secondary menu background color, pattern and image from here.', 'bridge' ),
				'options' => array(
					'color'		 => '',
					'image_pattern' => $mts_patterns,
					'image_upload'  => '',
					'repeat'		=> array(),
					'attachment'	=> array(),
					'position'	=> array(),
					'size'		=> array(),
					'gradient'	=> '',
					'parallax'	=> array(),
				),
				'std' => array(
					'color'		 => '#ededed',
					'use'		 => 'pattern',
					'image_pattern' => 'nobg',
					'image_upload'  => '',
					'repeat'		=> 'repeat',
					'attachment'	=> 'scroll',
					'position'	=> 'left top',
					'size'		=> 'cover',
					'gradient'	=> array('from' => '#ffffff', 'to' => '#000000', 'direction' => 'horizontal' ),
					'parallax'	=> '0',
				)
			),
		),
	);

	$sections[] = array(
		'icon' => '',
		'title' => __('Featured Categories', 'bridge' ),
		'desc' => '<p class="description">' . __('From here, you can control the elements of the homepage.', 'bridge' ) . '</p>',
		'fields' => array(
			
			array(
				'id' => 'mts_featured_categories',
				'type' => 'group',
				'title'	 => __('Featured Categories', 'bridge' ),
				'sub_desc'  => __('Select categories appearing on the homepage.', 'bridge' ),
				'groupname' => __('Section', 'bridge' ), // Group name
				'subfields' =>
					array(
						array(
							'id' => 'mts_featured_category',
							'type' => 'cats_select',
							'title' => __('Category', 'bridge' ),
							'sub_desc' => __('Select a category or the latest posts for this section', 'bridge' ),
							'std' => 'latest',
							'args' => array('include_latest' => 1, 'hide_empty' => 0),
						),
						array(
							'id' => 'mts_featured_category_postsnum',
							'type' => 'text',
							'class' => 'small-text',
							'title' => __('Number of posts', 'bridge' ),
							'sub_desc' => __('Enter the number of posts to show in this section.', 'bridge' ),
							'std' => '3',
							'args' => array('type' => 'number')
						),
				),
				'std' => array(
					'1' => array(
						'group_title' => '',
						'group_sort' => '1',
						'mts_featured_category' => 'latest',
						'mts_featured_category_postsnum' => get_option('posts_per_page')
					)
				)
			),
			array(
				'id' => 'mts_home_meta_info_enable',
				'type' => 'multi_checkbox',
				'title' => __('HomePage Post Meta Info', 'bridge' ),
				'sub_desc' => __('Organize how you want the post meta info to appear on the homepage', 'bridge' ),
				'options' => array(
					'author' => __('Enable Author Avatar & Name', 'bridge' ), 
					'time'=> __('Enable Time/Date', 'bridge' ), 
					'comment' => __('Enable Comments', 'bridge' ), 
					'category'=>__('Enable Category', 'bridge' ),
				),
				'std' => array(
					'author'=> '1',
					'time'=> '1',
					'comment'=> '1',
					'category'=>'1',
				)
			),
			array(
				'id' => 'mts_like_dislike',
				'type' => 'button_set',
				'title' => __('Enable Like/Dislike', 'bridge'), 
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('Use this button to enable Like &amp; Dislike features for posts.', 'bridge'),
				'std' => '1'
			),
		)
	);

	$sections[] = array(
		'icon' => 'fa fa-file-text',
		'title' => __('Single Posts', 'bridge' ),
		'desc' => '<p class="description">' . __('From here, you can control the appearance and functionality of your single posts page.', 'bridge' ) . '</p>',
		'fields' => array(
			array(
				'id' => 'mts_single_post_category',
				'type' => 'button_set',
				'title' => __('Show or Hide Category ', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('You can enable or disable single post category from this option', 'bridge' ),
				'std' => '1'
			),
			array(
				'id' => 'mts_single_featured_image',
				'type' => 'button_set',
				'title' => __('Show Featured Image', 'bridge' ), 
				'sub_desc' => __('Use this button to show Featured Image on Single Post', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'std' => '1'
			),
			array(
				'id' => 'mts_single_meta_info_enable',
				'type' => 'multi_checkbox',
				'title' => __('Single Post Meta Info', 'bridge' ),
				'sub_desc' => __('Organize how you want the post meta info to appear on the single post', 'bridge' ),
				'options' => array(
					'author' => __('Enable Author Avatar & Name', 'bridge' ), 
					'category'=>__('Enable Category', 'bridge' ),
					'time'=> __('Enable Time/Date', 'bridge' ), 
					'comment' => __('Enable Comments', 'bridge' ), 
				),
				'std' => array(
					'author'=> '1',
					'time'=> '1',
					'comment'=> '1',
					'category'=>'1',
				)
			),
			array(
				'id'	 => 'mts_single_post_layout',
				'type'	 => 'layout2',
				'title'	=> __('Sections after Content', 'bridge' ),
				'sub_desc' => __('Customize the look area present after single post content.', 'bridge' ),
				'options'  => array(
					'enabled'  => array(
						'related'   => array(
							'label' 	=> __('Related Posts', 'bridge' ),
							'subfields'	=> array(
								array(
									'id' => 'mts_related_posts_taxonomy',
									'type' => 'button_set',
									'title' => __('Related Posts Taxonomy', 'bridge' ) ,
									'options' => array(
										'tags' => __( 'Tags', 'bridge' ),
										'categories' => __( 'Categories', 'bridge' )
									) ,
									'class' => 'green',
									'sub_desc' => __('Related Posts based on tags or categories.', 'bridge' ) ,
									'std' => 'categories'
								),
								array(
									'id' => 'mts_related_postsnum',
									'type' => 'text',
									'class' => 'small-text',
									'title' => __('Number of related posts', 'bridge' ) ,
									'sub_desc' => __('Enter the number of posts to show in the related posts section.', 'bridge' ) ,
									'std' => '4',
									'args' => array(
										'type' => 'number'
									)
								),

							)
						),
						'author'   => array(
							'label' 	=> __('Author Box', 'bridge' ),
							'subfields'	=> array(

							)
						),
					),
					'disabled' => array(
						'tags'   => array(
							'label' 	=> __('Tags', 'bridge' ),
							'subfields'	=> array(
							)
						),
					)
				)
			),
			array(
				'id' => 'mts_single_like_dislike',
				'type' => 'button_set',
				'title' => __('Enable Like/Dislike', 'bridge'), 
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('Use this button to enable Like &amp; Dislike features from single posts.', 'bridge'),
				'std' => '1'
			),
			array(
				'id' => 'mts_breadcrumb',
				'type' => 'button_set',
				'title' => __('Breadcrumbs', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('Breadcrumbs are a great way to make your site more user-friendly. You can enable them by checking this box.', 'bridge' ),
				'std' => '0'
			),
			array(
                'id'       => 'mts_comments',
                'type'     => 'layout2',
                'title'    => __('Comments', 'bridge'),
                'sub_desc' => __('Show standard comments, Facebook comments, or both, in tabs layout.', 'bridge'),
                'options'  => array(
                    'enabled'  => array(
                        'comments'   => array(
                        	'label' 	=> __('Comments','bridge'),
                        	'subfields'	=> array()
                        ),
                        'fb_comments'   => array(
                        	'label' 	=> __('Facebook Comments','bridge'),
                        	'subfields'	=> array(
			        			array(
			        				'id' => 'mts_fb_app_id',
			        				'type' => 'text',
			        				'title' => __('Facebook App ID', 'bridge'),
									'sub_desc' => __('Enter your Facebook app ID here. You can create Facebook App id <a href="https://developers.facebook.com/apps" target="_blank">here</a>', 'bridge'),
			        				'class' => 'small'
			        			),
                        	)
                        ),
                    ),
                    'disabled' => array()
                )
            ),
			array(
				'id' => 'mts_author_comment',
				'type' => 'button_set',
				'title' => __('Highlight Author Comment', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('Use this button to highlight author comments.', 'bridge' ),
				'std' => '1'
			),
			array(
				'id' => 'mts_comment_date',
				'type' => 'button_set',
				'title' => __('Date in Comments', 'bridge' ),
				'options' => array( '0' => __( 'Off', 'bridge' ), '1' => __( 'On', 'bridge' ) ),
				'sub_desc' => __('Use this button to show the date for comments.', 'bridge' ),
				'std' => '1'
			),
		)
	);
	$sections[] = array(
		'icon' => 'fa fa-group',
		'title' => __('Social Buttons', 'bridge' ),
		'desc' => '<p class="description">' . __('Enable or disable social sharing buttons on single posts using these buttons.', 'bridge' ) . '</p>',
		'fields' => array(
			array(
				'id' => 'mts_social_button_layout',
				'type' => 'radio_img',
				'title' => __('Social Sharing Buttons Layout', 'bridge' ),
				'sub_desc' => wp_kses( __('Choose default <strong>social sharing buttons</strong> layout or modern <strong>social sharing buttons</strong> layout for your site. ', 'bridge' ), array( 'strong' => array() ) ),
				'options' => array(
					'default' => array('img' => NHP_OPTIONS_URL.'img/layouts/default-social.jpg'),
					'modern' => array('img' => NHP_OPTIONS_URL.'img/layouts/modern-social.jpg')
				),
				'std' => 'default',
				'reset_at_version' => '1.0.6'
			),
			array(
				'id' => 'mts_social_button_position',
				'type' => 'button_set',
				'title' => __('Social Sharing Buttons Position', 'bridge' ),
				'options' => array('top' => __('Above Content', 'bridge' ), 'bottom' => __('Below Content', 'bridge' ), 'top_and_bottom' => __('Above & Below', 'bridge'), 'floating' => __('Floating', 'bridge' )),
				'sub_desc' => __('Choose position for Social Sharing Buttons.', 'bridge' ),
				'std' => 'top_and_bottom',
				'class' => 'green'
			),
			array(
				'id' => 'mts_social_buttons_on_pages',
				'type' => 'button_set',
				'title' => __('Social Sharing Buttons on Pages', 'bridge' ),
				'options' => array('0' => __('Off', 'bridge' ), '1' => __('On', 'bridge' )),
				'sub_desc' => __('Enable the sharing buttons for pages too, not just posts.', 'bridge' ),
				'std' => '0',
			),
			array(
				'id'   => 'mts_social_buttons',
				'type' => 'layout',
				'title'	=> __('Social Media Buttons', 'bridge' ),
				'sub_desc' => __('Organize how you want the social sharing buttons to appear on single posts', 'bridge' ),
				'options'  => array(
					'enabled'  => array(
						'facebookshare'   => __('Facebook Share', 'bridge' ),
						'facebook'  => __('Facebook Like', 'bridge' ),
						'twitter'   => __('Twitter', 'bridge' ),
						'gplus' => __('Google Plus', 'bridge' ),
						'pinterest' => __('Pinterest', 'bridge' ),
					),
					'disabled' => array(
						'linkedin'  => __('LinkedIn', 'bridge' ),
						'stumble'   => __('StumbleUpon', 'bridge' ),
						'reddit'   => __('Reddit', 'bridge' ),
					)
				),
				'std'  => array(
					'enabled'  => array(
						'facebookshare'   => __('Facebook Share', 'bridge' ),
						'facebook'  => __('Facebook Like', 'bridge' ),
						'twitter'   => __('Twitter', 'bridge' ),
						'gplus' => __('Google Plus', 'bridge' ),
						'pinterest' => __('Pinterest', 'bridge' ),
					),
					'disabled' => array(
						'linkedin'  => __('LinkedIn', 'bridge' ),
						'stumble'   => __('StumbleUpon', 'bridge' ),
						'reddit'   => __('Reddit', 'bridge' ),
					)
				),
				'reset_at_version' => '1.0.6'
			),
		)
	);
	$sections[] = array(
		'icon' => 'fa fa-bar-chart-o',
		'title' => __('Ad Management', 'bridge' ),
		'desc' => '<p class="description">' . __('Now, ad management is easy with our options panel. You can control everything from here, without using separate plugins.', 'bridge' ) . '</p>',
		'fields' => array(
			array(
				'id' => 'mts_posttop_adcode',
				'type' => 'textarea',
				'title' => __('Below Post Title', 'bridge' ),
				'sub_desc' => __('Paste your Adsense, BSA or other ad code here to show ads below your article title on single posts.', 'bridge' )
			),
			array(
				'id' => 'mts_posttop_adcode_time',
				'type' => 'text',
				'title' => __('Show After X Days', 'bridge' ),
				'sub_desc' => __('Enter the number of days after which you want to show the Below Post Title Ad. Enter 0 to disable this feature.', 'bridge' ),
				'validate' => 'numeric',
				'std' => '0',
				'class' => 'small-text',
				'args' => array('type' => 'number')
			),
			array(
				'id' => 'mts_postend_adcode',
				'type' => 'textarea',
				'title' => __('Below Post Content', 'bridge' ),
				'sub_desc' => __('Paste your Adsense, BSA or other ad code here to show ads below the post content on single posts.', 'bridge' )
			),
			array(
				'id' => 'mts_postend_adcode_time',
				'type' => 'text',
				'title' => __('Show After X Days', 'bridge' ),
				'sub_desc' => __('Enter the number of days after which you want to show the Below Post Title Ad. Enter 0 to disable this feature.', 'bridge' ),
				'validate' => 'numeric',
				'std' => '0',
				'class' => 'small-text',
				'args' => array('type' => 'number')
			),
		)
	);
	$sections[] = array(
		'icon' => 'fa fa-columns',
		'title' => __('Sidebars', 'bridge' ),
		'desc' => '<p class="description">' . __('Now you have full control over the sidebars. Here you can manage sidebars and select one for each section of your site, or select a custom sidebar on a per-post basis in the post editor.', 'bridge' ) . '<br></p>',
		'fields' => array(
			array(
				'id' => 'mts_custom_sidebars',
				'type'  => 'group', //doesn't need to be called for callback fields
				'title' => __('Custom Sidebars', 'bridge' ),
				'sub_desc'  => wp_kses( __('Add custom sidebars. <strong style="font-weight: 800;">You need to save the changes to use the sidebars in the dropdowns below.</strong><br />You can add content to the sidebars in Appearance &gt; Widgets.', 'bridge' ), array( 'strong' => array(), 'br' => '' ) ),
				'groupname' => __('Sidebar', 'bridge' ), // Group name
				'subfields' =>
					array(
						array(
							'id' => 'mts_custom_sidebar_name',
							'type' => 'text',
							'title' => __('Name', 'bridge' ),
							'sub_desc' => __('Example: Homepage Sidebar', 'bridge' )
						),
						array(
							'id' => 'mts_custom_sidebar_id',
							'type' => 'text',
							'title' => __('ID', 'bridge' ),
							'sub_desc' => __('Enter a unique ID for the sidebar. Use only alphanumeric characters, underscores (_) and dashes (-), eg. "sidebar-home"', 'bridge' ),
							'std' => 'sidebar-'
						),
					),
			),
			array(
				'id' => 'mts_sidebar_for_home',
				'type' => 'sidebars_select',
				'title' => __('Homepage', 'bridge' ),
				'sub_desc' => __('Select a sidebar for the homepage.', 'bridge' ),
				'args' => array('allow_nosidebar' => false, 'exclude' => array('sidebar', 'widget-subscribe', 'footer-first', 'footer-first-2', 'footer-first-3', 'footer-first-4', 'footer-second', 'footer-second-2', 'footer-second-3', 'footer-second-4', 'widget-header','shop-sidebar', 'product-sidebar')),
				'std' => ''
			),
			array(
				'id' => 'mts_sidebar_for_post',
				'type' => 'sidebars_select',
				'title' => __('Single Post', 'bridge' ),
				'sub_desc' => __('Select a sidebar for the single posts. If a post has a custom sidebar set, it will override this.', 'bridge' ),
				'args' => array('exclude' => array('sidebar', 'widget-subscribe', 'footer-first', 'footer-first-2', 'footer-first-3', 'footer-first-4', 'footer-second', 'footer-second-2', 'footer-second-3', 'footer-second-4', 'widget-header','shop-sidebar', 'product-sidebar')),
				'std' => ''
			),
			array(
				'id' => 'mts_sidebar_for_page',
				'type' => 'sidebars_select',
				'title' => __('Single Page', 'bridge' ),
				'sub_desc' => __('Select a sidebar for the single pages. If a page has a custom sidebar set, it will override this.', 'bridge' ),
				'args' => array('exclude' => array('sidebar', 'widget-subscribe', 'footer-first', 'footer-first-2', 'footer-first-3', 'footer-first-4', 'footer-second', 'footer-second-2', 'footer-second-3', 'footer-second-4', 'widget-header','shop-sidebar', 'product-sidebar')),
				'std' => ''
			),
			array(
				'id' => 'mts_sidebar_for_archive',
				'type' => 'sidebars_select',
				'title' => __('Archive', 'bridge' ),
				'sub_desc' => __('Select a sidebar for the archives. Specific archive sidebars will override this setting (see below).', 'bridge' ),
				'args' => array('allow_nosidebar' => false, 'exclude' => array('sidebar', 'widget-subscribe', 'footer-first', 'footer-first-2', 'footer-first-3', 'footer-first-4', 'footer-second', 'footer-second-2', 'footer-second-3', 'footer-second-4', 'widget-header','shop-sidebar', 'product-sidebar')),
				'std' => ''
			),
			array(
				'id' => 'mts_sidebar_for_category',
				'type' => 'sidebars_select',
				'title' => __('Category Archive', 'bridge' ),
				'sub_desc' => __('Select a sidebar for the category archives.', 'bridge' ),
				'args' => array('allow_nosidebar' => false, 'exclude' => array('sidebar', 'widget-subscribe', 'footer-first', 'footer-first-2', 'footer-first-3', 'footer-first-4', 'footer-second', 'footer-second-2', 'footer-second-3', 'footer-second-4', 'widget-header','shop-sidebar', 'product-sidebar')),
				'std' => ''
			),
			array(
				'id' => 'mts_sidebar_for_tag',
				'type' => 'sidebars_select',
				'title' => __('Tag Archive', 'bridge' ),
				'sub_desc' => __('Select a sidebar for the tag archives.', 'bridge' ),
				'args' => array('allow_nosidebar' => false, 'exclude' => array('sidebar', 'widget-subscribe', 'footer-first', 'footer-first-2', 'footer-first-3', 'footer-first-4', 'footer-second', 'footer-second-2', 'footer-second-3', 'footer-second-4', 'widget-header','shop-sidebar', 'product-sidebar')),
				'std' => ''
			),
			array(
				'id' => 'mts_sidebar_for_date',
				'type' => 'sidebars_select',
				'title' => __('Date Archive', 'bridge' ),
				'sub_desc' => __('Select a sidebar for the date archives.', 'bridge' ),
				'args' => array('allow_nosidebar' => false, 'exclude' => array('sidebar', 'widget-subscribe', 'footer-first', 'footer-first-2', 'footer-first-3', 'footer-first-4', 'footer-second', 'footer-second-2', 'footer-second-3', 'footer-second-4', 'widget-header','shop-sidebar', 'product-sidebar')),
				'std' => ''
			),
			array(
				'id' => 'mts_sidebar_for_author',
				'type' => 'sidebars_select',
				'title' => __('Author Archive', 'bridge' ),
				'sub_desc' => __('Select a sidebar for the author archives.', 'bridge' ),
				'args' => array('allow_nosidebar' => false, 'exclude' => array('sidebar', 'widget-subscribe', 'footer-first', 'footer-first-2', 'footer-first-3', 'footer-first-4', 'footer-second', 'footer-second-2', 'footer-second-3', 'footer-second-4', 'widget-header','shop-sidebar', 'product-sidebar')),
				'std' => ''
			),
			array(
				'id' => 'mts_sidebar_for_search',
				'type' => 'sidebars_select',
				'title' => __('Search', 'bridge' ),
				'sub_desc' => __('Select a sidebar for the search results.', 'bridge' ),
				'args' => array('allow_nosidebar' => false, 'exclude' => array('sidebar', 'widget-subscribe', 'footer-first', 'footer-first-2', 'footer-first-3', 'footer-first-4', 'footer-second', 'footer-second-2', 'footer-second-3', 'footer-second-4', 'widget-header','shop-sidebar', 'product-sidebar')),
				'std' => ''
			),
			array(
				'id' => 'mts_sidebar_for_notfound',
				'type' => 'sidebars_select',
				'title' => __('404 Error', 'bridge' ),
				'sub_desc' => __('Select a sidebar for the 404 Not found pages.', 'bridge' ),
				'args' => array('allow_nosidebar' => false, 'exclude' => array('sidebar', 'widget-subscribe', 'footer-first', 'footer-first-2', 'footer-first-3', 'footer-first-4', 'footer-second', 'footer-second-2', 'footer-second-3', 'footer-second-4', 'widget-header','shop-sidebar', 'product-sidebar')),
				'std' => ''
			),
			array(
				'id' => 'mts_sidebar_for_shop',
				'type' => 'sidebars_select',
				'title' => __('Shop Pages', 'bridge' ),
				'sub_desc' => wp_kses( __('Select a sidebar for Shop main page and product archive pages (WooCommerce plugin must be enabled). Default is <strong>Shop Page Sidebar</strong>.', 'bridge' ), array( 'strong' => array() ) ),
				'args' => array('allow_nosidebar' => false, 'exclude' => array('sidebar', 'widget-subscribe', 'footer-first', 'footer-first-2', 'footer-first-3', 'footer-first-4', 'footer-second', 'footer-second-2', 'footer-second-3', 'footer-second-4', 'widget-header','shop-sidebar', 'product-sidebar')),
				'std' => 'shop-sidebar'
			),
			array(
				'id' => 'mts_sidebar_for_product',
				'type' => 'sidebars_select',
				'title' => __('Single Product', 'bridge' ),
				'sub_desc' => wp_kses( __('Select a sidebar for single products (WooCommerce plugin must be enabled). Default is <strong>Single Product Sidebar</strong>.', 'bridge' ), array( 'strong' => array() ) ),
				'args' => array('allow_nosidebar' => false, 'exclude' => array('sidebar', 'widget-subscribe', 'footer-first', 'footer-first-2', 'footer-first-3', 'footer-first-4', 'footer-second', 'footer-second-2', 'footer-second-3', 'footer-second-4', 'widget-header','shop-sidebar', 'product-sidebar')),
				'std' => 'product-sidebar'
			),
		),
	);

	$sections[] = array(
		'icon' => 'fa fa-list-alt',
		'title' => __('Navigation', 'bridge' ),
		'desc' => '<p class="description"><div class="controls">' . sprintf( __('Navigation settings can now be modified from the %s.', 'bridge' ), '<a href="nav-menus.php"><b>' . __( 'Menus Section', 'bridge' ) . '</b></a>' ) . '<br></div></p>'
	);


	$tabs = array();

	$args['presets'] = array();
	$args['show_translate'] = false;
	include('theme-presets.php');

	global $NHP_Options;
	$NHP_Options = new NHP_Options($sections, $args, $tabs);

} //function

add_action('init', 'setup_framework_options', 0);

/*
 *
 * Custom function for the callback referenced above
 *
 */
function my_custom_field($field, $value){
	print_r($field);
	print_r($value);

}//function

/*
 *
 * Custom function for the callback validation referenced above
 *
 */
function validate_callback_function($field, $value, $existing_value){

	$error = false;
	$value =  'just testing';
	$return['value'] = $value;
	if($error == true){
		$return['error'] = $field;
	}
	return $return;

}//function

/*--------------------------------------------------------------------
 *
 * Default Font Settings
 *
 --------------------------------------------------------------------*/
if(function_exists('mts_register_typography')) {
	mts_register_typography( array(
		'logo_font' => array(
			'preview_text' => __( 'Logo Font', 'bridge' ),
			'preview_color' => 'dark',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '36px',
			'font_color' => '#ffffff',
			'css_selectors' => '#header #logo a'
		),
		'primary_navigation_font' => array(
			'preview_text' => __( 'Primary Navigation', 'bridge' ),
			'preview_color' => 'dark',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '15px',
			'font_color' => '#eeeeee',
			'css_selectors' => '#secondary-navigation a, .search-wrap'
		),
		'secondary_navigation_font' => array(
			'preview_text' => __( 'Secondary Navigation', 'bridge' ),
			'preview_color' => 'dark',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '15px',
			'font_color' => '#444444',
			'additional_css' => 'text-transform: uppercase;',
			'css_selectors' => '#primary-navigation a'
		),
		'featured_area_title_font' => array(
			'preview_text' => __( 'Featured Area Title', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_size' => '16px',
			'font_variant' => '600',
			'font_color' => '#121212',
			'css_selectors' => '.featuredBox .title'
		),
		'home_title_font' => array(
			'preview_text' => __( 'Home Article Title', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_size' => '24px',
			'font_variant' => '600',
			'font_color' => '#121212',
			'css_selectors' => '.latestPost .title'
		),
		'post_info_font' => array(
			'preview_text' => __( 'Post Info text', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_size' => '13px',
			'font_variant' => 'normal',
			'font_color' => '#515151',
			'css_selectors' => '.post-info, .breadcrumb'
		),
		'single_title_font' => array(
			'preview_text' => __( 'Single Article Title', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_size' => '34px',
			'font_variant' => '600',
			'font_color' => '#121212',
			'css_selectors' => '.single-title'
		),
		'content_font' => array(
			'preview_text' => __( 'Content Font', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_size' => '17px',
			'font_variant' => 'normal',
			'font_color' => '#515151',
			'css_selectors' => 'body'
		),
		'sidebar_title' => array(
			'preview_text' => __( 'Sidebar Title', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '20px',
			'font_color' => '#121212',
			'css_selectors' => '.widget h3'
		),
		'sidebar_heading' => array(
			'preview_text' => __( 'Sidebar Post Heading', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '16px',
			'font_color' => '#121212',
			'css_selectors' => '.widget .post-title, .sidebar .widget .entry-title, .widget .slide-title, .widget .wpt_comment_meta'
		),
		'sidebar_font' => array(
			'preview_text' => __( 'Sidebar Font', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => 'normal',
			'font_size' => '16px',
			'font_color' => '#121212',
			'css_selectors' => '.widget'
		),
		'footer_heading' => array(
			'preview_text' => __( 'Footer Title', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '16px',
			'font_color' => '#ffffff',
			'additional_css' => 'text-transform: uppercase;',
			'css_selectors' => '#site-footer .widget h3'
		),
		'footer_title_font' => array(
			'preview_text' => __( 'Footer Post Heading', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '16px',
			'font_color' => '#ffffff',
			'css_selectors' => '#site-footer .widget .post-title, #site-footer .widget .entry-title, #site-footer .widget .slide-title, #site-footer .widget .wpt_comment_meta'
		),
		'footer_font' => array(
			'preview_text' => __( 'Footer Font', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => 'normal',
			'font_size' => '16px',
			'font_color' => '#aeb4bf',
			'css_selectors' => '#site-footer .widget'
		),
		'copyright_font' => array(
			'preview_text' => __( 'Copyright Font', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => 'normal',
			'font_size' => '14px',
			'font_color' => '#aeb4bf',
			'css_selectors' => '.copyrights'
		),
		'h1_headline' => array(
			'preview_text' => __( 'Content H1', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '30px',
			'font_color' => '#121212',
			'css_selectors' => 'h1'
		),
		'h2_headline' => array(
			'preview_text' => __( 'Content H2', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '28px',
			'font_color' => '#121212',
			'css_selectors' => 'h2'
		),
		'h3_headline' => array(
			'preview_text' => __( 'Content H3', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '26px',
			'font_color' => '#121212',
			'css_selectors' => 'h3'
		),
		'h4_headline' => array(
			'preview_text' => __( 'Content H4', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '25px',
			'font_color' => '#121212',
			'css_selectors' => 'h4'
		),
		'h5_headline' => array(
			'preview_text' => __( 'Content H5', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '22px',
			'font_color' => '#121212',
			'css_selectors' => 'h5'
		),
		'h6_headline' => array(
			'preview_text' => __( 'Content H6', 'bridge' ),
			'preview_color' => 'light',
			'font_family' => 'Source Sans Pro',
			'font_variant' => '600',
			'font_size' => '20px',
			'font_color' => '#121212',
			'css_selectors' => 'h6'
		)
	));
}
