<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Element_Availability $this */
/** @var int $post_id */

$availability = get_post_meta($post_id, 'lsd_ava', true);
if(!is_array($availability) || !count($availability)) return '';

$valid = false;
foreach($availability as $a)
{
    $valid = (isset($a['hours']) && $a['hours']) || (isset($a['off']) && $a['off']);
    if($valid) break;
}

// Not valid for Weekly
if(!$this->oneday && !$valid) return '';

$day = $this->day ?: current_time('N');
$today = $availability[$day] ?? [];
$isoffday = isset($today['off']) && $today['off'];

// Not Valid for One Day
if($this->oneday && !$isoffday && !trim($today['hours'])) return '';
?>
<?php 
	/** One Day **/ if($this->oneday):
?>
<div class="lsd-ava-one-day<?php if($isoffday) echo " lsd-ava-one-day-off" ?>">
    <?php if($isoffday || (isset($today['hours']) && trim($today['hours']))): ?>
    <i class="lsd-icon far fa-calendar-alt" aria-hidden="true"></i>
    <span class="lsd-ava-hour"><?php echo $isoffday ? esc_html__('Off', 'listdom') : LSD_Kses::element($today['hours']); ?></span>
    <?php endif; ?>
</div>
<?php /** Weekly **/ else: ?>
<div class="lsd-ava-week">
    <?php foreach(LSD_Main::get_weekdays() as $weekday): $daycode = $weekday['code']; ?>
    <div class="lsd-ava-weekday<?php if(isset($availability[$daycode]['off']) && $availability[$daycode]['off']) echo ' lsd-ava-offday'; ?>">
		<div class="lsd-ava-weekday-wrapper">
			<div class="lsd-row">
				<div class="lsd-col-4 lsd-ava-weekday-column">
					<?php echo esc_html($weekday['label']); ?>
				</div>
				<div class="lsd-col-8">
					<div class="lsd-ava-hours-column">
						<?php if(isset($availability[$daycode]['off']) && $availability[$daycode]['off']): ?>
							<?php esc_html_e('Off', 'listdom'); ?>
						<?php elseif(isset($availability[$daycode]['hours'])): ?>
							<span <?php echo lsd_schema()->openingHours(); ?>><?php echo LSD_Kses::element($availability[$daycode]['hours']); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif;
