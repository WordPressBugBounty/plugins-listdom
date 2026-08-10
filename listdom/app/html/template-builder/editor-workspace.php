<?php
/**
 * @var LSD_PTypes_Template $this
 */

global $post;

$sidebars     = $this->sidebar_menus();
$types        = $this->template_types();
$current_type = get_post_meta($post->ID, '_lsd_template_type', true) ?: $this->default_template_type();

if (!isset($types[$current_type])) $current_type = $this->default_template_type();

$visible_types = $types;

$placeholder   = esc_html__('Template name', 'listdom');
$raw_title     = get_the_title($post);

if ($post->post_status === 'auto-draft') $raw_title = '';

$raw_title     = is_string($raw_title) ? $raw_title : '';
$raw_title     = trim(wp_strip_all_tags($raw_title));
$display_title = $raw_title !== '' ? $raw_title : $placeholder;

$post_status = $post->post_status;
$post_status_label   = $this->status_label($post->post_status);
$template_layout_data = $this->get_editor_template_layout_data((int) $post->ID);
$status_options    = $this->get_status_options($post->post_status, $post_status_label);
$can_publish_templates = $this->current_user_can_publish_templates();
$is_publish_like_status = $this->is_publish_like_status($post->post_status);
$show_save_draft_action = !$is_publish_like_status || $can_publish_templates;
$show_publish_action = $can_publish_templates || $is_publish_like_status;
$preserve_pending_status = !$can_publish_templates && $post->post_status === 'pending';
$save_button_label = $preserve_pending_status ? esc_html__('Update', 'listdom') : esc_html__('Save Draft', 'listdom');
$save_button_toast = $preserve_pending_status ? esc_attr__('Template updated successfully.', 'listdom') : esc_attr__('Template saved as Draft successfully.', 'listdom');
$publish_button_label = $is_publish_like_status ? esc_html__('Update', 'listdom') : esc_html__('Publish', 'listdom');
$page_template_slug = get_page_template_slug($post);
$page_template_slug = is_string($page_template_slug) && $page_template_slug !== '' ? $page_template_slug : 'default';
$page_templates = $this->page_templates();

$listing_counts = (array) wp_count_posts(LSD_Base::PTYPE_LISTING);
unset($listing_counts['auto-draft']);
$number_of_listings = array_sum($listing_counts);
$preview_listing_id = (int) get_post_meta($post->ID, '_lsd_template_preview_listing', true);
if (!$preview_listing_id) $preview_listing_id = $this->get_preview_listing_id($post->ID);
$preview_iframe_url = $this->get_preview_iframe_url((int) $post->ID, $preview_listing_id);
?>

