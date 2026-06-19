#!/bin/bash
# DSM Marketing Intelligence Platform - Deploy Script
# Jalankan script ini di server cPanel via SSH Terminal

echo "========================================="
echo "  DSM Marketing Intelligence - Deploy"
echo "========================================="

# 1. Install dependencies
echo "[1/6] Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 2. Copy .env jika belum ada
echo "[2/6] Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "  --> .env dibuat dari .env.example"
    echo "  --> PENTING: Edit .env dan isi DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL, APP_KEY"
fi

# 3. Generate APP_KEY jika kosong
if grep -q "APP_KEY=$" .env; then
    php artisan key:generate
    echo "  --> APP_KEY berhasil digenerate"
fi

# 4. Migrate database
echo "[3/6] Running database migrations..."
php artisan migrate --force

# 5. Seed database (hanya jika tabel users kosong)
echo "[4/6] Checking seeder..."
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null | tail -1)
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    php artisan db:seed --force
    echo "  --> Database berhasil di-seed"
else
    echo "  --> Data sudah ada, skip seeder"
fi

# 6. Optimize & cache
echo "[5/6] Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Set permissions
echo "[6/6] Setting permissions..."
chmod -R 775 storage bootstrap/cache
chmod -R 644 storage/logs

echo ""
echo "========================================="
echo "  Deploy selesai!"
echo "  Akun login default:"
echo "  Admin    : admin@dsm.co.id / password"
echo "  PIC      : sinta@dsm.co.id / password"
echo "  Viewer   : viewer@dsm.co.id / password"
echo "========================================="
