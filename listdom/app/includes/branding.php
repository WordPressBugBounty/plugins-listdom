<?php

class LSD_Branding extends LSD_Base
{
    public static function defaults(): array
    {
        return [
            'name' => 'Listdom',
            'company_name' => 'Webilia',
            'support_url' => LSD_Base::addUtmParameters('https://listdom.net/support/'),
            'docs_url' => LSD_Base::addUtmParameters('https://api.webilia.com/go/listdom-docs'),
            'shop_url' => 'https://api.webilia.com/go/shop',
            'account_url' => LSD_Base::addUtmParameters('https://listdom.net/my-account/'),
            'manage_licenses_url' => 'https://api.webilia.com/go/my-account',
            'plugin_uri' => 'https://listdom.net',
            'author_uri' => 'https://webilia.com/',
        ];
    }

    public static function all(): array
    {
        $defaults = self::defaults();
        $branding = apply_filters('lsd_branding', $defaults);

        if (!is_array($branding)) $branding = [];
        $branding = array_merge($defaults, $branding);

        $branding['name'] = self::str(
            apply_filters('lsd_branding_name', self::str($branding['name'] ?? '', $defaults['name'])),
            $defaults['name']
        );
        $branding['company_name'] = self::str(
            apply_filters('lsd_branding_company_name', self::str($branding['company_name'] ?? '', $defaults['company_name'])),
            $defaults['company_name']
        );

        $branding['support_url'] = self::url(
            apply_filters('lsd_branding_support_url', self::str($branding['support_url'] ?? '', $defaults['support_url'])),
            $defaults['support_url']
        );
        $branding['docs_url'] = self::url(
            apply_filters('lsd_branding_docs_url', self::str($branding['docs_url'] ?? '', $defaults['docs_url'])),
            $defaults['docs_url']
        );
        $branding['shop_url'] = self::url(
            apply_filters('lsd_branding_shop_url', self::str($branding['shop_url'] ?? '', $defaults['shop_url'])),
            $defaults['shop_url']
        );
        $branding['account_url'] = self::url(
            apply_filters('lsd_branding_account_url', self::str($branding['account_url'] ?? '', $defaults['account_url'])),
            $defaults['account_url']
        );
        $branding['manage_licenses_url'] = self::url(
            apply_filters('lsd_branding_manage_licenses_url', self::str($branding['manage_licenses_url'] ?? '', $defaults['manage_licenses_url'])),
            $defaults['manage_licenses_url']
        );
        $branding['plugin_uri'] = self::url(
            apply_filters('lsd_branding_plugin_uri', self::str($branding['plugin_uri'] ?? '', $defaults['plugin_uri'])),
            $defaults['plugin_uri']
        );
        $branding['author_uri'] = self::url(
            apply_filters('lsd_branding_author_uri', self::str($branding['author_uri'] ?? '', $defaults['author_uri'])),
            $defaults['author_uri']
        );

        return $branding;
    }

    public static function name(): string
    {
        return self::all()['name'];
    }

    public static function companyName(): string
    {
        return self::all()['company_name'];
    }

    public static function supportUrl(): string
    {
        return self::all()['support_url'];
    }

    public static function docsUrl(): string
    {
        return self::all()['docs_url'];
    }

    public static function shopUrl(): string
    {
        return self::all()['shop_url'];
    }

    public static function accountUrl(): string
    {
        return self::all()['account_url'];
    }

    public static function manageLicensesUrl(): string
    {
        return self::all()['manage_licenses_url'];
    }

    public static function pluginUri(): string
    {
        return self::all()['plugin_uri'];
    }

    public static function authorUri(): string
    {
        return self::all()['author_uri'];
    }

    private static function str($value, string $fallback = ''): string
    {
        if (!is_string($value)) return $fallback;

        $value = trim($value);
        return $value !== '' ? $value : $fallback;
    }

    private static function url(string $url, string $fallback = ''): string
    {
        $url = trim($url);
        if ($url === '') return $fallback;

        $sanitized = esc_url_raw($url);
        return $sanitized !== '' ? $sanitized : $fallback;
    }
}
