<?php
namespace Webilia\LSDI;

class Base
{
    protected string $base_url;

    public function __construct(string $base_url)
    {
        $this->base_url = rtrim($base_url, '/');
    }

    protected function asset_url(string $asset): string
    {
        return $this->base_url . '/assets/' . ltrim($asset, '/');
    }
}
