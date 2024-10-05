<?php

use Webilia\WP\Plugin\Licensing;
use Webilia\WP\Plugin\Update;

/**
 * Listdom Plugin Update Class.
 *
 * @class LSD_Plugin_Update
 * @version	1.0.0
 */
class LSD_Plugin_Update
{
    /**
     * Initialize a new instance of the WordPress Auto-Update class
     *
     * @param array $args
     */
    function __construct(array $args = [])
    {
        $version = $args['version'];
        $server = $args['server'] ?? 'https://api.webilia.com/update';
        $basename = $args['basename'];

        $license_key_option = $args['license_key_option'] ?? $args['prefix'].'_purchase_code';
        $activation_id_option = $args['activation_id_option'] ?? $args['prefix'].'_activation_id';

        // Webilia Licensing Server
        $licensing = new Licensing(
            $license_key_option,
            $activation_id_option,
            $basename,
            LSD_LICENSING_SERVER
        );

        // Webilia Update Server
        new Update(
            $version,
            $basename,
            $licensing,
            LSD_VERSION,
            $server
        );
    }
}
