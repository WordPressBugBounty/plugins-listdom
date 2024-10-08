<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Collections.
 *
 * @class LSD_Collections
 * @version    1.0.0
 */
abstract class LSD_Collections
{
    protected $key;

    public function all()
    {
        return get_option($this->key, []);
    }

    public function get($key)
    {
        $collections = $this->all();
        return $collections[$key] ?? [];
    }

    public function add($item = []): int
    {
        // New Key
        $key = time();

        $collections = $this->all();
        $collections = [$key => $item] + $collections;

        $this->save($collections);

        return $key;
    }

    /**
     * @param $collections
     * @return void
     */
    public function save($collections)
    {
        update_option($this->key, $collections, false);
    }

    public function remove($key)
    {
        // Get All
        $collections = $this->all();

        // Remove Requested Item
        if (isset($collections[$key])) unset($collections[$key]);

        // Save New Set
        $this->save($collections);
    }

    public function update($key, $item)
    {
        // Get All
        $collections = $this->all();

        // Update Requested Item
        $collections[$key] = $item;

        // Save New Set
        $this->save($collections);
    }

    /**
     * @param array $args
     * @return false|string
     */
    public function dropdown(array $args)
    {
        $collections = $this->all();

        $options = [];
        foreach ($collections as $key => $item)
        {
            $options[$key] = $item['name'];
        }

        $args['options'] = $options;
        return LSD_Form::select($args);
    }
}
