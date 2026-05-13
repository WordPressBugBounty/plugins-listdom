<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Dashboard $this */

// Entity
$entity = new LSD_Entity_Listing($this->post->ID);
$taxonomies = new LSD_Dashboard_Taxonomies_Terms();

// Category
$category = $entity->get_data_category();

// All Categories
$all_categories = LSD_Taxonomies_Category::get_terms();

// Default Category Selection
$uncategorized_id = LSD_Main::get_uncategorized_category_id();
$selected_category_id = $category && isset($category->term_id)
    ? $category->term_id
    : (is_array($all_categories) && count($all_categories) === 1
        ? $all_categories[0]->term_id
        : ($uncategorized_id ?: null));

// Objects
$postType = new LSD_PTypes_Listing();

$gallery_max_size = $this->settings['submission_max_image_upload_size'] ?? '';
$image_aspect_ratio = $this->settings['submission_image_aspect_ratio'] ?? '';
$image_aspect_ratio = is_string($image_aspect_ratio) ? trim($image_aspect_ratio) : '';
$image_aspect_ratio_message = $image_aspect_ratio !== ''
    ? sprintf(
        /* translators: %s: Required aspect ratio for images. */
        esc_html__('Please upload an image with an aspect ratio close to %s.', 'listdom'),
        $image_aspect_ratio
    )
    : '';
$image_placeholder = LSD_Form::image_placeholder_data('large');
$image_placeholder_src = $image_placeholder['src'] ?? '';

// Forced Status by Settings
$forced_listing_status = $this->settings['dashboard_listing_status'] ?? '';

// Privacy Consent
$privacy = LSD_Options::privacy();

$dashboard_privacy_field = LSD_Privacy::consent_field([
    'id' => 'lsd_dashboard_privacy_consent_' . $this->post->ID,
    'name' => 'lsd[privacy_consent]',
    'checked' => $privacy['privacy_consent']['submission_pc_enabled'] ?? 0,
    'wrapper_class' => 'lsd-dashboard-privacy-consent-field',
    'context' => 'dashboard',
]);

$form_columns = $this->form_columns ?? 2;
$job_addon_installed = class_exists(LSDADDJOB::class) || class_exists(\LSDPACJOB\Base::class) ? 1 : 0;

$classes = ['lsd-dashboard', 'lsd-dashboard-form'];
if ($form_columns === 1) $classes[] = 'lsd-dashboard-form-single-column';

