<?php
// no direct access
defined('ABSPATH') || die();

/** @var LSD_PTypes_Listing_Single $this */

// Box Method
$box_method = $this->details_page_options['builder']['box_method'] ?? '1';
$head1_elements = $this->details_page_options['builder']['head1']['elements'] ?? [];
$head2_elements = $this->details_page_options['builder']['head2']['elements'] ?? [];
$head3_elements = $this->details_page_options['builder']['head3']['elements'] ?? [];
$col1_elements = $this->details_page_options['builder']['col1']['elements'] ?? [];
$col2_elements = $this->details_page_options['builder']['col2']['elements'] ?? [];
$col3_elements = $this->details_page_options['builder']['col3']['elements'] ?? [];
$foot1_elements = $this->details_page_options['builder']['foot1']['elements'] ?? [];
$foot2_elements = $this->details_page_options['builder']['foot2']['elements'] ?? [];
$foot3_elements = $this->details_page_options['builder']['foot3']['elements'] ?? [];

$head1_width = $this->details_page_options['builder']['head1']['width'] ?? '4-4';
$head2_width = $this->details_page_options['builder']['head2']['width'] ?? '4-4';
$head3_width = $this->details_page_options['builder']['head3']['width'] ?? '4-4';
$col1_width = $this->details_page_options['builder']['col1']['width'] ?? '1-4';
$col2_width = $this->details_page_options['builder']['col2']['width'] ?? '2-4';
$col3_width = $this->details_page_options['builder']['col3']['width'] ?? '1-4';
$foot1_width = $this->details_page_options['builder']['foot1']['width'] ?? '4-4';
$foot2_width = $this->details_page_options['builder']['foot2']['width'] ?? '4-4';
$foot3_width = $this->details_page_options['builder']['foot3']['width'] ?? '4-4';

$head1 = $this->section($head1_elements);
$head2 = $this->section($head2_elements);
$head3 = $this->section($head3_elements);
$col1 = $this->section($col1_elements);
$col2 = $this->section($col2_elements);
$col3 = $this->section($col3_elements);
$foot1 = $this->section($foot1_elements);
$foot2 = $this->section($foot2_elements);
$foot3 = $this->section($foot3_elements);
?>
<div
    class="lsd-dynamic-sections lsd-dynamic-box-method-<?php echo sanitize_html_class($box_method); ?>"
>
    <?php if(trim($head1) || trim($head2) || trim($head3)): ?>
        <div class="lsd-dynamic-section-header">
            <?php if(trim($head1)): ?>
                <div class="lsd-dynamic-col lsd-dynamic-col-<?php echo sanitize_html_class($head1_width); ?>">
                    <?php echo $head1; ?>
                </div>
            <?php endif; ?>
            <?php if(trim($head2)): ?>
                <div class="lsd-dynamic-col lsd-dynamic-col-<?php echo sanitize_html_class($head2_width); ?>">
                    <?php echo $head2; ?>
                </div>
            <?php endif; ?>
            <?php if(trim($head3)): ?>
                <div class="lsd-dynamic-col lsd-dynamic-col-<?php echo sanitize_html_class($head3_width); ?>">
                    <?php echo $head3; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if(trim($col1) || trim($col2) || trim($col3)): ?>
        <div class="lsd-dynamic-section-content">
            <?php if(trim($col1)): ?>
                <div class="lsd-dynamic-col lsd-dynamic-col-<?php echo sanitize_html_class($col1_width); ?>">
                    <?php echo $col1; ?>
                </div>
            <?php endif; ?>
            <?php if(trim($col2)): ?>
                <div class="lsd-dynamic-col lsd-dynamic-col-<?php echo sanitize_html_class($col2_width); ?>">
                    <?php echo $col2; ?>
                </div>
            <?php endif; ?>
            <?php if(trim($col3)): ?>
                <div class="lsd-dynamic-col lsd-dynamic-col-<?php echo sanitize_html_class($col3_width); ?>">
                    <?php echo $col3; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if(trim($foot1) || trim($foot2) || trim($foot3)): ?>
        <div class="lsd-dynamic-section-footer">
            <?php if(trim($foot1)): ?>
                <div class="lsd-dynamic-col lsd-dynamic-col-<?php echo sanitize_html_class($foot1_width); ?>">
                    <?php echo $foot1; ?>
                </div>
            <?php endif; ?>
            <?php if(trim($foot2)): ?>
                <div class="lsd-dynamic-col lsd-dynamic-col-<?php echo sanitize_html_class($foot2_width); ?>">
                    <?php echo $foot2; ?>
                </div>
            <?php endif; ?>
            <?php if(trim($foot3)): ?>
                <div class="lsd-dynamic-col lsd-dynamic-col-<?php echo sanitize_html_class($foot3_width); ?>">
                    <?php echo $foot3; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
