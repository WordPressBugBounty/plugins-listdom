<?php
// no direct access
defined('ABSPATH') || die();

/** @var int $post_id */

$networks = LSD_Options::socials();
$SN = new LSD_Socials();
?>
<?php if ($this->layout == 'archive'): ?>
    <div class="lsd-share lsd-share-archive">
        <div class="lsd-share-icon">
            <span class="lsd-main-icon lsd-color-m-txt lsd-share-modal-button" data-id="<?php echo esc_attr($post_id); ?>">
                <i class="lsd-icon fa fa-share-alt fa-lg"></i>
            </span>
        </div>

        <div id="lsd-share-modal-<?php echo esc_attr($post_id); ?>" class="lsd-modal">
            <div class="lsd-modal-content lsd-share-modal">
                <h3 class="lsd-modal-title lsd-m-0 lsd-fe-title">
                    <?php echo esc_html__('Share', 'listdom') . ' ' . esc_html(get_the_title($post_id)); ?>
                </h3>
                <ul class="lsd-share-list lsd-share-modal-list lsd-color-m-brd">
                    <?php
                    foreach ($networks as $network=>$values)
                    {
                        $obj = $SN->get($network, $values);

                        // Social Network is not Enabled
                        if (!$obj || !$obj->option('archive_share')) continue;

                        $share = $obj->share($post_id);
                        if (!trim($share)) continue;

                        echo '<li class="lsd-share-list-item">'.$share.'</li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="lsd-share lsd-share-single">
        <div class="lsd-share-networks">
            <ul class="lsd-share-list">
                <?php
                foreach ($networks as $network=>$values)
                {
                    $obj = $SN->get($network, $values);

                    // Social Network is not Enabled
                    if (!$obj || !$obj->option('single_share')) continue;

                    $share = $obj->share($post_id);
                    if (!trim($share)) continue;

                    echo '<li class="lsd-share-list-item">'.$share.'</li>';
                }
                ?>
            </ul>
        </div>
    </div>
<?php endif;
