<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_Shortcodes_Search $this */

$action = isset($this->form['page']) ? get_page_link($this->form['page']) : home_url();
$shortcode = isset($this->form['shortcode']) && trim($this->form['shortcode']) ? $this->form['shortcode'] : '';
$criteria = $this->form['criteria'] ?? 0;

$style = isset($this->form['style']) && trim($this->form['style']) ? trim(strtolower($this->form['style'])) : 'default';
if(isset($this->atts['style']) && trim($this->atts['style'])) $style = $this->atts['style'];

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
        sf: '.json_encode($this->sf).'
    });
});
</script>');
?>
<div class="lsd-search lsd-search-style-<?php echo esc_attr($style); ?> lsd-search-default-style" id="lsd_search_<?php echo esc_attr($this->id); ?>">

    <?php if(is_array($this->filters) && count($this->filters)): ?>
    <form action="<?php echo esc_url($action); ?>" class="lsd-search-form">
        <?php
            $HTML = '';
            foreach($this->filters as $row) $HTML .= $this->row($row);

            // Display Criteria
            if($criteria) $HTML .= $this->criteria();

            // Print the Search Form
            echo apply_filters('lsd_search_form_html', $HTML);
        ?>
    </form>
    <?php else: ?>
    <?php echo current_user_can('administrator') ? $this->alert(sprintf(esc_html__("No filter specified for this search form. You can add some using %s.", 'listdom'), '<a href="'.get_edit_post_link($this->id).'">'.esc_html__('search builder', 'listdom').'</a>')) : ''; ?>
    <?php endif; ?>

</div>
