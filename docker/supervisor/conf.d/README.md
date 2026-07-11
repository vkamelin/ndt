# Supervisor config examples

These examples are intended for production servers where Supervisor manages Laravel queue workers.

Recommended usage:

- copy `queue-worker.conf` to the target server;
- adjust the `user`, `numprocs`, and `command` values if the deployment path differs;
- place the resulting file in the active Supervisor `conf.d` directory;
- reload Supervisor after deployment.

Queue worker logs are written to `storage/logs/queue-worker.log` so they stay alongside the application logs.
