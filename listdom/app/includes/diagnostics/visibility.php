<?php

/** Target-membership inspection for anonymous visitors, independent of public feeds. */
class LSD_Diagnostics_Visibility
{
    public function inspect(array $input, array $display, LSD_Diagnostics_Report $report)
    {
        $id = (int) $input['listing_id'];
        $listing = LSD_Diagnostics_Report::resource($id, [LSD_Base::PTYPE_LISTING]);
        if (is_wp_error($listing)) return $listing;
        $search = $input['search'] ?? [];
        if (!empty($search['search_module']))
        {
            $module = LSD_Diagnostics_Report::resource((int) $search['search_module'], [LSD_Base::PTYPE_SEARCH]);
            if (is_wp_error($module)) return $module;
        }
        $vars = (new LSD_Abilities_Search())->parameters($search);
        if (is_wp_error($vars)) return $vars;
        $skin = $display['skin'];
        $pms = LSD_Diagnostics_Report::callback('lsd_skins_atts', 'LSDPACPMS\Addon', 'search');
        $guest = $pms ? [$pms[0], 'search_for_user'] : null;
        $sub = LSD_Diagnostics_Report::callback('lsd_before_search', 'LSDPACSUB\Addon', 'filter');
        $franchise = LSD_Diagnostics_Report::callback('lsd_before_search', 'LSDPACFS\Search', 'search');
        $where = LSD_Diagnostics_Report::callback('lsd_where_query', 'LSDPACFS\Search', 'where');
        // Only reviewed owning-package methods participate. Never call the filter chain.
        $args = LSD_Diagnostics_Report::isolated(static function () use ($skin, $vars, $guest, $franchise)
        {
            (new LSD_Search_Service())->prepare_inspection($skin, $skin->atts, $vars, $guest);
            $args = $skin->args;
            if ($franchise) $args = $franchise($args, $skin);
            return $args;
        });
        if ($sub)
        {
            // Authorize with the normal capability filters restored before package evaluation.
            $packages = $skin->filter_options['packages'] ?? [];
            foreach (is_array($packages) ? array_filter(array_map('absint', $packages)) : [] as $package_id)
            {
                $resource = LSD_Diagnostics_Report::resource($package_id, ['listdom-package']);
                if (is_wp_error($resource)) return $resource;
            }
            $args = LSD_Diagnostics_Report::isolated(static function () use ($sub, $args, $skin) { return $sub($args, $skin); });
        }
        $ref = 'listing:' . $id;
        $report->fact($ref, 'stored_status', (string) $listing->post_status);
        $report->fact($ref, 'password_protected', $listing->post_password !== '');
        $public = $listing->post_status === 'publish' && $listing->post_password === '';
        $report->fact($ref, 'anonymous_public_state', $public);
        if (!$public) $report->finding('not_publicly_published', 'warning', __('The listing is not in an unprotected published state for an anonymous visitor.', 'listdom'), [$ref]);
        $included = empty($args['post__in']) || in_array($id, array_map('intval', $args['post__in']), true);
        // WP_Query gives a non-empty include list precedence over post__not_in.
        $excluded = empty($args['post__in']) && !empty($args['post__not_in']) && in_array($id, array_map('intval', $args['post__not_in']), true);
        $report->fact($ref, 'view_inclusion_matches', $included);
        $report->fact($ref, 'view_excluded', $excluded);
        if (!$included || $excluded) $report->finding('view_id_constraint', 'warning', __('The selected view excludes this listing or includes other listing IDs only.', 'listdom'), [$ref, 'view:' . $input['view_id']]);
        $args['post__in'] = [$id];
        unset($args['post__not_in']);
        $args['lsd-init'] = true;
        $args['fields'] = 'ids';
        $args['posts_per_page'] = 1;
        $args['paged'] = 1;
        $args['offset'] = 0;
        $args['orderby'] = 'ID';
        $args['no_found_rows'] = true;
        $args['cache_results'] = false;
        $args['update_post_meta_cache'] = false;
        $args['update_post_term_cache'] = false;
        $args['suppress_filters'] = false;
        // Keep the view's status and taxonomy constraints, independent of admin/public-feed serializers.
        $matched = $included && !$excluded && $this->matches($args, $skin, $where);
        $report->fact($ref, 'supported_query_matches', $matched);
        if (!$matched) $report->finding('view_query_mismatch', 'warning', __('The listing does not match the supported saved-view and supplied search constraints.', 'listdom'), [$ref, 'view:' . $input['view_id']]);
        // Explain independent clauses using at most five additional one-target queries.
        foreach (['post_status' => 'status', 'tax_query' => 'taxonomy', 'meta_query' => 'metadata', 's' => 'keyword', 'author__in' => 'author'] as $key => $label)
        {
            if (empty($args[$key]) && ($label !== 'author' || empty($args['author__not_in']))) continue;
            if ($label === 'taxonomy' && !$this->has_taxonomy_clause($args[$key])) continue;
            $only = ['post_type' => LSD_Base::PTYPE_LISTING, 'post_status' => $listing->post_status,
                'post__in' => [$id], 'fields' => 'ids', 'posts_per_page' => 1, 'no_found_rows' => true,
                'cache_results' => false, 'update_post_meta_cache' => false, 'update_post_term_cache' => false];
            if (isset($args[$key])) $only[$key] = $args[$key];
            if ($label === 'author' && isset($args['author__not_in'])) $only['author__not_in'] = $args['author__not_in'];
            $report->fact($ref, $label . '_constraint_matches', $this->matches($only, $skin, null));
        }
        $eligible = $public && $matched;
        if (!empty($input['page_id']))
        {
            $page = get_post((int) $input['page_id']);
            $page_public = $page instanceof WP_Post && $page->post_status === 'publish' && $page->post_password === '';
            $report->fact('post:' . (int) $input['page_id'], 'anonymous_public_state', $page_public);
            $eligible = $eligible && $page_public;
            if (!$page_public) $report->finding('page_not_public', 'warning', __('The selected local page is not published without password protection for anonymous visitors.', 'listdom'), ['post:' . (int) $input['page_id']]);
        }
        if ($pms)
        {
            $allowed = $pms[0]->ai_visibility_public_listing_access(true, $listing);
            $report->fact($ref, 'pms_guest_access', $allowed);
            $eligible = $eligible && $allowed;
            if (!$allowed) $report->finding('pms_guest_restricted', 'warning', __('The listing category is restricted for guests by Paid Member Subscriptions.', 'listdom-pms'), [$ref]);
        }
        if ($sub) $report->fact('view:' . $input['view_id'], 'subscription_package_filter_checked', true);
        if ($franchise && $where) $report->fact('view:' . $input['view_id'], 'franchise_parent_rule_checked', true);
        $visibility = LSD_Diagnostics_Report::callback('lsd_listing_visited', 'LSDPACVIS\Addon', 'listing');
        if ($visibility)
        {
            $maximum = LSD_Options::settings()['visibility_max_visits'] ?? '';
            $maximum = is_numeric($maximum) && $maximum >= 0 ? (int) $maximum : '';
            $package_max = LSD_Diagnostics_Report::callback('lsd_visibility_max_visits', 'LSDPACSUB\Addon', 'max_visits');
            if ($package_max)
            {
                $subscription = (int) get_post_meta($id, 'lsd_subscription', true);
                if ($subscription)
                {
                    $resource = LSD_Diagnostics_Report::resource($subscription, ['listdom-subscription']);
                    if (is_wp_error($resource)) return $resource;
                    $package_id = (int) get_post_meta($subscription, 'lsd_package', true);
                    $resource = LSD_Diagnostics_Report::resource($package_id, ['listdom-package']);
                    if (is_wp_error($resource)) return $resource;
                    $maximum = $package_max($maximum, $listing);
                }
            }
            $evidence = $visibility[0]->inspection($listing, true, $maximum);
            foreach ($evidence as $key => $value) $report->fact($ref . ':visibility', $key, $value);
            $managed = in_array($listing->post_status, ['publish', LSD_Base::STATUS_OFFLINE], true);
            $report->fact($ref . ':visibility', 'managed_state', $managed);
            $approval = get_post_meta($id, 'lsd_visibility_publish_approved', true);
            $report->fact($ref . ':visibility', 'stored_publish_approval', $approval === '' ? null : (bool) $approval);
            if ($managed) $eligible = $eligible && $evidence['visible'];
            if ($managed && !$evidence['visible']) $report->finding('visibility_rule_inactive', 'warning', __('Visibility date or visit rules currently require this listing to be offline. Stored status and the next lifecycle evaluation may differ.', 'listdom-visibility'), [$ref . ':visibility']);
            $report->limit('visibility_lifecycle', __('Visibility rules were evaluated without migrating dates, publishing, changing approval or running lifecycle actions.', 'listdom-visibility'));
        }
        if (!empty($skin->filter_options['circle']) && !isset($args['lsd-circle']))
            $report->limit('geocoding_required', __('A named radius center requires geocoding and was not evaluated.', 'listdom'));
        if (!empty($vars['sf-ai']) || (!empty($vars['sf-s']) && LSD_Diagnostics_Report::callback('lsd_skins_atts', 'LSDPACAPS\\Addon', 'textsearch')))
            $report->limit('ai_search_not_executed', __('AI search interpretation was not executed; keyword evidence covers the core text query only.', 'listdom'));
        foreach (['ai', 'booking', 'inquiry'] as $key)
            if (!empty($skin->filter_options[$key])) $report->limit('dynamic_search', __('AI, booking or inquiry search constraints require a separate supported execution path and were not evaluated.', 'listdom'));
        $report->fact($ref, 'eligible_under_inspected_rules', $eligible);
        $report->limit('runtime_filters', __('Only core constraints and the named deterministic add-on rules were evaluated. Personalized, AI, opaque query filters and theme behavior remain unverified.', 'listdom'));
        $report->limit('ranking_and_rendering', __('Query membership does not establish ranking, pagination or appearance on a rendered page. No visitor event was emitted.', 'listdom'));
        return true;
    }

    /** A relation marker alone does not constrain a taxonomy query. */
    private function has_taxonomy_clause(array $query): bool
    {
        if (!empty($query['taxonomy'])) return true;
        foreach ($query as $clause)
            if (is_array($clause) && $this->has_taxonomy_clause($clause)) return true;
        return false;
    }

    private function matches(array $args, LSD_Skins $skin, $franchise): bool
    {
        return LSD_Diagnostics_Report::isolated(static function () use ($args, $skin, $franchise)
        {
            add_filter('posts_join', [$skin, 'query_join'], 10, 2);
            add_filter('posts_where', [$skin, 'query_where'], 10, 2);
            if ($franchise) add_filter('lsd_where_query', $franchise, 10, 3);
            $query = new WP_Query($args);
            return count($query->posts) > 0;
        });
    }
}
