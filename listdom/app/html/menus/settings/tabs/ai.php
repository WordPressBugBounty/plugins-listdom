<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Menus_Settings $this */

$ai = new LSD_AI();
$ai_settings = LSD_Options::ai();
$has_profiles = $ai->has_profile();
$connector_approval_notice = $ai->connector_approval_notice();
$module_panels = $this->get_ai_module_panels();

$semantic = new LSD_AI_Semantic();
$semantic_settings = $semantic->settings();
$semantic_profiles = $semantic->profiles();
$semantic_attributes = $semantic->attribute_options();
?>
<div class="lsd-settings-wrap">
    <form id="lsd_ai_form">
        <div id="lsd_panel_ai_profiles" class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === 'profiles' || !$this->subtab ? ' lsd-tab-content-active' : ''; ?>">
            <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('AI Profiles', 'listdom'); ?></h3>
            <div class="lsd-settings-form-group lsd-box-white lsd-rounded">
                <div class="lsd-settings-group-wrapper">
                    <div class="lsd-settings-fields-wrapper">
                        <div class="lsd-admin-section-heading">
                            <h3 class="lsd-admin-title"><?php esc_html_e('Profiles', 'listdom'); ?></h3>
                            <p class="lsd-admin-description lsd-m-0"><?php esc_html_e("You can create and manage multiple AI profiles, each configured with a specific model and settings. This allows you to choose the best AI for different tasks, such as low-cost models for auto-mapping or powerful models for content and image generation.", 'listdom'); ?></p>
                        </div>

                        <?php if ($connector_approval_notice['show']): ?>
                            <div class="lsd-alert-no-my">
                                <div class="lsd-alert lsd-info lsd-my-0">
                                    <?php echo wp_kses(
                                        sprintf(
                                            /* translators: %s: Connector Approvals page URL. */
                                            __('If your site uses WordPress 7 Connector Approvals, approve Listdom and its AI-powered addons, including Listdom Advanced Portal Search and Listdom Excel, under <a href="%s">Tools &rarr; Connector Approvals</a> before using AI profiles. Otherwise AI requests can be blocked.', 'listdom'),
                                            esc_url($connector_approval_notice['url'])
                                        ),
                                        [
                                            'a' => [
                                                'href' => [],
                                            ],
                                        ]
                                    ); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <button type="button" class="lsd-secondary-button" id="lsd_settings_ai_add_profile"><?php esc_html_e('Add New Profile', 'listdom'); ?></button>

                        <?php if (!isset($ai_settings['profiles']) || !is_array($ai_settings['profiles']) || !count($ai_settings['profiles'])): ?>
                            <div class="lsd-alert-no-my"><div class="lsd-alert lsd-info"><?php esc_html_e('Unlock AI capabilities by creating your first profile. You can then assign specific AI models to various tasks.', 'listdom'); ?></div></div>
                        <?php else: ?>
                            <div class="lsd-ai-profiles lsd-flex lsd-flex-row lsd-flex-content-start lsd-flex-wrap lsd-gap-4">
                                <?php foreach($ai_settings['profiles'] as $i => $profile): ?>
                                    <div class="lsd-box-white lsd-settings-fields-wrapper lsd-rounded lsd-flex lsd-flex-col lsd-flex-items-stretch lsd-gap-4" id="lsd_settings_ai_profiles_<?php echo esc_attr($i); ?>">
                                        <div class="lsd-flex lsd-flex-row">
                                            <h4 class="lsd-admin-title"><?php echo esc_html($profile['name'] ?? 'N/A'); ?></h4>
                                            <?php echo LSD_Form::hidden([
                                                'name' => 'lsd[profiles]['.esc_attr($i).'][id]',
                                                'value' => $profile['id'] ?? LSD_Base::str_random(10),
                                            ]); ?>
                                            <div class="lsd-ai-remove-profile lsd-pt-2 lsd-cursor-pointer" data-i="<?php echo esc_attr($i); ?>" data-confirm="0"><i class="lsd-icon fas fa-trash-alt"></i></div>
                                        </div>
                                        <div>
                                            <?php echo LSD_Form::label([
                                                'class' => 'lsd-fields-label',
                                                'for' => 'lsd_settings_ai_profile_'.esc_attr($i).'_name',
                                                'title' => esc_html__('Descriptive Name', 'listdom'),
                                            ]); ?>
                                            <?php echo LSD_Form::text([
                                                'class' => 'lsd-admin-input',
                                                'id' => 'lsd_settings_ai_profile_'.esc_attr($i).'_name',
                                                'name' => 'lsd[profiles]['.esc_attr($i).'][name]',
                                                'value' => $profile['name'] ?? '',
                                                'placeholder' => esc_attr__('Profile Name', 'listdom'),
                                            ]); ?>
                                        </div>
                                        <div>
                                            <?php echo LSD_Form::label([
                                                'class' => 'lsd-fields-label',
                                                'for' => 'lsd_settings_ai_profile_'.esc_attr($i).'_model',
                                                'title' => esc_html__('Model', 'listdom'),
                                            ]); ?>
                                            <?php echo LSD_Form::ai_providers([
                                                'class' => 'lsd-admin-input',
                                                'id' => 'lsd_settings_ai_profile_'.esc_attr($i).'_model',
                                                'value' => $profile['model'] ?? '',
                                                'name' => 'lsd[profiles]['.esc_attr($i).'][model]',
                                            ]); ?>
                                        </div>
                                        <div>
                                            <?php echo LSD_Form::label([
                                                'class' => 'lsd-fields-label',
                                                'for' => 'lsd_settings_ai_profile_'.esc_attr($i).'_api_key',
                                                'title' => esc_html__('API Key', 'listdom'),
                                            ]); ?>
                                            <?php echo LSD_Form::text([
                                                'class' => 'lsd-admin-input',
                                                'id' => 'lsd_settings_ai_profile_'.esc_attr($i).'_api_key',
                                                'value' => $profile['api_key'] ?? '',
                                                'name' => 'lsd[profiles]['.esc_attr($i).'][api_key]',
                                                'placeholder' => esc_attr__('API Key (Required)', 'listdom'),
                                            ]); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php foreach ($module_panels as $subtab => $panel): ?>
            <div id="lsd_panel_ai_<?php echo esc_attr($subtab); ?>" class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === $subtab ? ' lsd-tab-content-active' : ''; ?>">
                <h3 class="lsd-mt-0 lsd-admin-title"><?php echo esc_html($panel['title']); ?></h3>
                <div class="lsd-settings-form-group lsd-box-white lsd-rounded">
                    <div class="lsd-settings-group-wrapper">
                        <div class="lsd-settings-fields-wrapper">
                            <div class="lsd-admin-section-heading">
                                <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Manage who can use this AI feature in Listdom.', 'listdom'); ?></p>
                            </div>

                            <div class="lsd-alert-no-my">
                                <div class="lsd-alert lsd-info lsd-my-0">
                                    <?php echo esc_html($panel['description']); ?>
                                </div>
                            </div>

                            <?php if (!$has_profiles): ?>
                                <div class="lsd-alert-no-my lsd-mt-4">
                                    <div class="lsd-alert lsd-warning lsd-my-0">
                                        <?php esc_html_e('Create at least one AI profile in Profiles before using this feature.', 'listdom'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="lsd-form-row lsd-mt-4">
                                <div class="lsd-col-3">
                                    <?php echo LSD_Form::label([
                                        'class' => 'lsd-fields-label',
                                        'title' => esc_html__('Access', 'listdom'),
                                    ]); ?>
                                </div>
                                <div class="lsd-col-5">
                                    <?php
                                    echo LSD_Form::checkboxes([
                                        'class' => 'lsd-my-0 lsd-ai-checkboxes',
                                        'name' => 'lsd[modules][' . esc_attr($panel['module']) . '][access][]',
                                        'value' => $ai_settings['modules'][$panel['module']]['access'] ?? ['administrator'],
                                        'options' => LSD_Roles::all(),
                                    ]);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div id="lsd_panel_ai_semantic-search" class="lsd-settings-form-group lsd-tab-content<?php echo $this->subtab === 'semantic-search' ? ' lsd-tab-content-active' : ''; ?>">
            <h3 class="lsd-mt-0 lsd-admin-title"><?php esc_html_e('Semantic Search', 'listdom'); ?></h3>
            <div class="lsd-settings-form-group lsd-box-white lsd-rounded">
                <div class="lsd-settings-group-wrapper">
                    <div class="lsd-settings-fields-wrapper">
                        <div class="lsd-admin-section-heading">
                            <h3 class="lsd-admin-title"><?php esc_html_e('Configuration', 'listdom'); ?></h3>
                            <p class="lsd-admin-description lsd-m-0"><?php esc_html_e('Set up meaning-based search data for Listdom. This can be used by Advanced Portal Search (APS) add-on and other tools such as a chatbot.', 'listdom'); ?></p>
                        </div>

                        <div class="lsd-alert-no-my">
                            <div class="lsd-alert lsd-info lsd-my-0">
                                <?php esc_html_e('Semantic Search helps Listdom understand what each listing is about, so searches can match by meaning instead of only exact words.', 'listdom'); ?>
                            </div>
                        </div>

                        <div class="lsd-form-row">
                            <div class="lsd-col-3">
                                <?php echo LSD_Form::label([
                                    'title' => esc_html__('Status', 'listdom'),
                                    'for' => 'lsd_ai_semantic_search_enabled',
                                ]); ?>
                            </div>
                            <div class="lsd-col-9">
                                <?php echo LSD_Form::switcher([
                                    'id' => 'lsd_ai_semantic_search_enabled',
                                    'name' => 'lsd[semantic_search][enabled]',
                                    'value' => $semantic_settings['enabled'] ?? 0,
                                    'toggle' => '#lsd_ai_semantic_search_options',
                                ]); ?>
                                <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Turn this on when you want smarter, meaning-based matching for search and suggestions.', 'listdom'); ?></p>
                            </div>
                        </div>

                        <div id="lsd_ai_semantic_search_options" class="<?php echo !($semantic_settings['enabled'] ?? 0) ? 'lsd-util-hide' : ''; ?>">
                            <?php if (!count($semantic_profiles)): ?>
                                <div class="lsd-alert-no-my lsd-mb-4">
                                    <div class="lsd-alert lsd-warning lsd-my-0">
                                        <?php esc_html_e('Before you can use Semantic Search, please add a compatible AI profile such as OpenAI or Gemini.', 'listdom'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="lsd-form-row">
                                <div class="lsd-col-3">
                                    <?php echo LSD_Form::label([
                                        'title' => esc_html__('Semantic AI Profile', 'listdom'),
                                        'for' => 'lsd_ai_semantic_search_profile_id',
                                    ]); ?>
                                </div>
                                <div class="lsd-col-9">
                                    <?php echo LSD_Form::select([
                                        'id' => 'lsd_ai_semantic_search_profile_id',
                                        'name' => 'lsd[semantic_search][profile_id]',
                                        'value' => $semantic_settings['profile_id'] ?? '',
                                        'class' => 'lsd-admin-input',
                                        'options' => $semantic_profiles,
                                        'show_empty' => true,
                                        'empty_label' => count($semantic_profiles) ? esc_html__('Select a semantic AI profile', 'listdom') : esc_html__('No embedding-capable AI profiles available', 'listdom'),
                                        'attributes' => !count($semantic_profiles) ? ['disabled' => 'disabled'] : [],
                                    ]); ?>
                                    <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Choose the AI profile that should understand your listings and search intent by meaning.', 'listdom'); ?></p>
                                </div>
                            </div>

                            <div class="lsd-form-row lsd-mt-4">
                                <div class="lsd-col-3">
                                    <?php echo LSD_Form::label([
                                        'title' => esc_html__('Minimum Match Score', 'listdom'),
                                        'for' => 'lsd_ai_semantic_search_min_score',
                                    ]); ?>
                                </div>
                                <div class="lsd-col-9">
                                    <?php echo LSD_Form::number([
                                        'id' => 'lsd_ai_semantic_search_min_score',
                                        'name' => 'lsd[semantic_search][min_score]',
                                        'value' => $semantic->min_score(),
                                        'class' => 'lsd-admin-input',
                                        'attributes' => [
                                            'min' => '0.30',
                                            'max' => '0.90',
                                            'step' => '0.01',
                                        ],
                                    ]); ?>
                                    <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Use a higher number for tighter matches and a lower number for broader matches. The default value is 0.45.', 'listdom'); ?></p>
                                </div>
                            </div>

                            <div class="lsd-form-row lsd-mt-4">
                                <div class="lsd-col-3">
                                    <?php echo LSD_Form::label([
                                        'title' => esc_html__('Custom Fields', 'listdom'),
                                    ]); ?>
                                </div>
                                <div class="lsd-col-9">
                                    <?php if (count($semantic_attributes)): ?>
                                        <?php echo LSD_Form::checkboxes([
                                            'class' => 'lsd-my-0 lsd-ai-checkboxes',
                                            'name' => 'lsd[semantic_search][attributes][]',
                                            'options' => $semantic_attributes,
                                            'value' => $semantic_settings['attributes'] ?? [],
                                        ]); ?>
                                    <?php else: ?>
                                        <p class="lsd-admin-description-tiny lsd-my-0"><?php esc_html_e('No Listdom attributes are available yet.', 'listdom'); ?></p>
                                    <?php endif; ?>
                                    <p class="lsd-admin-description-tiny lsd-mt-3 lsd-mb-0"><?php esc_html_e('Choose any extra fields that help describe your listings more clearly. You can leave fields unchecked unless they add useful meaning.', 'listdom'); ?></p>
                                </div>
                            </div>

                            <div class="lsd-form-row lsd-mt-4">
                                <div class="lsd-col-3">
                                    <?php echo LSD_Form::label([
                                        'title' => esc_html__('Reindex', 'listdom'),
                                        'for' => 'lsd_ai_semantic_search_reindex',
                                    ]); ?>
                                </div>
                                <div class="lsd-col-9">
                                    <button type="button" class="lsd-secondary-button" id="lsd_ai_semantic_search_reindex" data-nonce="<?php echo esc_attr(wp_create_nonce('lsd_ai_semantic_reindex')); ?>" <?php echo !$semantic->is_ready() ? 'disabled="disabled"' : ''; ?>><?php esc_html_e('Queue Full Reindex', 'listdom'); ?></button>
                                    <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Use this after changing the AI profile or selected fields so Listdom can refresh this search data in the background.', 'listdom'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lsd-spacer-30"></div>
        <div class="lsd-form-row lsd-settings-submit-wrapper">
            <div class="lsd-col-12 lsd-flex lsd-flex-content-end">
                <?php LSD_Form::nonce('lsd_ai_form'); ?>
                <button type="submit" id="lsd_ai_save_button" class="lsd-primary-button">
                    <?php esc_html_e('Save The Changes', 'listdom'); ?>
                    <i class='wbli wbli-checkmark-circle'></i>
                </button>
            </div>
        </div>
    </form>
</div>
<script>
jQuery('#lsd_settings_ai_add_profile').on('click', function ()
{
    const $button = jQuery(this);

    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( esc_html__('Adding', 'listdom') ); ?>");

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_ai_add_profile&_wpnonce=<?php echo wp_create_nonce('lsd_ai_add_profile'); ?>",
        dataType: "json",
        success: function(response)
        {
            loading.stop();

            if(response.success === 1) location.reload();
        },
        error: function()
        {
            loading.stop();
        }
    });
});

