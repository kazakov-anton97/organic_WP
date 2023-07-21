<?php
/**
 * Organic Beauty Framework: shortcodes manipulations
 *
 * @package	organic_beauty
 * @since	organic_beauty 1.0
 */

// Disable direct call
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Theme init
if (!function_exists('organic_beauty_sc_theme_setup')) {
	add_action( 'organic_beauty_action_init_theme', 'organic_beauty_sc_theme_setup', 1 );
	function organic_beauty_sc_theme_setup() {
		// Add sc stylesheets
		add_action('organic_beauty_action_add_styles', 'organic_beauty_sc_add_styles', 1);
	}
}

if (!function_exists('organic_beauty_sc_theme_setup2')) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_sc_theme_setup2' );
	function organic_beauty_sc_theme_setup2() {

		if ( !is_admin() || isset($_POST['action']) ) {
			// Enable/disable shortcodes in excerpt
			add_filter('the_excerpt', 					'organic_beauty_sc_excerpt_shortcodes');
	
			// Prepare shortcodes in the content
			if (function_exists('organic_beauty_sc_prepare_content')) organic_beauty_sc_prepare_content();
		}

		// Add init script into shortcodes output in VC frontend editor
		add_filter('organic_beauty_shortcode_output', 'organic_beauty_sc_add_scripts', 10, 4);

		// AJAX: Send contact form data
		add_action('wp_ajax_send_form',			'organic_beauty_sc_form_send');
		add_action('wp_ajax_nopriv_send_form',	'organic_beauty_sc_form_send');

		// Show shortcodes list in admin editor
		add_action('media_buttons',				'organic_beauty_sc_selector_add_in_toolbar', 11);

        if(organic_beauty_exists_woocommerce()){
            add_action('organic_beauty_action_shortcodes_list', 			'organic_beauty_woocommerce_reg_shortcodes', 20);
            if (function_exists('organic_beauty_exists_visual_composer') && organic_beauty_exists_visual_composer())
                add_action('organic_beauty_action_shortcodes_list_vc',	'organic_beauty_woocommerce_reg_shortcodes_vc', 20);
        }

        if (organic_beauty_exists_revslider()) {
            add_filter( 'organic_beauty_filter_shortcodes_params',			'organic_beauty_revslider_shortcodes_params' );
        }

        // Registar shortcodes [trx_clients] and [trx_clients_item] in the shortcodes list
        add_action('organic_beauty_action_shortcodes_list',		'organic_beauty_clients_reg_shortcodes');
        if (function_exists('organic_beauty_exists_visual_composer') && organic_beauty_exists_visual_composer())
            add_action('organic_beauty_action_shortcodes_list_vc','organic_beauty_clients_reg_shortcodes_vc');

        // Register shortcodes [trx_services] and [trx_services_item]
        add_action('organic_beauty_action_shortcodes_list',		'organic_beauty_services_reg_shortcodes');
        if (function_exists('organic_beauty_exists_visual_composer') && organic_beauty_exists_visual_composer())
            add_action('organic_beauty_action_shortcodes_list_vc','organic_beauty_services_reg_shortcodes_vc');

        // Register shortcodes [trx_team] and [trx_team_item]
        add_action('organic_beauty_action_shortcodes_list',		'organic_beauty_team_reg_shortcodes');
        if (function_exists('organic_beauty_exists_visual_composer') && organic_beauty_exists_visual_composer())
            add_action('organic_beauty_action_shortcodes_list_vc','organic_beauty_team_reg_shortcodes_vc');

        // Register shortcodes [trx_testimonials] and [trx_testimonials_item]
        add_action('organic_beauty_action_shortcodes_list',		'organic_beauty_testimonials_reg_shortcodes');
        if (function_exists('organic_beauty_exists_visual_composer') && organic_beauty_exists_visual_composer())
            add_action('organic_beauty_action_shortcodes_list_vc','organic_beauty_testimonials_reg_shortcodes_vc');
    }
}


// Register shortcodes styles
if ( !function_exists( 'organic_beauty_sc_add_styles' ) ) {
	//add_action('organic_beauty_action_add_styles', 'organic_beauty_sc_add_styles', 1);
	function organic_beauty_sc_add_styles() {
		// Shortcodes
		wp_enqueue_style( 'organic-beauty-shortcodes-style',	trx_utils_get_file_url('shortcodes/theme.shortcodes.css'), array(), null );
	}
}


// Register shortcodes init scripts
if ( !function_exists( 'organic_beauty_sc_add_scripts' ) ) {
	//add_filter('organic_beauty_shortcode_output', 'organic_beauty_sc_add_scripts', 10, 4);
	function organic_beauty_sc_add_scripts($output, $tag='', $atts=array(), $content='') {

		if (organic_beauty_storage_empty('shortcodes_scripts_added')) {
			organic_beauty_storage_set('shortcodes_scripts_added', true);
			wp_enqueue_script( 'organic-beauty-shortcodes-script', trx_utils_get_file_url('shortcodes/theme.shortcodes.js'), array('jquery'), null, true );
		}
		
		return $output;
	}
}


/* Prepare text for shortcodes
-------------------------------------------------------------------------------- */

// Prepare shortcodes in content
if (!function_exists('organic_beauty_sc_prepare_content')) {
	function organic_beauty_sc_prepare_content() {
		if (function_exists('organic_beauty_sc_clear_around')) {
			$filters = array(
				array('organic_beauty', 'sc', 'clear', 'around'),
				array('widget', 'text'),
				array('the', 'excerpt'),
				array('the', 'content')
			);
			if (function_exists('organic_beauty_exists_woocommerce') && organic_beauty_exists_woocommerce()) {
				$filters[] = array('woocommerce', 'template', 'single', 'excerpt');
				$filters[] = array('woocommerce', 'short', 'description');
			}
			if (is_array($filters) && count($filters) > 0) {
				foreach ($filters as $flt)
					add_filter(join('_', $flt), 'organic_beauty_sc_clear_around', 1);	// Priority 1 to clear spaces before do_shortcodes()
			}
		}
	}
}

// Enable/Disable shortcodes in the excerpt
if (!function_exists('organic_beauty_sc_excerpt_shortcodes')) {
	//add_filter('the_excerpt', 'organic_beauty_sc_excerpt_shortcodes');
	function organic_beauty_sc_excerpt_shortcodes($content) {
		if (!empty($content)) {
			$content = do_shortcode($content);
		}
		return $content;
	}
}



/*
// Remove spaces and line breaks between close and open shortcode brackets ][:
[trx_columns]
	[trx_column_item]Column text ...[/trx_column_item]
	[trx_column_item]Column text ...[/trx_column_item]
	[trx_column_item]Column text ...[/trx_column_item]
[/trx_columns]

convert to

[trx_columns][trx_column_item]Column text ...[/trx_column_item][trx_column_item]Column text ...[/trx_column_item][trx_column_item]Column text ...[/trx_column_item][/trx_columns]
*/
if (!function_exists('organic_beauty_sc_clear_around')) {
	function organic_beauty_sc_clear_around($content) {
		if (!empty($content)) $content = preg_replace("/\](\s|\n|\r)*\[/", "][", $content);
		return $content;
	}
}






/* Shortcodes support utils
---------------------------------------------------------------------- */

// Organic Beauty shortcodes load scripts
if (!function_exists('organic_beauty_sc_load_scripts')) {
	function organic_beauty_sc_load_scripts() {
		static $loaded = false;
		if (!$loaded) {
			wp_enqueue_script( 'organic-beauty-shortcodes_admin-script', trx_utils_get_file_url('shortcodes/shortcodes_admin.js'), array('jquery'), null, true );
			wp_enqueue_script( 'organic-beauty-selection-script',  organic_beauty_get_file_url('js/jquery.selection.js'), array('jquery'), null, true );
			wp_localize_script( 'organic-beauty-shortcodes_admin-script', 'ORGANIC_BEAUTY_SHORTCODES_DATA', organic_beauty_storage_get('shortcodes') );
			$loaded = true;
		}
	}
}

// Organic Beauty shortcodes prepare scripts
if (!function_exists('organic_beauty_sc_prepare_scripts')) {
	function organic_beauty_sc_prepare_scripts() {
		static $prepared = false;
		if (!$prepared) {
			organic_beauty_storage_set_array('js_vars', 'shortcodes_cp', is_admin() ? (!organic_beauty_storage_empty('to_colorpicker') ? organic_beauty_storage_get('to_colorpicker') : 'wp') : 'custom');	// wp | tiny | custom
			$prepared = true;
		}
	}
}

// Show shortcodes list in admin editor
if (!function_exists('organic_beauty_sc_selector_add_in_toolbar')) {
	//add_action('media_buttons','organic_beauty_sc_selector_add_in_toolbar', 11);
	function organic_beauty_sc_selector_add_in_toolbar(){

		if ( !organic_beauty_options_is_used() ) return;

		organic_beauty_sc_load_scripts();
		organic_beauty_sc_prepare_scripts();

		$shortcodes = organic_beauty_storage_get('shortcodes');
		$shortcodes_list = '<select class="sc_selector"><option value="">&nbsp;'.esc_html__('- Select Shortcode -', 'organic-beauty').'&nbsp;</option>';

		if (is_array($shortcodes) && count($shortcodes) > 0) {
			foreach ($shortcodes as $idx => $sc) {
				$shortcodes_list .= '<option value="'.esc_attr($idx).'" title="'.esc_attr($sc['desc']).'">'.esc_html($sc['title']).'</option>';
			}
		}

		$shortcodes_list .= '</select>';

		organic_beauty_show_layout($shortcodes_list);
	}
}

