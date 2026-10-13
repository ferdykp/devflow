# AI Insights (optional)

This feature is disabled by default. It sends a limited selection of project data to a configured OpenAI-compatible chat-completions provider **only** on an authenticated user's explicit POST with consent. It does not run background AI analysis.

Environment keys: `DEVFLOW_AI_ENABLED`, `DEVFLOW_AI_ENDPOINT`, `DEVFLOW_AI_API_KEY`, `DEVFLOW_AI_MODEL`, `DEVFLOW_AI_TIMEOUT`. Set HTTPS endpoint controlled by the operator. External URLs are **administrator configuration**, never user input. Run `php artisan config:cache` on deployment after setting configuration. Do not log raw AI prompts or credentials.

Scope analysis requires a saved scope baseline or approved PRD version, otherwise returns `not_assessable` locally. The model response is treated as untrusted and strictly validated; no automatic PRD change, scope approval, task status update, or durable AI result is performed. Users must still use manual scope review. The submitted context may contain confidential PRD/task information: secure approval from organization before enabling an external provider.

## Remaining limitations

- Live provider integration, pricing, response consistency, and privacy contracts need operator verification.
- Requests run synchronously, up to the configured timeout; use a background job before high-volume production operation.
- The current scope analysis is advisory and does not link AI evidence to stable PRD paragraph IDs.
- AI Commit Assistant CLI is a separate module; repository GitHub API cannot inspect local staged changes.
