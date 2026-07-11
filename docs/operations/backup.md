# Backup Runbook

## Purpose

This runbook describes the production backup workflow for MySQL and private storage files.

## Scripts

- `scripts/backup/mysql-backup.sh`
- `scripts/backup/files-backup.sh`
- `scripts/backup/verify-backup.sh`

## Environment variables

- `BACKUP_ROOT`
- `BACKUP_RETENTION_DAYS`
- `BACKUP_MYSQL_DATABASE`
- `BACKUP_MYSQL_HOST`
- `BACKUP_MYSQL_PORT`
- `BACKUP_MYSQL_USER`
- `BACKUP_MYSQL_PASSWORD`
- `BACKUP_STORAGE_PATH`
- `BACKUP_LOG_FILE`

## Recommended sequence

1. Run the MySQL backup script.
2. Run the files backup script.
3. Run the verification script.
4. Check `storage/logs/backup.log` for errors.

## Notes

- Backups are stored under `storage/app/backups` by default.
- The scripts rotate old backup folders based on `BACKUP_RETENTION_DAYS`.
- Private files are backed up from `storage/app/private` by default.
