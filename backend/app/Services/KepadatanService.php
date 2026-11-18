<?php

namespace App\Services;

use App\Core\Database;

/** Kepadatan antrian per slot 30 menit (Asia/Jakarta). */
final class KepadatanService
{
    public const KAPASITAS_SLOT = 20;

    private const AKTIF = [
        'menunggu_pembayaran',
        'dibayar',
        'diproses',
        'siap_diambil',
    ];

    public static function saatIni(): array
    {
        $tz = new \DateTimeZone('Asia/Jakarta');
        $now = new \DateTimeImmutable('now', $tz);

        $minute = (int) $now->format('i');
        $slotStartMinute = $minute < 30 ? 0 : 30;
        $slotStart = $now->setTime((int) $now->format('H'), $slotStartMinute, 0);
        $slotEnd = $slotStart->modify('+30 minutes');

        $aktifStatuses = "'" . implode("','", self::AKTIF) . "'";
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) AS n
             FROM transaksi t
             WHERE t.status_transaksi IN ($aktifStatuses)
               AND (t.tgl_transaksi AT TIME ZONE 'Asia/Jakarta') >= :awal
               AND (t.tgl_transaksi AT TIME ZONE 'Asia/Jakarta') < :akhir"
        );
        $stmt->execute([
            ':awal' => $slotStart->format('Y-m-d H:i:s'),
            ':akhir' => $slotEnd->format('Y-m-d H:i:s'),
        ]);
        $aktif = (int) $stmt->fetchColumn();
        $persen = min(100, (int) round(($aktif / self::KAPASITAS_SLOT) * 100));
        $label = self::labelDariPersen($persen);

        return [
            'aktif' => $aktif,
            'kapasitas' => self::KAPASITAS_SLOT,
            'persen' => $persen,
            'label' => $label,
            'slot_label' => $slotStart->format('H.i') . '–' . $slotEnd->format('H.i'),
            'pesan' => self::pesanDariLabel($label),
        ];
    }

    public static function labelDariPersen(int $persen): string
    {
        if ($persen >= 100) {
            return 'Penuh';
        }
        if ($persen >= 81) {
            return 'Ramai';
        }
        if ($persen >= 41) {
            return 'Sedang';
        }
        return 'Sepi';
    }

    public static function pesanDariLabel(string $label): string
    {
        return match ($label) {
            'Penuh' => 'Kapasitas pesanan sudah penuh',
            'Ramai' => 'Kapasitas pesanan hampir penuh',
            default => 'Kapasitas pesanan masih tersedia',
        };
    }
}
