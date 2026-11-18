<?php

namespace App\Models;

use App\Core\ApiClient;

final class Antrian
{
    public const KAPASITAS_SLOT = 20;

    public static function slots(): array
    {
        // Belum ada tabel slot antrian di DDL — fallback demo.
        return [
            ['id' => 1, 'slot_label' => '11.00–11.30', 'slot_value' => '11.00-11.30', 'queue_count' => 8, 'is_current' => false, 'sort_order' => 1],
            ['id' => 2, 'slot_label' => '11.30–12.00', 'slot_value' => '11.30-12.00', 'queue_count' => 18, 'is_current' => false, 'sort_order' => 2],
            ['id' => 3, 'slot_label' => '12.00–12.30', 'slot_value' => '12.00-12.30', 'queue_count' => 35, 'is_current' => true, 'sort_order' => 3],
            ['id' => 4, 'slot_label' => '12.30–13.00', 'slot_value' => '12.30-13.00', 'queue_count' => 22, 'is_current' => false, 'sort_order' => 4],
            ['id' => 5, 'slot_label' => '13.00–13.30', 'slot_value' => '13.00-13.30', 'queue_count' => 12, 'is_current' => false, 'sort_order' => 5],
            ['id' => 6, 'slot_label' => '13.30–14.00', 'slot_value' => '13.30-14.00', 'queue_count' => 6, 'is_current' => false, 'sort_order' => 6],
        ];
    }

    public static function current(): ?array
    {
        foreach (self::slots() as $slot) {
            if (!empty($slot['is_current'])) {
                return $slot;
            }
        }

        $slots = self::slots();
        return $slots[0] ?? null;
    }

    /** Kepadatan slot 30 menit saat ini dari API backend. */
    public static function kepadatan(): array
    {
        $fallback = self::fallbackKepadatan();
        $res = ApiClient::get('/antrian/kepadatan');
        if (!$res['ok'] || !is_array($res['data'])) {
            return $fallback;
        }

        $data = $res['data'];
        $aktif = (int) ($data['aktif'] ?? 0);
        $kapasitas = (int) ($data['kapasitas'] ?? self::KAPASITAS_SLOT);
        $persen = (int) ($data['persen'] ?? 0);
        $label = (string) ($data['label'] ?? \gk_queue_density_label($persen));

        return [
            'aktif' => $aktif,
            'kapasitas' => $kapasitas > 0 ? $kapasitas : self::KAPASITAS_SLOT,
            'persen' => max(0, min(100, $persen)),
            'label' => $label,
            'slot_label' => (string) ($data['slot_label'] ?? $fallback['slot_label']),
            'pesan' => (string) ($data['pesan'] ?? \gk_queue_density_message($label)),
        ];
    }

    private static function fallbackKepadatan(): array
    {
        $tz = new \DateTimeZone('Asia/Jakarta');
        $now = new \DateTimeImmutable('now', $tz);
        $minute = (int) $now->format('i');
        $slotStartMinute = $minute < 30 ? 0 : 30;
        $slotStart = $now->setTime((int) $now->format('H'), $slotStartMinute, 0);
        $slotEnd = $slotStart->modify('+30 minutes');
        $label = 'Sepi';

        return [
            'aktif' => 0,
            'kapasitas' => self::KAPASITAS_SLOT,
            'persen' => 0,
            'label' => $label,
            'slot_label' => $slotStart->format('H.i') . '–' . $slotEnd->format('H.i'),
            'pesan' => \gk_queue_density_message($label),
        ];
    }
}
