<?php
/**
 * Theme sprecific functions and definitions
 */

/* Theme setup section
------------------------------------------------------------------- */

// Set the content width based on the theme's design and stylesheet.
if ( ! isset( $content_width ) ) $content_width = 1170; /* pixels */

// Add theme specific actions and filters
// Attention! Function were add theme specific actions and filters handlers must have priority 1
if ( !function_exists( 'organic_beauty_theme_setup' ) ) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_theme_setup', 1 );
	function organic_beauty_theme_setup() {

		// Add default posts and comments RSS feed links to head
		add_theme_support( 'automatic-feed-links' );

		// Enable support for Post Thumbnails
		add_theme_support( 'post-thumbnails' );

		// Custom header setup
		add_theme_support( 'custom-header', array('header-text'=>false));

		// Custom backgrounds setup
		add_theme_support( 'custom-background');

		// Supported posts formats
		add_theme_support( 'post-formats', array('gallery', 'video', 'audio', 'link', 'quote', 'image', 'status', 'aside', 'chat') );

		// Autogenerate title tag
		add_theme_support('title-tag');

		// Add user menu
		add_theme_support('nav-menus');

		// WooCommerce Support
		add_theme_support( 'woocommerce' );

		// Register theme menus
		add_filter( 'organic_beauty_filter_add_theme_menus',		'organic_beauty_add_theme_menus' );

		// Register theme sidebars
		add_filter( 'organic_beauty_filter_add_theme_sidebars',	'organic_beauty_add_theme_sidebars' );

		// Set options for importer
		add_filter( 'organic_beauty_filter_importer_options',		'organic_beauty_set_importer_options' );

		// Add theme required plugins
		add_filter( 'organic_beauty_filter_required_plugins',		'organic_beauty_add_required_plugins' );
		
		// Add preloader styles
		add_filter('organic_beauty_filter_add_styles_inline',		'organic_beauty_head_add_page_preloader_styles');

		// Init theme after WP is created
		add_action( 'wp',									'organic_beauty_core_init_theme' );

		// Add theme specified classes into the body
		add_filter( 'body_class', 							'organic_beauty_body_classes' );

		// Add data to the head and to the beginning of the body
		add_action('wp_head',								'organic_beauty_head_add_page_meta', 1);
		add_action('before',								'organic_beauty_body_add_gtm');
		add_action('before',								'organic_beauty_body_add_toc');
		add_action('before',								'organic_beauty_body_add_page_preloader');

		// Add data to the footer (priority 1, because priority 2 used for localize scripts)
		add_action('wp_footer',								'organic_beauty_footer_add_views_counter', 1);
		add_action('wp_footer',								'organic_beauty_footer_add_theme_customizer', 1);
		add_action('wp_footer',								'organic_beauty_footer_add_custom_html', 1);
		add_action('wp_footer',								'organic_beauty_footer_add_gtm2', 1);

		// Set list of the theme required plugins
		organic_beauty_storage_set('required_plugins', array(
			'essgrids',
			'revslider',
			'trx_utils',
			'visual_composer',
			'woocommerce',
			'mailchimp',
			'instagram_feed',
			'gdpr-compliance',
            'elegro-payment',
            'contact-form-7',
            'trx_updater'
			)
		);

		// Gutenberg support
		add_theme_support( 'align-wide' );

		// Set list of the theme required custom fonts from folder /css/font-faces
		// Attention! Font's folder must have name equal to the font's name
		organic_beauty_storage_set('required_custom_fonts', array(
			'Amadeus'
			)
		);
		
		organic_beauty_storage_set('demo_data_url',  esc_url(organic_beauty_get_protocol() . '://organic-beauty.themerex.net/demo/'));
		
	}
}


// Add/Remove theme nav menus
if ( !function_exists( 'organic_beauty_add_theme_menus' ) ) {
	//Handler of add_filter( 'organic_beauty_filter_add_theme_menus', 'organic_beauty_add_theme_menus' );
	function organic_beauty_add_theme_menus($menus) {
		return $menus;
	}
}


