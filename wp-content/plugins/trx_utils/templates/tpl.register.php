<?php
/**
 * The template to display register link and popup
 *
 * @package WordPress
 * @subpackage ThemeREX Utilities
 * @since v3.0
 */

// Display link
$args = get_query_var('trx_utils_args_register');
?><a href="#popup_registration" class="popup_link popup_register_link icon-pencil" title="<?php echo esc_attr($args['link_title']); ?>"><?php echo esc_html($args['link_text']); ?></a><?php

// Prepare popup
?>
<div id="popup_registration" class="popup_wrap popup_registration bg_tint_light">
	<a href="#" class="popup_close"></a>
	<div class="form_wrap">
		<form name="registration_form" method="post" class="popup_form registration_form">
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr(esc_url(home_url('/'))); ?>"/>
			<div class="form_left">
				<div class="popup_form_field login_field iconed_field icon-user"><input type="text" id="registration_username" name="registration_username"  value="" placeholder="<?php esc_attr_e('User name (login)', 'trx_utils'); ?>"></div>
				<div class="popup_form_field email_field iconed_field icon-mail"><input type="text" id="registration_email" name="registration_email" value="" placeholder="<?php esc_attr_e('E-mail', 'trx_utils'); ?>"></div>
                <div class="popup_form_field agree_field">
                    <?php
                    $trx_utils_privacy = trx_utils_get_privacy_text();
                    if (!empty($trx_utils_privacy)) {
                        ?><div>
                        <input type="checkbox" value="1" id="i_agree_privacy_policy_registration" name="i_agree_privacy_policy"><label for="i_agree_privacy_policy_registration"> <?php echo wp_kses_post($trx_utils_privacy); ?></label>
                        </div><?php
                    }
                    ?>
                </div>
				<div class="popup_form_field submit_field"><input type="submit" class="submit_button" value="<?php esc_attr_e('Sign Up', 'trx_utils'); ?>"<?php
                    if ( !empty($trx_utils_privacy) ) {
                        ?> disabled="disabled"<?php
                    }
                    ?>></div>
			</div>
			<div class="form_right">
				<div class="popup_form_field password_field iconed_field icon-lock"><input type="password" id="registration_pwd"  name="registration_pwd"  value="" placeholder="<?php esc_attr_e('Password', 'trx_utils'); ?>"></div>
				<div class="popup_form_field password_field iconed_field icon-lock"><input type="password" id="registration_pwd2" name="registration_pwd2" value="" placeholder="<?php esc_attr_e('Confirm Password', 'trx_utils'); ?>"></div>
				<div class="popup_form_field description_field"><?php esc_html_e('Minimum 4 characters', 'trx_utils'); ?></div>
			</div>
		</form>
		<div class="result message_block"></div>
	</div>	<!-- /.registration_wrap -->
</div>		<!-- /.user-popUp -->
