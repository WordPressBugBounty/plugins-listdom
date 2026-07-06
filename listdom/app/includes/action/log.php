<?php

class LSD_Action_Log extends LSD_Base
{
    public static function write(string $stage, string $action_id, LSD_Action_Context $context, array $payload = []): void
    {
        if (!LSD_Folder::exists(LSD_LOG_DIR)) LSD_Folder::create(LSD_LOG_DIR);

        $entry = [
            'timestamp' => current_time('mysql'),
            'stage' => sanitize_key($stage),
            'action' => $action_id,
            'request_id' => $context->id(),
            'source' => $context->source(),
            'user_id' => $context->user_id(),
            'dry_run' => $context->is_dry_run() ? 1 : 0,
            'payload' => $payload,
        ];

        LSD_File::append(LSD_LOG_DIR . 'actions.log', wp_json_encode($entry) . "\n");
        do_action('lsd_action_logged', $entry);
    }
}
