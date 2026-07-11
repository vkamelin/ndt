# Restore Runbook

## Purpose

This runbook describes the order for restoring the application after a production incident.

## Restore order

1. Put the application into maintenance mode.
2. Stop queue workers.
3. Stop the scheduler job.
4. Restore the database from the latest verified MySQL backup.
5. Restore private files from the latest verified files backup.
6. Run migrations only if they are part of the recovery plan.
7. Rebuild caches if needed.
8. Start queue workers.
9. Re-enable the scheduler.
10. Run the health-check endpoint.

## Database restore

Use the latest verified `database.sql.gz` archive and import it into the target MySQL database.

## Files restore

Extract the latest verified `files.tar.gz` archive and place the private storage tree back into its original path.

## Validation

- `GET /health` returns `200`;
- database responds to a simple query;
- Redis is reachable;
- private storage is reachable;
- the application can serve private file downloads.
