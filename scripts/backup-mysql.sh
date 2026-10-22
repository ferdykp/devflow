#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
if [[ $# != 3 ]]; then
  echo 'Usage: backup-mysql.sh /secure/client.cnf database /secure/backup-directory' >&2
  exit 2
fi
credentials=$1
database=$2
destination=$3
[[ -f "$credentials" && "$database" =~ ^[a-zA-Z0-9_]+$ ]] || exit 2
mkdir -p "$destination"
backup="$destination/${database}-$(date -u +%Y%m%dT%H%M%SZ)-$$.sql.gz"
partial=$(mktemp "$destination/.devflow-backup.XXXXXX")
trap 'rm -f "$partial"' EXIT
mysqldump --defaults-extra-file="$credentials" --single-transaction --quick --skip-lock-tables --no-tablespaces --set-gtid-purged=OFF "$database" | gzip > "$partial"
gzip -t "$partial"
mv "$partial" "$backup"
if command -v sha256sum >/dev/null; then
  (cd "$destination" && sha256sum "$(basename "$backup")") > "$backup.sha256"
else
  (cd "$destination" && shasum -a 256 "$(basename "$backup")") > "$backup.sha256"
fi
printf '%s\n' "$backup"
