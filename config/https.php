<?php

declare(strict_types=1);

return [
    /**
     * HTTPS middleware redirect always to HTTPS first
     */
    'enable'            => env('HTTPS_ENABLE', false),

    /**
     * If lifetime of HSTS header is > 0, set HSTS header
     */
    'hsts_lifetime'     => env('HSTS_LIFETIME', 0),
    /**
     * Add include_subdomains to HSTS header
     */
    'hsts_include_subdomains'   => env('HSTS_INCLUDE_SUBDOMAINS', true),

    /**
     * Add preload to HSTS header
     */
    'hsts_preload'      => env('HSTS_PRELOAD', false),

    /**
     * Url of HSTS pixel to add to HTML, if empty, nothing is added
     */
    'hsts_pixel_url'    => env('HSTS_PIXEL_URL', ''),

    /**
     * Set to true to act as web is on HTTPS even it is not set in headers nor Forwarded headers
     */
    'fake' => env('HTTPS_FAKE', false),
];
