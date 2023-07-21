<?php
/* Instagram Feed support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('organic_beauty_elegro_payment_theme_setup')) {
    add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_elegro_payment_theme_setup', 1 );
    function organic_beauty_elegro_payment_theme_setup() {
        if (is_admin()) {
            add_filter( 'organic_beauty_filter_required_plugins',		'organic_beauty_elegro_payment_required_plugins' );
        }
    }
}

// Filter to add in the required plugins list
if ( !function_exists( 'organic_beauty_elegro_payment_required_plugins' ) ) {
    function organic_beauty_elegro_payment_required_plugins($list=array()) {
        if (in_array('elegro-payment', (array)organic_beauty_storage_get('required_plugins'))) {
            $list[] = array(
                'name' 		=> esc_html__('Elegro Payment', 'organic-beauty'),
                'slug' 		=> 'elegro-payment',
                'required' 	=> false
            );
        }
        return $list;
    }
}

// Check if Instagram Feed installed and activated
if ( !function_exists( 'organic_beauty_exists_elegro_payment' ) ) {
    function organic_beauty_exists_elegro_payment() {
        return function_exists('init_Elegro_Payment');
    }
}
?>