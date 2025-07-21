<?php
// no direct access
defined('ABSPATH') || die();

/** @var int $post_id */
/** @var int $list_style */

$terms = wp_get_post_terms($post_id, LSD_Base::TAX_FEATURE);
if (!count($terms)) return '';
?>
<ul class="lsd-features-style-<?php echo esc_attr($list_style); ?>">
    <?php foreach ($terms as $term): ?>
        <?php
            $icon = LSD_Taxonomies::icon($term->term_id);
            $itemprop = get_term_meta($term->term_id, 'lsd_itemprop', true);
            $link = $this->enable_link ? '<a href="' . esc_url(get_term_link($term->term_id)) . '">' . esc_html($term->name) . '</a>' : '<span class="lsd-single-term">'.esc_html($term->name).'</span>';
        ?>
        <li <?php echo $itemprop ? lsd_schema()->prop($itemprop) : ''; ?>><?php echo $this->show_icons && trim($icon) ? $icon . ' ' : ''; ?><?php echo $link; ?></li>
    <?php endforeach; ?>
</ul>
