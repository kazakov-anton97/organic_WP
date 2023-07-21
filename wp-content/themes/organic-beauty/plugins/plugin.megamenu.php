<?php
/* Mega Main Menu support functions
------------------------------------------------------------------------------- */

// Theme init
if (!function_exists('organic_beauty_megamenu_theme_setup')) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_megamenu_theme_setup', 1 );
	function organic_beauty_megamenu_theme_setup() {
		if (is_admin()) {
			add_filter( 'organic_beauty_filter_importer_required_plugins',		'organic_beauty_megamenu_importer_required_plugins', 10, 2 );
			add_filter( 'organic_beauty_filter_required_plugins',					'organic_beauty_megamenu_required_plugins' );
		}
	}
}

// Check if MegaMenu installed and activated
if ( !function_exists( 'organic_beauty_exists_megamenu' ) ) {
	function organic_beauty_exists_megamenu() {
		return class_exists('mega_main_init');
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'organic_beauty_megamenu_required_plugins' ) ) {
	//Handler of add_filter('organic_beauty_filter_required_plugins',	'organic_beauty_megamenu_required_plugins');
	function organic_beauty_megamenu_required_plugins($list=array()) {
		if (in_array('mega_main_menu', (array)organic_beauty_storage_get('required_plugins'))) {
			$path = organic_beauty_get_file_dir('plugins/install/mega_main_menu.zip');
			if (file_exists($path)) {
				$list[] = array(
					'name' 		=> esc_html__('Mega Main Menu', 'organic-beauty'),
					'slug' 		=> 'mega_main_menu',
					'source'	=> $path,
					'required' 	=> false
				);
			}
		}
		return $list;
	}
}
?>