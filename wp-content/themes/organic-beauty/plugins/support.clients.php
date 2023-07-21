<?php
/**
 * Organic Beauty Framework: Clients support
 *
 * @package	organic_beauty
 * @since	organic_beauty 1.0
 */

// Theme init
if (!function_exists('organic_beauty_clients_theme_setup')) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_clients_theme_setup', 1 );
	function organic_beauty_clients_theme_setup() {

		// Detect current page type, taxonomy and title (for custom post_types use priority < 10 to fire it handles early, than for standard post types)
		add_filter('organic_beauty_filter_get_blog_type',			'organic_beauty_clients_get_blog_type', 9, 2);
		add_filter('organic_beauty_filter_get_blog_title',		'organic_beauty_clients_get_blog_title', 9, 2);
		add_filter('organic_beauty_filter_get_current_taxonomy',	'organic_beauty_clients_get_current_taxonomy', 9, 2);
		add_filter('organic_beauty_filter_is_taxonomy',			'organic_beauty_clients_is_taxonomy', 9, 2);
		add_filter('organic_beauty_filter_get_stream_page_title',	'organic_beauty_clients_get_stream_page_title', 9, 2);
		add_filter('organic_beauty_filter_get_stream_page_link',	'organic_beauty_clients_get_stream_page_link', 9, 2);
		add_filter('organic_beauty_filter_get_stream_page_id',	'organic_beauty_clients_get_stream_page_id', 9, 2);
		add_filter('organic_beauty_filter_query_add_filters',		'organic_beauty_clients_query_add_filters', 9, 2);
		add_filter('organic_beauty_filter_detect_inheritance_key','organic_beauty_clients_detect_inheritance_key', 9, 1);

		// Extra column for clients lists
		if (organic_beauty_get_theme_option('show_overriden_posts')=='yes') {
			add_filter('manage_edit-clients_columns',			'organic_beauty_post_add_options_column', 9);
			add_filter('manage_clients_posts_custom_column',	'organic_beauty_post_fill_options_column', 9, 2);
		}
		
		// Add supported data types
		organic_beauty_theme_support_pt('clients');
		organic_beauty_theme_support_tx('clients_group');
	}
}

if ( !function_exists( 'organic_beauty_clients_settings_theme_setup2' ) ) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_clients_settings_theme_setup2', 3 );
	function organic_beauty_clients_settings_theme_setup2() {
		// Add post type 'clients' and taxonomy 'clients_group' into theme inheritance list
		organic_beauty_add_theme_inheritance( array('clients' => array(
			'stream_template' => 'blog-clients',
			'single_template' => 'single-client',
			'taxonomy' => array('clients_group'),
			'taxonomy_tags' => array(),
			'post_type' => array('clients'),
			'override' => 'custom'
			) )
		);
	}
}


if (!function_exists('organic_beauty_clients_after_theme_setup')) {
	add_action( 'organic_beauty_action_after_init_theme', 'organic_beauty_clients_after_theme_setup' );
	function organic_beauty_clients_after_theme_setup() {
		// Update fields in the override options
		if (organic_beauty_storage_get_array('post_override_options', 'page')=='clients') {
			// Options fields
			organic_beauty_storage_set_array('post_override_options', 'title', esc_html__('Client Options', 'organic-beauty'));
			organic_beauty_storage_set_array('post_override_options', 'fields', array(
				"mb_partition_clients" => array(
					"title" => esc_html__('Clients', 'organic-beauty'),
					"override" => "page,post,custom",
					"divider" => false,
					"icon" => "iconadmin-users",
					"type" => "partition"),
				"mb_info_clients_1" => array(
					"title" => esc_html__('Client details', 'organic-beauty'),
					"override" => "page,post,custom",
					"divider" => false,
					"desc" => wp_kses_data( __('In this section you can put details for this client', 'organic-beauty') ),
					"class" => "client_meta",
					"type" => "info"),
				"client_name" => array(
					"title" => esc_html__('Contact name',  'organic-beauty'),
					"desc" => wp_kses_data( __("Name of the contacts manager", 'organic-beauty') ),
					"override" => "page,post,custom",
					"class" => "client_name",
					"std" => '',
					"type" => "text"),
				"client_position" => array(
					"title" => esc_html__('Position',  'organic-beauty'),
					"desc" => wp_kses_data( __("Position of the contacts manager", 'organic-beauty') ),
					"override" => "page,post,custom",
					"class" => "client_position",
					"std" => '',
					"type" => "text"),
				"client_show_link" => array(
					"title" => esc_html__('Show link',  'organic-beauty'),
					"desc" => wp_kses_data( __("Show link to client page", 'organic-beauty') ),
					"override" => "page,post,custom",
					"class" => "client_show_link",
					"std" => "no",
					"options" => organic_beauty_get_list_yesno(),
					"type" => "switch"),
				"client_link" => array(
					"title" => esc_html__('Link',  'organic-beauty'),
					"desc" => wp_kses_data( __("URL of the client's site. If empty - use link to this page", 'organic-beauty') ),
					"override" => "page,post,custom",
					"class" => "client_link",
					"std" => '',
					"type" => "text")
				)
			);
		}
	}
}


