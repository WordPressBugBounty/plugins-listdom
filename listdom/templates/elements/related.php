<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Element_Related $this */
/** @var int $post_id */

$related = get_post_meta($post_id, 'lsd_related_listings', true);
if (!is_array($related)) $related = [];

$latitude = get_post_meta($post_id, 'lsd_latitude', true);
$longitude = get_post_meta($post_id, 'lsd_longitude', true);
$shortcode = $this->args['shortcode'] ?? '';
$taxonomies = $this->args['taxonomy'] ?? [];
$radius = $this->args['radius'] ?? 0;

$terms = $this->get_post_taxonomies_id($post_id);
$terms = $this->filter_terms_by_taxonomies($terms, $taxonomies);

$filter = [];
if (count($related)) $filter = ['include' => $related];
elseif ($terms)
{
    $filter = $terms;

    if ($latitude && $longitude && $radius)
    {
        $filter['circle']['center'] = [$latitude, $longitude];
        $filter['circle']['radius'] = $radius;
    }
}

$filter['exclude'] = [$post_id];
?>
<div class="lsd-related-listings">
    <?php
        $LSD = new LSD_Shortcodes_Listdom();
        echo LSD_Kses::full($LSD->output([
            'id' => $shortcode,
        ], [
            'lsd_filter' => $filter,
        ]));
    ?>
</div>
