<?php
/* Mail Chimp support functions
------------------------------------------------------------------------------- */

// Theme init
if (!function_exists('organic_beauty_mailchimp_theme_setup')) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_mailchimp_theme_setup', 1 );
	function organic_beauty_mailchimp_theme_setup() {
		if (is_admin()) {
			add_filter( 'organic_beauty_filter_importer_required_plugins',		'organic_beauty_mailchimp_importer_required_plugins', 10, 2 );
			add_filter( 'organic_beauty_filter_required_plugins',					'organic_beauty_mailchimp_required_plugins' );
		}
	}
}

// Check if Instagram Feed installed and activated
if ( !function_exists( 'organic_beauty_exists_mailchimp' ) ) {
	function organic_beauty_exists_mailchimp() {
		return function_exists('mc4wp_load_plugin');
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'organic_beauty_mailchimp_required_plugins' ) ) {
	//Handler of add_filter('organic_beauty_filter_required_plugins',	'organic_beauty_mailchimp_required_plugins');
	function organic_beauty_mailchimp_required_plugins($list=array()) {
		if (in_array('mailchimp', (array)organic_beauty_storage_get('required_plugins')))
			$list[] = array(
				'name' 		=> esc_html__('MailChimp for WP', 'organic-beauty'),
				'slug' 		=> 'mailchimp-for-wp',
				'required' 	=> false
			);
		return $list;
	}
}
?>