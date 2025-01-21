<?php

namespace Example\CrmExample\Services\Auth;

class JwtParser
{
    /**
     * Mock implementation of JWT token decoder
     *
     * @param string $token
     * @return array{tenant_id: int, user_id: int}
     * @throws \RuntimeException
     */
    public function decode(string $token): array
    {
        // In a real implementation, this would validate and decode the JWT
        // For this example, we'll parse a simple base64 encoded payload
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                throw new \RuntimeException('Invalid token format');
            }

            $payload = json_decode(base64_decode($parts[1]), true);
            
            if (!isset($payload['tenant_id']) || !isset($payload['user_id'])) {
                throw new \RuntimeException('Invalid token payload');
            }

            return [
                'tenant_id' => (int) $payload['tenant_id'],
                'user_id' => (int) $payload['user_id']
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to decode token: ' . $e->getMessage());
        }
    }

    /**
     * Mock implementation of JWT token encoder
     * 
     * @param array{tenant_id: int, user_id: int} $payload
     */
    public function encode(array $payload): string
    {
        // In a real implementation, this would properly sign the JWT
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode($payload));
        $signature = base64_encode('mock-signature');

        return "$header.$payload.$signature";
    }
} 