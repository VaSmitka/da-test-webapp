<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HttpsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // This should work by default via X-Forwarded headers.
        // But if they are not working, it is possible to fake it via this.
        if (\config('https.fake') === true) {
            $request->headers->set('X-FORWARDED-PROTO', 'https');
            $request->headers->set('X-FORWARDED-PORT', '443');
        } elseif (\config('https.enable') === true) {
            if (!$request->secure()) {
                return \redirect()->secure($request->getRequestUri(), 301);
            }
            if (\config('https.hsts_lifetime') > 0) {
                $response = $next($request);
                $headerValue = 'max-age=' . \config('https.hsts_lifetime');

                if (\config('https.hsts_include_subdomains')) {
                    $headerValue .= '; includeSubdomains';
                }

                if (\config('https.hsts_preload')) {
                    $headerValue .= '; preload';
                }

                $response->headers->set('Strict-Transport-Security', $headerValue);

                $hstsPixelUrl = \config('https.hsts_pixel_url');
                if (!empty($hstsPixelUrl)) {
                    $response = $this->addHstsPixel($response, $hstsPixelUrl);
                }

                return $response;
            }
        }

        return $next($request);
    }

    /**
     * Add to HTML response HSTS pixel
     *
     * @param  mixed $response
     * @return mixed
     */
    protected function addHstsPixel($response, string $hstsPixelUrl)
    {
        $content = $response->getContent();
        if (\is_string($content) && \preg_match('/<\/body>/i', $content)) {
            $newContent = '<img src="' . $hstsPixelUrl
                . '" alt="Security HSTS pixel" style="display:none" width="0" height="0"></body>';
            $content = \str_replace('</body>', $newContent, $content);
            $response->setContent($content);
        }

        return $response;
    }
}
