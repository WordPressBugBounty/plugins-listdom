<?php
/**
 * Plugin Name: Listdom
 * Plugin URI: https://listdom.net
 * Description: Listdom is a powerful yet easy-to-use tool for listing anything on your website. It offers modern, responsive skins such as List, Grid, Map, and Masonry to showcase your content beautifully.
 * Version: 4.6.0
 * Author: Webilia
 * Author URI: https://webilia.com/
 * Requires at least: 4.2
 * Tested up to: 6.8
 *
 * Text Domain: listdom
 * Domain Path: /i18n/languages/
 */

// No Direct Access
defined('ABSPATH') || die();

// Initialize the Listdom or not?!
$init = true;

// Check Minimum PHP version
if (version_compare(phpversion(), '7.2', '<'))
{
    $init = false;
    add_action('admin_notices', function ()
    {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo sprintf(esc_html__("%s requires at least PHP 7.2 or higher, but your server is currently running PHP %s. Please contact your hosting provider to upgrade your PHP version or consider switching to a different host.", 'listdom'), '<strong>Listdom</strong>', '<strong>' . phpversion() . '</strong>'); ?></p>
        </div>
        <?php
    });
}

// Check Minimum WP version
global $wp_version;
if (version_compare($wp_version, '4.0.0', '<'))
{
    $init = false;
    add_action('admin_notices', function () use ($wp_version)
    {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo sprintf(esc_html__("%s requires at least WordPress 4.0.0 or higher, but your current version is %s. Please update WordPress to the latest version first.", 'listdom'), '<strong>Listdom</strong>', '<strong>' . esc_html($wp_version) . '</strong>'); ?></p>
        </div>
        <?php
    });
}

// Plugin initialized before! Maybe by Pro or Lite version
if (function_exists('listdom')) $init = false;

// Run the Listdom
if ($init) require_once 'LSD.php';
