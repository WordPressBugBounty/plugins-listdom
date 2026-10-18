<?php

class LSD_Menus_Analytics extends LSD_Menus
{
    private const STATS_BASENAME = 'listdom-stats/listdom-stats.php';

    public function output()
    {
        // Core Summary and Report Filters
        [$activity_filters, $period, $all_listings, $all_terms, $all_zero_terms] = $this->requestedView();
        $summary = (new LSD_Analytics_Summary())->get($activity_filters['days']);
        $status = $this->statsState();
        $listing_filter_active = $activity_filters['category'] || $activity_filters['location'] || $activity_filters['search'] !== '';

        // Listing and Event Reports
        $active = $status['code'] === 'active';
        [$report, $activity, $activity_supported, $activity_terms] = $active ? $this->listingReport($activity_filters, $all_listings) : [null, null, false, []];
        $events_period = $period === 'old' ? 'daily' : $period;
        $events = $this->eventReports($active, $events_period, $activity_filters, $activity_supported, $listing_filter_active, $all_terms, $all_zero_terms);
        if ($active && !$events['events_supported'] && $events_period === 'yearly') $period = $events_period = 'daily';
        if ($events['filters_supported'] && is_array($events['events_series'])) $summary = array_merge($summary, (new LSD_Analytics_Summary())->filtered($activity_filters));

        // Historical and Derived Reports
        [$old_stats, $old_stats_supported] = $active ? $this->pretrackingReport($listing_filter_active, $summary, $report) : [null, false];
        if ($period === 'old' && !$old_stats_supported) $period = 'daily';
        $listing_filter_active = $events['filters_supported'] && $listing_filter_active;
        $history = $events['events_series'];
        $conversion = $this->conversion($events['events_series']);
        $search_summary = $this->searchSummary($events['card_series']);
        $chart_search_summary = $this->searchSummary($events['events_series']);
        $action_range = $activity_filters['days'];

        // Dashboard Presentation
        [$low_data, $rich_data, $tracked_report] = $this->overviewState(
            $events['events_series'], $events['events_supported'], $events_period,
            $events['filters_supported'], $activity_supported
        );

        if ($active && $events['events_supported']) $status = $this->analyticsStatus($status, $low_data);
        $report_url = $this->reportUrl($period, $tracked_report, $activity_filters);
        $cards = $this->cards($summary, $status, $report, $search_summary, $events['card_series'], $rich_data, $events['changes'], $events['filters_supported'], $activity_filters['days']);
        $action_groups = $this->actionGroups($events['action_series']);
        $onboarding_steps = $low_data ? $this->onboardingSteps() : [];
        $notice = LSD_Webilia_Connect::notice();
        $preview = $this->preview($status);
        $preview_metrics = $this->previewMetrics();
        $activity_export_available = $activity_supported && is_array($activity) && $activity['total'] > 0;

        // Generate Output
        $parameters = $events + compact('report', 'activity', 'activity_supported', 'activity_terms',
            'history', 'conversion', 'search_summary', 'chart_search_summary', 'old_stats', 'old_stats_supported',
            'period', 'events_period', 'action_range', 'summary', 'cards', 'status', 'action_groups', 'all_terms', 'all_zero_terms',
            'rich_data', 'low_data', 'tracked_report', 'report_url', 'onboarding_steps', 'activity_filters',
            'listing_filter_active', 'activity_export_available', 'all_listings', 'notice', 'preview', 'preview_metrics');
        $this->include_html_file('menus/analytics/tpl.php', ['parameters' => $parameters]);
    }

    /**
     * Load event reports supported by the installed Stats add-on.
     */
    private function eventReports(bool $active, string $period, array $filters, bool $activity_supported, bool $listing_filter_active, bool $all_terms, bool $all_zero_terms): array
    {
        // Availability and Empty Results
        $events_series = $card_series = $action_series = $terms_report = $zero_terms_report = null;
        $events_supported = $filters_supported = $action_range_supported = false;
        $changes = [];

        if (!$active || !class_exists(\LSDPACSTS\Aggregates::class) || !method_exists(\LSDPACSTS\Aggregates::class, 'series'))
            return compact('events_series', 'card_series', 'action_series', 'terms_report', 'zero_terms_report', 'events_supported', 'filters_supported', 'action_range_supported', 'changes');

        // Chart and Search Term Reports
        $events_supported = true;
        $aggregates = new \LSDPACSTS\Aggregates();
        [$events_series, $card_series, $filters_supported, $action_range_supported, $action_series, $changes] = $this->eventSeries($aggregates, $period, $filters, $activity_supported);

        if (is_array($events_series) && (!$filters_supported || !$listing_filter_active) && method_exists($aggregates, 'terms'))
            [$terms_report, $zero_terms_report] = $this->termsReports($aggregates, $events_series, $filters, $filters_supported, $all_terms, $all_zero_terms);

        return compact('events_series', 'card_series', 'action_series', 'terms_report', 'zero_terms_report', 'events_supported', 'filters_supported', 'action_range_supported', 'changes');
    }

    /**
     * Read the report controls from the current Analytics request.
     */
    private function requestedView(): array
    {
        $filters = $this->activityFilters($_GET);
        $period = isset($_GET['analytics_period']) ? sanitize_key(wp_unslash($_GET['analytics_period'])) : 'daily';
        if (!in_array($period, ['daily', 'weekly', 'monthly', 'yearly', 'old'], true)) $period = 'daily';

        $all_listings = isset($_GET['analytics_view']) && $_GET['analytics_view'] === 'all';
        $all_terms = isset($_GET['analytics_terms']) && $_GET['analytics_terms'] === 'all';
        $all_zero_terms = isset($_GET['analytics_zero_terms']) && $_GET['analytics_zero_terms'] === 'all';

        return [$filters, $period, $all_listings, $all_terms, $all_zero_terms];
    }

    /**
     * Read the retained lifetime baseline only when the installed Stats API supports it.
     */
    private function pretrackingReport(bool $listing_filter_active, array $summary, ?array $report): array
    {
        if ($listing_filter_active || !class_exists(\LSDPACSTS\Reports::class)
            || !method_exists(\LSDPACSTS\Reports::class, 'pretracking')) return [null, false];

        return [(new \LSDPACSTS\Reports())->pretracking($summary['views'], $report['contacts'] ?? null), true];
    }

    /**
     * Load the lifetime listing report and any dated report API in the installed Stats version.
     */
    protected function listingReport(array $filters, bool $all_listings): array
    {
        if (!class_exists(\LSDPACSTS\Reports::class) || !method_exists(\LSDPACSTS\Reports::class, 'get'))
            return [null, null, false, []];

        $requested_page = $all_listings && isset($_GET['analytics_page']) ? absint($_GET['analytics_page']) : 1;
        $reports = new \LSDPACSTS\Reports();
        $report = $reports->get($requested_page);
        // Older Stats versions may have lifetime reports but no dated report API.
        if (!method_exists($reports, 'activity') || !method_exists($reports, 'activityRows'))
            return [$report, null, false, []];

        return [$report, $reports->activity($filters, $requested_page), true, $this->activityTerms()];
    }

