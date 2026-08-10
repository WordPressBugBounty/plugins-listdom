<?php
// no direct access
defined('ABSPATH') || die();

/** @var int $post_id */

$post_attributes = get_post_meta($post_id, 'lsd_attributes', true);
$layout = isset($layout) && in_array($layout, ['column', 'row', 'table'], true) ? $layout : 'column';

if (!is_array($post_attributes)) $post_attributes = [];
if (!count($post_attributes)) return '';

// Attributes
$terms = LSD_Main::get_attributes();

$attribute_context = LSD_Taxonomies_Attribute::context([
    'post_id' => (int) $post_id,
]);

$attributes = [];
foreach ($terms as $term)
{
    if (!LSD_Taxonomies_Attribute::applies((int) $term->term_id, $attribute_context)) continue;
    $attributes[$term->slug] = $term;
}

$render_attribute_items = static function (LSD_Entity_Attribute $att, $raw_value, string $class): string
{
    $items = [];

    if ($att->type === 'checkbox')
    {
        $items = is_array($raw_value) ? $raw_value : array_map('trim', explode(',', (string) $raw_value));
    }
    else if ($att->type === 'dropdown')
    {
        $items = is_array($raw_value) ? $raw_value : [(string) $raw_value];
    }

    $items = array_values(array_filter($items, static function ($item)
    {
        return trim((string) $item) !== '';
    }));

    if (!count($items)) return '';

    $output = [];
    foreach ($items as $item) $output[] = '<span class="' . esc_attr($class) . '">' . esc_html((string) $item) . '</span>';

    return implode(' ', $output);
};
?>
<div class="lsd-listing-attributes lsd-layout-<?php echo esc_attr($layout); ?>">
<?php $i = 0; $pending_separator = null; foreach ($attributes as $key => $attribute): $att = new LSD_Entity_Attribute($attribute->term_id); ?>
    <?php
    $is_separator = $att->type === 'separator';
    $value_exists = isset($post_attributes[$key])
        && !((is_string($post_attributes[$key]) && trim($post_attributes[$key]) === '') || (is_array($post_attributes[$key]) && count(array_filter($post_attributes[$key])) === 0));

    if ($is_separator)
    {
        if (isset($show_separator) && $show_separator) $pending_separator = $attribute;
        continue;
    }

    if (!$value_exists) continue;
    ?>
    <?php
    $value_tag = $att->is_rich_editor() ? 'div' : 'span';
    $value = in_array($att->type, ['checkbox', 'dropdown'], true)
        ? $render_attribute_items($att, $post_attributes[$key], 'lsd-attr-value-item')
        : LSD_Kses::element($att->render($post_attributes[$key]));
    if (trim($value) === '') continue;
    ?>
    <?php if ($pending_separator): ?>
        <?php
            if ($layout === 'column' && $i != 0)
            {
                echo '</div>';
                $i = 0;
            }
        ?>
        <div class="lsd-attributes-separator-wrap">
            <div class="lsd-separator"><?php echo esc_html($pending_separator->name); ?></div>
        </div>
        <?php $pending_separator = null; ?>
    <?php endif; ?>
    <?php if ($layout === 'column' && $i == 0): ?><div class="lsd-row"><?php endif; ?>
    <div class="<?php echo $layout === 'column' ? 'lsd-col-6 ' : ''; ?>lsd-attr lsd-attr-<?php echo esc_attr($att->type); ?>" <?php echo LSD_Entity_Attribute::schema($attribute->term_id); ?>>
        <span class="lsd-attr-key">
            <?php if (isset($show_icons) && $show_icons): ?><span class="lsd-attr-icon"><?php echo LSD_Kses::element($att->icon()); ?></span><?php endif; ?>
            <?php if (isset($show_attribute_title) && $show_attribute_title): ?>
                <span class="lsd-attr-label"><?php echo esc_html($attribute->name); ?></span><?php if ($layout !== 'table'): ?>:<?php endif; ?>
            <?php endif; ?>
        </span>
        <<?php echo esc_attr($value_tag); ?> class="lsd-attr-value"><?php echo LSD_Kses::element($value); ?></<?php echo esc_attr($value_tag); ?>>
    </div>
    <?php if ($layout === 'column' && $i == 1): ?></div><?php endif; ?>
    <?php if ($layout === 'column'): $i++; if ($i == 2) $i = 0; endif; ?>
<?php endforeach; ?>
<?php if ($layout === 'column' && $i != 0) echo '</div>'; ?>
</div>
