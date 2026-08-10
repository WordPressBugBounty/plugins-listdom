<?php
/**
 * @var LSD_PTypes_Template $this
 * @var bool $has_templates
 * @var array $templates
 * @var string $pagination
 * @var string $modal_id
 * @var bool $show_blank_screen
 * @var bool $no_results
 * @var array $statuses
 * @var string $current_status
 * @var string $search_query
 * @var string $reset_url
 * @var bool $can_create_templates
 */
?>
<div class="lsd-template-container">
    <div class="lsd-template-wrap">
        <div class="lsd-template-screen">
            <?php if ($show_blank_screen): ?>
                <div class="lsd-template-blank lsd-admin-sections">
                    <img width="77" height="77" src="<?php echo esc_url($this->lsd_asset_url('img/listdom-logo.svg')); ?>" alt="<?php esc_attr_e('Logo', 'listdom'); ?>">
                    <h3 class="lsd-blank-section-title">
                        <?php printf(__('Welcome to %s', 'listdom'),
                            '<span class="lsd-highlighted-title">Listdom Template Builder</span>'); ?>
                    </h3>
                    <div>
                        <p class="lsd-admin-description"><?php esc_html_e('The official visual template builder for Listdom directory system.', 'listdom'); ?></p>
                        <p class="lsd-admin-description"><?php esc_html_e('Create stunning, professional templates with drag-and-drop simplicity.', 'listdom'); ?></p>
                    </div>
                    <?php if ($can_create_templates): ?>
                        <a href="#" class="lsd-primary-button lsd-open-template-modal" data-modal-target="<?php echo esc_attr($modal_id); ?>">
                            <?php esc_html_e('Create Your First Template', 'listdom'); ?>
                            <i class="listdom-icon wbli-right-arrow"></i>
                        </a>
                    <?php endif; ?>

                    <div class="lsd-template-icon-box">
                        <div class="lsd-admin-icon-box">
                            <i class="listdom-icon wbli-cursor-pointer"></i>
                            <h3 class="lsd-admin-subtitle"><?php esc_html_e('Drag & Drop', 'listdom'); ?></h3>
                            <p class="lsd-admin-description"><?php esc_html_e('Intuitive builder', 'listdom'); ?></p>
                        </div>
                        <div class="lsd-admin-icon-box">
                            <i class="listdom-icon wbli-database"></i>
                            <h3 class="lsd-admin-subtitle"><?php esc_html_e('Dynamic Data', 'listdom'); ?></h3>
                            <p class="lsd-admin-description"><?php esc_html_e('Live field binding', 'listdom'); ?></p>
                        </div>
                        <div class="lsd-admin-icon-box">
                            <i class="listdom-icon wbli-responsive"></i>
                            <h3 class="lsd-admin-subtitle"><?php esc_html_e('Responsive', 'listdom'); ?></h3>
                            <p class="lsd-admin-description"><?php esc_html_e('Mobile-ready', 'listdom'); ?></p>
                        </div>
                    </div>
                </div>
            <?php elseif ($has_templates): ?>
                <?php if (!$show_blank_screen): ?>
                    <div class="lsd-template-toolbar">
                        <?php if (!empty($statuses)): ?>
                            <nav class="lsd-template-toolbar__filters" aria-label="<?php esc_attr_e('Filter templates by status', 'listdom'); ?>">
                                <ul class="lsd-tab-switcher lsd-level-5-menu lsd-sub-tabs lsd-flex" role="tablist">
                                    <?php foreach ($statuses as $status): ?>
                                        <?php
                                        $is_active = !empty($status['active']);
                                        $item_classes = $is_active ? 'lsd-sub-tabs-active' : '';
                                        ?>
                                        <li<?php echo $item_classes ? ' class="' . esc_attr($item_classes) . '"' : ''; ?>>
                                            <a href="<?php echo esc_url($status['url']); ?>" role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
                                                <?php echo esc_html($status['label']); ?>
                                                <span class="lsd-template-toolbar__count"><?php echo esc_html(number_format_i18n((int) $status['count'])); ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                        <form class="lsd-template-toolbar__search" method="get" action="<?php echo esc_url(admin_url('edit.php')); ?>">
                            <input type="hidden" name="post_type" value="<?php echo esc_attr($this->PT); ?>">
                            <?php if (!empty($current_status)): ?>
                                <input type="hidden" name="lsd_status" value="<?php echo esc_attr($current_status); ?>">
                            <?php endif; ?>
                            <label class="screen-reader-text" for="lsd-template-search-field"><?php esc_html_e('Search templates', 'listdom'); ?></label>
                            <input type="search" class="lsd-admin-input" id="lsd-template-search-field" name="lsd_template_search" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php esc_attr_e('Search templates…', 'listdom'); ?>">
                            <button type="submit" class="lsd-secondary-button"><?php esc_html_e('Search', 'listdom'); ?></button>
                            <?php if ($search_query !== ''): ?>
                                <a class="lsd-secondary-button lsd-template-toolbar__reset" href="<?php echo esc_url($this->templates_screen_url([], ['lsd_template_search'])); ?>"><?php esc_html_e('Reset', 'listdom'); ?></a>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endif; ?>
                <div class="lsd-template-grid">
                    <?php foreach ($templates as $template): ?>
                        <div class="lsd-template-card<?php echo !empty($template['preview_url']) ? ' lsd-template-card-has-preview' : ''; ?>">
                            <div class="lsd-template-card-thumb<?php echo !empty($template['preview_url']) ? ' lsd-template-card-thumb-has-preview' : ''; ?>">
                                <?php if (!empty($template['preview_url'])): ?>
                                    <div class="lsd-template-card-preview" aria-hidden="true">
                                        <div class="lsd-template-card-preview-inner">
                                            <iframe
                                                src="<?php echo esc_url($template['preview_url']); ?>"
                                                title="<?php echo esc_attr(sprintf(__('Preview of %s', 'listdom'), $template['title'])); ?>"
                                                loading="lazy"
                                                width="1000"
                                                height="1800"
                                                scrolling="no"
                                                tabindex="-1"
                                                style="display: block; border: 0; pointer-events: none;"
                                            ></iframe>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <img src="<?php echo esc_url($template['thumbnail']); ?>" alt="" loading="lazy">
                                <?php endif; ?>

                                <?php if (!empty($template['actions'])): ?>
                                    <div class="lsd-template-card-actions" role="group" aria-label="<?php esc_attr_e('Template actions', 'listdom'); ?>">
                                        <?php foreach ($template['actions'] as $action): ?>
                                            <?php
                                            $data_attributes = '';
                                            if (!empty($action['data']) && is_array($action['data']))
                                            {
                                                foreach ($action['data'] as $data_key => $data_value)
                                                {
                                                    if ($data_value === null) continue;
                                                    $data_attributes .= sprintf(' data-%s="%s"', esc_attr($data_key), esc_attr($data_value));
                                                }
                                            }
                                            ?>
                                            <a href="<?php echo esc_url($action['url']); ?>" class="lsd-template-card-action lsd-template-card-action-<?php echo esc_attr($action['name']); ?>" title="<?php echo esc_attr($action['label']); ?>"<?php echo $data_attributes; ?>>
                                                <i class="<?php echo esc_attr($action['icon']); ?>" aria-hidden="true"></i>
                                                <span class="screen-reader-text"><?php echo esc_html($action['label']); ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <span class="lsd-template-post-status">
                                    <span class="lsd-template-status lsd-template-status-<?php echo esc_attr($template['status']); ?>">
                                        <?php echo esc_html($template['status_label']); ?>
                                    </span>
                                </span>
                            </div>
                            <div class="lsd-template-card-body">
                                <div class="lsd-template-title-section">
                                    <h3 class="lsd-admin-subtitle"><?php echo esc_html($template['title']); ?></h3>
                                </div>
                                <div class="lsd-template-card-metas">
                                <span class="lsd-template-card-meta">
                                    <span class="lsd-template-card-type">
                                        <?php echo esc_html($template['type']); ?>
                                    </span>
                                </span>
                                    <span class="lsd-template-card-meta">
                                    <?php echo esc_html($template['created']); ?>
                                </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($can_create_templates): ?>
                        <div class="lsd-template-card lsd-template-card-create">
                            <a href="#" class="lsd-template-card-create-link lsd-open-template-modal" data-modal-target="<?php echo esc_attr($modal_id); ?>">
                                <div class="lsd-template-card-thumb">
                                <span class="lsd-template-card-create-icon">
                                    <i class="listdom-icon wbli-add-plus-circle" aria-hidden="true"></i>
                                </span>
                                </div>
                                <div class="lsd-template-card-body">
                                    <h3 class="lsd-admin-subtitle"><?php esc_html_e('Create New Template', 'listdom'); ?></h3>
                                    <p class="lsd-admin-description"><?php esc_html_e('Click to start building', 'listdom'); ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($pagination)): ?>
                    <?php echo $pagination; ?>
                <?php endif; ?>
            <?php elseif ($no_results): ?>
                <div class="lsd-template-toolbar">
                    <?php if (!empty($statuses)): ?>
                        <nav class="lsd-template-toolbar__filters" aria-label="<?php esc_attr_e('Filter templates by status', 'listdom'); ?>">
                            <ul class="lsd-tab-switcher lsd-level-5-menu lsd-sub-tabs lsd-flex" role="tablist">
                                <?php foreach ($statuses as $status): ?>
                                    <?php
                                    $is_active = !empty($status['active']);
                                    $item_classes = $is_active ? 'lsd-sub-tabs-active' : '';
                                    ?>
                                    <li<?php echo $item_classes ? ' class="' . esc_attr($item_classes) . '"' : ''; ?>>
                                        <a href="<?php echo esc_url($status['url']); ?>" role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
                                            <?php echo esc_html($status['label']); ?>
                                            <span class="lsd-template-toolbar__count"><?php echo esc_html(number_format_i18n((int) $status['count'])); ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                    <form class="lsd-template-toolbar__search" method="get" action="<?php echo esc_url(admin_url('edit.php')); ?>">
                        <input type="hidden" name="post_type" value="<?php echo esc_attr($this->PT); ?>">
                        <?php if (!empty($current_status)): ?>
                            <input type="hidden" name="lsd_status" value="<?php echo esc_attr($current_status); ?>">
                        <?php endif; ?>
                        <label class="screen-reader-text" for="lsd-template-search-field"><?php esc_html_e('Search templates', 'listdom'); ?></label>
                        <input type="search" class="lsd-admin-input" id="lsd-template-search-field" name="lsd_template_search" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php esc_attr_e('Search templates...', 'listdom'); ?>">
                        <button type="submit" class="lsd-secondary-button"><?php esc_html_e('Search', 'listdom'); ?></button>
                        <?php if ($search_query !== ''): ?>
                            <a class="lsd-secondary-button lsd-template-toolbar__reset" href="<?php echo esc_url($this->templates_screen_url([], ['lsd_template_search'])); ?>"><?php esc_html_e('Reset', 'listdom'); ?></a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="lsd-template-empty lsd-admin-sections">
                    <h3 class="lsd-admin-title"><?php esc_html_e('No templates found', 'listdom'); ?></h3>
                    <p class="lsd-admin-description"><?php esc_html_e('Try adjusting your search or filter options.', 'listdom'); ?></p>
                    <div class="lsd-template-empty__actions">
                        <a href="<?php echo esc_url($reset_url); ?>" class="lsd-primary-button">
                            <?php esc_html_e('Reset Filters', 'listdom'); ?>
                            <i class="listdom-icon wbli-reset"></i>
                        </a>
                        <?php if ($can_create_templates): ?>
                            <a href="#" class="lsd-primary-button lsd-open-template-modal" data-modal-target="<?php echo esc_attr($modal_id); ?>">
                                <?php esc_html_e('Create Template', 'listdom'); ?>
                                <i class="listdom-icon wbli-add-plus-circle"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
