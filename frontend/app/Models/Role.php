<?php

namespace App\Models;

use App\Core\Database;

final class Role
{
    public const BUILT_IN = ['pembeli', 'penjual', 'super_admin'];

    public static function all(): array
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return [];
        }

        return $pdo->query(
            'SELECT r.id_role, r.nama_role, r.deskripsi,
                    (SELECT COUNT(*) FROM user_roles ur WHERE ur.id_role = r.id_role) AS jumlah_user
             FROM roles r
             ORDER BY r.id_role'
        )->fetchAll();
    }

    public static function find(int $idRole): ?array
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT id_role, nama_role, deskripsi FROM roles WHERE id_role = ?');
        $stmt->execute([$idRole]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function existingIds(array $ids): array
    {
        $pdo = Database::connection();
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$pdo || !$ids) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id_role FROM roles WHERE id_role IN ($placeholders)");
        $stmt->execute($ids);

        return array_map('intval', array_column($stmt->fetchAll(), 'id_role'));
    }

    public static function nameExists(string $nama): bool
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return false;
        }

        $stmt = $pdo->prepare('SELECT 1 FROM roles WHERE nama_role = ? LIMIT 1');
        $stmt->execute([$nama]);

        return (bool) $stmt->fetchColumn();
    }

    public static function create(string $nama, ?string $deskripsi): int
    {
        $pdo = Database::connection();
        if (!$pdo) {
            throw new \RuntimeException('Database tidak tersedia.');
        }

        $stmt = $pdo->prepare('INSERT INTO roles (nama_role, deskripsi) VALUES (?, ?) RETURNING id_role');
        $stmt->execute([$nama, $deskripsi !== '' ? $deskripsi : null]);

        return (int) $stmt->fetchColumn();
    }

    public static function delete(int $idRole): void
    {
        $pdo = Database::connection();
        if (!$pdo) {
            throw new \RuntimeException('Database tidak tersedia.');
        }

        $pdo->prepare('DELETE FROM roles WHERE id_role = ?')->execute([$idRole]);
    }

    public static function allPermissions(): array
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return [];
        }

        return $pdo->query(
            'SELECT id_permission, nama_permission, deskripsi FROM permissions ORDER BY nama_permission'
        )->fetchAll();
    }

    /**
     * @return array<int, int[]> id_role => list of id_permission
     */
    public static function permissionMap(): array
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return [];
        }

        $map = [];
        foreach ($pdo->query('SELECT id_role, id_permission FROM role_permissions')->fetchAll() as $row) {
            $map[(int) $row['id_role']][] = (int) $row['id_permission'];
        }

        return $map;
    }

    public static function syncPermissions(int $idRole, array $permissionIds): void
    {
        $pdo = Database::connection();
        if (!$pdo) {
            throw new \RuntimeException('Database tidak tersedia.');
        }

        $permissionIds = array_values(array_unique(array_map('intval', $permissionIds)));

        $pdo->beginTransaction();

        try {
            $pdo->prepare('DELETE FROM role_permissions WHERE id_role = ?')->execute([$idRole]);

            if ($permissionIds) {
                $placeholders = implode(',', array_fill(0, count($permissionIds), '?'));
                $stmt = $pdo->prepare(
                    "INSERT INTO role_permissions (id_role, id_permission)
                     SELECT ?, id_permission FROM permissions WHERE id_permission IN ($placeholders)"
                );
                $stmt->execute(array_merge([$idRole], $permissionIds));
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
