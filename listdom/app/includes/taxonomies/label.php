<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Label Taxonomy Class.
 *
 * @class LSD_Taxonomies_Label
 * @version    1.0.0
 */
class LSD_Taxonomies_Label extends LSD_Taxonomies
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        add_action('init', [$this, 'register']);

        add_action(LSD_Base::TAX_LABEL . '_add_form_fields', [$this, 'add_form']);
        add_action(LSD_Base::TAX_LABEL . '_edit_form_fields', [$this, 'edit_form']);
        add_action('created_' . LSD_Base::TAX_LABEL, [$this, 'save_metadata']);
        add_action('edited_' . LSD_Base::TAX_LABEL, [$this, 'save_metadata']);
    }

    public function register()
    {
        $args = [
            'label' => esc_html__('Labels', 'listdom'),
            'labels' => [
                'name' => esc_html__('Labels', 'listdom'),
                'singular_name' => esc_html__('Label', 'listdom'),
                'all_items' => esc_html__('All Labels', 'listdom'),
                'edit_item' => esc_html__('Edit Label', 'listdom'),
                'view_item' => esc_html__('View Label', 'listdom'),
                'update_item' => esc_html__('Update Label', 'listdom'),
                'add_new_item' => esc_html__('Add New Label', 'listdom'),
                'new_item_name' => esc_html__('New Label Name', 'listdom'),
                'popular_items' => esc_html__('Popular Labels', 'listdom'),
                'search_items' => esc_html__('Search Labels', 'listdom'),
                'separate_items_with_commas' => esc_html__('Separate labels with commas', 'listdom'),
                'add_or_remove_items' => esc_html__('Add or remove labels', 'listdom'),
                'choose_from_most_used' => esc_html__('Choose from the most used labels', 'listdom'),
                'not_found' => esc_html__('No labels found.', 'listdom'),
                'back_to_items' => esc_html__('← Back to Labels', 'listdom'),
                'parent_item' => esc_html__('Parent Label', 'listdom'),
                'parent_item_colon' => esc_html__('Parent Label:', 'listdom'),
                'no_terms' => esc_html__('No Labels', 'listdom'),
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => false,
            'hierarchical' => false,
            'has_archive' => true,
            'rewrite' => ['slug' => LSD_Options::label_slug()],
        ];

        register_taxonomy(
            LSD_Base::TAX_LABEL,
            LSD_Base::PTYPE_LISTING,
            apply_filters('lsd_taxonomy_label_args', $args)
        );

        register_taxonomy_for_object_type(LSD_Base::TAX_LABEL, LSD_Base::PTYPE_LISTING);
    }

    public function add_form()
    {
        ?>
        <div class="form-field">
            <label for="lsd_color"><?php esc_html_e('Color', 'listdom'); ?></label>
            <?php echo LSD_Form::colorpicker([
                'name' => 'lsd_color',
                'id' => 'lsd_color',
                'default' => '#1d7ed3',
                'value' => '#1d7ed3',
            ]); ?>
        </div>
        <?php
    }

    public function edit_form($term)
    {
        $color = get_term_meta($term->term_id, 'lsd_color', true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="lsd_color"><?php esc_html_e('Color', 'listdom'); ?></label>
            </th>
            <td>
                <?php echo LSD_Form::colorpicker([
                    'name' => 'lsd_color',
                    'id' => 'lsd_color',
                    'default' => '#1d7ed3',
                    'value' => $color,
                ]); ?>
            </td>
        </tr>
        <?php
    }

    public function save_metadata($term_id)
    {
        // It's quick edit
        if (!isset($_POST['lsd_color'])) return;

        $color = sanitize_text_field($_POST['lsd_color']);
        update_term_meta($term_id, 'lsd_color', $color);
    }

    public function get_terms()
    {
        return get_terms([
            'taxonomy' => LSD_Base::TAX_LABEL,
            'hide_empty' => false,
        ]);
    }
}
