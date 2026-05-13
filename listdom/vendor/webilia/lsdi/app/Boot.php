<?php
namespace Webilia\LSDI;

class Boot extends Base
{
    public const VERSION = '1.0.0';

    public function enqueue(string $handle = 'webilia-lsdi', array $deps = [], string $version = self::VERSION)
    {
        wp_enqueue_style($handle, $this->asset_url('lsdi/lsdi.css'), $deps, $version);
    }
}