    /**
     * Select chart, card, and action series from the add-on APIs available on this site.
     */
    protected function eventSeries(\LSDPACSTS\Aggregates $aggregates, string $period, array $filters, bool $activity_supported): array
    {
        // Chart and Card Windows
        $filtered = $activity_supported && method_exists($aggregates, 'filteredSeries');
        $events = $filtered ? $aggregates->filteredSeries($period, $filters) : $aggregates->series($period);
        if (!is_array($events)) $filtered = false;

        $range_supported = $filtered || method_exists($aggregates, 'seriesForRange');
        $cards = $period !== 'daily'
            ? ($filtered ? $aggregates->filteredSeries('daily', $filters)
                : ($range_supported ? $aggregates->seriesForRange(30) : $events)) : $events;

        // Comparable Card Changes
        $changes = [];
        if (is_array($cards))
        {
            if ($filtered && method_exists($aggregates, 'filteredChanges'))
                $changes = $aggregates->filteredChanges($cards, $filters);
            elseif (!$filtered && method_exists($aggregates, 'changes'))
                $changes = $aggregates->changes($cards);
        }

        // Action Breakdown Window
        if ($filtered) $actions = $cards;
        elseif ($range_supported) $actions = $filters['days'] === 30 ? $cards : $aggregates->seriesForRange($filters['days']);
        else $actions = $events;

        return [$events, $cards, $filtered, $range_supported, $actions, $changes];
    }

    protected function termsReports(\LSDPACSTS\Aggregates $aggregates, array $series, array $filters, bool $filtered, bool $all_terms, bool $all_zero_terms): array
    {
        $from = $filtered
            ? (new DateTimeImmutable('today', LSD_Base::timezone()))->modify('-' . ($filters['days'] - 1) . ' days')->format('Y-m-d')
            : ($series['dates'][0] ?? '');
        $terms_page = $all_terms && isset($_GET['analytics_terms_page']) ? absint($_GET['analytics_terms_page']) : 1;
        $zero_terms_page = $all_zero_terms && isset($_GET['analytics_zero_terms_page']) ? absint($_GET['analytics_zero_terms_page']) : 1;

        $terms = $aggregates->terms($from, false, $terms_page, $all_terms ? 20 : 5);
        $zero = $aggregates->terms($from, true, $zero_terms_page, $all_zero_terms ? 20 : 5);
        if (is_array($terms)) $terms['from'] = $from;
        if (is_array($zero)) $zero['from'] = $from;
        return [$terms, $zero];
    }

    protected function analyticsStatus(array $status, bool $low_data): array
    {
        $collecting = class_exists(\LSDPACSTS\Settings::class)
            && method_exists(\LSDPACSTS\Settings::class, 'enabled')
            && \LSDPACSTS\Settings::enabled();
        if (!$collecting)
        {
            $status['tone'] = 'warning';
            $status['settings_url'] = admin_url('admin.php?page=listdom-settings&tab=addons&subtab=stats');
        }
        if (!empty($status['connected']))
        {
            $status['title'] = !$collecting
                ? __('Stats Addon is active, but Analytics is disabled', 'listdom')
                : ($low_data
                    ? __('Your site is connected and ready for analytics', 'listdom')
                    : __('Your site is connected and analytics are active', 'listdom'));
            $status['body'] = $collecting
                ? ($low_data
                    ? __('Your Webilia account is connected and Stats Addon is installed. Analytics will start collecting data as soon as visitors interact with your directory.', 'listdom')
                    : __('Your Webilia account is connected and Stats Addon is installed. Analytics data is now being collected and updated automatically.', 'listdom'))
                : __('Your Webilia account is connected and Stats Addon is active. Enable Analytics in Stats Addon settings to start collecting visitor activity.', 'listdom');
        }
        else
        {
            $status['title'] = !$collecting
                ? __('Stats Addon is active, but Analytics is disabled', 'listdom')
                : ($low_data ? __('Stats Addon is ready for analytics', 'listdom') : __('Stats Addon is active', 'listdom'));
            $status['body'] = $collecting
                ? ($low_data
                    ? __('Stats Addon is active. Analytics will start collecting data as soon as visitors interact with your directory.', 'listdom')
                    : __('Stats Addon is active. Analytics data is being collected as visitors interact with your directory.', 'listdom'))
                : __('Stats Addon is active. Enable Analytics in Stats Addon settings to start collecting visitor activity.', 'listdom');
        }

        return $status;
    }

    /**
     * Return the next five rows for the expanded top listings table.
     */
    public function moreListings(): void
    {
        // Request Validation
        if (!current_user_can('manage_options') || !check_ajax_referer('lsd_analytics_listings', '_ajax_nonce', false))
            wp_send_json_error(null, 403);

        if (!class_exists(\LSDPACSTS\Reports::class) || !class_exists(\LSDPACSTS\Settings::class))
            wp_send_json_error(null, 409);

        $offset = isset($_POST['offset']) && is_scalar($_POST['offset']) ? absint($_POST['offset']) : 0;
        $tracked_report = isset($_POST['tracked']) && $_POST['tracked'] === '1';
        if (!method_exists(\LSDPACSTS\Settings::class, 'topListings')
            || !method_exists(\LSDPACSTS\Reports::class, $tracked_report ? 'activity' : 'get')
            || ($tracked_report && !method_exists(\LSDPACSTS\Reports::class, 'activityRows')))
            wp_send_json_error(null, 409);

        // Report Page
        $page_size = \LSDPACSTS\Settings::topListings();
        $page = intdiv($offset, $page_size) + 1;
        $reports = new \LSDPACSTS\Reports();
        $listing_report = $tracked_report
            ? $reports->activity($this->activityFilters($_POST), $page) : $reports->get($page);
        if (!is_array($listing_report) || !is_array($listing_report['listings'] ?? null))
            wp_send_json_error(null, 409);

        // Row Rendering
        $listings = $offset < $listing_report['total']
            ? array_slice($listing_report['listings'], $offset % $page_size, 5) : [];
        $listing_offset = $offset;
        $unique_views_available = $tracked_report && isset($listings[0]['unique_views']);
        $detail_cells_hidden = false;
        $rows = $this->include_html_file('menus/analytics/listing-rows.php', [
            'parameters' => compact('listings', 'listing_offset', 'tracked_report', 'unique_views_available', 'detail_cells_hidden'),
            'return_output' => true,
        ]);

        // Response
        wp_send_json_success(['rows' => $rows, 'count' => count($listings), 'total' => (int) $listing_report['total']]);
    }

