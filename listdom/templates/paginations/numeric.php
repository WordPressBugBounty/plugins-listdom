<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins $this */

$total_pages = ceil($this->found_listings / $this->limit);

// No Pagination Needed
if ($total_pages <= 1) return '';

$pagination_links = paginate_links([
    'base'      => esc_url(get_pagenum_link()) . '%_%',
    'format'    => '?paged=%#%',
    'current'   => $this->page,
    'total'     => $total_pages,
    'type'      => 'array',
    'prev_text' => esc_html__('« Previous', 'listdom'),
    'next_text' => esc_html__('Next »', 'listdom'),
]);
?>
<div class="lsd-numeric-pagination-wrapper">
    <div class="lsd-numeric-pagination">
        <div class="pagination lsd-pagination">
            <?php if (is_array($pagination_links) && count($pagination_links)): ?>
                <ul class="page-numbers">
                    <?php foreach ($pagination_links as $link) : ?>
                        <?php $link = preg_replace('/<a (.*?)href=["\']([^"\']+)["\'](.*?)>(\d+)<\/a>/', '<a $1href="$2" data-page="$4"$3>$4</a>', $link); ?>
                        <li><?php echo LSD_Kses::element($link); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
