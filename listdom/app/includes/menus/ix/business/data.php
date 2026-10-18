<?php

class LSD_Menus_IX_Business_Data extends LSD_Base
{
    private const TAB = 'business-data';
    private const HISTORY_OPTION = 'lsd_business_data_history';
    private const HISTORY_DELETE_UNLOCKED_OPTION = 'lsd_business_data_history_delete_unlocked';
    private const HISTORY_LIMIT = 10;
    private const HISTORY_TTL = 2592000;
    private const CATEGORY_CACHE_TTL = 604800;
    // New keys leave previously cached 30-day category responses behind.
    private const CATEGORY_VERSION_TRANSIENT = 'lsd_business_data_taxonomy_version_v2';
    private const CATEGORY_TRANSIENT_PREFIX = 'lsd_business_data_categories_v2_';
    // Keep in sync with the Overture API's credits_per_places setting.
    private const RESULTS_PER_CREDIT = 5;
    private const PREFERENCES_META = 'lsd_business_data_preferences';
    private const NONCE = 'lsd_business_data';
    private const MAINTENANCE_HISTORY_OPTION = 'lsd_business_data_maintenance_history';
    private const TERM_TAXONOMIES = [
        'categories' => LSD_Base::TAX_CATEGORY,
        'locations' => LSD_Base::TAX_LOCATION,
        'features' => LSD_Base::TAX_FEATURE,
        'tags' => LSD_Base::TAX_TAG,
        'labels' => LSD_Base::TAX_LABEL,
    ];

    public function init(): void
    {
        add_action('wp_ajax_lsd_business_data_balance', [$this, 'balance']);
        add_action('wp_ajax_lsd_business_data_categories', [$this, 'categories']);
        add_action('wp_ajax_lsd_business_data_search', [$this, 'search']);
        add_action('wp_ajax_lsd_business_data_history', [$this, 'history']);
        add_action('wp_ajax_lsd_business_data_terms', [$this, 'terms']);
        add_action('wp_ajax_lsd_business_data_import', [$this, 'import']);
        add_action('wp_ajax_lsd_business_data_clear_history', [$this, 'clear_history']);
        add_action('wp_ajax_lsd_business_data_save_preferences', [$this, 'save_preferences']);
        add_action('wp_ajax_lsd_business_data_maintenance_list', [$this, 'maintenance_list']);
        add_action('wp_ajax_lsd_business_data_maintenance_quote', [$this, 'maintenance_quote']);
        add_action('wp_ajax_lsd_business_data_maintenance_fetch', [$this, 'maintenance_fetch']);
        add_action('wp_ajax_lsd_business_data_maintenance_apply', [$this, 'maintenance_apply']);
        add_action('wp_ajax_lsd_business_data_maintenance_history', [$this, 'maintenance_history']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('add_meta_boxes_' . LSD_Base::PTYPE_LISTING, [$this, 'register_metabox']);
        add_filter('post_row_actions', [$this, 'listing_row_actions'], 10, 2);
        add_filter('bulk_actions-edit-' . LSD_Base::PTYPE_LISTING, [$this, 'listing_bulk_actions']);
        add_filter('handle_bulk_actions-edit-' . LSD_Base::PTYPE_LISTING, [$this, 'handle_listing_bulk_action'], 10, 3);
    }

    public function assets(string $hook): void
    {
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : self::TAB;

        if (($hook !== 'listdom_page_listdom-ix' && $page !== 'listdom-ix') || $tab !== self::TAB) return;
        $connected = LSD_Webilia_Connect::hasConnection();

        if (function_exists('wp_set_script_translations') && wp_script_is('lsd-backend', 'registered')) wp_set_script_translations('lsd-backend', 'listdom');

        $mapEnabled = LSD_Assets::leaflet();
        $settings = LSD_Options::settings();
        $config = [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE),
            'connected' => $connected,
            'mapEnabled' => $mapEnabled,
            'resultsPerCredit' => self::RESULTS_PER_CREDIT,
            'center' => [
                'lat' => (float) ($settings['map_backend_lt'] ?? 0),
                'lng' => (float) ($settings['map_backend_ln'] ?? 0),
                'zoom' => (int) ($settings['map_backend_zl'] ?? 12),
            ],
            'preferences' => (array) get_user_meta(get_current_user_id(), self::PREFERENCES_META, true),
            'strings' => [
                'searching' => esc_html__('Searching Webilia Business Data…', 'listdom'),
                'loading' => esc_html__('Loading…', 'listdom'),
                'noResults' => esc_html__('No businesses matched this search.', 'listdom'),
                'selectCategory' => esc_html__('Choose at least one category.', 'listdom'),
                'maxCategories' => esc_html__('You can choose up to five categories.', 'listdom'),
                'importing' => esc_html__('Creating draft listings…', 'listdom'),
                'balanceUnavailable' => esc_html__('Webilia Credits are unavailable right now.', 'listdom'),
                'categoriesUnavailable' => esc_html__('Categories could not be loaded.', 'listdom'),
                'categoryPrompt' => esc_html__('Enter at least two characters to search categories.', 'listdom'),
                'categoryEmpty' => esc_html__('No matching categories.', 'listdom'),
                'categoryLoading' => esc_html__('Loading categories…', 'listdom'),
                'searchFailed' => esc_html__('Business Data search failed.', 'listdom'),
                'limitForCredits' => esc_html__('With %1$d Webilia Credits, you can request up to %2$d results.', 'listdom'),
                'searchComplete' => esc_html__('Business Data search complete.', 'listdom'),
                'selectBusiness' => esc_html__('Select at least one business.', 'listdom'),
                'importSuccessTitle' => esc_html__('Your draft listings are ready', 'listdom'),
                'importMixedTitle' => esc_html__('New drafts created and existing listings enriched', 'listdom'),
                'importUpdatedTitle' => esc_html__('Existing listings were enriched', 'listdom'),
                'importPartialTitle' => esc_html__('Listings processed with some skips', 'listdom'),
                'importFailedTitle' => esc_html__('No listings were changed', 'listdom'),
                'importSuccessDescription' => esc_html__('Your selected businesses were saved as drafts. Review their details, then publish them when they are ready.', 'listdom'),
                'importMixedDescription' => esc_html__('New businesses were saved as drafts and missing details were added to previously imported listings.', 'listdom'),
                'importUpdatedDescription' => esc_html__('Missing contact and address details were added to previously imported listings without replacing your edits.', 'listdom'),
                'importPartialDescription' => esc_html__('Some listings were created or enriched, while others were skipped. Review the details below before trying again.', 'listdom'),
                'importFailedDescription' => esc_html__('No listings were changed. Review the skipped reasons below before trying again.', 'listdom'),
                'importFailed' => esc_html__('The import request failed. Please try again.', 'listdom'),
                'createdDetails' => esc_html__('Created drafts', 'listdom'),
                'updatedDetails' => esc_html__('Enriched listings', 'listdom'),
                'skippedDetails' => esc_html__('Skipped businesses', 'listdom'),
                'editDraft' => esc_html__('Edit draft', 'listdom'),
                'editListing' => esc_html__('Edit listing', 'listdom'),
                'reviewListing' => esc_html__('Review listing', 'listdom'),
                'operatingOpen' => esc_html__('Open', 'listdom'),
                'operatingTemporarilyClosed' => esc_html__('Temporarily closed', 'listdom'),
                'operatingPermanentlyClosed' => esc_html__('Permanently closed', 'listdom'),
                'openSaved' => esc_html__('Saved results opened without a new search or charge.', 'listdom'),
                'removeConfirm' => esc_html__('Delete this saved search?', 'listdom'),
                'clearConfirm' => esc_html__('Delete all recent searches?', 'listdom'),
                'confirm' => esc_html__('Delete', 'listdom'),
                'cancel' => esc_html__('Cancel', 'listdom'),
                'historyRemoved' => esc_html__('Search history updated.', 'listdom'),
                'historyFailed' => esc_html__('Could not update search history.', 'listdom'),
                'historyUnavailable' => esc_html__('Recent searches could not be loaded.', 'listdom'),
                'removeSearch' => esc_html__('Delete saved search', 'listdom'),
                'preferencesFailed' => esc_html__('Search settings could not be saved.', 'listdom'),
                'createDrafts' => esc_html__('Create Drafts (%d)', 'listdom'),
                'nextDrafts' => esc_html__('Next (%d)', 'listdom'),
                'selectedBusinesses' => esc_html__('%d selected businesses. Choose the terms to assign to every draft.', 'listdom'),
                'loadingTerms' => esc_html__('Loading listing terms…', 'listdom'),
                'termsUnavailable' => esc_html__('Listing terms could not be loaded. Please try again.', 'listdom'),
                'noTerms' => esc_html__('No terms available.', 'listdom'),
                'selectManualCategory' => esc_html__('Choose at least one category, or enable automatic category assignment.', 'listdom'),
                'metersAway' => esc_html__('%d m away', 'listdom'),
                'untitledBusiness' => esc_html__('Untitled business', 'listdom'),
                'noRecentSearches' => esc_html__('No recent searches.', 'listdom'),
            ],
        ];

        wp_localize_script('lsd-backend', 'lsdBusinessDataConfig', $config);
        wp_enqueue_script('lsd-business-data-maintenance', $this->lsd_asset_url('js/business-data-maintenance.js'), ['jquery', 'lsd-backend'], LSD_Assets::version(), true);
        wp_enqueue_style('lsd-business-data-maintenance', $this->lsd_asset_url('css/business-data-maintenance.css'), ['lsd-backend'], LSD_Assets::version());
        wp_localize_script('lsd-business-data-maintenance', 'lsdBusinessMaintenance', [
            'ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce(self::NONCE),
            'connected' => $connected,
            'storageScope' => md5(home_url('/') . '|' . get_current_blog_id() . '|' . get_current_user_id()),
            'mode' => isset($_GET['mode']) ? sanitize_key(wp_unslash($_GET['mode'])) : 'find',
            'listingIds' => $this->maintenance_initial_ids(),
            'fieldLabels' => [
                'phone' => __('Phone', 'listdom'), 'email' => __('Email', 'listdom'), 'website' => __('Website', 'listdom'),
                'address' => __('Address', 'listdom'), 'coordinates' => __('Coordinates', 'listdom'),
                'socials' => __('Social profiles', 'listdom'), 'categories' => __('Categories', 'listdom'),
                'brand' => __('Brand', 'listdom'), 'operating_status' => __('Operating status', 'listdom'),
            ],
            'strings' => [
                'unavailable' => __('Business Data maintenance is unavailable. Update Webilia Connect and activate a maintenance-ready Business Data index.', 'listdom'),
                'selectListings' => __('Select at least one listing.', 'listdom'),
                'cost' => __('Estimated cost: %s Webilia Credits for %d listings.', 'listdom'),
                'ready' => __('Business Data is ready for review.', 'listdom'),
                'selectionRemoved' => __('Some selected listings are no longer eligible and were removed.', 'listdom'),
                'removeListing' => __('Remove %s from selection', 'listdom'),
                'listingId' => __('Listing #%d', 'listdom'),
                'historyOperations' => ['search' => __('Search', 'listdom'), 'enrich' => __('Enrichment lookup', 'listdom'), 'refresh' => __('Refresh lookup', 'listdom'), 'apply' => __('Apply changes', 'listdom')],
                'historyResults' => __('%d results', 'listdom'),
                'historyListings' => __('%d listings', 'listdom'),
                'historyCredits' => __('%s Webilia Credits', 'listdom'),
                'historyImports' => __('%d recorded import outcomes (created, updated, or skipped).', 'listdom'),
                'charged' => __('Charged %s Webilia Credits.', 'listdom'),
                'replayed' => __('Earlier paid results were restored without another charge.', 'listdom'),
                'apply' => __('Apply selected changes', 'listdom'),
                'noMatch' => __('No suitable match found. Check the listing name and address before trying again.', 'listdom'),
                'notFound' => __('The linked business was not found in the current provider release. Match it again manually.', 'listdom'),
                'error' => __('The Business Data operation could not be completed.', 'listdom'),
                'selectionCount' => __('%d / 100 selected', 'listdom'),
                'enrichTitle' => __('Enrich existing listings', 'listdom'),
                'refreshTitle' => __('Refresh linked listings', 'listdom'),
                'historyTitle' => __('Business Data history', 'listdom'),
                'enrichDescription' => __('Choose listings, review possible business matches, then select the fields to apply.', 'listdom'),
                'refreshDescription' => __('Choose linked listings, review source changes, then select fields to apply. Closed businesses remain published until you decide otherwise.', 'listdom'),
                'loadingListings' => __('Loading listings…', 'listdom'),
                'noEligible' => __('No eligible listings found.', 'listdom'),
                'page' => __('Page %1$d of %2$d', 'listdom'),
                'linkedTo' => __('Linked to %s', 'listdom'),
                'noHistory' => __('No recent Business Data activity.', 'listdom'),
                'loadingHistory' => __('Loading history…', 'listdom'),
                'fetching' => __('Fetching %1$d–%2$d of %3$d listings…', 'listdom'),
                'fetch' => __('Fetch Business Data and review', 'listdom'),
                'resume' => __('Resume remaining batches', 'listdom'),
                'noneSaved' => __('No listings were saved.', 'listdom'),
                'saved' => __('%d listings reviewed and saved.', 'listdom'),
                'skipped' => __('%d listings were skipped because their data changed, they are locked, or the business is already linked elsewhere.', 'listdom'),
                'skip' => __('Skip this listing', 'listdom'),
                'match' => __('Business match', 'listdom'),
                'rematch' => __('Match this listing again', 'listdom'),
                'applyColumn' => __('Apply', 'listdom'),
                'currentColumn' => __('Current', 'listdom'),
                'sourceColumn' => __('Business Data', 'listdom'),
                'primary' => __('primary', 'listdom'),
                'noDifferences' => __('No field differences found. You can still confirm the business link.', 'listdom'),
                'closed' => __('Source reports this business as %s. The listing will not be unpublished or deleted.', 'listdom'),
            ],
        ]);
    }

