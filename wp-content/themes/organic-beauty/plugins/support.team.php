<?php
/**
 * Organic Beauty Framework: Team support
 *
 * @package	organic_beauty
 * @since	organic_beauty 1.0
 */

// Theme init
if (!function_exists('organic_beauty_team_theme_setup')) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_team_theme_setup', 1 );
	function organic_beauty_team_theme_setup() {

		// Add item in the admin menu
		add_filter('trx_utils_filter_override_options',						'organic_beauty_team_add_override_options');

		// Save data from override options
		add_action('save_post',								'organic_beauty_team_save_data');
		
		// Detect current page type, taxonomy and title (for custom post_types use priority < 10 to fire it handles early, than for standard post types)
		add_filter('organic_beauty_filter_get_blog_type',			'organic_beauty_team_get_blog_type', 9, 2);
		add_filter('organic_beauty_filter_get_blog_title',		'organic_beauty_team_get_blog_title', 9, 2);
		add_filter('organic_beauty_filter_get_current_taxonomy',	'organic_beauty_team_get_current_taxonomy', 9, 2);
		add_filter('organic_beauty_filter_is_taxonomy',			'organic_beauty_team_is_taxonomy', 9, 2);
		add_filter('organic_beauty_filter_get_stream_page_title',	'organic_beauty_team_get_stream_page_title', 9, 2);
		add_filter('organic_beauty_filter_get_stream_page_link',	'organic_beauty_team_get_stream_page_link', 9, 2);
		add_filter('organic_beauty_filter_get_stream_page_id',	'organic_beauty_team_get_stream_page_id', 9, 2);
		add_filter('organic_beauty_filter_query_add_filters',		'organic_beauty_team_query_add_filters', 9, 2);
		add_filter('organic_beauty_filter_detect_inheritance_key','organic_beauty_team_detect_inheritance_key', 9, 1);

		// Extra column for team members lists
		if (organic_beauty_get_theme_option('show_overriden_posts')=='yes') {
			add_filter('manage_edit-team_columns',			'organic_beauty_post_add_options_column', 9);
			add_filter('manage_team_posts_custom_column',	'organic_beauty_post_fill_options_column', 9, 2);
		}

		// Options fields
		organic_beauty_storage_set('team_override_options', array(
			'id' => 'team-override-options',
			'title' => esc_html__('Team Member Details', 'organic-beauty'),
			'page' => 'team',
			'context' => 'normal',
			'priority' => 'high',
			'fields' => array(
				"team_member_position" => array(
					"title" => esc_html__('Position',  'organic-beauty'),
					"desc" => wp_kses_data( __("Position of the team member", 'organic-beauty') ),
					"class" => "team_member_position",
					"std" => "",
					"type" => "text"),
				"team_member_bday" => array(
					"title" => esc_html__("Birthday",  'organic-beauty'),
					"desc" => wp_kses_data( __("Birthday of the team member", 'organic-beauty') ),
					"class" => "team_member_email",
					"std" => "",
					"type" => "text"),
				"team_member_email" => array(
					"title" => esc_html__("E-mail",  'organic-beauty'),
					"desc" => wp_kses_data( __("E-mail of the team member - need to take Gravatar (if registered)", 'organic-beauty') ),
					"class" => "team_member_email",
					"std" => "",
					"type" => "text"),
				"team_member_link" => array(
					"title" => esc_html__('Link to profile',  'organic-beauty'),
					"desc" => wp_kses_data( __("URL of the team member profile page (if not this page)", 'organic-beauty') ),
					"class" => "team_member_link",
					"std" => "",
					"type" => "text"),
				"team_member_socials" => array(
					"title" => esc_html__("Social links",  'organic-beauty'),
					"desc" => wp_kses_data( __("Links to the social profiles of the team member", 'organic-beauty') ),
					"class" => "team_member_email",
					"std" => "",
					"type" => "social"),
				"team_member_brief_info" => array(
					"title" => esc_html__("Brief info",  'organic-beauty'),
					"desc" => wp_kses_data( __("Brief info about the team member", 'organic-beauty') ),
					"class" => "team_member_brief_info",
					"std" => "",
					"type" => "textarea"),
				)
			)
		);
		
		// Add supported data types
		organic_beauty_theme_support_pt('team');
		organic_beauty_theme_support_tx('team_group');
	}
}

if ( !function_exists( 'organic_beauty_team_settings_theme_setup2' ) ) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_team_settings_theme_setup2', 3 );
	function organic_beauty_team_settings_theme_setup2() {
		// Add post type 'team' and taxonomy 'team_group' into theme inheritance list
		organic_beauty_add_theme_inheritance( array('team' => array(
			'stream_template' => 'blog-team',
			'single_template' => 'single-team',
			'taxonomy' => array('team_group'),
			'taxonomy_tags' => array(),
			'post_type' => array('team'),
			'override' => 'custom'
			) )
		);
	}
}


