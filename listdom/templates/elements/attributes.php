<?php
// no direct access
defined('ABSPATH') || die();

/** @var int $post_id */

$post_attributes = get_post_meta($post_id, 'lsd_attributes', true);

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
?>
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
    <?php $value_tag = $att->is_rich_editor() ? 'div' : 'span'; ?>
    <?php if ($pending_separator): ?>
        <?php
            if ($i != 0)
            {
                echo '</div>';
                $i = 0;
            }
        ?>
        <div class="lsd-row">
            <div class="lsd-col-12">
                <div class="lsd-separator"><?php echo esc_html($pending_separator->name); ?></div>
            </div>
        </div>
        <?php $pending_separator = null; ?>
    <?php endif; ?>
    <?php if ($i == 0): ?><div class="lsd-row"><?php endif; ?>
    <div class="lsd-col-6" <?php echo LSD_Entity_Attribute::schema($attribute->term_id); ?>>
        <span class="lsd-attr-key">
            <?php if (isset($show_icons) && $show_icons): ?><span class="lsd-attr-icon"><?php echo LSD_Kses::element($att->icon()); ?></span><?php endif; ?>
            <?php if (isset($show_attribute_title) && $show_attribute_title): echo esc_html($attribute->name); ?>: <?php endif; ?>
        </span>
        <<?php echo esc_attr($value_tag); ?> class="lsd-attr-value"><?php echo LSD_Kses::element($att->render($post_attributes[$key])); ?></<?php echo esc_attr($value_tag); ?>>
    </div>
    <?php if ($i == 1): ?></div><?php endif; ?>
    <?php $i++; if ($i == 2) $i = 0; ?>
<?php endforeach; ?>
<?php if ($i != 0) echo '</div>';