// Add theme specific widgetized areas
if ( !function_exists( 'organic_beauty_add_theme_sidebars' ) ) {
	//Handler of add_filter( 'organic_beauty_filter_add_theme_sidebars',	'organic_beauty_add_theme_sidebars' );
	function organic_beauty_add_theme_sidebars($sidebars=array()) {
		if (is_array($sidebars)) {
			$theme_sidebars = array(
				'sidebar_main'		=> esc_html__( 'Main Sidebar', 'organic-beauty' ),
				'sidebar_footer'	=> esc_html__( 'Footer Sidebar', 'organic-beauty' )
			);
			if (function_exists('organic_beauty_exists_woocommerce') && organic_beauty_exists_woocommerce()) {
				$theme_sidebars['sidebar_cart']  = esc_html__( 'WooCommerce Cart Sidebar', 'organic-beauty' );
			}
			$sidebars = array_merge($theme_sidebars, $sidebars);
		}
		return $sidebars;
	}
}


// Add theme required plugins
if ( !function_exists( 'organic_beauty_add_required_plugins' ) ) {
	//Handler of add_filter( 'organic_beauty_filter_required_plugins',		'organic_beauty_add_required_plugins' );
	function organic_beauty_add_required_plugins($plugins) {
		$plugins[] = array(
			'name' 		=> esc_html__('ThemeREX Utilities', 'organic-beauty'),
			'version'	=> '3.4',					// Minimal required version
			'slug' 		=> 'trx_utils',
			'source'	=> organic_beauty_get_file_dir('plugins/install/trx_utils.zip'),
			'required' 	=> true
		);
		return $plugins;
	}
}


//------------------------------------------------------------------------
// One-click import support
//------------------------------------------------------------------------

// Set theme specific importer options
if ( ! function_exists( 'organic_beauty_importer_set_options' ) ) {
	add_filter( 'trx_utils_filter_importer_options', 'organic_beauty_importer_set_options', 9 );
	function organic_beauty_importer_set_options( $options=array() ) {
		if ( is_array( $options ) ) {
			// Save or not installer's messages to the log-file
			$options['debug'] = false;
			// Prepare demo data
			if ( is_dir( ORGANIC_BEAUTY_THEME_PATH . 'demo/' ) ) {
				$options['demo_url'] = ORGANIC_BEAUTY_THEME_PATH . 'demo/';
			} else {
				$options['demo_url'] = esc_url( organic_beauty_get_protocol().'://demofiles.themerex.net/organic-beauty/' ); // Demo-site domain
			}

			// Required plugins
			$options['required_plugins'] =  array(
				'essential-grid',
				'revslider',
				'js_composer',
				'woocommerce',
				'mailchimp-for-wp',
				'instagram-feed'
			);

			$options['theme_slug'] = 'organic_beauty';

			// Set number of thumbnails to regenerate when its imported (if demo data was zipped without cropped images)
			// Set 0 to prevent regenerate thumbnails (if demo data archive is already contain cropped images)
			$options['regenerate_thumbnails'] = 3;
			// Default demo
			$options['files']['default']['title'] = esc_html__( 'Organic Beauty Demo', 'organic-beauty' );
			$options['files']['default']['domain_dev'] = esc_url('http://organic-beauty.themerex.net'); // Developers domain
			$options['files']['default']['domain_demo']= esc_url('http://organic-beauty.themerex.net'); // Demo-site domain

		}
		return $options;
	}
}


// Add data to the head and to the beginning of the body
//------------------------------------------------------------------------

