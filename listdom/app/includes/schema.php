<?php

class LSD_Schema extends LSD_Base
{
    public bool $pro;

    private array $schema;

    public function __construct()
    {
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

    public function type($type = null, $category = null): LSD_Schema
    {
        if ($this->pro)
        {
            if (!$type)
            {
                // Category Type
                $t = $category && is_object($category) && isset($category->term_id)
                    ? get_term_meta($category->term_id, 'lsd_schema', true)
                    : '';
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

    public function reset(): LSD_Schema
    {
        $this->schema = [];
        return $this;
    }

    public function meta($name, $value): LSD_Schema
    {
        if ($this->pro)
        {
            $name = $this->normalize($name);
            if (trim($name)) $this->schema[] = '<meta itemprop="' . esc_attr($name) . '" content="' . esc_attr($value) . '">';
        }

        return $this;
    }

    public function prop($name): LSD_Schema
    {
        if ($this->pro)
        {
            $name = $this->normalize($name);
            if (trim($name)) $this->schema[] = 'itemprop="' . esc_attr($name) . '"';
        }

        return $this;
    }

    public function normalize($name): string
    {
        $name = trim((string) $name);
        if (!trim($name)) return '';

        $name = preg_replace('/^https?:\/\/schema\.org\//i', '', $name);
        return trim($name, " \t\n\r\0\x0B/#");
    }

    public function name(): LSD_Schema
    {
        return $this->prop('name');
    }

    public function url(): LSD_Schema
    {
        return $this->prop('url');
    }

    public function breadcrumb(): LSD_Schema
    {
        return $this->prop('breadcrumb');
    }

    public function address(): LSD_Schema
    {
        if ($this->pro) $this->schema[] = 'itemprop="address" itemscope itemtype="https://schema.org/PostalAddress"';
        return $this;
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