// Register shortcodes to the internal builder
//------------------------------------------------------------------------
if ( !function_exists( 'organic_beauty_woocommerce_reg_shortcodes' ) ) {
    //Handler of add_action('organic_beauty_action_shortcodes_list', 'organic_beauty_woocommerce_reg_shortcodes', 20);
    function organic_beauty_woocommerce_reg_shortcodes() {

        // WooCommerce - Cart
        organic_beauty_sc_map("woocommerce_cart", array(
                "title" => esc_html__("Woocommerce: Cart", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: show Cart page", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array()
            )
        );

        // WooCommerce - Checkout
        organic_beauty_sc_map("woocommerce_checkout", array(
                "title" => esc_html__("Woocommerce: Checkout", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: show Checkout page", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array()
            )
        );

        // WooCommerce - My Account
        organic_beauty_sc_map("woocommerce_my_account", array(
                "title" => esc_html__("Woocommerce: My Account", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: show My Account page", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array()
            )
        );

        // WooCommerce - Order Tracking
        organic_beauty_sc_map("woocommerce_order_tracking", array(
                "title" => esc_html__("Woocommerce: Order Tracking", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: show Order Tracking page", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array()
            )
        );

        // WooCommerce - Shop Messages
        organic_beauty_sc_map("shop_messages", array(
                "title" => esc_html__("Woocommerce: Shop Messages", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: show shop messages", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array()
            )
        );

        // WooCommerce - Product Page
        organic_beauty_sc_map("product_page", array(
                "title" => esc_html__("Woocommerce: Product Page", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: display single product page", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array(
                    "sku" => array(
                        "title" => esc_html__("SKU", 'organic-beauty'),
                        "desc" => wp_kses_data( __("SKU code of displayed product", 'organic-beauty') ),
                        "value" => "",
                        "type" => "text"
                    ),
                    "id" => array(
                        "title" => esc_html__("ID", 'organic-beauty'),
                        "desc" => wp_kses_data( __("ID of displayed product", 'organic-beauty') ),
                        "value" => "",
                        "type" => "text"
                    ),
                    "posts_per_page" => array(
                        "title" => esc_html__("Number", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "value" => "1",
                        "min" => 1,
                        "type" => "spinner"
                    ),
                    "post_type" => array(
                        "title" => esc_html__("Post type", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Post type for the WP query (leave 'product')", 'organic-beauty') ),
                        "value" => "product",
                        "type" => "text"
                    ),
                    "post_status" => array(
                        "title" => esc_html__("Post status", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Display posts only with this status", 'organic-beauty') ),
                        "value" => "publish",
                        "type" => "select",
                        "options" => array(
                            "publish" => esc_html__('Publish', 'organic-beauty'),
                            "protected" => esc_html__('Protected', 'organic-beauty'),
                            "private" => esc_html__('Private', 'organic-beauty'),
                            "pending" => esc_html__('Pending', 'organic-beauty'),
                            "draft" => esc_html__('Draft', 'organic-beauty')
                        )
                    )
                )
            )
        );

        // WooCommerce - Product
        organic_beauty_sc_map("product", array(
                "title" => esc_html__("Woocommerce: Product", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: display one product", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array(
                    "sku" => array(
                        "title" => esc_html__("SKU", 'organic-beauty'),
                        "desc" => wp_kses_data( __("SKU code of displayed product", 'organic-beauty') ),
                        "value" => "",
                        "type" => "text"
                    ),
                    "id" => array(
                        "title" => esc_html__("ID", 'organic-beauty'),
                        "desc" => wp_kses_data( __("ID of displayed product", 'organic-beauty') ),
                        "value" => "",
                        "type" => "text"
                    )
                )
            )
        );

        // WooCommerce - Best Selling Products
        organic_beauty_sc_map("best_selling_products", array(
                "title" => esc_html__("Woocommerce: Best Selling Products", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: show best selling products", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array(
                    "per_page" => array(
                        "title" => esc_html__("Number", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 1,
                        "type" => "spinner"
                    ),
                    "columns" => array(
                        "title" => esc_html__("Columns", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 2,
                        "max" => 4,
                        "type" => "spinner"
                    )
                )
            )
        );

        // WooCommerce - Recent Products
        organic_beauty_sc_map("recent_products", array(
                "title" => esc_html__("Woocommerce: Recent Products", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: show recent products", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array(
                    "per_page" => array(
                        "title" => esc_html__("Number", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 1,
                        "type" => "spinner"
                    ),
                    "columns" => array(
                        "title" => esc_html__("Columns", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 2,
                        "max" => 4,
                        "type" => "spinner"
                    ),
                    "orderby" => array(
                        "title" => esc_html__("Order by", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "date",
                        "type" => "select",
                        "options" => array(
                            "date" => esc_html__('Date', 'organic-beauty'),
                            "title" => esc_html__('Title', 'organic-beauty')
                        )
                    ),
                    "order" => array(
                        "title" => esc_html__("Order", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "desc",
                        "type" => "switch",
                        "size" => "big",
                        "options" => organic_beauty_get_sc_param('ordering')
                    )
                )
            )
        );

        // WooCommerce - Related Products
        organic_beauty_sc_map("related_products", array(
                "title" => esc_html__("Woocommerce: Related Products", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: show related products", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array(
                    "posts_per_page" => array(
                        "title" => esc_html__("Number", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 1,
                        "type" => "spinner"
                    ),
                    "columns" => array(
                        "title" => esc_html__("Columns", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 2,
                        "max" => 4,
                        "type" => "spinner"
                    ),
                    "orderby" => array(
                        "title" => esc_html__("Order by", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "date",
                        "type" => "select",
                        "options" => array(
                            "date" => esc_html__('Date', 'organic-beauty'),
                            "title" => esc_html__('Title', 'organic-beauty')
                        )
                    )
                )
            )
        );

        // WooCommerce - Featured Products
        organic_beauty_sc_map("featured_products", array(
                "title" => esc_html__("Woocommerce: Featured Products", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: show featured products", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array(
                    "per_page" => array(
                        "title" => esc_html__("Number", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 1,
                        "type" => "spinner"
                    ),
                    "columns" => array(
                        "title" => esc_html__("Columns", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 2,
                        "max" => 4,
                        "type" => "spinner"
                    ),
                    "orderby" => array(
                        "title" => esc_html__("Order by", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "date",
                        "type" => "select",
                        "options" => array(
                            "date" => esc_html__('Date', 'organic-beauty'),
                            "title" => esc_html__('Title', 'organic-beauty')
                        )
                    ),
                    "order" => array(
                        "title" => esc_html__("Order", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "desc",
                        "type" => "switch",
                        "size" => "big",
                        "options" => organic_beauty_get_sc_param('ordering')
                    )
                )
            )
        );

        // WooCommerce - Top Rated Products
        organic_beauty_sc_map("featured_products", array(
                "title" => esc_html__("Woocommerce: Top Rated Products", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: show top rated products", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array(
                    "per_page" => array(
                        "title" => esc_html__("Number", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 1,
                        "type" => "spinner"
                    ),
                    "columns" => array(
                        "title" => esc_html__("Columns", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 2,
                        "max" => 4,
                        "type" => "spinner"
                    ),
                    "orderby" => array(
                        "title" => esc_html__("Order by", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "date",
                        "type" => "select",
                        "options" => array(
                            "date" => esc_html__('Date', 'organic-beauty'),
                            "title" => esc_html__('Title', 'organic-beauty')
                        )
                    ),
                    "order" => array(
                        "title" => esc_html__("Order", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "desc",
                        "type" => "switch",
                        "size" => "big",
                        "options" => organic_beauty_get_sc_param('ordering')
                    )
                )
            )
        );

        // WooCommerce - Sale Products
        organic_beauty_sc_map("featured_products", array(
                "title" => esc_html__("Woocommerce: Sale Products", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: list products on sale", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array(
                    "per_page" => array(
                        "title" => esc_html__("Number", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 1,
                        "type" => "spinner"
                    ),
                    "columns" => array(
                        "title" => esc_html__("Columns", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 2,
                        "max" => 4,
                        "type" => "spinner"
                    ),
                    "orderby" => array(
                        "title" => esc_html__("Order by", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "date",
                        "type" => "select",
                        "options" => array(
                            "date" => esc_html__('Date', 'organic-beauty'),
                            "title" => esc_html__('Title', 'organic-beauty')
                        )
                    ),
                    "order" => array(
                        "title" => esc_html__("Order", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "desc",
                        "type" => "switch",
                        "size" => "big",
                        "options" => organic_beauty_get_sc_param('ordering')
                    )
                )
            )
        );

        // WooCommerce - Product Category
        organic_beauty_sc_map("product_category", array(
                "title" => esc_html__("Woocommerce: Products from category", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: list products in specified category(-ies)", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array(
                    "per_page" => array(
                        "title" => esc_html__("Number", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 1,
                        "type" => "spinner"
                    ),
                    "columns" => array(
                        "title" => esc_html__("Columns", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 2,
                        "max" => 4,
                        "type" => "spinner"
                    ),
                    "orderby" => array(
                        "title" => esc_html__("Order by", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "date",
                        "type" => "select",
                        "options" => array(
                            "date" => esc_html__('Date', 'organic-beauty'),
                            "title" => esc_html__('Title', 'organic-beauty')
                        )
                    ),
                    "order" => array(
                        "title" => esc_html__("Order", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "desc",
                        "type" => "switch",
                        "size" => "big",
                        "options" => organic_beauty_get_sc_param('ordering')
                    ),
                    "category" => array(
                        "title" => esc_html__("Categories", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Comma separated category slugs", 'organic-beauty') ),
                        "value" => '',
                        "type" => "text"
                    ),
                    "operator" => array(
                        "title" => esc_html__("Operator", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Categories operator", 'organic-beauty') ),
                        "value" => "IN",
                        "type" => "checklist",
                        "size" => "medium",
                        "options" => array(
                            "IN" => esc_html__('IN', 'organic-beauty'),
                            "NOT IN" => esc_html__('NOT IN', 'organic-beauty'),
                            "AND" => esc_html__('AND', 'organic-beauty')
                        )
                    )
                )
            )
        );

        // WooCommerce - Products
        organic_beauty_sc_map("products", array(
                "title" => esc_html__("Woocommerce: Products", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: list all products", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array(
                    "skus" => array(
                        "title" => esc_html__("SKUs", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Comma separated SKU codes of products", 'organic-beauty') ),
                        "value" => "",
                        "type" => "text"
                    ),
                    "ids" => array(
                        "title" => esc_html__("IDs", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Comma separated ID of products", 'organic-beauty') ),
                        "value" => "",
                        "type" => "text"
                    ),
                    "columns" => array(
                        "title" => esc_html__("Columns", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 2,
                        "max" => 4,
                        "type" => "spinner"
                    ),
                    "orderby" => array(
                        "title" => esc_html__("Order by", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "date",
                        "type" => "select",
                        "options" => array(
                            "date" => esc_html__('Date', 'organic-beauty'),
                            "title" => esc_html__('Title', 'organic-beauty')
                        )
                    ),
                    "order" => array(
                        "title" => esc_html__("Order", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "desc",
                        "type" => "switch",
                        "size" => "big",
                        "options" => organic_beauty_get_sc_param('ordering')
                    )
                )
            )
        );

        // WooCommerce - Product attribute
        organic_beauty_sc_map("product_attribute", array(
                "title" => esc_html__("Woocommerce: Products by Attribute", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: show products with specified attribute", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array(
                    "per_page" => array(
                        "title" => esc_html__("Number", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 1,
                        "type" => "spinner"
                    ),
                    "columns" => array(
                        "title" => esc_html__("Columns", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 2,
                        "max" => 4,
                        "type" => "spinner"
                    ),
                    "orderby" => array(
                        "title" => esc_html__("Order by", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "date",
                        "type" => "select",
                        "options" => array(
                            "date" => esc_html__('Date', 'organic-beauty'),
                            "title" => esc_html__('Title', 'organic-beauty')
                        )
                    ),
                    "order" => array(
                        "title" => esc_html__("Order", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "desc",
                        "type" => "switch",
                        "size" => "big",
                        "options" => organic_beauty_get_sc_param('ordering')
                    ),
                    "attribute" => array(
                        "title" => esc_html__("Attribute", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Attribute name", 'organic-beauty') ),
                        "value" => "",
                        "type" => "text"
                    ),
                    "filter" => array(
                        "title" => esc_html__("Filter", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Attribute value", 'organic-beauty') ),
                        "value" => "",
                        "type" => "text"
                    )
                )
            )
        );

        // WooCommerce - Products Categories
        organic_beauty_sc_map("product_categories", array(
                "title" => esc_html__("Woocommerce: Product Categories", 'organic-beauty'),
                "desc" => wp_kses_data( __("WooCommerce shortcode: show categories with products", 'organic-beauty') ),
                "decorate" => false,
                "container" => false,
                "params" => array(
                    "number" => array(
                        "title" => esc_html__("Number", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many categories showed", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 1,
                        "type" => "spinner"
                    ),
                    "columns" => array(
                        "title" => esc_html__("Columns", 'organic-beauty'),
                        "desc" => wp_kses_data( __("How many columns per row use for categories output", 'organic-beauty') ),
                        "value" => 4,
                        "min" => 2,
                        "max" => 4,
                        "type" => "spinner"
                    ),
                    "orderby" => array(
                        "title" => esc_html__("Order by", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "date",
                        "type" => "select",
                        "options" => array(
                            "date" => esc_html__('Date', 'organic-beauty'),
                            "title" => esc_html__('Title', 'organic-beauty')
                        )
                    ),
                    "order" => array(
                        "title" => esc_html__("Order", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "value" => "desc",
                        "type" => "switch",
                        "size" => "big",
                        "options" => organic_beauty_get_sc_param('ordering')
                    ),
                    "parent" => array(
                        "title" => esc_html__("Parent", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Parent category slug", 'organic-beauty') ),
                        "value" => "",
                        "type" => "text"
                    ),
                    "ids" => array(
                        "title" => esc_html__("IDs", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Comma separated ID of products", 'organic-beauty') ),
                        "value" => "",
                        "type" => "text"
                    ),
                    "hide_empty" => array(
                        "title" => esc_html__("Hide empty", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Hide empty categories", 'organic-beauty') ),
                        "value" => "yes",
                        "type" => "switch",
                        "options" => organic_beauty_get_sc_param('yes_no')
                    )
                )
            )
        );
    }
}

// Register shortcodes to the VC builder
//------------------------------------------------------------------------
if ( !function_exists( 'organic_beauty_woocommerce_reg_shortcodes_vc' ) ) {
    //Handler of add_action('organic_beauty_action_shortcodes_list_vc', 'organic_beauty_woocommerce_reg_shortcodes_vc');
    function organic_beauty_woocommerce_reg_shortcodes_vc() {

        if (false && function_exists('organic_beauty_exists_woocommerce') && organic_beauty_exists_woocommerce()) {

            // WooCommerce - Cart
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "woocommerce_cart",
                "name" => esc_html__("Cart", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: show cart page", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_wooc_cart',
                "class" => "trx_sc_alone trx_sc_woocommerce_cart",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => false,
                "params" => array(
                    array(
                        "param_name" => "dummy",
                        "heading" => esc_html__("Dummy data", 'organic-beauty'),
                        "description" => wp_kses_data( __("Dummy data - not used in shortcodes", 'organic-beauty') ),
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    )
                )
            ) );

            class WPBakeryShortCode_Woocommerce_Cart extends Organic_Beauty_Vc_ShortCodeAlone {}


            // WooCommerce - Checkout
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "woocommerce_checkout",
                "name" => esc_html__("Checkout", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: show checkout page", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_wooc_checkout',
                "class" => "trx_sc_alone trx_sc_woocommerce_checkout",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => false,
                "params" => array(
                    array(
                        "param_name" => "dummy",
                        "heading" => esc_html__("Dummy data", 'organic-beauty'),
                        "description" => wp_kses_data( __("Dummy data - not used in shortcodes", 'organic-beauty') ),
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    )
                )
            ) );

            class WPBakeryShortCode_Woocommerce_Checkout extends Organic_Beauty_Vc_ShortCodeAlone {}


            // WooCommerce - My Account
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "woocommerce_my_account",
                "name" => esc_html__("My Account", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: show my account page", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_wooc_my_account',
                "class" => "trx_sc_alone trx_sc_woocommerce_my_account",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => false,
                "params" => array(
                    array(
                        "param_name" => "dummy",
                        "heading" => esc_html__("Dummy data", 'organic-beauty'),
                        "description" => wp_kses_data( __("Dummy data - not used in shortcodes", 'organic-beauty') ),
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    )
                )
            ) );

            class WPBakeryShortCode_Woocommerce_My_Account extends Organic_Beauty_Vc_ShortCodeAlone {}


            // WooCommerce - Order Tracking
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "woocommerce_order_tracking",
                "name" => esc_html__("Order Tracking", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: show order tracking page", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_wooc_order_tracking',
                "class" => "trx_sc_alone trx_sc_woocommerce_order_tracking",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => false,
                "params" => array(
                    array(
                        "param_name" => "dummy",
                        "heading" => esc_html__("Dummy data", 'organic-beauty'),
                        "description" => wp_kses_data( __("Dummy data - not used in shortcodes", 'organic-beauty') ),
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    )
                )
            ) );

            class WPBakeryShortCode_Woocommerce_Order_Tracking extends Organic_Beauty_Vc_ShortCodeAlone {}


            // WooCommerce - Shop Messages
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "shop_messages",
                "name" => esc_html__("Shop Messages", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: show shop messages", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_wooc_shop_messages',
                "class" => "trx_sc_alone trx_sc_shop_messages",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => false,
                "params" => array(
                    array(
                        "param_name" => "dummy",
                        "heading" => esc_html__("Dummy data", 'organic-beauty'),
                        "description" => wp_kses_data( __("Dummy data - not used in shortcodes", 'organic-beauty') ),
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    )
                )
            ) );

            class WPBakeryShortCode_Shop_Messages extends Organic_Beauty_Vc_ShortCodeAlone {}


            // WooCommerce - Product Page
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "product_page",
                "name" => esc_html__("Product Page", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: display single product page", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_product_page',
                "class" => "trx_sc_single trx_sc_product_page",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => true,
                "params" => array(
                    array(
                        "param_name" => "sku",
                        "heading" => esc_html__("SKU", 'organic-beauty'),
                        "description" => wp_kses_data( __("SKU code of displayed product", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "id",
                        "heading" => esc_html__("ID", 'organic-beauty'),
                        "description" => wp_kses_data( __("ID of displayed product", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "posts_per_page",
                        "heading" => esc_html__("Number", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "1",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "post_type",
                        "heading" => esc_html__("Post type", 'organic-beauty'),
                        "description" => wp_kses_data( __("Post type for the WP query (leave 'product')", 'organic-beauty') ),
                        "class" => "",
                        "value" => "product",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "post_status",
                        "heading" => esc_html__("Post status", 'organic-beauty'),
                        "description" => wp_kses_data( __("Display posts only with this status", 'organic-beauty') ),
                        "class" => "",
                        "value" => array(
                            esc_html__('Publish', 'organic-beauty') => 'publish',
                            esc_html__('Protected', 'organic-beauty') => 'protected',
                            esc_html__('Private', 'organic-beauty') => 'private',
                            esc_html__('Pending', 'organic-beauty') => 'pending',
                            esc_html__('Draft', 'organic-beauty') => 'draft'
                        ),
                        "type" => "dropdown"
                    )
                )
            ) );

            class WPBakeryShortCode_Product_Page extends Organic_Beauty_Vc_ShortCodeSingle {}



            // WooCommerce - Product
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "product",
                "name" => esc_html__("Product", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: display one product", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_product',
                "class" => "trx_sc_single trx_sc_product",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => true,
                "params" => array(
                    array(
                        "param_name" => "sku",
                        "heading" => esc_html__("SKU", 'organic-beauty'),
                        "description" => wp_kses_data( __("Product's SKU code", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "id",
                        "heading" => esc_html__("ID", 'organic-beauty'),
                        "description" => wp_kses_data( __("Product's ID", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    )
                )
            ) );

            class WPBakeryShortCode_Product extends Organic_Beauty_Vc_ShortCodeSingle {}


            // WooCommerce - Best Selling Products
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "best_selling_products",
                "name" => esc_html__("Best Selling Products", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: show best selling products", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_best_selling_products',
                "class" => "trx_sc_single trx_sc_best_selling_products",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => true,
                "params" => array(
                    array(
                        "param_name" => "per_page",
                        "heading" => esc_html__("Number", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "4",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "columns",
                        "heading" => esc_html__("Columns", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "1",
                        "type" => "textfield"
                    )
                )
            ) );

            class WPBakeryShortCode_Best_Selling_Products extends Organic_Beauty_Vc_ShortCodeSingle {}



            // WooCommerce - Recent Products
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "recent_products",
                "name" => esc_html__("Recent Products", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: show recent products", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_recent_products',
                "class" => "trx_sc_single trx_sc_recent_products",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => true,
                "params" => array(
                    array(
                        "param_name" => "per_page",
                        "heading" => esc_html__("Number", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "4",
                        "type" => "textfield"

                    ),
                    array(
                        "param_name" => "columns",
                        "heading" => esc_html__("Columns", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "1",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "orderby",
                        "heading" => esc_html__("Order by", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array(
                            esc_html__('Date', 'organic-beauty') => 'date',
                            esc_html__('Title', 'organic-beauty') => 'title'
                        ),
                        "type" => "dropdown"
                    ),
                    array(
                        "param_name" => "order",
                        "heading" => esc_html__("Order", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array_flip((array)organic_beauty_get_sc_param('ordering')),
                        "type" => "dropdown"
                    )
                )
            ) );

            class WPBakeryShortCode_Recent_Products extends Organic_Beauty_Vc_ShortCodeSingle {}



            // WooCommerce - Related Products
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "related_products",
                "name" => esc_html__("Related Products", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: show related products", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_related_products',
                "class" => "trx_sc_single trx_sc_related_products",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => true,
                "params" => array(
                    array(
                        "param_name" => "posts_per_page",
                        "heading" => esc_html__("Number", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "4",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "columns",
                        "heading" => esc_html__("Columns", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "1",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "orderby",
                        "heading" => esc_html__("Order by", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array(
                            esc_html__('Date', 'organic-beauty') => 'date',
                            esc_html__('Title', 'organic-beauty') => 'title'
                        ),
                        "type" => "dropdown"
                    )
                )
            ) );

            class WPBakeryShortCode_Related_Products extends Organic_Beauty_Vc_ShortCodeSingle {}



            // WooCommerce - Featured Products
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "featured_products",
                "name" => esc_html__("Featured Products", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: show featured products", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_featured_products',
                "class" => "trx_sc_single trx_sc_featured_products",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => true,
                "params" => array(
                    array(
                        "param_name" => "per_page",
                        "heading" => esc_html__("Number", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "4",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "columns",
                        "heading" => esc_html__("Columns", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "1",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "orderby",
                        "heading" => esc_html__("Order by", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array(
                            esc_html__('Date', 'organic-beauty') => 'date',
                            esc_html__('Title', 'organic-beauty') => 'title'
                        ),
                        "type" => "dropdown"
                    ),
                    array(
                        "param_name" => "order",
                        "heading" => esc_html__("Order", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array_flip((array)organic_beauty_get_sc_param('ordering')),
                        "type" => "dropdown"
                    )
                )
            ) );

            class WPBakeryShortCode_Featured_Products extends Organic_Beauty_Vc_ShortCodeSingle {}



            // WooCommerce - Top Rated Products
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "top_rated_products",
                "name" => esc_html__("Top Rated Products", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: show top rated products", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_top_rated_products',
                "class" => "trx_sc_single trx_sc_top_rated_products",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => true,
                "params" => array(
                    array(
                        "param_name" => "per_page",
                        "heading" => esc_html__("Number", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "4",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "columns",
                        "heading" => esc_html__("Columns", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "1",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "orderby",
                        "heading" => esc_html__("Order by", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array(
                            esc_html__('Date', 'organic-beauty') => 'date',
                            esc_html__('Title', 'organic-beauty') => 'title'
                        ),
                        "type" => "dropdown"
                    ),
                    array(
                        "param_name" => "order",
                        "heading" => esc_html__("Order", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array_flip((array)organic_beauty_get_sc_param('ordering')),
                        "type" => "dropdown"
                    )
                )
            ) );

            class WPBakeryShortCode_Top_Rated_Products extends Organic_Beauty_Vc_ShortCodeSingle {}



            // WooCommerce - Sale Products
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "sale_products",
                "name" => esc_html__("Sale Products", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: list products on sale", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_sale_products',
                "class" => "trx_sc_single trx_sc_sale_products",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => true,
                "params" => array(
                    array(
                        "param_name" => "per_page",
                        "heading" => esc_html__("Number", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "4",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "columns",
                        "heading" => esc_html__("Columns", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "1",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "orderby",
                        "heading" => esc_html__("Order by", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array(
                            esc_html__('Date', 'organic-beauty') => 'date',
                            esc_html__('Title', 'organic-beauty') => 'title'
                        ),
                        "type" => "dropdown"
                    ),
                    array(
                        "param_name" => "order",
                        "heading" => esc_html__("Order", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array_flip((array)organic_beauty_get_sc_param('ordering')),
                        "type" => "dropdown"
                    )
                )
            ) );

            class WPBakeryShortCode_Sale_Products extends Organic_Beauty_Vc_ShortCodeSingle {}



            // WooCommerce - Product Category
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "product_category",
                "name" => esc_html__("Products from category", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: list products in specified category(-ies)", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_product_category',
                "class" => "trx_sc_single trx_sc_product_category",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => true,
                "params" => array(
                    array(
                        "param_name" => "per_page",
                        "heading" => esc_html__("Number", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "4",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "columns",
                        "heading" => esc_html__("Columns", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "1",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "orderby",
                        "heading" => esc_html__("Order by", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array(
                            esc_html__('Date', 'organic-beauty') => 'date',
                            esc_html__('Title', 'organic-beauty') => 'title'
                        ),
                        "type" => "dropdown"
                    ),
                    array(
                        "param_name" => "order",
                        "heading" => esc_html__("Order", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array_flip((array)organic_beauty_get_sc_param('ordering')),
                        "type" => "dropdown"
                    ),
                    array(
                        "param_name" => "category",
                        "heading" => esc_html__("Categories", 'organic-beauty'),
                        "description" => wp_kses_data( __("Comma separated category slugs", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "operator",
                        "heading" => esc_html__("Operator", 'organic-beauty'),
                        "description" => wp_kses_data( __("Categories operator", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array(
                            esc_html__('IN', 'organic-beauty') => 'IN',
                            esc_html__('NOT IN', 'organic-beauty') => 'NOT IN',
                            esc_html__('AND', 'organic-beauty') => 'AND'
                        ),
                        "type" => "dropdown"
                    )
                )
            ) );

            class WPBakeryShortCode_Product_Category extends Organic_Beauty_Vc_ShortCodeSingle {}



            // WooCommerce - Products
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "products",
                "name" => esc_html__("Products", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: list all products", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_products',
                "class" => "trx_sc_single trx_sc_products",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => true,
                "params" => array(
                    array(
                        "param_name" => "skus",
                        "heading" => esc_html__("SKUs", 'organic-beauty'),
                        "description" => wp_kses_data( __("Comma separated SKU codes of products", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "ids",
                        "heading" => esc_html__("IDs", 'organic-beauty'),
                        "description" => wp_kses_data( __("Comma separated ID of products", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "columns",
                        "heading" => esc_html__("Columns", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "1",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "orderby",
                        "heading" => esc_html__("Order by", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array(
                            esc_html__('Date', 'organic-beauty') => 'date',
                            esc_html__('Title', 'organic-beauty') => 'title'
                        ),
                        "type" => "dropdown"
                    ),
                    array(
                        "param_name" => "order",
                        "heading" => esc_html__("Order", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array_flip((array)organic_beauty_get_sc_param('ordering')),
                        "type" => "dropdown"
                    )
                )
            ) );

            class WPBakeryShortCode_Products extends Organic_Beauty_Vc_ShortCodeSingle {}




            // WooCommerce - Product Attribute
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "product_attribute",
                "name" => esc_html__("Products by Attribute", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: show products with specified attribute", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_product_attribute',
                "class" => "trx_sc_single trx_sc_product_attribute",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => true,
                "params" => array(
                    array(
                        "param_name" => "per_page",
                        "heading" => esc_html__("Number", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many products showed", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "4",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "columns",
                        "heading" => esc_html__("Columns", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many columns per row use for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "1",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "orderby",
                        "heading" => esc_html__("Order by", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array(
                            esc_html__('Date', 'organic-beauty') => 'date',
                            esc_html__('Title', 'organic-beauty') => 'title'
                        ),
                        "type" => "dropdown"
                    ),
                    array(
                        "param_name" => "order",
                        "heading" => esc_html__("Order", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array_flip((array)organic_beauty_get_sc_param('ordering')),
                        "type" => "dropdown"
                    ),
                    array(
                        "param_name" => "attribute",
                        "heading" => esc_html__("Attribute", 'organic-beauty'),
                        "description" => wp_kses_data( __("Attribute name", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "filter",
                        "heading" => esc_html__("Filter", 'organic-beauty'),
                        "description" => wp_kses_data( __("Attribute value", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    )
                )
            ) );

            class WPBakeryShortCode_Product_Attribute extends Organic_Beauty_Vc_ShortCodeSingle {}



            // WooCommerce - Products Categories
            //-------------------------------------------------------------------------------------

            vc_map( array(
                "base" => "product_categories",
                "name" => esc_html__("Product Categories", 'organic-beauty'),
                "description" => wp_kses_data( __("WooCommerce shortcode: show categories with products", 'organic-beauty') ),
                "category" => esc_html__('WooCommerce', 'organic-beauty'),
                'icon' => 'icon_trx_product_categories',
                "class" => "trx_sc_single trx_sc_product_categories",
                "content_element" => true,
                "is_container" => false,
                "show_settings_on_create" => true,
                "params" => array(
                    array(
                        "param_name" => "number",
                        "heading" => esc_html__("Number", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many categories showed", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "4",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "columns",
                        "heading" => esc_html__("Columns", 'organic-beauty'),
                        "description" => wp_kses_data( __("How many columns per row use for categories output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "1",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "orderby",
                        "heading" => esc_html__("Order by", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array(
                            esc_html__('Date', 'organic-beauty') => 'date',
                            esc_html__('Title', 'organic-beauty') => 'title'
                        ),
                        "type" => "dropdown"
                    ),
                    array(
                        "param_name" => "order",
                        "heading" => esc_html__("Order", 'organic-beauty'),
                        "description" => wp_kses_data( __("Sorting order for products output", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => array_flip((array)organic_beauty_get_sc_param('ordering')),
                        "type" => "dropdown"
                    ),
                    array(
                        "param_name" => "parent",
                        "heading" => esc_html__("Parent", 'organic-beauty'),
                        "description" => wp_kses_data( __("Parent category slug", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "date",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "ids",
                        "heading" => esc_html__("IDs", 'organic-beauty'),
                        "description" => wp_kses_data( __("Comma separated ID of products", 'organic-beauty') ),
                        "admin_label" => true,
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    ),
                    array(
                        "param_name" => "hide_empty",
                        "heading" => esc_html__("Hide empty", 'organic-beauty'),
                        "description" => wp_kses_data( __("Hide empty categories", 'organic-beauty') ),
                        "class" => "",
                        "value" => array("Hide empty" => "1" ),
                        "type" => "checkbox"
                    )
                )
            ) );

            class WPBakeryShortCode_Products_Categories extends Organic_Beauty_Vc_ShortCodeSingle {}

        }
    }
}

// Add RevSlider in the shortcodes params
if ( !function_exists( 'organic_beauty_revslider_shortcodes_params' ) ) {
    //Handler of add_filter( 'organic_beauty_filter_shortcodes_params',			'organic_beauty_revslider_shortcodes_params' );
    function organic_beauty_revslider_shortcodes_params($list=array()) {
        $list["revo_sliders"] = organic_beauty_get_list_revo_sliders();
        return $list;
    }
}

// ---------------------------------- [trx_clients] ---------------------------------------

if ( !function_exists( 'organic_beauty_sc_clients' ) ) {
    function organic_beauty_sc_clients($atts, $content=null){
        if (organic_beauty_in_shortcode_blogger()) return '';
        extract(organic_beauty_html_decode(shortcode_atts(array(
            // Individual params
            "style" => "clients-1",
            "columns" => 4,
            "slider" => "no",
            "slides_space" => 0,
            "controls" => "no",
            "interval" => "",
            "autoheight" => "no",
            "custom" => "no",
            "ids" => "",
            "cat" => "",
            "count" => 4,
            "offset" => "",
            "orderby" => "title",
            "order" => "asc",
            "title" => "",
            "subtitle" => "",
            "description" => "",
            "link_caption" => esc_html__('Learn more', 'organic-beauty'),
            "link" => '',
            "scheme" => '',
            // Common params
            "id" => "",
            "class" => "",
            "animation" => "",
            "css" => "",
            "width" => "",
            "height" => "",
            "top" => "",
            "bottom" => "",
            "left" => "",
            "right" => ""
        ), $atts)));

        if (empty($id)) $id = "sc_clients_".str_replace('.', '', mt_rand());
        if (empty($width)) $width = "100%";
        if (!empty($height) && organic_beauty_param_is_on($autoheight)) $autoheight = "no";
        if (empty($interval)) $interval = mt_rand(5000, 10000);

        $class .= ($class ? ' ' : '') . organic_beauty_get_css_position_as_classes($top, $right, $bottom, $left);

        $ws = organic_beauty_get_css_dimensions_from_values($width);
        $hs = organic_beauty_get_css_dimensions_from_values('', $height);
        $css .= ($hs) . ($ws);

        if (organic_beauty_param_is_on($slider)) organic_beauty_enqueue_slider('swiper');

        $columns = max(1, min(12, $columns));
        $count = max(1, (int) $count);
        if (organic_beauty_param_is_off($custom) && $count < $columns) $columns = $count;
        organic_beauty_storage_set('sc_clients_data', array(
                'id'=>$id,
                'style'=>$style,
                'counter'=>0,
                'columns'=>$columns,
                'slider'=>$slider,
                'css_wh'=>$ws . $hs
            )
        );

        $output = '<div' . ($id ? ' id="'.esc_attr($id).'_wrap"' : '')
            . ' class="sc_clients_wrap'
            . ($scheme && !organic_beauty_param_is_off($scheme) && !organic_beauty_param_is_inherit($scheme) ? ' scheme_'.esc_attr($scheme) : '')
            .'">'
            . '<div' . ($id ? ' id="'.esc_attr($id).'"' : '')
            . ' class="sc_clients sc_clients_style_'.esc_attr($style)
            . ' ' . esc_attr(organic_beauty_get_template_property($style, 'container_classes'))
            . (!empty($class) ? ' '.esc_attr($class) : '')
            .'"'
            . ($css!='' ? ' style="'.esc_attr($css).'"' : '')
            . (!organic_beauty_param_is_off($animation) ? ' data-animation="'.esc_attr(organic_beauty_get_animation_classes($animation)).'"' : '')
            . '>'
            . (!empty($subtitle) ? '<h6 class="sc_clients_subtitle sc_item_subtitle">' . trim(organic_beauty_strmacros($subtitle)) . '</h6>' : '')
            . (!empty($title) ? '<h2 class="sc_clients_title sc_item_title' . (empty($description) ? ' sc_item_title_without_descr' : ' sc_item_title_with_descr') . '">' . trim(organic_beauty_strmacros($title)) . '</h2>' : '')
            . (!empty($description) ? '<div class="sc_clients_descr sc_item_descr">' . trim(organic_beauty_strmacros($description)) . '</div>' : '')
            . (organic_beauty_param_is_on($slider)
                ? ('<div class="sc_slider_swiper swiper-slider-container'
                    . ' ' . esc_attr(organic_beauty_get_slider_controls_classes($controls))
                    . (organic_beauty_param_is_on($autoheight) ? ' sc_slider_height_auto' : '')
                    . ($hs ? ' sc_slider_height_fixed' : '')
                    . '"'
                    . (!empty($width) && organic_beauty_strpos($width, '%')===false ? ' data-old-width="' . esc_attr($width) . '"' : '')
                    . (!empty($height) && organic_beauty_strpos($height, '%')===false ? ' data-old-height="' . esc_attr($height) . '"' : '')
                    . ((int) $interval > 0 ? ' data-interval="'.esc_attr($interval).'"' : '')
                    . ($columns > 1 ? ' data-slides-per-view="' . esc_attr($columns) . '"' : '')
                    . ($slides_space > 0 ? ' data-slides-space="' . esc_attr($slides_space) . '"' : '')
                    . ' data-slides-min-width="' . ($style=='clients-1' ? 100 : 220) . '"'
                    . '>'
                    . '<div class="slides swiper-wrapper">')
                : ($columns > 1
                    ? '<div class="sc_columns columns_wrap">'
                    : '')
            );

        if (organic_beauty_param_is_on($custom) && $content) {
            $output .= do_shortcode($content);
        } else {
            global $post;

            if (!empty($ids)) {
                $posts = explode(',', $ids);
                $count = count($posts);
            }

            $args = array(
                'post_type' => 'clients',
                'post_status' => 'publish',
                'posts_per_page' => $count,
                'ignore_sticky_posts' => true,
                'order' => $order=='asc' ? 'asc' : 'desc',
            );

            if ($offset > 0 && empty($ids)) {
                $args['offset'] = $offset;
            }

            $args = organic_beauty_query_add_sort_order($args, $orderby, $order);
            $args = organic_beauty_query_add_posts_and_cats($args, $ids, 'clients', $cat, 'clients_group');

            $query = new WP_Query( $args );

            $post_number = 0;

            while ( $query->have_posts() ) {
                $query->the_post();
                $post_number++;
                $args = array(
                    'layout' => $style,
                    'show' => false,
                    'number' => $post_number,
                    'posts_on_page' => ($count > 0 ? $count : $query->found_posts),
                    "descr" => organic_beauty_get_custom_option('post_excerpt_maxlength'.($columns > 1 ? '_masonry' : '')),
                    "orderby" => $orderby,
                    'content' => false,
                    'terms_list' => false,
                    'columns_count' => $columns,
                    'slider' => $slider,
                    'tag_id' => $id ? $id . '_' . $post_number : '',
                    'tag_class' => '',
                    'tag_animation' => '',
                    'tag_css' => '',
                    'tag_css_wh' => $ws . $hs
                );
                $post_data = organic_beauty_get_post_data($args);
                $post_meta = get_post_meta($post_data['post_id'], organic_beauty_storage_get('options_prefix') . '_post_options', true);
                $thumb_sizes = organic_beauty_get_thumb_sizes(array('layout' => $style));
                $args['client_name'] = $post_meta['client_name'];
                $args['client_position'] = $post_meta['client_position'];
                $args['client_image'] = $post_data['post_thumb'];
                $args['client_link'] = organic_beauty_param_is_on('client_show_link')
                    ? (!empty($post_meta['client_link']) ? $post_meta['client_link'] : $post_data['post_link'])
                    : '';
                $output .= organic_beauty_show_post_layout($args, $post_data);
            }
            wp_reset_postdata();
        }

        if (organic_beauty_param_is_on($slider)) {
            $output .= '</div>'
                . '<div class="sc_slider_controls_wrap"><a class="sc_slider_prev" href="#"></a><a class="sc_slider_next" href="#"></a></div>'
                . '<div class="sc_slider_pagination_wrap"></div>'
                . '</div>';
        } else if ($columns > 1) {
            $output .= '</div>';
        }

        $output .= (!empty($link) ? '<div class="sc_clients_button sc_item_button">'.organic_beauty_do_shortcode('[trx_button link="'.esc_url($link).'" icon="icon-right"]'.esc_html($link_caption).'[/trx_button]').'</div>' : '')
            . '</div><!-- /.sc_clients -->'
            . '</div><!-- /.sc_clients_wrap -->';

        // Add template specific scripts and styles
        do_action('organic_beauty_action_blog_scripts', $style);

        return apply_filters('organic_beauty_shortcode_output', $output, 'trx_clients', $atts, $content);
    }
    add_shortcode('trx_clients', 'organic_beauty_sc_clients');
}


if ( !function_exists( 'organic_beauty_sc_clients_item' ) ) {
    function organic_beauty_sc_clients_item($atts, $content=null) {
        if (organic_beauty_in_shortcode_blogger()) return '';
        extract(organic_beauty_html_decode(shortcode_atts( array(
            // Individual params
            "name" => "",
            "position" => "",
            "image" => "",
            "link" => "",
            // Common params
            "id" => "",
            "class" => "",
            "animation" => "",
            "css" => ""
        ), $atts)));

        organic_beauty_storage_inc_array('sc_clients_data', 'counter');

        $id = $id ? $id : (organic_beauty_storage_get_array('sc_clients_data', 'id') ? organic_beauty_storage_get_array('sc_clients_data', 'id') . '_' . organic_beauty_storage_get_array('sc_clients_data', 'counter') : '');

        $descr = trim(chop(do_shortcode($content)));

        $thumb_sizes = organic_beauty_get_thumb_sizes(array('layout' => organic_beauty_storage_get_array('sc_clients_data', 'style')));

        if ($image > 0) {
            $attach = wp_get_attachment_image_src( $image, 'full' );
            if (isset($attach[0]) && $attach[0]!='')
                $image = $attach[0];
        }
        $image = organic_beauty_get_resized_image_tag($image, $thumb_sizes['w'], $thumb_sizes['h']);

        $post_data = array(
            'post_title' => $name,
            'post_excerpt' => $descr
        );
        $args = array(
            'layout' => organic_beauty_storage_get_array('sc_clients_data', 'style'),
            'number' => organic_beauty_storage_get_array('sc_clients_data', 'counter'),
            'columns_count' => organic_beauty_storage_get_array('sc_clients_data', 'columns'),
            'slider' => organic_beauty_storage_get_array('sc_clients_data', 'slider'),
            'show' => false,
            'descr'  => 0,
            'tag_id' => $id,
            'tag_class' => $class,
            'tag_animation' => $animation,
            'tag_css' => $css,
            'tag_css_wh' => organic_beauty_storage_get_array('sc_clients_data', 'css_wh'),
            'client_position' => $position,
            'client_link' => $link,
            'client_image' => $image
        );
        $output = organic_beauty_show_post_layout($args, $post_data);
        return apply_filters('organic_beauty_shortcode_output', $output, 'trx_clients_item', $atts, $content);
    }
    add_shortcode('trx_clients_item', 'organic_beauty_sc_clients_item');
}
// ---------------------------------- [/trx_clients] ---------------------------------------



// Add [trx_clients] and [trx_clients_item] in the shortcodes list
if (!function_exists('organic_beauty_clients_reg_shortcodes')) {
    //Handler of add_filter('organic_beauty_action_shortcodes_list',	'organic_beauty_clients_reg_shortcodes');
    function organic_beauty_clients_reg_shortcodes() {
        if (organic_beauty_storage_isset('shortcodes')) {

            $users = organic_beauty_get_list_users();
            $members = organic_beauty_get_list_posts(false, array(
                    'post_type'=>'clients',
                    'orderby'=>'title',
                    'order'=>'asc',
                    'return'=>'title'
                )
            );
            $clients_groups = organic_beauty_get_list_terms(false, 'clients_group');
            $clients_styles = organic_beauty_get_list_templates('clients');
            $controls 		= organic_beauty_get_list_slider_controls();

            organic_beauty_sc_map_after('trx_chat', array(

                // Clients
                "trx_clients" => array(
                    "title" => esc_html__("Clients", 'organic-beauty'),
                    "desc" => wp_kses_data( __("Insert clients list in your page (post)", 'organic-beauty') ),
                    "decorate" => true,
                    "container" => false,
                    "params" => array(
                        "title" => array(
                            "title" => esc_html__("Title", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Title for the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "subtitle" => array(
                            "title" => esc_html__("Subtitle", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Subtitle for the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "description" => array(
                            "title" => esc_html__("Description", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Short description for the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "textarea"
                        ),
                        "style" => array(
                            "title" => esc_html__("Clients style", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select style to display clients list", 'organic-beauty') ),
                            "value" => "clients-1",
                            "type" => "select",
                            "options" => $clients_styles
                        ),
                        "columns" => array(
                            "title" => esc_html__("Columns", 'organic-beauty'),
                            "desc" => wp_kses_data( __("How many columns use to show clients", 'organic-beauty') ),
                            "value" => 4,
                            "min" => 2,
                            "max" => 6,
                            "step" => 1,
                            "type" => "spinner"
                        ),
                        "scheme" => array(
                            "title" => esc_html__("Color scheme", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select color scheme for this block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "checklist",
                            "options" => organic_beauty_get_sc_param('schemes')
                        ),
                        "slider" => array(
                            "title" => esc_html__("Slider", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Use slider to show clients", 'organic-beauty') ),
                            "value" => "no",
                            "type" => "switch",
                            "options" => organic_beauty_get_sc_param('yes_no')
                        ),
                        "controls" => array(
                            "title" => esc_html__("Controls", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Slider controls style and position", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "divider" => true,
                            "value" => "no",
                            "type" => "checklist",
                            "dir" => "horizontal",
                            "options" => $controls
                        ),
                        "slides_space" => array(
                            "title" => esc_html__("Space between slides", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Size of space (in px) between slides", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "value" => 0,
                            "min" => 0,
                            "max" => 100,
                            "step" => 10,
                            "type" => "spinner"
                        ),
                        "interval" => array(
                            "title" => esc_html__("Slides change interval", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Slides change interval (in milliseconds: 1000ms = 1s)", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "value" => 7000,
                            "step" => 500,
                            "min" => 0,
                            "type" => "spinner"
                        ),
                        "autoheight" => array(
                            "title" => esc_html__("Autoheight", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Change whole slider's height (make it equal current slide's height)", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "value" => "no",
                            "type" => "switch",
                            "options" => organic_beauty_get_sc_param('yes_no')
                        ),
                        "custom" => array(
                            "title" => esc_html__("Custom", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Allow get team members from inner shortcodes (custom) or get it from specified group (cat)", 'organic-beauty') ),
                            "divider" => true,
                            "value" => "no",
                            "type" => "switch",
                            "options" => organic_beauty_get_sc_param('yes_no')
                        ),
                        "cat" => array(
                            "title" => esc_html__("Categories", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select categories (groups) to show team members. If empty - select team members from any category (group) or from IDs list", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "divider" => true,
                            "value" => "",
                            "type" => "select",
                            "style" => "list",
                            "multiple" => true,
                            "options" => organic_beauty_array_merge(array(0 => esc_html__('- Select category -', 'organic-beauty')), $clients_groups)
                        ),
                        "count" => array(
                            "title" => esc_html__("Number of posts", 'organic-beauty'),
                            "desc" => wp_kses_data( __("How many posts will be displayed? If used IDs - this parameter ignored.", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => 4,
                            "min" => 1,
                            "max" => 100,
                            "type" => "spinner"
                        ),
                        "offset" => array(
                            "title" => esc_html__("Offset before select posts", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Skip posts before select next part.", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => 0,
                            "min" => 0,
                            "type" => "spinner"
                        ),
                        "orderby" => array(
                            "title" => esc_html__("Post order by", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select desired posts sorting method", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => "title",
                            "type" => "select",
                            "options" => organic_beauty_get_sc_param('sorting')
                        ),
                        "order" => array(
                            "title" => esc_html__("Post order", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select desired posts order", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => "asc",
                            "type" => "switch",
                            "size" => "big",
                            "options" => organic_beauty_get_sc_param('ordering')
                        ),
                        "ids" => array(
                            "title" => esc_html__("Post IDs list", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Comma separated list of posts ID. If set - parameters above are ignored!", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "link" => array(
                            "title" => esc_html__("Button URL", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Link URL for the button at the bottom of the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "link_caption" => array(
                            "title" => esc_html__("Button caption", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Caption for the button at the bottom of the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "width" => organic_beauty_shortcodes_width(),
                        "height" => organic_beauty_shortcodes_height(),
                        "top" => organic_beauty_get_sc_param('top'),
                        "bottom" => organic_beauty_get_sc_param('bottom'),
                        "left" => organic_beauty_get_sc_param('left'),
                        "right" => organic_beauty_get_sc_param('right'),
                        "id" => organic_beauty_get_sc_param('id'),
                        "class" => organic_beauty_get_sc_param('class'),
                        "animation" => organic_beauty_get_sc_param('animation'),
                        "css" => organic_beauty_get_sc_param('css')
                    ),
                    "children" => array(
                        "name" => "trx_clients_item",
                        "title" => esc_html__("Client", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Single client (custom parameters)", 'organic-beauty') ),
                        "container" => true,
                        "params" => array(
                            "name" => array(
                                "title" => esc_html__("Name", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Client's name", 'organic-beauty') ),
                                "divider" => true,
                                "value" => "",
                                "type" => "text"
                            ),
                            "position" => array(
                                "title" => esc_html__("Position", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Client's position", 'organic-beauty') ),
                                "value" => "",
                                "type" => "text"
                            ),
                            "link" => array(
                                "title" => esc_html__("Link", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Link on client's personal page", 'organic-beauty') ),
                                "divider" => true,
                                "value" => "",
                                "type" => "text"
                            ),
                            "image" => array(
                                "title" => esc_html__("Image", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Client's image", 'organic-beauty') ),
                                "value" => "",
                                "readonly" => false,
                                "type" => "media"
                            ),
                            "_content_" => array(
                                "title" => esc_html__("Description", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Client's short description", 'organic-beauty') ),
                                "divider" => true,
                                "rows" => 4,
                                "value" => "",
                                "type" => "textarea"
                            ),
                            "id" => organic_beauty_get_sc_param('id'),
                            "class" => organic_beauty_get_sc_param('class'),
                            "animation" => organic_beauty_get_sc_param('animation'),
                            "css" => organic_beauty_get_sc_param('css')
                        )
                    )
                )

            ));
        }
    }
}


// Add [trx_clients] and [trx_clients_item] in the VC shortcodes list
if (!function_exists('organic_beauty_clients_reg_shortcodes_vc')) {
    //Handler of add_filter('organic_beauty_action_shortcodes_list_vc',	'organic_beauty_clients_reg_shortcodes_vc');
    function organic_beauty_clients_reg_shortcodes_vc() {

        $clients_groups = organic_beauty_get_list_terms(false, 'clients_group');
        $clients_styles = organic_beauty_get_list_templates('clients');
        $controls		= organic_beauty_get_list_slider_controls();

        // Clients
        vc_map( array(
            "base" => "trx_clients",
            "name" => esc_html__("Clients", 'organic-beauty'),
            "description" => wp_kses_data( __("Insert clients list", 'organic-beauty') ),
            "category" => esc_html__('Content', 'organic-beauty'),
            'icon' => 'icon_trx_clients',
            "class" => "trx_sc_columns trx_sc_clients",
            "content_element" => true,
            "is_container" => true,
            "show_settings_on_create" => true,
            "as_parent" => array('only' => 'trx_clients_item'),
            "params" => array(
                array(
                    "param_name" => "style",
                    "heading" => esc_html__("Clients style", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select style to display clients list", 'organic-beauty') ),
                    "class" => "",
                    "admin_label" => true,
                    "value" => array_flip($clients_styles),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "scheme",
                    "heading" => esc_html__("Color scheme", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select color scheme for this block", 'organic-beauty') ),
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('schemes')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "slider",
                    "heading" => esc_html__("Slider", 'organic-beauty'),
                    "description" => wp_kses_data( __("Use slider to show testimonials", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    "class" => "",
                    "std" => "no",
                    "value" => array_flip((array)organic_beauty_get_sc_param('yes_no')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "controls",
                    "heading" => esc_html__("Controls", 'organic-beauty'),
                    "description" => wp_kses_data( __("Slider controls style and position", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "std" => "no",
                    "value" => array_flip($controls),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "slides_space",
                    "heading" => esc_html__("Space between slides", 'organic-beauty'),
                    "description" => wp_kses_data( __("Size of space (in px) between slides", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "value" => "0",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "interval",
                    "heading" => esc_html__("Slides change interval", 'organic-beauty'),
                    "description" => wp_kses_data( __("Slides change interval (in milliseconds: 1000ms = 1s)", 'organic-beauty') ),
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "value" => "7000",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "autoheight",
                    "heading" => esc_html__("Autoheight", 'organic-beauty'),
                    "description" => wp_kses_data( __("Change whole slider's height (make it equal current slide's height)", 'organic-beauty') ),
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "value" => array("Autoheight" => "yes" ),
                    "type" => "checkbox"
                ),
                array(
                    "param_name" => "custom",
                    "heading" => esc_html__("Custom", 'organic-beauty'),
                    "description" => wp_kses_data( __("Allow get clients from inner shortcodes (custom) or get it from specified group (cat)", 'organic-beauty') ),
                    "class" => "",
                    "value" => array("Custom clients" => "yes" ),
                    "type" => "checkbox"
                ),
                array(
                    "param_name" => "title",
                    "heading" => esc_html__("Title", 'organic-beauty'),
                    "description" => wp_kses_data( __("Title for the block", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "subtitle",
                    "heading" => esc_html__("Subtitle", 'organic-beauty'),
                    "description" => wp_kses_data( __("Subtitle for the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "description",
                    "heading" => esc_html__("Description", 'organic-beauty'),
                    "description" => wp_kses_data( __("Description for the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textarea"
                ),
                array(
                    "param_name" => "cat",
                    "heading" => esc_html__("Categories", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select category to show clients. If empty - select clients from any category (group) or from IDs list", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => array_flip(organic_beauty_array_merge(array(0 => esc_html__('- Select category -', 'organic-beauty')), $clients_groups)),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "columns",
                    "heading" => esc_html__("Columns", 'organic-beauty'),
                    "description" => wp_kses_data( __("How many columns use to show clients", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    "admin_label" => true,
                    "class" => "",
                    "value" => "4",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "count",
                    "heading" => esc_html__("Number of posts", 'organic-beauty'),
                    "description" => wp_kses_data( __("How many posts will be displayed? If used IDs - this parameter ignored.", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => "4",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "offset",
                    "heading" => esc_html__("Offset before select posts", 'organic-beauty'),
                    "description" => wp_kses_data( __("Skip posts before select next part.", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => "0",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "orderby",
                    "heading" => esc_html__("Post sorting", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select desired posts sorting method", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "std" => "title",
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('sorting')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "order",
                    "heading" => esc_html__("Post order", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select desired posts order", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "std" => "asc",
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('ordering')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "ids",
                    "heading" => esc_html__("client's IDs list", 'organic-beauty'),
                    "description" => wp_kses_data( __("Comma separated list of client's ID. If set - parameters above (category, count, order, etc.)  are ignored!", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "link",
                    "heading" => esc_html__("Button URL", 'organic-beauty'),
                    "description" => wp_kses_data( __("Link URL for the button at the bottom of the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "link_caption",
                    "heading" => esc_html__("Button caption", 'organic-beauty'),
                    "description" => wp_kses_data( __("Caption for the button at the bottom of the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                organic_beauty_vc_width(),
                organic_beauty_vc_height(),
                organic_beauty_get_vc_param('margin_top'),
                organic_beauty_get_vc_param('margin_bottom'),
                organic_beauty_get_vc_param('margin_left'),
                organic_beauty_get_vc_param('margin_right'),
                organic_beauty_get_vc_param('id'),
                organic_beauty_get_vc_param('class'),
                organic_beauty_get_vc_param('animation'),
                organic_beauty_get_vc_param('css')
            ),
            'js_view' => 'VcTrxColumnsView'
        ) );


        vc_map( array(
            "base" => "trx_clients_item",
            "name" => esc_html__("Client", 'organic-beauty'),
            "description" => wp_kses_data( __("Client - all data pull out from it account on your site", 'organic-beauty') ),
            "show_settings_on_create" => true,
            "class" => "trx_sc_collection trx_sc_column_item trx_sc_clients_item",
            "content_element" => true,
            "is_container" => true,
            'icon' => 'icon_trx_clients_item',
            "as_child" => array('only' => 'trx_clients'),
            "as_parent" => array('except' => 'trx_clients'),
            "params" => array(
                array(
                    "param_name" => "name",
                    "heading" => esc_html__("Name", 'organic-beauty'),
                    "description" => wp_kses_data( __("Client's name", 'organic-beauty') ),
                    "admin_label" => true,
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "position",
                    "heading" => esc_html__("Position", 'organic-beauty'),
                    "description" => wp_kses_data( __("Client's position", 'organic-beauty') ),
                    "admin_label" => true,
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "link",
                    "heading" => esc_html__("Link", 'organic-beauty'),
                    "description" => wp_kses_data( __("Link on client's personal page", 'organic-beauty') ),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "image",
                    "heading" => esc_html__("Client's image", 'organic-beauty'),
                    "description" => wp_kses_data( __("Clients's image", 'organic-beauty') ),
                    "class" => "",
                    "value" => "",
                    "type" => "attach_image"
                ),
                organic_beauty_get_vc_param('id'),
                organic_beauty_get_vc_param('class'),
                organic_beauty_get_vc_param('animation'),
                organic_beauty_get_vc_param('css')
            ),
            'js_view' => 'VcTrxColumnItemView'
        ) );

        class WPBakeryShortCode_Trx_Clients extends Organic_Beauty_Vc_ShortCodeColumns {}
        class WPBakeryShortCode_Trx_Clients_Item extends Organic_Beauty_Vc_ShortCodeCollection {}

    }
}

// ---------------------------------- [trx_services] ---------------------------------------

if ( !function_exists( 'organic_beauty_sc_services' ) ) {
    function organic_beauty_sc_services($atts, $content=null){
        if (organic_beauty_in_shortcode_blogger()) return '';
        extract(organic_beauty_html_decode(shortcode_atts(array(
            // Individual params
            "style" => "services-1",
            "columns" => 4,
            "slider" => "no",
            "slides_space" => 0,
            "controls" => "no",
            "interval" => "",
            "autoheight" => "no",
            "equalheight" => "no",
            "align" => "",
            "custom" => "no",
            "type" => "images",	// icons | images
            "ids" => "",
            "cat" => "",
            "count" => 4,
            "offset" => "",
            "orderby" => "date",
            "order" => "desc",
            "readmore" => esc_html__('Learn more', 'organic-beauty'),
            "title" => "",
            "subtitle" => "",
            "description" => "",
            "link_caption" => esc_html__('Learn more', 'organic-beauty'),
            "link" => '',
            "scheme" => '',
            "image" => '',
            "image_align" => '',
            // Common params
            "id" => "",
            "class" => "",
            "animation" => "",
            "css" => "",
            "width" => "",
            "height" => "",
            "top" => "",
            "bottom" => "",
            "left" => "",
            "right" => ""
        ), $atts)));

        if (organic_beauty_param_is_off($slider) && $columns > 1 && $style == 'services-5' && !empty($image)) $columns = 2;
        if (!empty($image)) {
            if ($image > 0) {
                $attach = wp_get_attachment_image_src( $image, 'full' );
                if (isset($attach[0]) && $attach[0]!='')
                    $image = $attach[0];
            }
        }

        if (empty($id)) $id = "sc_services_".str_replace('.', '', mt_rand());
        if (empty($width)) $width = "100%";
        if (!empty($height) && organic_beauty_param_is_on($autoheight)) $autoheight = "no";
        if (empty($interval)) $interval = mt_rand(5000, 10000);

        $class .= ($class ? ' ' : '') . organic_beauty_get_css_position_as_classes($top, $right, $bottom, $left);

        $ws = organic_beauty_get_css_dimensions_from_values($width);
        $hs = organic_beauty_get_css_dimensions_from_values('', $height);
        $css .= ($hs) . ($ws);

        $columns = max(1, min(12, (int) $columns));
        $count = max(1, (int) $count);
        if (organic_beauty_param_is_off($custom) && $count < $columns) $columns = $count;

        if (organic_beauty_param_is_on($slider)) organic_beauty_enqueue_slider('swiper');

        organic_beauty_storage_set('sc_services_data', array(
                'id' => $id,
                'style' => $style,
                'type' => $type,
                'columns' => $columns,
                'counter' => 0,
                'slider' => $slider,
                'css_wh' => $ws . $hs,
                'readmore' => $readmore
            )
        );

        $output = '<div' . ($id ? ' id="'.esc_attr($id).'_wrap"' : '')
            . ' class="sc_services_wrap'
            . ($scheme && !organic_beauty_param_is_off($scheme) && !organic_beauty_param_is_inherit($scheme) ? ' scheme_'.esc_attr($scheme) : '')
            .'">'
            . '<div' . ($id ? ' id="'.esc_attr($id).'"' : '')
            . ' class="sc_services'
            . ' sc_services_style_'.esc_attr($style)
            . ' sc_services_type_'.esc_attr($type)
            . ' ' . esc_attr(organic_beauty_get_template_property($style, 'container_classes'))
            . (!empty($class) ? ' '.esc_attr($class) : '')
            . ($align!='' && $align!='none' ? ' align'.esc_attr($align) : '')
            . '"'
            . ($css!='' ? ' style="'.esc_attr($css).'"' : '')
            . (!organic_beauty_param_is_off($equalheight) ? ' data-equal-height=".sc_services_item"' : '')
            . (!organic_beauty_param_is_off($animation) ? ' data-animation="'.esc_attr(organic_beauty_get_animation_classes($animation)).'"' : '')
            . '>'
            . (!empty($subtitle) ? '<h6 class="sc_services_subtitle sc_item_subtitle">' . trim(organic_beauty_strmacros($subtitle)) . '</h6>' : '')
            . (!empty($title) ? '<h2 class="sc_services_title sc_item_title' . (empty($description) ? ' sc_item_title_without_descr' : ' sc_item_title_without_descr') . '">' . trim(organic_beauty_strmacros($title)) . '</h2>' : '')
            . (!empty($description) ? '<div class="sc_services_descr sc_item_descr">' . trim(organic_beauty_strmacros($description)) . '</div>' : '')
            . (organic_beauty_param_is_on($slider)
                ? ('<div class="sc_slider_swiper swiper-slider-container'
                    . ' ' . esc_attr(organic_beauty_get_slider_controls_classes($controls))
                    . (organic_beauty_param_is_on($autoheight) ? ' sc_slider_height_auto' : '')
                    . ($hs ? ' sc_slider_height_fixed' : '')
                    . '"'
                    . (!empty($width) && organic_beauty_strpos($width, '%')===false ? ' data-old-width="' . esc_attr($width) . '"' : '')
                    . (!empty($height) && organic_beauty_strpos($height, '%')===false ? ' data-old-height="' . esc_attr($height) . '"' : '')
                    . ((int) $interval > 0 ? ' data-interval="'.esc_attr($interval).'"' : '')
                    . ($columns > 1 ? ' data-slides-per-view="' . esc_attr($columns) . '"' : '')
                    . ($slides_space > 0 ? ' data-slides-space="' . esc_attr($slides_space) . '"' : '')
                    . ' data-slides-min-width="250"'
                    . '>'
                    . '<div class="slides swiper-wrapper">')
                : ($columns > 1
                    ? ($style == 'services-5' && !empty($image)
                        ? '<div class="sc_service_container sc_align_'.esc_attr($image_align).'">'
                        . '<div class="sc_services_image"><img src="'.esc_url($image).'" alt="'.esc_attr__('img', 'organic-beauty').'"></div>'
                        : '')
                    . '<div class="sc_columns columns_wrap">'
                    : '')
            );

        if (organic_beauty_param_is_on($custom) && $content) {
            $output .= do_shortcode($content);
        } else {
            global $post;

            if (!empty($ids)) {
                $posts = explode(',', $ids);
                $count = count($posts);
            }

            $args = array(
                'post_type' => 'services',
                'post_status' => 'publish',
                'posts_per_page' => $count,
                'ignore_sticky_posts' => true,
                'order' => $order=='asc' ? 'asc' : 'desc',
                'readmore' => $readmore
            );

            if ($offset > 0 && empty($ids)) {
                $args['offset'] = $offset;
            }

            $args = organic_beauty_query_add_sort_order($args, $orderby, $order);
            $args = organic_beauty_query_add_posts_and_cats($args, $ids, 'services', $cat, 'services_group');

            $query = new WP_Query( $args );

            $post_number = 0;

            while ( $query->have_posts() ) {
                $query->the_post();
                $post_number++;
                $args = array(
                    'layout' => $style,
                    'show' => false,
                    'number' => $post_number,
                    'posts_on_page' => ($count > 0 ? $count : $query->found_posts),
                    "descr" => organic_beauty_get_custom_option('post_excerpt_maxlength'.($columns > 1 ? '_masonry' : '')),
                    "orderby" => $orderby,
                    'content' => false,
                    'terms_list' => false,
                    'readmore' => $readmore,
                    'tag_type' => $type,
                    'columns_count' => $columns,
                    'slider' => $slider,
                    'tag_id' => $id ? $id . '_' . $post_number : '',
                    'tag_class' => '',
                    'tag_animation' => '',
                    'tag_css' => '',
                    'tag_css_wh' => $ws . $hs
                );
                $output .= organic_beauty_show_post_layout($args);
            }
            wp_reset_postdata();
        }

        if (organic_beauty_param_is_on($slider)) {
            $output .= '</div>'
                . '<div class="sc_slider_controls_wrap"><a class="sc_slider_prev" href="#"></a><a class="sc_slider_next" href="#"></a></div>'
                . '<div class="sc_slider_pagination_wrap"></div>'
                . '</div>';
        } else if ($columns > 1) {
            $output .= '</div>';
            if ($style == 'services-5' && !empty($image))
                $output .= '</div>';
        }

        $output .=  (!empty($link) ? '<div class="sc_services_button sc_item_button">'.organic_beauty_do_shortcode('[trx_button link="'.esc_url($link).'"]'.esc_html($link_caption).'[/trx_button]').'</div>' : '')
            . '</div><!-- /.sc_services -->'
            . '</div><!-- /.sc_services_wrap -->';

        // Add template specific scripts and styles
        do_action('organic_beauty_action_blog_scripts', $style);

        return apply_filters('organic_beauty_shortcode_output', $output, 'trx_services', $atts, $content);
    }
    add_shortcode('trx_services', 'organic_beauty_sc_services');
}


if ( !function_exists( 'organic_beauty_sc_services_item' ) ) {
    function organic_beauty_sc_services_item($atts, $content=null) {
        if (organic_beauty_in_shortcode_blogger()) return '';
        extract(organic_beauty_html_decode(shortcode_atts( array(
            // Individual params
            "icon" => "",
            "image" => "",
            "title" => "",
            "title_top" => "",
            "link" => "",
            "readmore" => "(none)",
            // Common params
            "id" => "",
            "class" => "",
            "animation" => "",
            "css" => ""
        ), $atts)));

        organic_beauty_storage_inc_array('sc_services_data', 'counter');

        $id = $id ? $id : (organic_beauty_storage_get_array('sc_services_data', 'id') ? organic_beauty_storage_get_array('sc_services_data', 'id') . '_' . organic_beauty_storage_get_array('sc_services_data', 'counter') : '');

        $descr = trim(chop(do_shortcode($content)));
        $readmore = $readmore=='(none)' ? organic_beauty_storage_get_array('sc_services_data', 'readmore') : $readmore;

        $type = organic_beauty_storage_get_array('sc_services_data', 'type');
        if (!empty($icon)) {
            $type = 'icons';
        } else if (!empty($image)) {
            $type = 'images';
            if ($image > 0) {
                $attach = wp_get_attachment_image_src( $image, 'full' );
                if (isset($attach[0]) && $attach[0]!='')
                    $image = $attach[0];
            }
            $thumb_sizes = organic_beauty_get_thumb_sizes(array('layout' => organic_beauty_storage_get_array('sc_services_data', 'style')));
            $image = organic_beauty_get_resized_image_tag($image, $thumb_sizes['w'], $thumb_sizes['h']);
        }

        $post_data = array(
            'post_title' => $title,
            'title_top' => $title_top,
            'post_excerpt' => $descr,
            'post_thumb' => $image,
            'post_icon' => $icon,
            'post_link' => $link,
            'post_protected' => false,
            'post_format' => 'standard'
        );
        $args = array(
            'layout' => organic_beauty_storage_get_array('sc_services_data', 'style'),
            'number' => organic_beauty_storage_get_array('sc_services_data', 'counter'),
            'columns_count' => organic_beauty_storage_get_array('sc_services_data', 'columns'),
            'slider' => organic_beauty_storage_get_array('sc_services_data', 'slider'),
            'show' => false,
            'descr'  => -1,		// -1 - don't strip tags, 0 - strip_tags, >0 - strip_tags and truncate string
            'readmore' => $readmore,
            'tag_type' => $type,
            'tag_id' => $id,
            'tag_class' => $class,
            'tag_animation' => $animation,
            'tag_css' => $css,
            'tag_css_wh' => organic_beauty_storage_get_array('sc_services_data', 'css_wh')
        );
        $output = organic_beauty_show_post_layout($args, $post_data);
        return apply_filters('organic_beauty_shortcode_output', $output, 'trx_services_item', $atts, $content);
    }
    add_shortcode('trx_services_item', 'organic_beauty_sc_services_item');
}
// ---------------------------------- [/trx_services] ---------------------------------------



// Add [trx_services] and [trx_services_item] in the shortcodes list
if (!function_exists('organic_beauty_services_reg_shortcodes')) {
    //Handler of add_filter('organic_beauty_action_shortcodes_list',	'organic_beauty_services_reg_shortcodes');
    function organic_beauty_services_reg_shortcodes() {
        if (organic_beauty_storage_isset('shortcodes')) {

            $services_groups = organic_beauty_get_list_terms(false, 'services_group');
            $services_styles = organic_beauty_get_list_templates('services');
            $controls 		 = organic_beauty_get_list_slider_controls();

            organic_beauty_sc_map_after('trx_section', array(

                // Services
                "trx_services" => array(
                    "title" => esc_html__("Services", 'organic-beauty'),
                    "desc" => wp_kses_data( __("Insert services list in your page (post)", 'organic-beauty') ),
                    "decorate" => true,
                    "container" => false,
                    "params" => array(
                        "title" => array(
                            "title" => esc_html__("Title", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Title for the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "subtitle" => array(
                            "title" => esc_html__("Subtitle", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Subtitle for the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "description" => array(
                            "title" => esc_html__("Description", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Short description for the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "textarea"
                        ),
                        "style" => array(
                            "title" => esc_html__("Services style", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select style to display services list", 'organic-beauty') ),
                            "value" => "services-1",
                            "type" => "select",
                            "options" => $services_styles
                        ),
                        "columns" => array(
                            "title" => esc_html__("Columns", 'organic-beauty'),
                            "desc" => wp_kses_data( __("How many columns use to show services list", 'organic-beauty') ),
                            "value" => 4,
                            "min" => 2,
                            "max" => 6,
                            "step" => 1,
                            "type" => "spinner"
                        ),
                        "scheme" => array(
                            "title" => esc_html__("Color scheme", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select color scheme for this block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "checklist",
                            "options" => organic_beauty_get_sc_param('schemes')
                        ),
                        "slider" => array(
                            "title" => esc_html__("Slider", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Use slider to show services", 'organic-beauty') ),
                            "value" => "no",
                            "type" => "switch",
                            "options" => organic_beauty_get_sc_param('yes_no')
                        ),
                        "controls" => array(
                            "title" => esc_html__("Controls", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Slider controls style and position", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "divider" => true,
                            "value" => "",
                            "type" => "checklist",
                            "dir" => "horizontal",
                            "options" => $controls
                        ),
                        "slides_space" => array(
                            "title" => esc_html__("Space between slides", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Size of space (in px) between slides", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "value" => 0,
                            "min" => 0,
                            "max" => 100,
                            "step" => 10,
                            "type" => "spinner"
                        ),
                        "interval" => array(
                            "title" => esc_html__("Slides change interval", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Slides change interval (in milliseconds: 1000ms = 1s)", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "value" => 7000,
                            "step" => 500,
                            "min" => 0,
                            "type" => "spinner"
                        ),
                        "autoheight" => array(
                            "title" => esc_html__("Autoheight", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Change whole slider's height (make it equal current slide's height)", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "value" => "yes",
                            "type" => "switch",
                            "options" => organic_beauty_get_sc_param('yes_no')
                        ),
                        "align" => array(
                            "title" => esc_html__("Alignment", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Alignment of the services block", 'organic-beauty') ),
                            "divider" => true,
                            "value" => "",
                            "type" => "checklist",
                            "dir" => "horizontal",
                            "options" => organic_beauty_get_sc_param('align')
                        ),
                        "custom" => array(
                            "title" => esc_html__("Custom", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Allow get services items from inner shortcodes (custom) or get it from specified group (cat)", 'organic-beauty') ),
                            "divider" => true,
                            "value" => "no",
                            "type" => "switch",
                            "options" => organic_beauty_get_sc_param('yes_no')
                        ),
                        "cat" => array(
                            "title" => esc_html__("Categories", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select categories (groups) to show services list. If empty - select services from any category (group) or from IDs list", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "divider" => true,
                            "value" => "",
                            "type" => "select",
                            "style" => "list",
                            "multiple" => true,
                            "options" => organic_beauty_array_merge(array(0 => esc_html__('- Select category -', 'organic-beauty')), $services_groups)
                        ),
                        "count" => array(
                            "title" => esc_html__("Number of posts", 'organic-beauty'),
                            "desc" => wp_kses_data( __("How many posts will be displayed? If used IDs - this parameter ignored.", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => 4,
                            "min" => 1,
                            "max" => 100,
                            "type" => "spinner"
                        ),
                        "offset" => array(
                            "title" => esc_html__("Offset before select posts", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Skip posts before select next part.", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => 0,
                            "min" => 0,
                            "type" => "spinner"
                        ),
                        "orderby" => array(
                            "title" => esc_html__("Post order by", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select desired posts sorting method", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => "date",
                            "type" => "select",
                            "options" => organic_beauty_get_sc_param('sorting')
                        ),
                        "order" => array(
                            "title" => esc_html__("Post order", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select desired posts order", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => "desc",
                            "type" => "switch",
                            "size" => "big",
                            "options" => organic_beauty_get_sc_param('ordering')
                        ),
                        "ids" => array(
                            "title" => esc_html__("Post IDs list", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Comma separated list of posts ID. If set - parameters above are ignored!", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "readmore" => array(
                            "title" => esc_html__("Read more", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Caption for the Read more link (if empty - link not showed)", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "link" => array(
                            "title" => esc_html__("Button URL", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Link URL for the button at the bottom of the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "link_caption" => array(
                            "title" => esc_html__("Button caption", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Caption for the button at the bottom of the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "width" => organic_beauty_shortcodes_width(),
                        "height" => organic_beauty_shortcodes_height(),
                        "top" => organic_beauty_get_sc_param('top'),
                        "bottom" => organic_beauty_get_sc_param('bottom'),
                        "left" => organic_beauty_get_sc_param('left'),
                        "right" => organic_beauty_get_sc_param('right'),
                        "id" => organic_beauty_get_sc_param('id'),
                        "class" => organic_beauty_get_sc_param('class'),
                        "animation" => organic_beauty_get_sc_param('animation'),
                        "css" => organic_beauty_get_sc_param('css')
                    ),
                    "children" => array(
                        "name" => "trx_services_item",
                        "title" => esc_html__("Service item", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Service item", 'organic-beauty') ),
                        "container" => true,
                        "params" => array(
                            "title" => array(
                                "title" => esc_html__("Title", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Item's title", 'organic-beauty') ),
                                "divider" => true,
                                "value" => "",
                                "type" => "text"
                            ),
                            "title_top" => array(
                                "title" => esc_html__("Top Title", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Item's top title", 'organic-beauty') ),
                                "divider" => true,
                                "value" => "",
                                "type" => "text"
                            ),
                            "image" => array(
                                "title" => esc_html__("Item's image", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Item's image (if icon not selected)", 'organic-beauty') ),
                                "value" => "",
                                "readonly" => false,
                                "type" => "media"
                            ),
                            "link" => array(
                                "title" => esc_html__("Link", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Link on service's item page", 'organic-beauty') ),
                                "divider" => true,
                                "value" => "",
                                "type" => "text"
                            ),
                            "readmore" => array(
                                "title" => esc_html__("Read more", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Caption for the Read more link (if empty - link not showed)", 'organic-beauty') ),
                                "value" => "",
                                "type" => "text"
                            ),
                            "_content_" => array(
                                "title" => esc_html__("Description", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Item's short description", 'organic-beauty') ),
                                "divider" => true,
                                "rows" => 4,
                                "value" => "",
                                "type" => "textarea"
                            ),
                            "id" => organic_beauty_get_sc_param('id'),
                            "class" => organic_beauty_get_sc_param('class'),
                            "animation" => organic_beauty_get_sc_param('animation'),
                            "css" => organic_beauty_get_sc_param('css')
                        )
                    )
                )

            ));
        }
    }
}


// Add [trx_services] and [trx_services_item] in the VC shortcodes list
if (!function_exists('organic_beauty_services_reg_shortcodes_vc')) {
    //Handler of add_filter('organic_beauty_action_shortcodes_list_vc',	'organic_beauty_services_reg_shortcodes_vc');
    function organic_beauty_services_reg_shortcodes_vc() {

        $services_groups = organic_beauty_get_list_terms(false, 'services_group');
        $services_styles = organic_beauty_get_list_templates('services');
        $controls		 = organic_beauty_get_list_slider_controls();

        // Services
        vc_map( array(
            "base" => "trx_services",
            "name" => esc_html__("Services", 'organic-beauty'),
            "description" => wp_kses_data( __("Insert services list", 'organic-beauty') ),
            "category" => esc_html__('Content', 'organic-beauty'),
            "icon" => 'icon_trx_services',
            "class" => "trx_sc_columns trx_sc_services",
            "content_element" => true,
            "is_container" => true,
            "show_settings_on_create" => true,
            "as_parent" => array('only' => 'trx_services_item'),
            "params" => array(
                array(
                    "param_name" => "style",
                    "heading" => esc_html__("Services style", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select style to display services list", 'organic-beauty') ),
                    "class" => "",
                    "admin_label" => true,
                    "value" => array_flip($services_styles),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "equalheight",
                    "heading" => esc_html__("Equal height", 'organic-beauty'),
                    "description" => wp_kses_data( __("Make equal height for all items in the row", 'organic-beauty') ),
                    "value" => array("Equal height" => "yes" ),
                    "type" => "checkbox"
                ),
                array(
                    "param_name" => "scheme",
                    "heading" => esc_html__("Color scheme", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select color scheme for this block", 'organic-beauty') ),
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('schemes')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "slider",
                    "heading" => esc_html__("Slider", 'organic-beauty'),
                    "description" => wp_kses_data( __("Use slider to show services", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    "class" => "",
                    "std" => "no",
                    "value" => array_flip((array)organic_beauty_get_sc_param('yes_no')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "controls",
                    "heading" => esc_html__("Controls", 'organic-beauty'),
                    "description" => wp_kses_data( __("Slider controls style and position", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "std" => "no",
                    "value" => array_flip($controls),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "slides_space",
                    "heading" => esc_html__("Space between slides", 'organic-beauty'),
                    "description" => wp_kses_data( __("Size of space (in px) between slides", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "value" => "0",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "interval",
                    "heading" => esc_html__("Slides change interval", 'organic-beauty'),
                    "description" => wp_kses_data( __("Slides change interval (in milliseconds: 1000ms = 1s)", 'organic-beauty') ),
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "value" => "7000",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "autoheight",
                    "heading" => esc_html__("Autoheight", 'organic-beauty'),
                    "description" => wp_kses_data( __("Change whole slider's height (make it equal current slide's height)", 'organic-beauty') ),
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "value" => array("Autoheight" => "yes" ),
                    "type" => "checkbox"
                ),
                array(
                    "param_name" => "align",
                    "heading" => esc_html__("Alignment", 'organic-beauty'),
                    "description" => wp_kses_data( __("Alignment of the services block", 'organic-beauty') ),
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('align')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "custom",
                    "heading" => esc_html__("Custom", 'organic-beauty'),
                    "description" => wp_kses_data( __("Allow get services from inner shortcodes (custom) or get it from specified group (cat)", 'organic-beauty') ),
                    "class" => "",
                    "value" => array("Custom services" => "yes" ),
                    "type" => "checkbox"
                ),
                array(
                    "param_name" => "title",
                    "heading" => esc_html__("Title", 'organic-beauty'),
                    "description" => wp_kses_data( __("Title for the block", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "subtitle",
                    "heading" => esc_html__("Subtitle", 'organic-beauty'),
                    "description" => wp_kses_data( __("Subtitle for the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "description",
                    "heading" => esc_html__("Description", 'organic-beauty'),
                    "description" => wp_kses_data( __("Description for the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textarea"
                ),
                array(
                    "param_name" => "cat",
                    "heading" => esc_html__("Categories", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select category to show services. If empty - select services from any category (group) or from IDs list", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_array_merge(array(0 => esc_html__('- Select category -', 'organic-beauty')), $services_groups)),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "columns",
                    "heading" => esc_html__("Columns", 'organic-beauty'),
                    "description" => wp_kses_data( __("How many columns use to show services list", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    "admin_label" => true,
                    "class" => "",
                    "value" => "4",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "count",
                    "heading" => esc_html__("Number of posts", 'organic-beauty'),
                    "description" => wp_kses_data( __("How many posts will be displayed? If used IDs - this parameter ignored.", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => "4",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "offset",
                    "heading" => esc_html__("Offset before select posts", 'organic-beauty'),
                    "description" => wp_kses_data( __("Skip posts before select next part.", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => "0",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "orderby",
                    "heading" => esc_html__("Post sorting", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select desired posts sorting method", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "std" => "date",
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('sorting')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "order",
                    "heading" => esc_html__("Post order", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select desired posts order", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "std" => "desc",
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('ordering')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "ids",
                    "heading" => esc_html__("Service's IDs list", 'organic-beauty'),
                    "description" => wp_kses_data( __("Comma separated list of service's ID. If set - parameters above (category, count, order, etc.)  are ignored!", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "readmore",
                    "heading" => esc_html__("Read more", 'organic-beauty'),
                    "description" => wp_kses_data( __("Caption for the Read more link (if empty - link not showed)", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "link",
                    "heading" => esc_html__("Button URL", 'organic-beauty'),
                    "description" => wp_kses_data( __("Link URL for the button at the bottom of the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "link_caption",
                    "heading" => esc_html__("Button caption", 'organic-beauty'),
                    "description" => wp_kses_data( __("Caption for the button at the bottom of the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                organic_beauty_vc_width(),
                organic_beauty_vc_height(),
                organic_beauty_get_vc_param('margin_top'),
                organic_beauty_get_vc_param('margin_bottom'),
                organic_beauty_get_vc_param('margin_left'),
                organic_beauty_get_vc_param('margin_right'),
                organic_beauty_get_vc_param('id'),
                organic_beauty_get_vc_param('class'),
                organic_beauty_get_vc_param('animation'),
                organic_beauty_get_vc_param('css')
            ),
            'default_content' => '
					[trx_services_item title="' . esc_html__( 'Service item 1', 'organic-beauty' ) . '"][/trx_services_item]
					[trx_services_item title="' . esc_html__( 'Service item 2', 'organic-beauty' ) . '"][/trx_services_item]
					[trx_services_item title="' . esc_html__( 'Service item 3', 'organic-beauty' ) . '"][/trx_services_item]
					[trx_services_item title="' . esc_html__( 'Service item 4', 'organic-beauty' ) . '"][/trx_services_item]
				',
            'js_view' => 'VcTrxColumnsView'
        ) );


        vc_map( array(
            "base" => "trx_services_item",
            "name" => esc_html__("Services item", 'organic-beauty'),
            "description" => wp_kses_data( __("Custom services item - all data pull out from shortcode parameters", 'organic-beauty') ),
            "show_settings_on_create" => true,
            "class" => "trx_sc_collection trx_sc_column_item trx_sc_services_item",
            "content_element" => true,
            "is_container" => true,
            'icon' => 'icon_trx_services_item',
            "as_child" => array('only' => 'trx_services'),
            "as_parent" => array('except' => 'trx_services'),
            "params" => array(
                array(
                    "param_name" => "title",
                    "heading" => esc_html__("Title", 'organic-beauty'),
                    "description" => wp_kses_data( __("Item's title", 'organic-beauty') ),
                    "admin_label" => true,
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "title_top",
                    "heading" => esc_html__("Title Top", 'organic-beauty'),
                    "description" => wp_kses_data( __("Item's top title", 'organic-beauty') ),
                    "admin_label" => true,
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "image",
                    "heading" => esc_html__("Image", 'organic-beauty'),
                    "description" => wp_kses_data( __("Item's image", 'organic-beauty') ),
                    "class" => "",
                    "value" => "",
                    "type" => "attach_image"
                ),
                array(
                    "param_name" => "link",
                    "heading" => esc_html__("Link", 'organic-beauty'),
                    "description" => wp_kses_data( __("Link on item's page", 'organic-beauty') ),
                    "admin_label" => true,
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "readmore",
                    "heading" => esc_html__("Read more", 'organic-beauty'),
                    "description" => wp_kses_data( __("Caption for the Read more link (if empty - link not showed)", 'organic-beauty') ),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                organic_beauty_get_vc_param('id'),
                organic_beauty_get_vc_param('class'),
                organic_beauty_get_vc_param('animation'),
                organic_beauty_get_vc_param('css')
            ),
            'js_view' => 'VcTrxColumnItemView'
        ) );

        class WPBakeryShortCode_Trx_Services extends Organic_Beauty_Vc_ShortCodeColumns {}
        class WPBakeryShortCode_Trx_Services_Item extends Organic_Beauty_Vc_ShortCodeCollection {}

    }
}

// ---------------------------------- [trx_team] ---------------------------------------

if ( !function_exists( 'organic_beauty_sc_team' ) ) {
    function organic_beauty_sc_team($atts, $content=null){
        if (organic_beauty_in_shortcode_blogger()) return '';
        extract(organic_beauty_html_decode(shortcode_atts(array(
            // Individual params
            "style" => "team-1",
            "slider" => "no",
            "controls" => "no",
            "slides_space" => 0,
            "interval" => "",
            "autoheight" => "no",
            "align" => "",
            "custom" => "no",
            "ids" => "",
            "cat" => "",
            "count" => 3,
            "columns" => 3,
            "offset" => "",
            "orderby" => "title",
            "order" => "asc",
            "title" => "",
            "subtitle" => "",
            "description" => "",
            "link_caption" => esc_html__('Learn more', 'organic-beauty'),
            "link" => '',
            "scheme" => '',
            // Common params
            "id" => "",
            "class" => "",
            "animation" => "",
            "css" => "",
            "width" => "",
            "height" => "",
            "top" => "",
            "bottom" => "",
            "left" => "",
            "right" => ""
        ), $atts)));

        if (empty($id)) $id = "sc_team_".str_replace('.', '', mt_rand());
        if (empty($width)) $width = "100%";
        if (!empty($height) && organic_beauty_param_is_on($autoheight)) $autoheight = "no";
        if (empty($interval)) $interval = mt_rand(5000, 10000);

        $class .= ($class ? ' ' : '') . organic_beauty_get_css_position_as_classes($top, $right, $bottom, $left);

        $ws = organic_beauty_get_css_dimensions_from_values($width);
        $hs = organic_beauty_get_css_dimensions_from_values('', $height);
        $css .= ($hs) . ($ws);

        $count = max(1, (int) $count);
        $columns = max(1, min(12, (int) $columns));
        if (organic_beauty_param_is_off($custom) && $count < $columns) $columns = $count;

        organic_beauty_storage_set('sc_team_data', array(
                'id' => $id,
                'style' => $style,
                'columns' => $columns,
                'counter' => 0,
                'slider' => $slider,
                'css_wh' => $ws . $hs
            )
        );

        if (organic_beauty_param_is_on($slider)) organic_beauty_enqueue_slider('swiper');

        $output = '<div' . ($id ? ' id="'.esc_attr($id).'_wrap"' : '')
            . ' class="sc_team_wrap'
            . ($scheme && !organic_beauty_param_is_off($scheme) && !organic_beauty_param_is_inherit($scheme) ? ' scheme_'.esc_attr($scheme) : '')
            .'">'
            . '<div' . ($id ? ' id="'.esc_attr($id).'"' : '')
            . ' class="sc_team sc_team_style_'.esc_attr($style)
            . ' ' . esc_attr(organic_beauty_get_template_property($style, 'container_classes'))
            . (!empty($class) ? ' '.esc_attr($class) : '')
            . ($align!='' && $align!='none' ? ' align'.esc_attr($align) : '')
            .'"'
            . ($css!='' ? ' style="'.esc_attr($css).'"' : '')
            . (!organic_beauty_param_is_off($animation) ? ' data-animation="'.esc_attr(organic_beauty_get_animation_classes($animation)).'"' : '')
            . '>'
            . (!empty($subtitle) ? '<h6 class="sc_team_subtitle sc_item_subtitle">' . trim(organic_beauty_strmacros($subtitle)) . '</h6>' : '')
            . (!empty($title) ? '<h2 class="sc_team_title sc_item_title' . (empty($description) ? ' sc_item_title_without_descr' : ' sc_item_title_without_descr') . '">' . trim(organic_beauty_strmacros($title)) . '</h2>' : '')
            . (!empty($description) ? '<div class="sc_team_descr sc_item_descr">' . trim(organic_beauty_strmacros($description)) . '</div>' : '')
            . (organic_beauty_param_is_on($slider)
                ? ('<div class="sc_slider_swiper swiper-slider-container'
                    . ' ' . esc_attr(organic_beauty_get_slider_controls_classes($controls))
                    . (organic_beauty_param_is_on($autoheight) ? ' sc_slider_height_auto' : '')
                    . ($hs ? ' sc_slider_height_fixed' : '')
                    . '"'
                    . (!empty($width) && organic_beauty_strpos($width, '%')===false ? ' data-old-width="' . esc_attr($width) . '"' : '')
                    . (!empty($height) && organic_beauty_strpos($height, '%')===false ? ' data-old-height="' . esc_attr($height) . '"' : '')
                    . ((int) $interval > 0 ? ' data-interval="'.esc_attr($interval).'"' : '')
                    . ($slides_space > 0 ? ' data-slides-space="' . esc_attr($slides_space) . '"' : '')
                    . ($columns > 1 ? ' data-slides-per-view="' . esc_attr($columns) . '"' : '')
                    . ' data-slides-min-width="250"'
                    . '>'
                    . '<div class="slides swiper-wrapper">')
                : ($columns > 1
                    ? '<div class="sc_columns columns_wrap">'
                    : '')
            );

        if (organic_beauty_param_is_on($custom) && $content) {
            $output .= do_shortcode($content);
        } else {
            global $post;

            if (!empty($ids)) {
                $posts = explode(',', $ids);
                $count = count($posts);
            }

            $args = array(
                'post_type' => 'team',
                'post_status' => 'publish',
                'posts_per_page' => $count,
                'ignore_sticky_posts' => true,
                'order' => $order=='asc' ? 'asc' : 'desc',
            );

            if ($offset > 0 && empty($ids)) {
                $args['offset'] = $offset;
            }

            $args = organic_beauty_query_add_sort_order($args, $orderby, $order);
            $args = organic_beauty_query_add_posts_and_cats($args, $ids, 'team', $cat, 'team_group');
            $query = new WP_Query( $args );

            $post_number = 0;

            while ( $query->have_posts() ) {
                $query->the_post();
                $post_number++;
                $args = array(
                    'layout' => $style,
                    'show' => false,
                    'number' => $post_number,
                    'posts_on_page' => ($count > 0 ? $count : $query->found_posts),
                    "descr" => organic_beauty_get_custom_option('post_excerpt_maxlength'.($columns > 1 ? '_masonry' : '')),
                    "orderby" => $orderby,
                    'content' => false,
                    'terms_list' => false,
                    "columns_count" => $columns,
                    'slider' => $slider,
                    'tag_id' => $id ? $id . '_' . $post_number : '',
                    'tag_class' => '',
                    'tag_animation' => '',
                    'tag_css' => '',
                    'tag_css_wh' => $ws . $hs
                );
                $post_data = organic_beauty_get_post_data($args);
                $post_meta = get_post_meta($post_data['post_id'], organic_beauty_storage_get('options_prefix').'_team_data', true);
                $thumb_sizes = organic_beauty_get_thumb_sizes(array('layout' => $style));
                $args['position'] = $post_meta['team_member_position'];
                $args['link'] = !empty($post_meta['team_member_link']) ? $post_meta['team_member_link'] : $post_data['post_link'];
                $args['email'] = $post_meta['team_member_email'];
                $args['photo'] = $post_data['post_thumb'];
                $mult = organic_beauty_get_retina_multiplier();
                if (empty($args['photo']) && !empty($args['email'])) $args['photo'] = get_avatar($args['email'], $thumb_sizes['w']*$mult);
                $args['socials'] = '';
                $soc_list = $post_meta['team_member_socials'];
                if (is_array($soc_list) && count($soc_list)>0) {
                    $soc_str = '';
                    foreach ($soc_list as $sn=>$sl) {
                        if (!empty($sl))
                            $soc_str .= (!empty($soc_str) ? '|' : '') . ($sn) . '=' . ($sl);
                    }
                    if (!empty($soc_str))
                        $args['socials'] = organic_beauty_do_shortcode('[trx_socials size="tiny" shape="round" socials="'.esc_attr($soc_str).'"][/trx_socials]');
                }
                $output .= organic_beauty_show_post_layout($args, $post_data);
            }
            wp_reset_postdata();
        }

        if (organic_beauty_param_is_on($slider)) {
            $output .= '</div>'
                . '<div class="sc_slider_controls_wrap"><a class="sc_slider_prev" href="#"></a><a class="sc_slider_next" href="#"></a></div>'
                . '<div class="sc_slider_pagination_wrap"></div>'
                . '</div>';
        } else if ($columns > 1) {
            $output .= '</div>';
        }

        $output .= (!empty($link) ? '<div class="sc_team_button sc_item_button">'.organic_beauty_do_shortcode('[trx_button link="'.esc_url($link).'" icon="icon-right"]'.esc_html($link_caption).'[/trx_button]').'</div>' : '')
            . '</div><!-- /.sc_team -->'
            . '</div><!-- /.sc_team_wrap -->';

        // Add template specific scripts and styles
        do_action('organic_beauty_action_blog_scripts', $style);

        return apply_filters('organic_beauty_shortcode_output', $output, 'trx_team', $atts, $content);
    }
    add_shortcode('trx_team', 'organic_beauty_sc_team');
}


if ( !function_exists( 'organic_beauty_sc_team_item' ) ) {
    function organic_beauty_sc_team_item($atts, $content=null) {
        if (organic_beauty_in_shortcode_blogger()) return '';
        extract(organic_beauty_html_decode(shortcode_atts( array(
            // Individual params
            "user" => "",
            "member" => "",
            "name" => "",
            "position" => "",
            "photo" => "",
            "email" => "",
            "link" => "",
            "socials" => "",
            // Common params
            "id" => "",
            "class" => "",
            "animation" => "",
            "css" => ""
        ), $atts)));

        organic_beauty_storage_inc_array('sc_team_data', 'counter');

        $id = $id ? $id : (organic_beauty_storage_get_array('sc_team_data', 'id') ? organic_beauty_storage_get_array('sc_team_data', 'id') . '_' . organic_beauty_storage_get_array('sc_team_data', 'counter') : '');

        $descr = trim(chop(do_shortcode($content)));

        $thumb_sizes = organic_beauty_get_thumb_sizes(array('layout' => organic_beauty_storage_get_array('sc_team_data', 'style')));

        if (!empty($socials)) $socials = organic_beauty_do_shortcode('[trx_socials size="tiny" shape="round" socials="'.esc_attr($socials).'"][/trx_socials]');

        if (!empty($user) && $user!='none' && ($user_obj = get_user_by('login', $user)) != false) {
            $meta = get_user_meta($user_obj->ID);
            if (empty($email))		$email = $user_obj->data->user_email;
            if (empty($name))		$name = $user_obj->data->display_name;
            if (empty($position))	$position = isset($meta['user_position'][0]) ? $meta['user_position'][0] : '';
            if (empty($descr))		$descr = isset($meta['description'][0]) ? $meta['description'][0] : '';
            if (empty($socials))	$socials = organic_beauty_show_user_socials(array('author_id'=>$user_obj->ID, 'echo'=>false));
        }

        if (!empty($member) && $member!='none' && ($member_obj = (intval($member) > 0 ? get_post($member, OBJECT) : get_page_by_title($member, OBJECT, 'team'))) != null) {
            if (empty($name))		$name = $member_obj->post_title;
            if (empty($descr))		$descr = $member_obj->post_excerpt;
            $post_meta = get_post_meta($member_obj->ID, organic_beauty_storage_get('options_prefix').'_team_data', true);
            if (empty($position))	$position = $post_meta['team_member_position'];
            if (empty($link))		$link = !empty($post_meta['team_member_link']) ? $post_meta['team_member_link'] : get_permalink($member_obj->ID);
            if (empty($email))		$email = $post_meta['team_member_email'];
            if (empty($photo)) 		$photo = wp_get_attachment_url(get_post_thumbnail_id($member_obj->ID));
            if (empty($socials)) {
                $socials = '';
                $soc_list = $post_meta['team_member_socials'];
                if (is_array($soc_list) && count($soc_list)>0) {
                    $soc_str = '';
                    foreach ($soc_list as $sn=>$sl) {
                        if (!empty($sl))
                            $soc_str .= (!empty($soc_str) ? '|' : '') . ($sn) . '=' . ($sl);
                    }
                    if (!empty($soc_str))
                        $socials = organic_beauty_do_shortcode('[trx_socials size="tiny" shape="round" socials="'.esc_attr($soc_str).'"][/trx_socials]');
                }
            }
        }
        if (empty($photo)) {
            $mult = organic_beauty_get_retina_multiplier();
            if (!empty($email)) $photo = get_avatar($email, $thumb_sizes['w']*$mult);
        } else {
            if ($photo > 0) {
                $attach = wp_get_attachment_image_src( $photo, 'full' );
                if (isset($attach[0]) && $attach[0]!='')
                    $photo = $attach[0];
            }
            $photo = organic_beauty_get_resized_image_tag($photo, $thumb_sizes['w'], $thumb_sizes['h']);
        }
        $post_data = array(
            'post_title' => $name,
            'post_excerpt' => $descr
        );
        $args = array(
            'layout' => organic_beauty_storage_get_array('sc_team_data', 'style'),
            'number' => organic_beauty_storage_get_array('sc_team_data', 'counter'),
            'columns_count' => organic_beauty_storage_get_array('sc_team_data', 'columns'),
            'slider' => organic_beauty_storage_get_array('sc_team_data', 'slider'),
            'show' => false,
            'descr'  => 0,
            'tag_id' => $id,
            'tag_class' => $class,
            'tag_animation' => $animation,
            'tag_css' => $css,
            'tag_css_wh' => organic_beauty_storage_get_array('sc_team_data', 'css_wh'),
            'position' => $position,
            'link' => $link,
            'email' => $email,
            'photo' => $photo,
            'socials' => $socials
        );
        $output = organic_beauty_show_post_layout($args, $post_data);

        return apply_filters('organic_beauty_shortcode_output', $output, 'trx_team_item', $atts, $content);
    }
    add_shortcode('trx_team_item', 'organic_beauty_sc_team_item');
}
// ---------------------------------- [/trx_team] ---------------------------------------



// Add [trx_team] and [trx_team_item] in the shortcodes list
if (!function_exists('organic_beauty_team_reg_shortcodes')) {
    //Handler of add_filter('organic_beauty_action_shortcodes_list',	'organic_beauty_team_reg_shortcodes');
    function organic_beauty_team_reg_shortcodes() {
        if (organic_beauty_storage_isset('shortcodes')) {

            $users = organic_beauty_get_list_users();
            $members = organic_beauty_get_list_posts(false, array(
                    'post_type'=>'team',
                    'orderby'=>'title',
                    'order'=>'asc',
                    'return'=>'title'
                )
            );
            $team_groups = organic_beauty_get_list_terms(false, 'team_group');
            $team_styles = organic_beauty_get_list_templates('team');
            $controls	 = organic_beauty_get_list_slider_controls();

            organic_beauty_sc_map_after('trx_tabs', array(

                // Team
                "trx_team" => array(
                    "title" => esc_html__("Team", 'organic-beauty'),
                    "desc" => wp_kses_data( __("Insert team in your page (post)", 'organic-beauty') ),
                    "decorate" => true,
                    "container" => false,
                    "params" => array(
                        "title" => array(
                            "title" => esc_html__("Title", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Title for the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "subtitle" => array(
                            "title" => esc_html__("Subtitle", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Subtitle for the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "description" => array(
                            "title" => esc_html__("Description", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Short description for the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "textarea"
                        ),
                        "style" => array(
                            "title" => esc_html__("Team style", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select style to display team members", 'organic-beauty') ),
                            "value" => "team-1",
                            "type" => "select",
                            "options" => $team_styles
                        ),
                        "columns" => array(
                            "title" => esc_html__("Columns", 'organic-beauty'),
                            "desc" => wp_kses_data( __("How many columns use to show team members", 'organic-beauty') ),
                            "value" => 3,
                            "min" => 2,
                            "max" => 5,
                            "step" => 1,
                            "type" => "spinner"
                        ),
                        "scheme" => array(
                            "title" => esc_html__("Color scheme", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select color scheme for this block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "checklist",
                            "options" => organic_beauty_get_sc_param('schemes')
                        ),
                        "slider" => array(
                            "title" => esc_html__("Slider", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Use slider to show team members", 'organic-beauty') ),
                            "value" => "no",
                            "type" => "switch",
                            "options" => organic_beauty_get_sc_param('yes_no')
                        ),
                        "controls" => array(
                            "title" => esc_html__("Controls", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Slider controls style and position", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "divider" => true,
                            "value" => "",
                            "type" => "checklist",
                            "dir" => "horizontal",
                            "options" => $controls
                        ),
                        "slides_space" => array(
                            "title" => esc_html__("Space between slides", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Size of space (in px) between slides", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "value" => 0,
                            "min" => 0,
                            "max" => 100,
                            "step" => 10,
                            "type" => "spinner"
                        ),
                        "interval" => array(
                            "title" => esc_html__("Slides change interval", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Slides change interval (in milliseconds: 1000ms = 1s)", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "value" => 7000,
                            "step" => 500,
                            "min" => 0,
                            "type" => "spinner"
                        ),
                        "autoheight" => array(
                            "title" => esc_html__("Autoheight", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Change whole slider's height (make it equal current slide's height)", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "value" => "yes",
                            "type" => "switch",
                            "options" => organic_beauty_get_sc_param('yes_no')
                        ),
                        "align" => array(
                            "title" => esc_html__("Alignment", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Alignment of the team block", 'organic-beauty') ),
                            "divider" => true,
                            "value" => "",
                            "type" => "checklist",
                            "dir" => "horizontal",
                            "options" => organic_beauty_get_sc_param('align')
                        ),
                        "custom" => array(
                            "title" => esc_html__("Custom", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Allow get team members from inner shortcodes (custom) or get it from specified group (cat)", 'organic-beauty') ),
                            "divider" => true,
                            "value" => "no",
                            "type" => "switch",
                            "options" => organic_beauty_get_sc_param('yes_no')
                        ),
                        "cat" => array(
                            "title" => esc_html__("Categories", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select categories (groups) to show team members. If empty - select team members from any category (group) or from IDs list", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "divider" => true,
                            "value" => "",
                            "type" => "select",
                            "style" => "list",
                            "multiple" => true,
                            "options" => organic_beauty_array_merge(array(0 => esc_html__('- Select category -', 'organic-beauty')), $team_groups)
                        ),
                        "count" => array(
                            "title" => esc_html__("Number of posts", 'organic-beauty'),
                            "desc" => wp_kses_data( __("How many posts will be displayed? If used IDs - this parameter ignored.", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => 3,
                            "min" => 1,
                            "max" => 100,
                            "type" => "spinner"
                        ),
                        "offset" => array(
                            "title" => esc_html__("Offset before select posts", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Skip posts before select next part.", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => 0,
                            "min" => 0,
                            "type" => "spinner"
                        ),
                        "orderby" => array(
                            "title" => esc_html__("Post order by", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select desired posts sorting method", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => "title",
                            "type" => "select",
                            "options" => organic_beauty_get_sc_param('sorting')
                        ),
                        "order" => array(
                            "title" => esc_html__("Post order", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select desired posts order", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => "asc",
                            "type" => "switch",
                            "size" => "big",
                            "options" => organic_beauty_get_sc_param('ordering')
                        ),
                        "ids" => array(
                            "title" => esc_html__("Post IDs list", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Comma separated list of posts ID. If set - parameters above are ignored!", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "link" => array(
                            "title" => esc_html__("Button URL", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Link URL for the button at the bottom of the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "link_caption" => array(
                            "title" => esc_html__("Button caption", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Caption for the button at the bottom of the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "width" => organic_beauty_shortcodes_width(),
                        "height" => organic_beauty_shortcodes_height(),
                        "top" => organic_beauty_get_sc_param('top'),
                        "bottom" => organic_beauty_get_sc_param('bottom'),
                        "left" => organic_beauty_get_sc_param('left'),
                        "right" => organic_beauty_get_sc_param('right'),
                        "id" => organic_beauty_get_sc_param('id'),
                        "class" => organic_beauty_get_sc_param('class'),
                        "animation" => organic_beauty_get_sc_param('animation'),
                        "css" => organic_beauty_get_sc_param('css')
                    ),
                    "children" => array(
                        "name" => "trx_team_item",
                        "title" => esc_html__("Member", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Team member", 'organic-beauty') ),
                        "container" => true,
                        "params" => array(
                            "user" => array(
                                "title" => esc_html__("Registerd user", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Select one of registered users (if present) or put name, position, etc. in fields below", 'organic-beauty') ),
                                "value" => "",
                                "type" => "select",
                                "options" => $users
                            ),
                            "member" => array(
                                "title" => esc_html__("Team member", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Select one of team members (if present) or put name, position, etc. in fields below", 'organic-beauty') ),
                                "value" => "",
                                "type" => "select",
                                "options" => $members
                            ),
                            "link" => array(
                                "title" => esc_html__("Link", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Link on team member's personal page", 'organic-beauty') ),
                                "divider" => true,
                                "value" => "",
                                "type" => "text"
                            ),
                            "name" => array(
                                "title" => esc_html__("Name", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Team member's name", 'organic-beauty') ),
                                "divider" => true,
                                "dependency" => array(
                                    'user' => array('is_empty', 'none'),
                                    'member' => array('is_empty', 'none')
                                ),
                                "value" => "",
                                "type" => "text"
                            ),
                            "position" => array(
                                "title" => esc_html__("Position", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Team member's position", 'organic-beauty') ),
                                "dependency" => array(
                                    'user' => array('is_empty', 'none'),
                                    'member' => array('is_empty', 'none')
                                ),
                                "value" => "",
                                "type" => "text"
                            ),
                            "email" => array(
                                "title" => esc_html__("E-mail", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Team member's e-mail", 'organic-beauty') ),
                                "dependency" => array(
                                    'user' => array('is_empty', 'none'),
                                    'member' => array('is_empty', 'none')
                                ),
                                "value" => "",
                                "type" => "text"
                            ),
                            "photo" => array(
                                "title" => esc_html__("Photo", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Team member's photo (avatar)", 'organic-beauty') ),
                                "dependency" => array(
                                    'user' => array('is_empty', 'none'),
                                    'member' => array('is_empty', 'none')
                                ),
                                "value" => "",
                                "readonly" => false,
                                "type" => "media"
                            ),
                            "socials" => array(
                                "title" => esc_html__("Socials", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Team member's socials icons: name=url|name=url... For example: facebook=http://facebook.com/myaccount|twitter=http://twitter.com/myaccount", 'organic-beauty') ),
                                "dependency" => array(
                                    'user' => array('is_empty', 'none'),
                                    'member' => array('is_empty', 'none')
                                ),
                                "value" => "",
                                "type" => "text"
                            ),
                            "_content_" => array(
                                "title" => esc_html__("Description", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Team member's short description", 'organic-beauty') ),
                                "divider" => true,
                                "rows" => 4,
                                "value" => "",
                                "type" => "textarea"
                            ),
                            "id" => organic_beauty_get_sc_param('id'),
                            "class" => organic_beauty_get_sc_param('class'),
                            "animation" => organic_beauty_get_sc_param('animation'),
                            "css" => organic_beauty_get_sc_param('css')
                        )
                    )
                )

            ));
        }
    }
}


// Add [trx_team] and [trx_team_item] in the VC shortcodes list
if (!function_exists('organic_beauty_team_reg_shortcodes_vc')) {
    //Handler of add_filter('organic_beauty_action_shortcodes_list_vc',	'organic_beauty_team_reg_shortcodes_vc');
    function organic_beauty_team_reg_shortcodes_vc() {

        $users = organic_beauty_get_list_users();
        $members = organic_beauty_get_list_posts(false, array(
                'post_type'=>'team',
                'orderby'=>'title',
                'order'=>'asc',
                'return'=>'title'
            )
        );
        $team_groups = organic_beauty_get_list_terms(false, 'team_group');
        $team_styles = organic_beauty_get_list_templates('team');
        $controls	 = organic_beauty_get_list_slider_controls();

        // Team
        vc_map( array(
            "base" => "trx_team",
            "name" => esc_html__("Team", 'organic-beauty'),
            "description" => wp_kses_data( __("Insert team members", 'organic-beauty') ),
            "category" => esc_html__('Content', 'organic-beauty'),
            'icon' => 'icon_trx_team',
            "class" => "trx_sc_columns trx_sc_team",
            "content_element" => true,
            "is_container" => true,
            "show_settings_on_create" => true,
            "as_parent" => array('only' => 'trx_team_item'),
            "params" => array(
                array(
                    "param_name" => "style",
                    "heading" => esc_html__("Team style", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select style to display team members", 'organic-beauty') ),
                    "class" => "",
                    "admin_label" => true,
                    "value" => array_flip($team_styles),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "scheme",
                    "heading" => esc_html__("Color scheme", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select color scheme for this block", 'organic-beauty') ),
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('schemes')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "slider",
                    "heading" => esc_html__("Slider", 'organic-beauty'),
                    "description" => wp_kses_data( __("Use slider to show team members", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    "class" => "",
                    "std" => "no",
                    "value" => array_flip((array)organic_beauty_get_sc_param('yes_no')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "controls",
                    "heading" => esc_html__("Controls", 'organic-beauty'),
                    "description" => wp_kses_data( __("Slider controls style and position", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "std" => "no",
                    "value" => array_flip($controls),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "slides_space",
                    "heading" => esc_html__("Space between slides", 'organic-beauty'),
                    "description" => wp_kses_data( __("Size of space (in px) between slides", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "value" => "0",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "interval",
                    "heading" => esc_html__("Slides change interval", 'organic-beauty'),
                    "description" => wp_kses_data( __("Slides change interval (in milliseconds: 1000ms = 1s)", 'organic-beauty') ),
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "value" => "7000",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "autoheight",
                    "heading" => esc_html__("Autoheight", 'organic-beauty'),
                    "description" => wp_kses_data( __("Change whole slider's height (make it equal current slide's height)", 'organic-beauty') ),
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "value" => array("Autoheight" => "yes" ),
                    "type" => "checkbox"
                ),
                array(
                    "param_name" => "align",
                    "heading" => esc_html__("Alignment", 'organic-beauty'),
                    "description" => wp_kses_data( __("Alignment of the team block", 'organic-beauty') ),
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('align')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "custom",
                    "heading" => esc_html__("Custom", 'organic-beauty'),
                    "description" => wp_kses_data( __("Allow get team members from inner shortcodes (custom) or get it from specified group (cat)", 'organic-beauty') ),
                    "class" => "",
                    "value" => array("Custom members" => "yes" ),
                    "type" => "checkbox"
                ),
                array(
                    "param_name" => "title",
                    "heading" => esc_html__("Title", 'organic-beauty'),
                    "description" => wp_kses_data( __("Title for the block", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "subtitle",
                    "heading" => esc_html__("Subtitle", 'organic-beauty'),
                    "description" => wp_kses_data( __("Subtitle for the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "description",
                    "heading" => esc_html__("Description", 'organic-beauty'),
                    "description" => wp_kses_data( __("Description for the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textarea"
                ),
                array(
                    "param_name" => "cat",
                    "heading" => esc_html__("Categories", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select category to show team members. If empty - select team members from any category (group) or from IDs list", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_array_merge(array(0 => esc_html__('- Select category -', 'organic-beauty')), $team_groups)),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "columns",
                    "heading" => esc_html__("Columns", 'organic-beauty'),
                    "description" => wp_kses_data( __("How many columns use to show team members", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    "admin_label" => true,
                    "class" => "",
                    "value" => "3",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "count",
                    "heading" => esc_html__("Number of posts", 'organic-beauty'),
                    "description" => wp_kses_data( __("How many posts will be displayed? If used IDs - this parameter ignored.", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => "3",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "offset",
                    "heading" => esc_html__("Offset before select posts", 'organic-beauty'),
                    "description" => wp_kses_data( __("Skip posts before select next part.", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => "0",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "orderby",
                    "heading" => esc_html__("Post sorting", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select desired posts sorting method", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "std" => "title",
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('sorting')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "order",
                    "heading" => esc_html__("Post order", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select desired posts order", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "std" => "asc",
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('ordering')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "ids",
                    "heading" => esc_html__("Team member's IDs list", 'organic-beauty'),
                    "description" => wp_kses_data( __("Comma separated list of team members's ID. If set - parameters above (category, count, order, etc.)  are ignored!", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "link",
                    "heading" => esc_html__("Button URL", 'organic-beauty'),
                    "description" => wp_kses_data( __("Link URL for the button at the bottom of the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "link_caption",
                    "heading" => esc_html__("Button caption", 'organic-beauty'),
                    "description" => wp_kses_data( __("Caption for the button at the bottom of the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                organic_beauty_vc_width(),
                organic_beauty_vc_height(),
                organic_beauty_get_vc_param('margin_top'),
                organic_beauty_get_vc_param('margin_bottom'),
                organic_beauty_get_vc_param('margin_left'),
                organic_beauty_get_vc_param('margin_right'),
                organic_beauty_get_vc_param('id'),
                organic_beauty_get_vc_param('class'),
                organic_beauty_get_vc_param('animation'),
                organic_beauty_get_vc_param('css')
            ),
            'default_content' => '
					[trx_team_item user="' . esc_html__( 'Member 1', 'organic-beauty' ) . '"][/trx_team_item]
					[trx_team_item user="' . esc_html__( 'Member 2', 'organic-beauty' ) . '"][/trx_team_item]
					[trx_team_item user="' . esc_html__( 'Member 4', 'organic-beauty' ) . '"][/trx_team_item]
				',
            'js_view' => 'VcTrxColumnsView'
        ) );


        vc_map( array(
            "base" => "trx_team_item",
            "name" => esc_html__("Team member", 'organic-beauty'),
            "description" => wp_kses_data( __("Team member - all data pull out from it account on your site", 'organic-beauty') ),
            "show_settings_on_create" => true,
            "class" => "trx_sc_collection trx_sc_column_item trx_sc_team_item",
            "content_element" => true,
            "is_container" => true,
            'icon' => 'icon_trx_team_item',
            "as_child" => array('only' => 'trx_team'),
            "as_parent" => array('except' => 'trx_team'),
            "params" => array(
                array(
                    "param_name" => "user",
                    "heading" => esc_html__("Registered user", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select one of registered users (if present) or put name, position, etc. in fields below", 'organic-beauty') ),
                    "admin_label" => true,
                    "class" => "",
                    "value" => array_flip($users),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "member",
                    "heading" => esc_html__("Team member", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select one of team members (if present) or put name, position, etc. in fields below", 'organic-beauty') ),
                    "admin_label" => true,
                    "class" => "",
                    "value" => array_flip($members),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "link",
                    "heading" => esc_html__("Link", 'organic-beauty'),
                    "description" => wp_kses_data( __("Link on team member's personal page", 'organic-beauty') ),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "name",
                    "heading" => esc_html__("Name", 'organic-beauty'),
                    "description" => wp_kses_data( __("Team member's name", 'organic-beauty') ),
                    "admin_label" => true,
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "position",
                    "heading" => esc_html__("Position", 'organic-beauty'),
                    "description" => wp_kses_data( __("Team member's position", 'organic-beauty') ),
                    "admin_label" => true,
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "email",
                    "heading" => esc_html__("E-mail", 'organic-beauty'),
                    "description" => wp_kses_data( __("Team member's e-mail", 'organic-beauty') ),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "photo",
                    "heading" => esc_html__("Member's Photo", 'organic-beauty'),
                    "description" => wp_kses_data( __("Team member's photo (avatar)", 'organic-beauty') ),
                    "class" => "",
                    "value" => "",
                    "type" => "attach_image"
                ),
                array(
                    "param_name" => "socials",
                    "heading" => esc_html__("Socials", 'organic-beauty'),
                    "description" => wp_kses_data( __("Team member's socials icons: name=url|name=url... For example: facebook=http://facebook.com/myaccount|twitter=http://twitter.com/myaccount", 'organic-beauty') ),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                organic_beauty_get_vc_param('id'),
                organic_beauty_get_vc_param('class'),
                organic_beauty_get_vc_param('animation'),
                organic_beauty_get_vc_param('css')
            ),
            'js_view' => 'VcTrxColumnItemView'
        ) );

        class WPBakeryShortCode_Trx_Team extends Organic_Beauty_Vc_ShortCodeColumns {}
        class WPBakeryShortCode_Trx_Team_Item extends Organic_Beauty_Vc_ShortCodeCollection {}

    }
}

// ---------------------------------- [trx_testimonials] ---------------------------------------

if (!function_exists('organic_beauty_sc_testimonials')) {
    function organic_beauty_sc_testimonials($atts, $content=null){
        if (organic_beauty_in_shortcode_blogger()) return '';
        extract(organic_beauty_html_decode(shortcode_atts(array(
            // Individual params
            "style" => "testimonials-1",
            "columns" => 1,
            "slider" => "yes",
            "slides_space" => 0,
            "controls" => "no",
            "interval" => "",
            "autoheight" => "no",
            "align" => "",
            "custom" => "no",
            "ids" => "",
            "cat" => "",
            "count" => "3",
            "offset" => "",
            "orderby" => "date",
            "order" => "desc",
            "scheme" => "",
            "bg_color" => "",
            "bg_image" => "",
            "bg_overlay" => "",
            "bg_texture" => "",
            "title" => "",
            "subtitle" => "",
            "description" => "",
            // Common params
            "id" => "",
            "class" => "",
            "animation" => "",
            "css" => "",
            "width" => "",
            "height" => "",
            "top" => "",
            "bottom" => "",
            "left" => "",
            "right" => ""
        ), $atts)));

        if (empty($id)) $id = "sc_testimonials_".str_replace('.', '', mt_rand());
        if (empty($width)) $width = "100%";
        if (!empty($height) && organic_beauty_param_is_on($autoheight)) $autoheight = "no";
        if (empty($interval)) $interval = mt_rand(5000, 10000);

        if ($bg_image > 0) {
            $attach = wp_get_attachment_image_src( $bg_image, 'full' );
            if (isset($attach[0]) && $attach[0]!='')
                $bg_image = $attach[0];
        }

        if ($bg_overlay > 0) {
            if ($bg_color=='') $bg_color = organic_beauty_get_scheme_color('bg');
            $rgb = organic_beauty_hex2rgb($bg_color);
        }

        $class .= ($class ? ' ' : '') . organic_beauty_get_css_position_as_classes($top, $right, $bottom, $left);

        $ws = organic_beauty_get_css_dimensions_from_values($width);
        $hs = organic_beauty_get_css_dimensions_from_values('', $height);
        $css .= ($hs) . ($ws);

        $count = max(1, (int) $count);
        $columns = max(1, min(12, (int) $columns));
        if (organic_beauty_param_is_off($custom) && $count < $columns) $columns = $count;

        organic_beauty_storage_set('sc_testimonials_data', array(
                'id' => $id,
                'style' => $style,
                'columns' => $columns,
                'counter' => 0,
                'slider' => $slider,
                'css_wh' => $ws . $hs
            )
        );

        if (organic_beauty_param_is_on($slider)) organic_beauty_enqueue_slider('swiper');

        $output = ($bg_color!='' || $bg_image!='' || $bg_overlay>0 || $bg_texture>0 || organic_beauty_strlen($bg_texture)>2 || ($scheme && !organic_beauty_param_is_off($scheme) && !organic_beauty_param_is_inherit($scheme))
                ? '<div class="sc_testimonials_wrap sc_section'
                . ($scheme && !organic_beauty_param_is_off($scheme) && !organic_beauty_param_is_inherit($scheme) ? ' scheme_'.esc_attr($scheme) : '')
                . '"'
                .' style="'
                . ($bg_color !== '' && $bg_overlay==0 ? 'background-color:' . esc_attr($bg_color) . ';' : '')
                . ($bg_image !== '' ? 'background-image:url(' . esc_url($bg_image) . ');' : '')
                . '"'
                . (!organic_beauty_param_is_off($animation) ? ' data-animation="'.esc_attr(organic_beauty_get_animation_classes($animation)).'"' : '')
                . '>'
                . '<div class="sc_section_overlay'.($bg_texture>0 ? ' texture_bg_'.esc_attr($bg_texture) : '') . '"'
                . ' style="' . ($bg_overlay>0 ? 'background-color:rgba('.(int)$rgb['r'].','.(int)$rgb['g'].','.(int)$rgb['b'].','.min(1, max(0, $bg_overlay)).');' : '')
                . (organic_beauty_strlen($bg_texture)>2 ? 'background-image:url('.esc_url($bg_texture).');' : '')
                . '"'
                . ($bg_overlay > 0 ? ' data-overlay="'.esc_attr($bg_overlay).'" data-bg_color="'.esc_attr($bg_color).'"' : '')
                . '>'
                : '')
            . '<div' . ($id ? ' id="'.esc_attr($id).'"' : '')
            . ' class="sc_testimonials sc_testimonials_style_'.esc_attr($style)
            . ' ' . esc_attr(organic_beauty_get_template_property($style, 'container_classes'))
            . (!empty($class) ? ' '.esc_attr($class) : '')
            . ($align!='' && $align!='none' ? ' align'.esc_attr($align) : '')
            . '"'
            . ($bg_color=='' && $bg_image=='' && $bg_overlay==0 && ($bg_texture=='' || $bg_texture=='0') && !organic_beauty_param_is_off($animation) ? ' data-animation="'.esc_attr(organic_beauty_get_animation_classes($animation)).'"' : '')
            . ($css!='' ? ' style="'.esc_attr($css).'"' : '')
            . '>'
            . (!empty($subtitle) ? '<h6 class="sc_testimonials_subtitle sc_item_subtitle">' . trim(organic_beauty_strmacros($subtitle)) . '</h6>' : '')
            . (!empty($title) ? '<h2 class="sc_testimonials_title sc_item_title' . (empty($description) ? ' sc_item_title_without_descr' : ' sc_item_title_without_descr') . '">' . trim(organic_beauty_strmacros($title)) . '</h2>' : '')
            . (!empty($description) ? '<div class="sc_testimonials_descr sc_item_descr">' . trim(organic_beauty_strmacros($description)) . '</div>' : '')
            . (organic_beauty_param_is_on($slider)
                ? ('<div class="sc_slider_swiper swiper-slider-container'
                    . ' ' . esc_attr(organic_beauty_get_slider_controls_classes($controls))
                    . (organic_beauty_param_is_on($autoheight) ? ' sc_slider_height_auto' : '')
                    . ($hs ? ' sc_slider_height_fixed' : '')
                    . '"'
                    . (!empty($width) && organic_beauty_strpos($width, '%')===false ? ' data-old-width="' . esc_attr($width) . '"' : '')
                    . (!empty($height) && organic_beauty_strpos($height, '%')===false ? ' data-old-height="' . esc_attr($height) . '"' : '')
                    . ((int) $interval > 0 ? ' data-interval="'.esc_attr($interval).'"' : '')
                    . ($columns > 1 ? ' data-slides-per-view="' . esc_attr($columns) . '"' : '')
                    . ($slides_space > 0 ? ' data-slides-space="' . esc_attr($slides_space) . '"' : '')
                    . ' data-slides-min-width="250"'
                    . '>'
                    . '<div class="slides swiper-wrapper">')
                : ($columns > 1
                    ? '<div class="sc_columns columns_wrap">'
                    : '')
            );

        if (organic_beauty_param_is_on($custom) && $content) {
            $output .= do_shortcode($content);
        } else {
            global $post;

            if (!empty($ids)) {
                $posts = explode(',', $ids);
                $count = count($posts);
            }

            $args = array(
                'post_type' => 'testimonial',
                'post_status' => 'publish',
                'posts_per_page' => $count,
                'ignore_sticky_posts' => true,
                'order' => $order=='asc' ? 'asc' : 'desc',
            );

            if ($offset > 0 && empty($ids)) {
                $args['offset'] = $offset;
            }

            $args = organic_beauty_query_add_sort_order($args, $orderby, $order);
            $args = organic_beauty_query_add_posts_and_cats($args, $ids, 'testimonial', $cat, 'testimonial_group');

            $query = new WP_Query( $args );

            $post_number = 0;

            while ( $query->have_posts() ) {
                $query->the_post();
                $post_number++;
                $args = array(
                    'layout' => $style,
                    'show' => false,
                    'number' => $post_number,
                    'posts_on_page' => ($count > 0 ? $count : $query->found_posts),
                    "descr" => organic_beauty_get_custom_option('post_excerpt_maxlength'.($columns > 1 ? '_masonry' : '')),
                    "orderby" => $orderby,
                    'content' => false,
                    'terms_list' => false,
                    'columns_count' => $columns,
                    'slider' => $slider,
                    'tag_id' => $id ? $id . '_' . $post_number : '',
                    'tag_class' => '',
                    'tag_animation' => '',
                    'tag_css' => '',
                    'tag_css_wh' => $ws . $hs
                );
                $post_data = organic_beauty_get_post_data($args);
                $post_data['post_content'] = wpautop($post_data['post_content']);	// Add <p> around text and paragraphs. Need separate call because 'content'=>false (see above)
                $post_meta = get_post_meta($post_data['post_id'], organic_beauty_storage_get('options_prefix').'_testimonial_data', true);
                $thumb_sizes = organic_beauty_get_thumb_sizes(array('layout' => $style));
                $args['author'] = $post_meta['testimonial_author'];
                $args['position'] = $post_meta['testimonial_position'];
                $args['link'] = !empty($post_meta['testimonial_link']) ? $post_meta['testimonial_link'] : '';
                $args['email'] = $post_meta['testimonial_email'];
                $args['photo'] = $post_data['post_thumb'];
                $mult = organic_beauty_get_retina_multiplier();
                if (empty($args['photo']) && !empty($args['email'])) $args['photo'] = get_avatar($args['email'], $thumb_sizes['w']*$mult);
                $output .= organic_beauty_show_post_layout($args, $post_data);
            }
            wp_reset_postdata();
        }

        if (organic_beauty_param_is_on($slider)) {
            $output .= '</div>'
                . '<div class="sc_slider_controls_wrap"><a class="sc_slider_prev" href="#"></a><a class="sc_slider_next" href="#"></a></div>'
                . '<div class="sc_slider_pagination_wrap"></div>'
                . '</div>';
        } else if ($columns > 1) {
            $output .= '</div>';
        }

        $output .= '</div>'
            . ($bg_color!='' || $bg_image!='' || $bg_overlay>0 || $bg_texture>0 || organic_beauty_strlen($bg_texture)>2 || ($scheme && !organic_beauty_param_is_off($scheme) && !organic_beauty_param_is_inherit($scheme))
                ?  '</div></div>'
                : '');

        // Add template specific scripts and styles
        do_action('organic_beauty_action_blog_scripts', $style);

        return apply_filters('organic_beauty_shortcode_output', $output, 'trx_testimonials', $atts, $content);
    }
    add_shortcode('trx_testimonials', 'organic_beauty_sc_testimonials');
}


if (!function_exists('organic_beauty_sc_testimonials_item')) {
    function organic_beauty_sc_testimonials_item($atts, $content=null){
        if (organic_beauty_in_shortcode_blogger()) return '';
        extract(organic_beauty_html_decode(shortcode_atts(array(
            // Individual params
            "author" => "",
            "position" => "",
            "link" => "",
            "photo" => "",
            "email" => "",
            // Common params
            "id" => "",
            "class" => "",
            "css" => "",
        ), $atts)));

        organic_beauty_storage_inc_array('sc_testimonials_data', 'counter');

        $id = $id ? $id : (organic_beauty_storage_get_array('sc_testimonials_data', 'id') ? organic_beauty_storage_get_array('sc_testimonials_data', 'id') . '_' . organic_beauty_storage_get_array('sc_testimonials_data', 'counter') : '');

        $thumb_sizes = organic_beauty_get_thumb_sizes(array('layout' => organic_beauty_storage_get_array('sc_testimonials_data', 'style')));

        if (empty($photo)) {
            if (!empty($email))
                $mult = organic_beauty_get_retina_multiplier();
            $photo = get_avatar($email, $thumb_sizes['w']*$mult);
        } else {
            if ($photo > 0) {
                $attach = wp_get_attachment_image_src( $photo, 'full' );
                if (isset($attach[0]) && $attach[0]!='')
                    $photo = $attach[0];
            }
            $photo = organic_beauty_get_resized_image_tag($photo, $thumb_sizes['w'], $thumb_sizes['h']);
        }

        $post_data = array(
            'post_content' => do_shortcode($content)
        );
        $args = array(
            'layout' => organic_beauty_storage_get_array('sc_testimonials_data', 'style'),
            'number' => organic_beauty_storage_get_array('sc_testimonials_data', 'counter'),
            'columns_count' => organic_beauty_storage_get_array('sc_testimonials_data', 'columns'),
            'slider' => organic_beauty_storage_get_array('sc_testimonials_data', 'slider'),
            'show' => false,
            'descr'  => 0,
            'tag_id' => $id,
            'tag_class' => $class,
            'tag_animation' => '',
            'tag_css' => $css,
            'tag_css_wh' => organic_beauty_storage_get_array('sc_testimonials_data', 'css_wh'),
            'author' => $author,
            'position' => $position,
            'link' => $link,
            'email' => $email,
            'photo' => $photo
        );
        $output = organic_beauty_show_post_layout($args, $post_data);

        return apply_filters('organic_beauty_shortcode_output', $output, 'trx_testimonials_item', $atts, $content);
    }
    add_shortcode('trx_testimonials_item', 'organic_beauty_sc_testimonials_item');
}
// ---------------------------------- [/trx_testimonials] ---------------------------------------



// Add [trx_testimonials] and [trx_testimonials_item] in the shortcodes list
if (!function_exists('organic_beauty_testimonials_reg_shortcodes')) {
    //Handler of add_filter('organic_beauty_action_shortcodes_list',	'organic_beauty_testimonials_reg_shortcodes');
    function organic_beauty_testimonials_reg_shortcodes() {
        if (organic_beauty_storage_isset('shortcodes')) {

            $testimonials_groups = organic_beauty_get_list_terms(false, 'testimonial_group');
            $testimonials_styles = organic_beauty_get_list_templates('testimonials');
            $controls = organic_beauty_get_list_slider_controls();

            organic_beauty_sc_map_before('trx_title', array(

                // Testimonials
                "trx_testimonials" => array(
                    "title" => esc_html__("Testimonials", 'organic-beauty'),
                    "desc" => wp_kses_data( __("Insert testimonials into post (page)", 'organic-beauty') ),
                    "decorate" => true,
                    "container" => false,
                    "params" => array(
                        "title" => array(
                            "title" => esc_html__("Title", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Title for the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "subtitle" => array(
                            "title" => esc_html__("Subtitle", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Subtitle for the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "description" => array(
                            "title" => esc_html__("Description", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Short description for the block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "textarea"
                        ),
                        "style" => array(
                            "title" => esc_html__("Testimonials style", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select style to display testimonials", 'organic-beauty') ),
                            "value" => "testimonials-1",
                            "type" => "select",
                            "options" => $testimonials_styles
                        ),
                        "columns" => array(
                            "title" => esc_html__("Columns", 'organic-beauty'),
                            "desc" => wp_kses_data( __("How many columns use to show testimonials", 'organic-beauty') ),
                            "value" => 1,
                            "min" => 1,
                            "max" => 6,
                            "step" => 1,
                            "type" => "spinner"
                        ),
                        "slider" => array(
                            "title" => esc_html__("Slider", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Use slider to show testimonials", 'organic-beauty') ),
                            "value" => "yes",
                            "type" => "switch",
                            "options" => organic_beauty_get_sc_param('yes_no')
                        ),
                        "controls" => array(
                            "title" => esc_html__("Controls", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Slider controls style and position", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "divider" => true,
                            "value" => "",
                            "type" => "checklist",
                            "dir" => "horizontal",
                            "options" => $controls
                        ),
                        "slides_space" => array(
                            "title" => esc_html__("Space between slides", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Size of space (in px) between slides", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "value" => 0,
                            "min" => 0,
                            "max" => 100,
                            "step" => 10,
                            "type" => "spinner"
                        ),
                        "interval" => array(
                            "title" => esc_html__("Slides change interval", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Slides change interval (in milliseconds: 1000ms = 1s)", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "value" => 7000,
                            "step" => 500,
                            "min" => 0,
                            "type" => "spinner"
                        ),
                        "autoheight" => array(
                            "title" => esc_html__("Autoheight", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Change whole slider's height (make it equal current slide's height)", 'organic-beauty') ),
                            "dependency" => array(
                                'slider' => array('yes')
                            ),
                            "value" => "yes",
                            "type" => "switch",
                            "options" => organic_beauty_get_sc_param('yes_no')
                        ),
                        "align" => array(
                            "title" => esc_html__("Alignment", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Alignment of the testimonials block", 'organic-beauty') ),
                            "divider" => true,
                            "value" => "",
                            "type" => "checklist",
                            "dir" => "horizontal",
                            "options" => organic_beauty_get_sc_param('align')
                        ),
                        "custom" => array(
                            "title" => esc_html__("Custom", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Allow get testimonials from inner shortcodes (custom) or get it from specified group (cat)", 'organic-beauty') ),
                            "divider" => true,
                            "value" => "no",
                            "type" => "switch",
                            "options" => organic_beauty_get_sc_param('yes_no')
                        ),
                        "cat" => array(
                            "title" => esc_html__("Categories", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select categories (groups) to show testimonials. If empty - select testimonials from any category (group) or from IDs list", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "divider" => true,
                            "value" => "",
                            "type" => "select",
                            "style" => "list",
                            "multiple" => true,
                            "options" => organic_beauty_array_merge(array(0 => esc_html__('- Select category -', 'organic-beauty')), $testimonials_groups)
                        ),
                        "count" => array(
                            "title" => esc_html__("Number of posts", 'organic-beauty'),
                            "desc" => wp_kses_data( __("How many posts will be displayed? If used IDs - this parameter ignored.", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => 3,
                            "min" => 1,
                            "max" => 100,
                            "type" => "spinner"
                        ),
                        "offset" => array(
                            "title" => esc_html__("Offset before select posts", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Skip posts before select next part.", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => 0,
                            "min" => 0,
                            "type" => "spinner"
                        ),
                        "orderby" => array(
                            "title" => esc_html__("Post order by", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select desired posts sorting method", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => "date",
                            "type" => "select",
                            "options" => organic_beauty_get_sc_param('sorting')
                        ),
                        "order" => array(
                            "title" => esc_html__("Post order", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select desired posts order", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => "desc",
                            "type" => "switch",
                            "size" => "big",
                            "options" => organic_beauty_get_sc_param('ordering')
                        ),
                        "ids" => array(
                            "title" => esc_html__("Post IDs list", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Comma separated list of posts ID. If set - parameters above are ignored!", 'organic-beauty') ),
                            "dependency" => array(
                                'custom' => array('no')
                            ),
                            "value" => "",
                            "type" => "text"
                        ),
                        "scheme" => array(
                            "title" => esc_html__("Color scheme", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select color scheme for this block", 'organic-beauty') ),
                            "value" => "",
                            "type" => "checklist",
                            "options" => organic_beauty_get_sc_param('schemes')
                        ),
                        "bg_color" => array(
                            "title" => esc_html__("Background color", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Any background color for this section", 'organic-beauty') ),
                            "value" => "",
                            "type" => "color"
                        ),
                        "bg_image" => array(
                            "title" => esc_html__("Background image URL", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Select or upload image or write URL from other site for the background", 'organic-beauty') ),
                            "readonly" => false,
                            "value" => "",
                            "type" => "media"
                        ),
                        "bg_overlay" => array(
                            "title" => esc_html__("Overlay", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Overlay color opacity (from 0.0 to 1.0)", 'organic-beauty') ),
                            "min" => "0",
                            "max" => "1",
                            "step" => "0.1",
                            "value" => "0",
                            "type" => "spinner"
                        ),
                        "bg_texture" => array(
                            "title" => esc_html__("Texture", 'organic-beauty'),
                            "desc" => wp_kses_data( __("Predefined texture style from 1 to 11. 0 - without texture.", 'organic-beauty') ),
                            "min" => "0",
                            "max" => "11",
                            "step" => "1",
                            "value" => "0",
                            "type" => "spinner"
                        ),
                        "width" => organic_beauty_shortcodes_width(),
                        "height" => organic_beauty_shortcodes_height(),
                        "top" => organic_beauty_get_sc_param('top'),
                        "bottom" => organic_beauty_get_sc_param('bottom'),
                        "left" => organic_beauty_get_sc_param('left'),
                        "right" => organic_beauty_get_sc_param('right'),
                        "id" => organic_beauty_get_sc_param('id'),
                        "class" => organic_beauty_get_sc_param('class'),
                        "animation" => organic_beauty_get_sc_param('animation'),
                        "css" => organic_beauty_get_sc_param('css')
                    ),
                    "children" => array(
                        "name" => "trx_testimonials_item",
                        "title" => esc_html__("Item", 'organic-beauty'),
                        "desc" => wp_kses_data( __("Testimonials item (custom parameters)", 'organic-beauty') ),
                        "container" => true,
                        "params" => array(
                            "author" => array(
                                "title" => esc_html__("Author", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Name of the testimonmials author", 'organic-beauty') ),
                                "value" => "",
                                "type" => "text"
                            ),
                            "link" => array(
                                "title" => esc_html__("Link", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Link URL to the testimonmials author page", 'organic-beauty') ),
                                "value" => "",
                                "type" => "text"
                            ),
                            "email" => array(
                                "title" => esc_html__("E-mail", 'organic-beauty'),
                                "desc" => wp_kses_data( __("E-mail of the testimonmials author (to get gravatar)", 'organic-beauty') ),
                                "value" => "",
                                "type" => "text"
                            ),
                            "photo" => array(
                                "title" => esc_html__("Photo", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Select or upload photo of testimonmials author or write URL of photo from other site", 'organic-beauty') ),
                                "value" => "",
                                "type" => "media"
                            ),
                            "_content_" => array(
                                "title" => esc_html__("Testimonials text", 'organic-beauty'),
                                "desc" => wp_kses_data( __("Current testimonials text", 'organic-beauty') ),
                                "divider" => true,
                                "rows" => 4,
                                "value" => "",
                                "type" => "textarea"
                            ),
                            "id" => organic_beauty_get_sc_param('id'),
                            "class" => organic_beauty_get_sc_param('class'),
                            "css" => organic_beauty_get_sc_param('css')
                        )
                    )
                )

            ));
        }
    }
}


// Add [trx_testimonials] and [trx_testimonials_item] in the VC shortcodes list
if (!function_exists('organic_beauty_testimonials_reg_shortcodes_vc')) {
    //Handler of add_filter('organic_beauty_action_shortcodes_list_vc',	'organic_beauty_testimonials_reg_shortcodes_vc');
    function organic_beauty_testimonials_reg_shortcodes_vc() {

        $testimonials_groups = organic_beauty_get_list_terms(false, 'testimonial_group');
        $testimonials_styles = organic_beauty_get_list_templates('testimonials');
        $controls			 = organic_beauty_get_list_slider_controls();

        // Testimonials
        vc_map( array(
            "base" => "trx_testimonials",
            "name" => esc_html__("Testimonials", 'organic-beauty'),
            "description" => wp_kses_data( __("Insert testimonials slider", 'organic-beauty') ),
            "category" => esc_html__('Content', 'organic-beauty'),
            'icon' => 'icon_trx_testimonials',
            "class" => "trx_sc_columns trx_sc_testimonials",
            "content_element" => true,
            "is_container" => true,
            "show_settings_on_create" => true,
            "as_parent" => array('only' => 'trx_testimonials_item'),
            "params" => array(
                array(
                    "param_name" => "style",
                    "heading" => esc_html__("Testimonials style", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select style to display testimonials", 'organic-beauty') ),
                    "class" => "",
                    "admin_label" => true,
                    "value" => array_flip($testimonials_styles),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "slider",
                    "heading" => esc_html__("Slider", 'organic-beauty'),
                    "description" => wp_kses_data( __("Use slider to show testimonials", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    "class" => "",
                    "std" => "yes",
                    "value" => array_flip((array)organic_beauty_get_sc_param('yes_no')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "controls",
                    "heading" => esc_html__("Controls", 'organic-beauty'),
                    "description" => wp_kses_data( __("Slider controls style and position", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "std" => "no",
                    "value" => array_flip($controls),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "slides_space",
                    "heading" => esc_html__("Space between slides", 'organic-beauty'),
                    "description" => wp_kses_data( __("Size of space (in px) between slides", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "value" => "0",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "interval",
                    "heading" => esc_html__("Slides change interval", 'organic-beauty'),
                    "description" => wp_kses_data( __("Slides change interval (in milliseconds: 1000ms = 1s)", 'organic-beauty') ),
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "value" => "7000",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "autoheight",
                    "heading" => esc_html__("Autoheight", 'organic-beauty'),
                    "description" => wp_kses_data( __("Change whole slider's height (make it equal current slide's height)", 'organic-beauty') ),
                    "group" => esc_html__('Slider', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'slider',
                        'value' => 'yes'
                    ),
                    "class" => "",
                    "value" => array("Autoheight" => "yes" ),
                    "type" => "checkbox"
                ),
                array(
                    "param_name" => "align",
                    "heading" => esc_html__("Alignment", 'organic-beauty'),
                    "description" => wp_kses_data( __("Alignment of the testimonials block", 'organic-beauty') ),
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('align')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "custom",
                    "heading" => esc_html__("Custom", 'organic-beauty'),
                    "description" => wp_kses_data( __("Allow get testimonials from inner shortcodes (custom) or get it from specified group (cat)", 'organic-beauty') ),
                    "class" => "",
                    "value" => array("Custom slides" => "yes" ),
                    "type" => "checkbox"
                ),
                array(
                    "param_name" => "title",
                    "heading" => esc_html__("Title", 'organic-beauty'),
                    "description" => wp_kses_data( __("Title for the block", 'organic-beauty') ),
                    "admin_label" => true,
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "subtitle",
                    "heading" => esc_html__("Subtitle", 'organic-beauty'),
                    "description" => wp_kses_data( __("Subtitle for the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "description",
                    "heading" => esc_html__("Description", 'organic-beauty'),
                    "description" => wp_kses_data( __("Description for the block", 'organic-beauty') ),
                    "group" => esc_html__('Captions', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textarea"
                ),
                array(
                    "param_name" => "cat",
                    "heading" => esc_html__("Categories", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select categories (groups) to show testimonials. If empty - select testimonials from any category (group) or from IDs list", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_array_merge(array(0 => esc_html__('- Select category -', 'organic-beauty')), $testimonials_groups)),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "columns",
                    "heading" => esc_html__("Columns", 'organic-beauty'),
                    "description" => wp_kses_data( __("How many columns use to show testimonials", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    "admin_label" => true,
                    "class" => "",
                    "value" => "1",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "count",
                    "heading" => esc_html__("Number of posts", 'organic-beauty'),
                    "description" => wp_kses_data( __("How many posts will be displayed? If used IDs - this parameter ignored.", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => "3",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "offset",
                    "heading" => esc_html__("Offset before select posts", 'organic-beauty'),
                    "description" => wp_kses_data( __("Skip posts before select next part.", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => "0",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "orderby",
                    "heading" => esc_html__("Post sorting", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select desired posts sorting method", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "std" => "date",
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('sorting')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "order",
                    "heading" => esc_html__("Post order", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select desired posts order", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "std" => "desc",
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('ordering')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "ids",
                    "heading" => esc_html__("Post IDs list", 'organic-beauty'),
                    "description" => wp_kses_data( __("Comma separated list of posts ID. If set - parameters above are ignored!", 'organic-beauty') ),
                    "group" => esc_html__('Query', 'organic-beauty'),
                    'dependency' => array(
                        'element' => 'custom',
                        'is_empty' => true
                    ),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "scheme",
                    "heading" => esc_html__("Color scheme", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select color scheme for this block", 'organic-beauty') ),
                    "group" => esc_html__('Colors and Images', 'organic-beauty'),
                    "class" => "",
                    "value" => array_flip((array)organic_beauty_get_sc_param('schemes')),
                    "type" => "dropdown"
                ),
                array(
                    "param_name" => "bg_color",
                    "heading" => esc_html__("Background color", 'organic-beauty'),
                    "description" => wp_kses_data( __("Any background color for this section", 'organic-beauty') ),
                    "group" => esc_html__('Colors and Images', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "colorpicker"
                ),
                array(
                    "param_name" => "bg_image",
                    "heading" => esc_html__("Background image URL", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select background image from library for this section", 'organic-beauty') ),
                    "group" => esc_html__('Colors and Images', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "attach_image"
                ),
                array(
                    "param_name" => "bg_overlay",
                    "heading" => esc_html__("Overlay", 'organic-beauty'),
                    "description" => wp_kses_data( __("Overlay color opacity (from 0.0 to 1.0)", 'organic-beauty') ),
                    "group" => esc_html__('Colors and Images', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "bg_texture",
                    "heading" => esc_html__("Texture", 'organic-beauty'),
                    "description" => wp_kses_data( __("Texture style from 1 to 11. Empty or 0 - without texture.", 'organic-beauty') ),
                    "group" => esc_html__('Colors and Images', 'organic-beauty'),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                organic_beauty_vc_width(),
                organic_beauty_vc_height(),
                organic_beauty_get_vc_param('margin_top'),
                organic_beauty_get_vc_param('margin_bottom'),
                organic_beauty_get_vc_param('margin_left'),
                organic_beauty_get_vc_param('margin_right'),
                organic_beauty_get_vc_param('id'),
                organic_beauty_get_vc_param('class'),
                organic_beauty_get_vc_param('animation'),
                organic_beauty_get_vc_param('css')
            ),
            'js_view' => 'VcTrxColumnsView'
        ) );


        vc_map( array(
            "base" => "trx_testimonials_item",
            "name" => esc_html__("Testimonial", 'organic-beauty'),
            "description" => wp_kses_data( __("Single testimonials item", 'organic-beauty') ),
            "show_settings_on_create" => true,
            "class" => "trx_sc_collection trx_sc_column_item trx_sc_testimonials_item",
            "content_element" => true,
            "is_container" => true,
            'icon' => 'icon_trx_testimonials_item',
            "as_child" => array('only' => 'trx_testimonials'),
            "as_parent" => array('except' => 'trx_testimonials'),
            "params" => array(
                array(
                    "param_name" => "author",
                    "heading" => esc_html__("Author", 'organic-beauty'),
                    "description" => wp_kses_data( __("Name of the testimonmials author", 'organic-beauty') ),
                    "admin_label" => true,
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "link",
                    "heading" => esc_html__("Link", 'organic-beauty'),
                    "description" => wp_kses_data( __("Link URL to the testimonmials author page", 'organic-beauty') ),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "email",
                    "heading" => esc_html__("E-mail", 'organic-beauty'),
                    "description" => wp_kses_data( __("E-mail of the testimonmials author", 'organic-beauty') ),
                    "class" => "",
                    "value" => "",
                    "type" => "textfield"
                ),
                array(
                    "param_name" => "photo",
                    "heading" => esc_html__("Photo", 'organic-beauty'),
                    "description" => wp_kses_data( __("Select or upload photo of testimonmials author or write URL of photo from other site", 'organic-beauty') ),
                    "class" => "",
                    "value" => "",
                    "type" => "attach_image"
                ),
                organic_beauty_get_vc_param('id'),
                organic_beauty_get_vc_param('class'),
                organic_beauty_get_vc_param('css')
            ),
            'js_view' => 'VcTrxColumnItemView'
        ) );

        class WPBakeryShortCode_Trx_Testimonials extends Organic_Beauty_Vc_ShortCodeColumns {}
        class WPBakeryShortCode_Trx_Testimonials_Item extends Organic_Beauty_Vc_ShortCodeCollection {}

    }
}

// Organic Beauty shortcodes builder settings
require_once trx_utils_get_file_dir('shortcodes/shortcodes_settings.php');

require_once trx_utils_get_file_dir('includes/theme.shortcodes.php');

// VC shortcodes settings
if ( class_exists('WPBakeryShortCode') ) {
    require_once trx_utils_get_file_dir('shortcodes/shortcodes_vc.php');
}

// Organic Beauty shortcodes implementation
// Using get_template_part(), because shortcodes can be replaced in the child theme
require_once trx_utils_get_file_dir('shortcodes/trx_basic/anchor.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/audio.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/blogger.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/br.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/call_to_action.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/chat.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/columns.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/content.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/form.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/googlemap.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/hide.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/image.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/infobox.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/intro.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/line.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/list.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/price_block.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/promo.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/quote.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/reviews.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/search.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/section.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/skills.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/slider.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/socials.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/table.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/title.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/twitter.php');
require_once trx_utils_get_file_dir('shortcodes/trx_basic/video.php');


require_once trx_utils_get_file_dir('shortcodes/trx_optional/button.php');
require_once trx_utils_get_file_dir('shortcodes/trx_optional/dropcaps.php');
require_once trx_utils_get_file_dir('shortcodes/trx_optional/gap.php');
require_once trx_utils_get_file_dir('shortcodes/trx_optional/highlight.php');
require_once trx_utils_get_file_dir('shortcodes/trx_optional/icon.php');
require_once trx_utils_get_file_dir('shortcodes/trx_optional/number.php');
require_once trx_utils_get_file_dir('shortcodes/trx_optional/popup.php');
require_once trx_utils_get_file_dir('shortcodes/trx_optional/price.php');
require_once trx_utils_get_file_dir('shortcodes/trx_optional/tabs.php');
require_once trx_utils_get_file_dir('shortcodes/trx_optional/tooltip.php');
?>