<?php 
if (is_singular()) {
	if (organic_beauty_get_theme_option('use_ajax_views_counter')=='yes') {
		organic_beauty_storage_set_array('js_vars', 'ajax_views_counter', array(
			'post_id' => get_the_ID(),
			'post_views' => apply_filters('trx_utils_filter_get_post_views', 0, get_the_ID())
		));
	} else
        do_action('trx_utils_filter_set_post_views', get_the_ID());
}
?>