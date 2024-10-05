<?php
// no direct access
defined('ABSPATH') || die();

/** @var int $post_id */

$networks = LSD_Options::socials();
$SN = new LSD_Socials();
?>
<?php if($this->layout == 'archive'): ?>
<div class="lsd-share lsd-share-archive">
    <div class="lsd-share-icon">
		<ul class="lsd-share-list lsd-color-m-brd">
			<li class="lsd-main-icon lsd-color-m-txt"><i class="lsd-icon fa fa-share-alt fa-lg"></i></li>
			<?php
                foreach($networks as $network=>$values)
                {
                    $obj = $SN->get($network, $values);

                    // Social Network is not Enabled
                    if(!$obj || !$obj->option('archive_share')) continue;

                    $share = $obj->share($post_id);
                    if(!trim($share)) continue;

                    echo '<li class="lsd-share-list-item">'.$share.'</li>';
                }
			?>
		</ul>
    </div>
</div>
<?php else: ?>
<div class="lsd-share lsd-share-single">
    <div class="lsd-share-networks">
        <ul class="lsd-share-list">
            <?php
                foreach($networks as $network=>$values)
                {
                    $obj = $SN->get($network, $values);

                    // Social Network is not Enabled
                    if(!$obj || !$obj->option('single_share')) continue;

                    $share = $obj->share($post_id);
                    if(!trim($share)) continue;

                    echo '<li class="lsd-share-list-item">'.$share.'</li>';
                }
            ?>
        </ul>
    </div>
</div>
<?php endif;