# E2E assertion follow-up

- Resume repeats the same task in "Continue working" and "Open work". Verify the first visible exact match rather than requiring one global match.
- Invalid login checks the POST response (302) rather than expecting a specific subsequent GET response event. This makes HTTP 429 (rate limiting) explicit.
- Existing login protection `throttle:5,1` is deliberately unchanged.
- No routes, controllers, database schemas or application views are modified.

Run `npm run test:e2e`. For intermittent login failures, inspect HTTP POST status and rerun after the 60-second throttle window; investigate test isolation before changing production throttling.