<div class="lsd-template-editor-workspace" data-lsd-template-id="<?php echo esc_attr($post->ID); ?>"
     data-lsd-can-publish="<?php echo esc_attr($can_publish_templates ? '1' : '0'); ?>"
     data-lsd-template-type-mismatch="<?php echo esc_attr__('The ({element}) isn\'t supported in ({template_type}) template type.', 'listdom'); ?>"
     data-lsd-unsaved-message="<?php echo esc_attr__('You have unsaved changes. Do you want to leave without saving? ', 'listdom'); ?>"
     data-lsd-unsaved-confirm-text="<?php echo esc_attr__('Leave Now', 'listdom'); ?>"
     data-lsd-unsaved-cancel-text="<?php echo esc_attr__('Cancel', 'listdom'); ?>">
    <div class="lsd-template-editor-workspace__topbar">
        <div class="lsd-template-editor-topbar__inner">
            <div class="lsd-template-editor-topbar__logo_section">
                <div class="lsd-template-card-dropdown">
                    <a href="" class="lsd-dropdown-toggle" aria-label="Toggle dropdown" aria-expanded="false" aria-haspopup="true">
                        <img class="lsd-logo" alt="<?php echo esc_attr__('logo', 'listdom') ?>" src="<?php echo esc_url($this->lsd_asset_url('img/listdom-logo.svg')); ?>">
                    </a>
                    <ul class="lsd-dropdown-menu" role="menu" aria-hidden="true">
                        <li role="menuitem"><a href="<?php echo esc_url(admin_url('edit.php?post_type=' . LSD_Base::PTYPE_TEMPLATE)); ?>" data-lsd-template-exit="1"><?php echo esc_html__('Exit', 'listdom'); ?></a></li>
                    </ul>
                </div>

                <div class="lsd-template-editor-topbar-title-section">
                    <span><?php esc_html_e('Listdom Template Builder', 'listdom'); ?></span>
                    <div class="lsd-template-editor-topbar__title" data-placeholder="<?php echo esc_attr($placeholder); ?>">
                        <h4 class="lsd-admin-title"><?php echo esc_html($display_title); ?></h4>
                    </div>
                </div>
            </div>
            <div class="lsd-template-editor-topbar__actions">
                <button type="button" class="lsd-text-button lsd-template-editor-add-toggle" data-lsd-template-action="add" aria-pressed="false">
                    <i class="listdom-icon wbli-add-plus-circle" aria-hidden="true"></i>
                </button>
                <button type="button" class="lsd-text-button lsd-template-editor-structure-toggle" data-lsd-template-action="structure" aria-pressed="false">
                    <i class="listdom-icon wbli-structure-check" aria-hidden="true"></i>
                </button>
                <button type="button" class="lsd-text-button lsd-template-editor-settings-toggle" data-lsd-template-action="settings" aria-pressed="false">
                    <i class="listdom-icon wbli-settings" aria-hidden="true"></i>
                </button>
                <?php if ($show_save_draft_action): ?>
                    <button type="button" class="lsd-secondary-button" data-lsd-template-action="save"
                            data-lsd-label-draft="<?php echo esc_attr__('Save Draft', 'listdom'); ?>"
                            data-lsd-label-pending="<?php echo esc_attr__('Update', 'listdom'); ?>"
                            data-lsd-toast="<?php echo $save_button_toast; ?>"
                            data-lsd-toast-draft="<?php echo esc_attr__('Template saved as Draft successfully.', 'listdom'); ?>"
                            data-lsd-toast-pending="<?php echo esc_attr__('Template updated successfully.', 'listdom'); ?>"
                            data-lsd-toast-update="<?php echo esc_attr__('Template updated successfully.', 'listdom'); ?>">
                        <i class="listdom-icon wbli-floppy-disk" aria-hidden="true"></i>
                        <span data-lsd-button-label><?php echo esc_html($save_button_label); ?></span>
                    </button>
                <?php endif; ?>

                <?php if ($show_publish_action): ?>
                    <button type="button" class="lsd-primary-button" data-lsd-template-action="publish"
                            data-lsd-label-publish="<?php echo esc_attr__('Publish', 'listdom'); ?>"
                            data-lsd-label-update="<?php echo esc_attr__('Update', 'listdom'); ?>"
                            data-lsd-toast="<?php echo esc_attr__('Template published successfully.', 'listdom'); ?>"
                            data-lsd-toast-update="<?php echo esc_attr__('Template updated successfully.', 'listdom'); ?>">
                        <i class="listdom-icon wbli-transition-top" aria-hidden="true"></i>
                        <span data-lsd-button-label><?php echo esc_html($publish_button_label); ?></span>
                    </button>
                <?php endif; ?>

            </div>
        </div>

        <?php wp_nonce_field('lsd-template-type', 'lsd-template-type-nonce'); ?>
    </div>

    <div class="lsd-template-editor-workspace__content">
        <div class="lsd-template-editor-workspace__toolbar">
            <nav class="lsd-template-editor-menu lsd-template-editor-menu--left" aria-label="<?php esc_attr_e('Template elements', 'listdom'); ?>" data-lsd-template-editor-menu="left-default">
                <div class="lsd-template-editor-selection lsd-util-hide" data-lsd-template-selection>
                    <span class="lsd-template-editor-selection__icon" aria-hidden="true">
                        <i class="listdom-icon wbli-add-plus-circle"></i>
                    </span>
                    <span class="lsd-template-editor-selection__label" data-lsd-template-selection-label></span>
                </div>
                <ul class="lsd-tab-switcher lsd-sub-tabs lsd-level-3-menu" data-for=".lsd-template-editor-sidebar-panel">
                    <?php foreach ($sidebars['elements'] as $index => $item): ?>
                        <li data-tab="<?php echo esc_attr($index); ?>" class="<?php echo $index === 'elements' ? 'lsd-sub-tabs-active' : ''; ?>">
                            <a href="#" role="tab">
                                <i class="listdom-icon wbli-add-plus-circle lsd-template-editor-tab__icon" aria-hidden="true"></i>
                                <span class="lsd-template-editor-tab__label"><?php echo esc_html($item); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li data-tab="general" class="lsd-util-hide">
                        <a href="#" role="tab" tabindex="-1">
                            <i class="listdom-icon wbli-structure-check lsd-template-editor-tab__icon" aria-hidden="true"></i>
                            <span class="lsd-template-editor-tab__label"><?php esc_html_e('General', 'listdom'); ?></span>
                        </a>
                    </li>
                    <li data-tab="layout" class="lsd-util-hide">
                        <a href="#" role="tab" tabindex="-1">
                            <i class="fa fa-columns lsd-template-editor-tab__icon" aria-hidden="true"></i>
                            <span class="lsd-template-editor-tab__label"><?php esc_html_e('Layout', 'listdom'); ?></span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="lsd-template-editor-workspace__responsive">
                <ul class="lsd-template-editor-workspace__responsive_controls lsd-tab-switcher lsd-level-5-menu lsd-sub-tabs" data-lsd-template-responsive>
                    <li data-lsd-responsive="desktop" class="lsd-sub-tabs-active lsd-tooltip lsd-tooltip-bottom" data-lsd-tooltip="<?php echo esc_attr__('Desktop', 'listdom'); ?>">
                        <a href="#" aria-label="<?php echo esc_attr__('Desktop', 'listdom'); ?>" title="<?php echo esc_attr__('Desktop', 'listdom'); ?>">
                            <i class="listdom-icon wbli-screen"></i>
                        </a>
                    </li>
                    <li data-lsd-responsive="tablet" class="lsd-tooltip lsd-tooltip-bottom" data-lsd-tooltip="<?php echo esc_attr__('Tablet', 'listdom'); ?>">
                        <a href="#" aria-label="<?php echo esc_attr__('Tablet', 'listdom'); ?>" title="<?php echo esc_attr__('Tablet', 'listdom'); ?>">
                            <i class="listdom-icon wbli-tablet"></i>
                        </a>
                    </li>
                    <li data-lsd-responsive="mobile" class="lsd-tooltip lsd-tooltip-bottom" data-lsd-tooltip="<?php echo esc_attr__('Mobile', 'listdom'); ?>">
                        <a href="#" aria-label="<?php echo esc_attr__('Mobile', 'listdom'); ?>" title="<?php echo esc_attr__('Mobile', 'listdom'); ?>">
                            <i class="listdom-icon wbli-mobile"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="lsd-template-editor-workspace__inner">
            <aside class="lsd-template-editor-sidebar lsd-template-editor-sidebar--left" data-lsd-template-editor-sidebar="left">
                <?php echo LSD_Form::nonce('lsd-template-element-settings', 'lsd-template-element-settings'); ?>
                <nav class="lsd-template-editor-menu lsd-template-editor-menu--options lsd-util-hide" aria-label="<?php esc_attr_e('Template element settings', 'listdom'); ?>" data-lsd-template-editor-menu="left-options">
                    <ul class="lsd-tab-switcher lsd-sub-tabs lsd-level-3-menu" data-for=".lsd-template-editor-sidebar-panel">
                        <?php foreach ($sidebars['options'] as $index => $item): ?>
                            <li data-tab="<?php echo esc_attr($index); ?>">
                                <a href="#" role="tab">
                                    <span class="lsd-template-editor-tab__label"><?php echo esc_html($item); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
                <div class="lsd-tab-switcher-content lsd-template-editor-sidebar-panel lsd-tab-switcher-content-active" id="lsd-tab-switcher-elements-content" data-lsd-template-editor-panel="default">
                    <div class="lsd-template-editor-sidebar__content">
                        <div class="lsd-template-editor-element-search">
                            <label class="screen-reader-text" for="lsd-template-editor-element-search"><?php esc_html_e('Search elements', 'listdom'); ?></label>
                            <input type="text" id="lsd-template-editor-element-search" class="lsd-admin-input lsd-template-editor-element-search__input" placeholder="<?php esc_attr_e('Search elements...', 'listdom'); ?>" autocomplete="off">
                        </div>
                        <div class="lsd-template-editor-sidebar__list" data-lsd-template-elements-list>
                            <?php
                            echo $this->include_html_file('template-builder/elements-list.php', [
                                'parameters' => [
                                    'current_type' => $current_type,
                                ],
                                'return_output' => true,
                            ]);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="lsd-tab-switcher-content lsd-template-editor-sidebar-panel" id="lsd-tab-switcher-general-content">
                    <div class="lsd-template-editor-sidebar__content lsd-template-editor-sidebar__content--settings">
                        <div class="lsd-alert lsd-warning lsd-util-hide" data-lsd-template-settings-alert>
                            <?php esc_html_e('Settings have changed. Save to apply them.', 'listdom'); ?>
                        </div>
                        <section class="lsd-template-editor-section">
                            <div class="lsd-template-editor-settings">
                                <div class="lsd-template-editor-setting-field">
                                    <label for="lsd-template-settings-name" class="lsd-fields-label-tiny"><?php esc_html_e('Template Name', 'listdom'); ?></label>
                                    <input type="text" id="lsd-template-settings-name" class="lsd-admin-input" value="<?php echo esc_attr($display_title); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" data-lsd-template-setting="title">
                                </div>
                                <div class="lsd-template-editor-setting-field">
                                    <label for="lsd-template-settings-type" class="lsd-fields-label-tiny"><?php esc_html_e('Template Type', 'listdom'); ?></label>
                                    <select name="lsd_template_type" class="lsd-admin-input" id="lsd-template-settings-type" data-lsd-template-setting="type">
                                        <?php foreach ($visible_types as $type => $info): ?>
                                            <option value="<?php echo esc_attr($type); ?>" <?php selected($current_type, $type); ?>>
                                                <?php echo esc_html($info['label']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="lsd-template-editor-setting-field">
                                    <label for="lsd-template-settings-status" class="lsd-fields-label-tiny"><?php esc_html_e('Post Status', 'listdom'); ?></label>
                                    <?php if (!empty($status_options)): ?>
                                        <?php $current_status = $post_status ?: 'draft'; ?>
                                        <select id="lsd-template-settings-status" class="lsd-admin-input" data-lsd-template-setting="status" data-current-status="<?php echo esc_attr($current_status); ?>">
                                            <?php foreach ($status_options as $status => $label): ?>
                                                <option value="<?php echo esc_attr($status); ?>" <?php selected($current_status, $status); ?>>
                                                    <?php echo esc_html($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <h4 class="lsd-admin-subtitle-tiny">
                                            <?php echo $post_status_label ?: esc_html__('Draft', 'listdom'); ?>
                                        </h4>
                                    <?php endif; ?>
                                </div>
                                <div class="lsd-template-editor-setting-field">
                                    <label for="lsd-template-settings-autosave" class="lsd-fields-label-tiny"><?php esc_html_e('Auto Save', 'listdom'); ?></label>
                                    <div class="lsd-template-editor-setting-field__control">
                                        <label class="lsd-switch">
                                            <input type="hidden" name="lsd_template_autosave_status" value="0">
                                            <input type="checkbox" id="lsd-template-settings-autosave" name="lsd_template_autosave_status" value="1" data-lsd-template-setting="autosave" <?php checked($this->autosave_enabled()); ?>>
                                            <span class="lsd-slider"></span>
                                        </label>
                                        <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Disable to turn off auto-save within the Listdom Template Builder.', 'listdom'); ?></p>
                                    </div>
                                </div>
                                <div class="lsd-template-editor-setting-field">
                                    <label for="lsd-template-settings-preview-listing" class="lsd-fields-label-tiny"><?php esc_html_e('Preview Listing', 'listdom'); ?></label>
                                    <div class="lsd-template-editor-setting-field__control">
                                        <?php echo LSD_Form::autosuggest([
                                            'source' => LSD_Base::PTYPE_LISTING,
                                            'name' => 'lsd_template_preview_listing',
                                            'id' => 'lsd-template-settings-preview-listing',
                                            'input_id' => 'lsd_template_settings_preview_listing_input',
                                            'suggestions' => 'lsd_template_settings_preview_listing_suggestions',
                                            'values' => $preview_listing_id ? [$preview_listing_id] : [],
                                            'max_items' => 1,
                                            'placeholder' => esc_attr__("Search listings to power the preview ...", 'listdom'),
                                            'description' => esc_html__('Choose which listing data is used to render the live preview.', 'listdom'),
                                            'description_class' => 'lsd-mb-0 lsd-mt-2',
                                        ]); ?>
                                    </div>
                                </div>
                                <?php echo LSD_Form::nonce('lsd-save-template-settings', 'lsd-save-template-settings'); ?>
                            </div>
                        </section>
                        <div class="lsd-template-editor-settings__footer">
                            <button type="button" class="lsd-primary-button lsd-template-editor-settings__save" data-lsd-template-action="settings-save"
                                    data-lsd-settings-toast="<?php esc_html_e('Settings saved successfully.', 'listdom'); ?>"
                                    data-lsd-settings-error-toast="<?php esc_html_e('Unable to save settings.', 'listdom'); ?>">
                                <i class="listdom-icon wbli-floppy-disk" aria-hidden="true"></i>
                                <?php esc_html_e('Save & Apply', 'listdom'); ?>
                            </button>
                            <button type="button" class="lsd-text-button lsd-template-editor-settings__back" data-lsd-template-action="settings-back">
                                <i class="listdom-icon wbli-move-left" aria-hidden="true"></i>
                                <?php esc_html_e('Back', 'listdom'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="lsd-tab-switcher-content lsd-template-editor-sidebar-panel" id="lsd-tab-switcher-layout-content">
                    <div class="lsd-template-editor-sidebar__content lsd-template-editor-sidebar__content--settings">
                        <div class="lsd-alert lsd-warning lsd-util-hide" data-lsd-template-settings-alert>
                            <?php esc_html_e('Settings have changed. Save to apply them.', 'listdom'); ?>
                        </div>
                        <section class="lsd-template-editor-section">
                            <div class="lsd-template-editor-settings">
                                <div class="lsd-template-editor-setting-field">
                                    <label for="lsd-template-settings-page-template" class="lsd-fields-label-tiny"><?php esc_html_e('Page Template', 'listdom'); ?></label>
                                    <select id="lsd-template-settings-page-template" name="lsd_template_page_template" class="lsd-admin-input" data-lsd-template-setting="page-template">
                                        <?php foreach ($page_templates as $template_file => $template_label): ?>
                                            <option value="<?php echo esc_attr($template_file); ?>" <?php selected($page_template_slug, $template_file); ?>>
                                                <?php echo esc_html($template_label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="lsd-admin-description-tiny lsd-mt-2 lsd-mb-0"><?php esc_html_e('Choose which theme template renders this template builder post.', 'listdom'); ?></p>
                                </div>
                            </div>
                        </section>
                        <div class="lsd-template-editor-settings__footer">
                            <button type="button" class="lsd-primary-button lsd-template-editor-settings__save" data-lsd-template-action="settings-save"
                                    data-lsd-settings-toast="<?php esc_html_e('Settings saved successfully.', 'listdom'); ?>"
                                    data-lsd-settings-error-toast="<?php esc_html_e('Unable to save settings.', 'listdom'); ?>">
                                <i class="listdom-icon wbli-floppy-disk" aria-hidden="true"></i>
                                <?php esc_html_e('Save & Apply', 'listdom'); ?>
                            </button>
                            <button type="button" class="lsd-text-button lsd-template-editor-settings__back" data-lsd-template-action="settings-back">
                                <i class="listdom-icon wbli-move-left" aria-hidden="true"></i>
                                <?php esc_html_e('Back', 'listdom'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="lsd-tab-switcher-content lsd-template-editor-sidebar-panel" id="lsd-tab-switcher-content-content" data-lsd-settings-scope="content">
                    <div class="lsd-template-editor-settings-panel lsd-template-editor-settings-panel--empty">
                        <div class="lsd-template-editor-sidebar__placeholder" data-empty-text="<?php echo esc_attr__('This element has no settings.', 'listdom'); ?>">
                            <span><i class="listdom-icon wbli-add-plus-circle" aria-hidden="true"></i></span>
                            <p class="lsd-admin-description"><?php esc_html_e('Select an element to edit its properties', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="lsd-tab-switcher-content lsd-template-editor-sidebar-panel" id="lsd-tab-switcher-style-content" data-lsd-settings-scope="style">
                    <div class="lsd-template-editor-settings-panel lsd-template-editor-settings-panel--empty">
                        <div class="lsd-template-editor-sidebar__placeholder" data-empty-text="<?php echo esc_attr__('This element has no settings.', 'listdom'); ?>">
                            <span><i class="listdom-icon wbli-add-plus-circle" aria-hidden="true"></i></span>
                            <p class="lsd-admin-description"><?php esc_html_e('Select an element to edit its properties', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="lsd-tab-switcher-content lsd-template-editor-sidebar-panel" id="lsd-tab-switcher-advanced-content" data-lsd-settings-scope="advanced">
                    <div class="lsd-template-editor-settings-panel lsd-template-editor-settings-panel--empty">
                        <div class="lsd-template-editor-sidebar__placeholder" data-empty-text="<?php echo esc_attr__('This element has no settings.', 'listdom'); ?>">
                            <span><i class="listdom-icon wbli-add-plus-circle" aria-hidden="true"></i></span>
                            <p class="lsd-admin-description"><?php esc_html_e('Select an element to edit its properties', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>
            </aside>
            <button type="button" class="lsd-template-editor-sidebar-toggle" data-lsd-template-action="sidebar-toggle" aria-label="<?php esc_attr_e('Toggle sidebar', 'listdom'); ?>" aria-pressed="false">
                <i class="fa-solid fa-angle-left" aria-hidden="true"></i>
            </button>

            <div class="lsd-template-editor-structure-floating lsd-tab-switcher-content lsd-tab-switcher-content-elements-structure" id="lsd-tab-switcher-structure-content" data-lsd-template-editor-panel="default">
                <div class="lsd-template-editor-structure-floating__header lsd-template-editor-structure-drag-handle">
                    <span class="lsd-template-editor-structure-floating__title"><?php esc_html_e('Structure', 'listdom'); ?></span>
                    <button type="button" class="lsd-template-editor-structure-floating__close" data-lsd-structure-close aria-label="<?php echo esc_attr__('Close structure', 'listdom'); ?>">
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="lsd-template-editor-structure-floating__body">
                    <div class="lsd-template-editor-sidebar__content">
                        <div class="lsd-template-editor-structure" data-lsd-template-editor-structure>
                            <div class="lsd-template-editor-structure__tree" data-lsd-structure-tree></div>
                            <div class="lsd-template-editor-sidebar__placeholder" data-lsd-structure-placeholder>
                                <span><i class="listdom-icon wbli-add-plus-circle" aria-hidden="true"></i></span>
                                <p class="lsd-admin-description"><?php esc_html_e('Add containers and elements to see the structure here.', 'listdom'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Canvas -->
            <div class="lsd-template-editor-canvas"
                 data-lsd-template-editor-canvas
                 data-lsd-template-error="<?php echo esc_html__('Unable to render the selected element right now.', 'listdom'); ?>"
                 data-lsd-preview-iframe-url="<?php echo esc_url($preview_iframe_url); ?>"
                 data-lsd-preview-template-id="<?php echo esc_attr((int) $post->ID); ?>">
                <?php echo LSD_Form::nonce('lsd-template-element', 'lsd-template-element'); ?>
                <div class="lsd-template-editor-canvas__preview-frame" data-lsd-template-preview-frame>
                    <div class="lsd-template-editor-canvas__preview-loading is-active" data-lsd-template-preview-loading aria-hidden="false">
                        <div class="lsd-template-editor-canvas__preview-loading-spinner" aria-hidden="true"></div>
                        <span class="lsd-template-editor-canvas__preview-loading-label"><?php esc_html_e('Loading preview...', 'listdom'); ?></span>
                    </div>
                    <iframe
                        class="lsd-template-editor-canvas__preview-iframe"
                        data-lsd-template-preview-iframe
                        src="<?php echo esc_url($preview_iframe_url); ?>"
                        title="<?php esc_attr_e('Template frontend preview', 'listdom'); ?>"
                        loading="lazy"></iframe>
                </div>
                <div class="lsd-template-editor-canvas__templates lsd-util-hide" aria-hidden="true" data-lsd-canvas-template-source>
                    <div class="lsd-template-editor-canvas__placeholder" data-lsd-canvas-template="placeholder">
                        <span><i class="listdom-icon wbli-add-plus-circle" aria-hidden="true"></i></span>
                        <p class="lsd-admin-description"><?php esc_html_e('Drag Elements here', 'listdom'); ?></p>
                    </div>
                    <div data-lsd-canvas-template="controls">
                        <?php include lsd_template('template-builder/controls.php'); ?>
                    </div>
                    <div data-lsd-canvas-template="container">
                        <div class="lsd-template-editor-canvas__container-item">
                            <?php include lsd_template('template-builder/controls.php'); ?>
                            <div class="lsd-template-editor-canvas__container">
                                <div class="lsd-template-editor-canvas__container-inner">
                                    <div class="lsd-template-editor-canvas__container-placeholder">
                                        <span><i class="listdom-icon wbli-add-plus-circle" aria-hidden="true"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div data-lsd-canvas-template="element-wrapper">
                        <div class="lsd-template-editor-canvas__element-block">
                            <?php include lsd_template('template-builder/controls.php'); ?>
                            <div class="lsd-template-editor-canvas__element-wrapper" data-lsd-element-type="" data-lsd-element-id="">
                                <div class="lsd-template-editor-canvas__content"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="lsd-template-layout" name="lsd_template_layout" data-lsd-template-layout value="<?php echo isset($template_layout_data) ? esc_attr($template_layout_data) : ''; ?>">
        </div>
    </div>

    <div class="lsd-template-editor-workspace__footer">
        <div class="lsd-template-editor-last-saved lsd-admin-subtitle-tiny" data-default-text="<?php echo esc_attr__('Last Saved', 'listdom'); ?>">
            <?php echo $this->last_saved_text($post); ?>
        </div>
        <div class="lsd-template-editor-auto-save-status
            <?php echo !$this->autosave_enabled() ? 'lsd-template-editor-auto-save-status--disabled' : ''; ?>"
             data-lsd-autosave-status="<?php echo esc_attr($this->autosave_enabled() ? 1 : 0); ?>"
             data-default-text="<?php echo $this->autosave_enabled() ? esc_html__('Auto Save Active', 'listdom') : esc_html__('Auto Save Not Active', 'listdom'); ?>"
             data-lsd-autosave-active-text="<?php echo esc_html__('Auto Save Active', 'listdom'); ?>"
             data-lsd-autosave-inactive-text="<?php echo esc_html__('Auto Save Not Active', 'listdom'); ?>"
             data-lsd-autosave-link="1"
             role="button"
             tabindex="0"
             aria-label="<?php esc_attr_e('Edit Auto Save Settings', 'listdom'); ?>">
            <span class="lsd-circle"></span>
            <span class="lsd-admin-subtitle-tiny"><?php echo $this->autosave_enabled() ? esc_html__('Auto Save Active', 'listdom') : esc_html__('Auto Save Not Active', 'listdom'); ?></span>
        </div>
    </div>
</div>
