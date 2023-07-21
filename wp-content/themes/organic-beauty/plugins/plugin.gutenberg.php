<?php
/* Gutenberg support functions
------------------------------------------------------------------------------- */

// Theme init
if (!function_exists('organic_beauty_gutenberg_theme_setup')) {
    add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_gutenberg_theme_setup', 1 );
    function organic_beauty_gutenberg_theme_setup() {
        if (is_admin()) {
            add_filter( 'organic_beauty_filter_required_plugins', 'organic_beauty_gutenberg_required_plugins' );
        }
    }
}

// Check if Instagram Widget installed and activated
if ( !function_exists( 'organic_beauty_exists_gutenberg' ) ) {
    function organic_beauty_exists_gutenberg() {
        return function_exists( 'the_gutenberg_project' ) && function_exists( 'register_block_type' );
    }
}

// Filter to add in the required plugins list
if ( !function_exists( 'organic_beauty_gutenberg_required_plugins' ) ) {
    //Handler of add_filter('organic_beauty_filter_required_plugins',    'organic_beauty_gutenberg_required_plugins');
    function organic_beauty_gutenberg_required_plugins($list=array()) {
        if (in_array('gutenberg', (array)organic_beauty_storage_get('required_plugins')))
            $list[] = array(
                'name'         => esc_html__('Gutenberg', 'organic-beauty'),
                'slug'         => 'gutenberg',
                'required'     => false
            );
        return $list;
    }
}