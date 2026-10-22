#!/bin/bash
# Instalacion inicial en la VPS descrita en docs/despliegue-vps.md.
set -euo pipefail

[[ $(id -un) == ubuntu ]] || { echo 'Ejecutar como ubuntu, sin sudo bash.' >&2; exit 1; }
[[ $# == 1 && -f $1 ]] || { echo 'Indicar la clave publica de Actions.' >&2; exit 1; }
key=$(realpath "$1")
cd /home/ubuntu/software-pedidos-alas
[[ $(git branch --show-current) == produccion ]] || { echo 'La rama debe ser produccion.' >&2; exit 1; }
git diff --cached --quiet || { echo 'Hay cambios preparados. Revisarlos antes de instalar.' >&2; exit 1; }
while IFS= read -r path; do
    case "$path" in
        .htaccess|app/Core/conexion.php|app/Core/http.php) ;;
        *) echo "Cambio local adicional: $path. No se modifico nada." >&2; exit 1 ;;
    esac
done < <(git diff --name-only)
git fetch origin produccion
git cat-file -e origin/produccion:deploy/install-vps.sh
git merge-base --is-ancestor HEAD origin/produccion
sudo /usr/local/sbin/alas-backup

umask 077
backup=$(mktemp -d /home/ubuntu/alas-backups/antes-auto-deploy.XXXXXXXX)
git diff --binary > "$backup/cambios-locales.patch"
cp --parents .htaccess app/Core/conexion.php app/Core/http.php "$backup/"
stashed=''
stopped=0
updated=0
cleanup() {
    result=$?
    trap - EXIT
    if [[ $result -ne 0 && $updated == 0 && -n $stashed ]]; then
        git stash apply "$stashed" || echo 'Revisar recuperacion del stash guardado.' >&2
    fi
    if [[ $stopped == 1 ]]; then
        sudo docker compose --project-directory /home/ubuntu/alas-docker start web || true
    fi
    exit "$result"
}
trap cleanup EXIT
sudo docker compose --project-directory /home/ubuntu/alas-docker stop web
stopped=1
if ! git diff --quiet; then
    git stash push -m 'Correcciones VPS antes de auto-deploy' -- \
        .htaccess app/Core/conexion.php app/Core/http.php
    stashed=$(git rev-parse refs/stash)
    printf '%s\n' "$stashed" > "$backup/stash.txt"
fi
git merge --ff-only origin/produccion
updated=1
sudo bash deploy/install-vps.sh "$key"
sudo docker compose --project-directory /home/ubuntu/alas-docker start web
stopped=0
echo "Instalacion inicial completada. Correcciones anteriores guardadas en $backup"
echo 'No aplicar ese stash: las correcciones ya estan incorporadas al repositorio.'
