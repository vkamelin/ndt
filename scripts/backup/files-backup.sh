#!/usr/bin/env bash

set -euo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
if [[ -f "${APP_ROOT}/.env" ]]; then
    set -a
    # shellcheck disable=SC1090
    . "${APP_ROOT}/.env"
    set +a
fi

BACKUP_ROOT="${BACKUP_ROOT:-${APP_ROOT}/storage/app/backups}"
LOG_FILE="${BACKUP_LOG_FILE:-${APP_ROOT}/storage/logs/backup.log}"
STAMP="$(date +%Y%m%d_%H%M%S)"
FILES_DIR="${BACKUP_ROOT}/files"
SOURCE_DIR="${BACKUP_STORAGE_PATH:-${APP_ROOT}/storage/app/private}"
TARGET_DIR="${FILES_DIR}/${STAMP}"
TARGET_FILE="${TARGET_DIR}/files.tar.gz"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-7}"

mkdir -p "${TARGET_DIR}" "$(dirname "${LOG_FILE}")"
exec >>"${LOG_FILE}" 2>&1

echo "[${STAMP}] Starting files backup."

if [[ ! -d "${SOURCE_DIR}" ]]; then
    echo "Source directory does not exist: ${SOURCE_DIR}"
    exit 1
fi

tar -czf "${TARGET_FILE}" -C "$(dirname "${SOURCE_DIR}")" "$(basename "${SOURCE_DIR}")"
sha256sum "${TARGET_FILE}" > "${TARGET_FILE}.sha256"

find "${FILES_DIR}" -mindepth 1 -maxdepth 1 -type d -mtime +"${RETENTION_DAYS}" -exec rm -rf {} +

echo "[${STAMP}] Files backup finished: ${TARGET_FILE}"
