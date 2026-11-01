<?php

/** Parse saved references only; never execute a shortcode, block or builder. */
class LSD_Diagnostics_References
{
    private $report;
    private $outcomes;
    private $metadata;
    private $candidates = [];
    private $error;
    private $purpose;
    private $branch = 'resolution';

    public function __construct(LSD_Diagnostics_Report $report, string $purpose = 'views')
    {
        $this->report = $report;
        $this->purpose = $purpose;
        $this->outcomes = new LSD_Diagnostics_Resolution($report);
        $this->metadata = new LSD_Diagnostics_Metadata($report);
    }

    public function resolve(array $input)
    {
        if (!empty($input['view_id']) && !empty($input['page_id']))
            return LSD_Diagnostics_Report::error('invalid_input', __('Select either a saved view or a page.', 'listdom'));
        if (!empty($input['view_id']))
        {
            if (!empty($input['reference'])) return LSD_Diagnostics_Report::error('invalid_input', __('A reference selector requires a page.', 'listdom'));
            $this->candidate((int) $input['view_id'], [], 'view', 0, false, true);
        }
        else if (!empty($input['page_id'])) $this->post((int) $input['page_id'], 0, 'page', [], ['page']);
        else return LSD_Diagnostics_Report::error('invalid_input', __('Select a saved view or a local page.', 'listdom'));
        if ($this->error) return $this->error;
        if (count($this->candidates) > 20)
            $this->report->limit('candidate_limit', __('Only the first 20 saved instances can be selected in this result. Select a narrower page or an explicit saved view to inspect other instances.', 'listdom'));
        $this->report->data['candidates'] = array_map(static function ($candidate)
        {
            return ['selector' => $candidate['selector'], 'view_id' => $candidate['view_id'], 'source' => $candidate['source']];
        }, array_slice($this->candidates, 0, 20));
        $this->report->fact('resolution', 'candidates_found', count($this->candidates));
        $this->report->fact('resolution', 'complete', $this->outcomes->complete());
        if (!empty($input['reference']))
        {
            foreach (array_slice($this->candidates, 0, 20) as $candidate)
                if (hash_equals($candidate['selector'], $input['reference'])) return $candidate;
            return LSD_Diagnostics_Report::error('invalid_reference', __('The selected page reference is no longer available. Discover references again.', 'listdom'));
        }
        if (count($this->candidates) === 1 && $this->outcomes->complete()) return $this->candidates[0];
        $this->report->data['status'] = count($this->candidates) ? 'ambiguous' : ($this->outcomes->complete() ? 'unsupported' : 'partial');
        return null;
    }

    /** Also inspect dashboard/add-listing references using the same permission/budget rules. */
    public function submission(int $id)
    {
        $this->post($id, 0, 'page', [], ['page']);
        if ($this->error) return $this->error;
        return ['complete' => $this->outcomes->complete(), 'found' => count($this->candidates) > 0,
            'usable' => $this->outcomes->existence(count($this->candidates) > 0)];
    }

    private function budget(int $depth, int $bytes = 0, bool $link = false): bool
    {
        if ($this->report->visit($depth, $bytes, $link)) return true;
        $this->incomplete('traversal_limit', __('Saved-reference traversal reached its depth, node, resource or source-size limit.', 'listdom'));
        return false;
    }

    private function incomplete(string $code, string $message): void
    {
        $this->outcomes->record($this->branch, 'unknown', $code, $message);
    }

