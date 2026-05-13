<?php

class LSD_IX_CSV extends LSD_IX_Array
{
    /**
     * @return void
     */
    public function csv(string $type = 'listings')
    {
        $type = LSD_IX::normalize_import_type($type);
        $data = $this->data($type);

        header('Content-Disposition: attachment; filename=listdom-' . sanitize_key($this->filename_type($type)) . '-' . current_time('Y-m-d-H-i') . '.csv');
        header('Content-Type: text/csv; charset=utf-8');

        $fh = fopen('php://output', 'w');
        fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));

        foreach ($data as $listing) fputcsv($fh, $listing);

        exit;
    }

    public function export_columns(string $type): array
    {
        $taxonomy = LSD_IX::taxonomy_for_type($type);

        if ($taxonomy)
        {
            $mapping = new LSD_IX_Mapping();
            $meta_fields = $mapping->taxonomy_meta_fields($taxonomy);

            return $this->term_columns($taxonomy, $meta_fields);
        }

        return apply_filters('lsd_csv_export_columns', $this->columns());
    }

    public function export_rows(string $type, int $offset, int $limit): array
    {
        $taxonomy = LSD_IX::taxonomy_for_type($type);
        $rows = [];

        if ($taxonomy)
        {
            $mapping = new LSD_IX_Mapping();
            $meta_fields = $mapping->taxonomy_meta_fields($taxonomy);
            $terms = $this->taxonomy_data_chunk($taxonomy, $offset, $limit);

            foreach ($terms as $term)
            {
                $row = $this->term_row($taxonomy, $term, array_keys($meta_fields));
                $rows[] = apply_filters('lsd_csv_export_term_data', $row, $taxonomy, $term);
            }

            return [$rows, count($rows)];
        }

        $listings = $this->listings_chunk($offset, $limit);
        foreach ($listings as $listing)
        {
            $listing = (array) $listing;
            $row = $this->row($listing);
            $rows[] = apply_filters('lsd_csv_export_data', $row, $listing['ID'], $listing);
        }

        return [$rows, count($rows)];
    }

    /**
     * @return array
     */
    protected function data(string $type = 'listings'): array
    {
        $taxonomy = LSD_IX::taxonomy_for_type($type);

        return $taxonomy ? $this->term_data($taxonomy) : $this->listing_data();
    }

    protected function listing_data(): array
    {
        // Listings
        $listings = $this->listings();

        // Apply Filter on Columns
        $columns = apply_filters('lsd_csv_export_columns', $this->columns());

        $data = [$columns];
        foreach ($listings as $listing)
        {
            // Force to Array
            $listing = (array) $listing;

            // Listing
            $row = $this->row($listing);

            // Apply Filters on Data
            $data[] = apply_filters('lsd_csv_export_data', $row, $listing['ID'], $listing);
        }

        return $data;
    }

    protected function term_data(string $taxonomy): array
    {
        $terms = $this->taxonomy_data($taxonomy);

        $mapping = new LSD_IX_Mapping();
        $meta_fields = $mapping->taxonomy_meta_fields($taxonomy);

        $columns = $this->term_columns($taxonomy, $meta_fields);

        $data = [$columns];
        foreach ($terms as $term)
        {
            $row = $this->term_row($taxonomy, $term, array_keys($meta_fields));
            $data[] = apply_filters('lsd_csv_export_term_data', $row, $taxonomy, $term);
        }

        return $data;
    }

    protected function term_columns(string $taxonomy, array $meta_fields): array
    {
        $columns = [
            esc_html__('Name', 'listdom'),
            esc_html__('Slug', 'listdom'),
            esc_html__('Description', 'listdom'),
            esc_html__('Image', 'listdom'),
        ];

        if (is_taxonomy_hierarchical($taxonomy))
        {
            array_splice($columns, 3, 0, esc_html__('Parent Slug', 'listdom'));
        }

        foreach ($meta_fields as $key => $field)
        {
            $columns[] = $field['label'] ?? $this->meta_label($key);
        }

        return apply_filters('lsd_csv_export_term_columns', $columns, $meta_fields, $taxonomy);
    }

    protected function term_row(string $taxonomy, array $term, array $meta_keys): array
    {
        $meta = isset($term['meta']) && is_array($term['meta']) ? $term['meta'] : [];

        $row = [
            $term['name'] ?? '',
            $term['slug'] ?? '',
            $term['description'] ?? '',
            $term['image'] ?? '',
        ];

        if (is_taxonomy_hierarchical($taxonomy))
        {
            array_splice($row, 3, 0, $term['parent_slug'] ?? '');
        }

        foreach ($meta_keys as $meta_key)
        {
            $value = $meta[$meta_key] ?? '';
            $row[] = $this->normalize_term_meta_value($meta_key, $value, $taxonomy);
        }

        return $row;
    }

    protected function meta_label(string $meta_key): string
    {
        $label = str_replace(['lsd_', '_'], ['', ' '], $meta_key);
        return ucwords($label);
    }

    public function import_by_mapping(string $file, array $mappings, int $offset = 0, int $limit = -1, string $type = 'listings', array $options = []): array
    {
        // Libraries
        $importer = new LSD_IX();
        $mapper = new LSD_IX_Mapping();

        // Import Type
        $type = LSD_IX::normalize_import_type($type);
        $taxonomy = LSD_IX::taxonomy_for_type($type);

        // Import
        $fh = fopen($file, 'r');

        $r = 0; // Row Counter
        $i = 0; // Imported Counter

        // Upserted Records
        $records = [];

        // Pending Terms for Taxonomies
        $terms_to_import = [];
        $mapped_terms = [];

        // File Delimiter
        $delimiter = $mapper->delimiter($file);

        // Read File
        while (($row = fgetcsv($fh, 0, $delimiter)) !== false)
        {
            // Row Counter
            $r++;

            // Skip Headers
            if ($r === 1) continue;

            // Skip Imported Rows
            if ($r <= $offset) continue;

            // Limit Reached
            if ($limit > 0 && $i >= $limit) break;

            // Imported Row Counter
            $i++;

            // Map Data
            $results = $taxonomy ? LSD_IX_Array::map_term($row, $mappings, $taxonomy, $options) : LSD_IX_Array::map($row, $mappings, $options);
            if (!count($results)) continue;

            if ($taxonomy)
            {
                $terms_to_import[$r] = $results[0];
                $mapped_terms[$r] = [$row, $results[1]];
            }
            else
            {
                $listing = $results[0];
                $mapped = $results[1];

                $listing_id = $importer->save($listing);

                // Add to Upserted Listings
                $records[] = $listing_id;

                // New Listing Imported
                do_action('lsd_csv_listing_imported', $listing_id, $row, $mapped);
            }
        }

        if ($taxonomy && count($terms_to_import))
        {
            $imported_terms = $importer->import_term_collection($terms_to_import, $taxonomy, $options);

            foreach ($imported_terms as $index => $term_id)
            {
                $records[] = $term_id;

                $mapped_row = $mapped_terms[$index] ?? [[], []];
                $row = $mapped_row[0] ?? [];
                $mapped = $mapped_row[1] ?? [];

                do_action('lsd_csv_term_imported', $term_id, $row, $mapped, $taxonomy);
            }
        }

        // Close the File
        fclose($fh);

        // Number of Imported Rows
        return [$i, $records];
    }
}
