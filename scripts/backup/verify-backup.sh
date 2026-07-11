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

mkdir -p "$(dirname "${LOG_FILE}")"
exec >>"${LOG_FILE}" 2>&1

MYSQL_BACKUP="$(find "${BACKUP_ROOT}/mysql" -type f -name '*.sql.gz' 2>/dev/null | sort | tail -n 1 || true)"
FILES_BACKUP="$(find "${BACKUP_ROOT}/files" -type f -name '*.tar.gz' 2>/dev/null | sort | tail -n 1 || true)"

if [[ -z "${MYSQL_BACKUP}" ]]; then
    echo "MySQL backup was not found."
    exit 1
fi

if [[ -z "${FILES_BACKUP}" ]]; then
    echo "Files backup was not found."
    exit 1
fi

gzip -t "${MYSQL_BACKUP}"
tar -tzf "${FILES_BACKUP}" >/dev/null

if [[ -f "${MYSQL_BACKUP}.sha256" ]]; then
    (cd "$(dirname "${MYSQL_BACKUP}")" && sha256sum -c "$(basename "${MYSQL_BACKUP}").sha256")
fi

if [[ -f "${FILES_BACKUP}.sha256" ]]; then
    (cd "$(dirname "${FILES_BACKUP}")" && sha256sum -c "$(basename "${FILES_BACKUP}").sha256")
fi

echo "Backup verification completed successfully."
