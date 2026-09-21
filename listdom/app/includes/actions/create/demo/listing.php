<?php

class LSD_Actions_Create_Demo_Listing extends LSD_Actions_Action
{
    public function get_id(): string
    {
        return 'create_demo_listing';
    }

    public function get_label(): string
    {
        return esc_html__('Create Demo Listing', 'listdom');
    }

    public function get_capability(): string
    {
        return 'edit_posts';
    }

    public function get_schema(): array
    {
        return [
            'title' => ['type' => 'string', 'default' => 'Sample Business'],
            'content' => ['type' => 'string', 'default' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'],
            'status' => ['type' => 'string', 'default' => 'publish'],
            'category_name' => ['type' => 'string', 'default' => 'General'],
            'category_parent' => ['type' => 'int', 'default' => 0],
            'location_name' => ['type' => 'string', 'default' => ''],
            'address' => ['type' => 'string', 'default' => 'New York, NY, USA'],
            'latitude' => ['type' => 'string', 'default' => '40.712776'],
            'longitude' => ['type' => 'string', 'default' => '-74.005974'],
            'location' => ['type' => 'bool', 'default' => true],
            'email' => ['type' => 'email', 'default' => 'sample@example.com'],
            'phone' => ['type' => 'string', 'default' => '+1234567890'],
            'website' => ['type' => 'url', 'default' => 'https://example.com'],
            'attributes' => ['type' => 'array', 'default' => []],
            'overwrite_existing' => ['type' => 'bool', 'default' => false],
            'reuse_existing' => ['type' => 'bool', 'default' => false],
        ];
    }

    public function validate(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        [$input, $errors] = $this->validate_schema($input);
        if (!in_array($input['status'], ['publish', 'draft', 'pending'], true)) $errors[] = esc_html__('Unsupported listing status.', 'listdom');

        if (count($errors))
        {
            return $this->failure('validation_failed', esc_html__('The demo listing request is invalid.', 'listdom'), $errors);
        }

        $existing = LSD_Base::get_post_by_title($input['title'], LSD_Base::PTYPE_LISTING);
        $warnings = [];
        if ($existing instanceof WP_Post && $existing->post_status !== 'trash')
        {
            if ($input['reuse_existing'])
            {
                $warnings[] = esc_html__('An existing listing will be reused.', 'listdom');
                $input['existing_post_id'] = (int) $existing->ID;
                $input['reuse_existing_mode'] = true;
            }
            else if (!$input['overwrite_existing'])
            {
                return $this->failure('already_exists', esc_html__('A listing with this title already exists.', 'listdom'), [], [
                    'post_id' => (int) $existing->ID,
                    'operation' => 'blocked',
                ]);
            }
            else
            {
                $warnings[] = esc_html__('An existing listing will be updated because overwrite mode is enabled.', 'listdom');
                $input['existing_post_id'] = (int) $existing->ID;
            }
        }

        return $this->validated($input, $warnings, [
            'operation' => !empty($input['reuse_existing_mode']) ? 'reuse' : (isset($input['existing_post_id']) ? 'update' : 'create'),
            'title' => $input['title'],
            'category_name' => $input['category_name'],
        ]);
    }

    public function execute(array $input, LSD_Action_Context $context): LSD_Action_Result
    {
        $categories = get_terms([
            'taxonomy' => LSD_Base::TAX_CATEGORY,
            'name' => $input['category_name'],
            'parent' => max(0, (int) $input['category_parent']),
            'hide_empty' => false,
            'number' => 1,
        ]);
        $category = !is_wp_error($categories) && is_array($categories) && count($categories) ? $categories[0] : null;
        if (!$category)
        {
            $insert = wp_insert_term($input['category_name'], LSD_Base::TAX_CATEGORY, [
                'parent' => max(0, (int) $input['category_parent']),
            ]);
            if (is_wp_error($insert))
            {
                return $this->failure('category_failed', $insert->get_error_message());
            }

            $category = get_term((int) $insert['term_id'], LSD_Base::TAX_CATEGORY);
        }

        if (!$category || is_wp_error($category))
        {
            return $this->failure('category_failed', esc_html__('The demo listing category could not be prepared.', 'listdom'));
        }

        $location = null;
        if ($input['location'] && trim($input['location_name']) !== '')
        {
            $locations = get_terms([
                'taxonomy' => LSD_Base::TAX_LOCATION,
                'name' => $input['location_name'],
                'parent' => 0,
                'hide_empty' => false,
                'number' => 1,
            ]);
            $location = !is_wp_error($locations) && is_array($locations) && count($locations) ? $locations[0] : null;
        }

        $post_id = (int) ($input['existing_post_id'] ?? 0);
        if (!empty($input['reuse_existing_mode']) && $post_id > 0)
        {
            if (LSD_Base::is_post($post_id, 'trash')) LSD_Base::untrash_post($post_id);

            return $this->success(esc_html__('Existing demo listing reused.', 'listdom'), [
                'post_id' => (int) $post_id,
                'permalink' => get_permalink($post_id),
                'operation' => 'reuse',
            ]);
        }

        $creating = $post_id <= 0;
        $post_args = [
            'post_title' => $input['title'],
            'post_content' => $input['content'],
            'post_type' => LSD_Base::PTYPE_LISTING,
            'post_status' => $input['status'],
        ];

        if ($creating) $post_id = wp_insert_post($post_args, true);
        else
        {
            $post_args['ID'] = $post_id;
            if (LSD_Base::is_post($post_id, 'trash')) LSD_Base::untrash_post($post_id);
            $post_id = wp_update_post($post_args, true);
        }

        if (is_wp_error($post_id))
        {
            return $this->failure($creating ? 'insert_failed' : 'update_failed', $post_id->get_error_message());
        }

        $entity = new LSD_Entity_Listing((int) $post_id);
        $listing_data = [
            'listing_category' => (int) $category->term_id,
            'email' => $input['email'],
            'phone' => $input['phone'],
            'website' => $input['website'],
            'attributes' => $input['attributes'],
            'remark' => 'Demo listing generated by the Listdom action layer.',
        ];
        if ($input['location'])
        {
            $listing_data['address'] = $input['address'];
            $listing_data['latitude'] = $input['latitude'];
            $listing_data['longitude'] = $input['longitude'];
        }
        else $listing_data['_lsd_skip_location'] = true;
        $entity->save($listing_data, false);
        if ($location instanceof WP_Term) wp_set_object_terms((int) $post_id, [(int) $location->term_id], LSD_Base::TAX_LOCATION);
        $this->mark_post((int) $post_id, $context);
        if ($creating && $context->source() === 'blueprint')
        {
            update_post_meta((int) $post_id, 'lsd_blueprint_owned', '1');
        }

        return $this->success(
            $creating ? esc_html__('Demo listing created successfully.', 'listdom') : esc_html__('Demo listing updated successfully.', 'listdom'),
            [
                'post_id' => (int) $post_id,
                'permalink' => get_permalink($post_id),
                'operation' => $creating ? 'create' : 'update',
            ]
        );
    }
}
