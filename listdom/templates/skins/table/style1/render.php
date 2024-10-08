<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Table $this */

// Fields
$fields = new LSD_Fields();
$columns = isset($this->skin_options['columns']) && is_array($this->skin_options['columns']) && count($this->skin_options['columns'])
    ? $this->skin_options['columns']
    : $fields->get();

// Listings
$ids = $this->listings;
?>
<?php foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); ?>
    <tr class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
        <?php foreach ($columns as $key => $column): ?>
            <?php if (isset($column['enabled']) && $column['enabled'] == '1'): ?>
                <td class="lsd-listing-<?php echo $key; ?>" <?php echo $fields->schema($key); ?>>
                    <?php echo $fields->content($key, $listing); ?>
                </td>
            <?php endif; ?>
        <?php endforeach; ?>
    </tr>
<?php endforeach; ?>
