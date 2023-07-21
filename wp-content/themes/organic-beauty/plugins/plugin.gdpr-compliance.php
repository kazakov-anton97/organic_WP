<?php
/* The GDPR Framework support functions
------------------------------------------------------------------------------- */

// Theme init
if (!function_exists('organic_beauty_gdpr_compliance_theme_setup')) {
    add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_gdpr_compliance_theme_setup', 1 );
    function organic_beauty_gdpr_compliance_theme_setup() {
        if (is_admin()) {
            add_filter( 'organic_beauty_filter_required_plugins', 'organic_beauty_gdpr_compliance_required_plugins' );
        }
    }
}

// Check if Instagram Widget installed and activated
if ( !function_exists( 'organic_beauty_exists_gdpr_compliance' ) ) {
    function organic_beauty_exists_gdpr_compliance() {
        return defined( 'WP_GDPR_C_SLUG' );
    }
}

// Filter to add in the required plugins list
if ( !function_exists( 'organic_beauty_gdpr_compliance_required_plugins' ) ) {
    //Handler of add_filter('organic_beauty_filter_required_plugins',    'organic_beauty_gdpr_compliance_required_plugins');
    function organic_beauty_gdpr_compliance_required_plugins($list=array()) {
        if (in_array('gdpr-compliance', (array)organic_beauty_storage_get('required_plugins')))
            $list[] = array(
                'name'         => esc_html__('WP GDPR Compliance', 'organic-beauty'),
                'slug'         => 'wp-gdpr-compliance',
                'required'     => false
            );
        return $list;
    }
}