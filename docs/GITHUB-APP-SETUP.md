# GitHub App connection (read-only)

## What is included

- Optional GitHub App user authorization (OAuth web flow), session-bound state and 10-minute expiry.
- Lists installations visible to the authenticated GitHub user and filters to the configured DevFlow GitHub App.
- Connects an explicitly selected installation repository to a DevFlow project owned by the signed-in user.
- Creates short-lived **installation access tokens on demand**, signed using an RS256 app JWT, for read-only commit sync. No user OAuth tokens or installation access tokens are persisted.
- Signed webhook handling accepts private repository pushes **only when the event installation matches the linked installation**.
- Existing manually linked public repositories continue to work.

## GitHub Developer Settings

1. Create a **GitHub App** (not an OAuth App) with Homepage URL for your HTTPS DevFlow domain.
2. Set **User authorization callback URL** to `https://YOUR-DOMAIN/github/callback`.
3. Grant repository permissions **Contents: Read-only** and **Metadata: Read-only**. Keep other repository/org permissions disabled unless a separately reviewed module needs them.
4. Subscribe to **Push** webhooks, URL `https://YOUR-DOMAIN/api/webhooks/github`, content type `application/json`, secret matching `GITHUB_WEBHOOK_SECRET`.
5. Generate and securely store the GitHub App PEM private key on your server, readable only by the PHP/queue user. Set the path using `file:///absolute/path/github-app.pem`.
6. Configure `GITHUB_APP_ID`, `GITHUB_APP_CLIENT_ID`, `GITHUB_APP_CLIENT_SECRET`, `GITHUB_APP_PRIVATE_KEY`, and `GITHUB_WEBHOOK_SECRET` in `.env` (never commit these).
7. Run `php artisan config:clear` (or production `php artisan config:cache` after deploy).
8. Install the App on a GitHub account/organization and select repositories. In DevFlow open an owned project and click **Connect GitHub App**, sign in to GitHub, then choose the repository.
9. Run `php artisan queue:work` and the Laravel scheduler as separate managed processes.

## Security notes and limitations

- This connection does not authenticate the user into DevFlow; the user must already be logged into DevFlow.
- A link to a private repo only grants read-only sync of the listed repo. Never display access tokens or private git contents in the browser.
- After connection, permission revocation is detected by GitHub API failures and shown as a sync error. Manual unlink and permission refresh UX are not yet implemented.
- The OAuth callback currently lists the first 1,000 installation repositories; large organizations should use pagination/installation discovery before production.
- OAuth installation list alone does not prove individual repository content access through the user; selecting a repository authorized to the App is an action granting DevFlow read access to that repository. For org installations, configure organization installation policies accordingly.
- Network integration tests and live GitHub App verification remain required before production.
