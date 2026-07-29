#!/usr/bin/env bash
set -euo pipefail

[ -f .env ] || cp .env.example .env

docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer install

./vendor/bin/sail up -d

grep -q '^APP_KEY=base64:' .env || ./vendor/bin/sail artisan key:generate

echo "waiting for mysql..."
until ./vendor/bin/sail artisan migrate --force >/dev/null 2>&1; do sleep 3; done

echo "app: http://localhost:$(grep '^APP_PORT=' .env | cut -d= -f2)"
echo "pma: http://localhost:8080"
