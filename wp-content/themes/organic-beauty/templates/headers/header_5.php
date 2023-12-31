<?php

// Disable direct call
if ( ! defined( 'ABSPATH' ) ) { exit; }


/* Theme setup section
-------------------------------------------------------------------- */

if ( !function_exists( 'organic_beauty_template_header_5_theme_setup' ) ) {
	add_action( 'organic_beauty_action_before_init_theme', 'organic_beauty_template_header_5_theme_setup', 1 );
	function organic_beauty_template_header_5_theme_setup() {
		organic_beauty_add_template(array(
			'layout' => 'header_5',
			'mode'   => 'header',
			'title'  => esc_html__('Header 5', 'organic-beauty'),
			'icon'   => organic_beauty_get_file_url('templates/headers/images/5.jpg')
			));
	}
}

// Template output
if ( !function_exists( 'organic_beauty_template_header_5_output' ) ) {
	function organic_beauty_template_header_5_output($post_options, $post_data) {

		// WP custom header
		$header_css = '';
		if ($post_options['position'] != 'over') {
			$header_image = get_header_image();
			$header_css = $header_image!='' 
				? ' style="background-image: url('.esc_url($header_image).')"' 
				: '';
		}
		?>

		<div class="top_panel_fixed_wrap"></div>

		<header class="top_panel_wrap top_panel_style_5 scheme_<?php echo esc_attr($post_options['scheme']); ?>">
			<div class="top_panel_wrap_inner top_panel_inner_style_5 top_panel_position_<?php echo esc_attr(organic_beauty_get_custom_option('top_panel_position')); ?>">
			
			<?php if (organic_beauty_get_custom_option('show_top_panel_top')=='yes') { ?>
				<div class="top_panel_top">
					<div class="content_wrap clearfix">
						<?php
						organic_beauty_template_set_args('top-panel-top', array(
							'top_panel_top_components' => array('login', 'currency', 'bookmarks', 'socials')
						));
						get_template_part(organic_beauty_get_file_slug('templates/headers/_parts/top-panel-top.php'));
						?>
					</div>
				</div>
			<?php } ?>

				<div class="top_panel_middle" <?php organic_beauty_show_layout($header_css); ?>>
					<div class="contact_logo">
						<?php organic_beauty_show_logo(true, true); ?>
					</div>
					<div class="top_panel_bottom">
						<div class="content_wrap clearfix">
								<nav class="menu_main_nav_area menu_hover_fade">
									<?php
									$menu_main = organic_beauty_get_nav_menu('menu_main');
									if (empty($menu_main)) $menu_main = organic_beauty_get_nav_menu();
									organic_beauty_show_layout($menu_main);
									?>
								</nav>
								<?php
								if (organic_beauty_get_custom_option('show_search')=='yes' && function_exists('organic_beauty_sc_search'))
									organic_beauty_show_layout(organic_beauty_sc_search(array('state'=>"closed", "style"=>organic_beauty_get_theme_option('search_style'))));
								?>
						</div>
					</div>
				</div>
			</div>
		</header>

		<?php
		organic_beauty_storage_set('header_mobile', array(
				'login' => true,
				'socials' => true,
				'bookmarks' => false,
				'contact_address' => false,
				'contact_phone_email' => false,
				'woo_cart' => true,
				'search' => true
			)
		);
	}
}
?>