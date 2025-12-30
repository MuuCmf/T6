#!/bin/bash

set -e

DEPLOY_ENV=${1:-production}
DEPLOY_DIR="/var/www/${DEPLOY_ENV}.muucmf.cc"
CURRENT_DIR="${DEPLOY_DIR}/current"
TARGET_RELEASE=${2:-""}

echo "========================================="
echo "MuuCmf T6 Rollback Script"
echo "Environment: ${DEPLOY_ENV}"
echo "========================================="

if [ -z "$TARGET_RELEASE" ]; then
    echo "Finding previous release..."
    TARGET_RELEASE=$(ls -t "${DEPLOY_DIR}/releases" | head -2 | tail -1)
fi

if [ -z "$TARGET_RELEASE" ]; then
    echo "Error: No previous release found"
    exit 1
fi

TARGET_RELEASE_PATH="${DEPLOY_DIR}/releases/${TARGET_RELEASE}"

if [ ! -d "$TARGET_RELEASE_PATH" ]; then
    echo "Error: Target release not found: $TARGET_RELEASE_PATH"
    exit 1
fi

echo "Rolling back to release: $TARGET_RELEASE"
echo "Path: $TARGET_RELEASE_PATH"

read -p "Are you sure you want to rollback? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo "Rollback cancelled"
    exit 0
fi

echo "[1/3] Switching to previous release..."
ln -snf "$TARGET_RELEASE_PATH" "$CURRENT_DIR"

echo "[2/3] Clearing cache..."
cd "$CURRENT_DIR"
php think cache:clear || true
php think queue:restart || true

echo "[3/3] Restarting services..."
systemctl restart php-fpm || true
systemctl restart nginx || true

echo "========================================="
echo "Rollback completed successfully!"
echo "Current release: $TARGET_RELEASE"
echo "========================================="