    /**
     * Return the next five search terms for an expanded ranking.
     */
    public function moreTerms(): void
    {
        // Request Validation
        if (!current_user_can('manage_options') || !check_ajax_referer('lsd_analytics_terms', '_ajax_nonce', false))
            wp_send_json_error(null, 403);

        $report = isset($_POST['report']) && is_scalar($_POST['report']) ? sanitize_key(wp_unslash($_POST['report'])) : '';
        $from = isset($_POST['from']) && is_scalar($_POST['from']) ? sanitize_text_field(wp_unslash($_POST['from'])) : '';
        $offset = isset($_POST['offset']) && is_scalar($_POST['offset']) ? absint($_POST['offset']) : 0;
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $from);
        if (!in_array($report, ['terms', 'zero'], true) || !$date || $date->format('Y-m-d') !== $from || $offset < 5)
            wp_send_json_error(null, 400);

        if (!class_exists(\LSDPACSTS\Aggregates::class) || !method_exists(\LSDPACSTS\Aggregates::class, 'terms'))
            wp_send_json_error(null, 409);

        // Report Page
        $page = intdiv($offset, 5) + 1;
        $aggregates = new \LSDPACSTS\Aggregates();
        $terms = $aggregates->terms($from, $report === 'zero', $page, 5);
        if (!is_array($terms) || !is_array($terms['rows'] ?? null)) wp_send_json_error(null, 409);

        // Row Rendering
        $rows = array_slice($terms['rows'], $offset % 5);
        $maximum = isset($_POST['maximum']) && is_scalar($_POST['maximum']) ? max(1, absint($_POST['maximum'])) : 1;
        $html = $this->include_html_file($report === 'zero' ? 'menus/analytics/zero-term-rows.php' : 'menus/analytics/term-rows.php', [
            'parameters' => compact('rows', 'maximum'),
            'return_output' => true,
        ]);