    public function tab(string $current): void
    {
        if (!LSD_Webilia_Connect::enabled()) return;
        $active = $current === self::TAB;
        $mode = isset($_GET['mode']) ? sanitize_key(wp_unslash($_GET['mode'])) : 'find';
        if (!in_array($mode, ['find', 'enrich', 'refresh', 'history'], true)) $mode = 'find';

        echo '<li class="lsd-has-children lsd-business-data-nav' . ($active ? ' lsd-nav-expanded' : '') . '">';
        echo '<a class="lsd-nav-tab ' . ($active ? 'lsd-nav-tab-active' : '') . '" href="' . esc_url(admin_url('admin.php?page=listdom-ix&tab=' . self::TAB . '&mode=find')) . '">';
        echo '<i class="webilia-icon wbli-database lsd-m-0"></i>' . esc_html__('Business Data', 'listdom') . '</a>';
        echo '<ul class="lsd-nav-sub-tabs" data-parent="business-data"' . ($active ? '' : ' hidden') . '>';
        foreach (['find' => __('Find Businesses', 'listdom'), 'enrich' => __('Enrich Listings', 'listdom'), 'refresh' => __('Refresh Listings', 'listdom'), 'history' => __('History', 'listdom')] as $key => $label)
        {
            echo '<li class="lsd-business-data-sub-tab' . ($active && $mode === $key ? ' lsd-nav-tab-active' : '') . '"><a' . ($active && $mode === $key ? ' aria-current="page"' : '') . ' href="' . esc_url(admin_url('admin.php?page=listdom-ix&tab=' . self::TAB . '&mode=' . $key)) . '">' . esc_html($label) . '</a></li>';
        }
        echo '</ul></li>';
    }

    public function content(string $tab): void
    {
        if ($tab === self::TAB) $this->include_html_file('menus/ix/tabs/business-data.php');
    }

    public function balance(): void
    {
        $this->guard();

        try
        {
            $balance = LSD_Webilia_Connect::creditBalance();
            $this->respond(['success' => 1, 'credits_balance' => $this->credit_balance($balance)]);
        }
        catch (Throwable $e)
        {
            $this->failure($e);
        }
    }

    public function categories(): void
    {
        $this->guard();

        $q = isset($_POST['q']) ? sanitize_text_field(wp_unslash($_POST['q'])) : '';
        $parent = isset($_POST['parent']) ? sanitize_key(wp_unslash($_POST['parent'])) : '';
        $cursor = isset($_POST['cursor']) ? sanitize_text_field(wp_unslash($_POST['cursor'])) : '';
        $limit = max(1, min(100, (int) ($_POST['limit'] ?? 50)));
        $refresh = !empty($_POST['refresh']);
        $query = array_filter(['q' => $q !== '' ? $q : null, 'parent' => $parent !== '' ? $parent : null, 'cursor' => $cursor !== '' ? $cursor : null, 'limit' => $limit], static fn($value) => $value !== null);
        $version = (string) get_transient(self::CATEGORY_VERSION_TRANSIENT);
        $key = self::CATEGORY_TRANSIENT_PREFIX . md5($version . '|' . wp_json_encode($query));

        $cached = !$refresh && $version !== '' ? get_transient($key) : false;
        if (is_array($cached)) $this->respond(['success' => 1, 'data' => $cached]);

        try
        {
            $data = LSD_Webilia_Connect::overtureCategories($query);
            $taxonomyVersion = sanitize_text_field((string) ($data['taxonomy_version'] ?? ''));
            if ($taxonomyVersion !== '')
            {
                set_transient(self::CATEGORY_VERSION_TRANSIENT, $taxonomyVersion, self::CATEGORY_CACHE_TTL);
                set_transient(self::CATEGORY_TRANSIENT_PREFIX . md5($taxonomyVersion . '|' . wp_json_encode($query)), $data, self::CATEGORY_CACHE_TTL);
            }

            $this->respond(['success' => 1, 'data' => $data]);
        }
        catch (Throwable $e)
        {
            $this->failure($e);
        }
    }

