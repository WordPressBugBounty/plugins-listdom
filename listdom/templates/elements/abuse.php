<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Element_Abuse $this */
/** @var int $post_id */

$owner_id = get_post_field('post_author', $post_id);

// Current User
$current = wp_get_current_user();
$current_id = get_current_user_id();
?>
<div class="lsd-report-abuse-form-wrapper">
	<form class="lsd-report-abuse-form" id="lsd_report_abuse_form_<?php echo esc_attr($post_id); ?>" data-id="<?php echo esc_attr($post_id); ?>">
		
		<div class="lsd-report-abuse-form-name-email-phone-wrapper">
			<div class="lsd-report-abuse-form-row lsd-report-abuse-form-row-name">
				<input
					class="lsd-form-control-input"
					type="text"
					name="lsd_name"
					placeholder="<?php esc_attr_e('Your Name', 'listdom') ?>"
					title="<?php esc_attr_e('Your Name', 'listdom') ?>"
					value="<?php echo $current_id ? esc_attr(trim($current->first_name.' '.$current->last_name)) : ''; ?>"
					required
				>
				<i class="lsd-icon fa fa-user"></i>
			</div>
			<div class="lsd-report-abuse-form-row lsd-report-abuse-form-row-email">
				<input
					class="lsd-form-control-input"
					type="email"
					name="lsd_email"
					placeholder="<?php esc_attr_e('Your Email', 'listdom') ?>"
					title="<?php esc_attr_e('Your Email', 'listdom') ?>"
					value="<?php echo $current_id ? esc_attr($current->user_email) : ''; ?>"
					required
				>
				<i class="lsd-icon fa fa-envelope"></i>
			</div>
			<div class="lsd-report-abuse-form-row lsd-report-abuse-form-row-phone">
				<input
					class="lsd-form-control-input"
					type="tel"
					name="lsd_phone"
					placeholder="<?php esc_attr_e('Your Phone', 'listdom') ?>"
					title="<?php esc_attr_e('Your Phone', 'listdom') ?>"
					value="<?php echo $current_id ? esc_attr(get_user_meta($current_id, 'lsd_phone', true)) : ''; ?>"
					required
				>
				<i class="lsd-icon fas fa-phone-alt"></i>
			</div>
		</div>
		
		<div class="lsd-report-abuse-form-row">
			<textarea
				class="lsd-form-control-textarea"
				name="lsd_message"
				placeholder="<?php esc_attr_e("Your message ...", 'listdom') ?>"
				title="<?php esc_attr_e("Your Message", 'listdom') ?>"
				required
			></textarea>
		</div>
		
		<div class="lsd-report-abuse-form-row lsd-report-abuse-form-third-row">
			<?php echo LSD_Main::grecaptcha_field('transform-95'); ?>
			<button type="submit" class="lsd-form-submit lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?>"><?php esc_html_e('Send', 'listdom'); ?></button>

			<?php wp_nonce_field('lsd_abuse_'.$post_id); ?>
			<input type="hidden" name="lsd_post_id" value="<?php echo esc_attr($post_id); ?>">
			<input type="hidden" name="action" value="lsd_report_abuse">
		</div>
	</form>

    <div class="lsd-report-abuse-form-alert"></div>
</div>