        // Response
        wp_send_json_success(['rows' => $html, 'count' => count($rows), 'total' => (int) $terms['total']]);
    }

    /**
     * Activate the installed Stats add-on without leaving Analytics.
     */
    public function activateStats(): void
    {
        // Activation Request
        if (!current_user_can('activate_plugin', self::STATS_BASENAME)
            || !check_ajax_referer('lsd_analytics_activate_stats', '_ajax_nonce', false))
            wp_send_json_error(['message' => esc_html__('You are not allowed to activate Stats Addon.', 'listdom')], 403);

        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $plugins = get_plugins();
        if (!isset($plugins[self::STATS_BASENAME]))
            wp_send_json_error(['message' => esc_html__('Stats Addon is no longer installed on this site.', 'listdom')], 404);

        // Plugin Activation
        if (!is_plugin_active(self::STATS_BASENAME))
        {
            $activation = activate_plugin(self::STATS_BASENAME);
            if (is_wp_error($activation))
                wp_send_json_error(['message' => esc_html($activation->get_error_message())], 500);
            if (!is_plugin_active(self::STATS_BASENAME))
                wp_send_json_error(['message' => esc_html__('WordPress could not activate Stats Addon. Please try again.', 'listdom')], 500);
        }

        // Refresh Analytics with the activated add-on.
        wp_send_json_success(['reload' => true, 'message' => esc_html__('Stats Addon was activated.', 'listdom')]);
    }

    /**
     * Low-data guidance with links only where Listdom has a real destination.
     */
    protected function onboardingSteps(): array
    {
        return [
            [
                'title' => __('Publish more listings', 'listdom'),
                'description' => __('Add quality listings with rich details and images.', 'listdom'),
                'url' => admin_url('post-new.php?post_type=' . LSD_Base::PTYPE_LISTING),
            ],
            [
                'title' => __('Share your directory', 'listdom'),
                'description' => __('Promote your site through your existing channels.', 'listdom'),
                'url' => '',
            ],
            [
                'title' => __('Enable contact actions', 'listdom'),
                'description' => __('Let visitors contact listing owners through your listing pages.', 'listdom'),
                'url' => '',
            ],
            [
                'title' => __('Install Favorites or Booking add-ons', 'listdom'),
                'description' => __('Add optional engagement features when they are available.', 'listdom'),
                'url' => admin_url('admin.php?page=listdom-connect&tab=addons'),
            ],
            [
                'title' => __('Wait for visitor activity', 'listdom'),
                'description' => __('Dated reports can grow as new visitors interact.', 'listdom'),
                'url' => '',
            ],
        ];
    }

    /**
     * Whitelist the controls shared by dashboard reports, cards, and CSV export.
     */
    protected function activityFilters(array $input): array
    {
        $default_days = class_exists(\LSDPACSTS\Settings::class) && method_exists(\LSDPACSTS\Settings::class, 'defaultRange') ? \LSDPACSTS\Settings::defaultRange() : 30;
        $days = isset($input['analytics_range']) && is_scalar($input['analytics_range']) ? absint($input['analytics_range'])
            : (isset($input['analytics_action_range']) && is_scalar($input['analytics_action_range']) ? absint($input['analytics_action_range']) : $default_days);
        if (!in_array($days, [7, 30, 90], true)) $days = 30;

        $category = isset($input['analytics_category']) && is_scalar($input['analytics_category']) ? absint($input['analytics_category']) : 0;
        $location = isset($input['analytics_location']) && is_scalar($input['analytics_location']) ? absint($input['analytics_location']) : 0;
        $search = isset($input['analytics_search']) && is_scalar($input['analytics_search'])
            ? sanitize_text_field(wp_unslash($input['analytics_search'])) : '';

        return ['days' => $days, 'category' => $category, 'location' => $location, 'search' => $search];
    }

    protected function activityTerms(): array
    {
        $options = [];
        foreach (['category' => self::TAX_CATEGORY, 'location' => self::TAX_LOCATION] as $key => $taxonomy)
        {
            $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
            $options[$key] = is_wp_error($terms) ? [] : $terms;
        }

        return $options;
    }

    /**
     * Preserve the chart period and active listing filters across report navigation.
     */
    protected function reportUrl(string $period, bool $tracked_report, array $filters): string
    {
        $args = ['analytics_period' => $period];
        if ($tracked_report) $args += [
            'analytics_range' => $filters['days'],
            'analytics_location' => $filters['location'],
            'analytics_category' => $filters['category'],
            'analytics_search' => $filters['search'],
        ];

        return add_query_arg($args, admin_url('admin.php?page=listdom-analytics'));
    }

    /**
     * Use retained yearly activity when the selected chart window is quiet.
     */
    protected function overviewState(?array $series, bool $supported, string $period, bool $filtered, bool $activity_supported): array
    {
        $overview = $series;
        if ($supported && is_array($series) && $period !== 'yearly'
            && empty($series['has_activity']) && empty($series['has_search_activity']))
        {
            $yearly = (new \LSDPACSTS\Aggregates())->series('yearly');
            if (is_array($yearly)) $overview = $yearly;
        }

        $low_data = $supported && is_array($overview)
            && empty($overview['has_activity']) && empty($overview['has_search_activity']);
        $rich_data = $supported && is_array($series) && !$low_data;
        $tracked_report = $filtered && $activity_supported
            ? true : ($rich_data && $activity_supported && $this->hasReportActivity($overview));

        return [$low_data, $rich_data, $tracked_report];
    }

    /**
     * Admin-post response: stream only the rows matching the visible dated report.
     */
    public function export(): void
    {
        // Request and Add-on Access
        if (!current_user_can('manage_options')) wp_die(esc_html__('You cannot export Analytics reports.', 'listdom'), '', ['response' => 403]);
        check_admin_referer('lsd_analytics_export');

        if (!isset(apply_filters('lsd_addons', [])['stats'])
            || !class_exists(\LSDPACSTS\Reports::class)
            || !method_exists(\LSDPACSTS\Reports::class, 'activity')
            || !method_exists(\LSDPACSTS\Reports::class, 'activityRows'))
            wp_die(esc_html__('The tracked listing report is unavailable.', 'listdom'), '', ['response' => 409]);

        // Matching Report Rows
        $filters = $this->activityFilters($_GET);
        $reports = new \LSDPACSTS\Reports();
        $activity = $reports->activity($filters);
        if (!is_array($activity))
            wp_die(esc_html__('The tracked listing report could not be read.', 'listdom'), '', ['response' => 500]);
        if ($activity['total'] < 1)
            wp_die(esc_html__('There are no tracked listing rows to export.', 'listdom'), '', ['response' => 409]);

        // Read the rows before sending CSV headers to avoid a partial download on query failure.
        $rows = $reports->activityRows($filters, $activity['total'], 0);
        if (!is_array($rows) || !$rows)
            wp_die(esc_html__('The tracked listing report could not be read.', 'listdom'), '', ['response' => 500]);

        // CSV Headers
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: attachment; filename="listdom-analytics-listings-' . $filters['days'] . '-days-' . current_time('Y-m-d') . '.csv"');
        $stream = fopen('php://output', 'w');
        fwrite($stream, "\xEF\xBB\xBF");
        $this->writeExportContext($stream, $filters, $activity);
        $unique_export = isset($rows[0]['unique_views']);
        $headings = [__('Listing ID', 'listdom'), __('Listing', 'listdom'), __('Views', 'listdom')];
        if ($unique_export) $headings[] = __('Tracked Daily Unique Views', 'listdom');
        array_push($headings, __('Card Impressions', 'listdom'), __('Card Clicks', 'listdom'), __('Card CTR', 'listdom'), __('Contacts', 'listdom'), __('Phone', 'listdom'), __('Email', 'listdom'), __('Website', 'listdom'), __('WhatsApp', 'listdom'), __('Directions', 'listdom'));
        fputcsv($stream, $headings);

        // Listing Rows
        foreach ($rows as $row)
        {
            // Prevent listing titles from being interpreted as spreadsheet formulas.
            $title = $row['title'];
            if (preg_match('/^[\x00-\x20]*[=+\-@]/', $title)) $title = "'" . $title;
            $values = [$row['id'], $title, $row['views'] === null ? __('Unavailable', 'listdom') : $row['views']];
            if ($unique_export) $values[] = $row['unique_views'];
            array_push($values,
                ($row['card_impressions'] ?? null) === null ? __('Unavailable', 'listdom') : $row['card_impressions'],
                ($row['card_clicks'] ?? null) === null ? __('Unavailable', 'listdom') : $row['card_clicks'],
                ($row['card_ctr'] ?? null) === null ? __('Unavailable', 'listdom') : $row['card_ctr'],
                $row['contacts'] === null ? __('Unavailable', 'listdom') : $row['contacts'],
                ($row['phone'] ?? null) === null ? __('Unavailable', 'listdom') : $row['phone'],
                ($row['email'] ?? null) === null ? __('Unavailable', 'listdom') : $row['email'],
                ($row['website'] ?? null) === null ? __('Unavailable', 'listdom') : $row['website'],
                ($row['whatsapp'] ?? null) === null ? __('Unavailable', 'listdom') : $row['whatsapp'],
                ($row['directions'] ?? null) === null ? __('Unavailable', 'listdom') : $row['directions']
            );
            fputcsv($stream, $values);
        }

        fclose($stream);
        exit;
    }

    protected function writeExportContext($stream, array $filters, array $activity): void
    {
        // Show the requested dates beside the effective collection window.
        $timezone = LSD_Base::timezone();
        $utc = new DateTimeZone('UTC');
        $start = (new DateTimeImmutable($activity['start'], $utc))->setTimezone($timezone);
        $end = (new DateTimeImmutable($activity['end'], $utc))->setTimezone($timezone);
        $last_day = $end->modify('-1 second');
        $first_day = $end->modify('-' . $filters['days'] . ' days');

        fputcsv($stream, [__('Selected range', 'listdom'), sprintf(__('Last %d days', 'listdom'), $filters['days'])]);
        fputcsv($stream, [__('Selected dates', 'listdom'), $first_day->format('Y-m-d') . ' - ' . $last_day->format('Y-m-d')]);
        fputcsv($stream, [__('Site timezone', 'listdom'), $timezone->getName()]);
        fputcsv($stream, [__('Collection window', 'listdom'), $start->format('Y-m-d H:i:s') . ' - ' . $last_day->format('Y-m-d H:i:s')]);
        fputcsv($stream, [__('Coverage', 'listdom'), !empty($activity['partial']) ? __('Partial', 'listdom') : __('Full', 'listdom')]);
        if (!empty($activity['partial']))
            fputcsv($stream, [__('Coverage note', 'listdom'), __('Some tracked metrics may cover only part of the selected range because collection started later, was paused, or older events were removed.', 'listdom')]);
        fputcsv($stream, []);
    }

    protected function statsState(): array
    {
        $addons_url = admin_url('admin.php?page=listdom-connect&tab=addons');
        $analytics_url = admin_url('admin.php?page=listdom-analytics');
        $connect_url = LSD_Webilia_Connect::connectUrl($analytics_url);

        // Running Add-on
        // Installed Stats runs without a license or Webilia connection.
        if (isset(apply_filters('lsd_addons', [])['stats']))
        {
            $connected = LSD_Webilia_Connect::hasConnection() && LSD_Webilia_Connect::isConnected();
            return [
                'code' => 'active',
                'connected' => $connected,
                'tone' => 'success',
                'title' => $connected ? __('Your site is connected and analytics are active', 'listdom') : __('Stats Addon is active', 'listdom'),
                'body' => $connected
                    ? __('Your Webilia account is connected and Stats Addon is active. Available lifetime totals appear below; dated reports update as new activity is collected.', 'listdom')
                    : __('Your available lifetime totals and published listing rankings are shown below. Dated charts use newly collected activity when analytics collection is configured.', 'listdom'),
                'url' => admin_url('admin.php?page=listdom-settings&tab=addons&subtab=stats'),
            ];
        }

        // Local Plugin State
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $active = is_plugin_active(self::STATS_BASENAME);
        $plugins = get_plugins();
        $installed = $active || isset($plugins[self::STATS_BASENAME]);

        if ($active) return [
            'code' => 'not_booted',
            'tone' => 'warning',
            'title' => __('Stats Addon is installed but not available', 'listdom'),
            'body' => __('Stats Addon is active, but its Listdom features have not started. Update Stats Addon and Listdom, then try again.', 'listdom'),
            'url' => admin_url('plugins.php'),
        ];

        if ($installed && !current_user_can('activate_plugin', self::STATS_BASENAME)) return [
            'code' => 'permission_error',
            'tone' => 'warning',
            'title' => __('Stats Addon is installed but inactive', 'listdom'),
            'body' => __('A WordPress administrator with plugin activation permission must activate Stats Addon.', 'listdom'),
            'url' => admin_url('plugins.php'),
        ];

        // A locally installed Stats copy can be activated without Webilia.
        if ($installed) return [
            'code' => 'installed_inactive',
            'tone' => 'warning',
            'title' => __('Stats Addon is installed but inactive', 'listdom'),
            'body' => __('Activate Stats Addon here to enable its features.', 'listdom'),
            'url' => $analytics_url,
        ];

        // Connection and Access
        if (!LSD_Webilia_Connect::enabled()) return [
            'code' => 'connect_disabled',
            'tone' => 'warning',
            'title' => __('Webilia Connect is unavailable', 'listdom'),
            'body' => __('Webilia Connect is unavailable on this site. You can install and activate Stats Addon manually from the Plugins page.', 'listdom'),
            'url' => $addons_url,
        ];

        if (!LSD_Webilia_Connect::isConnected())
        {
            $stored_connection = LSD_Webilia_Connect::hasConnection();
            if ($stored_connection) return [
                'code' => 'connection_error',
                'tone' => 'warning',
                'title' => __('Webilia connection could not be verified', 'listdom'),
                'body' => __('Your saved connection could not be checked. Your local listing totals remain available. Retry or reconnect to install Stats Addon.', 'listdom'),
                'url' => $connect_url,
            ];

            return [
                'code' => 'disconnected',
                'tone' => 'warning',
                'title' => '',
                'body' => '',
                'url' => $connect_url,
            ];
        }

        // Both the catalog and entitlement must identify this Stats package.
        $catalog = (new LSD_Menus_Addons())->get(true);
        if (!is_array($catalog)) return [
            'code' => 'catalog_error',
            'tone' => 'warning',
            'title' => __('Stats availability could not be checked', 'listdom'),
            'body' => __('The Webilia product catalog is unavailable. Your local listing totals remain available; try again later.', 'listdom'),
            'url' => $addons_url,
        ];

        $listed = false;
        foreach ($catalog as $addon)
        {
            if (is_object($addon) && ($addon->basename ?? '') === self::STATS_BASENAME) $listed = true;
        }
        if (!$listed) return [
            'code' => 'catalog_missing',
            'tone' => 'warning',
            'title' => __('Stats Addon is not in the Webilia catalog', 'listdom'),
            'body' => __('The connected catalog does not currently list Stats Addon. Please check again later.', 'listdom'),
            'url' => $addons_url,
        ];

        if (!LSD_Webilia_Connect::isAllowed(self::STATS_BASENAME, 'use')) return [
            'code' => LSD_Webilia_Connect::error() !== '' ? 'entitlement_error' : 'not_entitled',
            'tone' => 'warning',
            'title' => LSD_Webilia_Connect::error() !== '' ? __('Stats access could not be checked', 'listdom') : __('Stats access is unavailable', 'listdom'),
            'body' => LSD_Webilia_Connect::error() !== ''
                ? __('Webilia did not confirm access to Stats. Please retry; installation has not started.', 'listdom')
                : __('This connected account has not been granted Stats access. Installation has not started.', 'listdom'),
            'url' => $addons_url,
        ];

        // Installation Permission
        if (!current_user_can('activate_plugins') || !current_user_can('install_plugins')) return [
            'code' => 'permission_error',
            'tone' => 'warning',
            'title' => __('Stats access is ready', 'listdom'),
            'body' => __('A WordPress administrator with plugin installation and activation permission must complete this step.', 'listdom'),
            'url' => $addons_url,
        ];

        return [
            'code' => 'ready_install',
            'tone' => 'success',
            'title' => __('Webilia is connected; Stats Addon is ready to install', 'listdom'),
            'body' => __('This connected account can use Stats. Install the free add-on through the authorized Webilia package flow.', 'listdom'),
            'url' => $analytics_url,
        ];
    }

    /**
     * Count the four displayed interactions against views in the selected event window.
     */
    protected function conversion(?array $series): ?array
    {
        if (!is_array($series) || ($series['source'] ?? '') !== 'events') return null;
        if (!is_array($series['views'] ?? null)) return null;

        $views = $this->eventTotal($series, 'listing_view', 'views');
        $actions = [
            ['label' => __('Contact Form', 'listdom'), 'value' => $this->eventTotal($series, 'contact_submit', 'contacts'), 'color' => '#5a4cff'],
            ['label' => __('Website Clicks', 'listdom'), 'value' => $this->eventTotal($series, 'website_click', 'website_clicks'), 'color' => '#469bff'],
            ['label' => __('Phone Clicks', 'listdom'), 'value' => $this->eventTotal($series, 'phone_click', 'phone_clicks'), 'color' => '#ffba4d'],
            ['label' => __('Direction Requests', 'listdom'), 'value' => $this->eventTotal($series, 'direction_request', 'direction_requests'), 'color' => '#ff6381'],
        ];
        $interaction_total = 0;
        $has_tracked_interactions = false;
        foreach ($actions as $action)
        {
            if (!is_int($action['value'])) continue;
            $interaction_total += $action['value'];
            $has_tracked_interactions = true;
        }

        $comparable = $this->actionsComparable($series);

        return [
            'views' => $views,
            'rate' => $views > 0 && $has_tracked_interactions ? round($interaction_total * 100 / $views, 1) : null,
            'comparable' => $comparable,
            'actions' => $actions,
            'partial' => !$comparable,
        ];
    }

    /**
     * Counts in the selected retained chart range, never historical lifetime totals.
     */
    protected function searchSummary(?array $series): ?array
    {
        if (!is_array($series) || ($series['source'] ?? '') !== 'events' || ($series['started'] ?? '') === '') return null;
        if (!is_array($series['searches'] ?? null) || !is_array($series['zero_results'] ?? null)) return null;

        $coverage = $series['event_coverage']['search_executed'] ?? $series['coverage'] ?? [];
        if (!in_array('full', $coverage, true) && !in_array('partial', $coverage, true)) return null;
        $zero_coverage = $series['event_coverage']['search_zero_result'] ?? $coverage;
        $zero_available = in_array('full', $zero_coverage, true) || in_array('partial', $zero_coverage, true);
        $filter_coverage = $series['event_coverage']['search_filter_use'] ?? [];
        $filters_available = (in_array('full', $filter_coverage, true) || in_array('partial', $filter_coverage, true))
            && isset($series['filter_use'], $series['category_uses'], $series['location_uses'], $series['attribute_uses'], $series['other_uses']);
        return [
            'searches' => array_sum(array_filter($series['searches'], 'is_int')),
            'zero_results' => $zero_available ? array_sum(array_filter($series['zero_results'], 'is_int')) : null,
            'filter_use' => $filters_available ? array_sum(array_filter($series['filter_use'] ?? [], 'is_int')) : null,
            'category_uses' => $filters_available ? array_sum(array_filter($series['category_uses'] ?? [], 'is_int')) : null,
            'location_uses' => $filters_available ? array_sum(array_filter($series['location_uses'] ?? [], 'is_int')) : null,
            'attribute_uses' => $filters_available ? array_sum(array_filter($series['attribute_uses'] ?? [], 'is_int')) : null,
            'other_uses' => $filters_available ? array_sum(array_filter($series['other_uses'] ?? [], 'is_int')) : null,
            'filter_partial' => $filters_available && $this->eventPartial($series, 'search_filter_use'),
            'partial' => count(array_unique($coverage)) > 1 || in_array('partial', $coverage, true)
                || ($zero_available && (count(array_unique($zero_coverage)) > 1 || in_array('partial', $zero_coverage, true))),
        ];
    }

    protected function preview(array $status): array
    {
        $addons_url = 'https://api.webilia.com/go/stats-menu-cta';

        if ($status['code'] === 'active') return [];

        if ($status['code'] === 'ready_install')
        {
            return [
                'title' => __('Unlock Complete Analytics', 'listdom'),
                'body' => __('Your Webilia account can use Stats. Install it now; detailed Analytics reports are not available here yet.', 'listdom'),
                'action' => __('Install free Stats Addon', 'listdom'),
                'mode' => 'install',
                'url' => '',
                'learn_url' => $addons_url,
            ];
        }

        if ($status['code'] === 'installed_inactive')
        {
            return [
                'title' => __('Activate Stats Addon', 'listdom'),
                'body' => __('Stats Addon is installed but inactive. Activate it here to enable its features.', 'listdom'),
                'action' => __('Activate Stats Addon', 'listdom'),
                'mode' => 'activate',
                'url' => $status['url'],
                'learn_url' => $addons_url,
            ];
        }

        if ($status['code'] !== 'disconnected')
        {
            return [
                'title' => __('Unlock Complete Analytics', 'listdom'),
                'body' => __('Stats access or activation needs attention. Your local listing totals remain available; detailed Analytics reports are not available here yet.', 'listdom'),
                'action' => __('Review Stats access', 'listdom'),
                'url' => $status['url'],
                'learn_url' => $addons_url,
            ];
        }

        return [
            'title' => __('Unlock Complete Analytics', 'listdom'),
            'body' => __('Connect your Webilia account to check access to the free Stats Addon. Once installed and active, explore detailed listing, search, and engagement reports.', 'listdom'),
            'action' => __('Connect to Webilia', 'listdom'),
            'url' => $status['url'],
            'learn_url' => $addons_url,
        ];
    }

    protected function previewMetrics(): array
    {
        return [
            ['label' => __('Contacts', 'listdom'), 'icon' => 'contact'],
            ['label' => __('Favorites', 'listdom'), 'icon' => 'favorite'],
            ['label' => __('Searches', 'listdom'), 'icon' => 'search'],
            ['label' => __('Zero-result Searches', 'listdom'), 'icon' => 'trend'],
            ['label' => __('Phone Clicks', 'listdom'), 'icon' => 'phone'],
            ['label' => __('Website Clicks', 'listdom'), 'icon' => 'link'],
            ['label' => __('Direction Requests', 'listdom'), 'icon' => 'directions'],
        ];
    }

    /**
     * Match each Analytics icon with an installed Webilia or Font Awesome glyph.
     */
    public function iconClass(string $icon): string
    {
        $icons = [
            'listings' => 'far fa-building',
            'published' => 'webilia-icon wbli-checkmark-circle-fill',
            'categories' => 'far fa-folder',
            'views' => 'webilia-icon wbli-eye',
            'contact' => 'far fa-envelope',
            'search' => 'webilia-icon wbli-search-magnifier',
            'favorite' => 'far fa-heart',
            'trend' => 'webilia-icon wbli-chart-increase',
            'phone' => 'webilia-icon wbli-calling',
            'link' => 'webilia-icon wbli-link-square',
            'directions' => 'webilia-icon wbli-curvy-right-direction',
            'preview-contact' => 'far fa-envelope',
            'preview-search' => 'webilia-icon wbli-search-magnifier',
            'preview-favorite' => 'far fa-heart',
            'preview-phone' => 'webilia-icon wbli-calling',
            'preview-link' => 'webilia-icon wbli-link-square',
            'preview-directions' => 'webilia-icon wbli-curvy-right-direction',
            'preview-share' => 'webilia-icon wbli-share',
            'preview-trend' => 'webilia-icon wbli-chart-increase',
            'action-contact' => 'far fa-envelope',
            'action-email' => 'far fa-envelope',
            'action-whatsapp' => 'fab fa-whatsapp',
            'action-map-marker' => 'webilia-icon wbli-map-marker',
        ];

        return $icons[$icon] ?? 'webilia-icon wbli-question';
    }

    protected function cards(array $summary, array $status, ?array $report, ?array $search_summary, ?array $series, bool $rich_data, array $changes = [], bool $filtered = false, int $days = 30): array
    {
        // Dated Cards
        if ($status['code'] === 'active' && is_array($series) && ($rich_data || $filtered))
        {
            $days = (int) ($series['range_days'] ?? 30);
            $phone = $this->eventTotal($series, 'phone_click', 'phone_clicks');
            $website = $this->eventTotal($series, 'website_click', 'website_clicks');
            $directions = $this->eventTotal($series, 'direction_request', 'direction_requests');
            $favorites = $this->eventTotal($series, 'favorite_add', 'favorite_adds');
            $action_note = $filtered ? sprintf(__('Last %d days of tracked activity', 'listdom'), $days)
                : __('Last 30 days of tracked activity', 'listdom');

            return [
                ['label' => __('Total Listings', 'listdom'), 'value' => $summary['total'], 'icon' => 'listings', 'note' => '', 'change' => $this->listingCreationChange($summary['created_total'], false, $days)],
                ['label' => $filtered ? __('Listing Views', 'listdom') : __('Listing Views (Lifetime)', 'listdom'), 'value' => $filtered ? $this->eventTotal($series, 'listing_view', 'views') : $summary['views'], 'icon' => 'views', 'note' => $filtered ? $action_note : '', 'partial' => $filtered && $this->eventPartial($series, 'listing_view'), 'change' => $this->cardChange($changes['listing_view'] ?? null)],
                ['label' => $filtered ? __('Contacts', 'listdom') : __('Contacts (Lifetime)', 'listdom'), 'value' => $filtered ? $this->eventTotal($series, 'contact_submit', 'contacts') : ($report['contacts'] ?? null), 'icon' => 'contact', 'note' => $filtered ? $action_note : '', 'partial' => $filtered && $this->eventPartial($series, 'contact_submit'), 'change' => $this->cardChange($changes['contact_submit'] ?? null)],
                ['label' => __('Searches', 'listdom'), 'value' => $search_summary['searches'] ?? null, 'icon' => 'preview-search', 'note' => !empty($series['search_filtered_out']) ? __('Unavailable with listing filters', 'listdom') : $action_note, 'partial' => $this->eventPartial($series, 'search_executed'), 'change' => $this->cardChange($changes['search_executed'] ?? null)],
                ['label' => __('Favorite Adds', 'listdom'), 'value' => $favorites, 'icon' => 'preview-favorite', 'note' => $favorites === null ? __('Not tracked', 'listdom') : $action_note, 'partial' => $this->eventPartial($series, 'favorite_add'), 'change' => $this->cardChange($changes['favorite_add'] ?? null)],
                ['label' => __('Phone Clicks', 'listdom'), 'value' => $phone, 'icon' => 'preview-phone', 'note' => $phone === null ? __('Not tracked', 'listdom') : $action_note, 'partial' => $this->eventPartial($series, 'phone_click'), 'change' => $this->cardChange($changes['phone_click'] ?? null)],
                ['label' => __('Website Clicks', 'listdom'), 'value' => $website, 'icon' => 'preview-link', 'note' => $website === null ? __('Not tracked', 'listdom') : $action_note, 'partial' => $this->eventPartial($series, 'website_click'), 'change' => $this->cardChange($changes['website_click'] ?? null)],
                ['label' => __('Direction Requests', 'listdom'), 'value' => $directions, 'icon' => 'preview-directions', 'note' => $directions === null ? __('Not tracked', 'listdom') : $action_note, 'partial' => $this->eventPartial($series, 'direction_request'), 'change' => $this->cardChange($changes['direction_request'] ?? null)],
            ];
        }

        // Lifetime Cards
        $cards = [
            [
                'label' => __('Total Listings', 'listdom'),
                'value' => $summary['total'],
                'icon' => 'listings',
                'change' => $this->listingCreationChange($summary['created_total'], false, $days),
            ],
            [
                'label' => __('Published Listings', 'listdom'),
                'value' => $summary['published'],
                'icon' => 'published',
                'change' => $this->listingCreationChange($summary['created_published'], true, $days),
            ],
            [
                'label' => __('Categories', 'listdom'),
                'value' => $summary['categories'],
                'icon' => 'categories',
            ],
            [
                'label' => __('Listing Views (Lifetime)', 'listdom'),
                'value' => $summary['views'],
                'icon' => 'views',
                'change' => $this->cardChange($changes['listing_view'] ?? null),
            ],
        ];

        if ($status['code'] === 'active') $cards[] = [
            'label' => __('Lifetime Contact Submissions', 'listdom'),
            'value' => $report['contacts'] ?? null,
            'icon' => 'contact',
            'change' => $this->cardChange($changes['contact_submit'] ?? null),
        ];

        if ($status['code'] === 'active') $cards[] = [
            'label' => __('Searches', 'listdom'),
            'value' => $search_summary['searches'] ?? null,
            'icon' => 'preview-search',
            'note' => $search_summary !== null ? __('Last 30 days of tracked activity', 'listdom') : __('Not tracked yet', 'listdom'),
            'partial' => $this->eventPartial($series, 'search_executed'),
            'change' => $this->cardChange($changes['search_executed'] ?? null),
        ];

        return $cards;
    }

    protected function listingCreationChange(?int $created, bool $published = false, int $days = 30): ?array
    {
        if ($created === null) return null;

        return [
            'direction' => $created > 0 ? 'up' : 'flat',
            'value' => $created > 0 ? '+' . number_format_i18n($created) : '0',
            'period' => sprintf(__('created in last %d days', 'listdom'), $days),
            'tooltip' => sprintf($published
                ? __('%1$s currently published listings were created in the last %2$d days', 'listdom')
                : __('%1$s current listings were created in the last %2$d days', 'listdom'), number_format_i18n($created), $days),
        ];
    }

    /**
     * Match the event types in Stats' per-listing activity report.
     */
    protected function hasReportActivity(?array $series): bool
    {
        if (!is_array($series)) return false;

        foreach (['views', 'card_impressions', 'card_clicks', 'contacts', 'phone_clicks', 'email_clicks', 'website_clicks', 'whatsapp_clicks', 'direction_requests', 'share_clicks'] as $key)
        {
            if (is_array($series[$key] ?? null) && array_sum(array_filter($series[$key], 'is_int')) > 0) return true;
        }

        return false;
    }

    protected function cardChange(?array $counts): ?array
    {
        if (!isset($counts['current'], $counts['previous'])) return null;

        $current = (int) $counts['current'];
        $previous = (int) $counts['previous'];
        $days = isset($counts['days']) ? (int) $counts['days'] : 0;
        if (isset($counts['comparable']) && !$counts['comparable'] && empty($counts['partial']))
        {
            if (!$current) return null;
            return [
                'direction' => 'up',
                'value' => '+' . number_format_i18n($current),
                'period' => $days ? sprintf(__('in last %d days', 'listdom'), $days) : __('this month', 'listdom'),
                'tooltip' => $days
                    ? sprintf(__('%s tracked in the selected range; a comparable preceding range is unavailable', 'listdom'), number_format_i18n($current))
                    : sprintf(__('%s tracked this month; a complete last-month comparison is not available yet', 'listdom'), number_format_i18n($current)),
            ];
        }

        $direction = $current > $previous ? 'up' : ($current < $previous ? 'down' : 'flat');
        $value = $previous === 0 && $current > 0 ? '+' . number_format_i18n($current)
            : number_format_i18n($previous > 0 ? round(abs($current - $previous) * 100 / $previous) : 0) . '%';

        $tooltip = $days
            ? sprintf(__('%1$s in the selected range; %2$s in the preceding range', 'listdom'), number_format_i18n($current), number_format_i18n($previous))
            : sprintf(__('%1$s this month; %2$s over the same span last month', 'listdom'), number_format_i18n($current), number_format_i18n($previous));
        if (!empty($counts['partial'])) $tooltip .= '. ' . __('Tracking covered only part of one or both periods, so this comparison may be inaccurate.', 'listdom');

        return ['direction' => $direction, 'value' => $value, 'period' => $days ? sprintf(__('vs previous %d days', 'listdom'), $days) : __('vs last month', 'listdom'), 'tooltip' => $tooltip];
    }

    protected function eventTotal(?array $series, string $event, string $key): ?int
    {
        if (!is_array($series) || !is_array($series[$key] ?? null)) return null;
        // Older Stats series uses shared coverage for listing views.
        $coverage = $series['event_coverage'][$event] ?? ($event === 'listing_view' ? ($series['coverage'] ?? []) : []);
        if (!in_array('full', $coverage, true) && !in_array('partial', $coverage, true)) return null;

        return array_sum(array_filter($series[$key], 'is_int'));
    }

    /**
     * Exclude the current unfinished day when identifying collection gaps.
     */
    protected function eventPartial(?array $series, string $event): bool
    {
        $coverage = $series['event_coverage'][$event] ?? [];
        if (!is_array($coverage) || (!in_array('full', $coverage, true) && !in_array('partial', $coverage, true))) return false;
        $interrupted = $series['event_interrupted'][$event] ?? [];

        return in_array('unavailable', $coverage, true)
            || in_array('partial', array_slice($coverage, 0, -1), true)
            || (is_array($interrupted) && in_array(true, $interrupted, true));
    }

    /**
     * Compare the rate and action shares only when their collectors observed the same buckets.
     */
    protected function actionsComparable(?array $series, array $events = []): bool
    {
        if (!is_array($series)) return false;

        $reference = null;
        if (!$events) $events = ['listing_view', 'contact_submit', 'website_click', 'phone_click', 'direction_request'];
        foreach ($events as $event)
        {
            $coverage = $series['event_coverage'][$event] ?? null;
            if (!is_array($coverage) || (!in_array('full', $coverage, true) && !in_array('partial', $coverage, true))) return false;
            if ($reference !== null && $coverage !== $reference) return false;

            $interrupted = $series['event_interrupted'][$event] ?? null;
            if (is_array($interrupted) && in_array(true, $interrupted, true)) return false;
            if ($interrupted === null && in_array('partial', $coverage, true)) return false;
            if ($this->eventPartial($series, $event)) return false;

            $reference = $coverage;
        }

        return true;
    }

    protected function actionBreakdown(?array $series): array
    {
        return [
            ['label' => __('Phone Clicks', 'listdom'), 'value' => $this->eventTotal($series, 'phone_click', 'phone_clicks'), 'icon' => 'preview-phone', 'event' => 'phone_click'],
            ['label' => __('Website Clicks', 'listdom'), 'value' => $this->eventTotal($series, 'website_click', 'website_clicks'), 'icon' => 'preview-link', 'event' => 'website_click'],
            ['label' => __('Direction Requests', 'listdom'), 'value' => $this->eventTotal($series, 'direction_request', 'direction_requests'), 'icon' => 'preview-directions', 'event' => 'direction_request'],
            ['label' => __('Contact Form', 'listdom'), 'value' => $this->eventTotal($series, 'contact_submit', 'contacts'), 'icon' => 'action-contact', 'event' => 'contact_submit'],
        ];
    }

    protected function extraActions(?array $series): array
    {
        $actions = [
            ['label' => __('Email Clicks', 'listdom'), 'value' => $this->eventTotal($series, 'email_click', 'email_clicks'), 'icon' => 'action-email', 'event' => 'email_click'],
            ['label' => __('WhatsApp Clicks', 'listdom'), 'value' => $this->eventTotal($series, 'whatsapp_click', 'whatsapp_clicks'), 'icon' => 'action-whatsapp', 'event' => 'whatsapp_click'],
            ['label' => __('Share Clicks', 'listdom'), 'value' => $this->eventTotal($series, 'share_click', 'share_clicks'), 'icon' => 'preview-share', 'event' => 'share_click'],
            ['label' => __('Map Marker Clicks', 'listdom'), 'value' => $this->eventTotal($series, 'map_marker_click', 'map_marker_clicks'), 'icon' => 'action-map-marker', 'event' => 'map_marker_click'],
        ];

        if (!is_array($series['social_clicks'] ?? null)) return $actions;

        $social_icons = [
            'facebook' => 'fab fa-facebook-f',
            'twitter' => 'fab fa-x-twitter',
            'pinterest' => 'fab fa-pinterest-p',
            'linkedin' => 'fab fa-linkedin-in',
            'instagram' => 'fab fa-instagram',
            'whatsapp' => 'fab fa-whatsapp',
            'youtube' => 'fab fa-youtube',
            'tiktok' => 'fab fa-tiktok',
            'telegram' => 'fab fa-telegram',
        ];
        $socials = new LSD_Socials();
        foreach (LSD_Options::socials() as $network => $options)
        {
            $social = $socials->get($network, $options);
            if (!$social) continue;

            $counts = $series['social_clicks'][$network] ?? null;
            $coverage = $series['event_coverage']['social_click'] ?? [];
            $value = is_array($counts) && (in_array('full', $coverage, true) || in_array('partial', $coverage, true))
                ? array_sum(array_filter($counts, 'is_int')) : null;
            $actions[] = [
                'label' => sprintf(__('%s', 'listdom'), $social->label()),
                'value' => $value,
                'icon_class' => $social_icons[$network] ?? 'fas fa-globe',
                'event' => 'social_click',
                'social' => true,
            ];
        }

        return $actions;
    }

    protected function authorActions(?array $series): array
    {
        return [
            ['label' => __('Phone Clicks', 'listdom'), 'value' => $this->eventTotal($series, 'phone_click', 'author_phone_clicks'), 'icon' => 'preview-phone', 'event' => 'phone_click'],
            ['label' => __('Email Clicks', 'listdom'), 'value' => $this->eventTotal($series, 'email_click', 'author_email_clicks'), 'icon' => 'action-email', 'event' => 'email_click'],
            ['label' => __('Website Clicks', 'listdom'), 'value' => $this->eventTotal($series, 'website_click', 'author_website_clicks'), 'icon' => 'preview-link', 'event' => 'website_click'],
            ['label' => __('Contact Form', 'listdom'), 'value' => $this->eventTotal($series, 'contact_submit', 'contacts'), 'icon' => 'action-contact', 'event' => 'contact_submit'],
        ];
    }

    protected function actionGroups(?array $series): array
    {
        $actions = $this->extraActions($series);
        $other = array_values(array_filter($actions, static function ($action)
        {
            return empty($action['social']);
        }));
        $social = array_values(array_filter($actions, static function ($action)
        {
            return !empty($action['social']);
        }));

        $groups = [['label' => __('Primary Actions', 'listdom'), 'actions' => $this->actionBreakdown($series)]];
        if ($other) $groups[] = ['label' => __('Additional Actions', 'listdom'), 'actions' => $other];
        $groups[] = ['label' => __('Listing Author', 'listdom'), 'actions' => $this->authorActions($series)];
        if ($social) $groups[] = ['label' => __('Social Profile Clicks', 'listdom'), 'actions' => $social];

        foreach ($groups as &$group)
        {
            $available = array_filter($group['actions'], static function ($action)
            {
                return is_int($action['value']);
            });
            $group['total'] = array_sum(array_column($available, 'value'));
            $group['largest'] = $available ? max(array_column($available, 'value')) : 0;
            $group['comparable'] = count($available) === count($group['actions']) && $this->actionsComparable($series, array_unique(array_column($available, 'event')));
        }
        unset($group);

        return $groups;
    }
}