    public function search(): void
    {
        $this->guard();

        $categories = isset($_POST['categories']) ? (array) wp_unslash($_POST['categories']) : [];
        $categories = array_values(array_unique(array_filter(array_map('sanitize_key', $categories))));
        $lat = isset($_POST['latitude']) ? (float) wp_unslash($_POST['latitude']) : null;
        $lng = isset($_POST['longitude']) ? (float) wp_unslash($_POST['longitude']) : null;
        $radius = isset($_POST['radius_km']) ? (float) wp_unslash($_POST['radius_km']) : 0;
        $limit = isset($_POST['limit']) ? (int) wp_unslash($_POST['limit']) : 0;
        $key = isset($_POST['idempotency_key']) ? sanitize_text_field(wp_unslash($_POST['idempotency_key'])) : '';

        if (count($categories) < 1 || count($categories) > 5 || $lat === null || $lat < -90 || $lat > 90 || $lng === null || $lng < -180 || $lng > 180 || $radius <= 0 || $radius > 50 || $limit < 1 || $limit > 100 || !preg_match('/^[0-9a-f-]{36}$/i', $key))
        {
            $this->respond(['success' => 0, 'message' => esc_html__('Please provide one to five categories, a valid map area, and a result limit between 1 and 100.', 'listdom')], 422);
        }

        // The API reserves credits for the requested limit before running the search.
        // If the balance check is unavailable, let the API make the authoritative decision.
        $available = null;
        $balanceText = null;
        try
        {
            $balance = LSD_Webilia_Connect::creditBalance();
            $balanceText = $this->credit_balance($balance);
            $available = max(0, (int) $balanceText);
        }
        catch (Throwable $balanceError)
        {
            // The search API remains authoritative if this read-only balance check fails.
        }

        if ($available !== null)
        {
            if ($available === 0) $this->respond(['success' => 0, 'message' => esc_html__('A Business Data search requires at least one Webilia Credit.', 'listdom'), 'credits_balance' => $balanceText], 402);
            $limit = min($limit, 100, $available * self::RESULTS_PER_CREDIT);
        }

        try
        {
            $data = LSD_Webilia_Connect::overturePlacesSearch([
                'idempotency_key' => $key,
                'categories' => $categories,
                'area' => ['latitude' => $lat, 'longitude' => $lng, 'radius_km' => $radius],
                'limit' => $limit,
            ]);

            $history = $this->pruned_history();
            $entry = [
                'id' => wp_generate_uuid4(), 'created_at' => time(),
                'request' => ['categories' => $categories, 'latitude' => $lat, 'longitude' => $lng, 'radius_km' => $radius, 'limit' => $limit],
                'places' => array_values((array) ($data['places'] ?? [])), 'meta' => (array) ($data['meta'] ?? []), 'imports' => [],
            ];

            array_unshift($history, $entry);
            update_option(self::HISTORY_OPTION, array_slice($history, 0, self::HISTORY_LIMIT), false);
            $this->can_delete_history($history);

            $this->respond(['success' => 1, 'entry' => $entry]);
        }
        catch (Throwable $e)
        {
            if ((int) $e->getCode() === 402)
            {
                $required = (int) ceil($limit / self::RESULTS_PER_CREDIT);
                $available = null;
                $balanceText = null;
                try
                {
                    $balance = LSD_Webilia_Connect::creditBalance();
                    $balanceText = $this->credit_balance($balance);
                    $available = max(0, (int) $balanceText);
                    $maximum = min(100, $available * self::RESULTS_PER_CREDIT);
                    $message = $maximum > 0
                        ? sprintf(__('Requesting up to %1$d results requires %2$d Webilia Credits, but you have %3$s. Reduce Maximum results to %4$d or less.', 'listdom'), $limit, $required, $balanceText, $maximum)
                        : sprintf(__('Requesting up to %1$d results requires %2$d Webilia Credits, but your balance is %3$s. Add credits before searching again.', 'listdom'), $limit, $required, $balanceText);
                }
                catch (Throwable $balanceError)
                {
                    $message = sprintf(__('Requesting up to %1$d results requires %2$d Webilia Credits, but your balance is insufficient. Reduce Maximum results and try again.', 'listdom'), $limit, $required);
                }

                $response = ['success' => 0, 'message' => $message];
                if ($balanceText !== null) $response['credits_balance'] = $balanceText;
                $this->respond($response, 402);
            }
            $this->failure($e);
        }
    }

    public function history(): void
    {
        $this->guard();
        $history = $this->pruned_history();
        $this->respond(['success' => 1, 'history' => $history, 'can_delete_history' => $this->can_delete_history($history)]);
    }

    public function terms(): void
    {
        $this->guard();

        $result = [];
        foreach (self::TERM_TAXONOMIES as $key => $taxonomy)
        {
            $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC']);
            if (is_wp_error($terms)) $this->respond(['success' => 0, 'message' => esc_html__('Listing terms could not be loaded.', 'listdom')], 500);

            $byId = [];
            foreach ($terms as $term) $byId[$term->term_id] = $term;

            $result[$key] = [];
            foreach ($terms as $term)
            {
                $label = $term->name;
                $parent = (int) $term->parent;

                $depth = 0;
                while ($parent && isset($byId[$parent]) && $depth++ < 10)
                {
                    $label = $byId[$parent]->name . ' › ' . $label;
                    $parent = (int) $byId[$parent]->parent;
                }

                $result[$key][] = ['id' => (int) $term->term_id, 'label' => $label];
            }
        }

        $this->respond(['success' => 1, 'terms' => $result]);
    }

    public function clear_history(): void
    {
        $this->guard();

        $history = $this->pruned_history();
        if (!$this->can_delete_history($history)) $this->respond(['success' => 0, 'message' => esc_html__('Could not update search history.', 'listdom')], 403);

        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';
        if ($id === '') delete_option(self::HISTORY_OPTION);
        else update_option(self::HISTORY_OPTION, array_values(array_filter($history, static fn(array $entry): bool => ($entry['id'] ?? '') !== $id)), false);

        $this->respond(['success' => 1]);
    }

    public function save_preferences(): void
    {
        $this->guard();

        $fields = ['latitude', 'longitude', 'marker_latitude', 'marker_longitude', 'zoom', 'radius_km', 'limit'];
        $values = [];
        foreach ($fields as $field)
        {
            $value = isset($_POST[$field]) ? wp_unslash($_POST[$field]) : null;
            if (!is_scalar($value) || !is_numeric($value)) $this->respond(['success' => 0], 422);
            $values[$field] = (float) $value;
        }

        if ($values['latitude'] < -90 || $values['latitude'] > 90 || $values['longitude'] < -180 || $values['longitude'] > 180
            || $values['marker_latitude'] < -90 || $values['marker_latitude'] > 90 || $values['marker_longitude'] < -180 || $values['marker_longitude'] > 180
            || $values['zoom'] < 1 || $values['zoom'] > 20 || $values['radius_km'] <= 0 || $values['radius_km'] > 50
            || $values['limit'] < 1 || $values['limit'] > 100 || floor($values['limit']) !== $values['limit']) $this->respond(['success' => 0], 422);

        $showResults = isset($_POST['show_results']) ? wp_unslash($_POST['show_results']) : null;
        $values['show_results'] = is_scalar($showResults) && (string) $showResults === '1' ? 1 : 0;
        $values['limit_default_version'] = 2;
        update_user_meta(get_current_user_id(), self::PREFERENCES_META, $values);

        $this->respond(['success' => 1]);
    }

