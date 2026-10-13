# AI Commit Assistant — implementation update

- Added `tools/devflow-commit.py` as a standalone, dependency-free staged Git commit assistant.
- Local preview by default: no provider, no file modification, no commit.
- Optional HTTPS AI suggestion requires explicit `--ai --send-diff` consent.
- Strict Conventional Commit validation and diff size bound.
- Interactive commit confirmation, explicit opt-out via `--yes`, staged tree recheck, no auto `git add` or `git push`.
- Added Python CLI regression tests and documentation.

**Not production-complete:** live Laravel/browser/deployment verification, end-to-end integration of CLI with DevFlow user/project authorization, provider integration tests, and independent security audit remain pending.
