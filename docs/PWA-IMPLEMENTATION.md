# DevFlow PWA — implementation and limitations

The app is installable on supported browsers with a valid manifest, 192px and 512px PNG icons and a service worker. **HTTPS is required outside localhost.**

## Security constraints
- Only the offline fallback and public app icons are cached. No login, register, workspace/project pages, API responses, user assets, CSRF-bearing HTML, authenticated responses, or Vite build files are cached.
- Navigation is always network-first with a shared **public** offline fallback on network failure. User data cannot be read offline.
- The offline fallback includes no queued writes or stored credentials. Offline submissions are not supported.
- Cache name versioning ensures updates replace old public assets.

## Browser acceptance testing
1. Deploy over HTTPS and confirm manifest and service worker are served from root with correct MIME types.
2. Inspect Application > Manifest; confirm both icons and an install option where supported.
3. Visit dashboard while signed in, switch browser to Offline, reload: the generic offline fallback appears (not saved dashboard data).
4. Re-enable network and reload: authenticated page loads normally.
5. Check Cache Storage: no authenticated HTML or API payloads.
6. Sign out and repeat; no previous user's data should remain in Cache Storage.
7. Verify no scope or CORS errors in browser console.

## Not yet supported
Push notifications, offline project editing, background sync, downloadable confidential data, and install prompts are intentionally not implemented.
