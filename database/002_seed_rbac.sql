-- =========================================================
-- SEED RBAC + USER DEMO
-- Password semua akun demo: password123
-- Hash: password_hash('password123', PASSWORD_DEFAULT)
-- =========================================================

BEGIN;

INSERT INTO roles (nama_role, deskripsi) VALUES
    ('pembeli', 'Pembeli / mahasiswa'),
    ('penjual', 'Admin penjual / pemilik usaha'),
    ('super_admin', 'Super administrator sistem')
ON CONFLICT (nama_role) DO NOTHING;

INSERT INTO permissions (nama_permission, deskripsi) VALUES
    ('katalog.lihat', 'Melihat katalog dan menu'),
    ('keranjang.kelola', 'Mengelola keranjang belanja'),
    ('pesanan.buat', 'Membuat pesanan'),
    ('pesanan.lihat_milik', 'Melihat pesanan milik sendiri'),
    ('produk.kelola', 'Mengelola produk toko'),
    ('pesanan.kelola_toko', 'Mengelola pesanan toko'),
    ('user.kelola', 'Mengelola pengguna sistem'),
    ('penjual.setujui', 'Menyetujui pendaftaran penjual')
ON CONFLICT (nama_permission) DO NOTHING;

-- Mapping pembeli
INSERT INTO role_permissions (id_role, id_permission)
SELECT r.id_role, p.id_permission
FROM roles r
CROSS JOIN permissions p
WHERE r.nama_role = 'pembeli'
  AND p.nama_permission IN (
      'katalog.lihat',
      'keranjang.kelola',
      'pesanan.buat',
      'pesanan.lihat_milik'
  )
ON CONFLICT DO NOTHING;

-- Mapping penjual
INSERT INTO role_permissions (id_role, id_permission)
SELECT r.id_role, p.id_permission
FROM roles r
CROSS JOIN permissions p
WHERE r.nama_role = 'penjual'
  AND p.nama_permission IN (
      'katalog.lihat',
      'produk.kelola',
      'pesanan.kelola_toko'
  )
ON CONFLICT DO NOTHING;

-- Mapping super_admin (semua permission)
INSERT INTO role_permissions (id_role, id_permission)
SELECT r.id_role, p.id_permission
FROM roles r
CROSS JOIN permissions p
WHERE r.nama_role = 'super_admin'
ON CONFLICT DO NOTHING;

-- Users demo
INSERT INTO users (username, email, password_hash, status_akun)
VALUES
    (
        'pembeli',
        'pembeli@gokantin.local',
        '$2y$10$8TRYd0RIxDHkRBCEbB3NzeZ7bb1nVi.lsNSJCAdYzLPxoML0jAxsG',
        'aktif'
    ),
    (
        'penjual',
        'penjual@gokantin.local',
        '$2y$10$8TRYd0RIxDHkRBCEbB3NzeZ7bb1nVi.lsNSJCAdYzLPxoML0jAxsG',
        'aktif'
    ),
    (
        'admin',
        'admin@gokantin.local',
        '$2y$10$8TRYd0RIxDHkRBCEbB3NzeZ7bb1nVi.lsNSJCAdYzLPxoML0jAxsG',
        'aktif'
    )
ON CONFLICT (username) DO NOTHING;

INSERT INTO user_roles (id_user, id_role)
SELECT u.id_user, r.id_role
FROM users u
JOIN roles r ON r.nama_role = 'pembeli'
WHERE u.username = 'pembeli'
ON CONFLICT DO NOTHING;

INSERT INTO user_roles (id_user, id_role)
SELECT u.id_user, r.id_role
FROM users u
JOIN roles r ON r.nama_role = 'penjual'
WHERE u.username = 'penjual'
ON CONFLICT DO NOTHING;

INSERT INTO user_roles (id_user, id_role)
SELECT u.id_user, r.id_role
FROM users u
JOIN roles r ON r.nama_role = 'super_admin'
WHERE u.username = 'admin'
ON CONFLICT DO NOTHING;

INSERT INTO keranjang (id_user)
SELECT u.id_user
FROM users u
WHERE u.username = 'pembeli'
  AND NOT EXISTS (
      SELECT 1 FROM keranjang k WHERE k.id_user = u.id_user
  );

INSERT INTO kategori_penjual (nama_kategori, deskripsi) VALUES
  ('Makanan', 'Nasi, mie, dan sejenisnya'),
  ('Minuman', 'Aneka minuman'),
  ('Snack', 'Makanan ringan')
ON CONFLICT (nama_kategori) DO NOTHING;

COMMIT;