    public function import(): void
    {
        $this->guard();

        $historyId = isset($_POST['history_id']) ? sanitize_text_field(wp_unslash($_POST['history_id'])) : '';
        $ids = isset($_POST['place_ids']) ? array_slice(array_values(array_unique(array_filter(array_map('sanitize_text_field', (array) wp_unslash($_POST['place_ids']))))), 0, 20) : [];
        $autoCategory = !isset($_POST['auto_category']) || (string) wp_unslash($_POST['auto_category']) === '1';
        $enrichExisting = isset($_POST['enrich_existing']) && (string) wp_unslash($_POST['enrich_existing']) === '1';
        $submittedTerms = isset($_POST['terms']) && is_array($_POST['terms']) ? wp_unslash($_POST['terms']) : [];

        $selectedTerms = [];
        foreach (self::TERM_TAXONOMIES as $key => $taxonomy)
        {
            $termIds = $autoCategory && $key === 'categories' ? [] : array_values(array_unique(array_filter(array_map('absint', (array) ($submittedTerms[$key] ?? [])))));
            foreach ($termIds as $termId)
            {
                $term = get_term($termId, $taxonomy);
                if (!$term || is_wp_error($term)) $this->respond(['success' => 0, 'message' => esc_html__('One or more selected listing terms are unavailable.', 'listdom')], 422);
            }

            $selectedTerms[$key] = $termIds;
        }

        if (!$autoCategory && !$selectedTerms['categories']) $this->respond(['success' => 0, 'message' => esc_html__('Choose at least one category, or enable automatic category assignment.', 'listdom')], 422);

        $history = $this->pruned_history();
        $index = array_search($historyId, array_column($history, 'id'), true);
        if ($index === false || $ids === []) $this->respond(['success' => 0, 'message' => esc_html__('Select at least one saved search result.', 'listdom')], 422);

        $places = [];
        foreach ((array) ($history[$index]['places'] ?? []) as $place) if (is_array($place) && in_array((string) ($place['id'] ?? ''), $ids, true)) $places[] = $place;

        $created = [];
        $updated = [];
        $skipped = [];
        $categoryIds = [];

        foreach ($places as $place)
        {
            $placeId = sanitize_text_field((string) ($place['id'] ?? ''));
            $title = sanitize_text_field($this->decode_business_title((string) ($place['name'] ?? '')));
            if ($placeId === '' || $title === '')
            {
                $skipped[] = ['id' => $placeId, 'reason' => esc_html__('This result has no usable name or provider ID.', 'listdom')];
                continue;
            }

            $details = $this->place_details($place);
            $existing = $this->existing_listing($placeId);
            if ($existing)
            {
                if ($enrichExisting && $this->enrich_existing($existing, $details)) $updated[] = ['id' => $placeId, 'post_id' => $existing, 'edit_url' => get_edit_post_link($existing, 'raw')];
                else $skipped[] = ['id' => $placeId, 'reason' => $enrichExisting ? esc_html__('This business is already imported and has no empty fields to fill.', 'listdom') : esc_html__('This business is already imported.', 'listdom'), 'edit_url' => get_edit_post_link($existing, 'raw')];
                continue;
            }

            $listingCategories = $selectedTerms['categories'];
            $listingCategoryCodes = [];
            if ($autoCategory)
            {
                $code = $details['primary_category'] ?: sanitize_key((string) ($place['category'] ?? ''));
                if ($code === '')
                {
                    $skipped[] = ['id' => $placeId, 'reason' => esc_html__('This business has no category to assign.', 'listdom')];
                    continue;
                }

                if (!isset($categoryIds[$code])) $categoryIds[$code] = $this->business_category_term($code);
                if (!$categoryIds[$code])
                {
                    $skipped[] = ['id' => $placeId, 'reason' => esc_html__('The business category could not be created.', 'listdom')];
                    continue;
                }

                $listingCategories = [$categoryIds[$code]];
                $listingCategoryCodes[(string) $categoryIds[$code]] = $code;
                foreach ($details['alternate_categories'] as $alternate)
                {
                    if (!isset($categoryIds[$alternate])) $categoryIds[$alternate] = $this->business_category_term($alternate);
                    if ($categoryIds[$alternate] && !in_array($categoryIds[$alternate], $listingCategories, true))
                    {
                        $listingCategories[] = $categoryIds[$alternate];
                        $listingCategoryCodes[(string) $categoryIds[$alternate]] = $alternate;
                    }
                }
            }

            $postId = wp_insert_post(['post_type' => LSD_Base::PTYPE_LISTING, 'post_status' => 'draft', 'post_title' => $title, 'post_author' => get_current_user_id()], true);
            if (is_wp_error($postId))
            {
                $skipped[] = ['id' => $placeId, 'reason' => $postId->get_error_message()];
                continue;
            }

            (new LSD_Entity_Listing($postId))->save([
                'object_type' => 'marker', 'address' => $details['address'],
                'latitude' => (float) ($place['latitude'] ?? 0), 'longitude' => (float) ($place['longitude'] ?? 0),
                'phone' => $details['phones'][0] ?? '', 'website' => $details['websites'][0] ?? '', 'email' => $details['emails'][0] ?? '',
                'sc' => $details['social_channels'],
                'listing_category' => $listingCategories[0], 'listing_categories' => array_slice($listingCategories, 1),
            ], false);

            foreach (['locations', 'features', 'tags', 'labels'] as $key)
            {
                if ($selectedTerms[$key]) wp_set_object_terms($postId, $selectedTerms[$key], self::TERM_TAXONOMIES[$key]);
            }

            update_post_meta($postId, 'lsd_business_data_place_id', $placeId);
            update_post_meta($postId, 'lsd_business_data_metadata', array_merge($details['metadata'], [
                'source' => sanitize_text_field((string) ($history[$index]['meta']['source'] ?? '')), 'attribution' => sanitize_text_field((string) ($history[$index]['meta']['attribution'] ?? '')),
                'provider_release' => sanitize_text_field((string) ($history[$index]['meta']['provider_release'] ?? '')), 'search_id' => sanitize_text_field((string) ($history[$index]['meta']['search_id'] ?? '')),
                'category_term_codes' => $listingCategoryCodes,
                'last_checked_at' => gmdate('c'), 'last_synced_at' => gmdate('c'),
            ]));

            $created[] = ['id' => $placeId, 'post_id' => $postId, 'edit_url' => get_edit_post_link($postId, 'raw')];
        }

        $history[$index]['imports'] = array_merge((array) ($history[$index]['imports'] ?? []), $created, $updated, $skipped);
        update_option(self::HISTORY_OPTION, $history, false);

        $this->respond(['success' => 1, 'created' => $created, 'updated' => $updated, 'skipped' => $skipped]);
    }

    public function register_metabox(WP_Post $post): void
    {
        if (!current_user_can('manage_options') || !LSD_Webilia_Connect::enabled()) return;

        add_meta_box('lsd_metabox_business_data', esc_html__('Webilia Business Data', 'listdom'), [$this, 'metabox'], LSD_Base::PTYPE_LISTING, 'side', 'default');
    }

    public function metabox($post): void
    {
        if (!current_user_can('manage_options') || !$post instanceof WP_Post) return;

        $placeId = (string) get_post_meta($post->ID, 'lsd_business_data_place_id', true);
        $mode = $placeId === '' ? 'enrich' : 'refresh';
        if ($this->maintenance_eligible($post->ID, $mode)) echo '<p><a class="button" href="' . esc_url(admin_url('admin.php?page=listdom-ix&tab=business-data&mode=' . $mode . '&listing_ids=' . $post->ID)) . '">' . esc_html($placeId === '' ? __('Enrich with Business Data', 'listdom') : __('Refresh Business Data', 'listdom')) . '</a></p>';
        if ($placeId === '') return;

        $meta = (array) get_post_meta($post->ID, 'lsd_business_data_metadata', true);
        echo '<div class="lsd-business-data-metabox">';
        $this->metabox_text(__('Provider ID', 'listdom'), $placeId);
        foreach (['source' => __('Source', 'listdom'), 'attribution' => __('Attribution', 'listdom'), 'provider_release' => __('Provider release', 'listdom'), 'search_id' => __('Search ID', 'listdom')] as $key => $label) if (!empty($meta[$key])) $this->metabox_text($label, (string) $meta[$key]);
        foreach (['last_checked_at' => __('Last checked', 'listdom'), 'last_synced_at' => __('Last synced', 'listdom')] as $key => $label) if (!empty($meta[$key])) $this->metabox_text($label, (string) $meta[$key]);

        if (!empty($meta['place_version'])) $this->metabox_text(__('Place version', 'listdom'), (string) $meta['place_version']);
        if (!empty($meta['brand']['name'])) $this->metabox_text(__('Brand', 'listdom'), (string) $meta['brand']['name']);

        $statuses = ['open' => __('Open', 'listdom'), 'temporarily_closed' => __('Temporarily closed', 'listdom'), 'permanently_closed' => __('Permanently closed', 'listdom')];
        if (!empty($meta['operating_status'])) $this->metabox_text(__('Operating status', 'listdom'), $statuses[$meta['operating_status']] ?? (string) $meta['operating_status']);
        if (isset($meta['confidence'])) $this->metabox_text(__('Existence confidence', 'listdom'), number_format_i18n((float) $meta['confidence'] * 100) . '%');

        $address = $meta['addresses'][0] ?? [];
        if (is_array($address)) foreach (['locality' => __('Locality', 'listdom'), 'postcode' => __('Postcode', 'listdom'), 'region' => __('Region', 'listdom'), 'country' => __('Country', 'listdom')] as $key => $label) if (!empty($address[$key])) $this->metabox_text($label, (string) $address[$key]);

        foreach (['phones' => __('Phone numbers', 'listdom'), 'emails' => __('Email addresses', 'listdom')] as $key => $label) if (!empty($meta[$key]) && is_array($meta[$key])) $this->metabox_text($label, implode(', ', array_filter($meta[$key], 'is_scalar')));

        foreach (['websites' => __('Websites', 'listdom'), 'socials' => __('Social profiles', 'listdom')] as $key => $label) if (!empty($meta[$key]) && is_array($meta[$key]))
        {
            echo '<div class="lsd-business-data-metabox-field"><strong>' . esc_html($label) . '</strong><div class="lsd-business-data-metabox-links">';
            foreach ($meta[$key] as $url) if (is_string($url) && esc_url($url)) echo '<a href="' . esc_url($url) . '" target="_blank" rel="nofollow noopener noreferrer">' . esc_html($url) . '</a>';
            echo '</div></div>';
        }

        echo '</div>';
    }

