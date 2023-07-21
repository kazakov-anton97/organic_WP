<?php
/* WPBakery PageBuilder support functions
------------------------------------------------------------------------------- */

// Theme init
if (!function_exists('organic_beauty_vc_theme_setup')) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_vc_theme_setup', 1 );
	function organic_beauty_vc_theme_setup() {
		if (organic_beauty_exists_visual_composer()) {
			add_action('organic_beauty_action_add_styles',		 				'organic_beauty_vc_frontend_scripts' );
		}
		if (is_admin()) {
			add_filter( 'organic_beauty_filter_importer_required_plugins',		'organic_beauty_vc_importer_required_plugins', 10, 2 );
			add_filter( 'organic_beauty_filter_required_plugins',					'organic_beauty_vc_required_plugins' );
		}
	}
}

// Check if WPBakery PageBuilder installed and activated
if ( !function_exists( 'organic_beauty_exists_visual_composer' ) ) {
	function organic_beauty_exists_visual_composer() {
		return class_exists('Vc_Manager');
	}
}

// Check if WPBakery PageBuilder in frontend editor mode
if ( !function_exists( 'organic_beauty_vc_is_frontend' ) ) {
	function organic_beauty_vc_is_frontend() {
		return (isset($_GET['vc_editable']) && $_GET['vc_editable']=='true')
			|| (isset($_GET['vc_action']) && $_GET['vc_action']=='vc_inline');
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'organic_beauty_vc_required_plugins' ) ) {
	//Handler of add_filter('organic_beauty_filter_required_plugins',	'organic_beauty_vc_required_plugins');
	function organic_beauty_vc_required_plugins($list=array()) {
		if (in_array('visual_composer', (array)organic_beauty_storage_get('required_plugins'))) {
			$path = organic_beauty_get_file_dir('plugins/install/js_composer.zip');
			if (file_exists($path)) {
				$list[] = array(
					'name' 		=> esc_html__('WPBakery PageBuilder', 'organic-beauty'),
					'slug' 		=> 'js_composer',
                    'version'   => '6.5.0',
					'source'	=> $path,
					'required' 	=> false
				);
			}
			$path = organic_beauty_get_file_dir('plugins/install/vc-extensions-bundle.zip');
			if (file_exists($path)) {
				$list[] = array(
					'name'   => 'WPBakery Page Builder Extensions Bundle',
					'slug'   => 'vc_extensions',
                    'version'   => '3.6.0',
					'source' => $path,
					'required'  => false
				);
			}
		}
		return $list;
	}
}

// Enqueue VC custom styles
if ( !function_exists( 'organic_beauty_vc_frontend_scripts' ) ) {
	//Handler of add_action( 'organic_beauty_action_add_styles', 'organic_beauty_vc_frontend_scripts' );
	function organic_beauty_vc_frontend_scripts() {
		if (file_exists(organic_beauty_get_file_dir('css/plugin.visual-composer.css')))
			wp_enqueue_style( 'organic-beauty-plugin-visual-composer-style',  organic_beauty_get_file_url('css/plugin.visual-composer.css'), array(), null );
	}
}
?>