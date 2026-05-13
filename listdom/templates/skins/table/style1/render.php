<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Table $this */

// Columns metadata
$display = $this->get_table_columns_display();
$fields = $display['fields'];

// Listings
$ids = $this->listings;
?>
<?php foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
    <tr class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
        <?php foreach ($display['columns_union'] as $key => $column): ?>
            <?php
            $key_class = sanitize_html_class($key);
            $enabled = isset($display['desktop_enabled'][$key]) ? (int) $display['desktop_enabled'][$key] : 0;
            $width = isset($display['desktop_width'][$key]) ? (int) $display['desktop_width'][$key] : 150;
            if ($width <= 0) $width = 150;

            $classes = ['lsd-listing-' . $key_class, 'lsd-table-column', 'lsd-table-column-' . $key_class];
            if (!$enabled) $classes[] = 'lsd-table-column-hidden';
            ?>
            <td class="<?php echo esc_attr(implode(' ', $classes)); ?>" data-column="<?php echo esc_attr($key); ?>" style="width: <?php echo esc_attr($width); ?>px;" <?php echo $fields->schema($key); ?>>
                <div class="lsd-table-<?php echo esc_attr($key_class); ?>">
                    <?php echo $fields->content($key, $listing, $this); ?>
                </div>
            </td>
        <?php endforeach; ?>
    </tr>
<?php endforeach; ?>