jQuery('.lsd-ai-remove-profile').on('click', function()
{
    const $button = jQuery(this);

    const i = $button.data('i');
    const confirm = $button.data('confirm');

    if(!confirm)
    {
        $button.data('confirm', 1);
        $button.addClass('lsd-need-confirm');

        setTimeout(function()
        {
            $button.data('confirm', 0);
            $button.removeClass('lsd-need-confirm');
        }, 5000);

        return false;
    }

    const loading = new ListdomButtonLoader($button);
    loading.start("");

    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_ai_remove_profile&_wpnonce=<?php echo wp_create_nonce('lsd_ai_remove_profile'); ?>&i="+i,
        dataType: "json",
        success: function(response)
        {
            if(response.success === 1)
            {
                jQuery('#lsd_settings_ai_profiles_'+i).remove();
                loading.stop();
            }
        },
        error: function()
        {
            loading.stop();
        }
    });
});

jQuery('#lsd_ai_semantic_search_reindex').on('click', function (event)
{
    event.preventDefault();

    const $button = jQuery(this);
    if ($button.is(':disabled')) return;

    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( esc_html__('Queueing', 'listdom') ); ?>");

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
            action: 'lsd_ai_semantic_reindex',
            _wpnonce: $button.data('nonce')
        },
        success: function (response)
        {
            if (response && Number(response.success) === 1) listdom_toastify(response.message, 'lsd-success');
            else if (response && response.message) listdom_toastify(response.message, 'lsd-error');
            loading.stop();
        },
        error: function ()
        {
            listdom_toastify("<?php echo esc_js(esc_html__('Unable to queue semantic reindexing right now.', 'listdom')); ?>", 'lsd-error');
            loading.stop();
        }
    });
});

jQuery('#lsd_ai_form').on('submit', function (event)
{
    event.preventDefault();

    const $button = jQuery('#lsd_ai_save_button');
    const $tab = jQuery('.lsd-nav-tab-active');

    const loading = new ListdomButtonLoader($button);
    loading.start("<?php echo esc_js( esc_html__('Saving', 'listdom') ); ?>");

    const settings = jQuery(this).serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=lsd_save_ai&" + settings,
        success: function ()
        {
            $tab.attr('data-saved', 'true');

            listdom_toastify("<?php echo esc_js(esc_html__('Options saved successfully.', 'listdom')); ?>", 'lsd-success');
            loading.stop();
        },
        error: function ()
        {
            $tab.attr('data-saved', 'false');

            listdom_toastify("<?php echo esc_js(esc_html__('Error: Unable to save options.', 'listdom')); ?>", 'lsd-error');
            loading.stop();
        }
    });
});
</script>
