<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins_Timeline $this */

$ids = $this->listings;
?>
<?php $index = 0; foreach ($ids as $id): $listing = new LSD_Entity_Listing($id); $index++; ?>
    <?php
    $timeline_classes = ['lsd-timeline', 'lsd-timeline-style1'];
    if ($this->horizontal)
    {
        switch ($this->horizontal_alignment)
        {
            case 'top':
                $timeline_classes[] = 'lsd-timeline-top';
                break;
            case 'bottom':
                $timeline_classes[] = 'lsd-timeline-bottom';
                break;
            default:
                $timeline_classes[] = $index % 2 === 0 ? 'lsd-timeline-bottom' : 'lsd-timeline-top';
                break;
        }
    }
    else
    {
        switch ($this->vertical_alignment)
        {
            case 'left':
                $timeline_classes[] = 'lsd-timeline-left';
                break;
            case 'right':
                $timeline_classes[] = 'lsd-timeline-right';
                break;
            default:
                $timeline_classes[] = $index % 2 === 0 ? 'lsd-timeline-right' : 'lsd-timeline-left';
                break;
        }
    }
    ?>
    <div class="lsd-timeline-item">
        <?php if ($this->horizontal): ?>
            <div class="lsd-center-line"></div>
            <div class="lsd-circle"></div>
        <?php endif; ?>
        <div class="<?php echo esc_attr(implode(' ', array_map('sanitize_html_class', $timeline_classes))); ?>">
            <div class="lsd-listing" <?php echo lsd_schema()->scope()->type(null, $listing->get_data_category()); ?>>
                <?php echo (new LSD_Builders())->listing($listing)->build($this->style); ?>
            </div>
        </div>
    </div>
<?php endforeach;
