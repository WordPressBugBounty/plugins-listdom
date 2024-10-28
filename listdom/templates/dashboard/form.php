<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $this */

// Entity
$entity = new LSD_Entity_Listing($this->post->ID);

// Category
$category = $entity->get_data_category();

// Form Type
$form_type = $this->form_type;

// Objects
$postType = new LSD_PTypes_Listing();
$gallery_max_size = $this->settings['submission_max_image_upload_size'] ?? '';

// Add JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_dashboard").listdomDashboardForm(
    {
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        nonce: "'.wp_create_nonce('lsd_dashboard').'"
    });
});
</script>');
?>
<div class="lsd-dashboard lsd-dashboard-form" id="lsd_dashboard" data-job-addon-installed="<?php echo class_exists('LSDADDJOB_Addon') ? 1 : 0; ?>">

    <div class="lsd-row">
        <?php if (!$form_type): ?>
            <div class="lsd-col-2 lsd-dashboard-menus-wrapper">
                <?php echo LSD_Kses::element($this->menus()); ?>
            </div>
            <div class="lsd-col-10">
        <?php else: ?>
            <div class="lsd-col-12">
        <?php endif; ?>
            <div id="lsd_dashboard_form_message"></div>
            <form class="lsd-dashboard-form" id="lsd_dashboard_form" enctype="multipart/form-data">
                <div class="lsd-row">
                    <div class="lsd-col-8">
						<div class="lsd-dashboard-form-left-col-wrapper">
							<div class="lsd-dashboard-title">
								<input type="text" name="lsd[title]" required value="<?php echo isset($this->post->post_title) ? esc_attr($this->post->post_title) : ''; ?>" placeholder="<?php esc_attr_e('Title', 'listdom'); ?>">
							</div>

							<div class="lsd-dashboard-editor">
								<?php wp_editor($this->post->post_content ?? '', 'lsd_dashboard_content', ['textarea_name'=>'lsd[content]']); ?>
							</div>

							<?php if ($this->is_enabled('address')): ?>
							<div class="lsd-dashboard-right-box lsd-dashboard-address">
								<h4><?php esc_html_e('Address / Map', 'listdom'); ?></h4>
								<div>
									<?php $postType->metabox_address($this->post); ?>
								</div>
							</div>
							<?php endif; ?>

							<div class="lsd-dashboard-right-box lsd-dashboard-details">
								<h4><?php esc_html_e('Details', 'listdom'); ?></h4>
								<div>
									<?php $postType->metabox_details($this->post); ?>
								</div>
							</div>

							<?php if ($this->is_enabled('attributes')): ?>
							<div class="lsd-dashboard-right-box lsd-dashboard-attributes">
								<h4><?php esc_html_e('Attributes', 'listdom'); ?></h4>
								<div>
                                    <?php do_action('lsd_dashboard_attributes_metabox', $this); ?>
								</div>
							</div>
							<?php endif; ?>

							<?php do_action('lsd_dashboard_after_attributes', $this->post, $this); ?>

							<?php if (!get_current_user_id()): ?>
							<div class="lsd-dashboard-right-box lsd-dashboard-message">
								<h4><?php esc_html_e('To Reviewer', 'listdom'); ?></h4>

								<div class="lsd-dashboard-guest-email">
									<label for="lsd_guest_email"><?php echo esc_html__('Email', 'listdom').' '.LSD_Base::REQ_HTML; ?></label>
									<input type="email" id="lsd_guest_email" name="lsd[guest_email]" required value="<?php echo esc_attr(get_post_meta($this->post->ID, 'lsd_guest_email', true)); ?>" placeholder="<?php esc_attr_e('Your Email', 'listdom'); ?>">
								</div>

                                <?php if ($this->guest_registration): ?>
                                    <div class="lsd-dashboard-guest-name">
                                        <label for="lsd_guest_fullname"><?php esc_html_e('Full Name', 'listdom'); ?></label>
                                        <input type="text" id="lsd_guest_fullname" name="lsd[guest_fullname]" value="<?php echo esc_attr(get_post_meta($this->post->ID, 'lsd_guest_fullname', true)); ?>" placeholder="<?php esc_attr_e('Please insert your full name', 'listdom'); ?>">
                                    </div>
                                    <?php if($this->guest_registration === 'submission'): ?>
                                    <div class="lsd-dashboard-guest-password">
                                        <label for="lsd_guest_password"><?php echo esc_html__('Password', 'listdom').' '.LSD_Base::REQ_HTML; ?></label>
                                        <input type="password" id="lsd_guest_password" name="lsd[guest_password]" required value="<?php echo esc_attr(get_post_meta($this->post->ID, 'lsd_guest_password', true)); ?>" placeholder="<?php esc_attr_e('Should be at-least 8 characters', 'listdom'); ?>">
                                    </div>
                                    <?php endif; ?>
                                <?php endif; ?>

								<div class="lsd-dashboard-guest-message">
									<label for="lsd_guest_message"><?php esc_html_e('Message', 'listdom'); ?></label>
									<textarea id="lsd_guest_message" name="lsd[guest_message]" placeholder="<?php esc_attr_e('Message to Reviewer', 'listdom'); ?>" rows="7"><?php echo esc_textarea(stripslashes(get_post_meta($this->post->ID, 'lsd_guest_message', true))); ?></textarea>
								</div>
							</div>
							<?php endif; ?>
						</div>
                    </div>
                    <div class="lsd-col-4">

                        <div class="lsd-dashboard-submit">
                            <input type="hidden" name="id" value="<?php echo esc_attr($this->post->ID); ?>" id="lsd_dashboard_id">
                            <input type="hidden" name="action" value="lsd_dashboard_listing_save">

                            <?php LSD_Form::nonce('lsd_dashboard'); ?>
                            <?php /* Security Nonce */ LSD_Form::nonce('lsd_listing_cpt', '_lsdnonce'); ?>

                            <button type="submit" class="lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?>">
                                <?php esc_html_e('Save', 'listdom'); ?>
                            </button>

                            <?php do_action('lsd_dashboard_after_submit_button', $this); ?>

                            <div class="lsd-dashboard-grecaptcha">
                                <?php echo LSD_Main::grecaptcha_field(); ?>
                            </div>
                        </div>

                        <div class="lsd-dashboard-box lsd-dashboard-category">
                            <h4><?php echo esc_html__('Category', 'listdom').' '.LSD_Base::REQ_HTML; ?></h4>
                            <div>
                                <?php
                                    echo LSD_Dashboard_Terms::category([
                                        'taxonomy' => LSD_Base::TAX_CATEGORY,
                                        'hide_empty' => 0,
                                        'orderby' => 'name',
                                        'order' => 'ASC',
                                        'selected' => $category && isset($category->term_id) ? $category->term_id : null,
                                        'hierarchical' => 0,
                                        'id' => 'lsd_listing_category',
                                        'name' => 'lsd[listing_category]',
                                        'required' => true
                                    ]);

                                    // Additional Categories
                                    do_action('lsd_after_primary_category', $this->post, $this);
                                ?>
                            </div>
                        </div>

                        <?php if ($this->is_enabled('locations')): ?>
                        <div class="lsd-dashboard-box lsd-dashboard-locations">
                            <h4><?php esc_html_e('Locations', 'listdom'); ?><?php $this->required_html(LSD_Base::TAX_LOCATION); ?></h4>
                            <?php
                                echo LSD_Dashboard_Terms::locations([
                                    'taxonomy' => LSD_Base::TAX_LOCATION,
                                    'parent' => 0,
                                    'level' => 0,
                                    'hide_empty' => 0,
                                    'orderby' => 'name',
                                    'order' => 'ASC',
                                    'post_id' => $this->post->ID,
                                    'name' => 'tax_input['.LSD_Base::TAX_LOCATION.']'
                                ]);
                            ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($this->is_enabled('tags')): ?>
                        <div class="lsd-dashboard-box lsd-dashboard-tags">
                            <h4><?php esc_html_e('Tags', 'listdom'); ?><?php $this->required_html('tags'); ?></h4>
                            <?php
                                $terms = wp_get_post_terms($this->post->ID, LSD_Base::TAX_TAG);

                                $tags = '';
                                if(is_array($terms) && count($terms)) foreach($terms as $term) $tags .= $term->name.',';
                            ?>
                            <textarea name="tags" id="lsd_dashboard_tags" rows="3" placeholder="<?php esc_attr_e('Tag1,Tag2,Tag3', 'listdom'); ?>"><?php echo esc_textarea(stripslashes(trim($tags, ', '))); ?></textarea>
                        </div>
                        <?php endif; ?>

                        <?php if ($this->is_enabled('features')): ?>
                        <div class="lsd-dashboard-box lsd-dashboard-features">
                            <h4><?php esc_html_e('Features', 'listdom'); ?><?php $this->required_html(LSD_Base::TAX_FEATURE); ?></h4>
                            <?php
                                echo LSD_Dashboard_Terms::features([
                                    'taxonomy' => LSD_Base::TAX_FEATURE,
                                    'parent' => 0,
                                    'level' => 0,
                                    'hide_empty' => 0,
                                    'orderby' => 'name',
                                    'order' => 'ASC',
                                    'post_id' => $this->post->ID,
                                    'name' => 'tax_input['.LSD_Base::TAX_FEATURE.']'
                                ]);
                            ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($this->is_enabled('labels')): ?>
                        <div class="lsd-dashboard-box lsd-dashboard-labels" id="lsd-dashboard-labels">
                            <h4><?php esc_html_e('Labels', 'listdom'); ?><?php $this->required_html(LSD_Base::TAX_LABEL); ?></h4>
                            <?php
                                echo LSD_Dashboard_Terms::labels([
                                    'taxonomy' => LSD_Base::TAX_LABEL,
                                    'parent' => 0,
                                    'level' => 0,
                                    'hide_empty' => 0,
                                    'orderby' => 'name',
                                    'order' => 'ASC',
                                    'post_id' => $this->post->ID,
                                    'name' => 'tax_input['.LSD_Base::TAX_LABEL.']'
                                ]);
                            ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($this->is_enabled('image') && ($this->guest_status || LSD_Capability::can('upload_files'))): ?>
                        <div class="lsd-dashboard-box lsd-dashboard-featured-image">
                            <h4><?php esc_html_e('Featured Image', 'listdom'); ?><?php $this->required_html('featured_image'); ?></h4>
                            <div>
                                <?php
                                $attachment_id = get_post_thumbnail_id($this->post->ID);

                                $featured_image = wp_get_attachment_image_src($attachment_id, 'medium');
                                if (isset($featured_image[0])) $featured_image = $featured_image[0];
                                ?>
                                <div class="lsd-col-12" id="lsd_listing_featured_image_message"></div>
                                <span id="lsd_dashboard_featured_image_preview"><?php echo trim($featured_image) ? '<img src="'.esc_url($featured_image).'" />' : ''; ?></span>
                                <input type="hidden" id="lsd_featured_image" name="lsd[featured_image]" value="<?php echo esc_attr($attachment_id); ?>">
                                <input type="file" id="lsd_featured_image_file">
                                <p class="description"><?php sprintf(esc_html__('The uploaded image exceeds the maximum allowed size of %s KB.', 'listdom'), $gallery_max_size)?></p>

                                <div class="lsd-dashboard-feature-image-remove-wrapper">
                                    <span id="lsd_featured_image_remove_button" class="lsd-remove-image-button lsd-color-m-bg <?php echo esc_attr($this->get_text_class()); ?> <?php echo (trim($featured_image) ? '' : 'lsd-util-hide'); ?>">
                                        <?php esc_html_e('Remove Image', 'listdom'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </form>

        </div>
    </div>

</div>
