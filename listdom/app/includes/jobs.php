<?php

class LSD_Jobs extends LSD_Base
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_ALREADY_PENDING = 'already_pending';
    public const STATUS_FAILED = 'failed';

    protected LSD_db $db;

    public function __construct()
    {
        $this->db = new LSD_db();
    }

    public function init()
    {
        add_filter('cron_schedules', [$this, 'schedule']);
        add_action('lsd_jobs_run', [$this, 'run']);

        add_action('init', function ()
        {
            if (!wp_next_scheduled('lsd_jobs_run')) wp_schedule_event(time(), 'lsd_minute', 'lsd_jobs_run');
        });
    }

    public function schedule($schedules)
    {
        if (!isset($schedules['lsd_minute']))
        {
            $schedules['lsd_minute'] = [
                'interval' => MINUTE_IN_SECONDS,
                'display' => esc_html__('Every Minute', 'listdom'),
            ];
        }

        return $schedules;
    }

    public function run()
    {
        // Table Not Exists
        if (!$this->db->exists('lsd_jobs')) return;

        $jobs = $this->chunk();
        foreach ($jobs as $job)
        {
            $job_id = $job['id'];
            $data = maybe_unserialize($job['data']);

            // Job Hook
            $hook = 'lsd_job_' . $job['type'];
            if (isset($job['sub_type']) && $job['sub_type']) $hook .= '_' . $job['sub_type'];

            // Run the Job
            $result = apply_filters($hook, false, $data, $job);

            // Delete Job
            if ($result) $this->delete($job_id);
            // Mark as Ran
            else $this->ran($job_id);
        }
    }

    public function chunk(int $limit = 10, int $max_runs = 100)
    {
        // Chunk Limit
        $limit = (int) apply_filters('lsd_jobs_chunk_limit', $limit);

        // Max Runs
        $max_runs = $this->max_runs($max_runs);

        $now = lsd_date('Y-m-d H:i:s');
        return $this->db->select(
            "SELECT * FROM `#__lsd_jobs` WHERE `run_at` <= '" . esc_sql($now) . "' AND `runs` < " . esc_sql($max_runs) . " ORDER BY `priority` DESC, `created_at` ASC LIMIT " . esc_sql($limit),
            'loadAssocList'
        );
    }

    public function add($type, array $data = [], ?string $sub_type = null, int $priority = 1, ?string $run_at = null)
    {
        $now = lsd_date('Y-m-d H:i:s');
        if (!$run_at) $run_at = $now;

        return $this->db->q(
            "INSERT INTO `#__lsd_jobs` (`type`, `sub_type`, `data`, `priority`, `runs`, `run_at`, `created_at`, `updated_at`)" .
            " VALUES ('" . esc_sql($type) . "', '" . esc_sql($sub_type) . "', '" . esc_sql(maybe_serialize($data)) . "', '" . esc_sql($priority) . "', '0', '" . esc_sql($run_at) . "', '$now', '$now')",
            'insert'
        );
    }

    public function add_once($type, array $data = [], ?string $sub_type = null, int $priority = 1, ?string $run_at = null)
    {
        $existing = $this->pending($type, $data, $sub_type);
        if ($existing > 0) return [
            'job_id' => $existing,
            'status' => self::STATUS_ALREADY_PENDING,
        ];

        $job_id = $this->add($type, $data, $sub_type, $priority, $run_at);

        return [
            'job_id' => (int) $job_id,
            'status' => $job_id ? self::STATUS_QUEUED : self::STATUS_FAILED,
        ];
    }

    public function update(int $job_id, array $data = [])
    {
        $now = lsd_date('Y-m-d H:i:s');

        return $this->db->q(
            "UPDATE `#__lsd_jobs` SET `data`='" . esc_sql(maybe_serialize($data)) . "', `updated_at`='$now' WHERE `id`='" . esc_sql($job_id) . "'",
            'update'
        );
    }

    protected function delete(int $job_id)
    {
        return $this->db->q("DELETE FROM `#__lsd_jobs` WHERE `id`='" . esc_sql($job_id) . "'", 'DELETE');
    }

    protected function pending($type, array $data = [], ?string $sub_type = null): int
    {
        return (int) $this->db->select($this->db->prepare(
            "SELECT `id` FROM `#__lsd_jobs` WHERE `type`=%s AND `sub_type`=%s AND `data`=%s AND `runs`<%d LIMIT 1",
            $type,
            (string) $sub_type,
            maybe_serialize($data),
            $this->max_runs()
        ), 'loadResult');
    }

    protected function ran(int $job_id)
    {
        $now = lsd_date('Y-m-d H:i:s');
        return $this->db->q("UPDATE `#__lsd_jobs` SET `runs`=`runs`+1, `updated_at`='$now' WHERE `id`='" . esc_sql($job_id) . "'");
    }

    protected function max_runs(int $max_runs = 100): int
    {
        return (int) apply_filters('lsd_jobs_max_runs', $max_runs);
    }
}
