#!/bin/bash
# ─────────────────────────────────────────────────────────────────
#  deploy.sh — jalankan di VPS setiap kali ada update dari GitHub
#  Usage: bash deploy.sh
# ─────────────────────────────────────────────────────────────────
set -e

echo "╔══════════════════════════════════════╗"
echo "║   E-ADMINISTRASI — DEPLOY SCRIPT     ║"
echo "╚══════════════════════════════════════╝"

# ── 1. Pull update terbaru ───────────────────────────────────────
echo ""
echo "[1/6] Git pull..."
git pull origin main

# ── 2. Build ulang image (hanya rebuild jika ada perubahan) ──────
echo ""
echo "[2/6] Build Docker image..."
docker compose build --no-cache app queue

# ── 3. Jalankan container baru tanpa downtime ────────────────────
echo ""
echo "[3/6] Restart containers..."
docker compose up -d --force-recreate app queue nginx

# ── 4. Jalankan migration ────────────────────────────────────────
echo ""
echo "[4/6] Run migrations..."
docker compose exec app php artisan migrate --force

# ── 5. Optimize cache ────────────────────────────────────────────
echo ""
echo "[5/6] Optimize..."
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app php artisan event:cache

# ── 6. Fix storage permissions & copy icons ──────────────────────
echo ""
echo "[6/6] Fix permissions & icons..."
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage/app/private
docker compose cp ./public/icons app:/var/www/html/public/
docker compose cp ./storage/app/private/templates app:/var/www/html/storage/app/private/
docker compose exec app chown -R www-data:www-data storage/app/private/templates
docker compose cp ./public/vendor app:/var/www/html/public/ 2>/dev/null || true

echo ""
echo "✓ Deploy selesai!"
echo "  App  : $(docker compose ps app --format '{{.Status}}')"
echo "  DB   : $(docker compose ps db --format '{{.Status}}')"
echo "  Nginx: $(docker compose ps nginx --format '{{.Status}}')"