    private function post(int $id, int $depth, string $path, array $stack, array $types = ['wp_block', 'wp_template', 'wp_template_part', 'elementor_library', 'listdom-template']): void
    {
        if (!$this->budget($depth, 0, true)) return;
        $post = LSD_Diagnostics_Report::resource($id, $types);
        if (is_wp_error($post))
        {
            if ($depth > 0 && $post->get_error_code() === 'listdom_diagnostics_invalid_target')
                $this->outcomes->record($path, 'missing', 'missing_nested_reference', __('A linked saved resource is missing; other references were inspected.', 'listdom'));
            else $this->error = $post;
            return;
        }
        $this->branch = $path . ':' . $id;
        if (in_array('post:' . $id, $stack, true))
        {
            $this->incomplete('reference_cycle', __('A cycle in saved references was not followed.', 'listdom'));
            return;
        }
        $stack[] = 'post:' . $id;
        $this->report->fact('post:' . $id, 'status', (string) $post->post_status);
        $this->report->fact('post:' . $id, 'password_protected', $post->post_password !== '');
        // Reusable blocks are not frontend content unless published and unprotected.
        if ($depth > 0 && !LSD_Integrations_Blockeditor_Layout::reusable_eligible($post))
        {
            $this->outcomes->record($this->branch, 'not_public');
            return;
        }
        $mode = $post->post_type === 'wp_block' ? '' : $this->metadata->value($id, '_elementor_edit_mode', $depth);
        if ($mode === null) { $this->incomplete('traversal_limit', __('The saved builder mode could not be inspected within the source allowance.', 'listdom')); return; }
        $elementor_enabled = LSD_Integrations_Elementor::saved_document_enabled($mode);
        if ($post->post_type === 'elementor_library' && !$elementor_enabled)
        {
            $this->outcomes->record($this->branch, 'inactive');
            return;
        }
        if ($elementor_enabled)
        {
            $elementor = $this->metadata->value($id, '_elementor_data', $depth);
            if ($elementor === null) { $this->incomplete('traversal_limit', __('Saved Elementor data could not be inspected within the remaining source allowance.', 'listdom')); return; }
            if (is_string($elementor)) $elementor = json_decode($elementor, true);
            if (is_array($elementor)) $this->elements($elementor, $depth, $path . ':' . $id . '/elementor', $stack);
            else $this->incomplete('invalid_builder_data', __('Saved Elementor data could not be resolved.', 'listdom'));
        }
        else
        {
            // Other builder metadata can affect this source only when Elementor is not active.
            $unsupported = $post->post_type === 'wp_block' ? false : $this->metadata->unsupported_builder($id);
            if ($unsupported === null) $this->incomplete('metadata_unavailable', __('Saved builder availability could not be inspected.', 'listdom'));
            if ($unsupported) $this->incomplete('unsupported_builder', __('A saved builder is not supported by this inspection.', 'listdom'));
            // Only the active source consumes the source budget; Elementor ignores fallback content.
            $content = (string) $post->post_content;
            if (!$this->budget($depth, strlen($content))) return;
            if ($mode === 'builder') $this->outcomes->record($this->branch . '/elementor', 'inactive');
            if (strpos($content, '[elementor-template') !== false)
                $this->incomplete('embedded_template_shortcode', __('Embedded template shortcodes outside supported saved widget references require manual inspection.', 'listdom'));
            $this->blocks(parse_blocks($content), $depth, $path . ':' . $id, $stack);
        }
    }

    private function blocks(array $blocks, int $depth, string $path, array $stack): void
    {
        foreach ($blocks as $i => $block)
        {
            if (!$this->budget($depth) || $this->error) return;
            $here = $path . '/block:' . $i;
            $this->branch = $here;
            $name = $block['blockName'] ?? '';
            $attrs = $block['attrs'] ?? [];
            if ($this->purpose === 'views' && $name === 'listdom/layout') $this->candidate((int) ($attrs['layoutId'] ?? 0), [], $here, $depth, true);
            elseif ($this->purpose !== 'views' && $name === $this->purpose) $this->candidates[] = ['source' => $here];
            elseif ($name === 'core/block' && !empty($attrs['ref'])) $this->post((int) $attrs['ref'], $depth + 1, $here, $stack, ['wp_block']);
            elseif ($name === 'core/pattern' && !empty($attrs['slug']))
            {
                $slug = (string) $attrs['slug'];
                if (in_array('pattern:' . $slug, $stack, true)) $this->incomplete('reference_cycle', __('A cycle in saved references was not followed.', 'listdom'));
                else
                {
                    $pattern = WP_Block_Patterns_Registry::get_instance()->get_registered($slug);
                    if (!$pattern || !isset($pattern['content'])) $this->outcomes->record($here, 'missing', 'unresolved_pattern', __('A saved pattern reference is missing.', 'listdom'));
                    elseif ($this->budget($depth + 1, strlen($pattern['content']), true)) $this->blocks(parse_blocks($pattern['content']), $depth + 1, $here, array_merge($stack, ['pattern:' . $slug]));
                }
            }
            elseif ($name === 'core/template-part' || $name === 'core/post-content')
                $this->incomplete('theme_context', __('Theme template context is not resolved by saved-page inspection.', 'listdom'));
            // Parse only this block's own HTML; child blocks are separate instances.
            if (is_string($name) && preg_match('~^listdom/shortcodes-[0-9]+$~', $name))
            {
                $id = LSD_Integrations_Blockeditor_Layout::legacy_id((string) ($block['innerHTML'] ?? ''));
                if ($id && $this->purpose === 'views') $this->candidate($id, [], $here, $depth, true);
            }
            elseif ($name !== 'listdom/layout') $this->shortcodes((string) ($block['innerHTML'] ?? ''), $here, $depth);
            if (!empty($block['innerBlocks'])) $this->blocks($block['innerBlocks'], $depth + 1, $here, $stack);
        }
    }

