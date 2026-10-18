<?php
defined('ABSPATH') || die();

$business_data_has_connection = LSD_Webilia_Connect::hasConnection();
$business_data_mode = isset($_GET['mode']) ? sanitize_key(wp_unslash($_GET['mode'])) : 'find';
if (!in_array($business_data_mode, ['find', 'enrich', 'refresh', 'history'], true)) $business_data_mode = 'find';
$business_data_maintenance = $business_data_mode !== 'find';
$business_data_connection_required = !$business_data_has_connection && in_array($business_data_mode, ['enrich', 'refresh'], true);
$business_data_titles = [
    'enrich' => __('Enrich existing listings', 'listdom'),
    'refresh' => __('Refresh linked listings', 'listdom'),
    'history' => __('Business Data history', 'listdom'),
];
?>
<div class="lsd-ix-wrap">
    <section id="lsd-business-data" class="lsd-settings-form-group lsd-business-data"<?php if ($business_data_maintenance): ?> hidden<?php endif; ?>>
        <div class="lsd-business-data-header">
            <h3 class="lsd-admin-title"><?php esc_html_e('Webilia Business Data', 'listdom'); ?></h3>
            <?php if ($business_data_has_connection): ?>
            <div class="lsd-business-data-balance" aria-live="polite">
                <span><?php esc_html_e('Webilia Credits', 'listdom'); ?></span>
                <strong data-business-data-balance>
                    <output data-business-data-balance-value hidden></output>
                    <i class="lsd-loader" data-business-data-balance-loader role="status" aria-label="<?php esc_attr_e('Loading Webilia Credits…', 'listdom'); ?>"></i>
                </strong>
            </div>
            <?php endif; ?>
        </div>
        <?php if (!$business_data_has_connection): ?>
        <div class="lsd-business-data-guide lsd-wbl-connection-wrapper">
            <div class="lsd-wbl-connection-layout">
                <div class="lsd-wbl-connection-content">
                    <h4><?php esc_html_e('Connect your website to get started', 'listdom'); ?></h4>
                    <p class="lsd-admin-description"><?php esc_html_e('Open Webilia Connect, choose Connect to Webilia, and finish linking your account. Then return here to see your balance and search businesses. You’ll receive 20 free Webilia Credits when you connect your first website to Webilia.', 'listdom'); ?></p>
                    <a class="lsd-primary-button" href="<?php echo esc_url(admin_url('admin.php?page=listdom-connect&tab=connect')); ?>"><?php esc_html_e('Go to Webilia Connect', 'listdom'); ?><i class="webilia-icon wbli-right-arrow" aria-hidden="true"></i></a>
                </div>
                <div class="lsd-wbl-connection-art" aria-hidden="true"><img src="<?php echo esc_url($this->lsd_asset_url('img/webilia-connect-illustration.png')); ?>" alt=""></div>
            </div>
        </div>
        <?php else: ?>
        <div class="lsd-business-data-guide" data-business-data-checking role="status"><?php esc_html_e('Checking your Webilia Credits…', 'listdom'); ?></div>
        <div class="lsd-business-data-guide" data-business-data-no-credits hidden>
            <h4><?php esc_html_e('Get more Webilia Credits to search', 'listdom'); ?></h4>
            <p><?php esc_html_e('Your Webilia account has no credits available for a new Business Data search. Buy a credit package to continue. You can still reopen recent searches and import their saved results below without another charge.', 'listdom'); ?></p>
            <a class="lsd-primary-button" href="https://api.webilia.com/go/listdom-credits" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Buy Webilia Credits', 'listdom'); ?><i class="webilia-icon wbli-link-square" aria-hidden="true"></i></a>
            <button type="button" class="lsd-secondary-button" data-business-data-retry-balance><?php esc_html_e('Check balance again', 'listdom'); ?></button>
        </div>
        <div class="lsd-business-data-guide" data-business-data-balance-error hidden>
            <h4><?php esc_html_e('We could not check your Webilia Credits', 'listdom'); ?></h4>
            <p data-business-data-balance-error-message><?php esc_html_e('Please try again. If the problem continues, check your website connection in Webilia Connect.', 'listdom'); ?></p>
            <button type="button" class="lsd-primary-button" data-business-data-retry-balance><?php esc_html_e('Try again', 'listdom'); ?></button>
            <a class="lsd-secondary-button" href="<?php echo esc_url(admin_url('admin.php?page=listdom-connect&tab=connect')); ?>"><?php esc_html_e('Open Webilia Connect', 'listdom'); ?></a>
        </div>
        <div class="lsd-business-data-guide" data-business-data-map-disabled hidden>
            <h4><?php esc_html_e('Enable Map & Address to search businesses', 'listdom'); ?></h4>
            <p><?php esc_html_e('Business Data searches need a map to choose their center and radius. Enable the Map & Address component in Listdom settings to start a new search. You can still open recent searches and import saved results below.', 'listdom'); ?></p>
            <a class="lsd-primary-button" href="<?php echo esc_url(admin_url('admin.php?page=listdom-settings&tab=advanced')); ?>"><?php esc_html_e('Open Listdom settings', 'listdom'); ?></a>
        </div>
        <div data-business-data-search-workspace hidden>
            <div class="lsd-alert lsd-info lsd-my-0">
                <?php esc_html_e('Webilia Business Data lets you search verified businesses in a focused area and import the selected results as draft listings. Choose one or more categories, position the marker, set a radius and result limit, then select Search. Each new search costs 1–20 Webilia Credits.', 'listdom'); ?>
            </div>
            <div class="lsd-business-data-grid">
                <div class="lsd-business-data-panel">
                    <div class="lsd-business-data-category-heading">
                        <label for="lsd-business-data-category-search"><?php esc_html_e('Categories', 'listdom'); ?></label>
                        <button type="button" class="lsd-business-data-category-refresh" data-business-data-refresh-categories aria-label="<?php esc_attr_e('Refresh categories', 'listdom'); ?>" title="<?php esc_attr_e('Refresh categories', 'listdom'); ?>"><i class="lsd-icon fas fa-sync-alt" aria-hidden="true"></i></button>
                    </div>
                    <div class="lsd-business-data-category-picker">
                        <input id="lsd-business-data-category-search" class="lsd-admin-input" type="search" autocomplete="off" aria-controls="lsd-business-data-categories" placeholder="<?php esc_attr_e('Search categories', 'listdom'); ?>">
                        <div id="lsd-business-data-categories" data-business-data-categories class="lsd-business-data-categories"></div>
                    </div>
                    <div data-business-data-selected class="lsd-business-data-selected"></div>
                    <label for="lsd-business-data-radius"><?php esc_html_e('Radius (km)', 'listdom'); ?></label>
                    <input id="lsd-business-data-radius" class="lsd-admin-input" type="number" min="0.1" max="50" step="0.1" value="25">
                    <label for="lsd-business-data-limit"><?php esc_html_e('Maximum results', 'listdom'); ?></label>
                    <input id="lsd-business-data-limit" class="lsd-admin-input" type="number" min="1" max="100" step="1" value="20">
                    <small class="lsd-business-data-limit-help" data-business-data-limit-help hidden></small>
                    <button type="button" class="lsd-primary-button" data-business-data-search><?php esc_html_e('Search Webilia Business Data', 'listdom'); ?></button>
                    <div data-business-data-message class="lsd-business-data-message" aria-live="polite"></div>
                </div>
                <div class="lsd-business-data-map-wrap">
                    <label class="lsd-business-data-map-toggle"><input type="checkbox" data-business-data-show-results> <span><?php esc_html_e('Show search results on map', 'listdom'); ?></span></label>
                    <div id="lsd-business-data-map"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div id="lsd-business-data-results" class="lsd-business-data-results-wrap" hidden>
            <div class="lsd-business-data-results-head">
                <h3 class="lsd-admin-title"><?php esc_html_e('Search results', 'listdom'); ?></h3>
                <label><input type="checkbox" data-business-data-select-all> <?php esc_html_e('Select all', 'listdom'); ?></label>
                <button type="button" class="lsd-primary-button" data-business-data-next disabled><?php esc_html_e('Next (0)', 'listdom'); ?></button>
            </div>
            <div class="lsd-business-data-results-scroll">
                <div data-business-data-meta class="lsd-business-data-meta"></div>
                <div data-business-data-results class="lsd-business-data-results"></div>
            </div>
        </div>
        <div class="lsd-business-data-import-result" data-business-data-import-result hidden>
            <div class="lsd-business-data-import-result-hero" role="status" aria-live="polite">
                <span class="lsd-business-data-import-result-icon" aria-hidden="true"><i class="lsd-icon fas fa-check"></i></span>
                <div>
                    <h3 class="lsd-admin-title" data-business-data-import-result-title tabindex="-1"></h3>
                    <p data-business-data-import-result-description></p>
                </div>
            </div>
            <div class="lsd-business-data-import-result-counts">
                <div><strong data-business-data-created-count>0</strong><span><?php esc_html_e('Drafts created', 'listdom'); ?></span></div>
                <div><strong data-business-data-updated-count>0</strong><span><?php esc_html_e('Existing listings enriched', 'listdom'); ?></span></div>
                <div><strong data-business-data-skipped-count>0</strong><span><?php esc_html_e('Skipped', 'listdom'); ?></span></div>
            </div>
            <div class="lsd-business-data-import-result-actions">
                <a class="lsd-primary-button" href="<?php echo esc_url(admin_url('edit.php?post_type=' . LSD_Base::PTYPE_LISTING)); ?>"><?php esc_html_e('Manage listings', 'listdom'); ?><i class="webilia-icon wbli-right-arrow" aria-hidden="true"></i></a>
            </div>
            <div class="lsd-business-data-import-result-details" data-business-data-import-result-details></div>
        </div>
        <div class="lsd-modal lsd-business-data-import-modal" data-business-data-import-modal aria-hidden="true">
            <div class="lsd-modal-content" role="dialog" aria-modal="true" aria-labelledby="lsd-business-data-import-title">
                <div class="lsd-business-data-import-header">
                    <div>
                        <h3 id="lsd-business-data-import-title" class="lsd-admin-title"><?php esc_html_e('Create draft listings', 'listdom'); ?></h3>
                        <p class="lsd-admin-description" data-business-data-import-summary></p>
                    </div>
                    <button type="button" class="lsd-business-data-import-close" data-business-data-modal-close aria-label="<?php esc_attr_e('Close', 'listdom'); ?>">&times;</button>
                </div>
                <div class="lsd-business-data-import-body">
                    <div class="lsd-business-data-import-status" data-business-data-terms-status role="status"></div>
                    <label class="lsd-business-data-auto-category"><input type="checkbox" data-business-data-auto-category checked> <span><?php esc_html_e('Automatically assign each business category', 'listdom'); ?><small><?php esc_html_e('Use the category supplied with each result. Create it in Listdom if it does not exist.', 'listdom'); ?></small></span></label>
                    <label class="lsd-business-data-auto-category"><input type="checkbox" data-business-data-enrich-existing> <span><?php esc_html_e('Enrich listings previously imported from these results', 'listdom'); ?><small><?php esc_html_e('Fill only empty contact and address fields on existing listings. Your edits and current categories will not be replaced.', 'listdom'); ?></small></span></label>
                    <?php foreach (['categories' => __('Categories', 'listdom'), 'locations' => __('Locations', 'listdom'), 'features' => __('Features', 'listdom'), 'tags' => __('Tags', 'listdom'), 'labels' => __('Labels', 'listdom')] as $taxonomy => $label): ?>
                    <div class="lsd-business-data-term-group<?php echo $taxonomy === 'categories' ? ' lsd-business-data-manual-categories' : ''; ?>" data-business-data-term-group="<?php echo esc_attr($taxonomy); ?>"<?php echo $taxonomy === 'categories' ? ' hidden' : ''; ?>>
                        <h4 class="lsd-admin-subtitle"><?php echo esc_html($label); ?></h4>
                        <input type="search" class="lsd-admin-input" data-business-data-term-search="<?php echo esc_attr($taxonomy); ?>" placeholder="<?php echo esc_attr(sprintf(__('Search %s', 'listdom'), strtolower($label))); ?>" aria-label="<?php echo esc_attr(sprintf(__('Search %s', 'listdom'), strtolower($label))); ?>">
                        <div class="lsd-business-data-term-list" data-business-data-term-list="<?php echo esc_attr($taxonomy); ?>"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="lsd-business-data-import-footer">
                    <button type="button" class="lsd-secondary-button" data-business-data-modal-close><?php esc_html_e('Cancel', 'listdom'); ?></button>
                    <button type="button" class="lsd-primary-button" data-business-data-import disabled><?php esc_html_e('Create Drafts (0)', 'listdom'); ?></button>
                </div>
            </div>
        </div>
        <div class="lsd-business-data-history-wrap"<?php if (!$business_data_has_connection): ?> hidden<?php endif; ?>>
            <div class="lsd-business-data-results-head"><h3 class="lsd-admin-title"><?php esc_html_e('Recent searches', 'listdom'); ?></h3><button type="button" class="lsd-secondary-button" data-business-data-clear-history hidden><?php esc_html_e('Clear history', 'listdom'); ?></button></div>
            <div data-business-data-history class="lsd-business-data-history-list"></div>
        </div>
    </section>
    <section id="lsd-business-data-maintenance" class="lsd-settings-form-group lsd-business-data lsd-bdm"<?php if (!$business_data_maintenance): ?> hidden<?php endif; ?>>
        <div class="lsd-business-data-header">
            <h3 class="lsd-admin-title" data-bdm-title><?php echo esc_html($business_data_titles[$business_data_mode] ?? ''); ?></h3>
            <?php if ($business_data_has_connection): ?>
            <div class="lsd-business-data-balance" aria-live="polite">
                <span><?php esc_html_e('Webilia Credits', 'listdom'); ?></span>
                <strong data-bdm-balance>—</strong>
            </div>
            <?php endif; ?>
        </div>
        <div class="lsd-business-data-guide lsd-wbl-connection-wrapper" data-bdm-connection-guide<?php if (!$business_data_connection_required): ?> hidden<?php endif; ?>>
            <div class="lsd-wbl-connection-layout">
                <div class="lsd-wbl-connection-content">
                    <h4><?php esc_html_e('Connect your website to get started', 'listdom'); ?></h4>
                    <p class="lsd-admin-description"><?php esc_html_e('Open Webilia Connect and finish linking your account. Then return here to match or refresh your listings with Business Data.', 'listdom'); ?></p>
                    <a class="lsd-primary-button" href="<?php echo esc_url(admin_url('admin.php?page=listdom-connect&tab=connect')); ?>"><?php esc_html_e('Go to Webilia Connect', 'listdom'); ?><i class="webilia-icon wbli-right-arrow" aria-hidden="true"></i></a>
                </div>
                <div class="lsd-wbl-connection-art" aria-hidden="true"><img src="<?php echo esc_url($this->lsd_asset_url('img/webilia-connect-illustration.png')); ?>" alt=""></div>
            </div>
        </div>
        <div data-bdm-workspace<?php if ($business_data_mode === 'find' || $business_data_mode === 'history' || $business_data_connection_required): ?> hidden<?php endif; ?>>
            <div class="lsd-alert lsd-info lsd-my-0" data-bdm-description><?php if ($business_data_mode === 'enrich'): ?><?php esc_html_e('Choose listings, review possible business matches, then select the fields to apply.', 'listdom'); ?><?php elseif ($business_data_mode === 'refresh'): ?><?php esc_html_e('Choose linked listings, review source changes, then select fields to apply. Closed businesses remain published until you decide otherwise.', 'listdom'); ?><?php endif; ?></div>
            <div class="lsd-bdm-selection lsd-business-data-panel">
                <div class="lsd-bdm-controls">
                    <input type="search" class="lsd-admin-input" data-bdm-search placeholder="<?php esc_attr_e('Search listings by title', 'listdom'); ?>">
                    <button type="button" class="lsd-secondary-button" data-bdm-search-button><?php esc_html_e('Search', 'listdom'); ?></button>
                    <span data-bdm-selection-count><?php echo esc_html(sprintf(__('%d / 100 selected', 'listdom'), 0)); ?></span>
                </div>
                <div data-bdm-selected class="lsd-bdm-selected" aria-label="<?php esc_attr_e('Selected listings', 'listdom'); ?>" hidden></div>
                <label><input type="checkbox" data-bdm-select-page> <?php esc_html_e('Select all listings on this page', 'listdom'); ?></label>
                <div data-bdm-listings class="lsd-bdm-listings"><?php esc_html_e('Loading listings…', 'listdom'); ?></div>
                <div class="lsd-bdm-pages"><button type="button" class="lsd-secondary-button" data-bdm-prev><?php esc_html_e('Previous', 'listdom'); ?></button><span data-bdm-page></span><button type="button" class="lsd-secondary-button" data-bdm-next><?php esc_html_e('Next', 'listdom'); ?></button></div>
                <div class="lsd-bdm-actions">
                    <button type="button" class="lsd-primary-button" data-bdm-quote><?php esc_html_e('Estimate credits', 'listdom'); ?></button>
                    <button type="button" class="lsd-primary-button" data-bdm-fetch hidden><?php esc_html_e('Fetch Business Data and review', 'listdom'); ?></button>
                    <span data-bdm-quote-result></span>
                </div>
                <div data-bdm-progress class="lsd-alert lsd-info" role="status" aria-live="polite" aria-atomic="true" tabindex="-1" hidden></div>
            </div>
            <div data-bdm-review class="lsd-bdm-review"></div>
            <div class="lsd-bdm-actions" data-bdm-apply-actions hidden>
                <div data-bdm-shared-controls<?php if ($business_data_mode !== 'refresh'): ?> hidden<?php endif; ?>><label for="lsd-bdm-shared-rule"><?php esc_html_e('Shared refresh rule', 'listdom'); ?></label>
                <select id="lsd-bdm-shared-rule" data-bdm-shared-rule>
                    <option value="none"><?php esc_html_e('Review fields individually', 'listdom'); ?></option>
                    <option value="empty"><?php esc_html_e('Select empty fields across the batch', 'listdom'); ?></option>
                    <option value="selected"><?php esc_html_e('Select chosen field types across the batch', 'listdom'); ?></option>
                </select>
                <span data-bdm-shared-fields hidden><?php foreach (['phone' => __('Phone', 'listdom'), 'email' => __('Email', 'listdom'), 'website' => __('Website', 'listdom'), 'address' => __('Address', 'listdom'), 'coordinates' => __('Coordinates', 'listdom'), 'socials' => __('Socials', 'listdom'), 'categories' => __('Categories', 'listdom'), 'brand' => __('Brand', 'listdom'), 'operating_status' => __('Operating status', 'listdom')] as $field => $label): ?><label><input type="checkbox" data-bdm-shared-field="<?php echo esc_attr($field); ?>"> <?php echo esc_html($label); ?></label> <?php endforeach; ?></span></div>
                <button type="button" class="lsd-primary-button" data-bdm-apply><?php esc_html_e('Apply selected changes', 'listdom'); ?></button>
            </div>
        </div>
        <div data-bdm-history-panel<?php if ($business_data_mode !== 'history'): ?> hidden<?php endif; ?>><div class="lsd-alert lsd-info lsd-my-0"><?php esc_html_e('Review recent Business Data searches, import activity, lookups, and updates. Search history retains up to 10 searches for 30 days; maintenance history retains the latest 50 operations.', 'listdom'); ?></div><div class="lsd-business-data-history-wrap" data-bdm-history><?php esc_html_e('Loading history…', 'listdom'); ?></div></div>
    </section>
</div>
