#!/bin/bash
set -euo pipefail
cd -- "$(dirname -- "${BASH_SOURCE[0]}")"
source ./alas-deploy

temp=$(mktemp -d)
trap 'rm -rf -- "$temp"' EXIT
mkdir -p "$temp/candidate/deploy" "$temp/candidate/database"
LEDGER="$temp/ledger"
touch "$LEDGER"
candidate="$temp/candidate"
manifest="$candidate/deploy/migrations.txt"
migration=database/migrar-ejemplo.sql
printf 'SELECT 1;\n' > "$candidate/$migration"
printf '%s\n' "$migration" > "$manifest"
check_manifest "$candidate"
echo 'OK: acepta una migracion nueva declarada.'

digest=$(sha256sum "$candidate/$migration" | cut -d ' ' -f 1)
printf '%s\t%s\n' "$migration" "$digest" > "$LEDGER"
check_manifest "$candidate"
echo 'OK: acepta una migracion aplicada sin modificaciones.'

printf 'SELECT 2;\n' > "$candidate/$migration"
if check_manifest "$candidate" 2>/dev/null; then exit 1; fi
echo 'OK: rechaza modificar SQL ya aplicado.'
printf 'SELECT 1;\n' > "$candidate/$migration"

printf '# retirado\n' > "$manifest"
if check_manifest "$candidate" 2>/dev/null; then exit 1; fi
echo 'OK: rechaza retirar migraciones aplicadas.'
printf '%s\n%s\n' "$migration" "$migration" > "$manifest"
if check_manifest "$candidate" 2>/dev/null; then exit 1; fi
echo 'OK: rechaza duplicados.'

printf 'database/../secret.sql\n' > "$manifest"
if check_manifest "$candidate" 2>/dev/null; then exit 1; fi
printf 'database/datos_demo.sql\n' > "$manifest"
if check_manifest "$candidate" 2>/dev/null; then exit 1; fi
echo 'OK: rechaza rutas arbitrarias e importacion de ejemplos.'

printf '%s\n' "$migration" > "$manifest"
rm "$candidate/$migration"
if check_manifest "$candidate" 2>/dev/null; then exit 1; fi
echo 'OK: rechaza archivos faltantes.'

REPO="$temp/repo"
git init -q --initial-branch=produccion "$REPO"
git -C "$REPO" config user.email test@example.invalid
git -C "$REPO" config user.name Test
as_git() { git -C "$REPO" "$@"; }
printf 'original\n' > "$REPO/app.txt"
printf '.env\n' > "$REPO/.gitignore"
as_git add app.txt .gitignore
as_git commit -qm inicial
previous=$(as_git rev-parse HEAD)
assert_clean
printf 'local\n' > "$REPO/app.txt"
if assert_clean 2>/dev/null; then exit 1; fi
echo 'OK: rechaza cambios locales antes de desplegar.'
as_git add app.txt
if assert_clean 2>/dev/null; then exit 1; fi
echo 'OK: rechaza cambios preparados antes de desplegar.'
as_git commit -qm segundo
printf 'credencial-privada-de-prueba\n' > "$REPO/.env"
as_git reset --keep "$previous" >/dev/null
[[ $(cat "$REPO/app.txt") == original ]]
[[ $(cat "$REPO/.env") == credencial-privada-de-prueba ]]
echo 'OK: rollback de codigo conserva archivo privado ignorado.'

as_git checkout -qb otra
if assert_clean 2>/dev/null; then exit 1; fi
echo 'OK: rechaza una rama distinta de produccion.'

for invalid in '' 'deploy abc' 'deploy 0123456789012345678901234567890123456789; id' 'bash'; do
    if SSH_ORIGINAL_COMMAND="$invalid" bash ./alas-deploy-ssh >/dev/null 2>&1; then exit 1; fi
done
echo 'OK: SSH rechaza comandos y hashes invalidos.'
