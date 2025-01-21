<?php

namespace Example\CrmExample\Tests\Unit\Services;

use Example\CrmExample\Services\CallService;
use Example\CrmExample\Exceptions\CallFailedException;
use PHPUnit\Framework\TestCase;

class CallServiceTest extends TestCase
{
    private CallService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CallService();
    }

    public function test_returns_valid_status(): void
    {
        $status = $this->service->initiateCall('+61412345678');
        $this->assertContains($status, ['successful', 'busy', 'failed']);
    }

    public function test_validates_phone_number(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->initiateCall('invalid');
    }

    public function test_simulates_network_latency(): void
    {
        $start = microtime(true);
        $this->service->initiateCall('+61412345678');
        $duration = microtime(true) - $start;

        $this->assertGreaterThan(0.1, $duration); // At least 100ms delay
        $this->assertLessThan(0.6, $duration); // At most 500ms delay
    }
} 