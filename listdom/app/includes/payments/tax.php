<?php

class LSD_Payments_Tax extends LSD_Base
{
    protected $settings;
    protected $countries;
    protected $states;
    protected $geo_data;

    public function __construct()
    {
        $payments = LSD_Options::payments();
        $this->settings = isset($payments['taxes']) && is_array($payments['taxes']) ? $payments['taxes'] : [];

        $this->geo_data = $this->load_geo_data();
        $this->countries = $this->countries_list();
        $this->states = $this->states_list();

        $this->settings['locations'] = $this->sanitize_locations($this->settings['locations'] ?? []);

        $taxonomy_locations = $this->taxonomy_locations();
        if (count($taxonomy_locations)) $this->settings['locations'] = $taxonomy_locations;
    }

    public function enabled(): bool
    {
        return !empty($this->settings['enable']);
    }

    public function prices_include_tax(): bool
    {
        return !empty($this->settings['prices_include_tax']);
    }

    public function rate(array $location = []): float
    {
        $entries = $this->rate_entries($location);
        if (!count($entries))
        {
            $fallback = $this->settings['rate'] ?? 0;
            return max(0.0, (float) $fallback);
        }

        return array_reduce($entries, function ($carry, $entry)
        {
            $rate = isset($entry['rate']) ? (float) $entry['rate'] : 0.0;
            return $carry + max(0.0, $rate);
        }, 0.0);
    }

    public function label(): string
    {
        $label = isset($this->settings['label']) ? trim((string) $this->settings['label']) : '';
        return $label !== '' ? $label : esc_html__('Tax', 'listdom');
    }

    public function calculate(float $taxable_subtotal, float $discount = 0.0, float $cart_subtotal = 0.0, array $location = []): float
    {
        if (!$this->enabled()) return 0.0;

        $items = $this->calculate_items($taxable_subtotal, $discount, $cart_subtotal, $location);

        return round(array_reduce($items, function ($carry, $item)
        {
            $amount = isset($item['amount']) ? (float) $item['amount'] : 0.0;
            return $carry + $amount;
        }, 0.0), 2);
    }

    public function calculate_items(float $taxable_subtotal, float $discount = 0.0, float $cart_subtotal = 0.0, array $location = []): array
    {
        if (!$this->enabled()) return [];

        $entries = $this->rate_entries($location);
        if (!count($entries))
        {
            $fallback = $this->settings['rate'] ?? 0;
            if ($fallback > 0) $entries[] = [
                'rate' => (float) $fallback,
                'label' => $this->label(),
            ];
        }

        $taxable_subtotal = max($taxable_subtotal, 0.0);
        $cart_subtotal = max($cart_subtotal, 0.0);
        $discount = max($discount, 0.0);

        if ($taxable_subtotal <= 0 || !count($entries)) return [];

        if ($cart_subtotal > 0 && $discount > 0)
        {
            $discount_share = $taxable_subtotal / $cart_subtotal * $discount;
            $discount_share = min($discount_share, $taxable_subtotal);
            $taxable_subtotal -= $discount_share;
        }

        if ($taxable_subtotal <= 0) return [];

        $total_rate = array_reduce($entries, function ($carry, $entry)
        {
            $rate = isset($entry['rate']) ? (float) $entry['rate'] : 0.0;
            return $carry + max(0.0, $rate);
        }, 0.0);

        $items = [];
        foreach ($entries as $entry)
        {
            $rate = isset($entry['rate']) ? (float) $entry['rate'] : 0.0;
            $rate = max(0.0, $rate);
            if ($rate <= 0) continue;

            if ($this->prices_include_tax())
            {
                $divider = 100 + $total_rate;
                if ($divider <= 0) continue;

                $amount = $taxable_subtotal * ($rate / $divider);
            }
            else
            {
                $amount = $taxable_subtotal * ($rate / 100);
            }

            $label = isset($entry['label']) && trim((string) $entry['label']) !== '' ? (string) $entry['label'] : $this->label();

            $items[] = [
                'rate' => $rate,
                'amount' => round($amount, 2),
                'label' => $label,
            ];
        }

        return $items;
    }

    public function apply_to_total(float $post_discount_total, float $tax_amount): float
    {
        $post_discount_total = max($post_discount_total, 0.0);
        $tax_amount = max($tax_amount, 0.0);

        if (!$this->enabled() || $this->prices_include_tax()) return $post_discount_total;

        return round($post_discount_total + $tax_amount, 2);
    }

