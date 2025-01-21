<?php

namespace Example\CrmExample\Tests\Unit;

use Example\CrmExample\Http\Middleware\JwtTenantMiddleware;
use Example\CrmExample\Services\Auth\JwtParser;
use Example\CrmExample\Tests\TestCase;
use Illuminate\Http\Request;

class JwtTenantMiddlewareTest extends TestCase
{
    private JwtParser $jwtParser;
    private JwtTenantMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtParser = new JwtParser();
        $this->middleware = new JwtTenantMiddleware($this->jwtParser);
    }

    public function test_middleware_sets_tenant_context(): void
    {
        // Create a mock token
        $token = $this->jwtParser->encode([
            'tenant_id' => 123,
            'user_id' => 456
        ]);

        // Create request with token
        $request = Request::create('/api/contacts');
        $request->headers->set('Authorization', 'Bearer ' . $token);

        // Execute middleware
        $response = $this->middleware->handle($request, function ($request) {
            $this->assertEquals(123, tenant('id'));
            $this->assertEquals(456, auth()->id());
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_rejects_invalid_token(): void
    {
        $request = Request::create('/api/contacts');
        $request->headers->set('Authorization', 'Bearer invalid.token.here');

        $response = $this->middleware->handle($request, function () {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('Invalid token', json_decode($response->getContent())->error);
    }
} 