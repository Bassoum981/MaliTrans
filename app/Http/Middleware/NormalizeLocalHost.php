<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeLocalHost
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.env') !== 'local' || $request->is('api/*')) {
            return $next($request);
        }

        $applicationUrl = (string) config('app.url');
        $applicationUrlParts = parse_url($applicationUrl);
        $canonicalHost = $applicationUrlParts['host'] ?? null;
        $canonicalPort = $applicationUrlParts['port']
            ?? (($applicationUrlParts['scheme'] ?? 'http') === 'https' ? 443 : 80);
        $localHosts = ['localhost', '127.0.0.1', '::1'];

        if (
            $canonicalHost === null
            || ! in_array($canonicalHost, $localHosts, true)
            || ! in_array($request->getHost(), $localHosts, true)
            || ($request->getHost() === $canonicalHost && $request->getPort() === $canonicalPort)
        ) {
            return $next($request);
        }

        return redirect()->to(rtrim($applicationUrl, '/').$request->getRequestUri());
    }
}