$dashboard_wrapper = $this->get_dashboard_wrapper([
    'classes' => $classes,
    'attributes' => [
        'data-form-columns' => (int) $form_columns,
        'data-job-addon-installed' => $job_addon_installed,
    ],
]);

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
<div class="<?php echo esc_attr($dashboard_wrapper['class']); ?>" id="lsd_dashboard"<?php echo $dashboard_wrapper['attributes']; ?>>

    <div class="lsd-dashboard-wrapper">
        <?php if (!$this->form_type): ?>
            <div class="lsd-dashboard-menus-wrapper">
                <?php echo LSD_Kses::element($this->menus()); ?>
            </div>
        <?php endif; ?>

        <div class="lsd-dashboard-content-wrapper">
            <?php if($form_columns === 2): ?>
            <div id="lsd_dashboard_form_message"></div>
            <?php endif; ?>
            <form class="lsd-dashboard-form" id="lsd_dashboard_form" enctype="multipart/form-data">
                <div class="lsd-dashboard-form-columns">
                    <div class="lsd-dashboard-form-left-column">
						<div class="lsd-dashboard-form-left-col-wrapper lsd-fe-sections">
							<div class="lsd-dashboard-title lsd-fe-box-white">
                                <div class="lsd-col-3 lsd-text-left">
                                    <h4 class="lsd-fe-title"><?php esc_html_e('Title', 'listdom'); ?><?php $this->required_html('title'); ?></h4>
                                </div>
								<input type="text" name="lsd[title]" required value="<?php echo isset($this->post->post_title) ? esc_attr($this->post->post_title) : ''; ?>" placeholder="<?php esc_attr_e('Title', 'listdom'); ?>" title="<?php esc_attr_e('Title', 'listdom'); ?>">
							</div>

                            <div class="lsd-dashboard-editor lsd-fe-box-white">
                                <div class="lsd-col-3 lsd-text-left">
                                    <h4 class="lsd-fe-title"><?php esc_html_e('Description', 'listdom'); ?><?php $this->required_html('content'); ?></h4>
                                </div>

								<?php wp_editor($this->post->post_content ?? '', 'lsd_dashboard_content', ['textarea_name'=>'lsd[content]']); ?>
							</div>

                            <?php if ($this->is_enabled('excerpt')): ?>
                                <div class="lsd-listing-module-excerpt lsd-fe-box-white">
                                    <div class="lsd-excerpt-row lsd-fe-subsections">
                                        <div class="lsd-col-12 lsd-text-left">
                                            <h4 class="lsd-fe-title"><?php esc_html_e('Excerpt', 'listdom'); ?><?php $this->required_html('excerpt'); ?></h4>
                                        </div>
                                        <div>
                                            <?php wp_editor($this->post->post_excerpt ?? '', 'lsd_dashboard_excerpt', ['textarea_name' => 'lsd[excerpt]']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($this->is_enabled('attributes')): ?>
                            <div class="lsd-dashboard-right-box lsd-dashboard-attributes lsd-fe-box-white">
                                <h4 class="lsd-fe-title"><?php esc_html_e('Custom Fields', 'listdom'); ?></h4>
                                <div>
                                    <?php do_action('lsd_dashboard_attributes_metabox', $this); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($this->is_enabled('address') && LSD_Components::map()): ?>
							<div class="lsd-dashboard-right-box lsd-dashboard-address lsd-fe-box-white ">
								<h4 class="lsd-fe-title"><?php esc_html_e('Address / Map', 'listdom'); ?></h4>
                                <?php $postType->metabox_address($this->post); ?>
							</div>
							<?php endif; ?>

							<div class="lsd-dashboard-right-box lsd-dashboard-details">
								<div>
									<?php $postType->metabox_details($this->post); ?>
								</div>
							</div>

							<?php do_action('lsd_dashboard_after_attributes', $this->post, $this); ?>

							<?php if (!get_current_user_id()): ?>
							<div class="lsd-dashboard-right-box lsd-dashboard-message lsd-fe-box-white">
								<h4 class="lsd-fe-title"><?php esc_html_e('To Reviewer', 'listdom'); ?></h4>

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

                            <?php if ($form_columns === 1): ?>
                                <div class="lsd-fe-box-white">
                                    <div class="lsd-dashboard-submit lsd-dashboard-box lsd-dashboard-submit">
                                        <input type="hidden" name="id" value="<?php echo esc_attr($this->post->ID); ?>" id="lsd_dashboard_id">
                                        <input type="hidden" name="action" value="lsd_dashboard_listing_save">

                                        <?php LSD_Form::nonce('lsd_dashboard'); ?>
                                        <?php /* Security Nonce */ LSD_Form::nonce('lsd_listing_cpt', '_lsdnonce'); ?>

                                        <div class="lsd-fe-subsections">
                                            <?php if ((!$forced_listing_status || $this->post->ID > 0) && current_user_can('publish_posts')): ?>
                                                <div class="lsd-dashboard-listing-status">
                                                    <?php echo LSD_Form::select([
                                                        'id' => 'lsd_listing_status',
                                                        'name' => 'lsd[listing_status]',
                                                        'value' => $this->post->post_status ?? 'publish',
                                                        'options' => [
                                                            'publish' => esc_html__('Published', 'listdom'),
                                                            'pending' => esc_html__('Pending Review', 'listdom'),
                                                            'draft' => esc_html__('Draft', 'listdom'),
                                                        ],
                                                        'required' => true,
                                                    ]); ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($dashboard_privacy_field !== ''): ?>
                                                <div class="lsd-dashboard-privacy-consent">
                                                    <?php echo LSD_Kses::form($dashboard_privacy_field); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="lsd-dashboard-submit-wrapper">
                                            <div class="lsd-dashboard-grecaptcha">
                                                <?php echo LSD_Main::grecaptcha_field(); ?>
                                            </div>

                                            <button type="submit" class="lsd-general-button <?php echo esc_attr($this->get_text_class()); ?>">
                                                <?php esc_html_e('Save', 'listdom'); ?>
                                                <i class="lsd-fe-icon fa-solid fa-long-arrow-right"></i>
                                            </button>
                                        </div>

                                        <?php do_action('lsd_dashboard_after_submit_button', $this); ?>
                                    </div>

                                    <div id="lsd_dashboard_form_message"></div>
                                </div>
                            <?php endif; ?>
						</div>
                    </div>
                    <div class="lsd-dashboard-form-right-column lsd-fe-sections">

                        <?php if($form_columns === 2): ?>
                        <div class="lsd-dashboard-box lsd-dashboard-submit lsd-fe-box-white">
                            <input type="hidden" name="id" value="<?php echo esc_attr($this->post->ID); ?>" id="lsd_dashboard_id">
                            <input type="hidden" name="action" value="lsd_dashboard_listing_save">

                            <?php LSD_Form::nonce('lsd_dashboard'); ?>
                            <?php /* Security Nonce */ LSD_Form::nonce('lsd_listing_cpt', '_lsdnonce'); ?>

                            <div class="lsd-fe-subsections">
                                <?php if ((!$forced_listing_status || $this->post->ID > 0) && current_user_can('publish_posts')): ?>
                                    <div class="lsd-dashboard-listing-status">
                                        <?php echo LSD_Form::select([
                                            'id' => 'lsd_listing_status',
                                            'name' => 'lsd[listing_status]',
                                            'value' => $this->post->post_status ?? 'publish',
                                            'options' => [
                                                'publish' => esc_html__('Published', 'listdom'),
                                                'pending' => esc_html__('Pending Review', 'listdom'),
                                                'draft' => esc_html__('Draft', 'listdom'),
                                            ],
                                            'required' => true,
                                        ]); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($dashboard_privacy_field !== ''): ?>
                                    <div class="lsd-dashboard-privacy-consent">
                                        <?php echo LSD_Kses::form($dashboard_privacy_field); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="lsd-dashboard-submit-wrapper">
                                <div class="lsd-dashboard-grecaptcha">
                                    <?php echo LSD_Main::grecaptcha_field(); ?>
                                </div>

                                <button type="submit" class="lsd-general-button <?php echo esc_attr($this->get_text_class()); ?>">
                                    <?php esc_html_e('Save', 'listdom'); ?>
                                    <i class="lsd-fe-icon fa-solid fa-long-arrow-right"></i>
                                </button>
                            </div>
                            <?php do_action('lsd_dashboard_after_submit_button', $this); ?>
                        </div>
                        <?php endif; ?>
                        <div class="lsd-dashboard-box lsd-dashboard-category lsd-fe-box-white">
                            <div class="lsd-fe-section-heading">
                                <h4 class="lsd-fe-title"><?php echo esc_html__('Category', 'listdom').' '.LSD_Base::REQ_HTML; ?></h4>
                                <?php echo LSD_KSes::full($taxonomies->display(['taxonomy' => LSD_Base::TAX_CATEGORY])); ?>
                            </div>
                            <div class="lsd-fe-subsections">
                                <?php
                                    echo LSD_Dashboard_Terms::category([
                                        'taxonomy' => LSD_Base::TAX_CATEGORY,
                                        'hide_empty' => 0,
                                        'orderby' => 'name',
                                        'order' => 'ASC',
                                        'selected' => $selected_category_id,
                                        'hierarchical' => 0,
                                        'id' => 'lsd_listing_category',
                                        'name' => 'lsd[listing_category]',
                                        'required' => true,
                                        'parent' => 0,
                                        'level' => 0,
                                    ]);

                                    // Additional Categories
                                    do_action('lsd_after_primary_category', $this->post, $this);
                                ?>
                            </div>
                        </div>

                        <?php if ($this->is_enabled('locations')): ?>
                        <div class="lsd-dashboard-box lsd-dashboard-locations lsd-fe-box-white">
                            <div class="lsd-fe-section-heading">
                                <h4 class="lsd-fe-title"><?php esc_html_e('Locations', 'listdom'); ?><?php $this->required_html(LSD_Base::TAX_LOCATION); ?></h4>
                                <?php echo LSD_KSes::full($taxonomies->display(['taxonomy' => LSD_Base::TAX_LOCATION])); ?>
                            </div>
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
                        <div class="lsd-dashboard-box lsd-dashboard-tags lsd-fe-box-white">
                            <div class="lsd-fe-section-heading">
                                <h4 class="lsd-fe-title"><?php esc_html_e('Tags', 'listdom'); ?><?php $this->required_html('tags'); ?></h4>
                                <?php echo LSD_KSes::full($taxonomies->display(['taxonomy' => LSD_Base::TAX_TAG])); ?>
                            </div>
                            <?php
                                $terms = wp_get_post_terms($this->post->ID, LSD_Base::TAX_TAG);

                                $tags = '';
                                if (is_array($terms) && count($terms)) foreach ($terms as $term) $tags .= $term->name.',';

                                echo LSD_Dashboard_Terms::tags([
                                    'taxonomy' => LSD_Base::TAX_TAG,
                                    'name' => 'tags',
                                    'id' => 'lsd_dashboard_tags',
                                    'level' => 0,
                                    'rows' => 3,
                                    'post_id' => $this->post->ID,
                                    'hide_empty' => 0,
                                    'orderby' => 'name',
                                    'order' => 'ASC',
                                    'placeholder' => esc_attr__('Tag1,Tag2,Tag3', 'listdom'),
                                    'selected' => stripslashes(trim($tags, ', '))
                                ]);
                            ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($this->is_enabled('features')): ?>
                        <div class="lsd-dashboard-box lsd-dashboard-features lsd-fe-box-white">
                            <div class="lsd-fe-section-heading">
                                <h4 class="lsd-fe-title"><?php esc_html_e('Features', 'listdom'); ?><?php $this->required_html(LSD_Base::TAX_FEATURE); ?></h4>
                                <?php echo LSD_KSes::full($taxonomies->display(['taxonomy' => LSD_Base::TAX_FEATURE])); ?>
                            </div>
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
                        <div class="lsd-dashboard-box lsd-dashboard-labels lsd-fe-box-white" id="lsd-dashboard-labels">
                            <div class="lsd-fe-section-heading">
                                <h4 class="lsd-fe-title"><?php esc_html_e('Labels', 'listdom'); ?><?php $this->required_html(LSD_Base::TAX_LABEL); ?></h4>
                                <?php echo LSD_KSes::full($taxonomies->display(['taxonomy' => LSD_Base::TAX_LABEL])); ?>
                            </div>
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
                        <div class="lsd-dashboard-box lsd-dashboard-featured-image lsd-fe-box-white">
                            <div class="lsd-fe-section-heading">
                                <h4 class="lsd-fe-title"><?php esc_html_e('Featured Image', 'listdom'); ?><?php $this->required_html('featured_image'); ?></h4>
                                <p class="lsd-fe-description">
                                    <?php esc_html_e('This image will be displayed as the main image for your listing.', 'listdom'); ?>
                                </p>
                            </div>
                            <div class="lsd-fe-subsections">
                                <?php if ($gallery_max_size): ?>
                                <p class="lsd-fe-description"><?php echo sprintf(
                                    /* translators: %s: Maximum allowed image size in kilobytes. */
                                        esc_html__('The uploaded images cannot exceed the maximum allowed size of %s KB.', 'listdom'),
                                        $gallery_max_size
                                    ); ?></p>
                                <?php endif; ?>
                                <?php
                                    $attachment_id = get_post_thumbnail_id($this->post->ID);

                                    $featured_image = wp_get_attachment_image_src($attachment_id, 'large');
                                    $featured_image = $featured_image[0] ?? '';
                                    $has_featured_image = trim($featured_image) !== '';
                                ?>
                                <div id="lsd_listing_featured_image_message"></div>
                                <div id="lsd_dashboard_featured_image_placeholder" class="lsd-image-placeholder<?php echo $has_featured_image ? ' lsd-image-placeholder-has-image' : ''; ?>" data-placeholder="<?php echo esc_attr($image_placeholder_src); ?>">
                                    <div class="lsd-image-placeholder-inner">
                                        <div id="lsd_dashboard_featured_image_preview" class="lsd-image-placeholder-preview<?php echo $has_featured_image ? '' : ' lsd-util-hide'; ?>">
                                            <?php if ($has_featured_image): ?>
                                                <img src="<?php echo esc_url($featured_image); ?>" alt="<?php esc_attr_e('Featured image preview', 'listdom'); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="lsd-image-placeholder-empty<?php echo $has_featured_image ? ' lsd-util-hide' : ''; ?>">
                                            <p class="lsd-image-placeholder-text"><?php esc_html_e('No image selected', 'listdom'); ?></p>
                                            <label for="lsd_featured_image_file" class="lsd-choose-file lsd-light-button"><?php echo esc_html__('Choose Image', 'listdom'); ?></label>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="lsd_featured_image" name="lsd[featured_image]" value="<?php echo esc_attr($attachment_id); ?>">
                                <input class="lsd-util-hide" type="file" id="lsd_featured_image_file" data-aspect-ratio="<?php echo esc_attr($image_aspect_ratio); ?>"<?php echo $image_aspect_ratio_message !== '' ? ' data-aspect-message="' . esc_attr($image_aspect_ratio_message) . '"' : ''; ?>>
                                <div class="lsd-dashboard-feature-image-remove-wrapper lsd-mt-3">
                                    <span id="lsd_featured_image_remove_button" class="lsd-remove-image-button <?php echo esc_attr($this->get_text_class()); ?> <?php echo $has_featured_image ? '' : 'lsd-util-hide'; ?>">
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
