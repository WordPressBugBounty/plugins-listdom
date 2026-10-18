<?php

class LSD_Analytics_Summary
{
    /**
     * Build listing conditions for queries that use `listings` as the posts alias.
     */
    public static function where(array $filters): array
    {
        $where = '';
        $args = [];

        // Category and Location Terms
        foreach (['category' => LSD_Base::TAX_CATEGORY, 'location' => LSD_Base::TAX_LOCATION] as $key => $taxonomy)
        {
            $term = absint($filters[$key] ?? 0);
            if (!$term) continue;
            $terms = [$term];
            if (is_taxonomy_hierarchical($taxonomy))
            {
                $children = get_term_children($term, $taxonomy);
                if (!is_wp_error($children)) $terms = array_merge($terms, array_map('absint', $children));
            }
            $where .= ' AND EXISTS (SELECT 1 FROM `#__term_relationships` AS relation
                INNER JOIN `#__term_taxonomy` AS taxonomy ON taxonomy.term_taxonomy_id = relation.term_taxonomy_id
                WHERE relation.object_id = listings.ID AND taxonomy.taxonomy = %s AND taxonomy.term_id IN ('
                . implode(', ', array_fill(0, count($terms), '%d')) . '))';
            $args[] = $taxonomy;
            array_push($args, ...$terms);
        }

        // Listing Title
        if (!empty($filters['search']))
        {
            $db = new LSD_DB();
            $where .= ' AND listings.post_title LIKE %s';
            $args[] = '%' . $db->get_DBO()->esc_like((string) $filters['search']) . '%';
        }

        return [$where, $args];
    }

    /**
     * Count current listings and creations in the selected range matching the filters.
     */
    public function filtered(array $filters): array
    {
        // Current Listings
        $db = new LSD_DB();
        $where = 'listings.post_type = %s AND listings.post_status <> %s';
        $args = [LSD_Base::PTYPE_LISTING, LSD_Base::STATUS_TRASH];

        // Shared Listing Filters
        [$filter_where, $filter_args] = self::where($filters);
        $where .= $filter_where;
        array_push($args, ...$filter_args);
        $start = $this->rangeStart((int) ($filters['days'] ?? 30));
        $counts = $db->select($db->prepare("SELECT COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN listings.post_status = %s THEN 1 ELSE 0 END), 0) AS published,
            COALESCE(SUM(CASE WHEN listings.post_date >= %s AND listings.post_date <= %s THEN 1 ELSE 0 END), 0) AS created_total,
            COALESCE(SUM(CASE WHEN listings.post_status = %s AND listings.post_date >= %s AND listings.post_date <= %s THEN 1 ELSE 0 END), 0) AS created_published
            FROM `#__posts` AS listings WHERE $where", array_merge([
                LSD_Base::STATUS_PUBLISHED, $start, current_time('mysql'),
                LSD_Base::STATUS_PUBLISHED, $start, current_time('mysql'),
            ], $args)), 'loadAssoc');

        return [
            'total' => is_array($counts) ? (int) $counts['total'] : null,
            'published' => is_array($counts) ? (int) $counts['published'] : null,
            'created_total' => is_array($counts) ? (int) $counts['created_total'] : null,
            'created_published' => is_array($counts) ? (int) $counts['created_published'] : null,
        ];
    }

    /**
     * Core counts only. Null means the source could not be read; zero is a measured value.
     *
     * Total listings and views include every non-trash listing status. Views sum
     * stored lsd_visits values, not the per-listing start-value filter or people.
     */
    public function get(int $days = 30): array
    {
        $db = new LSD_DB();
        $start = $this->rangeStart($days);
        $now = current_time('mysql');

        // Listing Counts
        $counts = $db->select($db->prepare(
            "SELECT COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN post_status = %s THEN 1 ELSE 0 END), 0) AS published,
                COALESCE(SUM(CASE WHEN post_date < %s THEN 1 ELSE 0 END), 0) AS older_total,
                COALESCE(SUM(CASE WHEN post_status = %s AND post_date < %s THEN 1 ELSE 0 END), 0) AS older_published,
                COALESCE(SUM(CASE WHEN post_date >= %s AND post_date <= %s THEN 1 ELSE 0 END), 0) AS created_total,
                COALESCE(SUM(CASE WHEN post_status = %s AND post_date >= %s AND post_date <= %s THEN 1 ELSE 0 END), 0) AS created_published
            FROM `#__posts`
            WHERE post_type = %s AND post_status <> %s",
            LSD_Base::STATUS_PUBLISHED,
            $start,
            LSD_Base::STATUS_PUBLISHED,
            $start,
            $start,
            $now,
            LSD_Base::STATUS_PUBLISHED,
            $start,
            $now,
            LSD_Base::PTYPE_LISTING,
            LSD_Base::STATUS_TRASH
        ), 'loadAssoc');

        $categories = wp_count_terms(LSD_Base::TAX_CATEGORY, [
            'hide_empty' => false,
        ]);

        // Group by post ID so duplicate imported meta rows cannot inflate views.
        $views = $db->select($db->prepare(
            "SELECT COALESCE(SUM(totals.visits), 0)
            FROM (
                SELECT listings.ID, MAX(GREATEST(CAST(visits.meta_value AS SIGNED), 0)) AS visits
                FROM `#__posts` AS listings
                INNER JOIN `#__postmeta` AS visits
                    ON visits.post_id = listings.ID AND visits.meta_key = %s
                WHERE listings.post_type = %s AND listings.post_status <> %s
                GROUP BY listings.ID
            ) AS totals",
            'lsd_visits',
            LSD_Base::PTYPE_LISTING,
            LSD_Base::STATUS_TRASH
        ), 'loadResult');

        return [
            'total' => is_array($counts) ? (int) $counts['total'] : null,
            'published' => is_array($counts) ? (int) $counts['published'] : null,
            'older_total' => is_array($counts) ? (int) $counts['older_total'] : null,
            'older_published' => is_array($counts) ? (int) $counts['older_published'] : null,
            'created_total' => is_array($counts) ? (int) $counts['created_total'] : null,
            'created_published' => is_array($counts) ? (int) $counts['created_published'] : null,
            'categories' => is_numeric($categories) ? (int) $categories : null,
            'views' => is_numeric($views) ? (int) $views : null,
        ];
    }

    /**
     * Match the report window's first local calendar day.
     */
    private function rangeStart(int $days): string
    {
        if (!in_array($days, [7, 30, 90], true)) $days = 30;
        return (new DateTimeImmutable('today', LSD_Base::timezone()))->modify('-' . ($days - 1) . ' days')->format('Y-m-d H:i:s');
    }
}
