<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

$settings = LSD_Options::settings();
$ai_visibility_settings = LSD_AI_Visibility_Settings::settings();
$is_pro = LSD_Base::isPro();
$advanced_slug_status = $is_pro && !empty($settings['advanced_slug_status']) ? 1 : 0;
?>
<div class="lsd-settings-wrap">
    <form id="lsd_settings_form">
        <div id="lsd_panel_seo_structured-data" class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === 'structured-data' || !$this->subtab ? ' lsd-tab-content-active' : ''; ?>">
            <div class="lsd-settings-group-wrapper">
                <div class="lsd-settings-fields-wrapper">
                    <h3 class="lsd-my-0 lsd-admin-title"><?php esc_html_e('Structured Data', 'listdom'); ?></h3>
                    <div class="lsd-form-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Structured Data', 'listdom'),
                            'for' => 'lsd_settings_ai_visibility_structured_data',
                        ]); ?></div>
                        <div class="lsd-col-5">
                            <?php echo LSD_Form::switcher([
                                'id' => 'lsd_settings_ai_visibility_structured_data',
                                'name' => 'lsd[ai_visibility][structured_data]',
                                'value' => $ai_visibility_settings['structured_data'] ?? '1',
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Output JSON-LD for single listings and listing collection pages using only valid public listing data.', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div id="lsd_panel_seo_ai-visibility" class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === 'ai-visibility' ? ' lsd-tab-content-active' : ''; ?>">
            <div class="lsd-settings-group-wrapper">
                <div class="lsd-settings-fields-wrapper">
                    <h3 class="lsd-my-0 lsd-admin-title"><?php esc_html_e('AI Visibility', 'listdom'); ?></h3>
                    <div class="lsd-form-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Public AI Feed', 'listdom'),
                            'for' => 'lsd_settings_ai_visibility_public_feed',
                        ]); ?></div>
                        <div class="lsd-col-5">
                            <?php echo LSD_Form::switcher([
                                'id' => 'lsd_settings_ai_visibility_public_feed',
                                'name' => 'lsd[ai_visibility][public_feed]',
                                'value' => $ai_visibility_settings['public_feed'] ?? '0',
                                'toggle' => '#lsd_settings_ai_visibility_public_feed_options',
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Enable stable public endpoints for listings, categories, and locations under the Listdom REST namespace.', 'listdom'); ?></p>
                        </div>
                    </div>
                    <div id="lsd_settings_ai_visibility_public_feed_options" class="lsd-settings-fields-sub-wrapper <?php echo !empty($ai_visibility_settings['public_feed']) ? '' : 'lsd-util-hide'; ?>">
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                    'class' => 'lsd-fields-label',
                                    'title' => esc_html__('Public API URLs', 'listdom'),
                                    'for' => 'lsd_settings_ai_visibility_public_api_urls',
                                ]); ?></div>
                            <div class="lsd-col-9">
                                <div id="lsd_settings_ai_visibility_public_api_urls" class="lsd-flex lsd-flex-col lsd-flex-items-start lsd-gap-2">
                                    <?php foreach (LSD_AI_Visibility::public_feed_urls(['scope' => 'settings']) as $feed_key => $feed_url): ?>
                                        <?php if (!is_string($feed_url) || trim($feed_url) === '') continue; ?>
                                        <span>
                                            <strong><?php echo esc_html(LSD_AI_Visibility::public_feed_url_label((string) $feed_key)); ?>:</strong>
                                            <a href="<?php echo esc_url($feed_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($feed_url); ?></a>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-3">
                                    <?php esc_html_e('These endpoints become publicly discoverable when Public AI Feed is enabled and Settings > Reading > Search Engine Visibility is configured to allow search engines to index the site.', 'listdom'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Add llms.txt Discovery', 'listdom'),
                                'for' => 'lsd_settings_ai_visibility_llms_txt',
                            ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_settings_ai_visibility_llms_txt',
                                    'name' => 'lsd[ai_visibility][llms_txt]',
                                    'value' => $ai_visibility_settings['llms_txt'] ?? '1',
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Expose a dynamic /llms.txt document that points AI systems to the public Listdom feeds.', 'listdom'); ?></p>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Add robots.txt Discovery Hints', 'listdom'),
                                'for' => 'lsd_settings_ai_visibility_robots_txt',
                            ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_settings_ai_visibility_robots_txt',
                                    'name' => 'lsd[ai_visibility][robots_txt]',
                                    'value' => $ai_visibility_settings['robots_txt'] ?? '1',
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Append Listdom AI discovery hints to the virtual robots.txt output without touching any physical robots.txt file.', 'listdom'); ?></p>
                            </div>
                        </div>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Add HTML Feed Discovery Links', 'listdom'),
                                'for' => 'lsd_settings_ai_visibility_html_links',
                            ]); ?></div>
                            <div class="lsd-col-5">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_settings_ai_visibility_html_links',
                                    'name' => 'lsd[ai_visibility][html_links]',
                                    'value' => $ai_visibility_settings['html_links'] ?? '1',
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Add discovery link tags to public listing, category, location, and directory archive pages.', 'listdom'); ?></p>
                            </div>
                        </div>
                        <?php do_action('lsd_ai_visibility_settings', $ai_visibility_settings); ?>
                        <div class="lsd-form-row">
                            <div class="lsd-col-3"><?php echo LSD_Form::label([
                                'class' => 'lsd-fields-label',
                                'title' => esc_html__('Public Feed Fields', 'listdom'),
                                'for' => 'lsd_settings_ai_visibility_fields',
                            ]); ?></div>
                            <div class="lsd-col-9">
                                <div id="lsd_settings_ai_visibility_fields" class="lsd-flex lsd-flex-wrap lsd-gap-4">
                                    <?php foreach (LSD_AI_Visibility::field_options() as $field_key => $field_label): ?>
                                        <label class="lsd-flex lsd-gap-2 lsd-flex-align-center">
                                            <?php echo LSD_Form::switcher([
                                                'id' => 'lsd_settings_ai_visibility_field_' . $field_key,
                                                'name' => 'lsd[ai_visibility][fields][' . $field_key . ']',
                                                'value' => $ai_visibility_settings['fields'][$field_key] ?? '1',
                                            ]); ?>
                                            <span><?php echo esc_html($field_label); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php esc_html_e('Only these public fields will be exposed in the AI-readable listing feed. Private user, booking, payment, guest, and admin data are never included.', 'listdom'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div id="lsd_panel_seo_slugs" class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === 'slugs' ? ' lsd-tab-content-active' : ''; ?>">
            <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Slugs', 'listdom'); ?></h3>
            <div class="lsd-settings-group-wrapper">
                <div class="lsd-settings-fields-wrapper">
                    <h4 class="lsd-admin-title"><?php esc_html_e('Listings', 'listdom'); ?></h4>
                    <div class="lsd-form-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Prefix', 'listdom'),
                            'for' => 'lsd_settings_listings_slug',
                        ]); ?></div>
                        <div class="lsd-col-5">
                            <?php echo LSD_Form::text([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_settings_listings_slug',
                                'name' => 'lsd[listings_slug]',
                                'value' => isset($settings['listings_slug']) && trim($settings['listings_slug']) ? $settings['listings_slug'] : LSD_Options::slug()
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo sprintf(
                                /* translators: %s: Example listing permalink. */
                                esc_html__("This option modifies the listing page URL. For example, if you set it to markers, the listings' addresses will be %s", 'listdom'),
                                sprintf('https://yourwebsite.com/%s/listing-name/', '<strong>markers</strong>')
                            ); ?></p>
                        </div>
                    </div>
                    <div class="lsd-form-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Advanced Slug', 'listdom'),
                            'for' => 'lsd_settings_advanced_slug',
                        ]); ?></div>
                        <div class="lsd-col-5">
                            <?php echo LSD_Form::switcher([
                                'id' => 'lsd_settings_advanced_slug',
                                'name' => 'lsd[advanced_slug_status]',
                                'toggle' => '.lsd-advanced-slug',
                                'value' => $advanced_slug_status
                            ]); ?>
                        </div>
                    </div>
                    <?php if (!$is_pro): ?>
                        <div class="lsd-form-row lsd-advanced-slug <?php echo $advanced_slug_status ? '' : 'lsd-util-hide'; ?>">
                            <div class="lsd-col-12">
                                <?php echo LSD_Base::alert(LSD_Main::missFeatureMessage(esc_html__('Advanced Slug', 'listdom')), 'warning'); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="lsd-form-row lsd-mb-0 lsd-advanced-slug <?php echo $advanced_slug_status ? '' : 'lsd-util-hide'; ?>">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Pattern', 'listdom'),
                            'for' => 'lsd_settings_advanced_slug',
                        ]); ?></div>
                        <div class="lsd-col-5">
                            <?php echo LSD_Form::text([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_settings_advanced_slug',
                                'name' => 'lsd[advanced_slug]',
                                'value' => isset($settings['advanced_slug']) && trim($settings['advanced_slug']) ? preg_replace('/\s+/', '', strtolower($settings['advanced_slug'])) : ''
                            ]); ?>
                            <div class="lsd-advanced-slug-help">
                                <p><?php esc_html_e("You can use the following placeholders:", 'listdom'); ?></p>
                                <ul class="lsd-mb-0">
                                    <li><code>%category%</code>: <?php esc_html_e('Includes the slug of the primary category in the URL, such as restaurant or cafe.', 'listdom'); ?></li>
                                    <li><code>%categories%</code>: <?php esc_html_e('Includes the slug of the primary category and its parent categories in the URL, such as cars/suv or restaurants/persian.', 'listdom'); ?></li>
                                    <li><code>%location%</code>: <?php esc_html_e('Includes the slug of the first location in the URL.', 'listdom'); ?></li>
                                    <li><code>%locations%</code>: <?php esc_html_e('Includes the slug of the first location and its parent locations in the URL, such as united-states/california or canada/bc.', 'listdom'); ?></li>
                                    <li><?php echo sprintf(
                                        /* translators: 1: Slash character, 2: Hyphen character. */
                                        esc_html__("Make sure to use %1\$s or %2\$s to separate parts of the slug.", 'listdom'),
                                        '<code>/</code>',
                                        '<code>-</code>'
                                    ); ?></li>
                                    <li><?php esc_html_e('Please use lowercase characters and avoid spaces or tab characters in the URL.', 'listdom'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lsd-settings-fields-wrapper">
                    <h4 class="lsd-my-0 lsd-admin-title"><?php esc_html_e('Taxonomies', 'listdom'); ?></h4>
                    <div class="lsd-form-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Location', 'listdom'),
                            'for' => 'lsd_settings_location_slug',
                        ]); ?></div>
                        <div class="lsd-col-5">
                            <?php echo LSD_Form::text([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_settings_location_slug',
                                'name' => 'lsd[location_slug]',
                                'value' => $settings['location_slug'] ?? ''
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo esc_html__("It's for changing the location archive prefix.", 'listdom'); ?></p>
                        </div>
                    </div>
                    <div class="lsd-form-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Category', 'listdom'),
                            'for' => 'lsd_settings_category_slug',
                        ]); ?></div>
                        <div class="lsd-col-5">
                            <?php echo LSD_Form::text([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_settings_category_slug',
                                'name' => 'lsd[category_slug]',
                                'value' => $settings['category_slug'] ?? ''
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo esc_html__("It's for changing the category archive prefix.", 'listdom'); ?></p>
                        </div>
                    </div>
                    <div class="lsd-form-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Tag', 'listdom'),
                            'for' => 'lsd_settings_tag_slug',
                        ]); ?></div>
                        <div class="lsd-col-5">
                            <?php echo LSD_Form::text([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_settings_tag_slug',
                                'name' => 'lsd[tag_slug]',
                                'value' => $settings['tag_slug'] ?? ''
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo esc_html__("It's for changing the tag archive prefix.", 'listdom'); ?></p>
                        </div>
                    </div>
                    <div class="lsd-form-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Feature', 'listdom'),
                            'for' => 'lsd_settings_feature_slug',
                        ]); ?></div>
                        <div class="lsd-col-5">
                            <?php echo LSD_Form::text([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_settings_feature_slug',
                                'name' => 'lsd[feature_slug]',
                                'value' => $settings['feature_slug'] ?? ''
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo esc_html__("It's for changing the feature archive prefix.", 'listdom'); ?></p>
                        </div>
                    </div>
                    <div class="lsd-form-row">
                        <div class="lsd-col-3"><?php echo LSD_Form::label([
                            'class' => 'lsd-fields-label',
                            'title' => esc_html__('Label', 'listdom'),
                            'for' => 'lsd_settings_label_slug',
                        ]); ?></div>
                        <div class="lsd-col-5">
                            <?php echo LSD_Form::text([
                                'class' => 'lsd-admin-input',
                                'id' => 'lsd_settings_label_slug',
                                'name' => 'lsd[label_slug]',
                                'value' => $settings['label_slug'] ?? ''
                            ]); ?>
                            <p class="lsd-admin-description-tiny lsd-mb-0 lsd-mt-2"><?php echo esc_html__("It's for changing the label archive prefix.", 'listdom'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lsd-spacer-30"></div>
        <div class="lsd-form-row lsd-settings-submit-wrapper">
            <div class="lsd-col-12 lsd-flex lsd-gap-3 lsd-flex-content-end">
                <?php LSD_Form::nonce('lsd_settings_form'); ?>
                <button type="submit" id="lsd_settings_save_button" class="lsd-primary-button">
                    <?php esc_html_e('Save The Changes', 'listdom'); ?>
                    <i class="wbli wbli-checkmark-circle"></i>
                </button>
            </div>
        </div>
    </form>
</div>
<script>
jQuery('#lsd_settings_form').on('submit', function(e)
{
    e.preventDefault();

    // Elements
    const $button = jQuery('#lsd_settings_save_button');
    const $tab = jQuery('.lsd-nav-tab-active');

    // Loading Wrapper
    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( esc_html__('Saving', 'listdom') ); ?>");

    const settings = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: 'POST',
        url: ajaxurl,
        data: 'action=lsd_save_settings&' + settings,
        success: function()
        {
            $tab.attr('data-saved', 'true');

            listdom_toastify("<?php echo esc_js(esc_html__('Options saved successfully.', 'listdom')); ?>", 'lsd-success');

            // Unloading
            loading.stop();
        },
        error: function()
        {
            $tab.attr('data-saved', 'false');

            listdom_toastify("<?php echo esc_js(esc_html__('Error: Unable to save options.', 'listdom')); ?>", 'lsd-error');

            // Unloading
            loading.stop();
        }
    });
});
</script>
