<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Taxonomy Walker Class.
 *
 * @class LSD_Walker_Taxonomy
 * @version    1.0.0
 */
class LSD_Walker_Taxonomy extends Walker_Category_Checklist
{
    /**
     * Constructor method
     */
    public function __construct()
    {
    }

    public function start_el(&$output, $category, $depth = 0, $args = [], $id = 0)
    {
        if (empty($args['taxonomy'])) $taxonomy = 'category';
        else $taxonomy = $args['taxonomy'];

        if ($taxonomy == 'category') $name = 'post_category';
        else $name = 'lsd[filter][' . $taxonomy . ']';

        $args['popular_cats'] = empty($args['popular_cats']) ? [] : $args['popular_cats'];
        $class = in_array($category->term_id, $args['popular_cats']) ? ' class="popular-category"' : '';

        $args['selected_cats'] = empty($args['selected_cats']) ? [] : $args['selected_cats'];

        if (!empty($args['list_only']))
        {
            $aria_cheched = 'false';
            $inner_class = 'category';

            if (in_array($category->term_id, $args['selected_cats']))
            {
                $inner_class .= ' selected';
                $aria_cheched = 'true';
            }

            $output .= "\n" . '<li' . $class . '>' .
                '<div class="' . esc_attr($inner_class) . '" data-term-id=' . esc_attr($category->term_id) .
                ' tabindex="0" role="checkbox" aria-checked="' . esc_attr($aria_cheched) . '">' .
                esc_html(apply_filters('the_category', $category->name)) . '</div>';
        }
        else
        {
            $output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" .
                '<label class="selectit"><input value="' . esc_attr($category->term_id) . '" type="checkbox" name="' . esc_attr($name) . '[]" id="in-' . esc_attr($taxonomy . '-' . $category->term_id) . '"' .
                checked(in_array($category->term_id, $args['selected_cats']), true, false) .
                disabled(empty($args['disabled']), false, false) . ' /> ' .
                esc_html(apply_filters('the_category', $category->name)) . '</label>';
        }
    }
}
