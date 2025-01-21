<?php

if (!function_exists('tenant')) {
    /**
     * Get tenant context information
     *
     * @param string|null $key Specific tenant attribute to retrieve
     * @return mixed
     */
    function tenant(?string $key = null): mixed
    {
        if ($key === null) {
            return app('tenant.id');
        }

        return match($key) {
            'id' => app('tenant.id'),
            default => null
        };
    }
}

if (!function_exists('auth')) {
    /**
     * Mock auth helper
     */
    function auth(): object
    {
        return new class {
            public function id(): ?int
            {
                return app('user.id');
            }
        };
    }
} 