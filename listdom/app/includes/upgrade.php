<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Upgrade Class.
 *
 * @class LSD_Upgrade
 * @version    1.0.0
 */
class LSD_Upgrade extends LSD_Base
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
        // Plugin is not installed yet!
        if (!get_option('lsd_settings', 0)) return;

        // Run the Upgrade
        add_action('wp_loaded', [$this, 'upgrade']);
    }

    public function upgrade()
    {
        $version = get_option('lsd_version', '1.0.0');

        // It's updated to latest version
        if (version_compare($version, LSD_VERSION, '>=')) return;

        // Update to latest version
        update_option('lsd_version', LSD_VERSION);

        // Run the updates one by one
        if (version_compare($version, '1.2.1', '<')) $this->v121();
        if (version_compare($version, '1.7.0', '<')) $this->v170();
        if (version_compare($version, '1.8.0', '<')) $this->v180();
        if (version_compare($version, '1.9.0', '<')) $this->v190();
        if (version_compare($version, '2.0.0', '<')) $this->v200();
        if (version_compare($version, '2.2.0', '<')) $this->v220();
        if (version_compare($version, '2.4.0', '<')) $this->v240();
        if (version_compare($version, '2.5.1', '<')) $this->v251();
        if (version_compare($version, '3.2.0', '<')) $this->v320();
        if (version_compare($version, '3.3.0', '<')) $this->v330();
        if (version_compare($version, '3.3.1', '<')) $this->v331();
        if (version_compare($version, '3.4.0', '<')) $this->v340();
        if (version_compare($version, '3.5.0', '<')) $this->v350();
    }

    private function socials()
    {
        $socials = LSD_Options::parse_args(
            LSD_Options::socials(),
            LSD_Options::defaults('socials')
        );

        update_option('lsd_socials', $socials);
    }

    private function reset_validations()
    {
        // Database
        $db = new LSD_db();

        $db->q("DELETE FROM `#__options` WHERE `option_name` LIKE '%lsd_product_validation_%'");
    }

    private function roles()
    {
        remove_role('listdom_author');
        add_role('listdom_author', __('Listdom Author', 'listdom'), [
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => true,
            'delete_published_posts' => true,
            'edit_published_posts' => true,
            'edit_listings' => true,
            'delete_listings' => true,
            'edit_listing' => true,
            'upload_files' => true,
        ]);

        remove_role('listdom_publisher');
        add_role('listdom_publisher', __('Listdom Publisher', 'listdom'), [
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => true,
            'publish_posts' => true,
            'delete_published_posts' => true,
            'edit_published_posts' => true,
            'edit_listings' => true,
            'delete_listings' => true,
            'edit_listing' => true,
            'upload_files' => true,
        ]);
    }

    private function v121()
    {
        // Contact Notification
        $post_id = wp_insert_post([
            'post_type' => LSD_Base::PTYPE_NOTIFICATION,
            'post_status' => 'publish',
            'post_title' => 'New Contact By #name#!',
            'post_content' => '',
        ]);

        update_post_meta($post_id, 'lsd_hook', 'lsd_contact_owner');
        update_post_meta($post_id, 'lsd_original_to', 1);
        update_post_meta($post_id, 'lsd_to', '');
        update_post_meta($post_id, 'lsd_cc', '');
        update_post_meta($post_id, 'lsd_bcc', '');
        update_post_meta($post_id, 'lsd_content', 'Hi,

        Following contact received for #listing_link#.
        
        Name: #name#
        Email: #email#
        Phone: #phone#
        Message: #message#
        
        Regards,
        #site_name#');

        // New Listing Notification
        $post_id = wp_insert_post([
            'post_type' => LSD_Base::PTYPE_NOTIFICATION,
            'post_status' => 'draft',
            'post_title' => 'New Listing Added!',
            'post_content' => '',
        ]);

        update_post_meta($post_id, 'lsd_hook', 'lsd_new_listing');
        update_post_meta($post_id, 'lsd_original_to', 1);
        update_post_meta($post_id, 'lsd_to', '');
        update_post_meta($post_id, 'lsd_cc', '');
        update_post_meta($post_id, 'lsd_bcc', '');
        update_post_meta($post_id, 'lsd_content', 'Hi,

        Following listing gets added.
        #listing_link#
        
        Title: #listing_title#
        Status: #listing_status#
        Date: #listing_date#
        Owner Name: #owner_name#
        Owner Email: #owner_email#
        
        You can manage it using #admin_link# link.
        
        Regards,
        #site_name#');
    }

    private function v170()
    {
        // Taxonomies
        $taxonomies = [
            LSD_Base::TAX_CATEGORY,
            LSD_Base::TAX_FEATURE,
        ];

        // Add Attributes to Taxonomies
        if ($this->isPro()) $taxonomies[] = LSD_Base::TAX_ATTRIBUTE;

        // Icons to be Replaced
        $icons = [
            'fa fa-glass' => 'fas fa-glass-martini',
            'fa fa-envelope-o' => 'far fa-envelope',
            'fa fa-star-o' => 'far fa-star',
            'fa fa-trash-o' => 'far fa-trash-alt',
            'fa fa-file-o' => 'far fa-file',
            'fa fa-clock-o' => 'far fa-clock',
            'fa fa-arrow-circle-o-down' => 'far fa-arrow-alt-circle-down',
            'fa fa-arrow-circle-o-up' => 'far fa-arrow-alt-circle-up',
            'fa fa-play-circle-o' => 'far fa-play-circle',
            'fa fa-refresh' => 'fas fa-sync-alt',
            'fa fa-video-camera' => 'fas fa-video',
            'fa fa-picture-o' => 'far fa-image',
            'fa fa-pencil' => 'fas fa-pencil-alt',
            'fa fa-pencil-square-o' => 'fas fa-edit',
            'fa fa-share-square-o' => 'fas fa-share-square',
            'fa fa-check-square-o' => 'far fa-check-square',
            'fa fa-arrows' => 'fas fa-arrows-alt',
            'fa fa-times-circle-o' => 'far fa-times-circle',
            'fa fa-check-circle-o' => 'far fa-check-circle',
            'fa fa-arrows-v' => 'fas fa-arrows-alt-v',
            'fa fa-arrows-h' => 'fas fa-arrows-alt-h',
            'fa fa-bar-chart' => 'far fa-chart-bar',
            'fa fa-twitter-square' => 'fab fa-twitter-square',
            'fa fa-facebook-square' => 'fab fa-facebook-square',
            'fa fa-thumbs-o-up' => 'far fa-thumbs-up',
            'fa fa-thumbs-o-down' => 'far fa-thumbs-down',
            'fa fa-heart-o' => 'far fa-heart',
            'fa fa-sign-out' => 'fas fa-sign-out-alt',
            'fa fa-linkedin-square' => 'fab fa-linkedin',
            'fa fa-thumb-tack' => 'fas fa-thumbtack',
            'fa fa-external-link' => 'fas fa-external-link-alt',
            'fa fa-sign-in' => 'fas fa-sign-in-alt',
            'fa fa-github-square' => 'fab fa-github-square',
            'fa fa-lemon-o' => 'far fa-lemon',
            'fa fa-phone' => 'fas fa-phone-alt',
            'fa fa-square-o' => 'far fa-square',
            'fa fa-bookmark-o' => 'far fa-bookmark',
            'fa fa-phone-square' => 'fas fa-phone-square-alt',
            'fa fa-twitter' => 'fab fa-twitter',
            'fa fa-facebook' => 'fab fa-facebook',
            'fa fa-github' => 'fab fa-github',
            'fa fa-hdd-o' => 'far fa-hdd',
            'fa fa-hand-o-right' => 'far fa-hand-point-right',
            'fa fa-hand-o-left' => 'far fa-hand-point-left',
            'fa fa-hand-o-up' => 'far fa-hand-point-up',
            'fa fa-hand-o-down' => 'far fa-hand-point-down',
            'fa fa-scissors' => 'fas fa-cut',
            'fa fa-files-o' => 'far fa-copy',
            'fa fa-floppy-o' => 'far fa-save',
            'fa fa-pinterest' => 'fab fa-pinterest',
            'fa fa-pinterest-square' => 'fab fa-pinterest-square',
            'fa fa-google-plus-square' => 'fab fa-google-plus-square',
            'fa fa-google-plus' => 'fab fa-google-plus-g',
            'fa fa-money' => 'far fa-money-bill-alt',
            'fa fa-sort-desc' => 'fas fa-sort-down',
            'fa fa-sort-asc' => 'fas fa-sort-up',
            'fa fa-linkedin' => 'fab fa-linkedin-in',
            'fa fa-tachometer' => 'fa fa-tachometer-alt',
            'fa fa-comment-o' => 'far fa-comment',
            'fa fa-comments-o' => 'far fa-comments',
            'fa fa-lightbulb-o' => 'far fa-lightbulb',
            'fa fa-exchange' => 'fas fa-exchange-alt',
            'fa fa-cloud-download' => 'fas fa-cloud-download-alt',
            'fa fa-cloud-upload' => 'fas fa-cloud-upload-alt',
            'fa fa-bell-o' => 'far fa-bell',
            'fa fa-cutlery' => 'fas fa-utensils',
            'fa fa-file-text-o' => 'far fa-file-alt',
            'fa fa-building-o' => 'far fa-building',
            'fa fa-hospital-o' => 'far fa-hospital',
            'fa fa-circle-o' => 'far fa-circle',
            'fa fa-github-alt' => 'fab fa-github-alt',
            'fa fa-folder-o' => 'far fa-folder',
            'fa fa-folder-open-o' => 'far fa-folder-open',
            'fa fa-smile-o' => 'far fa-smile',
            'fa fa-frown-o' => 'far fa-frown',
            'fa fa-meh-o' => 'far fa-meh',
            'fa fa-keyboard-o' => 'far fa-keyboard',
            'fa fa-star-half-o' => 'fas fa-star-half-alt',
            'fa fa-flag-o' => 'far fa-flag',
            'fa fa-code-fork' => 'fas fa-code-branch',
            'fa fa-chain-broken' => 'fas fa-unlink',
            'fa fa-shield' => 'fas fa-shield-alt',
            'fa fa-calendar-o' => 'far fa-calendar',
            'fa fa-maxcdn' => 'fab fa-maxcdn',
            'fa fa-html5' => 'fab fa-html5',
            'fa fa-css3' => 'fab fa-css3',
            'fa fa-ticket' => 'fas fa-ticket-alt',
            'fa fa-minus-square-o' => 'far fa-minus-square',
            'fa fa-level-up' => 'fas fa-level-up-alt',
            'fa fa-level-down' => 'fas fa-level-down-alt',
            'fa fa-pencil-square' => 'fas fa-pen-square',
            'fa fa-external-link-square' => 'fas fa-external-link-square-alt',
            'fa fa-caret-square-o-down' => 'fas fa-caret-square-down',
            'fa fa-caret-square-o-up' => 'fas fa-caret-square-up',
            'fa fa-caret-square-o-right' => 'fas fa-caret-square-right',
            'fa fa-eur' => 'fas fa-euro-sign',
            'fa fa-gbp' => 'fas fa-pound-sign',
            'fa fa-usd' => 'fas fa-dollar-sign',
            'fa fa-inr' => 'fas fa-rupee-sign',
            'fa fa-jpy' => 'fas fa-yen-sign',
            'fa fa-rub' => 'fas fa-ruble-sign',
            'fa fa-krw' => 'fas fa-won-sign',
            'fa fa-btc' => 'fab fa-btc',
            'fa fa-file-text' => 'fas fa-file-alt',
            'fa fa-sort-alpha-asc' => 'fas fa-sort-alpha-down',
            'fa fa-sort-alpha-desc' => 'fas fa-sort-alpha-down-alt',
            'fa fa-sort-amount-asc' => 'fas fa-sort-amount-down-alt',
            'fa fa-sort-amount-desc' => 'fas fa-sort-amount-down',
            'fa fa-sort-numeric-asc' => 'fas fa-sort-numeric-down',
            'fa fa-sort-numeric-desc' => 'fas fa-sort-numeric-down-alt',
            'fa fa-youtube-square' => 'fab fa-youtube-square',
            'fa fa-youtube' => 'fab fa-youtube',
            'fa fa-xing' => 'fab fa-xing',
            'fa fa-xing-square' => 'fab fa-xing-square',
            'fa fa-youtube-play' => 'fab fa-apple-pay',
            'fa fa-dropbox' => 'fab fa-dropbox',
            'fa fa-stack-overflow' => 'fab fa-stack-overflow',
            'fa fa-instagram' => 'fab fa-instagram',
            'fa fa-flickr' => 'fab fa-flickr',
            'fa fa-adn' => 'fab fa-adn',
            'fa fa-bitbucket' => 'fab fa-bitbucket',
            'fa fa-bitbucket-square' => 'fas fa-place-of-worship',
            'fa fa-tumblr' => 'fab fa-tumblr',
            'fa fa-tumblr-square' => 'fab fa-tumblr-square',
            'fa fa-long-arrow-down' => 'fas fa-long-arrow-alt-down',
            'fa fa-long-arrow-up' => 'fas fa-long-arrow-alt-up',
            'fa fa-long-arrow-left' => 'fas fa-long-arrow-alt-left',
            'fa fa-long-arrow-right' => 'fas fa-long-arrow-alt-right',
            'fa fa-apple' => 'fab fa-apple',
            'fa fa-windows' => 'fab fa-windows',
            'fa fa-android' => 'fab fa-android',
            'fa fa-linux' => 'fab fa-linux',
            'fa fa-dribbble' => 'fab fa-dribbble',
            'fa fa-skype' => 'fab fa-skype',
            'fa fa-foursquare' => 'fab fa-foursquare',
            'fa fa-trello' => 'fab fa-trello',
            'fa fa-gratipay' => 'fab fa-gratipay',
            'fa fa-sun-o' => 'far fa-sun',
            'fa fa-moon-o' => 'far fa-moon',
            'fa fa-vk' => 'fab fa-vk',
            'fa fa-weibo' => 'fab fa-weibo',
            'fa fa-renren' => 'fab fa-renren',
            'fa fa-pagelines' => 'fab fa-pagelines',
            'fa fa-stack-exchange' => 'fab fa-stack-exchange',
            'fa fa-arrow-circle-o-right' => 'far fa-arrow-alt-circle-right',
            'fa fa-arrow-circle-o-left' => 'far fa-arrow-alt-circle-left',
            'fa fa-caret-square-o-left' => 'far fa-caret-square-left',
            'fa fa-dot-circle-o' => 'far fa-dot-circle',
            'fa fa-vimeo-square' => 'fab fa-vimeo-square',
            'fa fa-try' => 'fas fa-lira-sign',
            'fa fa-plus-square-o' => 'far fa-plus-square',
            'fa fa-slack' => 'fab fa-slack',
            'fa fa-wordpress' => 'fab fa-wordpress',
            'fa fa-openid' => 'fab fa-openid',
            'fa fa-yahoo' => 'fab fa-yahoo',
            'fa fa-google' => 'fab fa-google',
            'fa fa-reddit' => 'fab fa-reddit',
            'fa fa-reddit-square' => 'fab fa-reddit-square',
            'fa fa-stumbleupon-circle' => 'fab fa-stumbleupon-circle',
            'fa fa-stumbleupon' => 'fab fa-stumbleupon',
            'fa fa-delicious' => 'fab fa-delicious',
            'fa fa-digg' => 'fab fa-digg',
            'fa fa-pied-piper-pp' => 'fab fa-pied-piper-pp',
            'fa fa-pied-piper-alt' => 'fab fa-pied-piper-alt',
            'fa fa-drupal' => 'fab fa-drupal',
            'fa fa-joomla' => 'fab fa-joomla',
            'fa fa-spoon' => 'fas fa-utensil-spoon',
            'fa fa-behance' => 'fab fa-behance',
            'fa fa-behance-square' => 'fab fa-behance-square',
            'fa fa-steam' => 'fab fa-steam',
            'fa fa-steam-square' => 'fab fa-steam-square',
            'fa fa-spotify' => 'fab fa-spotify',
            'fa fa-deviantart' => 'fab fa-deviantart',
            'fa fa-soundcloud' => 'fab fa-soundcloud',
            'fa fa-file-pdf-o' => 'far fa-file-pdf',
            'fa fa-file-word-o' => 'far fa-file-word',
            'fa fa-file-excel-o' => 'far fa-file-excel',
            'fa fa-file-powerpoint-o' => 'far fa-file-powerpoint',
            'fa fa-file-image-o' => 'far fa-file-image',
            'fa fa-file-archive-o' => 'far fa-file-archive',
            'fa fa-file-audio-o' => 'far fa-file-audio',
            'fa fa-file-video-o' => 'far fa-file-video',
            'fa fa-file-code-o' => 'far fa-file-code',
            'fa fa-vine' => 'fab fa-vine',
            'fa fa-codepen' => 'fab fa-codepen',
            'fa fa-jsfiddle' => 'fab fa-jsfiddle',
            'fa fa-circle-o-notch' => 'fas fa-circle-notch',
            'fa fa-rebel' => 'fab fa-rebel',
            'fa fa-empire' => 'fab fa-empire',
            'fa fa-git-square' => 'fab fa-git-square',
            'fa fa-git' => 'fab fa-git',
            'fa fa-hacker-news' => 'fab fa-hacker-news',
            'fa fa-tencent-weibo' => 'fab fa-tencent-weibo',
            'fa fa-qq' => 'fab fa-qq',
            'fa fa-weixin' => 'fab fa-weixin',
            'fa fa-paper-plane-o' => 'far fa-paper-plane',
            'fa fa-circle-thin' => 'fas fa-icicles',
            'fa fa-header' => 'fas fa-heading',
            'fa fa-sliders' => 'fas fa-sliders-h',
            'fa fa-futbol-o' => 'fas fa-futbol',
            'fa fa-slideshare' => 'fab fa-slideshare',
            'fa fa-twitch' => 'fab fa-twitch',
            'fa fa-yelp' => 'fab fa-yelp',
            'fa fa-newspaper-o' => 'far fa-newspaper',
            'fa fa-wifi' => 'fas fa-wifi',
            'fa fa-calculator' => 'fas fa-calculator',
            'fa fa-paypal' => 'fab fa-paypal',
            'fa fa-google-wallet' => 'fab fa-google-wallet',
            'fa fa-cc-visa' => 'fab fa-cc-visa',
            'fa fa-cc-mastercard' => 'fab fa-cc-mastercard',
            'fa fa-cc-discover' => 'fab fa-cc-discover',
            'fa fa-cc-amex' => 'fab fa-cc-amex',
            'fa fa-cc-paypal' => 'fab fa-cc-paypal',
            'fa fa-cc-stripe' => 'fab fa-cc-stripe',
            'fa fa-bell-slash-o' => 'far fa-bell-slash',
            'fa fa-eyedropper' => 'fas fa-eye-dropper',
            'fa fa-area-chart' => 'fas fa-chart-area',
            'fa fa-pie-chart' => 'fas fa-chart-pie',
            'fa fa-line-chart' => 'fas fa-chart-line',
            'fa fa-lastfm' => 'fab fa-lastfm',
            'fa fa-lastfm-square' => 'fab fa-lastfm-square',
            'fa fa-ioxhost' => 'fab fa-ioxhost',
            'fa fa-angellist' => 'fab fa-angellist',
            'fa fa-cc' => 'far fa-closed-captioning',
            'fa fa-ils' => 'fas fa-shekel-sign',
            'fa fa-meanpath' => 'fas fa-drumstick-bite',
            'fa fa-buysellads' => 'fab fa-buysellads',
            'fa fa-connectdevelop' => 'fab fa-connectdevelop',
            'fa fa-dashcube' => 'fab fa-dashcube',
            'fa fa-forumbee' => 'fab fa-forumbee',
            'fa fa-leanpub' => 'fab fa-leanpub',
            'fa fa-sellsy' => 'fab fa-sellsy',
            'fa fa-shirtsinbulk' => 'fab fa-shirtsinbulk',
            'fa fa-simplybuilt' => 'fab fa-simplybuilt',
            'fa fa-skyatlas' => 'fab fa-skyatlas',
            'fa fa-diamond' => 'far fa-gem',
            'fa fa-facebook-official' => 'fab fa-facebook-f',
            'fa fa-pinterest-p' => 'fab fa-pinterest-p',
            'fa fa-whatsapp' => 'fab fa-whatsapp',
            'fa fa-viacoin' => 'fab fa-viacoin',
            'fa fa-medium' => 'fab fa-medium',
            'fa fa-y-combinator' => 'fab fa-y-combinator',
            'fa fa-optin-monster' => 'fab fa-optin-monster',
            'fa fa-opencart' => 'fab fa-opencart',
            'fa fa-expeditedssl' => 'fab fa-expeditedssl',
            'fa fa-sticky-note-o' => 'far fa-sticky-note',
            'fa fa-cc-jcb' => 'fab fa-cc-jcb',
            'fa fa-cc-diners-club' => 'fab fa-cc-diners-club',
            'fa fa-hourglass-o' => 'far fa-hourglass',
            'fa fa-hand-rock-o' => 'far fa-hand-rock',
            'fa fa-hand-paper-o' => 'far fa-hand-paper',
            'fa fa-hand-scissors-o' => 'far fa-hand-scissors',
            'fa fa-hand-lizard-o' => 'far fa-hand-lizard',
            'fa fa-hand-spock-o' => 'far fa-hand-spock',
            'fa fa-hand-pointer-o' => 'far fa-hand-pointer',
            'fa fa-hand-peace-o' => 'far fa-hand-peace',
            'fa fa-trademark' => 'fas fa-trademark',
            'fa fa-registered' => 'fas fa-registered',
            'fa fa-creative-commons' => 'fab fa-creative-commons',
            'fa fa-gg' => 'fab fa-gg',
            'fa fa-gg-circle' => 'fab fa-gg-circle',
            'fa fa-tripadvisor' => 'fab fa-tripadvisor',
            'fa fa-odnoklassniki' => 'fab fa-odnoklassniki',
            'fa fa-odnoklassniki-square' => 'fab fa-odnoklassniki-square',
            'fa fa-get-pocket' => 'fab fa-get-pocket',
            'fa fa-wikipedia-w' => 'fab fa-wikipedia-w',
            'fa fa-safari' => 'fab fa-safari',
            'fa fa-chrome' => 'fab fa-chrome',
            'fa fa-firefox' => 'fab fa-firefox',
            'fa fa-opera' => 'fab fa-opera',
            'fa fa-internet-explorer' => 'fab fa-internet-explorer',
            'fa fa-television' => 'fas fa-tv',
            'fa fa-contao' => 'fab fa-contao',
            'fa fa-500px' => 'fab fa-500px',
            'fa fa-amazon' => 'fab fa-amazon',
            'fa fa-calendar-plus-o' => 'far fa-calendar-plus',
            'fa fa-calendar-minus-o' => 'far fa-calendar-minus',
            'fa fa-calendar-times-o' => 'far fa-calendar-times',
            'fa fa-calendar-check-o' => 'far fa-calendar-check',
            'fa fa-map-o' => 'far fa-map',
            'fa fa-commenting' => 'fas fa-comment-dots',
            'fa fa-commenting-o' => 'far fa-comment-dots',
            'fa fa-houzz' => 'fab fa-houzz',
            'fa fa-vimeo' => 'fab fa-vimeo',
            'fa fa-black-tie' => 'fab fa-black-tie',
            'fa fa-fonticons' => 'fab fa-fonticons',
            'fa fa-reddit-alien' => 'fab fa-reddit-alien',
            'fa fa-edge' => 'fab fa-edge',
            'fa fa-credit-card-alt' => 'fas fa-credit-card',
            'fa fa-codiepie' => 'fab fa-codiepie',
            'fa fa-modx' => 'fab fa-modx',
            'fa fa-fort-awesome' => 'fab fa-fort-awesome',
            'fa fa-usb' => 'fab fa-usb',
            'fa fa-product-hunt' => 'fab fa-product-hunt',
            'fa fa-mixcloud' => 'fab fa-mixcloud',
            'fa fa-scribd' => 'fab fa-scribd',
            'fa fa-pause-circle-o' => 'far fa-pause-circle',
            'fa fa-stop-circle-o' => 'far fa-stop-circle',
            'fa fa-bluetooth' => 'fab fa-bluetooth',
            'fa fa-bluetooth-b' => 'fab fa-bluetooth-b',
            'fa fa-gitlab' => 'fab fa-gitlab',
            'fa fa-wpbeginner' => 'fab fa-wpbeginner',
            'fa fa-wpforms' => 'fab fa-wpforms',
            'fa fa-envira' => 'fab fa-envira',
            'fa fa-wheelchair-alt' => 'fab fa-accessible-icon',
            'fa fa-question-circle-o' => 'far fa-question-circle',
            'fa fa-volume-control-phone' => 'fas fa-phone-volume',
            'fa fa-glide' => 'fab fa-glide',
            'fa fa-glide-g' => 'fab fa-glide-g',
            'fa fa-sign-language' => 'fas fa-sign-language',
            'fa fa-low-vision' => 'fas fa-low-vision',
            'fa fa-viadeo' => 'fab fa-viadeo',
            'fa fa-viadeo-square' => 'fab fa-viadeo-square',
            'fa fa-snapchat' => 'fab fa-snapchat',
            'fa fa-snapchat-ghost' => 'fab fa-snapchat-ghost',
            'fa fa-snapchat-square' => 'fab fa-snapchat-square',
            'fa fa-pied-piper' => 'fab fa-pied-piper',
            'fa fa-first-order' => 'fab fa-first-order',
            'fa fa-yoast' => 'fab fa-yoast',
            'fa fa-themeisle' => 'fab fa-themeisle',
            'fa fa-google-plus-official' => 'fab fa-google-plus',
            'fa fa-font-awesome' => 'fab fa-font-awesome',
        ];

        // Update Icons
        foreach ($taxonomies as $taxonomy)
        {
            // All Terms
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'number' => '', // Return All Terms
            ]);

            foreach ($terms as $term)
            {
                $icon = get_term_meta($term->term_id, 'lsd_icon', true);
                if (!trim($icon)) continue;

                // Use New Icon
                if (isset($icons[$icon])) update_term_meta($term->term_id, 'lsd_icon', $icons[$icon]);
            }
        }
    }

    private function v180()
    {
        // Add Primary Category Meta
        $listings = get_posts([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'posts_per_page' => '-1',
        ]);

        foreach ($listings as $listing)
        {
            $category = LSD_Entity_Listing::get_primary_category($listing->ID);
            add_post_meta($listing->ID, 'lsd_primary_category', ($category ? $category->term_id : null), true);
        }
    }

    private function v190()
    {
        // Add Price Class
        $listings = get_posts([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'posts_per_page' => '-1',
        ]);

        foreach ($listings as $listing) add_post_meta($listing->ID, 'lsd_price_class', 2, true);
    }

    private function v200()
    {
        // Listing Status Notification
        $post_id = wp_insert_post([
            'post_type' => LSD_Base::PTYPE_NOTIFICATION,
            'post_status' => 'draft',
            'post_title' => 'Listing Status Changed!',
            'post_content' => '',
        ]);

        update_post_meta($post_id, 'lsd_hook', 'lsd_listing_status_changed');
        update_post_meta($post_id, 'lsd_original_to', 1);
        update_post_meta($post_id, 'lsd_to', '');
        update_post_meta($post_id, 'lsd_cc', '');
        update_post_meta($post_id, 'lsd_bcc', '');
        update_post_meta($post_id, 'lsd_content', 'Hi,

        Status of #listing_title# changed at #datetime#.
        
        Previous Status: #previous_status#
        New Status: #listing_status#
        
        Regards,
        #site_name#');

        // Report Abuse Notification
        $post_id = wp_insert_post([
            'post_type' => LSD_Base::PTYPE_NOTIFICATION,
            'post_status' => 'publish',
            'post_title' => 'Abuse Report',
            'post_content' => '',
        ]);

        update_post_meta($post_id, 'lsd_hook', 'lsd_listing_report_abuse');
        update_post_meta($post_id, 'lsd_original_to', 1);
        update_post_meta($post_id, 'lsd_to', '');
        update_post_meta($post_id, 'lsd_cc', '');
        update_post_meta($post_id, 'lsd_bcc', '');
        update_post_meta($post_id, 'lsd_content', 'Hi,

        Following abuse report received for #listing_link#.
        
        Name: #name#
        Email: #email#
        Phone: #phone#
        Message: #message#
        
        Regards,
        #site_name#');
    }

    private function v220()
    {
        $this->socials();
    }

    private function v240()
    {
        $this->socials();
    }

    private function v251()
    {
        $this->socials();
    }

    private function v320()
    {
        // Change Name of Installation Time Option
        $installed_at = get_option('lsd_installation_time', null);

        // Validate Time
        if (!$installed_at) $installed_at = current_time('timestamp');

        // Update New Option
        update_option('lsd_installed_at', $installed_at);

        // Delete Previous Option
        delete_option('lsd_installation_time');
    }

    private function v330()
    {
        $this->roles();
    }

    private function v331()
    {
        // Reset Validations
        $this->reset_validations();
    }

    private function v340()
    {
        $this->roles();
    }

    private function v350()
    {
        $this->roles();
    }
}
