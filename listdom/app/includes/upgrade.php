<?php

class LSD_Upgrade extends LSD_Base
{
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
        if (version_compare($version, '3.8.0', '<')) $this->v380();
        if (version_compare($version, '4.1.0', '<')) $this->v410();
        if (version_compare($version, '4.5.0', '<')) $this->v450();
        if (version_compare($version, '4.8.0', '<')) $this->v480();
        if (version_compare($version, '5.2.1', '<')) $this->v521();
        if (version_compare($version, '5.3.0', '<')) $this->v530();
        if (version_compare($version, '5.3.1', '<')) $this->v531();
        if (version_compare($version, '5.4.0', '<')) $this->v540();

        // Regenerate personalized CSS after any update
        LSD_Personalize::generate();
    }

    private function default_notifications(): array
    {
        return [
            'lsd_contact_owner' => [
                'status' => 'publish',
                'title' => 'New Contact By #name#!',
                'content' => 'Hi,

        Following contact received for #listing_link#.
        
        Name: #name#
        Email: #email#
        Phone: #phone#
        Message: #message#
        
        Regards,
        #site_name#',
            ],
            'lsd_new_listing' => [
                'status' => 'draft',
                'title' => 'New Listing Added!',
                'content' => 'Hi,

        Following listing gets added.
        #listing_link#
        
        Title: #listing_title#
        Status: #listing_status#
        Date: #listing_date#
        Owner Name: #owner_name#
        Owner Email: #owner_email#
        
        You can manage it using #admin_link# link.
        
        Regards,
        #site_name#',
            ],
            'lsd_listing_status_changed' => [
                'status' => 'draft',
                'title' => 'Listing Status Changed!',
                'content' => 'Hi,

        Status of #listing_title# changed at #datetime#.
        
        Previous Status: #previous_status#
        New Status: #listing_status#
        
        Regards,
        #site_name#',
            ],
            'lsd_listing_report_abuse' => [
                'status' => 'publish',
                'title' => 'Abuse Report',
                'content' => 'Hi,

        Following abuse report received for #listing_link#.
        
        Name: #name#
        Email: #email#
        Phone: #phone#
        Message: #message#
        
        Regards,
        #site_name#',
            ],
            'lsd_profile_contact' => [
                'status' => 'publish',
                'title' => 'Profile Contact By #name#!',
                'content' => 'Hi,

        You have received a new contact request from your profile page at #profile_link#
        
        Name: #name#
        Email: #email#
        Phone: #phone#
        Message: #message#
        
        Regards,
        #site_name#',
            ],
            'lsd_payments_order_completed' => [
                'status' => 'publish',
                'title' => 'Order Completed!',
                'content' => 'Hi #customer_name#,

                Your order #order_id# has been completed.

                Total: #order_total#

                Invoice: #order_invoice_url#

                Regards,
                #site_name#',
            ],
            'lsd_payments_order_canceled' => [
                'status' => 'publish',
                'title' => 'Order Canceled!',
                'content' => 'Hi #customer_name#,

                Your order #order_id# has been canceled.

                Total: #order_total#

                Regards,
                #site_name#',
            ],
            'lsd_payments_order_refunded' => [
                'status' => 'publish',
                'title' => 'Order Refunded!',
                'content' => 'Hi #customer_name#,

                Your order #order_id# has been refunded.

                Total: #order_total#

                Regards,
                #site_name#',
            ],
            'lsd_payments_order_new_receiver' => [
                'status' => 'publish',
                'title' => 'New Order Received!',
                'content' => 'Hi Admin,

                A new order #order_id# has been received.

                #order_admin_link#

                Total: #order_total#
                Customer Name: #customer_name#
                Customer Email: #customer_email#

                Regards,
                #site_name#',
            ],
            'lsd_payments_order_new_payer' => [
                'status' => 'publish',
                'title' => 'Order Received!',
                'content' => 'Hi #customer_name#,

                Your order #order_id# has been received.

                Total: #order_total#

                Regards,
                #site_name#',
            ],
        ];
    }

    private function seed_notification(string $hook)
    {
        $notifications = $this->default_notifications();
        if (!isset($notifications[$hook])) return;

        $existing = get_posts([
            'post_type' => LSD_Base::PTYPE_NOTIFICATION,
            'posts_per_page' => 1,
            'post_status' => 'any',
            'fields' => 'ids',
            'meta_key' => 'lsd_hook',
            'meta_value' => $hook,
        ]);

        if (is_array($existing) && count($existing)) return;

        $notification = $notifications[$hook];
        $post_id = wp_insert_post([
            'post_type' => LSD_Base::PTYPE_NOTIFICATION,
            'post_status' => $notification['status'],
            'post_title' => $notification['title'],
            'post_content' => '',
        ]);

        if (!$post_id || is_wp_error($post_id)) return;

        update_post_meta($post_id, 'lsd_hook', $hook);
        update_post_meta($post_id, 'lsd_original_to', 1);
        update_post_meta($post_id, 'lsd_to', '');
        update_post_meta($post_id, 'lsd_cc', '');
        update_post_meta($post_id, 'lsd_bcc', '');
        update_post_meta($post_id, 'lsd_content', $notification['content']);
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

    private function v121()
    {
        $this->seed_notification('lsd_contact_owner');
        $this->seed_notification('lsd_new_listing');
    }

    private function v170()
    {
        // Taxonomies
        $taxonomies = [
            LSD_Base::TAX_CATEGORY,
            LSD_Base::TAX_FEATURE,
            LSD_Base::TAX_ATTRIBUTE,
        ];

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
            'posts_per_page' => 1000,
            'meta_query' => [
                [
                    'key' => 'lsd_primary_category',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ]);

        foreach ($listings as $listing)
        {
            $category = LSD_Entity_Listing::get_primary_category($listing->ID);
            add_post_meta($listing->ID, 'lsd_primary_category', ($category ? $category->slug : null), true);
        }
    }

    private function v190()
    {
        // Add Price Class
        $listings = get_posts([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'posts_per_page' => 1000,
            'meta_query' => [
                [
                    'key' => 'lsd_price_class',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ]);

        foreach ($listings as $listing) add_post_meta($listing->ID, 'lsd_price_class', 2, true);
    }

    private function v200()
    {
        $this->seed_notification('lsd_listing_status_changed');
        $this->seed_notification('lsd_listing_report_abuse');
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
        LSD_Roles::add(true);
    }

    private function v331()
    {
        // Reset Validations
        $this->reset_validations();
    }

    private function v340()
    {
        LSD_Roles::add(true);
    }

    private function v350()
    {
        LSD_Roles::add(true);
    }

    private function v380()
    {
        // Add Visits
        $listings = get_posts([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'posts_per_page' => 1000,
            'meta_query' => [
                [
                    'key' => 'lsd_visits',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ]);

        foreach ($listings as $listing) add_post_meta($listing->ID, 'lsd_visits', 0, true);
    }

    private function v410()
    {
        $this->seed_notification('lsd_profile_contact');
    }

    private function v450()
    {
        // Database
        $db = new LSD_db();

        $metas = $db->select(
            "SELECT `meta_id`, `post_id`, `meta_value` FROM `#__postmeta` WHERE `meta_key`='lsd_attributes' AND `meta_value`!='a:0:{}' ORDER BY `meta_id` DESC LIMIT 1000",
            'loadAssocList'
        );

        $terms = get_terms([
            'taxonomy' => LSD_Base::TAX_ATTRIBUTE,
            'hide_empty' => false,
        ]);

        // Map Term ID to Slug
        $attr_map = [];
        foreach ($terms as $term) $attr_map[$term->term_id] = $term->slug;

        foreach ($metas as $meta)
        {
            $values = maybe_unserialize($meta['meta_value']);
            if (!is_array($values)) continue;

            foreach ($values as $id => $val)
            {
                if (!is_numeric($id) || !isset($attr_map[$id])) continue;

                $slug = $attr_map[$id];
                if ($slug && !array_key_exists($slug, $values)) $values[$slug] = $val;

                update_post_meta($meta['post_id'], 'lsd_attribute_' . $slug, $val);
            }

            update_post_meta($meta['post_id'], 'lsd_attributes', $values);
        }
    }

    private function v480()
    {
        $this->seed_notification('lsd_payments_order_completed');
        $this->seed_notification('lsd_payments_order_canceled');
        $this->seed_notification('lsd_payments_order_refunded');
        $this->seed_notification('lsd_payments_order_new_receiver');
        $this->seed_notification('lsd_payments_order_new_payer');
    }

    private function v521()
    {
        // Update Searches
        $search_ids = get_posts([
            'post_type' => LSD_Base::PTYPE_SEARCH,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'inherit', 'trash'],
        ]);

        if (is_array($search_ids) && count($search_ids))
        {
            $unit_divisor = static function (string $unit): float
            {
                if ($unit === 'km') return 1000.0;
                if ($unit === 'mile') return 1609.0;
                return 1.0;
            };

            $format_value = static function (float $value): string
            {
                $formatted = rtrim(rtrim(sprintf('%.3f', $value), '0'), '.');
                return $formatted === '-0' ? '0' : $formatted;
            };

            $convert_values = static function (string $values, string $unit) use ($unit_divisor, $format_value): string
            {
                $divisor = $unit_divisor($unit);
                if ($divisor === 1.0) return $values;

                $items = array_map('trim', explode(',', $values));
                $converted = [];

                foreach ($items as $item)
                {
                    if ($item === '') continue;
                    if (!is_numeric($item))
                    {
                        $converted[] = $item;
                        continue;
                    }

                    $converted[] = $format_value(((float) $item) / $divisor);
                }

                return implode(',', $converted);
            };

            foreach ($search_ids as $search_id)
            {
                foreach (['lsd_fields', 'lsd_tablet', 'lsd_mobile'] as $meta_key)
                {
                    $fields = get_post_meta($search_id, $meta_key, true);
                    if (!is_array($fields) || !count($fields)) continue;

                    $updated = false;
                    foreach ($fields as $row_index => $row)
                    {
                        if (!isset($row['filters']) || !is_array($row['filters'])) continue;

                        foreach ($row['filters'] as $filter_key => $filter)
                        {
                            if (!is_array($filter)) continue;

                            $key = $filter['key'] ?? $filter_key;
                            if ($key !== 'address') continue;

                            $method = $filter['method'] ?? '';
                            $unit = $filter['radius_display_unit'] ?? 'm';
                            if (!in_array($unit, ['m', 'km', 'mile'], true)) $unit = 'm';

                            if ($method === 'radius-dropdown' && isset($filter['radius_values']) && is_string($filter['radius_values']))
                            {
                                $converted = $convert_values($filter['radius_values'], $unit);
                                if ($converted !== $filter['radius_values'])
                                {
                                    $fields[$row_index]['filters'][$filter_key]['radius_values'] = $converted;
                                    $updated = true;
                                }
                            }
                        }
                    }

                    if ($updated) update_post_meta($search_id, $meta_key, $fields);
                }
            }
        }

        // Update Memberships
        if (class_exists(LSDPACSUB\Base::class))
        {
            $listing_ids = get_posts([
                'post_type' => LSD_Base::PTYPE_LISTING,
                'posts_per_page' => 1000,
                'fields' => 'ids',
                'post_status' => ['publish', LSD_Base::STATUS_HOLD, LSD_Base::STATUS_EXPIRED],
                'meta_query' => [
                    [
                        'key' => 'lsd_subscription',
                        'compare' => 'EXISTS',
                    ],
                    [
                        'key' => 'lsd_package',
                        'compare' => 'NOT EXISTS',
                    ],
                ],
            ]);

            if (is_array($listing_ids) && count($listing_ids))
            {
                foreach ($listing_ids as $listing_id)
                {
                    $subscription_id = get_post_meta($listing_id, 'lsd_subscription', true);
                    if (!$subscription_id) continue;

                    $package_id = get_post_meta($subscription_id, 'lsd_package', true);
                    if (!$package_id) continue;

                    update_post_meta($listing_id, 'lsd_package', $package_id);
                }
            }
        }
    }

    private function v530()
    {
        $shortcode_ids = get_posts([
            'post_type' => LSD_Base::PTYPE_SHORTCODE,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'inherit', 'trash'],
        ]);

        if (!is_array($shortcode_ids) || !count($shortcode_ids)) return;

        $taxonomies = [
            LSD_Base::TAX_CATEGORY,
            LSD_Base::TAX_LOCATION,
            LSD_Base::TAX_FEATURE,
            LSD_Base::TAX_LABEL,
            LSD_Base::TAX_TAG,
        ];

        foreach ($shortcode_ids as $shortcode_id)
        {
            $filter = get_post_meta($shortcode_id, 'lsd_filter', true);
            $exclude = get_post_meta($shortcode_id, 'lsd_exclude', true);

            $filter_changed = false;
            $exclude_changed = false;

            if (is_array($filter))
            {
                foreach ($taxonomies as $tax)
                {
                    if (!isset($filter[$tax])) continue;

                    $original = $filter[$tax];
                    $filter[$tax] = LSD_Taxonomies::ids_to_slugs($tax, $filter[$tax]);

                    if ($filter[$tax] !== $original) $filter_changed = true;
                }
            }

            if (is_array($exclude))
            {
                foreach ($taxonomies as $tax)
                {
                    if (!isset($exclude[$tax])) continue;

                    $original = $exclude[$tax];
                    $exclude[$tax] = LSD_Taxonomies::ids_to_slugs($tax, $exclude[$tax]);

                    if ($exclude[$tax] !== $original) $exclude_changed = true;
                }
            }

            if ($filter_changed) update_post_meta($shortcode_id, 'lsd_filter', $filter);
            if ($exclude_changed) update_post_meta($shortcode_id, 'lsd_exclude', $exclude);
        }
    }

    private function v531()
    {
        if (!taxonomy_exists(LSD_Base::TAX_ATTRIBUTE)) return;

        $attribute_ids = get_terms([
            'taxonomy' => LSD_Base::TAX_ATTRIBUTE,
            'hide_empty' => false,
            'fields' => 'ids',
        ]);

        if (!is_array($attribute_ids) || !count($attribute_ids)) return;

        foreach ($attribute_ids as $attribute_id)
        {
            $all_categories = get_term_meta((int) $attribute_id, 'lsd_all_categories', true);
            if (trim((string) $all_categories) === '') $all_categories = 1;

            $categories = get_term_meta((int) $attribute_id, 'lsd_categories', true);
            $normalized = $all_categories ? [] : LSD_Taxonomies_Attribute::normalize_categories($categories);

            if (!is_array($categories) || $categories !== $normalized)
            {
                update_term_meta((int) $attribute_id, 'lsd_categories', $normalized);
            }
        }
    }

    private function v540()
    {
        $this->shortcode_filters();
        $this->primary_categories();

        $map = [];
        foreach (LSD_Main::get_attributes_details() as $attribute)
        {
            $attribute_id = isset($attribute['id']) ? (int) $attribute['id'] : 0;
            $slug = isset($attribute['slug']) ? sanitize_title($attribute['slug']) : '';

            if ($attribute_id <= 0 || $slug === '') continue;
            $map['lsd_attribute_' . $attribute_id] = 'lsd_attribute_' . $slug;
        }

        if (!count($map)) return;

        $db = new LSD_db();
        $metas = $db->select(
            "SELECT `meta_id`, `meta_value` FROM `#__postmeta` WHERE `meta_key` IN ('_elementor_data', '_elementor_page_settings') AND `meta_value` LIKE '%lsd_attribute_%' ORDER BY `meta_id` ASC",
            'loadAssocList'
        );

        if (!is_array($metas) || !count($metas)) return;

        foreach ($metas as $meta)
        {
            $meta_id = isset($meta['meta_id']) ? (int) $meta['meta_id'] : 0;
            if ($meta_id <= 0 || !array_key_exists('meta_value', $meta)) continue;

            $value = $meta['meta_value'];
            $changed = false;
            $prepared = $value;

            if (is_string($value))
            {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                {
                    $changed = $this->elementor($decoded, $map);
                    $prepared = $changed ? wp_json_encode($decoded) : $value;
                }
                else
                {
                    $unserialized = maybe_unserialize($value);
                    if (is_array($unserialized))
                    {
                        $changed = $this->elementor($unserialized, $map);
                        $prepared = $changed ? $unserialized : $value;
                    }
                }
            }
            else if (is_array($value))
            {
                $changed = $this->elementor($value, $map);
                $prepared = $value;
            }

            if (!$changed) continue;
            update_metadata_by_mid('post', $meta_id, $prepared);
        }
    }

    private function primary_categories()
    {
        $listing_ids = get_posts([
            'post_type' => LSD_Base::PTYPE_LISTING,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'inherit', 'trash'],
            'meta_query' => [
                [
                    'key' => 'lsd_primary_category',
                    'compare' => 'EXISTS',
                ],
            ],
        ]);

        if (!is_array($listing_ids) || !count($listing_ids)) return;

        foreach ($listing_ids as $listing_id)
        {
            $primary = get_post_meta($listing_id, 'lsd_primary_category', true);
            if (!is_scalar($primary)) continue;

            $primary = trim((string) $primary);
            if ($primary === '') continue;

            $term = null;
            if (is_numeric($primary))
            {
                $term = get_term((int) $primary, LSD_Base::TAX_CATEGORY);
            }
            else
            {
                $term = get_term_by('slug', sanitize_title($primary), LSD_Base::TAX_CATEGORY);
            }

            if (!$term || is_wp_error($term) || !isset($term->slug)) continue;

            $slug = sanitize_title($term->slug);
            if ($slug === '' || $slug === $primary) continue;

            update_post_meta($listing_id, 'lsd_primary_category', $slug);
        }
    }

    private function shortcode_filters()
    {
        if (!taxonomy_exists(LSD_Base::TAX_ATTRIBUTE)) return;

        $shortcode_ids = get_posts([
            'post_type' => LSD_Base::PTYPE_SHORTCODE,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'inherit', 'trash'],
        ]);

        if (!is_array($shortcode_ids) || !count($shortcode_ids)) return;

        foreach ($shortcode_ids as $shortcode_id)
        {
            $filter = get_post_meta($shortcode_id, 'lsd_filter', true);
            if (!is_array($filter) || !isset($filter['attributes']) || !is_array($filter['attributes'])) continue;

            $normalized = [];
            foreach ($filter['attributes'] as $key => $value)
            {
                $separator = strrpos((string) $key, '-');
                if ($separator === false) continue;

                $slug = substr($key, 0, $separator);
                $query = trim(substr($key, $separator + 1));
                if ($query === '') continue;

                if (is_numeric($slug))
                {
                    $slug = LSD_Taxonomies::to_slug(LSD_Base::TAX_ATTRIBUTE, $slug);
                    if ($slug === '') continue;
                }

                $migrated_key = LSD_Taxonomies_Attribute::normalize_filter_attribute_key($slug . '-' . $query);
                if ($migrated_key === '') continue;

                if (isset($normalized[$migrated_key]) && is_array($normalized[$migrated_key]))
                {
                    $incoming = is_array($value) ? $value : [$value];
                    $normalized[$migrated_key] = array_values(array_unique(array_merge($normalized[$migrated_key], $incoming)));

                    continue;
                }

                $normalized[$migrated_key] = $value;
            }

            if ($normalized === $filter['attributes']) continue;

            $filter['attributes'] = $normalized;
            update_post_meta($shortcode_id, 'lsd_filter', $filter);
        }
    }

    private function elementor(array &$data, array $map): bool
    {
        $changed = false;
        foreach ($data as $key => &$value)
        {
            if ($key === 'lsd_meta' && is_string($value) && isset($map[$value]))
            {
                $value = $map[$value];
                $changed = true;

                continue;
            }

            if (is_array($value))
            {
                if ($this->elementor($value, $map)) $changed = true;
                continue;
            }

            if (!is_string($value)) continue;
            if (strpos($value, 'lsd_attribute_') === false) continue;

            $is_dynamic = strpos($value, 'lsd_meta') !== false
                || strpos($value, 'elementor-tag') !== false
                || strpos($value, '%22lsd_meta%22') !== false;

            if ($is_dynamic)
            {
                $migrated = preg_replace_callback('/lsd_attribute_\d+\b/', static function (array $matches) use ($map)
                {
                    $match = $matches[0] ?? '';
                    return $map[$match] ?? $match;
                }, $value);

                if (!is_string($migrated)) continue;

                if ($migrated !== $value)
                {
                    $value = $migrated;
                    $changed = true;
                }
            }
        }

        return $changed;
    }
}
