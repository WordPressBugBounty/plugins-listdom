<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom API Routes Class.
 *
 * @class LSD_API_Routes
 * @version    1.0.0
 */
class LSD_API_Routes extends LSD_API
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        add_action('rest_api_init', [$this, 'register']);
    }

    public function register()
    {
        // Validation Library
        $validation = new LSD_API_Validation();

        // I18n Controller
        $i18n = new LSD_API_Controllers_I18n();

        register_rest_route($this->namespace, 'languages', [
            'methods' => 'GET',
            'callback' => [$i18n, 'languages'],
            'permission_callback' => [$i18n, 'guest'],
        ]);

        // Register Controller
        $register = new LSD_API_Controllers_Register();

        register_rest_route($this->namespace, 'register', [
            'methods' => 'POST',
            'callback' => [$register, 'perform'],
            'permission_callback' => [$register, 'guest'],
        ]);

        // Login Controller
        $login = new LSD_API_Controllers_Login();

        register_rest_route($this->namespace, 'login', [
            'methods' => 'POST',
            'callback' => [$login, 'perform'],
            'permission_callback' => [$login, 'guest'],
        ]);

        register_rest_route($this->namespace, 'login/key', [
            'methods' => 'POST',
            'callback' => [$login, 'key'],
            'permission_callback' => [$login, 'permission'],
        ]);

        register_rest_route($this->namespace, 'login/redirect/(?P<key>[a-zA-Z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [$login, 'redirect'],
            'permission_callback' => '__return_true',
        ]);

        // Password Controller
        $password = new LSD_API_Controllers_Password();

        register_rest_route($this->namespace, 'forgot', [
            'methods' => 'POST',
            'callback' => [$password, 'forgot'],
            'permission_callback' => [$password, 'guest'],
        ]);

        register_rest_route($this->namespace, 'password', [
            'methods' => 'POST',
            'callback' => [$password, 'update'],
            'permission_callback' => [$password, 'permission'],
        ]);

        // Logout Controller
        $logout = new LSD_API_Controllers_Logout();

        register_rest_route($this->namespace, 'logout', [
            'methods' => 'POST',
            'callback' => [$logout, 'perform'],
            'permission_callback' => [$logout, 'permission'],
        ]);

        // Profile Controller
        $profile = new LSD_API_Controllers_Profile();

        // Get Profile
        register_rest_route($this->namespace, 'profile', [
            'methods' => 'GET',
            'callback' => [$profile, 'get'],
            'permission_callback' => [$profile, 'permission'],
        ]);

        // Update Profile
        register_rest_route($this->namespace, 'profile', [
            'methods' => 'PUT',
            'callback' => [$profile, 'update'],
            'permission_callback' => [$profile, 'permission'],
        ]);

        // Taxonomies Controller
        $taxonomies = new LSD_API_Controllers_Taxonomies();

        // Get Taxonomies
        register_rest_route($this->namespace, 'taxonomies', [
            'methods' => 'GET',
            'callback' => [$taxonomies, 'get'],
            'permission_callback' => [$taxonomies, 'guest'],
        ]);

        // Get Terms
        register_rest_route($this->namespace, 'taxonomies/(?P<taxonomy>[a-zA-Z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [$taxonomies, 'terms'],
            'permission_callback' => [$taxonomies, 'guest'],
        ]);

        // Images Controller
        $images = new LSD_API_Controllers_Images();

        // Upload Image
        register_rest_route($this->namespace, 'images', [
            'methods' => 'POST',
            'callback' => [$images, 'upload'],
            'permission_callback' => [$taxonomies, 'permission'],
        ]);

        // Get Image
        register_rest_route($this->namespace, 'images/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$images, 'get'],
            'permission_callback' => [$taxonomies, 'guest'],
            'args' => [
                'id' => [
                    'validate_callback' => [$validation, 'numeric'],
                ],
            ],
        ]);

        // Search Modules Controller
        $sm = new LSD_API_Controllers_SearchModules();

        // Get All Search Modules
        register_rest_route($this->namespace, 'search-modules', [
            'methods' => 'GET',
            'callback' => [$sm, 'perform'],
            'permission_callback' => [$sm, 'guest'],
        ]);

        // Get Search Module
        register_rest_route($this->namespace, 'search-modules/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$sm, 'get'],
            'permission_callback' => [$sm, 'guest'],
            'args' => [
                'id' => [
                    'validate_callback' => [$validation, 'numeric'],
                ],
            ],
        ]);

        // Listings Controller
        $listings = new LSD_API_Controllers_Listings();

        // Create Listing
        register_rest_route($this->namespace, 'listings', [
            'methods' => 'POST',
            'callback' => [$listings, 'create'],
            'permission_callback' => [$listings, 'permission'],
        ]);

        // Edit Listing
        register_rest_route($this->namespace, 'listings', [
            'methods' => 'PUT',
            'callback' => [$listings, 'edit'],
            'permission_callback' => [$listings, 'permission'],
        ]);

        // Trash Listing
        register_rest_route($this->namespace, 'listings/(?P<id>\d+)/trash', [
            'methods' => 'DELETE',
            'callback' => [$listings, 'trash'],
            'permission_callback' => [$listings, 'permission'],
            'args' => [
                'id' => [
                    'validate_callback' => [$validation, 'numeric'],
                ],
            ],
        ]);

        // Delete Listing
        register_rest_route($this->namespace, 'listings/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$listings, 'delete'],
            'permission_callback' => [$listings, 'permission'],
            'args' => [
                'id' => [
                    'validate_callback' => [$validation, 'numeric'],
                ],
            ],
        ]);

        // Get Listing
        register_rest_route($this->namespace, 'listings/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$listings, 'get'],
            'permission_callback' => [$listings, 'guest'],
            'args' => [
                'id' => [
                    'validate_callback' => [$validation, 'numeric'],
                ],
            ],
        ]);

        // Contact Listing
        register_rest_route($this->namespace, 'listings/(?P<id>\d+)/contact', [
            'methods' => 'POST',
            'callback' => [$listings, 'contact'],
            'permission_callback' => [$listings, 'guest'],
            'args' => [
                'id' => [
                    'validate_callback' => [$validation, 'numeric'],
                ],
            ],
        ]);

        // Report Abuse
        register_rest_route($this->namespace, 'listings/(?P<id>\d+)/abuse', [
            'methods' => 'POST',
            'callback' => [$listings, 'abuse'],
            'permission_callback' => [$listings, 'guest'],
            'args' => [
                'id' => [
                    'validate_callback' => [$validation, 'numeric'],
                ],
            ],
        ]);

        // Listing Fields
        register_rest_route($this->namespace, 'listings/fields', [
            'methods' => 'GET',
            'callback' => [$listings, 'fields'],
            'permission_callback' => [$listings, 'guest'],
        ]);

        // Push Controller
        $push = new LSD_API_Controllers_Push();

        // Push Listing
        register_rest_route($this->namespace, 'listings/push', [
            'methods' => 'POST',
            'callback' => [$push, 'listing'],
            'permission_callback' => [$push, 'guest'],
        ]);

        // Map Controller
        $map = new LSD_API_Controllers_Map();

        // Listing Map
        register_rest_route($this->namespace, 'listings/(?P<id>\d+)/map', [
            'methods' => 'GET',
            'callback' => [$map, 'single'],
            'permission_callback' => [$map, 'guest'],
            'args' => [
                'id' => [
                    'validate_callback' => [$validation, 'numeric'],
                ],
            ],
        ]);

        // Upsert Map
        register_rest_route($this->namespace, 'listings/(?P<id>\d+)/map-upsert', [
            'methods' => 'GET',
            'callback' => [$map, 'upsert'],
            'permission_callback' => [$map, 'permission'],
            'args' => [
                'id' => [
                    'validate_callback' => [$validation, 'numeric'],
                ],
            ],
        ]);

        // Listings Map
        register_rest_route($this->namespace, 'search/map', [
            'methods' => 'GET',
            'callback' => [$map, 'search'],
            'permission_callback' => [$map, 'guest'],
        ]);

        // Search Controller
        $search = new LSD_API_Controllers_Search();

        // Current User Listings
        register_rest_route($this->namespace, 'my-listings', [
            'methods' => 'GET',
            'callback' => [$search, 'my'],
            'permission_callback' => [$search, 'permission'],
        ]);

        // Search Listings
        register_rest_route($this->namespace, 'search', [
            'methods' => 'GET',
            'callback' => [$search, 'search'],
            'permission_callback' => [$search, 'guest'],
        ]);

        // Addons Controller
        $addons = new LSD_API_Controllers_Addons();

        // Search Listings
        register_rest_route($this->namespace, 'addons', [
            'methods' => 'GET',
            'callback' => [$addons, 'get'],
            'permission_callback' => [$addons, 'guest'],
        ]);
    }
}
