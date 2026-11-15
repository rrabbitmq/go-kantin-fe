-- =========================================================
-- SEED DATA KATALOG (kategori, profil penjual, produk, varian)
-- Jalankan setelah 001 dan 002:
--   psql -U postgres -d go_kantin -f database/005_seed_produk.sql
-- Aman dijalankan berulang (tidak membuat data ganda).
-- =========================================================
 
BEGIN;
 
INSERT INTO kategori_penjual (nama_kategori, deskripsi) VALUES
    ('Makanan Berat', 'Nasi, mie, dan lauk'),
    ('Minuman', 'Aneka minuman'),
    ('Snack', 'Makanan ringan')
ON CONFLICT (nama_kategori) DO NOTHING;
 
INSERT INTO kategori_produk (nama_kategori, deskripsi) VALUES
    ('Makanan', 'Nasi, mie, dan lauk pauk'),
    ('Minuman', 'Aneka minuman dingin dan panas'),
    ('Snack', 'Camilan dan kue')
ON CONFLICT (nama_kategori) DO NOTHING;
 
-- Profil usaha untuk akun demo 'penjual'
INSERT INTO profil_penjual
    (id_user, nama_usaha, id_kategori_penjual, no_hp, alamat, disetujui_oleh, tgl_konfirmasi)
SELECT u.id_user,
       'Kantin Bu Sari',
       (SELECT id_kategori_penjual FROM kategori_penjual WHERE nama_kategori = 'Makanan Berat'),
       '081234567890',
       'Kantin Pusat',
       a.id_user,
       CURRENT_TIMESTAMP
FROM users u
CROSS JOIN users a
WHERE u.username = 'penjual' AND a.username = 'admin'
ON CONFLICT (id_user) DO NOTHING;
 
-- Produk + satu varian 'Reguler' per produk
WITH data (nama, kategori, deskripsi, gambar, harga, stok) AS (
    VALUES
    ('Nasi Goreng Spesial', 'Makanan', 'Nasi goreng dengan telur dan ayam suwir', '/img/menu/1.jpg', 15000, 50),
    ('Mie Ayam Bakso',      'Makanan', 'Mie ayam dengan bakso sapi',               '/img/menu/2.jpg', 13000, 40),
    ('Ayam Geprek',         'Makanan', 'Ayam goreng tepung dengan sambal geprek',  '/img/menu/3.jpg', 14000, 40),
    ('Soto Ayam',           'Makanan', 'Soto ayam kuah bening dengan nasi',        '/img/menu/4.jpg', 12000, 30),
    ('Tahu Isi Pedas',      'Makanan', 'Tahu isi sayur dengan cabai rawit',        '/img/menu/6.jpg',  9000, 40),
    ('Es Teh Manis',        'Minuman', 'Teh manis dingin segar',                   '/img/menu/7.jpg',  4000, 100),
    ('Kopi Susu',           'Minuman', 'Kopi susu gula aren',                      '/img/menu/8.jpg',  8000, 60),
    ('Air Mineral',         'Minuman', 'Air mineral botol 600 ml',                 '/img/menu/9.jpg',  4000, 100),
    ('Jus Jeruk',           'Minuman', 'Jus jeruk peras segar',                    '/img/menu/10.jpg', 8000, 40),
    ('Keju Goreng Madu',    'Snack',   'Keju goreng renyah dengan siraman madu',   '/img/menu/1.jpg', 14000, 30),
    ('Pisang Goreng',       'Snack',   'Pisang goreng tepung renyah',              '/img/menu/2.jpg',  5000, 50),
    ('Oreo Soft Cake',      'Snack',   'Kue lembut rasa oreo',                     '/img/menu/3.jpg',  3000, 40)
),
pen AS (
    SELECT pp.id_profil_penjual
    FROM profil_penjual pp
    JOIN users u ON u.id_user = pp.id_user
    WHERE u.username = 'penjual'
),
ins AS (
    INSERT INTO produk (id_profil_penjual, id_kategori_produk, nama_produk, deskripsi, gambar)
    SELECT pen.id_profil_penjual, k.id_kategori_produk, d.nama, d.deskripsi, d.gambar
    FROM data d
    CROSS JOIN pen
    JOIN kategori_produk k ON k.nama_kategori = d.kategori
    WHERE NOT EXISTS (
        SELECT 1 FROM produk p
        WHERE p.nama_produk = d.nama AND p.id_profil_penjual = pen.id_profil_penjual
    )
    RETURNING id_produk, nama_produk
)
INSERT INTO varian_produk (id_produk, nama_varian, harga_satuan, stok, batas_stok_minimum)
SELECT ins.id_produk, 'Reguler', d.harga, d.stok, 5
FROM ins
JOIN data d ON d.nama = ins.nama_produk
ON CONFLICT (id_produk, nama_varian) DO NOTHING;
 
COMMIT;