    private function shortcodes(string $content, string $path, int $depth): void
    {
        $tag = $this->purpose === 'views' ? 'listdom' : ($this->purpose === 'listdom/dashboard' ? 'listdom-dashboard' : 'listdom-add-listing');
        $matches = LSD_Checklist_Helper::saved_shortcode_matches($content, $tag);
        foreach ($matches as $i => $match)
        {
            if (!$this->budget($depth)) return;
            $this->branch = $path . '/shortcode:' . $i;
            $atts = shortcode_parse_atts($match[3]);
            if ($this->purpose !== 'views') $this->candidates[] = ['source' => $path];
            elseif (is_array($atts)) $this->candidate((int) ($atts['id'] ?? 0), $atts, $path . '/shortcode:' . $i, $depth);
        }
    }

    private function elements(array $elements, int $depth, string $path, array $stack): void
    {
        foreach ($elements as $i => $element)
        {
            if (!$this->budget($depth) || $this->error) return;
            if (!is_array($element)) continue;
            $here = $path . '/node:' . $i;
            $settings = $element['settings'] ?? [];
            $type = $element['widgetType'] ?? '';
            $this->branch = $here;
            $dedicated = in_array($type, ['lsd-listing-shortcode', 'lsd-frontend-dashboard'], true);
            $provider = LSD_Diagnostics_Report::callback('elementor/widgets/register', 'LSDPACELM\\Elementor', 'widgets');
            if ($dedicated)
            {
                if (!$provider) $this->outcomes->record($here, 'inactive');
                else
                {
                    $reference = \LSDPACELM\Elementor::saved_reference($type, $settings);
                    if ($reference['state'] === 'unknown') $this->incomplete('dynamic_reference', __('A saved widget reference is dynamic and requires rendered inspection.', 'listdom'));
                    elseif ($reference['state'] === 'empty') $this->outcomes->record($here, 'empty');
                    elseif ($this->purpose === 'views' && $reference['kind'] === 'view') $this->candidate($reference['id'], [], $here, $depth);
                    elseif ($this->purpose === 'listdom/dashboard' && $reference['kind'] === 'dashboard') $this->candidates[] = ['source' => $here];
                }
            }
            elseif ($type === 'shortcode' || $type === 'template')
            {
                if (!LSD_Integrations_Elementor::saved_widget_available($type))
                {
                    $this->outcomes->record($here, 'inactive');
                    continue;
                }
                $field = $type === 'template' ? 'template_id' : 'shortcode';
                if (!empty($settings['__dynamic__'][$field]))
                    $this->incomplete('dynamic_reference', __('An Elementor shortcode or template reference is dynamic and requires rendered inspection.', 'listdom'));
                elseif ($type === 'template' && !empty($settings['template_id']))
                    $this->post((int) $settings['template_id'], $depth + 1, $here, $stack, ['elementor_library']);
                elseif ($type === 'shortcode' && is_string($settings['shortcode'] ?? null)) $this->shortcodes($settings['shortcode'], $here, $depth);
                else $this->outcomes->record($here, 'empty');
            }
            if (!empty($element['elements']) && is_array($element['elements'])) $this->elements($element['elements'], $depth + 1, $here, $stack);
        }
    }

    private function candidate(int $id, array $atts, string $source, int $depth, bool $require_published = false, bool $explicit = false): void
    {
        if (!$id)
        {
            if ($require_published) $this->outcomes->record($source, 'empty');
            else $this->incomplete('dynamic_reference', __('An inline or dynamic view without a saved ID requires manual inspection.', 'listdom'));
            return;
        }
        if (!$this->budget($depth, 0, true)) return;
        $post = LSD_Diagnostics_Report::resource($id, [LSD_Base::PTYPE_SHORTCODE]);
        if (is_wp_error($post))
        {
            if (!$explicit && $post->get_error_code() === 'listdom_diagnostics_invalid_target')
                $this->outcomes->record($source, 'missing', 'missing_nested_reference', __('A linked saved view is missing; other references were inspected.', 'listdom'));
            else $this->error = $post;
            return;
        }
        $this->report->fact('view:' . $id, 'stored_status', (string) $post->post_status);
        if ($require_published && !LSD_Integrations_Blockeditor_Layout::eligible($post))
        {
            $this->report->finding('native_layout_unpublished', 'warning', __('A native layout block references an unpublished view and does not render that layout.', 'listdom'), ['view:' . $id, $source]);
            return;
        }
        $this->outcomes->record($source, 'usable');
        $this->candidates[] = ['view_id' => $id, 'atts' => $atts, 'source' => $source,
            'selector' => substr(hash('sha256', $source . ':' . $id . ':' . wp_json_encode($atts)), 0, 32)];
    }
}
