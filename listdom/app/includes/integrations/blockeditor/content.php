<?php

/** Inspect rendered block sources without rendering their dynamic callbacks. */
class LSD_Integrations_Blockeditor_Content
{
    public static function has_block(string $content, string $name): bool
    {
        $seen = [];
        return self::blocks_match(parse_blocks($content), function ($block) use ($name): bool
        {
            return ($block['blockName'] ?? '') === $name;
        }, $seen);
    }

    public static function blocks_match(array $blocks, callable $matches, array &$seen): bool
    {
        foreach ($blocks as $block)
        {
            if ($matches($block)) return true;
            if (!empty($block['innerBlocks']) && self::blocks_match($block['innerBlocks'], $matches, $seen)) return true;

            $content = self::reference_content($block, $seen);
            if ($content !== '' && self::blocks_match(parse_blocks($content), $matches, $seen)) return true;
        }

        return false;
    }

    private static function reference_content(array $block, array &$seen): string
    {
        $name = $block['blockName'] ?? '';
        $attributes = $block['attrs'] ?? [];

        if ($name === 'core/template-part' && !empty($attributes['slug']) && function_exists('get_block_templates'))
        {
            $theme = $attributes['theme'] ?? get_stylesheet();
            if ($theme !== get_stylesheet()) return '';

            $id = $theme . '//' . $attributes['slug'];
            if (isset($seen[$id])) return '';
            $seen[$id] = true;

            // Use the published customization or theme file, as the frontend renderer does.
            foreach (get_block_templates(['slug__in' => [$attributes['slug']]], 'wp_template_part') as $part)
            {
                if ($part->id === $id) return $part->content;
            }
        }
        elseif ($name === 'core/block' && !empty($attributes['ref']))
        {
            $id = 'wp_block:' . (int) $attributes['ref'];
            if (isset($seen[$id])) return '';
            $seen[$id] = true;

            $pattern = get_post((int) $attributes['ref']);
            if ($pattern instanceof WP_Post && $pattern->post_type === 'wp_block' && $pattern->post_status === 'publish' && empty($pattern->post_password)) return $pattern->post_content;
        }
        elseif ($name === 'core/pattern' && !empty($attributes['slug']) && class_exists('WP_Block_Patterns_Registry'))
        {
            $id = 'pattern:' . $attributes['slug'];
            if (isset($seen[$id])) return '';
            $seen[$id] = true;

            $pattern = WP_Block_Patterns_Registry::get_instance()->get_registered($attributes['slug']);
            if (isset($pattern['content'])) return $pattern['content'];
        }

        return '';
    }
}
