<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Kses Class.
 *
 * @class LSD_Kses
 * @version    1.0.0
 */
class LSD_Kses extends LSD_Base
{
    static $allowed_html_form = null;
    static $allowed_html_element = null;
    static $allowed_html_rich = null;
    static $allowed_html_embed = null;
    static $allowed_html_full = null;
    static $allowed_html_page = null;
    static $allowed_attrs = [
        'data-*' => 1,
        'aria-*' => 1,
        'type' => 1,
        'value' => 1,
        'class' => 1,
        'id' => 1,
        'for' => 1,
        'style' => 1,
        'src' => 1,
        'alt' => 1,
        'title' => 1,
        'placeholder' => 1,
        'href' => 1,
        'rel' => 1,
        'target' => 1,
        'novalidate' => 1,
        'name' => 1,
        'tabindex' => 1,
        'action' => 1,
        'method' => 1,
        'width' => 1,
        'height' => 1,
        'selected' => 1,
        'checked' => 1,
        'readonly' => 1,
        'disabled' => 1,
        'required' => 1,
        'autocomplete' => 1,
        'min' => 1,
        'max' => 1,
        'step' => 1,
        'cols' => 1,
        'rows' => 1,
        'lang' => 1,
        'dir' => 1,
        'enctype' => 1,
        'multiple' => 1,
        'frameborder' => 1,
        'allow' => 1,
        'allowfullscreen' => 1,
        'label' => 1,
        'align' => 1,
        'accept-charset' => 1,
        'viewBox' => 1,
        'd' => 1,
        'xmlns' => 1,
        'points' => 1,
        'fill' => 1,
        'stroke' => 1,
        'stroke-width' => 1,
        'stroke-linecap' => 1,
        'stroke-linejoin' => 1,
    ];

    static $schema_attr = [
        'itemtype' => 1,
        'itemscope' => 1,
        'itemprop' => 1,
        'content' => 1,
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        add_filter('lsd_kses_tags', [$this, 'tags'], 10, 2);
        add_filter('safe_style_css', [$this, 'styles']);
    }

    public static function page($html): string
    {
        if (is_null(self::$allowed_html_page))
        {
            $allowed = wp_kses_allowed_html('post');
            self::$allowed_html_page = apply_filters('lsd_kses_tags', $allowed, 'page');
        }

        return wp_kses($html, self::$allowed_html_page);
    }

    public static function form($html): string
    {
        if (is_null(self::$allowed_html_form))
        {
            $allowed = wp_kses_allowed_html('post');
            self::$allowed_html_form = apply_filters('lsd_kses_tags', $allowed, 'form');
        }

        return wp_kses($html, self::$allowed_html_form);
    }

    public static function element($html): string
    {
        if (is_null(self::$allowed_html_element))
        {
            $allowed = wp_kses_allowed_html('post');
            self::$allowed_html_element = apply_filters('lsd_kses_tags', $allowed, 'element');
        }

        return wp_kses($html, self::$allowed_html_element);
    }

    /**
     * Element + Embed
     * @param $html
     * @return string
     */
    public static function rich($html): string
    {
        if (is_null(self::$allowed_html_rich))
        {
            $allowed = wp_kses_allowed_html('post');
            self::$allowed_html_rich = apply_filters('lsd_kses_tags', $allowed, 'rich');
        }

        return wp_kses($html, self::$allowed_html_rich);
    }

    /**
     * Only Embed
     * @param $html
     * @return string
     */
    public static function embed($html): string
    {
        if (is_null(self::$allowed_html_embed))
        {
            self::$allowed_html_embed = apply_filters('lsd_kses_tags', [], 'embed');
        }

        return wp_kses($html, self::$allowed_html_embed);
    }

    /**
     * Full Page
     *
     * @param $html
     * @return string
     */
    public static function full($html): string
    {
        if (is_null(self::$allowed_html_full))
        {
            $allowed = wp_kses_allowed_html('post');
            self::$allowed_html_full = apply_filters('lsd_kses_tags', $allowed, 'full');
        }

        return wp_kses($html, self::$allowed_html_full);
    }

    public function styles($styles)
    {
        if (!in_array('display', $styles)) $styles[] = 'display';
        return $styles;
    }

    public static function tags($tags, $context)
    {
        if (in_array($context, ['form', 'page', 'full']))
        {
            $tags['form'] = self::$allowed_attrs;
            $tags['label'] = self::$allowed_attrs;
            $tags['input'] = self::$allowed_attrs;
            $tags['select'] = self::$allowed_attrs;
            $tags['option'] = self::$allowed_attrs;
            $tags['optgroup'] = self::$allowed_attrs;
            $tags['textarea'] = self::$allowed_attrs;
            $tags['button'] = self::$allowed_attrs;
            $tags['fieldset'] = self::$allowed_attrs;
            $tags['output'] = self::$allowed_attrs;
            $tags['svg'] = self::$allowed_attrs;
            $tags['polygon'] = self::$allowed_attrs;
            $tags['path'] = self::$allowed_attrs;
        }

        if (in_array($context, ['embed', 'rich', 'full']))
        {
            if (!isset($tags['iframe'])) $tags['iframe'] = self::$allowed_attrs;
        }

        if ($context === 'full')
        {
            if (!isset($tags['style'])) $tags['style'] = self::$allowed_attrs;
            if (!isset($tags['script'])) $tags['script'] = self::$allowed_attrs;
        }

        // Add Schema Attributes
        if (LSD_Base::isPro()) foreach ($tags as $tag => $attr) $tags[$tag] = array_merge($attr, self::$schema_attr);

        return $tags;
    }
}
