<?php

namespace Example\CrmContactModule\Http\Middleware;

use Closure;
use Example\CrmContactModule\Services\Auth\JwtParser;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtTenantMiddleware
{
    public function __construct(
        private readonly JwtParser $jwtParser
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'No token provided'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $payload = $this->jwtParser->decode($token);

            // Add tenant and user context to the request
            $request->merge([
                'tenant_id' => $payload['tenant_id'],
                'user_id' => $payload['user_id']
            ]);

            // Make tenant info available globally
            app()->bind('tenant.id', fn() => $payload['tenant_id']);
            app()->bind('user.id', fn() => $payload['user_id']);

            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
