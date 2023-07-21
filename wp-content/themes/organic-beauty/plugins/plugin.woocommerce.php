<?php
/* Woocommerce support functions
------------------------------------------------------------------------------- */

// Theme init
if (!function_exists('organic_beauty_woocommerce_theme_setup')) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_woocommerce_theme_setup', 1 );
	function organic_beauty_woocommerce_theme_setup() {

		if (organic_beauty_exists_woocommerce()) {
			
			add_theme_support( 'woocommerce' );
			// Next setting from the WooCommerce 3.0+ enable built-in image zoom on the single product page
			add_theme_support( 'wc-product-gallery-zoom' );
			// Next setting from the WooCommerce 3.0+ enable built-in image slider on the single product page
			add_theme_support( 'wc-product-gallery-slider' );
			// Next setting from the WooCommerce 3.0+ enable built-in image lightbox on the single product page
			add_theme_support( 'wc-product-gallery-lightbox' );
			
			add_action('organic_beauty_action_add_styles', 				'organic_beauty_woocommerce_frontend_scripts' );

			// Detect current page type, taxonomy and title (for custom post_types use priority < 10 to fire it handles early, than for standard post types)
			add_filter('organic_beauty_filter_get_blog_type',				'organic_beauty_woocommerce_get_blog_type', 9, 2);
			add_filter('organic_beauty_filter_get_blog_title',			'organic_beauty_woocommerce_get_blog_title', 9, 2);
			add_filter('organic_beauty_filter_get_current_taxonomy',		'organic_beauty_woocommerce_get_current_taxonomy', 9, 2);
			add_filter('organic_beauty_filter_is_taxonomy',				'organic_beauty_woocommerce_is_taxonomy', 9, 2);
			add_filter('organic_beauty_filter_get_stream_page_title',		'organic_beauty_woocommerce_get_stream_page_title', 9, 2);
			add_filter('organic_beauty_filter_get_stream_page_link',		'organic_beauty_woocommerce_get_stream_page_link', 9, 2);
			add_filter('organic_beauty_filter_get_stream_page_id',		'organic_beauty_woocommerce_get_stream_page_id', 9, 2);
			add_filter('organic_beauty_filter_detect_inheritance_key',	'organic_beauty_woocommerce_detect_inheritance_key', 9, 1);
			add_filter('organic_beauty_filter_detect_template_page_id',	'organic_beauty_woocommerce_detect_template_page_id', 9, 2);
			add_filter('organic_beauty_filter_orderby_need',				'organic_beauty_woocommerce_orderby_need', 9, 2);

			add_filter('organic_beauty_filter_show_post_navi', 			'organic_beauty_woocommerce_show_post_navi');
			add_filter('organic_beauty_filter_list_post_types', 			'organic_beauty_woocommerce_list_post_types');
			
			// Detect if WooCommerce support 'Product Grid' feature
			$product_grid = organic_beauty_exists_woocommerce() && function_exists( 'wc_get_theme_support' ) ? wc_get_theme_support( 'product_grid' ) : false;
			add_theme_support( 'wc-product-grid-enable', isset( $product_grid['min_columns'] ) && isset( $product_grid['max_columns'] ) );
		}

		if (is_admin()) {
			add_filter( 'organic_beauty_filter_importer_required_plugins',		'organic_beauty_woocommerce_importer_required_plugins', 10, 2 );
			add_filter( 'organic_beauty_filter_required_plugins',					'organic_beauty_woocommerce_required_plugins' );
		}
	}
}

