<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Dashboard_Promotions $this */
/** @var LSD_Shortcodes_Dashboard $dashboard */

$listing_categories = $this->get_popup_listing_categories();

$default_tab = $this->is_topup_available() ? 'topup' : 'labelize';
$dashboard_wrapper = $dashboard->get_dashboard_wrapper([
    'classes' => ['lsd-dashboard', 'lsd-dashboard-promotions'],
    'attributes' => [
        'data-promotions-ajax-url' => admin_url('admin-ajax.php', null),
        'data-promotions-checkout-nonce' => wp_create_nonce('lsd_dashboard_promotions_checkout'),
        'data-promotions-topup-checkout-nonce' => wp_create_nonce('lsd_dashboard_promotions_topup_checkout'),
        'data-promotions-remove-nonce' => wp_create_nonce('lsd_dashboard_promotions_remove_label'),
        'data-promotions-remove-topup-nonce' => wp_create_nonce('lsd_dashboard_promotions_remove_topup'),
        'data-promotions-listings-nonce' => wp_create_nonce('lsd_dashboard_promotions_listings'),
        'data-promotions-topup-records-nonce' => wp_create_nonce('lsd_dashboard_promotions_topup_records'),
        'data-promotions-default-tab' => $default_tab,
        'data-promotions-message-select-label' => esc_attr__('Select at least one label first.', 'listdom'),
        'data-promotions-message-select-listing' => esc_attr__('Select at least one listing first.', 'listdom'),
        'data-promotions-message-processing' => esc_attr__('Processing...', 'listdom'),
        'data-promotions-message-remove-confirm' => esc_attr__('Remove this label from the listing?', 'listdom'),
        'data-promotions-message-remove-topup-confirm' => esc_attr__('Remove this Top-up from the listing?', 'listdom'),
        'data-promotions-message-remove-topup-title' => esc_attr__('Remove Top-up?', 'listdom'),
        'data-promotions-message-remove-topup-approve' => esc_attr__('Remove', 'listdom'),
        'data-promotions-message-remove-topup-cancel' => esc_attr__('Cancel', 'listdom'),
        'data-promotions-message-request-failed' => esc_attr__('Something went wrong. Please try again.', 'listdom'),
        'data-promotions-message-no-listings' => esc_attr__('No listings match your current filters.', 'listdom'),
        'data-promotions-message-loading-listings' => esc_attr__('Loading listings...', 'listdom'),
    ],
]);
?>
<div class="<?php echo esc_attr($dashboard_wrapper['class']); ?>" id="lsd_dashboard_promotions"<?php echo $dashboard_wrapper['attributes']; ?>>
    <div class="lsd-row lsd-dashboard-wrapper">
        <div class="lsd-dashboard-menus-wrapper">
            <?php echo LSD_Kses::element($dashboard->menus()); ?>
        </div>
        <div class="lsd-dashboard-content-wrapper <?php echo LSD_Base::get_lsd_class('sections'); ?>">
            <div class="lsd-fe-section-heading">
                <div class="lsd-fe-title-icon">
                    <i class="lsd-fe-icon fa-solid fa-angles-up"></i>
                    <h2 class="lsd-fe-title"><?php esc_html_e('Listing Promotions', 'listdom'); ?></h2>
                </div>
                <p class="lsd-fe-description"><?php esc_html_e('Manage listing boosts, paid labels, and visibility options.', 'listdom'); ?></p>
            </div>

            <div class="lsd-fe-box-white lsd-dashboard-promotions-tabs-box">
                <div class="lsd-fe-tabs lsd-dashboard-promotions-tabs">
                    <ul class="lsd-fe-tabs-nav">
                        <li class="<?php echo $default_tab === 'topup' ? 'lsd-active' : ''; ?>">
                            <a href="#" class="lsd-promotions-tab-trigger" data-tab="topup">
                                <i class="lsd-fe-icon fa-solid fa-arrow-up"></i>
                                <?php esc_html_e('Top-up', 'listdom'); ?>
                            </a>
                        </li>
                        <li class="<?php echo $default_tab === 'labelize' ? 'lsd-active' : ''; ?>">
                            <a href="#" class="lsd-promotions-tab-trigger" data-tab="labelize">
                                <i class="lsd-fe-icon fa-solid fa-tags"></i>
                                <?php esc_html_e('Labelize', 'listdom'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="lsd-dashboard-promotions-tab-content lsd-dashboard-promotions-tab-topup <?php echo $default_tab === 'topup' ? ' lsd-tab-content-active' : ''; ?>" data-promotions-tab="topup">
                <?php include lsd_template('dashboard/promotions/topup/topup.php'); ?>
            </div>
            <div class="lsd-dashboard-promotions-tab-content lsd-dashboard-promotions-tab-labelize <?php echo $default_tab === 'labelize' ? ' lsd-tab-content-active' : ''; ?>" data-promotions-tab="labelize">
                <?php include lsd_template('dashboard/promotions/labelize.php'); ?>
            </div>
        </div>
    </div>

    <div class="lsd-modal lsd-dashboard-promotions-modal" id="lsd_dashboard_promotions_popup">
        <div class="lsd-modal-content lsd-dashboard-promotions-modal-content">
            <div class="lsd-dashboard-promotions-modal-header">
                <div class="lsd-dashboard-promotions-modal-title">
                    <i class="lsd-fe-icon fa-solid fa-list-ul"></i>
                    <h3 class="lsd-fe-title"><?php esc_html_e('Select Listings', 'listdom'); ?></h3>
                </div>
                <div class="lsd-dashboard-promotions-modal-actions">
                    <span><strong class="lsd-promotion-selected-listing-count">0</strong> <?php esc_html_e('Selected', 'listdom'); ?></span>
                    <button type="button" class="lsd-light-button lsd-promotion-submit-checkout"><?php esc_html_e('Continue', 'listdom'); ?> <i class="lsd-fe-icon fa fa-arrow-right"></i></button>
                </div>
            </div>

            <div class="lsd-dashboard-promotions-modal-toolbar">
                <select class="lsd-fe-select2-base lsd-promotion-popup-category" placeholder="<?php esc_attr_e('All Categories', 'listdom'); ?>">
                    <option value=""><?php esc_html_e('All Categories', 'listdom'); ?></option>
                    <?php foreach ($listing_categories as $category): ?>
                        <?php if (!$category instanceof WP_Term) continue; ?>
                        <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="search" class="lsd-fe-input-base lsd-promotion-popup-search" placeholder="<?php esc_attr_e('Type to Search', 'listdom'); ?>">
            </div>

            <div class="lsd-dashboard-promotions-selected-listings lsd-promotion-selected-listings"></div>

            <div class="lsd-dashboard-promotions-modal-results lsd-promotion-modal-results" data-next-page="0" data-has-more="0" data-mode="">
                <div class="lsd-dashboard-promotions-modal-list lsd-promotion-modal-list"></div>
                <div class="lsd-dashboard-promotions-modal-loading lsd-promotion-modal-loading lsd-util-hide">
                    <span class="lsd-loader"></span>
                </div>
                <div class="lsd-promotion-modal-empty lsd-util-hide">
                    <?php $dashboard->empty([
                        'title' => esc_html__('No listings available', 'listdom'),
                        'description' => esc_html__('Create or publish a listing first to assign paid labels or buy a Top-up.', 'listdom'),
                        'image' => 'img/dashboard/no-listings.svg',
                        'quick_actions' => [],
                        'classes' => ['lsd-fe-box-white'],
                    ]); ?>
                </div>
            </div>

            <div class="lsd-dashboard-promotions-modal-footer">
                <span><strong class="lsd-promotion-selected-listing-count">0</strong> <?php esc_html_e('Selected', 'listdom'); ?></span>
                <button type="button" class="lsd-light-button lsd-promotion-submit-checkout"><?php esc_html_e('Continue', 'listdom'); ?> <i class="lsd-fe-icon fa fa-arrow-right"></i></button>
            </div>
        </div>
    </div>
</div>
