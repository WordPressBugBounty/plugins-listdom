<?php
namespace Webilia\Toast;

class Boot extends Base
{
    public const VERSION = '1.0.0';

    public function enqueue(
        string $handle = 'webilia-toast',
        array $deps = ['jquery'],
        string $version = self::VERSION
    ): void
    {
        wp_enqueue_script(
            $handle,
            $this->asset_url('js/webilia-toast.min.js'),
            $deps,
            $version,
            true
        );

        wp_enqueue_style(
            $handle,
            $this->asset_url('css/webilia-toast.min.css'),
            [],
            $version
        );
    }
}
