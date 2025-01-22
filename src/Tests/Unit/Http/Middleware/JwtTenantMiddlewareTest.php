<?php

namespace Example\CrmContactModule\Tests\Unit\Http\Middleware;

use Example\CrmContactModule\Http\Middleware\JwtTenantMiddleware;
use Example\CrmContactModule\Services\Auth\JwtParser;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class JwtTenantMiddlewareTest extends TestCase
{
    private JwtParser $parser;
    private JwtTenantMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new JwtParser();
        $this->middleware = new JwtTenantMiddleware($this->parser);
    }

    public function test_adds_tenant_context(): void
    {
        $token = $this->parser->encode([
            'tenant_id' => 123,
            'user_id' => 456
        ]);

        $request = Request::create('/test');
        $request->headers->set('Authorization', "Bearer $token");

        $response = $this->middleware->handle($request, function ($request) {
            $this->assertEquals(123, $request->get('tenant_id'));
            $this->assertEquals(456, $request->get('user_id'));
            return new Response();
        });

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function test_rejects_missing_token(): void
    {
        $request = Request::create('/test');

        $response = $this->middleware->handle($request, function () {
            return new Response();
        });

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertStringContainsString('No token provided', $response->getContent());
    }

    public function test_rejects_invalid_token(): void
    {
        $request = Request::create('/test');
        $request->headers->set('Authorization', 'Bearer invalid.token');

        $response = $this->middleware->handle($request, function () {
            return new Response();
        });

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertStringContainsString('Invalid token', $response->getContent());
    }
}