// Add theme specified classes to the body tag
if ( !function_exists('organic_beauty_body_classes') ) {
	//Handler of add_filter( 'body_class', 'organic_beauty_body_classes' );
	function organic_beauty_body_classes( $classes ) {

		$classes[] = 'organic_beauty_body';
		$classes[] = 'body_style_' . trim(organic_beauty_get_custom_option('body_style'));
		$classes[] = 'body_' . (organic_beauty_get_custom_option('body_filled')=='yes' ? 'filled' : 'transparent');
		$classes[] = 'article_style_' . trim(organic_beauty_get_custom_option('article_style'));
		
		$blog_style = organic_beauty_get_custom_option(is_singular() && !organic_beauty_storage_get('blog_streampage') ? 'single_style' : 'blog_style');
		$classes[] = 'layout_' . trim($blog_style);
		$classes[] = 'template_' . trim(organic_beauty_get_template_name($blog_style));
		
		$body_scheme = organic_beauty_get_custom_option('body_scheme');
		if (empty($body_scheme)  || organic_beauty_is_inherit_option($body_scheme)) $body_scheme = 'original';
		$classes[] = 'scheme_' . $body_scheme;

		$top_panel_position = organic_beauty_get_custom_option('top_panel_position');
		if (!organic_beauty_param_is_off($top_panel_position)) {
			$classes[] = 'top_panel_show';
			$classes[] = 'top_panel_' . trim($top_panel_position);
		} else 
			$classes[] = 'top_panel_hide';
		$classes[] = organic_beauty_get_sidebar_class();

		if (organic_beauty_get_custom_option('show_video_bg')=='yes' && (organic_beauty_get_custom_option('video_bg_youtube_code')!='' || organic_beauty_get_custom_option('video_bg_url')!=''))
			$classes[] = 'video_bg_show';

		if (!organic_beauty_param_is_off(organic_beauty_get_theme_option('page_preloader')))
			$classes[] = 'preloader';

		return $classes;
	}
}


// Add page meta to the head
if (!function_exists('organic_beauty_head_add_page_meta')) {
	//Handler of add_action('wp_head', 'organic_beauty_head_add_page_meta', 1);
	function organic_beauty_head_add_page_meta() {
		?>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1<?php if (organic_beauty_get_theme_option('responsive_layouts')=='yes') echo ', maximum-scale=1'; ?>">
		<meta name="format-detection" content="telephone=no">
	
		<link rel="profile" href="//gmpg.org/xfn/11" />
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<?php
	}
}

// Add page preloader styles to the head
if (!function_exists('organic_beauty_head_add_page_preloader_styles')) {
	//Handler of add_filter('organic_beauty_filter_add_styles_inline', 'organic_beauty_head_add_page_preloader_styles');
	function organic_beauty_head_add_page_preloader_styles($css) {
		if (($preloader=organic_beauty_get_theme_option('page_preloader'))!='none') {
			$image = organic_beauty_get_theme_option('page_preloader_image');
			$bg_clr = organic_beauty_get_scheme_color('bg_color');
			$link_clr = organic_beauty_get_scheme_color('text_link');
			$css .= '
				#page_preloader {
					background-color: '. esc_attr($bg_clr) . ';'
					. ($preloader=='custom' && $image
						? 'background-image:url('.esc_url($image).');'
						: ''
						)
					. '
				}
				.preloader_wrap > div {
					background-color: '.esc_attr($link_clr).';
				}';
		}
		return $css;
	}
}

// Add gtm code to the beginning of the body 
if (!function_exists('organic_beauty_body_add_gtm')) {
	//Handler of add_action('before', 'organic_beauty_body_add_gtm');
	function organic_beauty_body_add_gtm() {
		organic_beauty_show_layout(organic_beauty_get_custom_option('gtm_code'));
	}
}

// Add TOC anchors to the beginning of the body 
if (!function_exists('organic_beauty_body_add_toc')) {
	//Handler of add_action('before', 'organic_beauty_body_add_toc');
	function organic_beauty_body_add_toc() {
		// Add TOC items 'Home' and "To top"
		if (organic_beauty_get_custom_option('menu_toc_home')=='yes' && function_exists('organic_beauty_sc_anchor'))
			organic_beauty_show_layout(organic_beauty_sc_anchor(array(
				'id' => "toc_home",
				'title' => esc_html__('Home', 'organic-beauty'),
				'description' => esc_html__('{{Return to Home}} - ||navigate to home page of the site', 'organic-beauty'),
				'icon' => "icon-home",
				'separator' => "yes",
				'url' => esc_url(home_url('/'))
				)
			)); 
		if (organic_beauty_get_custom_option('menu_toc_top')=='yes' && function_exists('organic_beauty_sc_anchor'))
			organic_beauty_show_layout(organic_beauty_sc_anchor(array(
				'id' => "toc_top",
				'title' => esc_html__('To Top', 'organic-beauty'),
				'description' => esc_html__('{{Back to top}} - ||scroll to top of the page', 'organic-beauty'),
				'icon' => "icon-double-up",
				'separator' => "yes")
				)); 
	}
}