if ( !function_exists( 'organic_beauty_woocommerce_settings_theme_setup2' ) ) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_woocommerce_settings_theme_setup2', 3 );
	function organic_beauty_woocommerce_settings_theme_setup2() {
		if (organic_beauty_exists_woocommerce()) {
			// Add WooCommerce pages in the Theme inheritance system
			organic_beauty_add_theme_inheritance( array( 'woocommerce' => array(
				'stream_template' => 'blog-woocommerce',		// This params must be empty
				'single_template' => 'single-woocommerce',		// They are specified to enable separate settings for blog and single wooc
				'taxonomy' => array('product_cat'),
				'taxonomy_tags' => array('product_tag'),
				'post_type' => array('product'),
				'override' => 'custom'
				) )
			);

			// Add WooCommerce specific options in the Theme Options

			organic_beauty_storage_set_array_before('options', 'partition_service', array(
				
				"partition_woocommerce" => array(
					"title" => esc_html__('WooCommerce', 'organic-beauty'),
					"icon" => "iconadmin-basket",
					"type" => "partition"),

				"info_wooc_1" => array(
					"title" => esc_html__('WooCommerce products list parameters', 'organic-beauty'),
					"desc" => esc_html__("Select WooCommerce products list's style and crop parameters", 'organic-beauty'),
					"type" => "info"),
		
				"shop_mode" => array(
					"title" => esc_html__('Shop list style',  'organic-beauty'),
					"desc" => esc_html__("WooCommerce products list's style: thumbs or list with description", 'organic-beauty'),
					"std" => "thumbs",
					"divider" => false,
					"options" => array(
						'thumbs' => esc_html__('Thumbs', 'organic-beauty'),
						'list' => esc_html__('List', 'organic-beauty')
					),
					"type" => "checklist"),
		
				"show_mode_buttons" => array(
					"title" => esc_html__('Show style buttons',  'organic-beauty'),
					"desc" => esc_html__("Show buttons to allow visitors change list style", 'organic-beauty'),
					"std" => "yes",
					"options" => organic_beauty_get_options_param('list_yes_no'),
					"type" => "switch"),

				"shop_loop_columns" => array(
					"title" => esc_html__('Shop columns',  'organic-beauty'),
					"desc" => esc_html__("How many columns used to show products on shop page", 'organic-beauty'),
					"std" => "3",
					"step" => 1,
					"min" => 1,
					"max" => 6,
					"type" => "spinner"),

				"show_currency" => array(
					"title" => esc_html__('Show currency selector', 'organic-beauty'),
					"desc" => esc_html__('Show currency selector in the user menu', 'organic-beauty'),
					"std" => "no",
					"options" => organic_beauty_get_options_param('list_yes_no'),
					"type" => "switch"),

				"show_cart" => array(
					"title" => esc_html__('Show cart button', 'organic-beauty'),
					"desc" => esc_html__('Show cart button in the user menu', 'organic-beauty'),
					"std" => "shop",
					"options" => array(
						'hide'   => esc_html__('Hide', 'organic-beauty'),
						'always' => esc_html__('Always', 'organic-beauty'),
						'shop'   => esc_html__('Only on shop pages', 'organic-beauty')
					),
					"type" => "checklist"),

				"crop_product_thumb" => array(
					"title" => esc_html__("Crop product's thumbnail",  'organic-beauty'),
					"desc" => esc_html__("Crop product's thumbnails on search results page or scale it", 'organic-beauty'),
					"std" => "no",
					"options" => organic_beauty_get_options_param('list_yes_no'),
					"type" => "switch")

				)
			);

		}
	}
}

// WooCommerce hooks
if (!function_exists('organic_beauty_woocommerce_theme_setup3')) {
	add_action( 'organic_beauty_action_after_init_theme', 'organic_beauty_woocommerce_theme_setup3' );
	function organic_beauty_woocommerce_theme_setup3() {

		if (organic_beauty_exists_woocommerce()) {
			add_action(    'woocommerce_before_subcategory_title',		'organic_beauty_woocommerce_open_thumb_wrapper', 9 );
			add_action(    'woocommerce_before_shop_loop_item_title',	'organic_beauty_woocommerce_open_thumb_wrapper', 9 );

			add_action(    'woocommerce_before_subcategory_title',		'organic_beauty_woocommerce_open_item_wrapper', 20 );
			add_action(    'woocommerce_before_shop_loop_item_title',	'organic_beauty_woocommerce_open_item_wrapper', 20 );

			add_action(    'woocommerce_after_subcategory',				'organic_beauty_woocommerce_close_item_wrapper', 20 );
			add_action(    'woocommerce_after_shop_loop_item',			'organic_beauty_woocommerce_close_item_wrapper', 20 );

			add_action(    'woocommerce_after_shop_loop_item_title',	'organic_beauty_woocommerce_after_shop_loop_item_title', 7);

			add_action(    'woocommerce_after_subcategory_title',		'organic_beauty_woocommerce_after_subcategory_title', 10 );

			add_action(    'the_title',									'organic_beauty_woocommerce_the_title');

			// Wrap category title into link
			remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );
			add_action(		'woocommerce_shop_loop_subcategory_title',  'organic_beauty_woocommerce_shop_loop_subcategory_title', 9, 1);

			// Remove link around product item
			remove_action('woocommerce_before_shop_loop_item',			'woocommerce_template_loop_product_link_open', 10);
			remove_action('woocommerce_after_shop_loop_item',			'woocommerce_template_loop_product_link_close', 5);
			// Remove link around product category
			remove_action('woocommerce_before_subcategory',				'woocommerce_template_loop_category_link_open', 10);
			remove_action('woocommerce_after_subcategory',				'woocommerce_template_loop_category_link_close', 10);
            // Replace product item title tag from h2 to h3
            remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
            add_action( 'woocommerce_shop_loop_item_title',    'tennisclub_woocommerce_template_loop_product_title', 10 );
		}

		if (organic_beauty_is_woocommerce_page()) {
			
			remove_action( 'woocommerce_sidebar', 						'woocommerce_get_sidebar', 10 );					// Remove WOOC sidebar
			
			remove_action( 'woocommerce_before_main_content',			'woocommerce_output_content_wrapper', 10);
			add_action(    'woocommerce_before_main_content',			'organic_beauty_woocommerce_wrapper_start', 10);
			
			remove_action( 'woocommerce_after_main_content',			'woocommerce_output_content_wrapper_end', 10);		
			add_action(    'woocommerce_after_main_content',			'organic_beauty_woocommerce_wrapper_end', 10);

			add_action(    'woocommerce_show_page_title',				'organic_beauty_woocommerce_show_page_title', 10);

			remove_action( 'woocommerce_single_product_summary',		'woocommerce_template_single_title', 5);		
			add_action(    'woocommerce_single_product_summary',		'organic_beauty_woocommerce_show_product_title', 5 );

            remove_action(  'woocommerce_single_product_summary',       'woocommerce_template_single_excerpt', 20);
            add_action(    'woocommerce_single_product_summary',		'organic_beauty_template_single_excerpt', 20 );

			add_action(    'woocommerce_before_shop_loop', 				'organic_beauty_woocommerce_before_shop_loop', 10 );

			if(function_exists('woocommerce_products_will_display') && 'subcategories' !== get_option( 'woocommerce_shop_page_display' )){
				remove_action( 'woocommerce_after_shop_loop',				'woocommerce_pagination', 10 );
				add_action(    'woocommerce_after_shop_loop',				'organic_beauty_woocommerce_pagination', 10 );
			}
			
			

			add_action(    'woocommerce_product_meta_end',				'organic_beauty_woocommerce_show_product_id', 10);

            if (organic_beauty_param_is_on(organic_beauty_get_custom_option('show_post_related'))) {
                add_filter('woocommerce_output_related_products_args', 'organic_beauty_woocommerce_output_related_products_args');
                add_filter('woocommerce_related_products_args', 'organic_beauty_woocommerce_related_products_args');
            } else {
                remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
            }

			add_filter(    'woocommerce_product_thumbnails_columns',	'organic_beauty_woocommerce_product_thumbnails_columns' );

			add_filter(    'get_product_search_form',					'organic_beauty_woocommerce_get_product_search_form' );
			// Set columns number for the products loop
			if ( ! get_theme_support( 'wc-product-grid-enable' ) ) {
				add_filter(    'post_class',								'organic_beauty_woocommerce_loop_shop_columns_class' );
				add_filter(    'product_cat_class',							'organic_beauty_woocommerce_loop_shop_columns_class', 10, 3 );
			}
			organic_beauty_enqueue_popup();
		}
	}
}



