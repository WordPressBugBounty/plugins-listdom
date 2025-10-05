<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Search $this */

$action = isset($this->form['page']) ? get_page_link($this->form['page']) : home_url();
if (is_tax() && isset($this->form['page']) && !trim($this->form['page'])) $action = get_term_link(get_queried_object()->term_id);

$shortcode = isset($this->form['shortcode']) && trim($this->form['shortcode']) ? $this->form['shortcode'] : '';
$criteria = (bool) ($this->form['criteria'] ?? 0);

$style = isset($this->form['style']) && trim($this->form['style']) ? trim(strtolower($this->form['style'])) : 'default';
if (isset($this->atts['style']) && trim($this->atts['style'])) $style = $this->atts['style'];

// No Fields!
if (!count($this->desktop) && !count($this->tablet) && !count($this->mobile)) return '';

// Add JS codes to footer
$assets = new LSD_Assets();
$assets->footer('<script>
jQuery(document).ready(function()
{
    jQuery("#lsd_search_'.$this->id.'").listdomSearchForm(
    {
        id: "'.$this->id.'",
        shortcode: "'.$shortcode.'",
        ajax: '.$this->ajax.',
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        nonce: "'.wp_create_nonce('lsd_search_form').'",
        sf: '.wp_json_encode($this->sf).',
        connected_shortcodes: '.wp_json_encode($this->connected_shortcodes, JSON_NUMERIC_CHECK).',
        select2: {
            noResults: "'.esc_js(esc_html__('No results found.', 'listdom')).'"
        }
    });
});
</script>');
?>
<div class="lsd-search lsd-search-style-<?php echo esc_attr($style); ?> lsd-search-default-style lsd-search-<?php echo esc_attr($this->id); ?>" id="lsd_search_<?php echo esc_attr($this->id); ?>">

    <div class="lsd-search-devices-wrapper">
        <?php if (count($this->desktop)) $this->device($action, $criteria); ?>
        <?php if (count($this->tablet)) $this->device($action, $criteria, 'tablet'); ?>
        <?php if (count($this->mobile)) $this->device($action, $criteria, 'mobile'); ?>
    </div>

</div>
