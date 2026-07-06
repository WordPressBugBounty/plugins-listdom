<?php

class LSD_Blueprints extends LSD_Base
{
    const HISTORY_OPTION = 'lsd_blueprint_history';

    protected static ?self $instance = null;
    protected ?LSD_Blueprints_Registry $registry = null;

    public static function instance(): self
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function init(): void
    {
        if (!$this->registry) $this->registry = new LSD_Blueprints_Registry();
    }

    public function registry(): LSD_Blueprints_Registry
    {
        $this->init();
        return $this->registry;
    }

    public function all(): array
    {
        $items = [];
        foreach ($this->registry()->all() as $blueprint)
        {
            $items[] = [
                'id' => $blueprint->get_id(),
                'label' => $blueprint->get_label(),
                'description' => $blueprint->get_description(),
            ];
        }

        return $items;
    }

    public function definition(string $blueprint_id, array $options = []): array
    {
        $blueprint = $this->registry()->get($blueprint_id);
        if (!$blueprint) return [];

        return $blueprint->definition($options);
    }

    public function preview(string $blueprint_id, array $options = []): array
    {
        $definition = $this->definition($blueprint_id, $options);
        if (!count($definition))
        {
            return [
                'success' => 0,
                'message' => esc_html__('The selected blueprint was not found.', 'listdom'),
            ];
        }

        return [
            'success' => 1,
            'preview' => $this->run_plan($definition, $options, true),
        ];
    }

    public function apply(string $blueprint_id, array $options = []): array
    {
        $definition = $this->definition($blueprint_id, $options);
        if (!count($definition))
        {
            return [
                'success' => 0,
                'message' => esc_html__('The selected blueprint was not found.', 'listdom'),
            ];
        }

        $application_id = wp_generate_uuid4();
        $result = $this->run_plan($definition, $options, false, $application_id);
        $history = [
            'application_id' => $application_id,
            'blueprint_id' => $definition['id'],
            'label' => $definition['label'],
            'created_at' => current_time('mysql'),
            'options' => $options,
            'summary' => $result['summary'],
            'items' => $result['items'],
        ];

        $this->store_history($history);

        return [
            'success' => 1,
            'application' => $history,
            'next_steps' => $definition['next_steps'] ?? [],
        ];
    }

    public function history(): array
    {
        $history = get_option(self::HISTORY_OPTION, []);
        return is_array($history) ? $history : [];
    }

    protected function store_history(array $entry): void
    {
        $history = $this->history();
        array_unshift($history, $entry);
        if (count($history) > 20) $history = array_slice($history, 0, 20);

        update_option(self::HISTORY_OPTION, $history, false);
    }

    protected function run_plan(array $definition, array $options = [], bool $dry_run = true, string $application_id = ''): array
    {
        $include_demo = !isset($options['include_demo']) || $options['include_demo'];
        $plan = $this->plan($definition, $include_demo);
        $items = [];
        $summary = ['create' => 0, 'update' => 0, 'reuse' => 0, 'error' => 0];

        foreach ($plan as $step)
        {
            $response = LSD_Actions::instance()->execute($step['action'], $step['input'], [
                'dry_run' => $dry_run,
                'approved' => !$dry_run,
                'source' => 'blueprint',
                'blueprint_id' => $definition['id'],
                'application_id' => $application_id,
            ]);

            $operation = $response['data']['operation'] ?? ($response['meta']['dry_run'] ?? false ? ($step['input']['reuse_existing'] ?? false ? 'reuse' : 'create') : 'create');
            if (empty($response['success'])) $operation = 'error';
            if (!isset($summary[$operation])) $summary[$operation] = 0;
            $summary[$operation]++;

            $items[] = [
                'group' => $step['group'],
                'label' => $step['label'],
                'action' => $step['action'],
                'result' => $response,
                'operation' => $operation,
            ];
        }

        return [
            'definition' => $definition,
            'items' => $items,
            'summary' => $summary,
            'recommendations' => $definition['recommendations'] ?? [],
            'next_steps' => $definition['next_steps'] ?? [],
        ];
    }

    protected function plan(array $definition, bool $include_demo = true): array
    {
        $generate = $definition['generate'] ?? [];
        $plan = [];
        $map = [
            'categories' => ['action' => 'create_category', 'label' => esc_html__('Category', 'listdom')],
            'locations' => ['action' => 'create_category', 'label' => esc_html__('Location', 'listdom')],
            'labels' => ['action' => 'create_category', 'label' => esc_html__('Label', 'listdom')],
            'custom_fields' => ['action' => 'create_custom_field', 'label' => esc_html__('Custom Field', 'listdom')],
            'search_forms' => ['action' => 'create_search_form', 'label' => esc_html__('Search Form', 'listdom')],
            'pages' => ['action' => 'create_directory_page', 'label' => esc_html__('Page', 'listdom')],
            'demo_listings' => ['action' => 'create_demo_listing', 'label' => esc_html__('Demo Listing', 'listdom')],
        ];

        foreach ($map as $group => $config)
        {
            if ($group === 'demo_listings' && !$include_demo) continue;
            $entries = isset($generate[$group]) && is_array($generate[$group]) ? $generate[$group] : [];

            foreach ($entries as $entry)
            {
                $plan[] = [
                    'group' => $group,
                    'action' => $config['action'],
                    'label' => $entry['title'] ?? ($entry['name'] ?? $config['label']),
                    'input' => $entry,
                ];
            }
        }

        return apply_filters('lsd_blueprint_plan', $plan, $definition, $include_demo);
    }
}
