-- Daftar token yang sudah di-logout (dipakai logout.php dan verifyToken)
-- Jalankan: psql -U postgres -d go_kantin -f database/003_token_blacklist.sql
CREATE TABLE IF NOT EXISTS token_blacklist (
    jti VARCHAR(64) PRIMARY KEY,
    expired_at TIMESTAMPTZ NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
 
CREATE INDEX IF NOT EXISTS idx_token_blacklist_expired
    ON token_blacklist(expired_at);
 