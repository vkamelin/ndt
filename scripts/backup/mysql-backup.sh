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
MYSQL_DIR="${BACKUP_ROOT}/mysql"
TARGET_DIR="${MYSQL_DIR}/${STAMP}"
TARGET_FILE="${TARGET_DIR}/database.sql.gz"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-7}"

mkdir -p "${TARGET_DIR}" "$(dirname "${LOG_FILE}")"
exec >>"${LOG_FILE}" 2>&1

echo "[${STAMP}] Starting MySQL backup."

MYSQL_DUMP_BIN="${MYSQL_DUMP_BIN:-mysqldump}"
MYSQL_HOST="${BACKUP_MYSQL_HOST:-${DB_HOST:-127.0.0.1}}"
MYSQL_PORT="${BACKUP_MYSQL_PORT:-${DB_PORT:-3306}}"
MYSQL_DATABASE="${BACKUP_MYSQL_DATABASE:-${DB_DATABASE:-ndt}}"
MYSQL_USER="${BACKUP_MYSQL_USER:-${DB_USERNAME:-ndt}}"
MYSQL_PASSWORD="${BACKUP_MYSQL_PASSWORD:-${DB_PASSWORD:-}}"

if [[ -z "${MYSQL_PASSWORD}" ]]; then
    "${MYSQL_DUMP_BIN}" \
        --host="${MYSQL_HOST}" \
        --port="${MYSQL_PORT}" \
        --user="${MYSQL_USER}" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        "${MYSQL_DATABASE}" | gzip > "${TARGET_FILE}"
else
    MYSQL_PWD="${MYSQL_PASSWORD}" "${MYSQL_DUMP_BIN}" \
        --host="${MYSQL_HOST}" \
        --port="${MYSQL_PORT}" \
        --user="${MYSQL_USER}" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        "${MYSQL_DATABASE}" | gzip > "${TARGET_FILE}"
fi

sha256sum "${TARGET_FILE}" > "${TARGET_FILE}.sha256"

find "${MYSQL_DIR}" -mindepth 1 -maxdepth 1 -type d -mtime +"${RETENTION_DAYS}" -exec rm -rf {} +

echo "[${STAMP}] MySQL backup finished: ${TARGET_FILE}"