    public function get_countries(): array
    {
        return $this->countries;
    }

    public function get_states(string $country): array
    {
        $country = $this->normalize_country($country);
        return $this->states[$country] ?? [];
    }

    public function get_states_list(): array
    {
        return $this->states;
    }

    public function default_location(): array
    {
        $location = $this->settings['locations'][0] ?? [];
        if (!is_array($location)) return [];

        return [
            'country' => $location['country'] ?? '',
            'state' => $location['state'] ?? '',
        ];
    }

    protected function match_location_rate(array $location): ?float
    {
        $country = $this->normalize_country($location['country'] ?? '');
        $state = $this->normalize_state($location['state'] ?? '');

        $locations = $this->settings['locations'];
        if (!is_array($locations) || !count($locations)) return null;

        $country_match = [];
        foreach ($locations as $loc)
        {
            $loc_country = $this->normalize_country($loc['country'] ?? '');
            $loc_state = $this->normalize_state($loc['state'] ?? '');

            if ($country === '' || $loc_country === '') continue;

            if ($loc_country === $country && $loc_state !== '' && $loc_state === $state)
            {
                $country_match[] = isset($loc['rate']) ? (float) $loc['rate'] : null;
            }

            if ($loc_country === $country && $loc_state === '')
            {
                $country_match[] = isset($loc['rate']) ? (float) $loc['rate'] : null;
            }
        }

        $country_match = array_filter($country_match, static function ($rate)
        {
            return $rate !== null;
        });

        if (!count($country_match))
        {
            return null;
        }

        return array_sum(array_map('floatval', $country_match));
    }

    public function rates(array $location = []): array
    {
        $entries = $this->rate_entries($location);
        if (!count($entries)) return [];

        $rates = [];
        foreach ($entries as $entry)
        {
            $rates[] = max(0.0, (float) ($entry['rate'] ?? 0));
        }

        return $rates;
    }

    public function rate_entries(array $location = []): array
    {
        $country = $this->normalize_country($location['country'] ?? '');
        $state = $this->normalize_state($location['state'] ?? '');

        $entries = [];

        // Prefer taxonomy-defined rates with labels (term names)
        if (taxonomy_exists(LSD_Base::TAX_TAX))
        {
            $terms = get_terms([
                'taxonomy' => LSD_Base::TAX_TAX,
                'hide_empty' => false,
            ]);

            if (is_array($terms) && count($terms))
            {
                if ($country === '')
                {
                    $first = reset($terms);
                    if ($first instanceof WP_Term)
                    {
                        $rate = (float) get_term_meta($first->term_id, LSD_Payments_Taxonomy::META_RATE, true);
                        if ($rate > 0)
                        {
                            $entries[] = [
                                'rate' => $rate,
                                'label' => $first->name,
                            ];
                        }
                    }
                    return $entries;
                }

                foreach ($terms as $term)
                {
                    if (!$term instanceof WP_Term) continue;

                    $loc_country = $this->normalize_country(get_term_meta($term->term_id, LSD_Payments_Taxonomy::META_COUNTRY, true));
                    $loc_state = $this->normalize_state(get_term_meta($term->term_id, LSD_Payments_Taxonomy::META_STATE, true));
                    $rate = (float) get_term_meta($term->term_id, LSD_Payments_Taxonomy::META_RATE, true);
                    if ($rate <= 0) continue;

                    if ($loc_country === '' || $loc_country !== $country) continue;

                    if ($loc_state !== '')
                    {
                        if ($loc_state === $state)
                        {
                            $entries[] = [
                                'rate' => $rate,
                                'label' => $term->name,
                            ];
                        }
                    }
                    else
                    {
                        $entries[] = [
                            'rate' => $rate,
                            'label' => $term->name,
                        ];
                    }
                }

                if (count($entries)) return $entries;
            }
        }

        // Fallback to settings-based locations (no per-rate labels available)
        $locations = $this->settings['locations'];
        if (!is_array($locations) || !count($locations)) return [];

        if ($country === '')
        {
            $first_rate = isset($locations[0]['rate']) ? (float) $locations[0]['rate'] : 0.0;
            if ($first_rate > 0)
            {
                return [[
                    'rate' => max(0.0, $first_rate),
                    'label' => $this->label(),
                ]];
            }
            return [];
        }

        foreach ($locations as $loc)
        {
            $loc_country = $this->normalize_country($loc['country'] ?? '');
            $loc_state = $this->normalize_state($loc['state'] ?? '');
            $rate = isset($loc['rate']) ? (float) $loc['rate'] : 0;
            if ($loc_country === '' || $loc_country !== $country) continue;
            if ($rate <= 0) continue;

            $label = $this->label();

            if ($loc_state !== '')
            {
                if ($loc_state === $state)
                {
                    $entries[] = [
                        'rate' => max(0.0, $rate),
                        'label' => $label,
                    ];
                }
            }
            else
            {
                $entries[] = [
                    'rate' => max(0.0, $rate),
                    'label' => $label,
                ];
            }
        }

        return $entries;
    }

