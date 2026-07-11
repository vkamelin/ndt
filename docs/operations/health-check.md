# Health Check

## Endpoint

`GET /health`

## Response

The endpoint returns a minimal JSON payload with:

- overall status;
- check timestamp;
- database status;
- Redis status;
- storage status.

## Rules

- the endpoint must not expose secrets or internal configuration values;
- it should return `200` when all checks pass;
- it should return `503` when any check fails.
