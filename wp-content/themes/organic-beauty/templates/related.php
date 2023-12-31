<?php

// Disable direct call
if ( ! defined( 'ABSPATH' ) ) { exit; }


/* Theme setup section
-------------------------------------------------------------------- */

if ( !function_exists( 'organic_beauty_template_related_theme_setup' ) ) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_template_related_theme_setup', 1 );
	function organic_beauty_template_related_theme_setup() {
		organic_beauty_add_template(array(
			'layout' => 'related',
			'mode'   => 'blog',
			'need_columns' => true,
			'need_terms' => true,
			'title'  => esc_html__('Related posts /no columns/', 'organic-beauty'),
			'thumb_title'  => esc_html__('Medium image (crop)', 'organic-beauty'),
			'w'		 => 370,
			'h'		 => 209
		));
		organic_beauty_add_template(array(
			'layout' => 'related_2',
			'template' => 'related',
			'mode'   => 'blog',
			'need_columns' => true,
			'need_terms' => true,
			'title'  => esc_html__('Related posts /2 columns/', 'organic-beauty'),
			'thumb_title'  => esc_html__('Medium image (crop)', 'organic-beauty'),
			'w'		 => 370,
			'h'		 => 209
		));
		organic_beauty_add_template(array(
			'layout' => 'related_3',
			'template' => 'related',
			'mode'   => 'blog',
			'need_columns' => true,
			'need_terms' => true,
			'title'  => esc_html__('Related posts /3 columns/', 'organic-beauty'),
			'thumb_title'  => esc_html__('Medium image (crop)', 'organic-beauty'),
			'w'		 => 370,
			'h'		 => 209
		));
		organic_beauty_add_template(array(
			'layout' => 'related_4',
			'template' => 'related',
			'mode'   => 'blog',
			'need_columns' => true,
			'need_terms' => true,
			'title'  => esc_html__('Related posts /4 columns/', 'organic-beauty'),
			'thumb_title'  => esc_html__('Medium square image (crop)', 'organic-beauty'),
			'w'		 => 370,
			'h'		 => 370
		));
	}
}

// Template output
if ( !function_exists( 'organic_beauty_template_related_output' ) ) {
	function organic_beauty_template_related_output($post_options, $post_data) {
		$show_title = true;	
		$parts = explode('_', $post_options['layout']);
		$style = $parts[0];
		$columns = max(1, min(12, empty($post_options['columns_count']) 
									? (empty($parts[1]) ? 1 : (int) $parts[1])
									: $post_options['columns_count']
									));
		$tag = organic_beauty_in_shortcode_blogger(true) ? 'div' : 'article';
		if ($columns > 1) {
			?><div class="<?php echo 'column-1_'.esc_attr($columns); ?> column_padding_bottom"><?php
		}
		?>
		<<?php organic_beauty_show_layout($tag); ?> class="post_item post_item_<?php echo esc_attr($style); ?> post_item_<?php echo esc_attr($post_options['number']); ?>">

			<div class="post_content">
				<?php if ($post_data['post_video'] || $post_data['post_thumb'] || $post_data['post_gallery']) { ?>
				<div class="post_featured">
					<?php
					organic_beauty_template_set_args('post-featured', array(
						'post_options' => $post_options,
						'post_data' => $post_data
					));
					get_template_part(organic_beauty_get_file_slug('templates/_parts/post-featured.php'));
					?>
				</div>
				<?php } ?>

				<?php if ($show_title) { ?>
					<div class="post_content_wrap">
						<?php


						if (!in_array($post_data['post_type'], array('players', 'team'))) {
							$post_date = apply_filters('organic_beauty_filter_post_date', $post_data['post_date_sql'], $post_data['post_id'], $post_data['post_type']);
							$post_date_diff = organic_beauty_get_date_or_difference($post_date);
							if (!organic_beauty_param_is_inherit($post_date)) {
								?>
								<span class="post_info_posted"><?php
									echo (in_array($post_data['post_type'], array('post', 'page', 'product'))
										? ''
										: ($post_date <= date('Y-m-d H:i:s')
											? esc_html__('Started', 'organic-beauty')
											: esc_html__('Will start', 'organic-beauty')));
									?> <a href="<?php echo esc_url($post_data['post_link']); ?>" class="post_info_date"<?php echo !empty($info_parts['snippets']) ? ' itemprop="datePublished" content="'.esc_attr($post_date).'"' : ''; ?>><?php echo esc_html($post_date_diff); ?></a></span>
							<?php
							}
						}


						if (!isset($post_options['links']) || $post_options['links']) { 
							?><h5 class="post_title"><a href="<?php echo esc_url($post_data['post_link']); ?>"><?php organic_beauty_show_layout($post_data['post_title']); ?></a></h5><?php
						} else {
							?><h5 class="post_title"><?php organic_beauty_show_layout($post_data['post_title']); ?></h5><?php
						}
						if (false && !empty($post_data['post_terms'][$post_data['post_taxonomy_tags']]->terms_links)) {
							?><div class="post_info post_info_tags"><?php echo join(', ', $post_data['post_terms'][$post_data['post_taxonomy_tags']]->terms_links); ?></div><?php
						}
						?>
					</div>
				<?php } ?>
			</div>	<!-- /.post_content -->
		</<?php organic_beauty_show_layout($tag); ?>>	<!-- /.post_item -->
		<?php
		if ($columns > 1) {
			?></div><?php
		}
	}
}
?>