    private function metabox_text(string $label, string $value): void
    {
        if ($value === '') return;
        echo '<div class="lsd-business-data-metabox-field"><strong>' . esc_html($label) . '</strong><span>' . esc_html($value) . '</span></div>';
    }

    public function listing_row_actions(array $actions, WP_Post $post): array
    {
        if ($post->post_type !== LSD_Base::PTYPE_LISTING || !current_user_can('manage_options') || !LSD_Webilia_Connect::enabled()) return $actions;
        $linked = (string) get_post_meta($post->ID, 'lsd_business_data_place_id', true) !== '';
        $mode = $linked ? 'refresh' : 'enrich';
        if (!$this->maintenance_eligible($post->ID, $mode)) return $actions;
        $actions['lsd-business-data'] = '<a href="' . esc_url(admin_url('admin.php?page=listdom-ix&tab=business-data&mode=' . $mode . '&listing_ids=' . $post->ID)) . '">'
            . esc_html($linked ? __('Refresh Business Data', 'listdom') : __('Enrich with Business Data', 'listdom')) . '</a>';

        return $actions;
    }

    public function listing_bulk_actions(array $actions): array
    {
        if (current_user_can('manage_options') && LSD_Webilia_Connect::enabled())
        {
            $actions['lsd_business_data_enrich'] = __('Enrich with Business Data', 'listdom');
            $actions['lsd_business_data_refresh'] = __('Refresh Business Data', 'listdom');
        }

        return $actions;
    }

    public function handle_listing_bulk_action(string $redirect, string $action, array $postIds): string
    {
        if (!in_array($action, ['lsd_business_data_enrich', 'lsd_business_data_refresh'], true) || !current_user_can('manage_options') || !LSD_Webilia_Connect::enabled()) return $redirect;
        $ids = array_values(array_unique(array_filter(array_map('absint', $postIds))));
        $mode = $action === 'lsd_business_data_enrich' ? 'enrich' : 'refresh';
        $ids = array_values(array_filter($ids, fn(int $id): bool => $this->maintenance_eligible($id, $mode)));
        $ids = array_slice($ids, 0, 100);

        return admin_url('admin.php?page=listdom-ix&tab=business-data&mode=' . $mode . '&listing_ids=' . implode(',', $ids));
    }

    public function maintenance_list(): void
    {
        $this->guard();
        $mode = isset($_POST['mode']) ? sanitize_key(wp_unslash($_POST['mode'])) : '';
        if (!in_array($mode, ['enrich', 'refresh'], true)) $this->respond(['success' => 0], 422);
        $page = max(1, (int) ($_POST['page'] ?? 1));
        $search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $query = new WP_Query([
            'post_type' => LSD_Base::PTYPE_LISTING, 'post_status' => ['draft', 'pending', 'publish', 'future', 'private'],
            'posts_per_page' => -1, 's' => $search, 'no_found_rows' => true,
            'orderby' => 'title', 'order' => 'ASC', 'fields' => 'ids',
        ]);
        $eligibleIds = [];
        foreach ($query->posts as $postId)
        {
            $postId = (int) $postId;
            if (!$this->maintenance_eligible($postId, $mode)) continue;
            $eligibleIds[] = $postId;
        }
        $listings = [];
        foreach (array_slice($eligibleIds, ($page - 1) * 100, 100) as $postId)
        {
            $listings[] = ['id' => $postId, 'title' => $this->maintenance_title($postId), 'address' => (string) get_post_meta($postId, 'lsd_address', true), 'place_id' => (string) get_post_meta($postId, 'lsd_business_data_place_id', true)];
        }
        $selectedIds = isset($_POST['selected_ids']) ? array_slice(array_values(array_unique(array_filter(array_map('absint', (array) wp_unslash($_POST['selected_ids']))))), 0, 100) : [];
        $selected = [];
        foreach ($selectedIds as $postId)
        {
            if (get_post_type($postId) === LSD_Base::PTYPE_LISTING && $this->maintenance_eligible($postId, $mode)) $selected[] = ['id' => $postId, 'title' => $this->maintenance_title($postId)];
        }
        $this->respond(['success' => 1, 'listings' => $listings, 'selected' => $selected, 'pages' => max(1, (int) ceil(count($eligibleIds) / 100))]);
    }

    public function maintenance_quote(): void
    {
        $this->guard();
        $mode = isset($_POST['mode']) ? sanitize_key(wp_unslash($_POST['mode'])) : '';
        $count = (int) ($_POST['count'] ?? 0);
        if (!in_array($mode, ['enrich', 'refresh'], true) || $count < 1 || $count > 100) $this->respond(['success' => 0], 422);
        try
        {
            $quote = LSD_Webilia_Connect::businessDataMaintenance('quote', ['operation' => $mode === 'enrich' ? 'match' : 'lookup', 'count' => $count]);
            $this->respond(['success' => 1, 'quote' => $quote]);
        }
        catch (Throwable $e) { $this->maintenance_failure($e); }
    }

