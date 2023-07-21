<?php
/* Revolution Slider support functions
------------------------------------------------------------------------------- */

// Theme init
if (!function_exists('organic_beauty_revslider_theme_setup')) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_revslider_theme_setup', 1 );
	function organic_beauty_revslider_theme_setup() {
		if (organic_beauty_exists_revslider()) {
			add_filter( 'organic_beauty_filter_list_sliders',					'organic_beauty_revslider_list_sliders' );
			add_filter( 'organic_beauty_filter_theme_options_params',			'organic_beauty_revslider_theme_options_params' );
		}
		if (is_admin()) {
			add_filter( 'organic_beauty_filter_importer_required_plugins',	'organic_beauty_revslider_importer_required_plugins', 10, 2 );
			add_filter( 'organic_beauty_filter_required_plugins',				'organic_beauty_revslider_required_plugins' );
		}
	}
}

if ( !function_exists( 'organic_beauty_revslider_settings_theme_setup2' ) ) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_revslider_settings_theme_setup2', 3 );
	function organic_beauty_revslider_settings_theme_setup2() {
		if (organic_beauty_exists_revslider()) {

			// Add Revslider specific options in the Theme Options
			organic_beauty_storage_set_array_after('options', 'slider_engine', "slider_alias", array(
				"title" => esc_html__('Revolution Slider: Select slider',  'organic-beauty'),
				"desc" => wp_kses_data( __("Select slider to show (if engine=revo in the field above)", 'organic-beauty') ),
				"override" => "category,services_group,page,custom",
				"dependency" => array(
					'show_slider' => array('yes'),
					'slider_engine' => array('revo')
				),
				"std" => "",
				"options" => organic_beauty_get_options_param('list_revo_sliders'),
				"type" => "select"
				)
			);

		}
	}
}

// Check if RevSlider installed and activated
if ( !function_exists( 'organic_beauty_exists_revslider' ) ) {
	function organic_beauty_exists_revslider() {
		return function_exists('rev_slider_shortcode');
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'organic_beauty_revslider_required_plugins' ) ) {
	//Handler of add_filter('organic_beauty_filter_required_plugins',	'organic_beauty_revslider_required_plugins');
	function organic_beauty_revslider_required_plugins($list=array()) {
		if (in_array('revslider', (array)organic_beauty_storage_get('required_plugins'))) {
			$path = organic_beauty_get_file_dir('plugins/install/revslider.zip');
			if (file_exists($path)) {
				$list[] = array(
					'name' 		=> esc_html__('Revolution Slider', 'organic-beauty'),
					'slug' 		=> 'revslider',
                    'version'   => '6.3.4',
					'source'	=> $path,
					'required' 	=> false
					);
			}
		}
		return $list;
	}
}


// Lists
//------------------------------------------------------------------------

// Add RevSlider in the sliders list, prepended inherit (if need)
if ( !function_exists( 'organic_beauty_revslider_list_sliders' ) ) {
	//Handler of add_filter( 'organic_beauty_filter_list_sliders',					'organic_beauty_revslider_list_sliders' );
	function organic_beauty_revslider_list_sliders($list=array()) {
		$list = is_array($list) ? $list : array();
		$list["revo"] = esc_html__("Layer slider (Revolution)", 'organic-beauty');
		return $list;
	}
}

// Return Revo Sliders list, prepended inherit (if need)
if ( !function_exists( 'organic_beauty_get_list_revo_sliders' ) ) {
	function organic_beauty_get_list_revo_sliders($prepend_inherit=false) {
		if (($list = organic_beauty_storage_get('list_revo_sliders'))=='') {
			$list = array();
			if (organic_beauty_exists_revslider()) {
				global $wpdb;
                // Attention! The use of wpdb->prepare() is not required
                // because the query does not use external data substitution
				$rows = $wpdb->get_results( "SELECT alias, title FROM " . esc_sql($wpdb->prefix) . "revslider_sliders" );
				if (is_array($rows) && count($rows) > 0) {
					foreach ($rows as $row) {
						$list[$row->alias] = $row->title;
					}
				}
			}
			$list = apply_filters('organic_beauty_filter_list_revo_sliders', $list);
			if (organic_beauty_get_theme_setting('use_list_cache')) organic_beauty_storage_set('list_revo_sliders', $list);
		}
		return $prepend_inherit ? organic_beauty_array_merge(array('inherit' => esc_html__("Inherit", 'organic-beauty')), $list) : $list;
	}
}

// Add RevSlider in the Theme Options params
if ( !function_exists( 'organic_beauty_revslider_theme_options_params' ) ) {
	//Handler of add_filter( 'organic_beauty_filter_theme_options_params',			'organic_beauty_revslider_theme_options_params' );
	function organic_beauty_revslider_theme_options_params($list=array()) {
		$list["list_revo_sliders"] = array('$organic_beauty_get_list_revo_sliders' => '');
		return $list;
	}
}
?>