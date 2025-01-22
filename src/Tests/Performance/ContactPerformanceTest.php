<?php

namespace Example\CrmContactModule\Tests\Performance;

use Example\CrmContactModule\Services\ContactService;
use Example\CrmContactModule\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class ContactPerformanceTest extends TestCase
{
    private ContactService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(ContactService::class);
    }

    public function test_bulk_contact_creation(): void
    {
        $startTime = microtime(true);
        $count = 1000;

        DB::beginTransaction();
        try {
            for ($i = 0; $i < $count; $i++) {
                $this->service->createContact([
                    'first_name' => "Test$i",
                    'last_name' => 'User',
                    'phones' => [['number' => '+61412345678']],
                    'emails' => [['email' => "test$i@example.com"]]
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $duration = microtime(true) - $startTime;
        $avgTime = $duration / $count;

        // Assert performance metrics
        $this->assertLessThan(
            0.01, // 10ms per contact
            $avgTime,
            "Contact creation took too long (avg: {$avgTime}s per contact)"
        );
    }

    public function test_search_performance(): void
    {
        // Create test data
        $this->createTestContacts(1000);

        $startTime = microtime(true);

        // Test search by phone
        $contacts = $this->service->getAllContacts([
            'phone' => '123456'
        ]);

        $duration = microtime(true) - $startTime;

        // Assert search performance
        $this->assertLessThan(
            0.1, // 100ms max
            $duration,
            "Search took too long ({$duration}s)"
        );
    }

    public function test_concurrent_updates(): void
    {
        $contact = $this->service->createContact([
            'first_name' => 'Test',
            'last_name' => 'User',
            'phones' => [['number' => '+61412345678']]
        ]);

        $startTime = microtime(true);

        // Simulate concurrent updates
        $promises = [];
        for ($i = 0; $i < 10; $i++) {
            $promises[] = async(function() use ($contact) {
                return $this->service->updateContact($contact['id'], [
                    'first_name' => 'Updated' . uniqid()
                ]);
            });
        }

        await($promises);
        $duration = microtime(true) - $startTime;

        // Assert concurrency handling
        $this->assertLessThan(
            1.0, // 1 second max for 10 concurrent updates
            $duration,
            "Concurrent updates took too long ({$duration}s)"
        );
    }

    private function createTestContacts(int $count): void
    {
        DB::beginTransaction();
        try {
            for ($i = 0; $i < $count; $i++) {
                $this->service->createContact([
                    'first_name' => "Test$i",
                    'last_name' => 'User',
                    'phones' => [['number' => "+614123456$i"]],
                    'emails' => [['email' => "test$i@example.com"]]
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