// Check if WooCommerce installed and activated
if ( !function_exists( 'organic_beauty_exists_woocommerce' ) ) {
	function organic_beauty_exists_woocommerce() {
		return class_exists('Woocommerce');
	}
}

// Return true, if current page is any woocommerce page
if ( !function_exists( 'organic_beauty_is_woocommerce_page' ) ) {
	function organic_beauty_is_woocommerce_page() {
		$rez = false;
		if (organic_beauty_exists_woocommerce()) {
			if (!organic_beauty_storage_empty('pre_query')) {
				$id = organic_beauty_storage_get_obj_property('pre_query', 'queried_object_id', 0);
				$rez = organic_beauty_storage_call_obj_method('pre_query', 'get', 'post_type')=='product'
						|| $id==wc_get_page_id('shop')
						|| $id==wc_get_page_id('cart')
						|| $id==wc_get_page_id('checkout')
						|| $id==wc_get_page_id('myaccount')
						|| organic_beauty_storage_call_obj_method('pre_query', 'is_tax', 'product_cat')
						|| organic_beauty_storage_call_obj_method('pre_query', 'is_tax', 'product_tag')
						|| organic_beauty_storage_call_obj_method('pre_query', 'is_tax', get_object_taxonomies('product'));
						
			} else
				$rez = is_shop() || is_product() || is_product_category() || is_product_tag() || is_product_taxonomy() || is_cart() || is_checkout() || is_account_page();
		}
		return $rez;
	}
}

// Filter to detect current page inheritance key
if ( !function_exists( 'organic_beauty_woocommerce_detect_inheritance_key' ) ) {
	//Handler of add_filter('organic_beauty_filter_detect_inheritance_key',	'organic_beauty_woocommerce_detect_inheritance_key', 9, 1);
	function organic_beauty_woocommerce_detect_inheritance_key($key) {
		if (!empty($key)) return $key;
		return organic_beauty_is_woocommerce_page() ? 'woocommerce' : '';
	}
}

// Filter to detect current template page id
if ( !function_exists( 'organic_beauty_woocommerce_detect_template_page_id' ) ) {
	//Handler of add_filter('organic_beauty_filter_detect_template_page_id',	'organic_beauty_woocommerce_detect_template_page_id', 9, 2);
	function organic_beauty_woocommerce_detect_template_page_id($id, $key) {
		if (!empty($id)) return $id;
		if ($key == 'woocommerce_cart')				$id = get_option('woocommerce_cart_page_id');
		else if ($key == 'woocommerce_checkout')	$id = get_option('woocommerce_checkout_page_id');
		else if ($key == 'woocommerce_account')		$id = get_option('woocommerce_account_page_id');
		else if ($key == 'woocommerce')				$id = get_option('woocommerce_shop_page_id');
		return $id;
	}
}