    public function maintenance_fetch(): void
    {
        $this->guard();
        $mode = isset($_POST['mode']) ? sanitize_key(wp_unslash($_POST['mode'])) : '';
        $ids = isset($_POST['listing_ids']) ? array_values(array_unique(array_filter(array_map('absint', (array) wp_unslash($_POST['listing_ids']))))) : [];
        $key = isset($_POST['idempotency_key']) ? sanitize_text_field(wp_unslash($_POST['idempotency_key'])) : '';
        if (!in_array($mode, ['enrich', 'refresh'], true) || count($ids) < 1 || count($ids) > 20 || !preg_match('/^[0-9a-f-]{36}$/i', $key)) $this->respond(['success' => 0], 422);

        $listings = [];
        $placeIds = [];
        foreach ($ids as $id)
        {
            $post = get_post($id);
            if (!$post || $post->post_type !== LSD_Base::PTYPE_LISTING || !$this->maintenance_eligible($id, $mode)) continue;
            $placeId = (string) get_post_meta($id, 'lsd_business_data_place_id', true);
            $current = $this->maintenance_fields($id);
            $listings[$id] = ['id' => $id, 'title' => $this->maintenance_title($id), 'place_id' => $placeId, 'current' => $current];
            if ($mode === 'refresh') $placeIds[] = $placeId;
        }
        if (!$listings) $this->respond(['success' => 0, 'message' => esc_html__('No eligible listings were selected.', 'listdom')], 422);

        try
        {
            $request = ['idempotency_key' => $key];
            if ($mode === 'enrich')
            {
                $request['listings'] = [];
                foreach ($listings as $listing)
                {
                    $entry = ['reference' => (string) $listing['id'], 'name' => $listing['title'], 'address' => $listing['current']['address']];
                    $metadata = (array) get_post_meta($listing['id'], 'lsd_business_data_metadata', true);
                    $category = sanitize_key((string) ($metadata['primary_category'] ?? $metadata['category'] ?? ''));
                    if ($category === '')
                    {
                        $slugs = wp_get_post_terms($listing['id'], LSD_Base::TAX_CATEGORY, ['fields' => 'slugs']);
                        if (!is_wp_error($slugs) && $slugs) $category = sanitize_key(str_replace('-', '_', (string) $slugs[0]));
                    }
                    if ($category !== '') $entry['category'] = $category;
                    if ($this->maintenance_valid_coordinates($listing['current']['latitude'], $listing['current']['longitude']))
                    {
                        $entry['latitude'] = (float) $listing['current']['latitude'];
                        $entry['longitude'] = (float) $listing['current']['longitude'];
                    }
                    $request['listings'][] = $entry;
                }
            }
            else $request['place_ids'] = $placeIds;

            $result = LSD_Webilia_Connect::businessDataMaintenance($mode === 'enrich' ? 'match' : 'lookup', $request);
            $preview = [];
            foreach ($listings as $listing)
            {
                $id = $listing['id'];
                $candidates = $mode === 'enrich' ? (array) ($result['matches'][(string) $id] ?? []) : [];
                if ($mode === 'refresh' && isset($result['places'][$listing['place_id']])) $candidates = [['place' => $result['places'][$listing['place_id']], 'score' => 1]];
                foreach ($candidates as &$candidate)
                {
                    if (isset($candidate['place']['name'])) $candidate['place']['name'] = $this->decode_business_title((string) $candidate['place']['name']);
                    $candidate['fields'] = $this->maintenance_source_fields((array) ($candidate['place'] ?? []));
                }
                unset($candidate);
                $preview[$id] = ['listing' => $listing, 'candidates' => $candidates];
                $metadata = (array) get_post_meta($id, 'lsd_business_data_metadata', true);
                $metadata['last_checked_at'] = gmdate('c');
                $metadata['last_check_provider_release'] = sanitize_text_field((string) ($result['meta']['provider_release'] ?? ''));
                update_post_meta($id, 'lsd_business_data_metadata', $metadata);
            }
            $token = wp_generate_uuid4();
            set_transient('lsd_bd_preview_' . get_current_user_id() . '_' . $token, ['mode' => $mode, 'entries' => $preview, 'provider_release' => $result['meta']['provider_release'] ?? ''], 1800);
            $this->record_maintenance_history(['created_at' => time(), 'operation' => $mode, 'count' => count($listings), 'credits_charged' => !empty($result['meta']['replayed']) ? '0.000' : ($result['meta']['credits_charged'] ?? '0.000')]);
            $this->respond(['success' => 1, 'token' => $token, 'entries' => $preview, 'meta' => $result['meta'] ?? []]);
        }
        catch (Throwable $e) { $this->maintenance_failure($e); }
    }

    public function maintenance_apply(): void
    {
        $this->guard();
        $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';
        $choices = isset($_POST['choices']) && is_array($_POST['choices']) ? wp_unslash($_POST['choices']) : [];
        $preview = preg_match('/^[0-9a-f-]{36}$/i', $token) ? get_transient('lsd_bd_preview_' . get_current_user_id() . '_' . $token) : false;
        if (!is_array($preview) || count($choices) > 20) $this->respond(['success' => 0, 'message' => esc_html__('The review expired. Fetch current Business Data again.', 'listdom')], 422);

        $updated = [];
        $skipped = [];
        foreach ($choices as $choice)
        {
            $id = absint($choice['listing_id'] ?? 0);
            $placeId = sanitize_text_field((string) ($choice['place_id'] ?? ''));
            $fields = array_values(array_intersect((array) ($choice['fields'] ?? []), ['phone', 'email', 'website', 'address', 'coordinates', 'socials', 'categories', 'brand', 'operating_status']));
            $entry = $preview['entries'][$id] ?? null;
            $post = get_post($id);
            if (!$entry || !$post || $post->post_type !== LSD_Base::PTYPE_LISTING || !$this->maintenance_eligible($id, $preview['mode'])) { $skipped[] = $id; continue; }
            if ($this->maintenance_fields($id) !== $entry['listing']['current']) { $skipped[] = $id; continue; }
            $candidate = null;
            foreach ($entry['candidates'] as $possible) if ((string) ($possible['place']['id'] ?? '') === $placeId) $candidate = $possible['place'];
            if (!$candidate || ($preview['mode'] === 'refresh' && (string) get_post_meta($id, 'lsd_business_data_place_id', true) !== $placeId)) { $skipped[] = $id; continue; }
            if ($preview['mode'] === 'enrich' && (string) get_post_meta($id, 'lsd_business_data_place_id', true) !== (string) $entry['listing']['place_id']) { $skipped[] = $id; continue; }
            if ($this->existing_listing($placeId, $id)) { $skipped[] = $id; continue; }

            $changed = $this->apply_maintenance_fields($id, $candidate, $fields);
            $linked = $preview['mode'] === 'enrich' && (string) get_post_meta($id, 'lsd_business_data_place_id', true) !== $placeId;
            if ($linked) update_post_meta($id, 'lsd_business_data_place_id', $placeId);
            $changed = $changed || $linked;
            $sourceDetails = $this->place_details($candidate);
            $sourceMetadata = $sourceDetails['metadata'];
            $metadata = (array) get_post_meta($id, 'lsd_business_data_metadata', true);
            $metadataFields = [
                'phone' => ['phones'], 'email' => ['emails'], 'website' => ['websites'], 'address' => ['addresses'],
                'socials' => ['socials'], 'categories' => ['category', 'primary_category', 'taxonomy', 'alternate_categories'],
                'brand' => ['brand'], 'operating_status' => ['operating_status'],
            ];
            foreach ($fields as $field) foreach ($metadataFields[$field] ?? [] as $key)
            {
                $value = $sourceMetadata[$key] ?? null;
                if ($value !== null && $value !== '' && $value !== [] && $value !== ['name' => '', 'wikidata' => '']) $metadata[$key] = $value;
            }
            if (in_array('address', $fields, true) && empty($sourceMetadata['addresses']) && $sourceDetails['address'] !== '') unset($metadata['addresses']);
            if (in_array('categories', $fields, true) && !empty($sourceMetadata['category']) && empty($sourceMetadata['primary_category'])) unset($metadata['primary_category']);
            if (isset($sourceMetadata['confidence'])) $metadata['confidence'] = $sourceMetadata['confidence'];
            $metadata['source'] = sanitize_text_field((string) ($candidate['source'] ?? 'Overture Maps Places'));
            $metadata['attribution'] = sanitize_text_field((string) ($candidate['attribution'] ?? '© Overture Maps Foundation'));
            $metadata['provider_release'] = sanitize_text_field((string) $preview['provider_release']);
            $metadata['place_version'] = isset($candidate['place_version']) ? absint($candidate['place_version']) : null;
            if ($changed) $metadata['last_synced_at'] = gmdate('c');
            update_post_meta($id, 'lsd_business_data_metadata', $metadata);
            $updated[] = ['id' => $id, 'changed' => $changed];
        }
        delete_transient('lsd_bd_preview_' . get_current_user_id() . '_' . $token);
        $this->record_maintenance_history(['created_at' => time(), 'operation' => 'apply', 'count' => count($updated), 'credits_charged' => '0.000']);
        $this->respond(['success' => 1, 'updated' => $updated, 'skipped' => $skipped]);
    }

    public function maintenance_history(): void
    {
        $this->guard();
        $history = (array) get_option(self::MAINTENANCE_HISTORY_OPTION, []);
        foreach ($this->pruned_history() as $entry)
        {
            $history[] = [
                'created_at' => $entry['created_at'], 'operation' => 'search',
                'count' => $entry['meta']['result_count'] ?? count((array) ($entry['places'] ?? [])),
                'credits_charged' => $entry['meta']['credits_charged'] ?? null,
                'import_count' => count((array) ($entry['imports'] ?? [])),
            ];
        }
        usort($history, static fn(array $a, array $b): int => (int) $b['created_at'] <=> (int) $a['created_at']);
        $this->respond(['success' => 1, 'history' => $history]);
    }

