<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Element_Mapcard $this */
/** @var int $post_id */

$availability = $this->listing->get_availability(true);

$phone = $this->listing->get_meta('lsd_phone');
$website = $this->listing->get_meta('lsd_website');
?>
<div class="lsd-map-card-wrapper lsd-map-card-style1 lsd-font-m lsd-tablet-hidden">
	<div class="lsd-map-card-body">
		<h4 class="lsd-listing-title">
			<a data-listing-id="<?php echo esc_attr($post_id); ?>" href="<?php echo esc_url(get_permalink($post_id)); ?>" aria-label="<?php echo esc_attr($this->listing->get_title()); ?>"><?php echo LSD_Kses::element($this->listing->get_title()); ?></a>
		</h4>
		<div class="lsd-listing-address">
			<?php echo LSD_Kses::element($this->listing->get_address(false)); ?>
		</div>

		<?php if (trim($availability)): ?>
		<div class="lsd-listing-availability">
			<?php echo LSD_Kses::element($availability); ?>
		</div>
		<?php endif; ?>

		<?php if (trim($phone) || trim($website)): ?>
			<div class="lsd-listing-phone-website">
				<?php if ($phone): ?>
					<a href="tel:<?php echo esc_attr($phone); ?>"><?php echo esc_html($phone); ?></a>
				<?php elseif ($website): ?>
					<a href="<?php echo esc_attr($website); ?>" target="_blank"><?php echo esc_html(LSD_Base::remove_protocols($website)); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php
			// Map Card Bottom
			do_action('lsd_map_card_bottom', $this->listing);
		?>
	</div>
	<div class="lsd-listing-image">
		<?php echo LSD_Kses::element($this->listing->get_cover_image([80, 80])); ?>
	</div>
</div>
