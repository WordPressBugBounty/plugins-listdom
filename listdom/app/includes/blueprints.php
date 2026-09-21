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

        return $this->configure_definition($blueprint->definition($options), $options);
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
        $preflight = $this->run_plan($definition, $options, true, $application_id);
        $preflight_errors = isset($preflight['summary']['error']) ? (int) $preflight['summary']['error'] : 0;
        if ($preflight_errors > 0)
        {
            return [
                'success' => 0,
                'message' => $this->preflight_message($preflight, $preflight_errors),
            ];
        }

        $result = $this->run_plan($definition, $options, false, $application_id);

        $errors = isset($result['summary']['error']) ? (int) $result['summary']['error'] : 0;
        if ($errors > 0)
        {
            return [
                'success' => 0,
                'message' => sprintf(esc_html__('%d setup actions failed. Setup stopped; earlier actions may have been applied.', 'listdom'), $errors),
            ];
        }

        $configuration = $this->apply_configuration($options);
        $history = [
            'application_id' => $application_id,
            'blueprint_id' => $definition['id'],
            'label' => $definition['label'],
            'created_at' => current_time('mysql'),
            'options' => $options,
            'summary' => $result['summary'],
            'items' => $result['items'],
            'recommendations' => $result['recommendations'],
            'configuration' => $configuration,
        ];

        $this->store_history($history);

        return [
            'success' => 1,
            'application' => $history,
            'next_steps' => $definition['next_steps'] ?? [],
        ];
    }

    protected function preflight_message(array $preflight, int $errors): string
    {
        $issue = '';
        foreach ($preflight['items'] ?? [] as $item)
        {
            $response = isset($item['result']) && is_array($item['result']) ? $item['result'] : [];
            if (!empty($response['success'])) continue;

            $label = isset($item['label']) ? (string) $item['label'] : '';
            $code = isset($response['code']) ? (string) $response['code'] : '';
            $message = isset($response['message']) ? (string) $response['message'] : '';
            $validation_errors = isset($response['errors']) && is_array($response['errors']) ? $response['errors'] : [];
            $first_error = isset($validation_errors[0]) && is_scalar($validation_errors[0]) ? (string) $validation_errors[0] : '';
            if ($first_error !== '') $message = trim($message . ' ' . $first_error);
            $data = isset($response['data']) && is_array($response['data']) ? $response['data'] : [];
            $reference = '';
            if (isset($data['post_id'])) $reference = sprintf(esc_html__('Post ID %d', 'listdom'), (int) $data['post_id']);
            else if (isset($data['term_id'])) $reference = sprintf(esc_html__('Term ID %d', 'listdom'), (int) $data['term_id']);

            $issue = trim($label . ($code !== '' ? ' [' . $code . ']' : '') . ($message !== '' ? ': ' . $message : '') . ($reference !== '' ? ' (' . $reference . ')' : ''));
            break;
        }

        if ($issue === '') return sprintf(esc_html__('%d setup actions failed validation. No changes were made.', 'listdom'), $errors);

        return sprintf(esc_html__('%1$d setup actions failed validation. %2$s No changes were made.', 'listdom'), $errors, esc_html($issue));
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
        $search_form_ids = [];
        $shortcode_ids = [];
        $summary = ['create' => 0, 'update' => 0, 'reuse' => 0, 'error' => 0];
        $field_slugs = [];

        foreach ($plan as $step)
        {
            $input = $step['input'];
            if ($step['action'] === 'create_search_form' && count($field_slugs)) $input['fields'] = $this->map_field_filters($input['fields'], $field_slugs);
            if ($step['action'] === 'create_directory_shortcode' && !empty($input['search_form_title']))
            {
                $search_form_title = (string) $input['search_form_title'];
                if (isset($search_form_ids[$search_form_title])) $input['search_form_id'] = $search_form_ids[$search_form_title];
            }
            if ($step['action'] === 'create_directory_page' && !empty($input['shortcode_title']))
            {
                $shortcode_title = (string) $input['shortcode_title'];
                if (isset($shortcode_ids[$shortcode_title])) $input['shortcode_id'] = $shortcode_ids[$shortcode_title];
            }
            if ($step['action'] === 'create_directory_page' && empty($input['shortcode_id']) && !empty($input['search_form_title']))
            {
                $search_form_title = (string) $input['search_form_title'];
                if (isset($search_form_ids[$search_form_title])) $input['search_form_id'] = $search_form_ids[$search_form_title];
            }

            $response = LSD_Actions::instance()->execute($step['action'], $input, [
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
                'url' => $dry_run ? '' : $this->item_url($response),
                'directory_page' => !empty($input['directory_page']),
            ];

            if ($step['action'] === 'create_custom_field' && !empty($response['success']))
            {
                $original_slug = isset($input['slug']) ? (string) $input['slug'] : '';
                $prepared_input = isset($response['meta']['input']) && is_array($response['meta']['input']) ? $response['meta']['input'] : [];
                $prepared_slug = isset($prepared_input['slug']) ? (string) $prepared_input['slug'] : '';
                if ($prepared_slug === '' && isset($response['data']['slug'])) $prepared_slug = (string) $response['data']['slug'];
                if ($original_slug !== '' && $prepared_slug !== '' && $original_slug !== $prepared_slug) $field_slugs[$original_slug] = $prepared_slug;
            }

            if ($step['action'] === 'create_search_form' && !empty($response['success']) && !empty($response['data']['post_id']))
            {
                $search_form_ids[(string) ($input['title'] ?? '')] = (int) $response['data']['post_id'];
            }

            if ($step['action'] === 'create_directory_shortcode' && !empty($response['success']) && !empty($response['data']['post_id']))
            {
                $shortcode_ids[(string) ($input['title'] ?? '')] = (int) $response['data']['post_id'];
            }

            if (!$dry_run && empty($response['success'])) break;
        }

        return [
            'definition' => $definition,
            'items' => $items,
            'summary' => $summary,
            'recommendations' => $this->recommendations($definition, $options),
            'next_steps' => $definition['next_steps'] ?? [],
        ];
    }

    protected function map_field_filters(array $fields, array $field_slugs): array
    {
        foreach ($fields as &$row)
        {
            if (!is_array($row) || !isset($row['filters']) || !is_array($row['filters'])) continue;

            foreach ($row['filters'] as $key => $filter)
            {
                $key = (string) $key;
                if (strpos($key, 'att-') !== 0) continue;

                $slug = substr($key, 4);
                if (!isset($field_slugs[$slug])) continue;

                $new_key = 'att-' . $field_slugs[$slug];
                if (is_array($filter)) $filter['key'] = $new_key;
                $row['filters'][$new_key] = $filter;
                unset($row['filters'][$key]);
            }
        }
        unset($row);

        return $fields;
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
            'shortcodes' => ['action' => 'create_directory_shortcode', 'label' => esc_html__('Directory Shortcode', 'listdom')],
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

    protected function item_url(array $response): string
    {
        $data = isset($response['data']) && is_array($response['data']) ? $response['data'] : [];
        if (!empty($data['permalink'])) return (string) $data['permalink'];

        if (!empty($data['post_id'])) return (string) get_edit_post_link((int) $data['post_id'], 'raw');
        if (!empty($data['term_id']))
        {
            $taxonomy = isset($data['taxonomy']) ? (string) $data['taxonomy'] : LSD_Base::TAX_ATTRIBUTE;
            $url = get_edit_term_link((int) $data['term_id'], $taxonomy);
            return is_wp_error($url) ? '' : (string) $url;
        }

        return '';
    }

    protected function configure_definition(array $definition, array $options): array
    {
        if (!count($definition) || empty($options['wizard'])) return $definition;

        $generate = isset($definition['generate']) && is_array($definition['generate']) ? $definition['generate'] : [];
        $location = !empty($options['location']);
        $pricing = !empty($options['pricing']);

        if (!$location)
        {
            unset($generate['locations']);

            if (isset($generate['custom_fields']) && is_array($generate['custom_fields']))
            {
                $generate['custom_fields'] = array_values(array_filter($generate['custom_fields'], function ($field)
                {
                    $slug = isset($field['slug']) ? (string) $field['slug'] : '';
                    return !in_array($slug, ['neighborhood', 'service-area'], true);
                }));
            }

            if (isset($generate['demo_listings']) && is_array($generate['demo_listings']))
            {
                foreach ($generate['demo_listings'] as &$listing)
                {
                    if (!is_array($listing)) continue;
                    $listing['address'] = '';
                    $listing['latitude'] = '';
                    $listing['longitude'] = '';
                    $listing['location'] = false;
                    if (isset($listing['attributes']) && is_array($listing['attributes']))
                    {
                        unset($listing['attributes']['neighborhood'], $listing['attributes']['service-area']);
                    }
                }
                unset($listing);
            }
        }

        if (!$pricing)
        {
            if (isset($generate['custom_fields']) && is_array($generate['custom_fields']))
            {
                $generate['custom_fields'] = array_values(array_filter($generate['custom_fields'], function ($field)
                {
                    $slug = isset($field['slug']) ? (string) $field['slug'] : '';
                    return strpos($slug, 'pric') === false;
                }));
            }

            if (isset($generate['search_forms']) && is_array($generate['search_forms']))
            {
                foreach ($generate['search_forms'] as &$form)
                {
                    if (!isset($form['fields']) || !is_array($form['fields'])) continue;
                    foreach ($form['fields'] as &$row)
                    {
                        if (!isset($row['filters']) || !is_array($row['filters'])) continue;
                        foreach ($row['filters'] as $key => $filter)
                        {
                            if (strpos((string) $key, 'pric') !== false) unset($row['filters'][$key]);
                        }
                    }
                    unset($row);
                }
                unset($form);
            }

            if (isset($generate['demo_listings']) && is_array($generate['demo_listings']))
            {
                foreach ($generate['demo_listings'] as &$listing)
                {
                    if (!isset($listing['attributes']) || !is_array($listing['attributes'])) continue;
                    foreach (array_keys($listing['attributes']) as $slug)
                    {
                        if (strpos((string) $slug, 'pric') !== false) unset($listing['attributes'][$slug]);
                    }
                }
                unset($listing);
            }
        }

        if (!$location && isset($generate['search_forms']) && is_array($generate['search_forms']))
        {
            foreach ($generate['search_forms'] as &$form)
            {
                if (!isset($form['fields']) || !is_array($form['fields'])) continue;
                foreach ($form['fields'] as &$row)
                {
                    if (isset($row['filters'][LSD_Base::TAX_LOCATION])) unset($row['filters'][LSD_Base::TAX_LOCATION]);
                }
                unset($row);
            }
            unset($form);
        }

        if (LSD_Base::isLite() && isset($generate['pages']) && is_array($generate['pages']))
        {
            $generate['pages'] = array_values(array_filter($generate['pages'], function ($page)
            {
                $content = isset($page['content']) ? (string) $page['content'] : '';
                return !preg_match('/\[listdom-add-listing(?:\s|\]|\/)/i', $content);
            }));
        }

        if (empty($options['frontend_dashboard']) && isset($generate['pages']) && is_array($generate['pages']))
        {
            $generate['pages'] = array_values(array_filter($generate['pages'], function ($page)
            {
                if (!is_array($page)) return false;

                $option_path = isset($page['option_path']) ? (string) $page['option_path'] : '';
                if (in_array($option_path, ['submission_page', 'add_listing_page'], true)) return false;

                $content = isset($page['content']) ? (string) $page['content'] : '';
                return !preg_match('/\[listdom-(?:dashboard|add-listing)(?:\s|\]|\/)/i', $content);
            }));
        }

        $definition['generate'] = $generate;
        return $definition;
    }

    protected function recommendations(array $definition, array $options): array
    {
        $recommendations = isset($definition['recommendations']) && is_array($definition['recommendations']) ? $definition['recommendations'] : [];
        if (empty($options['wizard'])) return $recommendations;

        $addon_options = isset($recommendations['addon_options']) && is_array($recommendations['addon_options']) ? $recommendations['addon_options'] : [];
        $addons = isset($recommendations['addons']) && is_array($recommendations['addons']) ? $recommendations['addons'] : [];
        $addons = array_values(array_filter($addons, static function ($addon) use ($addon_options, $options)
        {
            return !isset($addon_options[$addon]) || !empty($options[$addon_options[$addon]]);
        }));

        foreach (['reviews' => 'Reviews', 'booking' => 'Booking', 'memberships' => 'Memberships'] as $option => $addon)
        {
            if (!empty($options[$option])) $addons[] = $addon;
        }

        $recommendations['addons'] = array_values(array_unique($addons));
        unset($recommendations['addon_options']);

        return $recommendations;
    }

    protected function apply_configuration(array $options): array
    {
        if (empty($options['wizard'])) return [];

        $frontend_dashboard = !empty($options['frontend_dashboard']);

        $settings = LSD_Options::settings();
        $components = isset($settings['components']) && is_array($settings['components']) ? $settings['components'] : [];
        $components['map'] = !empty($options['location']) ? 1 : 0;
        $components['pricing'] = !empty($options['pricing']) ? 1 : 0;
        $components['work_hours'] = !empty($options['work_hours']) ? 1 : 0;

        $ai_visibility = isset($settings['ai_visibility']) && is_array($settings['ai_visibility']) ? $settings['ai_visibility'] : [];
        $ai_visibility['structured_data'] = !empty($options['ai_visibility']) ? 1 : 0;
        $ai_visibility['public_feed'] = !empty($options['ai_visibility']) ? 1 : 0;
        $ai_visibility['llms_txt'] = !empty($options['ai_visibility']) ? 1 : 0;
        $ai_visibility['robots_txt'] = !empty($options['ai_visibility']) ? 1 : 0;
        $ai_visibility['html_links'] = !empty($options['ai_visibility']) ? 1 : 0;

        $add_listing_page_status = $frontend_dashboard && !LSD_Base::isLite();
        $updates = [
            'components' => $components,
            'help_improve_listdom' => !empty($options['usage_data']) ? 1 : 0,
            'add_listing_page_status' => $add_listing_page_status ? 1 : 0,
            'ai_visibility' => $ai_visibility,
        ];
        if (!$frontend_dashboard)
        {
            $updates['submission_page'] = 0;
            $updates['add_listing_page'] = 0;
        }

        LSD_Options::merge('lsd_settings', $updates);

        return [
            'components' => $components,
            'ai_visibility' => $ai_visibility,
            'frontend_dashboard' => $frontend_dashboard,
            'reviews' => !empty($options['reviews']),
            'booking' => !empty($options['booking']),
            'memberships' => !empty($options['memberships']),
        ];
    }

}
