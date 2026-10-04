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
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('add_meta_boxes_' . LSD_Base::PTYPE_LISTING, [$this, 'register_metabox']);
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
    }

    public function tab(string $current): void
    {
        if (!LSD_Webilia_Connect::enabled()) return;

        echo '<li><a class="lsd-nav-tab ' . ($current === self::TAB ? 'lsd-nav-tab-active' : '') . '" href="' . esc_url(admin_url('admin.php?page=listdom-ix&tab=' . self::TAB)) . '">';
        echo '<i class="webilia-icon wbli-database lsd-m-0"></i>' . esc_html__('Business Data', 'listdom') . '</a></li>';
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
            $this->respond(['success' => 1, 'credits_balance' => max(0, (int) ($balance['credits_balance'] ?? 0))]);
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

        if (count($categories) < 1 || count($categories) > 5 || $lat === null || $lat < -90 || $lat > 90 || $lng === null || $lng < -180 || $lng > 180 || $radius <= 0 || $radius > 25 || $limit < 1 || $limit > 100 || !preg_match('/^[0-9a-f-]{36}$/i', $key))
        {
            $this->respond(['success' => 0, 'message' => esc_html__('Please provide one to five categories, a valid map area, and a result limit between 1 and 100.', 'listdom')], 422);
        }

        // The API reserves credits for the requested limit before running the search.
        // If the balance check is unavailable, let the API make the authoritative decision.
        $available = null;
        try
        {
            $balance = LSD_Webilia_Connect::creditBalance();
            $available = max(0, (int) ($balance['credits_balance'] ?? 0));
        }
        catch (Throwable $balanceError)
        {
            // The search API remains authoritative if this read-only balance check fails.
        }

        if ($available !== null)
        {
            if ($available === 0) $this->respond(['success' => 0, 'message' => esc_html__('You have no Webilia Credits available for a new search.', 'listdom'), 'credits_balance' => 0], 402);
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
                try
                {
                    $balance = LSD_Webilia_Connect::creditBalance();
                    $available = max(0, (int) ($balance['credits_balance'] ?? 0));
                    $maximum = min(100, $available * self::RESULTS_PER_CREDIT);
                    $message = $maximum > 0
                        ? sprintf(__('Requesting up to %1$d results requires %2$d Webilia Credits, but you have %3$d. Reduce Maximum results to %4$d or less.', 'listdom'), $limit, $required, $available, $maximum)
                        : sprintf(__('Requesting up to %1$d results requires %2$d Webilia Credits, but you have none. Add credits before searching again.', 'listdom'), $limit, $required);
                }
                catch (Throwable $balanceError)
                {
                    $message = sprintf(__('Requesting up to %1$d results requires %2$d Webilia Credits, but your balance is insufficient. Reduce Maximum results and try again.', 'listdom'), $limit, $required);
                }

                $response = ['success' => 0, 'message' => $message];
                if ($available !== null) $response['credits_balance'] = $available;
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
            || $values['zoom'] < 1 || $values['zoom'] > 20 || $values['radius_km'] <= 0 || $values['radius_km'] > 25
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
            $title = sanitize_text_field((string) ($place['name'] ?? ''));
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
                foreach ($details['alternate_categories'] as $alternate)
                {
                    if (!isset($categoryIds[$alternate])) $categoryIds[$alternate] = $this->business_category_term($alternate);
                    if ($categoryIds[$alternate] && !in_array($categoryIds[$alternate], $listingCategories, true)) $listingCategories[] = $categoryIds[$alternate];
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
            ]));

            $created[] = ['id' => $placeId, 'post_id' => $postId, 'edit_url' => get_edit_post_link($postId, 'raw')];
        }

        $history[$index]['imports'] = array_merge((array) ($history[$index]['imports'] ?? []), $created, $updated, $skipped);
        update_option(self::HISTORY_OPTION, $history, false);

        $this->respond(['success' => 1, 'created' => $created, 'updated' => $updated, 'skipped' => $skipped]);
    }

    public function register_metabox(WP_Post $post): void
    {
        if (!current_user_can('manage_options') || !get_post_meta($post->ID, 'lsd_business_data_place_id', true)) return;

        add_meta_box('lsd_metabox_business_data', esc_html__('Webilia Business Data', 'listdom'), [$this, 'metabox'], LSD_Base::PTYPE_LISTING, 'side', 'default');
    }

    public function metabox($post): void
    {
        if (!current_user_can('manage_options') || !$post instanceof WP_Post) return;

        $placeId = (string) get_post_meta($post->ID, 'lsd_business_data_place_id', true);
        if ($placeId === '') return;

        $meta = (array) get_post_meta($post->ID, 'lsd_business_data_metadata', true);
        echo '<div class="lsd-business-data-metabox">';
        $this->metabox_text(__('Provider ID', 'listdom'), $placeId);
        foreach (['source' => __('Source', 'listdom'), 'attribution' => __('Attribution', 'listdom'), 'provider_release' => __('Provider release', 'listdom'), 'search_id' => __('Search ID', 'listdom')] as $key => $label) if (!empty($meta[$key])) $this->metabox_text($label, (string) $meta[$key]);

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

    private function guard(): void
    {
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, self::NONCE) || !current_user_can('manage_options')) $this->respond(['success' => 0, 'message' => esc_html__('You are not allowed to use Webilia Business Data.', 'listdom')], 403);
    }

    private function existing_listing(string $placeId): int
    {
        $ids = get_posts(['post_type' => LSD_Base::PTYPE_LISTING, 'post_status' => ['draft', 'pending', 'publish', 'future', 'private'], 'fields' => 'ids', 'posts_per_page' => 1, 'meta_query' => [['key' => 'lsd_business_data_place_id', 'value' => $placeId]]]);
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

        if ($changed) update_post_meta($postId, 'lsd_business_data_metadata', $metadata);
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

    private function respond(array $data, int $status = 200): void
    {
        wp_send_json($data, $status);
    }
}