    public function sanitize_locations($locations): array
    {
        if (!is_array($locations)) return [];

        $sanitized = [];
        foreach ($locations as $location)
        {
            if (!is_array($location)) continue;

            $country = $this->normalize_country($location['country'] ?? '');
            $state = $this->normalize_state($location['state'] ?? '');
            $rate = isset($location['rate']) ? (float) $location['rate'] : 0;

            if ($country === '' && $state === '') continue;

            $sanitized[] = [
                'country' => $country,
                'state' => $state,
                'rate' => max(0.0, $rate),
            ];
        }

        return array_values($sanitized);
    }

    protected function normalize_country(string $country): string
    {
        return strtoupper(trim($country));
    }

    protected function normalize_state(string $state): string
    {
        return strtoupper(trim($state));
    }

    protected function countries_list(): array
    {
        $countries = $this->geo_data['countries'] ?? [];
        if (!is_array($countries) || !count($countries)) $countries = $this->fallback_countries();

        return array_map('sanitize_text_field', $countries);
    }

    protected function states_list(): array
    {
        $states = $this->geo_data['states'] ?? [];
        if (!is_array($states) || !count($states)) $states = $this->fallback_states();

        $sanitized = [];
        foreach ($states as $country => $country_states)
        {
            if (!is_array($country_states)) continue;

            $country = $this->normalize_country($country);
            $sanitized[$country] = array_map('sanitize_text_field', $country_states);
        }

        return $sanitized;
    }

    protected function load_geo_data(): array
    {
        $file = LSD_ABSPATH . '/i18n/data/regions.json';
        if (file_exists($file))
        {
            if (function_exists('wp_json_file_decode')) $data = wp_json_file_decode($file, ['associative' => true]);
            else
            {
                $contents = file_get_contents($file);
                $data = $contents !== false ? json_decode($contents, true) : null;
            }
            if (is_array($data)) return $data;
        }

        return [
            'countries' => $this->fallback_countries(),
            'states' => $this->fallback_states(),
        ];
    }

    protected function fallback_countries(): array
    {
        return [
            'US' => 'United States',
            'CA' => 'Canada',
            'GB' => 'United Kingdom',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'FR' => 'France',
            'ES' => 'Spain',
            'IT' => 'Italy',
        ];
    }

    protected function fallback_states(): array
    {
        return [
            'US' => [
                'CA' => 'California',
                'NY' => 'New York',
            ],
            'CA' => [
                'ON' => 'Ontario',
                'QC' => 'Quebec',
            ],
            'AU' => [
                'NSW' => 'New South Wales',
                'VIC' => 'Victoria',
            ],
        ];
    }

    protected function taxonomy_locations(): array
    {
        if (!taxonomy_exists(LSD_Base::TAX_TAX) || !class_exists('LSD_Payments_Taxonomy')) return [];

        $terms = get_terms([
            'taxonomy' => LSD_Base::TAX_TAX,
            'hide_empty' => false,
        ]);

        if (!is_array($terms) || !count($terms)) return [];

        $locations = [];
        foreach ($terms as $term)
        {
            $country = get_term_meta($term->term_id, LSD_Payments_Taxonomy::META_COUNTRY, true);
            $state = get_term_meta($term->term_id, LSD_Payments_Taxonomy::META_STATE, true);
            $rate = get_term_meta($term->term_id, LSD_Payments_Taxonomy::META_RATE, true);

            $locations[] = [
                'country' => $country,
                'state' => $state,
                'rate' => $rate,
            ];
        }

        return $this->sanitize_locations($locations);
    }
}
