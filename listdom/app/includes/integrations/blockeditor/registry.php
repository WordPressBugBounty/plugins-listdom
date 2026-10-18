<?php

/**
 * Canonical definitions for native Listdom blocks.
 *
 * Add-ons may append definitions through lsd_block_editor_blocks. Each definition
 * needs a stable namespace/name, title and callable render_callback (or a class
 * with a render method). Optional keys are description, icon, category, attributes,
 * defaults, supports, requires, editor_style, editor and editor_dependencies. Use
 * listdom or listdom-single-listing for category. Render callbacks
 * can enqueue the frontend assets used by that block when it is rendered. Scripts
 * listed in editor_dependencies may customize edit through the
 * lsd.blockEditor.edit JavaScript filter before blockeditor.js registers blocks.
 */
class LSD_Integrations_Blockeditor_Registry
{
    private $blocks;

    public function blocks(): array
    {
        if ($this->blocks !== null) return $this->blocks;

        $this->blocks = [];
        $definitions = apply_filters('lsd_block_editor_blocks', []);
        if (!is_array($definitions)) return $this->blocks;

        foreach ($definitions as $definition)
        {
            if (!is_array($definition) || empty($definition['name']) || empty($definition['title']) || !is_string($definition['title'])) continue;

            $name = $definition['name'];
            if (!is_string($name) || !preg_match('~^[a-z][a-z0-9-]*/[a-z][a-z0-9-]*$~', $name)) continue;
            if ($name === 'listdom/shortcodes' || preg_match('~^listdom/shortcodes-[0-9]+$~', $name)) continue;
            if (!$this->available($definition['requires'] ?? null)) continue;

            $renderer = $this->renderer($definition['render_callback'] ?? null);
            if (!$renderer) continue;

            $definition['render_callback'] = $renderer;
            $this->blocks[$name] = $definition;
        }

        return $this->blocks;
    }

    public function register(): void
    {
        foreach ($this->blocks() as $name => $definition)
        {
            $api_version = $this->api_version();
            $args = [
                'title' => $definition['title'],
                'description' => $definition['description'] ?? '',
                'icon' => $definition['icon'] ?? 'list-view',
                'category' => $definition['category'] ?? 'listdom',
                'attributes' => $this->attributes($definition),
                'supports' => is_array($definition['supports'] ?? null) ? $definition['supports'] : [],
                'render_callback' => $definition['render_callback'],
                'editor_script' => 'lsd-blockeditor',
            ];
            if (!empty($definition['editor_style'])) $args['editor_style'] = $definition['editor_style'];

            // WordPress 5.0 through 5.5 does not have a server-side api_version property.
            if ($api_version > 1) $args['api_version'] = $api_version;

            register_block_type($name, $args);
        }
    }

    public function editor_blocks(): array
    {
        $blocks = [];
        foreach ($this->blocks() as $name => $definition)
        {
            $blocks[] = [
                'name' => $name,
                'apiVersion' => $this->api_version(),
                'title' => $definition['title'],
                'description' => $definition['description'] ?? '',
                'icon' => $definition['icon'] ?? 'list-view',
                'category' => $definition['category'] ?? 'listdom',
                'attributes' => $this->attributes($definition),
                'supports' => is_array($definition['supports'] ?? null) ? $definition['supports'] : [],
                'editor' => is_array($definition['editor'] ?? null) ? $definition['editor'] : [],
            ];
        }

        return $blocks;
    }

    public function editor_dependencies(): array
    {
        $handles = [];
        foreach ($this->blocks() as $definition)
        {
            foreach ((is_array($definition['editor_dependencies'] ?? null) ? $definition['editor_dependencies'] : []) as $handle)
            {
                if (is_string($handle) && wp_script_is($handle, 'registered')) $handles[] = $handle;
            }
        }

        return array_unique($handles);
    }

    private function attributes(array $definition): array
    {
        $attributes = is_array($definition['attributes'] ?? null) ? $definition['attributes'] : [];
        foreach ((is_array($definition['defaults'] ?? null) ? $definition['defaults'] : []) as $attribute => $value)
        {
            if (isset($attributes[$attribute]) && is_array($attributes[$attribute]) && !array_key_exists('default', $attributes[$attribute])) $attributes[$attribute]['default'] = $value;
        }

        return $attributes;
    }

    private function api_version(): int
    {
        $version = get_bloginfo('version');
        if (version_compare($version, '6.3', '>=')) return 3;
        if (version_compare($version, '5.6', '>=')) return 2;

        return 1;
    }

    private function available($requirement): bool
    {
        if (!$requirement) return true;
        if (is_callable($requirement)) return (bool) call_user_func($requirement);
        if (is_string($requirement)) return class_exists($requirement);
        if (!is_array($requirement)) return false;

        if (isset($requirement['function'])) return function_exists($requirement['function']);
        if (!isset($requirement['class']) || !class_exists($requirement['class'])) return false;

        return !isset($requirement['method']) || method_exists($requirement['class'], $requirement['method']);
    }

    private function renderer($callback)
    {
        if (is_callable($callback)) return $callback;
        if (!is_string($callback) || !class_exists($callback) || !method_exists($callback, 'render')) return null;

        $renderer = new $callback();
        return is_callable([$renderer, 'render']) ? [$renderer, 'render'] : null;
    }
}
