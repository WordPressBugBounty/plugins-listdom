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

    public function key(string $name): int
    {
        $collections = $this->all();

        foreach ($collections as $key => $collection)
        {
            if (isset($collection['name']) && $collection['name'] === $name) return (int) $key;
        }

        return 0;
    }

    public function exists(string $name): bool
    {
        return (bool) $this->key($name);
    }

    public function upsert($item = []): int
    {
        // Collection Name
        $name = $item['name'] ?? '';

        // Update
        if ($key = $this->key($name))
        {
            $this->update($key, $item);
            return $key;
        }

        // Insert
        return $this->add($item);
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
