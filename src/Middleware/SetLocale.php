<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Config::set(key: 'app.locale', value: $request->input('lang', Config::get(key: 'app.locale', default: 'en')));

        return $next($request);
    }
}
