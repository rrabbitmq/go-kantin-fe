<?php

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public static function findByLogin(string $login): ?array
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return null;
        }

        $stmt = $pdo->prepare(
            'SELECT id_user, username, email, password_hash, status_akun
             FROM users
             WHERE username = :login OR email = :login
             LIMIT 1'
        );
        $stmt->execute(['login' => $login]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return null;
        }

        $stmt = $pdo->prepare(
            'SELECT id_user, username, email, status_akun, created_at
             FROM users
             WHERE id_user = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function usernameExists(string $username): bool
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return false;
        }

        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);

        return (bool) $stmt->fetchColumn();
    }

    public static function emailExists(string $email): bool
    {
        $pdo = Database::connection();
        if (!$pdo || $email === '') {
            return false;
        }

        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);

        return (bool) $stmt->fetchColumn();
    }

    public static function createPembeli(string $username, ?string $email, string $password): int
    {
        $pdo = Database::connection();
        if (!$pdo) {
            throw new \RuntimeException('Database tidak tersedia.');
        }

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (username, email, password_hash, status_akun)
                 VALUES (?, ?, ?, \'aktif\')
                 RETURNING id_user'
            );
            $stmt->execute([
                $username,
                $email !== '' ? $email : null,
                password_hash($password, PASSWORD_DEFAULT),
            ]);
            $idUser = (int) $stmt->fetchColumn();

            $roleStmt = $pdo->prepare('SELECT id_role FROM roles WHERE nama_role = \'pembeli\' LIMIT 1');
            $roleStmt->execute();
            $idRole = $roleStmt->fetchColumn();
            if (!$idRole) {
                throw new \RuntimeException('Role pembeli belum di-seed.');
            }

            $ur = $pdo->prepare('INSERT INTO user_roles (id_user, id_role) VALUES (?, ?)');
            $ur->execute([$idUser, $idRole]);

            $cart = $pdo->prepare('INSERT INTO keranjang (id_user) VALUES (?)');
            $cart->execute([$idUser]);

            $pdo->commit();

            return $idUser;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function rolesForUser(int $idUser): array
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return [];
        }

        $stmt = $pdo->prepare(
            'SELECT r.nama_role
             FROM user_roles ur
             JOIN roles r ON r.id_role = ur.id_role
             WHERE ur.id_user = ?
             ORDER BY r.id_role'
        );
        $stmt->execute([$idUser]);

        return array_column($stmt->fetchAll(), 'nama_role');
    }

    public static function permissionsForUser(int $idUser): array
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return [];
        }

        $stmt = $pdo->prepare(
            'SELECT DISTINCT p.nama_permission
             FROM user_roles ur
             JOIN role_permissions rp ON rp.id_role = ur.id_role
             JOIN permissions p ON p.id_permission = rp.id_permission
             WHERE ur.id_user = ?
             ORDER BY p.nama_permission'
        );
        $stmt->execute([$idUser]);

        return array_column($stmt->fetchAll(), 'nama_permission');
    }

    public const STATUSES = ['aktif', 'pending', 'ditolak', 'nonaktif'];

    public static function allWithRoles(): array
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return [];
        }

        $rows = $pdo->query(
            'SELECT u.id_user, u.username, u.email, u.status_akun, u.created_at,
                    COALESCE(string_agg(r.nama_role, \',\' ORDER BY r.id_role), \'\') AS roles,
                    COALESCE(string_agg(r.id_role::text, \',\' ORDER BY r.id_role), \'\') AS role_ids
             FROM users u
             LEFT JOIN user_roles ur ON ur.id_user = u.id_user
             LEFT JOIN roles r ON r.id_role = ur.id_role
             GROUP BY u.id_user
             ORDER BY u.id_user'
        )->fetchAll();

        foreach ($rows as &$row) {
            $row['roles'] = $row['roles'] !== '' ? explode(',', $row['roles']) : [];
            $row['role_ids'] = $row['role_ids'] !== '' ? array_map('intval', explode(',', $row['role_ids'])) : [];
        }

        return $rows;
    }

    public static function usernameTakenByOther(string $username, int $exceptId): bool
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return false;
        }

        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = ? AND id_user <> ? LIMIT 1');
        $stmt->execute([$username, $exceptId]);

        return (bool) $stmt->fetchColumn();
    }

    public static function emailTakenByOther(string $email, int $exceptId): bool
    {
        $pdo = Database::connection();
        if (!$pdo || $email === '') {
            return false;
        }

        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ? AND id_user <> ? LIMIT 1');
        $stmt->execute([$email, $exceptId]);

        return (bool) $stmt->fetchColumn();
    }

    public static function create(string $username, ?string $email, string $password, string $status, array $roleIds): int
    {
        $pdo = Database::connection();
        if (!$pdo) {
            throw new \RuntimeException('Database tidak tersedia.');
        }

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (username, email, password_hash, status_akun)
                 VALUES (?, ?, ?, ?)
                 RETURNING id_user'
            );
            $stmt->execute([
                $username,
                $email !== '' ? $email : null,
                password_hash($password, PASSWORD_DEFAULT),
                $status,
            ]);
            $idUser = (int) $stmt->fetchColumn();

            self::syncRoles($idUser, $roleIds);

            $pdo->commit();

            return $idUser;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function update(int $idUser, string $username, ?string $email, ?string $password, string $status, array $roleIds): void
    {
        $pdo = Database::connection();
        if (!$pdo) {
            throw new \RuntimeException('Database tidak tersedia.');
        }

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET username = ?, email = ?, status_akun = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE id_user = ?'
            );
            $stmt->execute([$username, $email !== '' ? $email : null, $status, $idUser]);

            if ($password !== null && $password !== '') {
                $pw = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id_user = ?');
                $pw->execute([password_hash($password, PASSWORD_DEFAULT), $idUser]);
            }

            self::syncRoles($idUser, $roleIds);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Must be called inside a transaction.
     */
    public static function syncRoles(int $idUser, array $roleIds): void
    {
        $pdo = Database::connection();
        if (!$pdo) {
            throw new \RuntimeException('Database tidak tersedia.');
        }

        $pdo->prepare('DELETE FROM user_roles WHERE id_user = ?')->execute([$idUser]);

        $insert = $pdo->prepare('INSERT INTO user_roles (id_user, id_role) VALUES (?, ?)');
        foreach (array_unique(array_map('intval', $roleIds)) as $idRole) {
            $insert->execute([$idUser, $idRole]);
        }

        if (in_array('pembeli', self::rolesForUser($idUser), true)) {
            $pdo->prepare(
                'INSERT INTO keranjang (id_user)
                 SELECT ? WHERE NOT EXISTS (SELECT 1 FROM keranjang WHERE id_user = ?)'
            )->execute([$idUser, $idUser]);
        }
    }

    public static function delete(int $idUser): void
    {
        $pdo = Database::connection();
        if (!$pdo) {
            throw new \RuntimeException('Database tidak tersedia.');
        }

        $stmt = $pdo->prepare('DELETE FROM users WHERE id_user = ?');
        $stmt->execute([$idUser]);
    }
}
