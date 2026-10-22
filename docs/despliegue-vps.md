# Despliegue automatico a la VPS Ubuntu

Cada push a `produccion` (incluido integrar un PR) ejecuta **Deploy VPS** en GitHub Actions. El workflow sustituye el anterior despliegue FTP a InfinityFree. No requiere Portainer abierto ni un tunel SSH desde una computadora personal.

Esta instalacion usa la VPS existente: usuario `ubuntu`, repositorio `/home/ubuntu/software-pedidos-alas`, Compose `/home/ubuntu/alas-docker`, imagen local `alas-docker-web` y web `https://40.160.100.156`. No reconstruye imagenes ni actualiza Docker/PHP automaticamente; los cambios de infraestructura siguen siendo una tarea explicita.

## Comportamiento

1. Actions solicita desplegar el SHA del push por SSH con comprobacion estricta de la identidad del servidor.
2. La VPS admite solo SHAs completos, serializa las ejecuciones y omite solicitudes superadas por una version mas reciente de `produccion`.
3. Comprueba que el checkout no tenga cambios locales, prepara el commit aparte, valida la sintaxis de todos los PHP con la imagen actual y verifica el registro de migraciones.
4. Ejecuta `alas-backup` y revalida que nadie modifico el checkout durante la preparacion.
5. Detiene brevemente solo la web, ejecuta las migraciones nuevas, actualiza por fast-forward y arranca la web. Hay una ventana breve de indisponibilidad; no es un despliegue sin interrupciones.
6. Comprueba por HTTPS el inicio y los accesos del equipo y clientes. Si falla, intenta recuperar el commit anterior con `git reset --keep` y arrancarlo. No revierte la base ni sobrescribe cambios locales en conflicto.

El despliegue conserva `.env`, `db_credentials.php`, `gmail-config.local.php`, imagenes subidas, certificados, volumenes, usuarios y datos. Sus rutas no se sincronizan ni se limpian. Las credenciales no pasan por el workflow.

## Instalacion inicial (una sola vez)

Integrar primero este PR en `produccion`. La primera ejecucion de Actions puede fallar por faltar los secretos o el usuario de despliegue: no modifica la web. Completar los pasos siguientes y volver a ejecutar el workflow.

### 1. Crear una clave exclusiva para Actions

En la VPS, como `ubuntu`:

```bash
cd ~/alas-docker
ssh-keygen -t ed25519 -N '' -C github-actions-alas -f github-actions-alas
chmod 600 github-actions-alas
```

Si la clave ya existe, no sobrescribirla. El archivo sin `.pub` es privado: no enviarlo a chats, al repositorio ni a Portainer. Solo se copia al secreto de Actions indicado abajo. No usar la clave personal de ubuntu o Mia.

### 2. Incorporar las correcciones locales e instalar el acceso restringido

El PR incorpora las correcciones que antes estaban solo en la VPS: conexion privada, `base_url` en la raiz y las rutas publicas de clientes/personal. El instalador inicial guarda un respaldo y un stash de esas tres correcciones, actualiza el repositorio e instala scripts propiedad de root. No vuelve a aplicar ese stash porque reintroduciria diferencias locales.

```bash
cd ~/software-pedidos-alas
git fetch origin produccion
git show origin/produccion:deploy/bootstrap-vps.sh > ~/alas-docker/bootstrap-vps.sh
bash ~/alas-docker/bootstrap-vps.sh ~/alas-docker/github-actions-alas.pub
```

Si detecta otros cambios locales, se detiene para revisarlos. No usar `git reset --hard`, ni borrar credenciales para resolverlo.

El usuario `deploy-alas` tiene la clave restringida a `/usr/local/bin/alas-deploy-ssh`; no permite terminal, SFTP ni tuneles. Solo puede invocar `/usr/local/sbin/alas-deploy` mediante sudo. Desplegar codigo de produccion sigue siendo una operacion privilegiada: limitar quienes pueden escribir en `produccion`.

### 3. Configurar secretos en GitHub

