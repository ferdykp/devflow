# DevFlow Commit Assistant CLI

A standalone, optional local utility for suggesting Conventional Commit subjects from **staged** Git changes. Requires Python 3.10+ and Git; no Composer or npm package is added. It does not stage changes, push code, or call DevFlow's server.

## Preview locally (no network)

```bash
git add path/to/changed/file
python3 tools/devflow-commit.py
```

The local fallback categorizes changed **file names** only, so its suggested message is intentionally generic. Use `--message` for a precise description:

```bash
python3 tools/devflow-commit.py --message 'feat(projects): add project search' --commit
```

`--commit` prompts for confirmation; `--commit --yes` allows scripting. Git commit hooks still run normally. Changes to staged files between preview and confirmation cause an error. Review with `git diff --cached` before committing.

## Optional AI suggestion

The AI provider **does not receive anything** without both `--ai` and `--send-diff`. The CLI sends up to 40 KB of staged **text** diff, including code and possibly sensitive content. Check `git diff --cached` and remove secrets or sensitive code before opting in. No token is saved by the CLI. Only use a trusted HTTPS-compatible Chat Completions API provider.

```bash
export DEVFLOW_AI_ENDPOINT='https://your-provider.example/v1/chat/completions'
export DEVFLOW_AI_MODEL='your-model'
export DEVFLOW_AI_API_KEY='your-key'
python3 tools/devflow-commit.py --ai --send-diff
python3 tools/devflow-commit.py --ai --send-diff --commit
```

The second invocation sends the staged diff **again**. If you approve a previously suggested message and do not want another upload, use `--message 'type: subject' --commit` instead. The AI suggestion is validated against Conventional Commits and a 72-character maximum. AI suggestions are untrusted, always review the proposed subject.

## Limitations

- This CLI is deliberately not yet connected to the web application's authenticated project mapping, OAuth, or PRD Scope Guardian; it cannot assert whether a commit is inside PRD scope.
- Local fallback does not read changes' semantic meaning.
- Large or binary diffs are rejected in AI mode.
- Credentials must be supplied to the CLI process, not put into git-tracked config.
- For unattended scripts, protect the repository from concurrent writes. The CLI verifies the index tree before committing but does not provide an atomic lock across external processes.

## Tests

```bash
python3 -m unittest discover -s tests/cli -p 'test_*.py' -v
```
