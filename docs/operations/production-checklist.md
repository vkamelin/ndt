# Production Checklist

## Environment

- `APP_ENV=production`
- `APP_DEBUG=false`
- HTTPS is enabled
- production database credentials are set
- Redis is configured
- private storage is configured

## Application

- migrations are applied;
- base roles and reference data are seeded;
- queue workers are running under Supervisor;
- scheduler is running via cron;
- private files are not publicly exposed;
- backup scripts are available on the server;
- restore instructions are available to operators.

## Verification

- `GET /health` responds successfully;
- database access is healthy;
- Redis access is healthy;
- storage access is healthy;
- backup verification succeeds;
- queue worker logs are being written;
- scheduler logs are being written;
- backup logs are being written.