    private function maintenance_fields(int $id): array
    {
        $metadata = (array) get_post_meta($id, 'lsd_business_data_metadata', true);
        $socials = [];
        foreach (['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'tiktok'] as $network) $socials[$network] = (string) get_post_meta($id, 'lsd_' . $network, true);

        $categoryTerms = wp_get_post_terms($id, LSD_Base::TAX_CATEGORY);
        if (is_wp_error($categoryTerms)) $categoryTerms = [];
        $categoryCodes = (array) ($metadata['category_term_codes'] ?? []);
        $storedCategories = array_filter(array_merge([(string) ($metadata['primary_category'] ?? $metadata['category'] ?? '')], (array) ($metadata['alternate_categories'] ?? [])));
        $categories = array_map(static function ($term) use ($categoryCodes, $storedCategories): string
        {
            if (isset($categoryCodes[(string) $term->term_id])) return sanitize_key((string) $categoryCodes[(string) $term->term_id]);
            $nameCode = str_replace('-', '_', sanitize_title($term->name));
            if (in_array($nameCode, $storedCategories, true)) return $nameCode;
            return str_replace('-', '_', (string) $term->slug);
        }, $categoryTerms);
        $categoryLabels = array_map(static function ($term): string { return (string) $term->name; }, $categoryTerms);
        $primarySlug = (string) get_post_meta($id, 'lsd_primary_category', true);
        $primaryCategory = str_replace('-', '_', $primarySlug);
        foreach ($categoryTerms as $index => $term) if ($term->slug === $primarySlug) { $primaryCategory = $categories[$index]; break; }

        return [
            'phone' => (string) get_post_meta($id, 'lsd_phone', true), 'email' => (string) get_post_meta($id, 'lsd_email', true),
            'website' => (string) get_post_meta($id, 'lsd_website', true), 'address' => (string) get_post_meta($id, 'lsd_address', true),
            'latitude' => (string) get_post_meta($id, 'lsd_latitude', true), 'longitude' => (string) get_post_meta($id, 'lsd_longitude', true),
            'socials' => $socials, 'categories' => $categories, 'category_labels' => $categoryLabels, 'primary_category' => $primaryCategory,
            'brand' => (string) ($metadata['brand']['name'] ?? ''), 'operating_status' => (string) ($metadata['operating_status'] ?? ''),
        ];
    }

    private function maintenance_initial_ids(): array
    {
        $mode = isset($_GET['mode']) ? sanitize_key(wp_unslash($_GET['mode'])) : '';
        if (!in_array($mode, ['enrich', 'refresh'], true) || !isset($_GET['listing_ids'])) return [];

        $ids = array_values(array_unique(array_filter(array_map('absint', explode(',', sanitize_text_field(wp_unslash($_GET['listing_ids'])))))));
        $ids = array_filter($ids, function (int $id) use ($mode): bool
        {
            $post = get_post($id);
            return $post && $post->post_type === LSD_Base::PTYPE_LISTING && $this->maintenance_eligible($id, $mode);
        });

        return array_slice(array_values($ids), 0, 100);
    }

    private function decode_business_title(string $title): string
    {
        return html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function maintenance_title(int $id): string
    {
        return $this->decode_business_title((string) get_post_field('post_title', $id, 'raw'));
    }

    private function maintenance_eligible(int $id, string $mode): bool
    {
        if (!in_array(get_post_status($id), ['draft', 'pending', 'publish', 'future', 'private'], true)) return false;
        if (get_post_meta($id, 'lsd_lock_edit', true)) return false;
        if ($mode === 'refresh') return (string) get_post_meta($id, 'lsd_business_data_place_id', true) !== '';

        return trim($this->maintenance_title($id)) !== ''
            && (trim((string) get_post_meta($id, 'lsd_address', true)) !== ''
                || $this->maintenance_valid_coordinates((string) get_post_meta($id, 'lsd_latitude', true), (string) get_post_meta($id, 'lsd_longitude', true)));
    }

    private function maintenance_valid_coordinates(string $latitude, string $longitude): bool
    {
        return is_numeric($latitude) && is_numeric($longitude)
            && (float) $latitude >= -90 && (float) $latitude <= 90
            && (float) $longitude >= -180 && (float) $longitude <= 180;
    }

    private function maintenance_categories(array $details): array
    {
        $primary = $details['primary_category'] ?: ($details['metadata']['category'] ?? '');
        return array_values(array_unique(array_filter(array_merge([$primary], $details['alternate_categories']))));
    }

    private function maintenance_source_fields(array $place): array
    {
        $details = $this->place_details($place);
        $categories = $this->maintenance_categories($details);
        return [
            'phone' => $details['phones'][0] ?? '', 'email' => $details['emails'][0] ?? '',
            'website' => $details['websites'][0] ?? '', 'address' => $details['address'],
            'coordinates' => isset($place['latitude'], $place['longitude']) && is_numeric($place['latitude']) && is_numeric($place['longitude'])
                ? [(float) $place['latitude'], (float) $place['longitude']] : '',
            'socials' => array_values($details['social_channels']), 'categories' => $categories, 'primary_category' => $categories[0] ?? '',
            'brand' => $details['metadata']['brand']['name'], 'operating_status' => $details['metadata']['operating_status'],
        ];
    }

    private function apply_maintenance_fields(int $id, array $place, array $fields): bool
    {
        $details = $this->place_details($place);
        $changed = false;
        $resolvedCategoryCodes = null;
        foreach (['phone' => ['lsd_phone', $details['phones'][0] ?? ''], 'email' => ['lsd_email', $details['emails'][0] ?? ''],
            'website' => ['lsd_website', $details['websites'][0] ?? ''], 'address' => ['lsd_address', $details['address']]] as $field => [$key, $value])
        {
            if (in_array($field, $fields, true) && $value !== '' && (string) get_post_meta($id, $key, true) !== $value)
            {
                update_post_meta($id, $key, $value);
                $changed = true;
            }
        }
        if (in_array('coordinates', $fields, true) && isset($place['latitude'], $place['longitude']) && is_numeric($place['latitude']) && is_numeric($place['longitude']))
        {
            $lat = (float) $place['latitude']; $lng = (float) $place['longitude'];
            if ((string) get_post_meta($id, 'lsd_latitude', true) !== (string) $lat || (string) get_post_meta($id, 'lsd_longitude', true) !== (string) $lng)
            {
                update_post_meta($id, 'lsd_latitude', $lat);
                update_post_meta($id, 'lsd_longitude', $lng);
                (new LSD_Entity_Listing($id))->update_geopoint($id, $lat, $lng);
                $changed = true;
            }
        }
        if (in_array('socials', $fields, true) && $details['social_channels']) foreach (['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'tiktok'] as $network)
        {
            $key = 'lsd_' . $network;
            $current = (string) get_post_meta($id, $key, true);
            $source = $details['social_channels'][$network] ?? '';
            if ($current === $source) continue;
            if ($source === '') delete_post_meta($id, $key);
            else update_post_meta($id, $key, $source);
            $changed = true;
        }
        if (in_array('categories', $fields, true))
        {
            $categories = $this->maintenance_categories($details);
            $terms = [];
            $categoryCodes = [];
            foreach ($categories as $category)
            {
                $term = $this->business_category_term($category);
                if ($term)
                {
                    $terms[] = $term;
                    if (!isset($categoryCodes[(string) $term])) $categoryCodes[(string) $term] = $category;
                }
            }
            if ($categories && count($terms) === count($categories))
            {
                $terms = array_values(array_unique($terms));
                $primary = get_term($terms[0], LSD_Base::TAX_CATEGORY);
                $existing = wp_get_post_terms($id, LSD_Base::TAX_CATEGORY, ['fields' => 'ids']);
                $current = is_wp_error($existing) ? [] : array_map('intval', $existing);
                $sortedCurrent = $current; $sortedTerms = $terms;
                sort($sortedCurrent); sort($sortedTerms);
                $assigned = $sortedCurrent === $sortedTerms || !is_wp_error(wp_set_object_terms($id, $terms, LSD_Base::TAX_CATEGORY));
                if ($assigned) $resolvedCategoryCodes = $categoryCodes;
                if ($assigned && $sortedCurrent !== $sortedTerms) $changed = true;
                if ($assigned && $primary && !is_wp_error($primary) && (string) get_post_meta($id, 'lsd_primary_category', true) !== $primary->slug)
                {
                    update_post_meta($id, 'lsd_primary_category', $primary->slug);
                    $changed = true;
                }
            }
        }
        $metadata = (array) get_post_meta($id, 'lsd_business_data_metadata', true);
        if ($resolvedCategoryCodes !== null && ($metadata['category_term_codes'] ?? null) !== $resolvedCategoryCodes)
        {
            $metadata['category_term_codes'] = $resolvedCategoryCodes;
            $changed = true;
        }
        foreach (['brand', 'operating_status'] as $field) if (in_array($field, $fields, true) && !empty($details['metadata'][$field]) && ($metadata[$field] ?? null) !== $details['metadata'][$field])
        {
            $metadata[$field] = $details['metadata'][$field];
            $changed = true;
        }
        if ($changed) update_post_meta($id, 'lsd_business_data_metadata', $metadata);

        return $changed;
    }

    private function record_maintenance_history(array $entry): void
    {
        $history = (array) get_option(self::MAINTENANCE_HISTORY_OPTION, []);
        array_unshift($history, $entry);
        update_option(self::MAINTENANCE_HISTORY_OPTION, array_slice($history, 0, 50), false);
    }

    private function maintenance_failure(Throwable $e): void
    {
        $status = in_array((int) $e->getCode(), [402, 409, 422, 429, 503], true) ? (int) $e->getCode() : 502;
        $this->respond(['success' => 0, 'message' => esc_html($e->getMessage())], $status);
    }

    private function guard(): void
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, self::NONCE) || !current_user_can('manage_options')) $this->respond(['success' => 0, 'message' => esc_html__('You are not allowed to use Webilia Business Data.', 'listdom')], 403);
    }

    private function existing_listing(string $placeId, int $exceptId = 0): int
    {
        $ids = get_posts(['post_type' => LSD_Base::PTYPE_LISTING, 'post_status' => ['draft', 'pending', 'publish', 'future', 'private'], 'fields' => 'ids', 'posts_per_page' => 1,
            'post__not_in' => $exceptId ? [$exceptId] : [], 'meta_query' => [['key' => 'lsd_business_data_place_id', 'value' => $placeId]]]);
        return isset($ids[0]) ? (int) $ids[0] : 0;
    }

    private function place_details(array $place): array
    {
        $addresses = [];
        foreach ((array) ($place['addresses'] ?? []) as $address)
        {
            if (!is_array($address)) continue;
            $addresses[] = array_map('sanitize_text_field', [
                'freeform' => (string) ($address['freeform'] ?? ''), 'locality' => (string) ($address['locality'] ?? ''),
                'postcode' => (string) ($address['postcode'] ?? ''), 'region' => (string) ($address['region'] ?? ''),
                'country' => (string) ($address['country'] ?? ''),
            ]);
        }

        $parts = [];
        $firstAddress = $addresses[0] ?? [];
        foreach (['freeform', 'locality', 'region', 'postcode', 'country'] as $field)
        {
            $part = trim($firstAddress[$field] ?? '');
            if ($part === '') continue;
            foreach (explode(',', implode(', ', $parts)) as $existingPart)
            {
                if (strcasecmp(trim($existingPart), $part) === 0) continue 2;
            }
            $parts[] = $part;
        }

        $address = $parts ? implode(', ', $parts) : sanitize_text_field((string) ($place['address'] ?? ''));

        $phones = $this->place_values($place['phones'] ?? [], 'sanitize_text_field', $place['phone'] ?? '');
        $websites = $this->place_values($place['websites'] ?? [], 'esc_url_raw', $place['website'] ?? '');
        $emails = $this->place_values($place['emails'] ?? [], 'sanitize_email', $place['email'] ?? '');
        $socials = $this->place_values($place['socials'] ?? [], 'esc_url_raw');
        $taxonomy = $this->place_values($place['taxonomy'] ?? [], 'sanitize_key');
        $alternates = $this->place_values($place['alternate_categories'] ?? [], 'sanitize_key');
        $brand = is_array($place['brand'] ?? null) ? $place['brand'] : [];

        $socialChannels = [];
        foreach ($socials as $url)
        {
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            foreach (['facebook' => ['facebook.com', 'fb.com'], 'twitter' => ['twitter.com', 'x.com'], 'instagram' => ['instagram.com'], 'linkedin' => ['linkedin.com'], 'youtube' => ['youtube.com', 'youtu.be'], 'tiktok' => ['tiktok.com']] as $network => $domains)
            {
                foreach ($domains as $domain)
                {
                    if (($host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain) && !isset($socialChannels[$network])) $socialChannels[$network] = $url;
                }
            }
        }

        return [
            'address' => $address, 'phones' => $phones, 'websites' => $websites, 'emails' => $emails,
            'social_channels' => $socialChannels,
            'primary_category' => sanitize_key((string) ($place['primary_category'] ?? '')),
            'alternate_categories' => $alternates,
            'metadata' => [
                'category' => sanitize_key((string) ($place['category'] ?? '')),
                'primary_category' => sanitize_key((string) ($place['primary_category'] ?? '')),
                'taxonomy' => $taxonomy, 'alternate_categories' => $alternates,
                'addresses' => $addresses, 'phones' => $phones, 'websites' => $websites, 'emails' => $emails, 'socials' => $socials,
                'brand' => ['name' => sanitize_text_field((string) ($brand['name'] ?? '')), 'wikidata' => sanitize_text_field((string) ($brand['wikidata'] ?? ''))],
                'operating_status' => sanitize_key((string) ($place['operating_status'] ?? '')),
                'confidence' => isset($place['confidence']) && is_numeric($place['confidence']) ? (float) $place['confidence'] : null,
                'place_version' => isset($place['place_version']) ? absint($place['place_version']) : null,
            ],
        ];
    }

    private function place_values($values, string $sanitize, $fallback = ''): array
    {
        $values = is_array($values) ? $values : [];
        if (!$values && is_scalar($fallback) && (string) $fallback !== '') $values = [$fallback];

        return array_values(array_unique(array_filter(array_map(static function ($value) use ($sanitize): string
        {
            return is_scalar($value) ? (string) $sanitize((string) $value) : '';
        }, $values), static fn(string $value): bool => $value !== '')));
    }

    private function enrich_existing(int $postId, array $details): bool
    {
        $changed = false;
        foreach (['lsd_address' => $details['address'], 'lsd_phone' => $details['phones'][0] ?? '', 'lsd_website' => $details['websites'][0] ?? '', 'lsd_email' => $details['emails'][0] ?? ''] as $key => $value)
        {
            if ($value !== '' && get_post_meta($postId, $key, true) === '')
            {
                update_post_meta($postId, $key, $value);
                $changed = true;
            }
        }

        foreach ($details['social_channels'] as $network => $url)
        {
            if (get_post_meta($postId, 'lsd_' . $network, true) === '')
            {
                update_post_meta($postId, 'lsd_' . $network, $url);
                $changed = true;
            }
        }

        $metadata = (array) get_post_meta($postId, 'lsd_business_data_metadata', true);
        foreach ($details['metadata'] as $key => $value)
        {
            if ((!array_key_exists($key, $metadata) || $metadata[$key] === '' || $metadata[$key] === [] || $metadata[$key] === null) && $value !== '' && $value !== [] && $value !== null)
            {
                $metadata[$key] = $value;
                $changed = true;
            }
        }

        if ($changed)
        {
            $metadata['last_checked_at'] = gmdate('c');
            $metadata['last_synced_at'] = gmdate('c');
            update_post_meta($postId, 'lsd_business_data_metadata', $metadata);
        }
        return $changed;
    }

    private function business_category_term(string $code): int
    {
        $slug = sanitize_title($code);
        $term = get_term_by('slug', $slug, self::TAX_CATEGORY);
        if ($term) return (int) $term->term_id;

        $words = explode('_', $code);
        $label = implode(' ', array_map(static function (string $word, int $index): string
        {
            if ($index > 0 && in_array($word, ['and', 'or', 'of', 'the'], true)) return $word;
            return $word === 'b2b' ? 'B2B' : ucfirst($word);
        }, $words, array_keys($words)));

        $term = get_term_by('name', $label, self::TAX_CATEGORY);
        if ($term) return (int) $term->term_id;

        $created = wp_insert_term($label, self::TAX_CATEGORY, ['slug' => $slug]);
        if (is_wp_error($created)) return 0;

        return (int) $created['term_id'];
    }

    private function can_delete_history(array $history): bool
    {
        if (get_option(self::HISTORY_DELETE_UNLOCKED_OPTION, false)) return true;
        if (count($history) < 2) return false;

        update_option(self::HISTORY_DELETE_UNLOCKED_OPTION, 1, false);
        return true;
    }

    private function pruned_history(): array
    {
        $history = get_option(self::HISTORY_OPTION, []);
        $history = is_array($history) ? array_values(array_filter($history, static fn($entry): bool => is_array($entry) && (int) ($entry['created_at'] ?? 0) >= time() - self::HISTORY_TTL)) : [];

        if (count($history) !== count((array) get_option(self::HISTORY_OPTION, []))) update_option(self::HISTORY_OPTION, $history, false);
        return array_slice($history, 0, self::HISTORY_LIMIT);
    }

    private function failure(Throwable $e): void
    {
        $this->respond(['success' => 0, 'message' => esc_html($e->getMessage())], 502);
    }

    private function credit_balance(array $balance): string
    {
        $value = (string) ($balance['credits_balance'] ?? '0');
        if (!preg_match('/^-?(?:0|[1-9][0-9]*)(?:\.([0-9]{1,3}))?$/D', $value, $parts)) return '0.000';

        return $value . (strpos($value, '.') !== false ? str_repeat('0', 3 - strlen($parts[1])) : '.000');
    }

    private function respond(array $data, int $status = 200): void
    {
        wp_send_json($data, $status);
    }
}
