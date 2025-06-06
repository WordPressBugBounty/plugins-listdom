<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Element_Owner $this */
/** @var int $post_id */

$owner_id = get_post_field('post_author', $post_id);
$display_avatar = !isset($this->args['display_avatar']) || $this->args['display_avatar'];
$display_name = !isset($this->args['display_name']) || $this->args['display_name'];
$display_job_title = !isset($this->args['display_job_title']) || $this->args['display_job_title'];
$display_bio = !isset($this->args['display_bio']) || $this->args['display_bio'];
$display_socials = !isset($this->args['display_socials']) || $this->args['display_socials'];
$display_tel = !isset($this->args['display_tel']) || $this->args['display_tel'];
$display_email = !isset($this->args['display_email']) || $this->args['display_email'];
$display_mobile = !isset($this->args['display_mobile']) || $this->args['display_mobile'];
$display_website = isset($this->args['display_website']) && $this->args['display_website'];
$display_fax = !isset($this->args['display_fax']) || $this->args['display_fax'];
$display_form = !isset($this->args['display_form']) || $this->args['display_form'];
$author_link = isset($this->args['author_link']) && $this->args['author_link'];
$link_avatar = isset($this->args['link_avatar']) && $this->args['link_avatar'];
$field_name = !isset($this->args['name_field']) || $this->args['name_field'];
$field_phone = !isset($this->args['phone_field']) || $this->args['phone_field'];

// Social Networks
$socials = (new LSD_Socials())->list($owner_id);

// Current User
$current = wp_get_current_user();
$current_id = get_current_user_id();
?>
<?php if ($this->layout === 'image-name'): $size = $this->args['size'] ?? 48; ?>
<div class="lsd-owner-image-name">
    <div class="lsd-owner-image" title="<?php echo esc_attr(get_the_author_meta('display_name', $owner_id)); ?>">
        <?php echo get_avatar($owner_id, $size); ?>
    </div>
