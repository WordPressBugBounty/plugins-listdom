<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Schema Class.
 *
 * @class LSD_Schema
 * @version    1.0.0
 */
class LSD_Schema extends LSD_Base
{
    /**
     * @var bool
     */
    public $pro;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    private $schema;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        // Pro
        $this->pro = $this->isPro();

        // Schema
        $this->schema = [];
    }

    public function __toString()
    {
        $string = trim(implode(' ', $this->schema));

        $this->schema = [];
        return $string;
    }

    public function scope(): LSD_Schema
    {
        if ($this->pro) $this->schema[] = 'itemscope';
        return $this;
    }

    /**
     * @param string $type
     * @param WP_Term $category
     * @return $this
     */
    public function type($type = null, $category = null): LSD_Schema
    {
        if ($this->pro)
        {
            if (!$type)
            {
                // Category Type
                $t = isset($category->term_id) ? get_term_meta($category->term_id, 'lsd_schema', true) : '';
                if (!trim($t)) $t = 'https://schema.org/LocalBusiness';

                $type = $t;
            }

            $this->schema[] = 'itemtype="' . esc_attr($type) . '"';
        }

        return $this;
    }

    public function attr($name, $value): LSD_Schema
    {
        if ($this->pro) $this->schema[] = $name . '="' . esc_attr($value) . '"';
        return $this;
    }

    public function meta($name, $value): LSD_Schema
    {
        if ($this->pro) $this->schema[] = '<meta itemprop="' . esc_attr($name) . '" content="' . esc_attr($value) . '">';
        return $this;
    }

    public function prop($name): LSD_Schema
    {
        if ($this->pro) $this->schema[] = 'itemprop="' . esc_attr($name) . '"';
        return $this;
    }

    public function name(): LSD_Schema
    {
        return $this->prop('name');
    }

    public function url(): LSD_Schema
    {
        return $this->prop('url');
    }

    public function address(): LSD_Schema
    {
        return $this->prop('address');
    }

    public function priceRange(): LSD_Schema
    {
        return $this->prop('priceRange');
    }

    public function telephone(): LSD_Schema
    {
        return $this->prop('telephone');
    }

    public function email(): LSD_Schema
    {
        return $this->prop('email');
    }

    public function description(): LSD_Schema
    {
        return $this->prop('description');
    }

    public function jobTitle(): LSD_Schema
    {
        return $this->prop('jobTitle');
    }

    public function faxNumber(): LSD_Schema
    {
        return $this->prop('faxNumber');
    }

    public function openingHours(): LSD_Schema
    {
        return $this->prop('openingHours');
    }

    public function category(): LSD_Schema
    {
        return $this->prop('category');
    }

    public function subjectOf(): LSD_Schema
    {
        return $this->prop('subjectOf');
    }

    public function commentText(): LSD_Schema
    {
        return $this->prop('commentText');
    }

    public function associatedMedia(): LSD_Schema
    {
        return $this->prop('associatedMedia');
    }
}
