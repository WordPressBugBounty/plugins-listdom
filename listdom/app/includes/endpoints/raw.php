<?php
// no direct access
defined('ABSPATH') || die();

/**
 * Listdom Raw Endpoint Class.
 *
 * @class LSD_Endpoints_Raw
 * @version    1.0.0
 */
class LSD_Endpoints_Raw extends LSD_Base
{
    public function output(string $classes = '')
    {
        global $post;
        $content = do_shortcode(wpautop($post->post_content));

        $body = (new LSD_PTypes_Listing_Single())->get($content);
        $class = trim('lsd-iframe-page lsd-raw-page '.$classes);

        // Generate output
        ob_start();
        include lsd_template('iframe.php');
        return ob_get_clean();
    }
}
