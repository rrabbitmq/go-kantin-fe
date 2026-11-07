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
}
