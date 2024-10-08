<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Skins $this */

// Get Sort Options
$options = isset($this->sorts['options']) && is_array($this->sorts['options']) ? $this->sorts['options'] : [];

// Sort Style
$sort_style = $this->sorts['sort_style'] ?? '';

// Filter Enabled Options
$enableds = [];
foreach($options as $key => $option)
{
    $status = $option['status'] ?? 0;
    if(!$status) continue;

    $enableds[$key] = $option;
}

// No Enabled Option
if(!count($enableds)) return '';
?>
<div class="lsd-view-sortbar-wrapper<?php echo $sort_style ? ' lsd-sort-style-'. esc_attr($sort_style) : ''; ?>">
	<ul class="lsd-sortbar-list">
		<?php foreach($enableds as $key => $option): ?>
		<li data-orderby="<?php echo esc_attr($key); ?>" data-order="<?php echo ($this->orderby == $key ? ($this->order == 'DESC' ? 'ASC' : 'DESC') : (isset($option['sort']) ? esc_attr($option['sort']) : 'DESC')); ?>" class="<?php echo ($this->orderby == $key ? 'lsd-active' : ''); ?>">
			<?php esc_html_e($option['name'], 'listdom'); ?>
			<?php if($this->orderby == $key): ?>
			<i class="lsd-icon fas fa-sort-amount-<?php echo ($this->order == 'DESC' ? 'down' : 'up'); ?>" aria-hidden="true"></i>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ul>
	<div class="lsd-sortbar-dropdown">
		<span>
			<?php esc_html_e('Sort By', 'listdom'); ?>
			<i class="lsd-icon fas fa-caret-right"></i>
		</span>
		<select title="<?php esc_attr_e('Sort By', 'listdom'); ?>">
			<?php foreach($enableds as $key => $option): ?>
			<option value="<?php echo esc_attr($key); ?>" data-order="ASC" <?php echo $this->orderby == $key && $this->order == 'ASC' ? 'selected="selected"' : ''; ?>><?php echo esc_html__($option['name'], 'listdom'); ?> &#8593;</option>
			<option value="<?php echo esc_attr($key); ?>" data-order="DESC" <?php echo $this->orderby == $key && $this->order == 'DESC' ? 'selected="selected"' : ''; ?>><?php echo esc_html__($option['name'], 'listdom'); ?> &#8595;</option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
