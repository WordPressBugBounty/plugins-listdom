<?php

class LSD_IX_Import_Session extends LSD_Base
{
    const TTL = DAY_IN_SECONDS;
    const CLEANUP_HOOK = 'lsd_ix_import_session_cleanup';
    protected static bool $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) return;
        self::$initialized = true;

        add_action(self::CLEANUP_HOOK, [self::class, 'cleanup'], 10, 4);
    }

    public function get(string $format): ?array
    {
        $format = $this->format($format);
        $state = get_transient($this->key($format));

        if (!is_array($state)) return null;
        if (($state['user_id'] ?? 0) !== get_current_user_id()) return null;
        if (($state['format'] ?? '') !== $format) return null;

        return $state;
    }

    public function create(string $format, array $state): ?array
    {
        $format = $this->format($format);
        if ($this->get($format)) return null;

        $created_at = time();
        $state = array_merge($state, [
            'id' => wp_generate_uuid4(),
            'format' => $format,
            'user_id' => get_current_user_id(),
            'offset' => 0,
            'created_at' => $created_at,
            'expires_at' => $created_at + self::TTL,
        ]);

        $this->save($state);
        return $state;
    }

    public function update_offset(array $state, int $offset): array
    {
        $state['offset'] = max(0, $offset);
        $this->save($state);

        return $state;
    }

    public function delete(string $format, ?string $id = null, string $file = ''): void
    {
        $format = $this->format($format);
        $state = $this->get($format);

        if ($state && $id && !hash_equals((string) ($state['id'] ?? ''), $id)) return;

        if ($state)
        {
            $file = (string) ($state['file'] ?? $file);
            $args = [$format, get_current_user_id(), $state['id'], $file];
            $timestamp = wp_next_scheduled(self::CLEANUP_HOOK, $args);
            if ($timestamp) wp_unschedule_event($timestamp, self::CLEANUP_HOOK, $args);
        }

        delete_transient($this->key($format));

        if ($file !== '') LSD_File::delete($this->get_upload_path() . sanitize_file_name($file));
    }

    public static function cleanup(string $format, int $user_id, string $id, string $file): void
    {
        $format = sanitize_key($format);
        if (!in_array($format, ['csv', 'excel'], true)) return;

        $key = self::transient_key($format, $user_id);
        $state = get_transient($key);

        if (is_array($state) && ($state['id'] ?? '') !== $id) return;

        delete_transient($key);
        if ($file !== '') LSD_File::delete((new LSD_Main())->get_upload_path() . sanitize_file_name($file));
    }

    protected function save(array $state): void
    {
        $format = $this->format($state['format'] ?? '');
        $user_id = (int) ($state['user_id'] ?? 0);
        $id = (string) ($state['id'] ?? '');
        $file = sanitize_file_name($state['file'] ?? '');
        $expires_at = (int) ($state['expires_at'] ?? (time() + self::TTL));
        $expiration = max(1, $expires_at - time());

        set_transient(self::transient_key($format, $user_id), $state, $expiration);

        if (!wp_next_scheduled(self::CLEANUP_HOOK, [$format, $user_id, $id, $file]))
        {
            wp_schedule_single_event($expires_at, self::CLEANUP_HOOK, [$format, $user_id, $id, $file]);
        }
    }

    protected function key(string $format): string
    {
        return self::transient_key($format, get_current_user_id());
    }

    protected static function transient_key(string $format, int $user_id): string
    {
        return 'lsd_ix_import_session_' . $format . '_' . $user_id;
    }

    protected function format(string $format): string
    {
        return in_array($format, ['csv', 'excel'], true) ? $format : 'csv';
    }
}
