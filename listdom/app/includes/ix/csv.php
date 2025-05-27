<?php

class LSD_IX_CSV extends LSD_IX_Array
{
    /**
     * @return void
     */
    public function csv()
    {
        $data = $this->data();

        header('Content-Disposition: attachment; filename=listings-' . current_time('Y-m-d-H-i') . '.csv');
        header('Content-Type: text/csv; charset=utf-8');

        $fh = fopen('php://output', 'w');
        fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));

        foreach ($data as $listing) fputcsv($fh, $listing);

        exit;
    }

    /**
     * @return array
     */
    protected function data(): array
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

    public function import_by_mapping(string $file, array $mappings, int $offset = 0, int $limit = -1): array
    {
        // Libraries
        $importer = new LSD_IX();
        $mapper = new LSD_IX_Mapping();

        // Import
        $fh = fopen($file, 'r');

        $r = 0; // Row Counter
        $i = 0; // Imported Counter

        // Upserted Listings
        $listings = [];

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
            $results = LSD_IX_Array::map($row, $mappings);
            if (!count($results)) continue;

            $listing = $results[0];
            $mapped = $results[1];

            $listing_id = $importer->save($listing);

            // Add to Upserted Listings
            $listings[] = $listing_id;

            // New Listing Imported
            do_action('lsd_csv_listing_imported', $listing_id, $row, $mapped);
        }

        // Close the File
        fclose($fh);

        // Number of Imported Rows
        return [$i, $listings];
    }
}
