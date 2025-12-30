#!/bin/bash

set -e

DEPLOY_ENV=${1:-staging}
DEPLOY_DIR="/var/www/${DEPLOY_ENV}.muucmf.cc"
BACKUP_DIR="${DEPLOY_DIR}/backups/$(date +%Y%m%d_%H%M%S)"
RELEASE_DIR="${DEPLOY_DIR}/releases/$(date +%Y%m%d_%H%M%S)"
CURRENT_DIR="${DEPLOY_DIR}/current"
PACKAGE_FILE=${2:-""}

echo "========================================="
echo "MuuCmf T6 Deployment Script"
echo "Environment: ${DEPLOY_ENV}"
echo "========================================="

if [ -z "$PACKAGE_FILE" ]; then
    echo "Error: Package file not specified"
    echo "Usage: $0 <environment> <package_file>"
    exit 1
fi

if [ ! -f "$PACKAGE_FILE" ]; then
    echo "Error: Package file not found: $PACKAGE_FILE"
    exit 1
fi

echo "[1/7] Creating backup..."
mkdir -p "$BACKUP_DIR"
if [ -L "$CURRENT_DIR" ]; then
    cp -r "$(readlink -f $CURRENT_DIR)" "$BACKUP_DIR/" || true
    echo "Backup created at: $BACKUP_DIR"
else
    echo "Warning: No current release to backup"
fi

echo "[2/7] Creating release directory..."
mkdir -p "$RELEASE_DIR"

echo "[3/7] Extracting package..."
tar -xzf "$PACKAGE_FILE" -C "$RELEASE_DIR"

echo "[4/7] Copying environment file..."
if [ -f "${RELEASE_DIR}/.env.${DEPLOY_ENV}" ]; then
    cp "${RELEASE_DIR}/.env.${DEPLOY_ENV}" "${RELEASE_DIR}/.env"
    echo "Environment file copied"
else
    echo "Warning: .env.${DEPLOY_ENV} not found"
fi

echo "[5/7] Installing dependencies..."
cd "$RELEASE_DIR"
composer install --no-dev --optimize-autoloader --no-interaction

echo "[6/7] Running migrations..."
php think migrate:run --force || true

echo "[6/7] Clearing cache..."
php think cache:clear || true
php think queue:restart || true

echo "[7/7] Switching to new release..."
ln -snf "$RELEASE_DIR" "$CURRENT_DIR"

echo "Setting permissions..."
chmod -R 755 "${RELEASE_DIR}/runtime"
chmod -R 755 "${RELEASE_DIR}/public/uploads"
chown -R www-data:www-data "$DEPLOY_DIR"

echo "========================================="
echo "Deployment completed successfully!"
echo "Release directory: $RELEASE_DIR"
echo "Current link: $CURRENT_DIR -> $(readlink -f $CURRENT_DIR)"
echo "========================================="

echo "Cleaning old releases (keeping last 5)..."
cd "${DEPLOY_DIR}/releases"
ls -t | tail -n +6 | xargs -r rm -rf

echo "Cleaning old backups (keeping last 10)..."
cd "${DEPLOY_DIR}/backups"
ls -t | tail -n +11 | xargs -r rm -rf

echo "========================================="
echo "Deployment finished!"
echo "========================================="
