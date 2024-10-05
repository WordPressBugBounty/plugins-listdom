<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Element_Infowindow $this */
/** @var int $post_id */

$availability = $this->listing->get_availability(true);
?>
<div class="lsd-infowindow-wrapper lsd-infowindow-style1">
    <div class="lsd-infowindow-container lsd-font-m lsd-color-m-brd <?php echo ($this->listing->is_shape() ? 'lsd-shape' : ''); ?>">
        
		<div class="lsd-listing-image">
            <?php echo LSD_Kses::element($this->listing->get_cover_image([300, 160])); ?>
        </div>
        
        <div class="lsd-infowindow-body">
			<h4 class="lsd-listing-title">
				<a data-listing-id="<?php echo esc_attr($post_id); ?>" href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo LSD_Kses::element($this->listing->get_title()); ?></a>
			</h4>
			<div class="lsd-listing-address">
				<?php echo LSD_Kses::element($this->listing->get_address(false)); ?>
			</div>
			
			<?php if(trim($availability)): ?>
			<div class="lsd-listing-availability">
				<?php echo LSD_Kses::element($availability); ?>
			</div>
			<?php endif; ?>

			<?php
				// Infowindow Bottom
				do_action('lsd_infowindow_bottom', $this->listing);
			?>
		</div>
    </div>
	
</div>