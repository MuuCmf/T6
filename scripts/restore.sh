#!/bin/bash

set -e

DEPLOY_ENV=${1:-production}
DEPLOY_DIR="/var/www/${DEPLOY_ENV}.muucmf.cc"
BACKUP_FILE=${2:-""}

echo "========================================="
echo "MuuCmf T6 Restore Script"
echo "Environment: ${DEPLOY_ENV}"
echo "========================================="

if [ -z "$BACKUP_FILE" ]; then
    echo "Error: Backup file not specified"
    echo "Usage: $0 <environment> <backup_file>"
    exit 1
fi

if [ ! -f "$BACKUP_FILE" ]; then
    echo "Error: Backup file not found: $BACKUP_FILE"
    exit 1
fi

echo "Backup file: $BACKUP_FILE"
echo "Size: $(du -h $BACKUP_FILE | cut -f1)"

read -p "Are you sure you want to restore? This will overwrite current data! (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo "Restore cancelled"
    exit 0
fi

TEMP_DIR=$(mktemp -d)
echo "Extracting backup to: $TEMP_DIR"
tar -xzf "$BACKUP_FILE" -C "$TEMP_DIR"

BACKUP_TIMESTAMP=$(basename "$BACKUP_FILE" | sed 's/backup_//' | sed 's/.tar.gz//')
RESTORE_DIR="${TEMP_DIR}/${BACKUP_TIMESTAMP}"

echo "[1/3] Restoring files..."
if [ -d "${RESTORE_DIR}/files" ]; then
    rm -rf "${DEPLOY_DIR}/current"
    cp -r "${RESTORE_DIR}/files" "${DEPLOY_DIR}/current"
    echo "Files restored"
fi

echo "[2/3] Restoring database..."
if [ -f "${RESTORE_DIR}/db_backup.sql" ]; then
    DB_NAME=${3:-muucmf}
    DB_USER=${4:-root}
    DB_PASS=${5:-""}
    
    if [ -n "$DB_PASS" ]; then
        mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "${RESTORE_DIR}/db_backup.sql"
    else
        mysql -u"$DB_USER" "$DB_NAME" < "${RESTORE_DIR}/db_backup.sql"
    fi
    echo "Database restored"
fi

echo "[3/3] Restoring uploads..."
if [ -d "${RESTORE_DIR}/uploads" ]; then
    rm -rf "${DEPLOY_DIR}/current/public/uploads"
    cp -r "${RESTORE_DIR}/uploads" "${DEPLOY_DIR}/current/public/"
    echo "Uploads restored"
fi

echo "Clearing cache..."
cd "${DEPLOY_DIR}/current"
php think cache:clear || true
php think queue:restart || true

echo "Setting permissions..."
chmod -R 755 "${DEPLOY_DIR}/current/runtime"
chmod -R 755 "${DEPLOY_DIR}/current/public/uploads"
chown -R www-data:www-data "$DEPLOY_DIR"

rm -rf "$TEMP_DIR"

echo "========================================="
echo "Restore completed successfully!"
echo "========================================="
