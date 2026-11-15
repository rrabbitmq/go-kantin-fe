<?php

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    protected function flash(string $key, $value): void
    {
        gk_flash_set($key, $value);
    }

    protected function requireAuth(): void
    {
        Auth::requireAuth();
    }

    protected function requireGuest(): void
    {
        Auth::requireGuest();
    }

    protected function requireRole(string ...$roles): void
    {
        Auth::requireRole(...$roles);
    }

    protected function renderAdmin(string $view, array $data): void
    {
        $data['currentUser'] = $data['currentUser'] ?? $this->currentUserForLayout();
        $data['standMeta'] = $data['standMeta'] ?? [
            'nama' => 'Stand Kantin JTI',
            'lokasi' => 'Kantin JTI • Lantai 8',
            'jam' => '08.00 - 15.00 WIB',
            'status_buka' => true,
            'role_label' => Auth::hasRole('super_admin') ? 'Super Admin' : 'Kasir Stand Terpadu',
        ];
        $data['queueBadge'] = $data['queueBadge'] ?? (int) ($data['queueCount'] ?? 4);
        $data['flashSuccess'] = $data['flashSuccess'] ?? gk_flash('success');
        $data['flashError'] = $data['flashError'] ?? gk_flash('error');

        $this->render($view, $data, 'layouts/admin');
    }

    protected function currentUserForLayout(): array
    {
        $auth = Auth::user();
        if (!$auth) {
            return [
                'id' => 0,
                'name' => 'Tamu',
                'username' => '',
                'student_id' => '-',
                'avatar_letter' => 'T',
            ];
        }

        return [
            'id' => (int) $auth['id_user'],
            'name' => $auth['username'],
            'username' => $auth['username'],
            'student_id' => 'USR-' . $auth['id_user'],
            'avatar_letter' => gk_avatar_letter($auth['username']),
        ];
    }
}
