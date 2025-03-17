<?php

class LSD_Endpoints_Raw extends LSD_Base
{
    public function output(string $classes = '', string $style = null)
    {
        global $post;
        $content = do_shortcode(wpautop($post->post_content));

        $body = (new LSD_PTypes_Listing_Single())->get($content, $style);
        $class = trim('lsd-iframe-page lsd-raw-page '.$classes);

        // Generate output
        ob_start();
        include lsd_template('iframe.php');
        return ob_get_clean();
    }
}