// Add override options
if (!function_exists('organic_beauty_team_add_override_options')) {
	//Handler of add_filter('trx_utils_filter_override_options', 'organic_beauty_team_add_override_options');
	function organic_beauty_team_add_override_options($boxes = array()) {
        $boxes[] = array_merge(organic_beauty_storage_get('team_override_options'), array('callback' => 'organic_beauty_team_show_override_options'));
        return $boxes;
	}
}

// Callback function to show fields in override options
if (!function_exists('organic_beauty_team_show_override_options')) {
	function organic_beauty_team_show_override_options() {
		global $post;

		$data = get_post_meta($post->ID, organic_beauty_storage_get('options_prefix').'_team_data', true);
		$fields = organic_beauty_storage_get_array('team_override_options', 'fields');
		?>
		<input type="hidden" name="override_options_team_nonce" value="<?php echo esc_attr(wp_create_nonce(admin_url())); ?>" />
		<table class="team_area">
		<?php
		if (is_array($fields) && count($fields) > 0) {
			foreach ($fields as $id=>$field) { 
				$meta = isset($data[$id]) ? $data[$id] : '';
				?>
				<tr class="team_field <?php echo esc_attr($field['class']); ?>" valign="top">
					<td><label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($field['title']); ?></label></td>
					<td>
						<?php
						if ($id == 'team_member_socials') {
							$socials_type = organic_beauty_get_theme_setting('socials_type');
							$social_list = organic_beauty_get_theme_option('social_icons');
							if (is_array($social_list) && count($social_list) > 0) {
								foreach ($social_list as $soc) {
									if ($socials_type == 'icons') {
										$parts = explode('-', $soc['icon'], 2);
										$sn = isset($parts[1]) ? $parts[1] : $soc['icon'];
									} else {
										$sn = basename($soc['icon']);
										$sn = organic_beauty_substr($sn, 0, organic_beauty_strrpos($sn, '.'));
										if (($pos=organic_beauty_strrpos($sn, '_'))!==false)
											$sn = organic_beauty_substr($sn, 0, $pos);
									}   
									$link = isset($meta[$sn]) ? $meta[$sn] : '';
									?>
									<label for="<?php echo esc_attr(($id).'_'.($sn)); ?>"><?php echo esc_html(organic_beauty_strtoproper($sn)); ?></label><br>
									<input type="text" name="<?php echo esc_attr($id); ?>[<?php echo esc_attr($sn); ?>]" id="<?php echo esc_attr(($id).'_'.($sn)); ?>" value="<?php echo esc_attr($link); ?>" size="30" /><br>
									<?php
								}
							}
						} else if (!empty($field['type']) && $field['type']=='textarea') {
							?>
							<textarea name="<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id); ?>" rows="8" cols="100"><?php echo esc_html($meta); ?></textarea>
							<?php
						} else {
							?>
							<input type="text" name="<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($meta); ?>" size="30" />
							<?php
						}
						?>
						<br><small><?php echo esc_html($field['desc']); ?></small>
					</td>
				</tr>
				<?php
			}
		}
		?>
		</table>
		<?php
	}
}


// Save data from override options
if (!function_exists('organic_beauty_team_save_data')) {
	//Handler of add_action('save_post', 'organic_beauty_team_save_data');
	function organic_beauty_team_save_data($post_id) {
		// verify nonce
		if ( !wp_verify_nonce( organic_beauty_get_value_gp('override_options_team_nonce'), admin_url() ) )
			return $post_id;

		// check autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}

		// check permissions
		if ($_POST['post_type']!='team' || !current_user_can('edit_post', $post_id)) {
			return $post_id;
		}

		$data = array();

		$fields = organic_beauty_storage_get_array('team_override_options', 'fields');

		// Post type specific data handling
		if (is_array($fields) && count($fields) > 0) {
			foreach ($fields as $id=>$field) {
				if (isset($_POST[$id])) {
					if (is_array($_POST[$id]) && count($_POST[$id]) > 0) {
						foreach ($_POST[$id] as $sn=>$link) {
							$_POST[$id][$sn] = stripslashes($link);
						}
						$data[$id] = organic_beauty_get_value_gp($id);
					} else {
						$data[$id] = stripslashes($_POST[$id]);
					}
				}
			}
		}

		update_post_meta($post_id, organic_beauty_storage_get('options_prefix').'_team_data', $data);
	}
}



// Return true, if current page is team member page
if ( !function_exists( 'organic_beauty_is_team_page' ) ) {
	function organic_beauty_is_team_page() {
		$is = in_array(organic_beauty_storage_get('page_template'), array('blog-team', 'single-team'));
		if (!$is) {
			if (!organic_beauty_storage_empty('pre_query'))
				$is = organic_beauty_storage_call_obj_method('pre_query', 'get', 'post_type')=='team' 
						|| organic_beauty_storage_call_obj_method('pre_query', 'is_tax', 'team_group') 
						|| (organic_beauty_storage_call_obj_method('pre_query', 'is_page') 
								&& ($id=organic_beauty_get_template_page_id('blog-team')) > 0 
								&& $id==organic_beauty_storage_get_obj_property('pre_query', 'queried_object_id', 0)
							);
			else
				$is = get_query_var('post_type')=='team' || is_tax('team_group') || (is_page() && ($id=organic_beauty_get_template_page_id('blog-team')) > 0 && $id==get_the_ID());
		}
		return $is;
	}
}

