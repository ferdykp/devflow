#!/usr/bin/env bash
set -Eeuo pipefail
if [[ $# != 3 ]]; then
  echo 'Usage: restore-drill-mysql.sh /secure/client.cnf devflow_restore_UNIQUE backup.sql.gz' >&2
  exit 2
fi
credentials=$1
database=$2
backup=$3
[[ -f "$credentials" && -f "$backup" && "$database" =~ ^devflow_restore_[a-zA-Z0-9_]+$ ]] || {
  echo 'Restore drills require a separate database prefixed devflow_restore_.' >&2
  exit 2
}
[[ -f "$backup.sha256" ]] || { echo 'Missing backup checksum.' >&2; exit 2; }
if command -v sha256sum >/dev/null; then
  (cd "$(dirname "$backup")" && sha256sum -c "$(basename "$backup").sha256")
else
  (cd "$(dirname "$backup")" && shasum -a 256 -c "$(basename "$backup").sha256")
fi
gzip -t "$backup"
tables=$(mysql --defaults-extra-file="$credentials" --batch --skip-column-names -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$database'")
[[ "$tables" == '0' ]] || { echo 'Refusing to restore into a non-empty database.' >&2; exit 1; }
# The target must already exist. This script never creates, drops, or clears a database.
mysql --defaults-extra-file="$credentials" "$database" -e 'SELECT DATABASE()' >/dev/null
gzip -dc "$backup" | mysql --defaults-extra-file="$credentials" "$database"
mysql --defaults-extra-file="$credentials" "$database" --batch --skip-column-names -e 'SELECT COUNT(*) AS migration_count FROM migrations; SELECT COUNT(*) AS user_count FROM users;'
