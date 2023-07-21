<?php
/* Contact Form 7 support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('organic_beauty_cf7_theme_setup')) {
    add_action( 'after_setup_theme', 'organic_beauty_cf7_theme_setup', 9 );
    function organic_beauty_cf7_theme_setup() {
        if (is_admin()) {
            add_filter( 'organic_beauty_filter_required_plugins',			'organic_beauty_cf7_required_plugins' );
        }
    }
}


// Check if cf7 installed and activated
if ( !function_exists( 'organic_beauty_exists_cf7' ) ) {
    function organic_beauty_exists_cf7() {
        return class_exists('WPCF7');
    }
}


// Filter to add in the required plugins list
if ( !function_exists( 'organic_beauty_cf7_required_plugins' ) ) {
    function organic_beauty_cf7_required_plugins($list=array()) {

        $list[] = array(
            'name' 		=> esc_html__('Contact Form 7', 'organic-beauty'),
            'slug' 		=> 'contact-form-7',
            'required' 	=> false
        );

        return $list;
    }
}
?>