// Filter to detect current page type (slug)
if ( !function_exists( 'organic_beauty_woocommerce_get_blog_type' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_blog_type',	'organic_beauty_woocommerce_get_blog_type', 9, 2);
	function organic_beauty_woocommerce_get_blog_type($page, $query=null) {
		if (!empty($page)) return $page;
		
		if (is_shop()) 					$page = 'woocommerce_shop';
		else if ($query && $query->get('post_type')=='product' || is_product())		$page = 'woocommerce_product';
		else if ($query && $query->get('product_tag')!='' || is_product_tag())		$page = 'woocommerce_tag';
		else if ($query && $query->get('product_cat')!='' || is_product_category())	$page = 'woocommerce_category';
		else if (is_cart())				$page = 'woocommerce_cart';
		else if (is_checkout())			$page = 'woocommerce_checkout';
		else if (is_account_page())		$page = 'woocommerce_account';
		else if (is_woocommerce())		$page = 'woocommerce';
		return $page;
	}
}

// Filter to detect current page title
if ( !function_exists( 'organic_beauty_woocommerce_get_blog_title' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_blog_title',	'organic_beauty_woocommerce_get_blog_title', 9, 2);
	function organic_beauty_woocommerce_get_blog_title($title, $page) {
		if (!empty($title)) return $title;
		
		if ( organic_beauty_strpos($page, 'woocommerce')!==false ) {
			if ( $page == 'woocommerce_category' ) {
				$term = get_term_by( 'slug', get_query_var( 'product_cat' ), 'product_cat', OBJECT);
				$title = $term->name;
			} else if ( $page == 'woocommerce_tag' ) {
				$term = get_term_by( 'slug', get_query_var( 'product_tag' ), 'product_tag', OBJECT);
				$title = esc_html__('Tag:', 'organic-beauty') . ' ' . esc_html($term->name);
			} else if ( $page == 'woocommerce_cart' ) {
				$title = esc_html__( 'Your cart', 'organic-beauty' );
			} else if ( $page == 'woocommerce_checkout' ) {
				$title = esc_html__( 'Checkout', 'organic-beauty' );
			} else if ( $page == 'woocommerce_account' ) {
				$title = esc_html__( 'Account', 'organic-beauty' );
			} else if ( $page == 'woocommerce_product' ) {
				$title = organic_beauty_get_post_title();
			} else if (($page_id=get_option('woocommerce_shop_page_id')) > 0) {
				$title = organic_beauty_get_post_title($page_id);
			} else {
				$title = esc_html__( 'Shop', 'organic-beauty' );
			}
		}
		
		return $title;
	}
}

// Filter to detect stream page title
if ( !function_exists( 'organic_beauty_woocommerce_get_stream_page_title' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_stream_page_title',	'organic_beauty_woocommerce_get_stream_page_title', 9, 2);
	function organic_beauty_woocommerce_get_stream_page_title($title, $page) {
		if (!empty($title)) return $title;
		if (organic_beauty_strpos($page, 'woocommerce')!==false) {
			if (($page_id = organic_beauty_woocommerce_get_stream_page_id(0, $page)) > 0)
				$title = organic_beauty_get_post_title($page_id);
			else
				$title = esc_html__('Shop', 'organic-beauty');
		}
		return $title;
	}
}

// Filter to detect stream page ID
if ( !function_exists( 'organic_beauty_woocommerce_get_stream_page_id' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_stream_page_id',	'organic_beauty_woocommerce_get_stream_page_id', 9, 2);
	function organic_beauty_woocommerce_get_stream_page_id($id, $page) {
		if (!empty($id)) return $id;
		if (organic_beauty_strpos($page, 'woocommerce')!==false) {
			$id = get_option('woocommerce_shop_page_id');
		}
		return $id;
	}
}

// Filter to detect stream page link
if ( !function_exists( 'organic_beauty_woocommerce_get_stream_page_link' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_stream_page_link',	'organic_beauty_woocommerce_get_stream_page_link', 9, 2);
	function organic_beauty_woocommerce_get_stream_page_link($url, $page) {
		if (!empty($url)) return $url;
		if (organic_beauty_strpos($page, 'woocommerce')!==false) {
			$id = organic_beauty_woocommerce_get_stream_page_id(0, $page);
			if ($id) $url = get_permalink($id);
		}
		return $url;
	}
}

// Filter to detect current taxonomy
if ( !function_exists( 'organic_beauty_woocommerce_get_current_taxonomy' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_current_taxonomy',	'organic_beauty_woocommerce_get_current_taxonomy', 9, 2);
	function organic_beauty_woocommerce_get_current_taxonomy($tax, $page) {
		if (!empty($tax)) return $tax;
		if ( organic_beauty_strpos($page, 'woocommerce')!==false ) {
			$tax = 'product_cat';
		}
		return $tax;
	}
}

// Return taxonomy name (slug) if current page is this taxonomy page
if ( !function_exists( 'organic_beauty_woocommerce_is_taxonomy' ) ) {
	//Handler of add_filter('organic_beauty_filter_is_taxonomy',	'organic_beauty_woocommerce_is_taxonomy', 9, 2);
	function organic_beauty_woocommerce_is_taxonomy($tax, $query=null) {
		if (!empty($tax))
			return $tax;
		else
			return $query!==null && $query->get('product_cat')!='' || is_product_category() ? 'product_cat' : '';
	}
}

// Return false if current plugin not need theme orderby setting
if ( !function_exists( 'organic_beauty_woocommerce_orderby_need' ) ) {
	//Handler of add_filter('organic_beauty_filter_orderby_need',	'organic_beauty_woocommerce_orderby_need', 9, 1);
	function organic_beauty_woocommerce_orderby_need($need) {
		if ($need == false || organic_beauty_storage_empty('pre_query'))
			return $need;
		else {
			return organic_beauty_storage_call_obj_method('pre_query', 'get', 'post_type')!='product'
					&& organic_beauty_storage_call_obj_method('pre_query', 'get', 'product_cat')==''
					&& organic_beauty_storage_call_obj_method('pre_query', 'get', 'product_tag')=='';
		}
	}
}

// Add custom post type into list
if ( !function_exists( 'organic_beauty_woocommerce_list_post_types' ) ) {
	//Handler of add_filter('organic_beauty_filter_list_post_types', 	'organic_beauty_woocommerce_list_post_types', 10, 1);
	function organic_beauty_woocommerce_list_post_types($list) {
		$list = is_array($list) ? $list : array();
		$list['product'] = esc_html__('Products', 'organic-beauty');
		return $list;
	}
}



// Enqueue WooCommerce custom styles
if ( !function_exists( 'organic_beauty_woocommerce_frontend_scripts' ) ) {
	//Handler of add_action( 'organic_beauty_action_add_styles', 'organic_beauty_woocommerce_frontend_scripts' );
	function organic_beauty_woocommerce_frontend_scripts() {
		if (organic_beauty_is_woocommerce_page() || organic_beauty_get_custom_option('show_cart')=='always')
			if (file_exists(organic_beauty_get_file_dir('css/plugin.woocommerce.css')))
				wp_enqueue_style( 'organic-beauty-plugin-woocommerce-style',  organic_beauty_get_file_url('css/plugin.woocommerce.css'), array(), null );
	}
}

// Before main content
if ( !function_exists( 'organic_beauty_woocommerce_wrapper_start' ) ) {
	//Handler of add_action('woocommerce_before_main_content', 'organic_beauty_woocommerce_wrapper_start', 10);
	function organic_beauty_woocommerce_wrapper_start() {
		if (is_product() || is_cart() || is_checkout() || is_account_page()) {
			?>
			<article class="post_item post_item_single post_item_product">
			<?php
		} else {
			?>
			<div class="list_products shop_mode_<?php echo !organic_beauty_storage_empty('shop_mode') ? organic_beauty_storage_get('shop_mode') : 'thumbs'; ?>">
			<?php
		}
	}
}

// After main content
if ( !function_exists( 'organic_beauty_woocommerce_wrapper_end' ) ) {
	//Handler of add_action('woocommerce_after_main_content', 'organic_beauty_woocommerce_wrapper_end', 10);
	function organic_beauty_woocommerce_wrapper_end() {
		if (is_product() || is_cart() || is_checkout() || is_account_page()) {
			?>
			</article>	<!-- .post_item -->
			<?php
		} else {
			?>
			</div>	<!-- .list_products -->
			<?php
		}
	}
}

// Check to show page title
if ( !function_exists( 'organic_beauty_woocommerce_show_page_title' ) ) {
	//Handler of add_action('woocommerce_show_page_title', 'organic_beauty_woocommerce_show_page_title', 10);
	function organic_beauty_woocommerce_show_page_title($defa=true) {
		return organic_beauty_get_custom_option('show_page_title')=='no';
	}
}

// Check to show product title
if ( !function_exists( 'organic_beauty_woocommerce_show_product_title' ) ) {
	//Handler of add_action( 'woocommerce_single_product_summary', 'organic_beauty_woocommerce_show_product_title', 5 );
	function organic_beauty_woocommerce_show_product_title() {
		if (organic_beauty_get_custom_option('show_post_title')=='yes' || organic_beauty_get_custom_option('show_page_title')=='no') {
			wc_get_template( 'single-product/title.php' );
		}
	}
}

// New product excerpt with video shortcode
if ( !function_exists( 'organic_beauty_template_single_excerpt' ) ) {
    //Handler of add_action(    'woocommerce_single_product_summary',		'organic_beauty_template_single_excerpt', 20 );
    function organic_beauty_template_single_excerpt() {
        if ( ! defined( 'ABSPATH' ) ) {
            exit; // Exit if accessed directly
        }
        global $post;
        if ( ! $post->post_excerpt ) {
            return;
        }
        ?>
        <div itemprop="description">
            <?php echo organic_beauty_substitute_all(apply_filters( 'woocommerce_short_description', $post->post_excerpt )); ?>
        </div>
    <?php
    }
}

// Add list mode buttons
if ( !function_exists( 'organic_beauty_woocommerce_before_shop_loop' ) ) {
	//Handler of add_action( 'woocommerce_before_shop_loop', 'organic_beauty_woocommerce_before_shop_loop', 10 );
	function organic_beauty_woocommerce_before_shop_loop() {
		if (organic_beauty_get_custom_option('show_mode_buttons')=='yes') {
			echo '<div class="mode_buttons"><form action="' . esc_url(organic_beauty_get_current_url()) . '" method="post">'
				. '<input type="hidden" name="organic_beauty_shop_mode" value="'.esc_attr(organic_beauty_storage_get('shop_mode')).'" />'
				. '<a href="#" class="woocommerce_thumbs icon-th" title="'.esc_attr__('Show products as thumbs', 'organic-beauty').'"></a>'
				. '<a href="#" class="woocommerce_list icon-th-list" title="'.esc_attr__('Show products as list', 'organic-beauty').'"></a>'
				. '</form></div>';
		}
	}
}


// Open thumbs wrapper for categories and products
if ( !function_exists( 'organic_beauty_woocommerce_open_thumb_wrapper' ) ) {
	//Handler of add_action( 'woocommerce_before_subcategory_title', 'organic_beauty_woocommerce_open_thumb_wrapper', 9 );
	//Handler of add_action( 'woocommerce_before_shop_loop_item_title', 'organic_beauty_woocommerce_open_thumb_wrapper', 9 );
	function organic_beauty_woocommerce_open_thumb_wrapper($cat='') {
		organic_beauty_storage_set('in_product_item', true);
		?>
		<div class="post_item_wrap">
			<div class="post_featured">
				<div class="post_thumb">
					<a class="hover_icon hover_icon_link" href="<?php echo esc_url(is_object($cat) ? get_term_link($cat->slug, 'product_cat') : get_permalink()); ?>">
		<?php
	}
}

// Remove "Add to Cart Button"
if (!function_exists('organic_beauty_add_to_cart_button_remove')) {
	add_action('organic_beauty_action_after_init_theme', 'organic_beauty_add_to_cart_button_remove', 10);
	function organic_beauty_add_to_cart_button_remove() {
		remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
	}
}

// Add Product Buttons Wrap
if (!function_exists('organic_beauty_add_wrap')) {
	add_action('organic_beauty_action_after_init_theme', 'organic_beauty_add_woo_buttons_wrap', 10);
	function organic_beauty_add_woo_buttons_wrap() {
		add_action('woocommerce_before_shop_loop_item_title', 'organic_beauty_woo_buttons_wrap_start', 10);
		add_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 11);
		add_action('woocommerce_before_shop_loop_item_title', 'organic_beauty_woo_buttons_wrap_end', 12);
		function organic_beauty_woo_buttons_wrap_start(){ echo '<div class="woo_buttons_wrap">'; ?><a class="button" href="<?php echo esc_url(isset($cat) ? get_term_link($cat->slug, 'product_cat') : get_permalink()); ?>"><?php esc_html_e('View', 'organic-beauty'); ?></a><?php }
		function organic_beauty_woo_buttons_wrap_end(){ echo '</div>'; }
	}
}

// Add Product categories
if ( !function_exists( 'organic_beauty_woocommerce_product_cats' ) ) {
	function organic_beauty_woocommerce_product_cats()
	{
		global $post;
		$post_id = $post->ID;
		$post_cats = wp_get_post_terms($post_id, 'product_cat');
		$cats_out = '';
		$i = 0;
		if (!empty($post_cats)) {
			$count_cats = count($post_cats);
			foreach ($post_cats as $term) {
				$i++;
				$term_link = get_term_link($term, 'product_cat');
				$cats_out .= !empty($term_link) ?  '<a href="' . esc_url($term_link) . '">' . esc_html($term->name) . '</a>' : '';
				$cats_out .= $i < $count_cats ? ', ' : '';
				$cats_out .= count($post_cats) > 1 && $i < count($post_cats) ? '' : '';

			}
		}
		echo '<div class="product_cats">';
		echo(!empty($cats_out) ? $cats_out : '');
		echo '</div>';
	}
}
add_action('woocommerce_after_shop_loop_item_title', 'organic_beauty_woocommerce_product_cats', 1);

// Open item wrapper for categories and products
if ( !function_exists( 'organic_beauty_woocommerce_open_item_wrapper' ) ) {
	//Handler of add_action( 'woocommerce_before_subcategory_title', 'organic_beauty_woocommerce_open_item_wrapper', 20 );
	//Handler of add_action( 'woocommerce_before_shop_loop_item_title', 'organic_beauty_woocommerce_open_item_wrapper', 20 );
	function organic_beauty_woocommerce_open_item_wrapper($cat='') {
		?>
				</a>
			</div>
		</div>
		<div class="post_content">
		<?php
	}
}

// Close item wrapper for categories and products
if ( !function_exists( 'organic_beauty_woocommerce_close_item_wrapper' ) ) {
	//Handler of add_action( 'woocommerce_after_subcategory', 'organic_beauty_woocommerce_close_item_wrapper', 20 );
	//Handler of add_action( 'woocommerce_after_shop_loop_item', 'organic_beauty_woocommerce_close_item_wrapper', 20 );
	function organic_beauty_woocommerce_close_item_wrapper($cat='') {
		?>
			</div>
		</div>
		<?php
		organic_beauty_storage_set('in_product_item', false);
	}
}

// Add excerpt in output for the product in the list mode
if ( !function_exists( 'organic_beauty_woocommerce_after_shop_loop_item_title' ) ) {
	//Handler of add_action( 'woocommerce_after_shop_loop_item_title', 'organic_beauty_woocommerce_after_shop_loop_item_title', 7);
	function organic_beauty_woocommerce_after_shop_loop_item_title() {
		if (organic_beauty_storage_get('shop_mode') == 'list') {
		    $excerpt = apply_filters('the_excerpt', get_the_excerpt());
			echo '<div class="description">'.trim($excerpt).'</div>';
		}
	}
}

// Add excerpt in output for the product in the list mode
if ( !function_exists( 'organic_beauty_woocommerce_after_subcategory_title' ) ) {
	//Handler of add_action( 'woocommerce_after_subcategory_title', 'organic_beauty_woocommerce_after_subcategory_title', 10 );
	function organic_beauty_woocommerce_after_subcategory_title($category) {
		if (organic_beauty_storage_get('shop_mode') == 'list')
			echo '<div class="description">' . trim($category->description) . '</div>';
	}
}

// Add Product ID for single product
if ( !function_exists( 'organic_beauty_woocommerce_show_product_id' ) ) {
	//Handler of add_action( 'woocommerce_product_meta_end', 'organic_beauty_woocommerce_show_product_id', 10);
	function organic_beauty_woocommerce_show_product_id() {
		global $post, $product;
		echo '<span class="product_id">'.esc_html__('Product ID: ', 'organic-beauty') . '<span>' . ($post->ID) . '</span></span>';
	}
}

// Redefine number of related products
if ( !function_exists( 'organic_beauty_woocommerce_output_related_products_args' ) ) {
	//Handler of add_filter( 'woocommerce_output_related_products_args', 'organic_beauty_woocommerce_output_related_products_args' );
	function organic_beauty_woocommerce_output_related_products_args($args) {
		$ppp = $ccc = 0;
		if (organic_beauty_param_is_on(organic_beauty_get_custom_option('show_post_related'))) {
			$ccc_add = in_array(organic_beauty_get_custom_option('body_style'), array('fullwide', 'fullscreen')) ? 1 : 0;
			$ccc =  organic_beauty_get_custom_option('post_related_columns');
			$ccc = $ccc > 0 ? $ccc : (organic_beauty_param_is_off(organic_beauty_get_custom_option('show_sidebar_main')) ? 3+$ccc_add : 2+$ccc_add);
			$ppp = organic_beauty_get_custom_option('post_related_count');
			$ppp = $ppp > 0 ? $ppp : $ccc;
		}
		$args['posts_per_page'] = $ppp;
		$args['columns'] = $ccc;
		return $args;
	}
}

// Redefine post_type if number of related products == 0
if ( !function_exists( 'organic_beauty_woocommerce_related_products_args' ) ) {
	//Handler of add_filter( 'woocommerce_related_products_args', 'organic_beauty_woocommerce_related_products_args' );
	function organic_beauty_woocommerce_related_products_args($args) {
		if ($args['posts_per_page'] == 0)
			$args['post_type'] .= '_';
		return $args;
	}
}

// Number columns for product thumbnails
if ( !function_exists( 'organic_beauty_woocommerce_product_thumbnails_columns' ) ) {
	//Handler of add_filter( 'woocommerce_product_thumbnails_columns', 'organic_beauty_woocommerce_product_thumbnails_columns' );
	function organic_beauty_woocommerce_product_thumbnails_columns($cols) {
		return 4;
	}
}

// Add column class into product item in shop streampage
if ( !function_exists( 'organic_beauty_woocommerce_loop_shop_columns_class' ) ) {
	//Handler of add_filter( 'post_class', 'organic_beauty_woocommerce_loop_shop_columns_class' );
	//Handler of add_filter( 'product_cat_class', 'organic_beauty_woocommerce_loop_shop_columns_class', 10, 3 );
	function organic_beauty_woocommerce_loop_shop_columns_class($class, $class2='', $cat='') {
		global $woocommerce_loop;
		if (is_product()) {
			if (!empty($woocommerce_loop['columns']))
			$class[] = ' column-1_'.esc_attr($woocommerce_loop['columns']);
		} else if (!is_product() && !is_cart() && !is_checkout() && !is_account_page()) {
            $cols = function_exists('wc_get_default_products_per_row') ? wc_get_default_products_per_row() : 2;
            $class[] = ' column-1_' . $cols;
		}
		return $class;
	}
}

// Search form
if ( !function_exists( 'organic_beauty_woocommerce_get_product_search_form' ) ) {
	//Handler of add_filter( 'get_product_search_form', 'organic_beauty_woocommerce_get_product_search_form' );
	function organic_beauty_woocommerce_get_product_search_form($form) {
		return '
		<form role="search" method="get" class="search_form" action="' . esc_url(home_url('/')) . '">
			<input type="text" class="search_field" placeholder="' . esc_attr__('Search for products &hellip;', 'organic-beauty') . '" value="' . esc_attr(get_search_query()) . '" name="s" title="' . esc_attr__('Search for products:', 'organic-beauty') . '" /><button class="search_button icon-search" type="submit"></button>
			<input type="hidden" name="post_type" value="product" />
		</form>
		';
	}
}

// Wrap product title into link
if ( !function_exists( 'organic_beauty_woocommerce_the_title' ) ) {
	//Handler of add_filter( 'the_title', 'organic_beauty_woocommerce_the_title' );
	function organic_beauty_woocommerce_the_title($title) {
		if (organic_beauty_storage_get('in_product_item') && get_post_type()=='product') {
			$title = '<a href="'.esc_url(get_permalink()).'">'.($title).'</a>';
		}
		return $title;
	}
}

// Wrap category title into link
if ( !function_exists( 'organic_beauty_woocommerce_shop_loop_subcategory_title' ) ) {
	//Handler of the add_filter( 'woocommerce_shop_loop_subcategory_title', 'organic_beauty_woocommerce_shop_loop_subcategory_title' );
	function organic_beauty_woocommerce_shop_loop_subcategory_title($category) {
		$category->name = sprintf('<a href="%s">%s</a>', esc_url(get_term_link($category->slug, 'product_cat')), $category->name);
		?>
        <h2 class="woocommerce-loop-category__title">
			<?php
            organic_beauty_show_layout($category->name);
			
			if ( $category->count > 0 ) {
				echo apply_filters( 'woocommerce_subcategory_count_html', ' <mark class="count">(' . esc_html( $category->count ) . ')</mark>', $category ); // WPCS: XSS ok.
			}
			?>
        </h2>
		<?php
	}
}

// Replace H2 tag to H3 tag in product headings
if ( !function_exists( 'tennisclub_woocommerce_template_loop_product_title' ) ) {
    // Handler of add_action( 'woocommerce_shop_loop_item_title',    'tennisclub_woocommerce_template_loop_product_title', 10 );
    function tennisclub_woocommerce_template_loop_product_title() {
        echo '<h3>' . get_the_title() . '</h3>';
    }
}

// Show pagination links
if ( !function_exists( 'organic_beauty_woocommerce_pagination' ) ) {
	//Handler of add_filter( 'woocommerce_after_shop_loop', 'organic_beauty_woocommerce_pagination', 10 );
	function organic_beauty_woocommerce_pagination() {
		$style = organic_beauty_get_custom_option('blog_pagination');
		organic_beauty_show_pagination(array(
			'class' => 'pagination_wrap pagination_' . esc_attr($style),
			'style' => $style,
			'button_class' => '',
			'first_text'=> '',
			'last_text' => '',
			'prev_text' => '',
			'next_text' => '',
			'pages_in_group' => $style=='pages' ? 10 : 20
			)
		);
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'organic_beauty_woocommerce_required_plugins' ) ) {
	//Handler of add_filter('organic_beauty_filter_required_plugins',	'organic_beauty_woocommerce_required_plugins');
	function organic_beauty_woocommerce_required_plugins($list=array()) {
		if (in_array('woocommerce', (array)organic_beauty_storage_get('required_plugins')))
			$list[] = array(
					'name' 		=> 'WooCommerce',
					'slug' 		=> 'woocommerce',
					'required' 	=> false
				);

		return $list;
	}
}

// Show products navigation
if ( !function_exists( 'organic_beauty_woocommerce_show_post_navi' ) ) {
	//Handler of add_filter('organic_beauty_filter_show_post_navi', 'organic_beauty_woocommerce_show_post_navi');
	function organic_beauty_woocommerce_show_post_navi($show=false) {
		return $show || (organic_beauty_get_custom_option('show_page_title')=='yes' && is_single() && organic_beauty_is_woocommerce_page());
	}
}
?>