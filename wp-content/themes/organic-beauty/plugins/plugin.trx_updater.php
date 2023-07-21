<?php
/* Instagram Feed support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('organic_beauty_trx_updater_theme_setup')) {
    add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_trx_updater_theme_setup', 1 );
    function organic_beauty_trx_updater_theme_setup() {
        if (is_admin()) {
            add_filter( 'organic_beauty_filter_required_plugins',		'organic_beauty_trx_updater_required_plugins' );
        }
    }
}

// Filter to add in the required plugins list
if ( !function_exists( 'organic_beauty_trx_updater_required_plugins' ) ) {
    function organic_beauty_trx_updater_required_plugins($list=array()) {
        if (in_array('trx_updater', (array)organic_beauty_storage_get('required_plugins'))) {
            $list[] = array(
                'name' 		=> esc_html__('Themerex Updater', 'organic-beauty'),
                'slug' 		=> 'trx_updater',
                'version'   => '1.5.3',
                'source'	=> organic_beauty_get_file_dir('plugins/install/trx_updater.zip'),
                'required' 	=> false
            );
        }
        return $list;
    }
}

// Check if Instagram Feed installed and activated
if ( !function_exists( 'organic_beauty_exists_trx_updater' ) ) {
    function organic_beauty_exists_trx_updater() {
        return defined('TRX_UPDATER_VERSION');
    }
}
?>