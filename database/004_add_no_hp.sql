-- Nomor HP pembeli disimpan di tabel users
-- Jalankan: psql -U postgres -d go_kantin -f database/004_add_no_hp.sql
ALTER TABLE users ADD COLUMN IF NOT EXISTS no_hp VARCHAR(20);