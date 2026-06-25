<?php
abstract class TestCase extends PHPUnit\Framework\TestCase
{
    protected function roleMappingProvider(): array
    {
        return [
            [1, 'admin'],
            [2, 'employee'],
            [3, 'manager'],
        ];
    }

    protected function mapUserRole(int $role_id): string
    {
        return $role_id === 1 ? 'admin' : ($role_id === 3 ? 'manager' : 'employee');
    }
}
