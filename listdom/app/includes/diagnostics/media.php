<?php

/** Inspect only declared media references, metadata and local upload files. */
class LSD_Diagnostics_Media
{
    public function inspect(array $input, LSD_Diagnostics_Report $report, ?array $display = null)
    {
        $id = (int) ($input['listing_id'] ?? 0);
        $listing = LSD_Diagnostics_Report::resource($id, [LSD_Base::PTYPE_LISTING]);
        if (is_wp_error($listing)) return $listing;
        $page = (int) ($input['page'] ?? 1);
        $per_page = (int) ($input['per_page'] ?? 5);
        if ($page < 1 || $page > 10000 || $per_page < 1 || $per_page > 20)
            return LSD_Diagnostics_Report::error('invalid_input', __('Media pages contain between 1 and 20 attachments.', 'listdom'));
        $featured = (int) get_post_thumbnail_id($id);
        $gallery = get_post_meta($id, 'lsd_gallery', true);
        if (!is_array($gallery))
        {
            if ($gallery !== '' && $gallery !== false) $report->finding('invalid_gallery', 'warning', __('The stored gallery is not a list of attachment references.', 'listdom'), ['listing:' . $id]);
            $gallery = [];
        }
        $sources = LSD_Media_Sources::gallery($id, LSD_Options::settings());
        $ids = array_values(array_unique(array_merge($featured ? [$featured] : [], $gallery), SORT_REGULAR));
        $selected = $display && $display['enabled'] === true
            ? ($display['method'] === 'slider' ? $sources['ids'] : ($featured ? [$featured] : [])) : [];
        $report->fact('listing:' . $id, 'featured_id', $featured);
        $report->fact('listing:' . $id, 'gallery_references', count($gallery));
        $report->fact('listing:' . $id, 'gallery_featured_policy', $sources['policy']);
        $report->data['pagination'] = ['page' => $page, 'per_page' => $per_page, 'total' => count($ids), 'has_more' => $page * $per_page < count($ids)];
        if ($display && $display['enabled'] === true && !$selected)
            $report->finding('no_image_source', 'info', __('The selected presentation has no image source and may use a placeholder.', 'listdom'), ['listing:' . $id]);
        foreach (array_slice($ids, ($page - 1) * $per_page, $per_page) as $offset => $attachment_id)
        {
            if (!is_scalar($attachment_id) || !ctype_digit((string) $attachment_id) || (int) $attachment_id < 1)
            {
                $report->finding('invalid_attachment_reference', 'warning', __('A gallery reference is not a valid attachment ID.', 'listdom'), ['listing:' . $id . ':media:' . (($page - 1) * $per_page + $offset)]);
                continue;
            }
            $attachment_id = (int) $attachment_id;
            $ref = 'attachment:' . $attachment_id;
            $post = get_post($attachment_id);
            if (!$post instanceof WP_Post)
            {
                $report->finding('missing_attachment', 'warning', __('A referenced attachment record is missing.', 'listdom'), [$ref]);
                continue;
            }
            $attachment = LSD_Diagnostics_Report::resource($attachment_id, ['attachment']);
            if (is_wp_error($attachment))
            {
                if ($attachment->get_error_code() !== 'listdom_diagnostics_invalid_target') return $attachment;
                $report->finding('invalid_attachment', 'warning', __('A media reference points to a non-attachment record.', 'listdom'), [$ref]);
                continue;
            }
            $report->fact($ref, 'selected_source', $display && $display['enabled'] === false ? false
                : (!$display || $display['method'] === 'unknown' ? null : in_array($attachment_id, array_map('intval', $selected), true)));
            $image = wp_attachment_is_image($attachment_id);
            $report->fact($ref, 'is_image', $image);
            if (!$image)
            {
                $report->finding('non_image_attachment', 'warning', __('A referenced attachment is not an image.', 'listdom'), [$ref]);
                continue;
            }
            $metadata = wp_get_attachment_metadata($attachment_id);
            if (!is_array($metadata) || empty($metadata['width']) || empty($metadata['height']))
            {
                $report->finding('missing_image_metadata', 'warning', __('Image dimensions or metadata are missing.', 'listdom'), [$ref]);
                $metadata = is_array($metadata) ? $metadata : [];
            }
            $report->fact($ref, 'width', (int) ($metadata['width'] ?? 0));
            $report->fact($ref, 'height', (int) ($metadata['height'] ?? 0));
            $file = get_attached_file($attachment_id, true);
            $uploads = wp_get_upload_dir();
            $local = $this->local_file($file, $uploads['basedir']);
            $url = wp_get_attachment_url($attachment_id);
            $offloaded = get_attached_file($attachment_id) !== $file || !$url || strpos($url, trailingslashit($uploads['baseurl'])) !== 0
                || has_filter('get_attached_file') !== false || has_filter('wp_get_attachment_url') !== false;
            $availability = $local === true ? 'present' : ($offloaded || $local === null ? 'unverified' : 'missing');
            $report->fact($ref, 'local_file', $availability);
            if ($availability === 'missing') $report->finding('missing_local_image', 'warning', __('The local image file is missing from the configured uploads storage.', 'listdom'), [$ref]);
            if ($availability === 'unverified') $report->limit('offloaded_media', __('Remote or offloaded media availability was not verified. No URL was fetched.', 'listdom'));
            $sizes = is_array($metadata['sizes'] ?? null) ? $metadata['sizes'] : [];
            foreach ($sizes as $name => $size)
            {
                if (!is_array($size)) continue;
                $size_ref = $ref . ':size:' . sanitize_key($name);
                $report->fact($size_ref, 'width', (int) ($size['width'] ?? 0));
                $report->fact($size_ref, 'height', (int) ($size['height'] ?? 0));
                $filename = (string) ($size['file'] ?? '');
                $safe = $filename !== '' && basename($filename) === $filename && strpos($filename, '..') === false;
                $exists = $safe && is_string($file) ? $this->local_file(dirname($file) . '/' . $filename, $uploads['basedir']) : null;
                $report->fact($size_ref, 'local_file', $exists === true ? 'present' : ($offloaded || $exists === null ? 'unverified' : 'missing'));
                if ($exists === false && !$offloaded) $report->finding('missing_derivative_file', 'warning', __('A generated image size has metadata but its local file is missing.', 'listdom'), [$size_ref]);
            }
            if ($display && $display['size'] !== null && in_array($attachment_id, array_map('intval', $selected), true))
            {
                $size = $display['size'];
                $derivative = $size === 'full' ? null : image_get_intermediate_size($attachment_id, $size);
                $needed = is_string($size) ? $size !== 'full' : ((int) ($metadata['width'] ?? 0) > $size[0] || (int) ($metadata['height'] ?? 0) > $size[1]);
                // Metadata proves a derivative was recorded, not that its bytes remain available.
                $requested_file = $local;
                if ($derivative)
                {
                    $filename = (string) ($derivative['file'] ?? '');
                    $safe = $filename !== '' && basename($filename) === $filename && strpos($filename, '..') === false;
                    $requested_file = $safe && is_string($file) ? $this->local_file(dirname($file) . '/' . $filename, $uploads['basedir']) : null;
                }
                $available = $needed && !$derivative ? false : ($requested_file === true ? true : ($offloaded || $requested_file === null ? null : false));
                $report->fact($ref, 'requested_derivative_available', $available);
                if ($needed && !$derivative) $report->finding('requested_derivative_unavailable', 'warning', __('No generated derivative matches the requested size; WordPress may fall back to the original image.', 'listdom'), [$ref, 'view:' . (int) ($display['skin']->atts['id'] ?? 0)]);
            }
        }
        if (count($ids) > $per_page || $page > 1)
            $report->limit('media_page_scope', __('Only the requested media page was inspected; follow pagination before drawing conclusions about all attachments.', 'listdom'));
        return true;
    }

    /** Never inspect files outside uploads, follow directory trees or return a path. */
    private function local_file($file, string $base): ?bool
    {
        if (!is_string($file) || !$file || strpos($file, '://') !== false) return null;
        $lexical_base = rtrim(str_replace('\\', '/', $base), '/');
        $lexical_file = str_replace('\\', '/', $file);
        // Reject traversal before looking for an existing ancestor of a deleted directory.
        if (in_array('..', explode('/', $lexical_file), true) || strpos($lexical_file, $lexical_base . '/') !== 0) return null;
        $base = realpath($base);
        if (!$base) return null;
        $ancestor = dirname($file);
        while (!file_exists($ancestor) && !is_link($ancestor))
        {
            $parent = dirname($ancestor);
            if ($parent === $ancestor) return null;
            $ancestor = $parent;
        }
        $parent = realpath($ancestor);
        if (!$parent || ($parent !== $base && strpos($parent, $base . DIRECTORY_SEPARATOR) !== 0)) return null;
        $real = realpath($file);
        if ($real && strpos($real, $base . DIRECTORY_SEPARATOR) !== 0) return null;
        return is_file($file);
    }
}