</div>
<?php elseif ($this->layout === 'details'): $user = get_user_meta($owner_id); ?>
<div class="lsd-owner-details" <?php echo lsd_schema()->scope()->type('https://schema.org/Person'); ?>>
	<div class="lsd-owner-details-wrapper">
		<div class="lsd-owner-information">
			<div class="lsd-owner-first-part">
        		<?php if($display_avatar) :?>
                    <div class="lsd-owner-image-wrapper">
                        <?php if ($link_avatar): ?>
                            <a href="<?php echo esc_url_raw(LSD_User::profile_link($owner_id)); ?>">
                                <?php echo get_avatar($owner_id, 250); ?>
                            </a>
                            <?php else: echo get_avatar($owner_id, 250); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
        		<div class="lsd-owner-information-part-1">
                    <?php if ($display_name): ?>
					<h4 class="lsd-owner-name" <?php echo lsd_schema()->name(); ?>>
						<?php if ($author_link): ?>
							<a href="<?php echo esc_url_raw(LSD_User::profile_link($owner_id)); ?>">
								<?php echo esc_html(get_the_author_meta('display_name', $owner_id)); ?>
							</a>
						<?php else: echo esc_html(get_the_author_meta('display_name', $owner_id)); ?>
						<?php endif; ?>
					</h4>
                    <?php endif; ?>

					<?php if ($display_job_title && isset($user['lsd_job_title'][0]) && trim($user['lsd_job_title'][0])): ?>
					<div class="lsd-owner-job-title" <?php echo lsd_schema()->jobTitle(); ?> ><?php echo esc_html($user['lsd_job_title'][0]); ?></div>
					<?php endif; ?>

					<?php if ($display_socials && trim($socials) != ''): ?>
					<div class="lsd-owner-social-networks">
						<ul><?php echo LSD_Kses::element($socials); ?></ul>
					</div>
					<?php endif; ?>

					<?php if ($display_bio && isset($user['description'][0]) && trim($user['description'][0])): ?>
					<div class="lsd-owner-biography" <?php echo lsd_schema()->description(); ?> ><?php echo esc_html($user['description'][0]); ?></div>
					<?php endif; ?>
				</div>
			</div>

			<div class="lsd-owner-information-part-2">
				<?php if ($display_tel && isset($user['lsd_phone'][0]) && trim($user['lsd_phone'][0])): ?>
				<div class="lsd-owner-phone" title="<?php esc_attr_e('Phone', 'listdom'); ?>" <?php echo lsd_schema()->telephone(); ?>>
					<i class="lsd-icon fas fa-phone-alt"></i>
					<a href="tel:<?php echo esc_html($user['lsd_phone'][0]); ?>"><?php echo esc_html($user['lsd_phone'][0]); ?></a>
				</div>
				<?php endif; ?>

                <?php if ($display_email): ?>
				<div class="lsd-owner-email" title="<?php esc_attr_e('Email', 'listdom'); ?>" <?php echo lsd_schema()->email(); ?>>
					<i class="lsd-icon fa fa-envelope"></i>
					<a href="mailto:<?php echo esc_html(get_the_author_meta('email', $owner_id)); ?>"><?php echo esc_html(get_the_author_meta('email', $owner_id)); ?></a>
				</div>
                <?php endif; ?>

				<?php if ($display_mobile && isset($user['lsd_mobile'][0]) && trim($user['lsd_mobile'][0])): ?>
				<div class="lsd-owner-mobile" title="<?php esc_attr_e('Mobile', 'listdom'); ?>" <?php echo lsd_schema()->telephone(); ?>>
					<i class="lsd-icon fa fa-mobile"></i>
					<a href="tel:<?php echo esc_html($user['lsd_mobile'][0]); ?>"><?php echo esc_html($user['lsd_mobile'][0]); ?></a>
				</div>
				<?php endif; ?>

                <?php if ($display_website && isset($user['lsd_website'][0]) && trim($user['lsd_website'][0])): ?>
                <div class="lsd-owner-website" title="<?php esc_attr_e('Website', 'listdom'); ?>">
                    <i class="lsd-icon fas fa-link"></i>
                    <a href="<?php echo esc_url($user['lsd_website'][0]); ?>" target="_blank"><?php echo esc_html(LSD_Base::remove_protocols($user['lsd_website'][0])); ?></a>
                </div>
                <?php endif; ?>

				<?php if ($display_fax && isset($user['lsd_fax'][0]) && trim($user['lsd_fax'][0])): ?>
				<div class="lsd-owner-fax" title="<?php esc_attr_e('Fax', 'listdom'); ?>" <?php echo lsd_schema()->faxNumber(); ?>>
					<i class="lsd-icon fa fa-fax"></i>
					<span><?php echo esc_html($user['lsd_fax'][0]); ?></span>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php if ($display_form): ?>
<div class="lsd-owner-contact-form-wrapper">
	<form class="lsd-owner-contact-form" id="lsd_owner_contact_form_<?php echo esc_attr($post_id); ?>" data-id="<?php echo esc_attr($post_id); ?>">

		<div class="lsd-owner-contact-form-name-email-phone-wrapper">
			<?php if ($field_name): ?>
			<div class="lsd-owner-contact-form-row lsd-owner-contact-form-row-name">
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
			<?php endif; ?>

			<div class="lsd-owner-contact-form-row lsd-owner-contact-form-row-email">
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

			<?php if ($field_phone): ?>
			<div class="lsd-owner-contact-form-row lsd-owner-contact-form-row-phone">
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
			<?php endif; ?>
		</div>

		<div class="lsd-owner-contact-form-row">
			<textarea
				class="lsd-form-control-textarea"
				name="lsd_message"
				placeholder="<?php esc_attr_e("I'm interested in ....", 'listdom') ?>"
				title="<?php esc_attr_e('Your Message', 'listdom') ?>"
				required
			></textarea>
		</div>

		<div class="lsd-owner-contact-form-row lsd-owner-contact-form-third-row">
			<?php echo LSD_Main::grecaptcha_field('transform-95'); ?>
			<button class="lsd-form-submit lsd-widefat lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?>" type="submit"><?php esc_html_e('Send', 'listdom'); ?></button>

			<?php wp_nonce_field('lsd_contact_'.$post_id); ?>
			<input type="hidden" name="lsd_post_id" value="<?php echo esc_attr($post_id); ?>">
			<input type="hidden" name="action" value="lsd_owner_contact">
		</div>
	</form>

    <div class="lsd-owner-contact-form-alert"></div>
</div>
<?php endif; ?>
<?php endif;
