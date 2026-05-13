<?php
// no direct access
defined('ABSPATH') || die();

/** @var array $cta */
/** @var array $args */
/** @var LSD_Entity_Listing $listing */

$button_class = trim($args['button_class'] ?? 'lsd-light-button lsd-cta-button');
$context = $args['context'] ?? 'archive';
$listing_id = $listing->id();
$lightbox_style = $args['lightbox_style'] ?? '';
$popup_width = $args['popup_width'] ?? null;

$attributes = [];
$href = '#';
$rel = '';
$target_attr = '';

if ($button_class !== '') $attributes[] = 'class="' . esc_attr($button_class) . '"';

switch ($cta['target'])
{
    case 'custom':
        $href = esc_url($cta['url']);
        $target_attr = 'target="_blank"';
        $rel = 'rel="noopener noreferrer"';
        break;

    case 'lightbox':
        $href = esc_url($cta['permalink']);
        $attributes[] = 'data-listdom-lightbox';
        $attributes[] = 'data-listing-id="' . esc_attr($listing_id) . '"';
        if ($lightbox_style) $attributes[] = 'data-listdom-style="' . esc_attr($lightbox_style) . '"';
        break;

    case 'popup':
        $href = '#';
        $popup_id = uniqid('lsd-cta-popup-' . $listing_id . '-');
        $attributes[] = 'data-listdom-cta="popup"';
        $attributes[] = 'data-listdom-cta-target="' . esc_attr($popup_id) . '"';
        break;

    case 'details':
    default:
        $href = esc_url($cta['permalink']);
        break;
}

if ($target_attr) $attributes[] = $target_attr;
if ($rel) $attributes[] = $rel;
?>
<a href="<?php echo $href; ?>" <?php echo implode(' ', $attributes); ?>><?php echo esc_html($cta['text']); ?></a>
<?php if ($cta['target'] === 'popup' && !empty($popup_id)): $popup_content = LSD_Kses::rich(do_shortcode($cta['content'])); ?>
    <?php if (trim($popup_content)): ?>
    <div class="lsd-popup-wrapper lsd-modal lsd-cta-modal" id="<?php echo esc_attr($popup_id); ?>" role="dialog" aria-modal="true">
        <div class="lsd-modal-content"<?php echo $popup_width ? ' style="max-width:' . esc_attr($popup_width) . ';"' : ''; ?>>
            <a href="#" class="lsd-modal-close" aria-label="<?php esc_attr_e('Close popup', 'listdom'); ?>">
                <i class="lsd-fe-icon fa fa-times"></i>
            </a>
            <div class="lsd-modal-body">
                <?php echo $popup_content; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endif;
