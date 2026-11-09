#!/bin/bash
# ══════════════════════════════════════════════════════════════
#  install.sh — Setup awal Sistem Administrasi Digital Ilmu Komunikasi
#  Jalankan: bash install.sh
# ══════════════════════════════════════════════════════════════

set -e

echo ""
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║   SISTEM ADMINISTRASI DIGITAL ILMU KOMUNIKASI               ║"
echo "║   Setup Awal                                                 ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# ── Cek PHP ────────────────────────────────────────────────────
if ! command -v php &> /dev/null; then
    echo "✗ PHP tidak ditemukan. Pastikan PHP 8.4 sudah terinstall."
    exit 1
fi
echo "✓ PHP $(php -r 'echo PHP_VERSION;') ditemukan"

# ── Cek Composer ───────────────────────────────────────────────
if ! command -v composer &> /dev/null; then
    echo "✗ Composer tidak ditemukan. Install dari https://getcomposer.org"
    exit 1
fi
echo "✓ Composer ditemukan"

# ── Cek .env ───────────────────────────────────────────────────
if [ ! -f ".env" ]; then
    echo ""
    echo "→ Menyalin .env.example ke .env..."
    cp .env.example .env
    echo "✓ File .env berhasil dibuat"
    echo ""
    echo "⚠  PENTING: Edit file .env terlebih dahulu!"
    echo "   Isi nilai berikut sebelum melanjutkan:"
    echo "   - DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
    echo "   - APP_URL"
    echo ""
    read -p "Tekan ENTER setelah selesai mengedit .env..."
fi

# ── Generate APP_KEY ────────────────────────────────────────────
if grep -q "^APP_KEY=$" .env || grep -q "^APP_KEY=\"\"" .env; then
    echo ""
    echo "→ Generate APP_KEY..."
    php artisan key:generate --ansi
    echo "✓ APP_KEY berhasil dibuat"
fi

# ── Composer Install ────────────────────────────────────────────
echo ""
echo "→ Menginstall dependencies PHP..."
composer install --no-interaction --optimize-autoloader 2>&1 | tail -5
echo "✓ Dependencies berhasil diinstall"

# ── NPM Build ──────────────────────────────────────────────────
if command -v npm &> /dev/null && [ -f "package.json" ]; then
    echo ""
    echo "→ Menginstall dependencies NPM..."
    npm install --silent
    echo "→ Build assets..."
    npm run build
    echo "✓ Assets berhasil dibuild"
fi

# ── Storage Link ────────────────────────────────────────────────
echo ""
echo "→ Membuat storage link..."
php artisan storage:link --quiet 2>/dev/null || true
echo "✓ Storage link berhasil"

# ── Jalankan Installer ─────────────────────────────────────────
echo ""
php artisan aplikasi:install

