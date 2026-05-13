<?php

class LSD_IX_Templates_CSV extends LSD_IX_Template
{
    protected $key = 'lsdaddcsv_templates';

    protected $type = 'listings';

    public function __construct($type = 'listings')
    {
        $this->type = LSD_IX::normalize_import_type($type);
    }

    public function type(): string
    {
        return $this->type;
    }

    protected function collections(): array
    {
        $collections = get_option($this->key, []);
        if (!is_array($collections)) $collections = [];

        $legacy = false;
        foreach ($collections as $item)
        {
            if (isset($item['name']))
            {
                $legacy = true;
                break;
            }
        }

        if ($legacy) $collections = ['listings' => $collections];

        return $collections;
    }

    public function all()
    {
        $collections = $this->collections();
        return $collections[$this->type] ?? [];
    }

    public function save($collections)
    {
        $all = $this->collections();
        $all[$this->type] = $collections;

        update_option($this->key, $all, false);
    }

    public function dropdown(array $args = [])
    {
        $args['attributes']['data-lsd-type'] = $this->type;

        return parent::dropdown($args);
    }
}