// Return true, if current page is clients page
if ( !function_exists( 'organic_beauty_is_clients_page' ) ) {
	function organic_beauty_is_clients_page() {
		$is = in_array(organic_beauty_storage_get('page_template'), array('blog-clients', 'single-client'));
		if (!$is) {
			if (!organic_beauty_storage_empty('pre_query'))
				$is = organic_beauty_storage_call_obj_method('pre_query', 'get', 'post_type')=='clients'
						|| organic_beauty_storage_call_obj_method('pre_query', 'is_tax', 'clients_group') 
						|| (organic_beauty_storage_call_obj_method('pre_query', 'is_page') 
							&& ($id=organic_beauty_get_template_page_id('blog-clients')) > 0 
							&& $id==organic_beauty_storage_get_obj_property('pre_query', 'queried_object_id', 0)
							);
			else
				$is = get_query_var('post_type')=='clients' 
						|| is_tax('clients_group') 
						|| (is_page() && ($id=organic_beauty_get_template_page_id('blog-clients')) > 0 && $id==get_the_ID());
		}
		return $is;
	}
}

// Filter to detect current page inheritance key
if ( !function_exists( 'organic_beauty_clients_detect_inheritance_key' ) ) {
	//Handler of add_filter('organic_beauty_filter_detect_inheritance_key',	'organic_beauty_clients_detect_inheritance_key', 9, 1);
	function organic_beauty_clients_detect_inheritance_key($key) {
		if (!empty($key)) return $key;
		return organic_beauty_is_clients_page() ? 'clients' : '';
	}
}

// Filter to detect current page slug
if ( !function_exists( 'organic_beauty_clients_get_blog_type' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_blog_type',	'organic_beauty_clients_get_blog_type', 9, 2);
	function organic_beauty_clients_get_blog_type($page, $query=null) {
		if (!empty($page)) return $page;
		if ($query && $query->is_tax('clients_group') || is_tax('clients_group'))
			$page = 'clients_category';
		else if ($query && $query->get('post_type')=='clients' || get_query_var('post_type')=='clients')
			$page = $query && $query->is_single() || is_single() ? 'clients_item' : 'clients';
		return $page;
	}
}

// Filter to detect current page title
if ( !function_exists( 'organic_beauty_clients_get_blog_title' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_blog_title',	'organic_beauty_clients_get_blog_title', 9, 2);
	function organic_beauty_clients_get_blog_title($title, $page) {
		if (!empty($title)) return $title;
		if ( organic_beauty_strpos($page, 'clients')!==false ) {
			if ( $page == 'clients_category' ) {
				$term = get_term_by( 'slug', get_query_var( 'clients_group' ), 'clients_group', OBJECT);
				$title = $term->name;
			} else if ( $page == 'clients_item' ) {
				$title = organic_beauty_get_post_title();
			} else {
				$title = esc_html__('All clients', 'organic-beauty');
			}
		}
		return $title;
	}
}

// Filter to detect stream page title
if ( !function_exists( 'organic_beauty_clients_get_stream_page_title' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_stream_page_title',	'organic_beauty_clients_get_stream_page_title', 9, 2);
	function organic_beauty_clients_get_stream_page_title($title, $page) {
		if (!empty($title)) return $title;
		if (organic_beauty_strpos($page, 'clients')!==false) {
			if (($page_id = organic_beauty_clients_get_stream_page_id(0, $page=='clients' ? 'blog-clients' : $page)) > 0)
				$title = organic_beauty_get_post_title($page_id);
			else
				$title = esc_html__('All clients', 'organic-beauty');				
		}
		return $title;
	}
}

// Filter to detect stream page ID
if ( !function_exists( 'organic_beauty_clients_get_stream_page_id' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_stream_page_id',	'organic_beauty_clients_get_stream_page_id', 9, 2);
	function organic_beauty_clients_get_stream_page_id($id, $page) {
		if (!empty($id)) return $id;
		if (organic_beauty_strpos($page, 'clients')!==false) $id = organic_beauty_get_template_page_id('blog-clients');
		return $id;
	}
}

// Filter to detect stream page URL
if ( !function_exists( 'organic_beauty_clients_get_stream_page_link' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_stream_page_link',	'organic_beauty_clients_get_stream_page_link', 9, 2);
	function organic_beauty_clients_get_stream_page_link($url, $page) {
		if (!empty($url)) return $url;
		if (organic_beauty_strpos($page, 'clients')!==false) {
			$id = organic_beauty_get_template_page_id('blog-clients');
			if ($id) $url = get_permalink($id);
		}
		return $url;
	}
}

// Filter to detect current taxonomy
if ( !function_exists( 'organic_beauty_clients_get_current_taxonomy' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_current_taxonomy',	'organic_beauty_clients_get_current_taxonomy', 9, 2);
	function organic_beauty_clients_get_current_taxonomy($tax, $page) {
		if (!empty($tax)) return $tax;
		if ( organic_beauty_strpos($page, 'clients')!==false ) {
			$tax = 'clients_group';
		}
		return $tax;
	}
}

// Return taxonomy name (slug) if current page is this taxonomy page
if ( !function_exists( 'organic_beauty_clients_is_taxonomy' ) ) {
	//Handler of add_filter('organic_beauty_filter_is_taxonomy',	'organic_beauty_clients_is_taxonomy', 9, 2);
	function organic_beauty_clients_is_taxonomy($tax, $query=null) {
		if (!empty($tax))
			return $tax;
		else 
			return $query && $query->get('clients_group')!='' || is_tax('clients_group') ? 'clients_group' : '';
	}
}

// Add custom post type and/or taxonomies arguments to the query
if ( !function_exists( 'organic_beauty_clients_query_add_filters' ) ) {
	//Handler of add_filter('organic_beauty_filter_query_add_filters',	'organic_beauty_clients_query_add_filters', 9, 2);
	function organic_beauty_clients_query_add_filters($args, $filter) {
		if ($filter == 'clients') {
			$args['post_type'] = 'clients';
		}
		return $args;
	}
}
?>