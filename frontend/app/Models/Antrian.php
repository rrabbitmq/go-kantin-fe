<?php

namespace App\Models;

final class Antrian
{
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
}
