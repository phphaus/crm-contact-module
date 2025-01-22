<?php

namespace Example\CrmContactModule\Services;

use Example\CrmContactModule\Exceptions\CallFailedException;
use Illuminate\Support\Facades\Http;
use React\Http\Browser;
use React\EventLoop\Factory;

class CallService
{
    /**
     * Mock third-party calling service integration
     * In a real implementation, this would integrate with Twilio, Vonage, etc.
     *
     * @param string $phoneNumber The phone number to call in E.164 format
     * @return string The call status (successful|busy|failed)
     * @throws CallFailedException
     */
    public function initiateCall(string $phoneNumber): string
    {
        // Simulate API call to third-party service
        try {

            // The following is an example of how to use ReactPHP to make asynchronous API calls
            // and combine the results with synchronous code:
            // $this->fetchThings();

            // Simulate network latency - we do this since the above functionality does not actually make a network request
            usleep(random_int(100000, 500000));

            // Simulate different call outcomes
            return match(random_int(1, 4)) {
                1 => 'busy',
                2 => 'failed',
                default => 'successful'
            };
        } catch (\Exception $e) {
            throw new CallFailedException('Failed to initiate call: ' . $e->getMessage());
        }
    }

    /**
     * DUMMY method to demo async/sync behaviour
     */
    private function fetchThings()
    {
        // Simulate API call
        $loop = Factory::create();
        $client = new Browser($loop);

        $deferred = new React\Promise\Deferred(); // Create a deferred promise to return later

        // Call the first two APIs asynchronously
        $promise1 = $client->get('https://api.example.com/endpoint1');
        $promise2 = $client->get('https://api.example.com/endpoint2');

        // Combine promises and proceed when both are resolved
        React\Promise\all([$promise1, $promise2])
            ->then(function (array $responses) use ($client, $deferred) {
                // Handle the results of the first two promises
                $response1 = (string)$responses[0]->getBody();
                $response2 = (string)$responses[1]->getBody();

                // Use the responses to construct the third API call
                return $client->get("https://api.example.com/endpoint3/{$response1}/{$response2}");
            })
            ->then(function (\Psr\Http\Message\ResponseInterface $response) use ($deferred) {
                // Resolve the deferred promise with the third API call's result
                $deferred->resolve((string)$response->getBody());
            })
            ->otherwise(function (\Exception $e) use ($deferred) {
                // Reject the deferred promise in case of error
                $deferred->reject($e->getMessage());
            });

        $loop->run();

        // Return the deferred promise
        return $deferred->promise();
    }
}
