<?php
namespace Webilia\Icons;

class Boot extends Base
{
    public const VERSION = '1.0.0';

    public function enqueue(string $handle = 'webilia-icons', array $deps = [], string $version = self::VERSION)
    {
        wp_enqueue_style($handle, $this->asset_url('icons/icons.css'), $deps, $version);
    }
}
