<?php
/* Essential Grid support functions
------------------------------------------------------------------------------- */

// Theme init
if (!function_exists('organic_beauty_essgrids_theme_setup')) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_essgrids_theme_setup', 1 );
	function organic_beauty_essgrids_theme_setup() {
		if (is_admin()) {
			add_filter( 'organic_beauty_filter_importer_required_plugins',	'organic_beauty_essgrids_importer_required_plugins', 10, 2 );
			add_filter( 'organic_beauty_filter_required_plugins',				'organic_beauty_essgrids_required_plugins' );
		}
	}
}


// Check if Ess. Grid installed and activated
if ( !function_exists( 'organic_beauty_exists_essgrids' ) ) {
	function organic_beauty_exists_essgrids() {
		return defined('EG_PLUGIN_PATH');
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'organic_beauty_essgrids_required_plugins' ) ) {
	//Handler of add_filter('organic_beauty_filter_required_plugins',	'organic_beauty_essgrids_required_plugins');
	function organic_beauty_essgrids_required_plugins($list=array()) {
		if (in_array('essgrids', (array)organic_beauty_storage_get('required_plugins'))) {
			$path = organic_beauty_get_file_dir('plugins/install/essential-grid.zip');
			if (file_exists($path)) {
				$list[] = array(
					'name' 		=> esc_html__('Essential Grid', 'organic-beauty'),
					'slug' 		=> 'essential-grid',
					'version'   => '3.0.10',
					'source'	=> $path,
					'required' 	=> false
					);
			}
		}
		return $list;
	}
}
?>