// Add page preloader to the beginning of the body
if (!function_exists('organic_beauty_body_add_page_preloader')) {
	//Handler of add_action('before', 'organic_beauty_body_add_page_preloader');
	function organic_beauty_body_add_page_preloader() {
		if ( ($preloader=organic_beauty_get_theme_option('page_preloader')) != 'none' && ( $preloader != 'custom' || ($image=organic_beauty_get_theme_option('page_preloader_image')) != '')) {
			?><div id="page_preloader"><?php
				if ($preloader == 'circle') {
					?><div class="preloader_wrap preloader_<?php echo esc_attr($preloader); ?>"><div class="preloader_circ1"></div><div class="preloader_circ2"></div><div class="preloader_circ3"></div><div class="preloader_circ4"></div></div><?php
				} else if ($preloader == 'square') {
					?><div class="preloader_wrap preloader_<?php echo esc_attr($preloader); ?>"><div class="preloader_square1"></div><div class="preloader_square2"></div></div><?php
				}
			?></div><?php
		}
	}
}

// Add theme required plugins
if ( !function_exists( 'organic_beauty_add_trx_utils' ) ) {
	add_filter( 'trx_utils_active', 'organic_beauty_add_trx_utils' );
	function organic_beauty_add_trx_utils($enable=true) {
		return true;
	}
}

// Return text for the "I agree ..." checkbox
if ( ! function_exists( 'organic_beauty_trx_utils_privacy_text' ) ) {
	add_filter( 'trx_utils_filter_privacy_text', 'organic_beauty_trx_utils_privacy_text' );
	function organic_beauty_trx_utils_privacy_text( $text='' ) {
		return organic_beauty_get_privacy_text();
	}
}

// Add data to the footer
//------------------------------------------------------------------------

// Add post/page views counter
if (!function_exists('organic_beauty_footer_add_views_counter')) {
	//Handler of add_action('wp_footer', 'organic_beauty_footer_add_views_counter');
	function organic_beauty_footer_add_views_counter() {
		// Post/Page views counter
		get_template_part(organic_beauty_get_file_slug('templates/_parts/views-counter.php'));
	}
}

// Add theme customizer
if (!function_exists('organic_beauty_footer_add_theme_customizer')) {
	//Handler of add_action('wp_footer', 'organic_beauty_footer_add_theme_customizer');
	function organic_beauty_footer_add_theme_customizer() {
		// Front customizer
		if (organic_beauty_get_custom_option('show_theme_customizer')=='yes') {
			require_once ORGANIC_BEAUTY_FW_PATH . 'core/core.customizer/front.customizer.php';
		}
	}
}

// Add custom html
if (!function_exists('organic_beauty_footer_add_custom_html')) {
	//Handler of add_action('wp_footer', 'organic_beauty_footer_add_custom_html');
	function organic_beauty_footer_add_custom_html() {
		?><div class="custom_html_section"><?php
			organic_beauty_show_layout(organic_beauty_get_custom_option('custom_code'));
		?></div><?php
	}
}

// Add gtm code
if (!function_exists('organic_beauty_footer_add_gtm2')) {
	//Handler of add_action('wp_footer', 'organic_beauty_footer_add_gtm2');
	function organic_beauty_footer_add_gtm2() {
		organic_beauty_show_layout(organic_beauty_get_custom_option('gtm_code2'));
	}
}

/**
 * Fire the wp_body_open action.
 *
 * Added for backwards compatibility to support pre 5.2.0 WordPress versions.
 */
if ( ! function_exists( 'wp_body_open' ) ) {
    function wp_body_open() {
        /**
         * Triggered after the opening <body> tag.
         */
        do_action('wp_body_open');
    }
}

// Include framework core files
//-------------------------------------------------------------------
require_once trailingslashit( get_template_directory() ) . 'fw/loader.php';
?>