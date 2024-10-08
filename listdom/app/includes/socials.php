<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Socials Class.
 *
 * @class LSD_Socials
 * @version	1.0.0
 */
class LSD_Socials extends LSD_Base
{
    public $path;
    public $key;
    public $label;
    public $option;

    /**
	 * Constructor method
	 */
	public function __construct()
    {
        parent::__construct();

        $this->path = $this->get_listdom_path().'/app/includes/socials/';
	}
    
    public function init()
    {
        // Profile
        add_action('lsd_social_networks_profile_form', [$this, 'profile_form']);
        add_action('lsd_social_networks_profile_save', [$this, 'profile_save']);

        // Listing
        add_action('lsd_social_networks_listing_form', [$this, 'listing_form'], 10, 2);
        add_action('lsd_listing_saved', [$this, 'listing_save'], 10, 2);
    }

    /**
     * @param string $network
     * @param array|null $options
     * @return bool|object
     */
    public function get(string $network, array $options = null)
    {
        $class = 'LSD_Socials_'.ucfirst($network);

        // Class doesn't exists
        if (!class_exists($class)) return false;

        // Return the object
        $obj = new $class();
        $obj->option = $options;

        return $obj;
    }

    public function key()
    {
        return $this->key;
    }

    public function label()
    {
        return $this->label;
    }

    public function option($name)
    {
        return $this->option[$name] ?? null;
    }

    public function share($post_id): string
    {
        return '';
    }

    public function icon($url): string
    {
        return '';
    }

    public function owner($value): string
    {
        return $this->icon($value);
    }

    public function listing($value): string
    {
        return $this->icon($value);
    }

    public function get_input_type(): string
    {
        return 'url';
    }

    public function profile_form($user)
    {
        $networks = LSD_Options::socials();
        foreach($networks as $network => $values)
        {
            $obj = $this->get($network, $values);

            // Social Network is not Enabled
            if(!$obj || !$obj->option('profile')) continue;

            echo '<tr>';
            echo '<th><label for="lsd_'.$obj->key().'">'.$obj->label().'</label></th>';
            echo '<td><input type="'.esc_attr($obj->get_input_type()).'" name="lsd_'.$obj->key().'" id="lsd_'.$obj->key().'" value="'.esc_attr(get_the_author_meta('lsd_'.$obj->key(), $user->ID)).'" class="regular-text ltr"></td>';
            echo '</tr>';
        }
    }

    public function profile_save($user_id)
    {
        $networks = LSD_Options::socials();
        foreach($networks as $network => $values)
        {
            $obj = $this->get($network, $values);

            // Social Network is not Enabled
            if(!$obj || !$obj->option('profile')) continue;

            // Save
            update_user_meta($user_id, 'lsd_'.$obj->key(), sanitize_text_field($_POST['lsd_'.$obj->key()]));
        }
    }

    /**
     * @param WP_Post $listing
     * @param LSD_Shortcodes_Dashboard $dashboard
     */
    public function listing_form($listing, $dashboard = null)
    {
        $networks = LSD_Options::socials();
        foreach($networks as $network => $values)
        {
            $obj = $this->get($network, $values);

            // Social Network is not Enabled
            if(!$obj || !$obj->option('listing')) continue;

            $type = $obj->get_input_type();
            $value = get_post_meta($listing->ID, 'lsd_'.$obj->key(), true);

            echo '<div class="lsd-form-row">
                <div class="lsd-col-3 lsd-text-right">
                    <label for="lsd_'.$obj->key().'">'.$obj->label().($dashboard && $dashboard->is_required($obj->key()) ? ' '.LSD_Base::REQ_HTML : '').'</label>
                </div>
                <div class="lsd-col-9">
                    <input type="'.esc_attr($type).'" name="lsd[sc]['.$obj->key().']" id="lsd_'.$obj->key().'" placeholder="" value="'.($type === 'url' ? esc_url($value) : esc_attr($value)).'">
                </div>
            </div>';
        }
    }

    /**
     * @param WP_Post $listing
     * @param array $data
     */
    public function listing_save($listing, $data)
    {
        // Social Data
        $data = (isset($data['sc']) and is_array($data['sc'])) ? $data['sc'] : [];

        $networks = LSD_Options::socials();
        foreach($networks as $network => $values)
        {
            $obj = $this->get($network, $values);

            // Social Network is not Enabled
            if(!$obj || !$obj->option('listing')) continue;

            // URL is not Set
            if(!isset($data[$obj->key()])) continue;

            // Save
            update_post_meta($listing->ID, 'lsd_'.$obj->key(), sanitize_text_field($data[$obj->key()]));
        }
    }
}