En el repositorio: **Settings > Environments > New environment > production**. Para que sea automatico, no agregar aprobacion manual obligatoria. Restringir las ramas de despliegue a `produccion`.

Crear estos **Environment secrets**:

| Nombre | Valor |
| --- | --- |
| `VPS_HOST` | `40.160.100.156` |
| `VPS_USER` | `deploy-alas` |
| `VPS_SSH_KEY` | Todo el contenido del archivo privado `~/alas-docker/github-actions-alas`, incluidos encabezado y pie |
| `VPS_KNOWN_HOSTS` | La linea publica obtenida con el comando siguiente |

```bash
sudo awk '{print "40.160.100.156 " $1 " " $2}' /etc/ssh/ssh_host_ed25519_key.pub
```

Obtener esta identidad desde la sesion de la VPS ya confiable, no confiar ciegamente en `ssh-keyscan` desde Internet. Para copiar la clave privada al secreto puede abrirse localmente con `nano ~/alas-docker/github-actions-alas`; nunca pegarla en un chat ni compartir su salida.

### 4. Probar

En **Actions > Deploy VPS > Run workflow**, elegir `produccion`. La primera ejecucion completa registra la migracion de presencia existente; es idempotente. Confirmar el mensaje `Despliegue correcto: <sha>` y comprobar la web y el acceso del equipo.

Luego cada push a `produccion` ejecutara el mismo proceso. Si falla, Actions muestra el error. Revisar y corregir; no asumir que el cambio quedo publicado solo porque se hizo push.

## Migraciones futuras

Agregar un archivo SQL nuevo, idempotente y compatible con el codigo anterior, y su ruta al final de `deploy/migrations.txt`, en el mismo PR de la funcionalidad. El despliegue no descubre ni ejecuta automaticamente todos los archivos de `database/`, para evitar importar ejemplos o instaladores antiguos.

Solo se permiten nombres `database/migrar-<nombre>.sql`. El registro local `/var/lib/alas-deploy/migrations.tsv` conserva ruta y SHA-256 despues de completar cada migracion. Si una migracion ya aplicada cambia de contenido, el despliegue se detiene: escribir otra migracion. Si una migracion falla a mitad, no se registra y debe poder repetirse sin danar los datos. No retirar del manifiesto una migracion ya publicada.

Las migraciones destructivas o incompatibles con la version anterior requieren un plan manual de mantenimiento. No se hace rollback automatico de SQL. El script no decide a que usuario otorgar el rol Dueno ni cambia roles.

## Archivos privados y permisos de ejecucion

Los scripts usan Bash, Docker Compose v2 o posterior, curl, git, flock, runuser, tar y sha256sum; estan disponibles o forman parte de la instalacion Ubuntu descrita. Ejecutar el bootstrap como `ubuntu`; el despliegue autorizado se ejecuta como root y git como `ubuntu`.

Los scripts instalados NO se actualizan solos con cada push. Si se modifica `deploy/alas-deploy`, su wrapper o instalador, revisar el cambio y ejecutar otra vez `sudo bash deploy/install-vps.sh ~/alas-docker/github-actions-alas.pub` desde el checkout actualizado.

Conservar tambien en un respaldo privado: `/usr/local/sbin/alas-backup`, `/usr/local/sbin/alas-renovar-certificado`, `/usr/local/sbin/alas-deploy`, `/usr/local/bin/alas-deploy-ssh`, unidades/timers `alas-*` de `/etc/systemd/system`, `/etc/sudoers.d/alas-deploy`, `/var/lib/alas-deploy` y la configuracion de claves autorizadas. El respaldo de aplicacion actual por si solo no incluye estas rutas.

## Limites de validacion

La validacion de PHP es sintactica; no sustituye las pruebas de negocio, permisos, pedidos, correo ni compatibilidad de las migraciones. El proceso de despliegue debe probarse en la VPS con los secretos configurados antes de considerarlo activo. El HTTPS de la VPS debe estar vigente y la imagen local `alas-docker-web` debe existir.
