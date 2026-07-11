# Operations

This folder contains the production operations runbooks for the NDT web application.

Included documents:

- `health-check.md` - public health-check endpoint and its response shape;
- `backup.md` - how to create and verify database and files backups;
- `restore.md` - how to restore the application from backup;
- `production-checklist.md` - pre-production checklist for deployment.

Operational scripts live under `scripts/backup/` and the cron / Supervisor examples live under `deploy/` and `docker/supervisor/`.
