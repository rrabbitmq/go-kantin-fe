<?php

namespace App\Core;

final class Rbac
{
    public static function hasRole(array $roles, string $role): bool
    {
        return in_array($role, $roles, true);
    }

    public static function hasAnyRole(array $roles, array $required): bool
    {
        foreach ($required as $role) {
            if (self::hasRole($roles, $role)) {
                return true;
            }
        }

        return false;
    }

    public static function can(array $permissions, string $permission): bool
    {
        return in_array($permission, $permissions, true);
    }

    public static function primaryRole(array $roles): ?string
    {
        foreach (['super_admin', 'penjual', 'pembeli'] as $role) {
            if (self::hasRole($roles, $role)) {
                return $role;
            }
        }

        return $roles[0] ?? null;
    }
}
