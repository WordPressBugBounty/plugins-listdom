<?php

use Webilia\WP\Plugin\Licensing;

/**
 * Listdom Plugin Licensing Class.
 *
 * @class LSD_Plugin_Licensing
 * @version	1.0.0
 */
class LSD_Plugin_Licensing
{
    /**
     * @var Licensing
     */
    private $handler;

    /**
     * @var string
     */
    private $prefix;

    /**
     * Constructor
     */
    function __construct(array $args = [])
    {
        $this->prefix = $args['prefix'] ?? '';

        $license_key_option = $args['license_key_option'] ?? $args['prefix'].'_purchase_code';
        $activation_id_option = $args['activation_id_option'] ?? $args['prefix'].'_activation_id';
        $basename = $args['basename'] ?? LSD_BASENAME;

        // Webilia Licensing Server
        $this->handler = new Licensing(
            $license_key_option,
            $activation_id_option,
            $basename,
            LSD_LICENSING_SERVER
        );
    }

    /**
     * @return mixed
     */
    public function getLicenseKey()
    {
        return $this->handler->getLicenseKey();
    }

    /**
     * @return mixed
     */
    public function getActivationId()
    {
        return $this->handler->getActivationId();
    }

    /**
     * @return string
     */
    public function getBasename(): string
    {
        return $this->handler->getBasename();
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Check to See if License is Valid
     *
     * @return bool
     */
    public function isLicenseValid(): bool
    {
        return $this->handler->isValid();
    }

    /**
     * @param string $license_key
     * @return array
     */
    public function activate(string $license_key): array
    {
        list($status, $response, $activation_id) = $this->handler->activate($license_key);

        if($response === Licensing::STATUS_VALID) $message = esc_html__('License key is valid and your website activated successfully!', 'listdom');
        else if($response === Licensing::STATUS_INVALID) $message = esc_html__('The license key is either invalid, expired, not meant for this product, or has reached its activation limit. Please verify the key or obtain a new one if needed.', 'listdom');
        else if($response === Licensing::ERROR_UNKNOWN) $message = esc_html__('Something went wrong!', 'listdom');
        else if($response === Licensing::ERROR_CONNECTION) $message = esc_html__('It seems your website cannot webilia.com server for validating the license key! Please consult with your host provider.', 'listdom');
        else $message = $response;

        return [$status, $message];
    }

    public function deactivate(string $license_key): array
    {
        $message = esc_html__("The license key has been successfully removed, and your website is now deactivated.", 'listdom');

        // Envato or Legacy Key
        if(strlen($license_key) === 36 || strpos($license_key, '-'))
        {
            // Delete License Key
            delete_option($this->handler->getLicenseKeyOption());

            // Delete Activation ID
            delete_option($this->handler->getActivationIdOption());

            return [true, $message];
        }

        // Webilia Key
        $status = $this->handler->deactivate($license_key);
        if(!$status) $message = esc_html__("License deactivation error. Retry later or contact support. We apologize for any inconvenience.", 'listdom');

        return [$status, $message];
    }
}
