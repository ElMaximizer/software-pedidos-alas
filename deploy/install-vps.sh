#!/bin/bash
set -euo pipefail

[[ $(id -u) == 0 ]] || { echo 'Ejecutar con sudo.' >&2; exit 1; }
[[ $# == 1 && -f $1 ]] || { echo 'Indicar el archivo de clave PUBLICA de Actions.' >&2; exit 1; }
ssh-keygen -lf "$1" >/dev/null
[[ $(awk 'END {print NR}' "$1") == 1 ]] || { echo 'Se espera una sola clave publica.' >&2; exit 1; }
read -r key_type _ < "$1"
[[ $key_type == ssh-ed25519 ]] || { echo 'Se requiere una clave publica Ed25519 sin opciones.' >&2; exit 1; }
source_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
[[ -x /usr/local/sbin/alas-backup ]] || { echo 'Falta instalar alas-backup.' >&2; exit 1; }

install -m 755 -o root -g root "$source_dir/alas-deploy" /usr/local/sbin/alas-deploy
install -m 755 -o root -g root "$source_dir/alas-deploy-ssh" /usr/local/bin/alas-deploy-ssh
if ! id deploy-alas >/dev/null 2>&1; then
    adduser --disabled-password --gecos 'GitHub Actions deploy' deploy-alas
fi
install -d -m 700 -o deploy-alas -g deploy-alas /home/deploy-alas/.ssh
authorized="restrict,command=\"/usr/local/bin/alas-deploy-ssh\" $(cat "$1")"
touch /home/deploy-alas/.ssh/authorized_keys
if ! grep -qxF "$authorized" /home/deploy-alas/.ssh/authorized_keys; then
    printf '%s\n' "$authorized" >> /home/deploy-alas/.ssh/authorized_keys
fi
chown deploy-alas:deploy-alas /home/deploy-alas/.ssh/authorized_keys
chmod 600 /home/deploy-alas/.ssh/authorized_keys

rule=$(mktemp)
trap 'rm -f "$rule"' EXIT
printf '%s\n' 'deploy-alas ALL=(root) NOPASSWD: /usr/local/sbin/alas-deploy *' > "$rule"
visudo -cf "$rule"
install -m 440 -o root -g root "$rule" /etc/sudoers.d/alas-deploy
install -d -m 700 /var/lib/alas-deploy
echo 'Acceso de despliegue instalado. La clave no permite terminal, SFTP ni tuneles.'
