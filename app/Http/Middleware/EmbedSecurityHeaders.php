<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmbedSecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if (! $this->isEmbedRequest($request)) {
            return $response;
        }

        // Remove conflicting clickjacking headers to let frame-ancestors drive the policy.
        $response->headers->remove('X-Frame-Options');

        $frameAncestors = $this->frameAncestors();
        if ($frameAncestors === []) {
            // If no domains configured we keep the header removed but do not send an empty CSP directive.
            $response->headers->remove('Content-Security-Policy');

            return $response;
        }

        $directive = sprintf('frame-ancestors %s;', implode(' ', $frameAncestors));
        $response->headers->set('Content-Security-Policy', $directive);

        return $response;
    }

    private function isEmbedRequest(Request $request): bool
    {
        if ($request->routeIs('public.embed')) {
            return true;
        }

        if ($request->boolean('embed')) {
            return true;
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function frameAncestors(): array
    {
        $ancestors = config('security.embed.frame_ancestors', []);

        return array_values(array_filter(array_map('trim', (array) $ancestors)));
    }
}