// Filter to detect current page inheritance key
if ( !function_exists( 'organic_beauty_team_detect_inheritance_key' ) ) {
	//Handler of add_filter('organic_beauty_filter_detect_inheritance_key',	'organic_beauty_team_detect_inheritance_key', 9, 1);
	function organic_beauty_team_detect_inheritance_key($key) {
		if (!empty($key)) return $key;
		return organic_beauty_is_team_page() ? 'team' : '';
	}
}

// Filter to detect current page slug
if ( !function_exists( 'organic_beauty_team_get_blog_type' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_blog_type',	'organic_beauty_team_get_blog_type', 9, 2);
	function organic_beauty_team_get_blog_type($page, $query=null) {
		if (!empty($page)) return $page;
		if ($query && $query->is_tax('team_group') || is_tax('team_group'))
			$page = 'team_category';
		else if ($query && $query->get('post_type')=='team' || get_query_var('post_type')=='team')
			$page = $query && $query->is_single() || is_single() ? 'team_item' : 'team';
		return $page;
	}
}

// Filter to detect current page title
if ( !function_exists( 'organic_beauty_team_get_blog_title' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_blog_title',	'organic_beauty_team_get_blog_title', 9, 2);
	function organic_beauty_team_get_blog_title($title, $page) {
		if (!empty($title)) return $title;
		if ( organic_beauty_strpos($page, 'team')!==false ) {
			if ( $page == 'team_category' ) {
				$term = get_term_by( 'slug', get_query_var( 'team_group' ), 'team_group', OBJECT);
				$title = $term->name;
			} else if ( $page == 'team_item' ) {
				$title = organic_beauty_get_post_title();
			} else {
				$title = esc_html__('All team', 'organic-beauty');
			}
		}

		return $title;
	}
}

// Filter to detect stream page title
if ( !function_exists( 'organic_beauty_team_get_stream_page_title' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_stream_page_title',	'organic_beauty_team_get_stream_page_title', 9, 2);
	function organic_beauty_team_get_stream_page_title($title, $page) {
		if (!empty($title)) return $title;
		if (organic_beauty_strpos($page, 'team')!==false) {
			if (($page_id = organic_beauty_team_get_stream_page_id(0, $page=='team' ? 'blog-team' : $page)) > 0)
				$title = organic_beauty_get_post_title($page_id);
			else
				$title = esc_html__('All team', 'organic-beauty');				
		}
		return $title;
	}
}

// Filter to detect stream page ID
if ( !function_exists( 'organic_beauty_team_get_stream_page_id' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_stream_page_id',	'organic_beauty_team_get_stream_page_id', 9, 2);
	function organic_beauty_team_get_stream_page_id($id, $page) {
		if (!empty($id)) return $id;
		if (organic_beauty_strpos($page, 'team')!==false) $id = organic_beauty_get_template_page_id('blog-team');
		return $id;
	}
}

// Filter to detect stream page URL
if ( !function_exists( 'organic_beauty_team_get_stream_page_link' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_stream_page_link',	'organic_beauty_team_get_stream_page_link', 9, 2);
	function organic_beauty_team_get_stream_page_link($url, $page) {
		if (!empty($url)) return $url;
		if (organic_beauty_strpos($page, 'team')!==false) {
			$id = organic_beauty_get_template_page_id('blog-team');
			if ($id) $url = get_permalink($id);
		}
		return $url;
	}
}

// Filter to detect current taxonomy
if ( !function_exists( 'organic_beauty_team_get_current_taxonomy' ) ) {
	//Handler of add_filter('organic_beauty_filter_get_current_taxonomy',	'organic_beauty_team_get_current_taxonomy', 9, 2);
	function organic_beauty_team_get_current_taxonomy($tax, $page) {
		if (!empty($tax)) return $tax;
		if ( organic_beauty_strpos($page, 'team')!==false ) {
			$tax = 'team_group';
		}
		return $tax;
	}
}

// Return taxonomy name (slug) if current page is this taxonomy page
if ( !function_exists( 'organic_beauty_team_is_taxonomy' ) ) {
	//Handler of add_filter('organic_beauty_filter_is_taxonomy',	'organic_beauty_team_is_taxonomy', 9, 2);
	function organic_beauty_team_is_taxonomy($tax, $query=null) {
		if (!empty($tax))
			return $tax;
		else 
			return $query && $query->get('team_group')!='' || is_tax('team_group') ? 'team_group' : '';
	}
}

// Add custom post type and/or taxonomies arguments to the query
if ( !function_exists( 'organic_beauty_team_query_add_filters' ) ) {
	//Handler of add_filter('organic_beauty_filter_query_add_filters',	'organic_beauty_team_query_add_filters', 9, 2);
	function organic_beauty_team_query_add_filters($args, $filter) {
		if ($filter == 'team') {
			$args['post_type'] = 'team';
		}
		return $args;
	}
}
?>