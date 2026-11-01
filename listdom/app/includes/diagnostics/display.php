<?php

/** Effective saved presentation without invoking the renderer or extension filters. */
class LSD_Diagnostics_Display
{
    public function inspect(array $candidate, LSD_Diagnostics_Report $report)
    {
        $id = $candidate['view_id'];
        $metadata = new LSD_Diagnostics_Metadata($report);
        $saved = $metadata->view($id);
        if ($saved === null) return null;
        $atts = (new LSD_Shortcodes())->parse_saved($saved, $candidate['atts'] + ['id' => $id]);
        $name = $atts['lsd_display']['skin'] ?? 'grid';
        $known = ['singlemap', 'list', 'grid', 'side', 'listgrid', 'halfmap', 'table', 'cover', 'carousel', 'slider', 'masonry', 'accordion', 'mosaic', 'timeline', 'gallery'];
        if (!in_array($name, $known, true))
            $report->limit('unknown_skin', __('The saved skin is unknown; core uses its grid fallback.', 'listdom'));
        $skin = (new LSD_Shortcodes_Listdom())->SKO($name);
        $skin->prepare_inspection($atts);
        // These first-party after-start methods only resolve saved properties.
        if ($skin->skin !== 'table') $skin->after_start();
        else $skin->style = $skin->default_style;
        $ref = 'view:' . $id;
        $report->fact($ref, 'skin', (string) $skin->skin);
        $report->fact($ref, 'style', (string) $skin->style);
        $report->fact($ref, 'columns', (int) $skin->columns);
        $report->fact($ref, 'configured_image_method', (string) $skin->image_method);
        $report->fact($ref, 'image_fit', $skin->image_fit === 'contain' ? 'contain' : 'cover');
        $report->fact($ref, 'pro_active', LSD_Base::isPro());
        $pro_unavailable = LSD_Base::isLite() && in_array($skin->skin, ['side', 'accordion', 'mosaic', 'timeline', 'gallery'], true);
        $map_unavailable = !LSD_Components::map() && in_array($skin->skin, ['singlemap', 'halfmap'], true);
        if ($pro_unavailable)
            $report->finding('skin_requires_pro', 'warning', __('The selected skin requires Listdom Pro and does not render listings in Lite.', 'listdom'), [$ref]);
        if ($map_unavailable)
            $report->finding('skin_requires_map', 'warning', __('The selected map skin requires the map component.', 'listdom'), [$ref]);
        $size = $skin->presentation_image_size();
        $method = $skin->image_method === 'slider' ? 'slider' : 'cover';
        if ($skin->skin === 'slider' || $skin->skin === 'side') $method = 'cover';
        if ($skin->skin === 'cover')
        {
            $method = 'cover';
            if (in_array($skin->style, ['style1', 'style2', 'style3'], true)) $size = 'full';
        }
        if ($skin->is_builder_style())
        {
            $template_id = (int) $skin->style;
            $template = LSD_Diagnostics_Report::resource($template_id, [LSD_Base::PTYPE_TEMPLATE, 'elementor_library', 'page', 'listdom-elm-details', 'listdom-div-details', 'listdom-brx-details']);
            if (is_wp_error($template)) return $template;
            $report->fact($ref, 'template_id', $template_id);
            $template_ref = 'template:' . $template_id;
            $report->fact($template_ref, 'post_type', (string) $template->post_type);
            $report->fact($template_ref, 'status', (string) $template->post_status);
            $type = $metadata->value($template_id, $template->post_type === LSD_Base::PTYPE_TEMPLATE ? '_lsd_template_type' : 'lsd_type');
            if ($type === null) return null;
            $template_type = LSD_Builders::template_type_from_value($template, $type);
            $card_compatible = $template_type === 'listing_card';
            $report->fact($template_ref, 'template_type', $template_type);
            $report->fact($template_ref, 'listing_card_compatible', $card_compatible);
            if (!$card_compatible) $report->finding('template_type_incompatible', 'warning', __('The selected template is not a listing-card template and is rejected by the card renderer.', 'listdom'), [$template_ref, $ref]);
            if ($template->post_type === LSD_Base::PTYPE_TEMPLATE)
            {
                $published = $template->post_status === 'publish';
                $report->fact($template_ref, 'frontend_publication_ready', $published);
                if (!$published) $report->finding('native_template_unpublished', 'warning', __('The native card template is not published and will not render on the frontend outside an authorized preview.', 'listdom'), [$template_ref, $ref]);
                // Saved image controls are evidence only; custom templates can contain several images.
                $layout = $metadata->value($template_id, '_lsd_template_layout');
                if ($layout === null) return null;
                if (is_string($layout)) $layout = json_decode($layout, true);
                $nodes = 0;
                $layout_nodes = is_array($layout['layout'] ?? null) ? $layout['layout'] : [];
                $elements = is_array($layout['elements'] ?? null) ? $layout['elements'] : [];
                $this->template_images($layout_nodes, $elements, $template_id, $report, $nodes);
            }
            else $report->limit('builder_template', __('The saved builder card template is accessible, but its rendered image behavior requires builder inspection.', 'listdom'));
            $size = null;
            $method = 'unknown';
            $report->limit('custom_template', __('Saved template controls do not establish the final image source, responsive layout or rendered appearance.', 'listdom'));
        }
        elseif (!preg_match('/^style[1-5]$/', (string) $skin->style))
        {
            $size = null;
            $method = 'unknown';
            $report->limit('custom_style', __('Custom style presentation requires manual inspection.', 'listdom'));
        }
        if (in_array($skin->skin, ['table', 'singlemap'], true))
        {
            $size = null;
            $method = 'unknown';
            $report->limit('contextual_images', __('Table columns and map popups have separate image contexts; inspect their saved configuration and rendered output.', 'listdom'));
        }
        // Cover and Slider render their image regardless of the card visibility switch.
        $enabled = $pro_unavailable || $map_unavailable ? false
            : ($method === 'unknown' ? null : (in_array($skin->skin, ['cover', 'slider'], true) || (bool) $skin->display_image));
        $report->fact($ref, 'image_enabled', $enabled);
        $report->fact($ref, 'image_method', $method);
        $report->fact($ref, 'image_source', $method === 'unknown' ? 'unresolved' : ($method === 'slider' ? 'gallery_with_featured_policy' : 'featured'));
        $report->fact($ref, 'requested_size', is_array($size) ? implode('x', $size) : $size);
        if (is_array($size))
        {
            $report->fact($ref, 'requested_width', $size[0]);
            $report->fact($ref, 'requested_height', $size[1]);
        }
        $registered = wp_get_registered_image_subsizes();
        if (is_string($size) && isset($registered[$size])) $report->fact($ref, 'registered_crop', (bool) $registered[$size]['crop']);
        else $report->fact($ref, 'registered_crop', null);
        $report->limit('rendered_appearance', __('No page was rendered. CSS, responsive dimensions, theme overrides and visible stretching require browser inspection.', 'listdom'));
        if (has_filter('lsd_skins_atts') !== false || has_filter('lsd_shortcode_atts') !== false || has_filter('lsd_pre_shortcode') !== false)
            $report->limit('presentation_filters', __('Runtime shortcode and skin filters were not executed; saved first-party configuration is reported.', 'listdom'));
        return ['skin' => $skin, 'size' => $size, 'method' => $method, 'enabled' => $enabled];
    }

    private function template_images(array $layout, array $elements, int $id, LSD_Diagnostics_Report $report, int &$nodes, int $depth = 0): void
    {
        foreach ($layout as $node)
        {
            if (!is_array($node)) continue;
            if (!$report->visit($depth))
            {
                $report->limit('template_limit', __('Saved template inspection reached its node or depth limit.', 'listdom'));
                return;
            }
            $nodes++;
            if (($node['type'] ?? '') === 'image')
            {
                // Layout nodes reference controls in the separately persisted element map.
                $settings = $elements[$node['id'] ?? '']['settings']['style'] ?? [];
                foreach (['image_resolution', 'image_height', 'image_object_fit'] as $key)
                {
                    $value = $settings[$key] ?? null;
                    if (is_scalar($value)) $report->fact('template:' . $id . ':image:' . $nodes, $key, $value);
                }
            }
            if (is_array($node['children'] ?? null)) $this->template_images($node['children'], $elements, $id, $report, $nodes, $depth + 1);
        }
    }
}
