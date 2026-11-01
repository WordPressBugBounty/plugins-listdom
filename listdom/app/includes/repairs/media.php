<?php

/** Small, actor-bound missing-derivative jobs. No original replacement or remote I/O. */
class LSD_Repairs_Media
{
    const HOOK = 'lsd_repair_media_tick';

    public static function init(): void
    {
        add_action(self::HOOK, [new self(), 'tick']);
    }

    private function key(string $id): string
    {
        return 'lsd_media_repair_' . $id;
    }

    private function locked(string $id, callable $callback)
    {
        global $wpdb;
        $lock = 'lsd-repair-' . substr(hash('sha256', get_current_blog_id() . ':' . $id), 0, 40);
        if ((string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) !== '1') return LSD_Repairs_Display::error('job_busy');
        try
        {
            return $callback();
        }
        finally
        {
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
        }
    }

    private function attachment(int $id, array $sizes)
    {
        $post = LSD_Diagnostics_Report::resource($id, ['attachment']);
        if (is_wp_error($post)) return $post;
        if (!wp_attachment_is_image($id)) return LSD_Repairs_Display::error('not_image');
        if (!wp_image_editor_supports(['mime_type' => get_post_mime_type($id)])) return LSD_Repairs_Display::error('unsupported_editor');
        $file = get_attached_file($id, true);
        $uploads = wp_get_upload_dir();
        $base = realpath($uploads['basedir']);
        $real = is_string($file) ? realpath($file) : false;
        if (!$base || !$real || strpos($real, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($real) || !is_readable($real)) return LSD_Repairs_Display::error('local_original_unavailable');
        // Filters/offload plugins must not silently redirect a local repair.
        if (get_attached_file($id) !== $file) return LSD_Repairs_Display::error('offloaded_media');
        $metadata = wp_get_attachment_metadata($id);
        if (!is_array($metadata) || empty($metadata['file']) || empty($metadata['width']) || empty($metadata['height'])) return LSD_Repairs_Display::error('metadata_unavailable');
        if (filesize($real) > 25 * 1024 * 1024 || (int) $metadata['width'] * (int) $metadata['height'] > 40000000) return LSD_Repairs_Display::error('image_resource_limit');
        if (realpath($uploads['basedir'] . '/' . $metadata['file']) !== $real) return LSD_Repairs_Display::error('original_mapping');
        $registered = wp_get_registered_image_subsizes();
        $missing = [];
        $definitions = [];
        foreach ($sizes as $size)
        {
            if (!isset($registered[$size])) return LSD_Repairs_Display::error('unknown_size');
            $definitions[$size] = $registered[$size];
            $name = $metadata['sizes'][$size]['file'] ?? '';
            if ($name && basename($name) !== $name) return LSD_Repairs_Display::error('invalid_derivative');
            if (!$name || !is_file(dirname($real) . '/' . $name))
            {
                if (!image_resize_dimensions((int) $metadata['width'], (int) $metadata['height'], (int) $registered[$size]['width'], (int) $registered[$size]['height'], $registered[$size]['crop'])) return LSD_Repairs_Display::error('size_not_generatable');
                $missing[] = $size;
            }
        }
        return ['file' => $real, 'metadata' => $metadata, 'definitions' => $definitions, 'missing' => $missing,
            'revision' => LSD_Repairs_Display::revision([$id, $metadata, $definitions, filesize($real), filemtime($real)])];
    }

    private function plan(array $input)
    {
        $listing = LSD_Diagnostics_Report::resource((int) $input['listing_id'], [LSD_Base::PTYPE_LISTING]);
        if (is_wp_error($listing)) return $listing;
        $restore = LSD_Diagnostics_Report::suspend_thumbnail_fallback();
        try
        {
            $gallery = get_post_meta($listing->ID, 'lsd_gallery', true);
            $allowed = array_map('intval', array_merge(is_array($gallery) ? $gallery : [], [(int) get_post_thumbnail_id($listing->ID)]));
        }
        finally
        {
            $restore();
        }
        $items = [];
        foreach ($input['attachment_ids'] as $id)
        {
            if (!in_array($id, $allowed, true)) return LSD_Repairs_Display::error('attachment_not_in_listing');
            $state = $this->attachment($id, $input['sizes']);
            if (is_wp_error($state)) return $state;
            $items[] = ['id' => $id, 'missing' => $state['missing'], 'revision' => $state['revision']];
        }
        return ['items' => $items, 'revision' => LSD_Repairs_Display::revision([get_current_user_id(), $input['listing_id'], $items])];
    }

    public function preview(array $input)
    {
        $plan = $this->plan($input);
        if (is_wp_error($plan)) return $plan;
        if (isset($input['expectedRevision']) && !hash_equals($plan['revision'], $input['expectedRevision'])) return LSD_Repairs_Display::error('stale');
        return ['revision' => $plan['revision'], 'preview' => ['title' => __('Generate missing listing thumbnails', 'listdom'),
            'fields' => [['key' => 'attachments', 'label' => __('Selected missing derivatives', 'listdom'), 'before' => $plan['items'], 'after' => $input['sizes']]],
            'warnings' => [__('A background job creates derivative files. Completed files remain if cancelled. No browser appearance is verified.', 'listdom')]]];
    }

    public function enqueue(array $input)
    {
        $id = substr(LSD_Repairs_Display::revision([get_current_user_id(), $input]), 0, 40);
        return $this->locked($id, function () use ($id, $input)
        {
            $existing = get_option($this->key($id), null);
            if ($existing !== null) return $this->status(['job_id' => $id], false);
            $plan = $this->plan($input);
            if (is_wp_error($plan)) return $plan;
            if (!hash_equals($plan['revision'], $input['expectedRevision'])) return LSD_Repairs_Display::error('stale');
            $job = ['actor' => get_current_user_id(), 'blog' => get_current_blog_id(), 'input' => $input, 'items' => $plan['items'],
                'cursor' => 0, 'state' => 'queued', 'created' => time(), 'results' => []];
            if (!add_option($this->key($id), $job, '', false)) return LSD_Repairs_Display::error('job_save', true);
            // Establish retention before dispatch. No files have been touched, so
            // removing an undispatched journal permits an explicit fresh attempt.
            if (!$this->schedule_cleanup($id, $job) || !$this->schedule_tick($id, $job))
            {
                // Remove retention along with an undispatched journal. Leaving
                // it behind would make WordPress deduplicate a later retry's
                // cleanup event, even though no repair had ever been dispatched.
                if (wp_clear_scheduled_hook(self::HOOK, [$id]) === false)
                {
                    $job['state'] = 'failed';
                    update_option($this->key($id), $job, false);
                    return LSD_Repairs_Display::error('job_schedule', true);
                }
                $removed = delete_option($this->key($id));
                return LSD_Repairs_Display::error('job_schedule', !$removed);
            }
            LSD_Repairs_Display::audit('media_queued', $input['listing_id'], ['job_id' => $id]);
            do_action('lsd_ability_repair_applied', 'media_queued', $input['listing_id'], ['job_id' => $id]);
            return $this->status(['job_id' => $id], false);
        });
    }

    public function status(array $input, bool $recover = true)
    {
        $id = $input['job_id'];
        $job = get_option($this->key($id), null);
        if (!$job || (int) $job['actor'] !== get_current_user_id() || (int) $job['blog'] !== get_current_blog_id()) return LSD_Repairs_Display::error('job_forbidden');
        $listing = LSD_Diagnostics_Report::resource((int) $job['input']['listing_id'], [LSD_Base::PTYPE_LISTING]);
        if (is_wp_error($listing)) return $listing;
        foreach ($job['items'] as $item)
        {
            $resource = LSD_Diagnostics_Report::resource($item['id'], ['attachment']);
            if (is_wp_error($resource)) return $resource;
        }
        // If the worker no longer owns its lock, a running journal means interrupted work.
        if ($recover && $job['state'] === 'running')
        {
            $recovered = $this->locked($id, function () use ($id)
            {
                $current = get_option($this->key($id));
                if ($current['state'] === 'running') $current['state'] = 'uncertain';
                return $current;
            });
            if (!is_wp_error($recovered)) $job = $recovered;
        }
        $outcome = 'not_verified';
        if (!in_array($job['state'], ['queued', 'running'], true))
        {
            $matched = 0;
            foreach ($job['items'] as $item)
            {
                $state = $this->attachment($item['id'], $job['input']['sizes']);
                if (!is_wp_error($state) && !$state['missing']) $matched++;
            }
            $outcome = $matched === count($job['items']) ? 'fixed' : ($matched ? 'partially_fixed' : 'not_fixed');
        }
        return ['job_id' => $id, 'state' => $job['state'], 'processed' => $job['cursor'], 'total' => count($job['items']), 'results' => $job['results'],
            'verification' => ['outcome' => $outcome, 'scope' => 'local_derivative_files'],
            'limitations' => [__('Remote availability and rendered appearance were not inspected. Job records are retained for seven days.', 'listdom')]];
    }

    public function cancel(array $input)
    {
        $status = $this->status($input);
        if (is_wp_error($status)) return $status;
        return $this->locked($input['job_id'], function () use ($input)
        {
            $job = get_option($this->key($input['job_id']));
            if ($job['state'] === 'queued')
            {
                $job['state'] = 'cancelled';
                update_option($this->key($input['job_id']), $job, false);
                LSD_Repairs_Display::audit('media_cancelled', $job['input']['listing_id'], ['job_id' => $input['job_id']]);
                wp_clear_scheduled_hook(self::HOOK, [$input['job_id']]);
                $this->schedule_cleanup($input['job_id'], $job);
            }
            return $this->status($input, false);
        });
    }

    private function schedule_tick(string $id, array $job): bool
    {
        $next = wp_next_scheduled(self::HOOK, [$id]);
        // WordPress reports duplicate single events as false. An existing work
        // event is sufficient; the separate retention event cannot advance a job.
        if ($next && $next < $job['created'] + 7 * DAY_IN_SECONDS) return true;
        return (bool) wp_schedule_single_event(time() + 1, self::HOOK, [$id]);
    }

    private function schedule_cleanup(string $id, array $job): bool
    {
        $expiry = $job['created'] + 7 * DAY_IN_SECONDS + 1;
        return (bool) wp_get_scheduled_event(self::HOOK, [$id], $expiry)
            || (bool) wp_schedule_single_event($expiry, self::HOOK, [$id]);
    }

    /** Generate one attachment while holding its cross-job lock. */
    private function process_attachment(string $id, array &$job): void
    {
        $item = $job['items'][$job['cursor']];
        $state = $this->attachment($item['id'], $job['input']['sizes']);
        if (is_wp_error($state) || $state['revision'] !== $item['revision']) throw new RuntimeException('stale');

        $running = $job;
        $running['state'] = 'running';
        if (!update_option($this->key($id), $running, false)) throw new RuntimeException('checkpoint');
        $job = $running;
        if ($state['missing']) $this->generate_derivatives($item['id'], $state, $job['input']['sizes']);

        $job['results'][] = [
            'attachment_id' => $item['id'],
            'outcome' => $state['missing'] ? 'applied' : 'unchanged',
        ];
        LSD_Repairs_Display::audit('media_attachment', $job['input']['listing_id'], [
            'job_id' => $id,
            'attachment_id' => $item['id'],
            'sizes' => $state['missing'],
        ]);
        $job['cursor']++;
        $job['state'] = $job['cursor'] === count($job['items']) ? 'completed' : 'queued';
    }

    private function generate_derivatives(int $attachment, array $state, array $requested): void
    {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $editor = wp_get_image_editor($state['file']);
        if (is_wp_error($editor)) throw new RuntimeException('editor');
        $sizes = $editor->multi_resize(array_intersect_key($state['definitions'], array_flip($state['missing'])));
        if (count($sizes) !== count($state['missing'])) throw new RuntimeException('derivatives');

        // Preserve metadata siblings; a concurrent non-repair save is not overwritten.
        $metadata = wp_get_attachment_metadata($attachment);
        if ($metadata !== $state['metadata']) throw new RuntimeException('metadata_changed');
        $metadata['sizes'] = array_replace($metadata['sizes'] ?? [], $sizes);
        wp_update_attachment_metadata($attachment, $metadata);
        $readback = $this->attachment($attachment, $requested);
        if (is_wp_error($readback) || $readback['missing']) throw new RuntimeException('readback');
    }

    public function tick(string $id): void
    {
        if (!preg_match('/^[a-f0-9]{40}$/D', $id)) return;
        $this->locked($id, function () use ($id)
        {
            $job = get_option($this->key($id));
            if (!$job) return;
            if (time() - $job['created'] > 7 * DAY_IN_SECONDS)
            {
                delete_option($this->key($id));
                return;
            }
            if ($job['state'] !== 'queued') return;
            $actor = get_current_user_id();
            wp_set_current_user($job['actor']);
            try
            {
                $plan = $this->plan($job['input']);
                if (is_wp_error($plan)) throw new RuntimeException('prerequisites');
                $attachment = $job['items'][$job['cursor']]['id'];
                // Different actors or size selections can enqueue overlapping jobs.
                // Serialize the shared attachment from fresh read through readback.
                $result = $this->locked('attachment:' . $attachment, function () use ($id, &$job)
                {
                    $this->process_attachment($id, $job);
                });
                if (is_wp_error($result)) throw new RuntimeException('attachment_busy');
            }
            catch (Throwable $error)
            {
                $job['state'] = $job['state'] === 'running' ? 'uncertain' : 'failed';
            }
            finally
            {
                wp_set_current_user($actor);
            }

            // A lost completion write leaves the durable running checkpoint for
            // read-only reconciliation. Never dispatch more work after that failure.
            if (!update_option($this->key($id), $job, false)) return;
            if ($job['state'] === 'queued' && !$this->schedule_tick($id, $job))
            {
                $job['state'] = 'failed';
                update_option($this->key($id), $job, false);
            }
            $this->schedule_cleanup($id, $job);
        });
    }
}
