-- =========================================================
-- Pengaturan stand (jam operasional + status buka/tutup)
-- Satu stand = satu akun penjual; tanpa role/akun kasir terpisah.
-- =========================================================

BEGIN;

ALTER TABLE profil_penjual
    ADD COLUMN IF NOT EXISTS jam_buka TIME,
    ADD COLUMN IF NOT EXISTS jam_tutup TIME,
    ADD COLUMN IF NOT EXISTS status_buka BOOLEAN NOT NULL DEFAULT TRUE;

UPDATE profil_penjual
SET jam_buka = COALESCE(jam_buka, TIME '08:00'),
    jam_tutup = COALESCE(jam_tutup, TIME '15:00')
WHERE jam_buka IS NULL OR jam_tutup IS NULL;

COMMIT;
