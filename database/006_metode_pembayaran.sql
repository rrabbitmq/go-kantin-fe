-- Metode pembayaran: Tunai (bayar saat ambil) dan QRIS
-- Jalankan: psql -U postgres -d go_kantin -f database/006_metode_pembayaran.sql
-- Aman dijalankan berulang.

BEGIN;

INSERT INTO metode_pembayaran (kode_metode, nama_metode, tipe_metode) VALUES
    ('tunai', 'Tunai (bayar saat ambil)', 'tunai'),
    ('qris',  'QRIS',                     'payment_gateway')
ON CONFLICT (kode_metode) DO NOTHING;

-- Pesanan lama yang belum punya data pembayaran dianggap tunai
INSERT INTO pembayaran
    (id_transaksi, id_metode_pembayaran, provider, referensi_provider, jumlah_bayar, status_pembayaran, waktu_bayar)
SELECT t.id_transaksi,
       m.id_metode_pembayaran,
       'manual',
       t.kode_transaksi,
       t.total_harga,
       CASE t.status_transaksi
            WHEN 'selesai'    THEN 'dibayar'
            WHEN 'dibatalkan' THEN 'dibatalkan'
            ELSE 'pending'
       END,
       CASE WHEN t.status_transaksi = 'selesai' THEN COALESCE(t.waktu_selesai, t.tgl_transaksi) END
FROM transaksi t
JOIN metode_pembayaran m ON m.kode_metode = 'tunai'
WHERE NOT EXISTS (SELECT 1 FROM pembayaran p WHERE p.id_transaksi = t.id_transaksi);

COMMIT;