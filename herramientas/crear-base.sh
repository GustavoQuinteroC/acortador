#!/usr/bin/env bash
# Crea la base y el usuario del acortador en un MariaDB que corre en Docker.
# Toma nombre, usuario y contraseña del .env de la app y pide la contraseña root sin mostrarla.
#
# Uso: herramientas/crear-base.sh <ruta/al/.env> <contenedor-mariadb> [hosts permitidos]
# Hosts por omisión: '%'. Para limitarlo a la red de Docker, pasa sus rangos, por ejemplo: "172.18.0.% fd00:1234:%"
set -euo pipefail

ENV_APP="${1:?Falta la ruta al .env}"
CONTENEDOR="${2:?Falta el nombre del contenedor de MariaDB}"
read -r -a HOSTS <<< "${3:-%}"

leer() { sed -n "s/^$1=//p" "$ENV_APP" | tail -1; }
BD="$(leer DB_DATABASE)"; USUARIO="$(leer DB_USERNAME)"; CLAVE="$(leer DB_PASSWORD)"
[[ "$BD" =~ ^[a-z0-9_]+$ && "$USUARIO" =~ ^[a-z0-9_]+$ && "$CLAVE" =~ ^[A-Za-z0-9]{16,}$ ]] \
  || { echo "Revisa DB_DATABASE, DB_USERNAME y DB_PASSWORD (16+ letras o números) en $ENV_APP" >&2; exit 1; }

SQL="CREATE DATABASE IF NOT EXISTS \`$BD\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
for HOST in "${HOSTS[@]}"; do
  SQL+="
CREATE USER IF NOT EXISTS '$USUARIO'@'$HOST' IDENTIFIED BY '$CLAVE';
ALTER USER '$USUARIO'@'$HOST' IDENTIFIED BY '$CLAVE';
GRANT ALL PRIVILEGES ON \`$BD\`.* TO '$USUARIO'@'$HOST';"
done

read -r -s -p "Contraseña root de MariaDB: " RAIZ; echo
# La contraseña viaja por variable de entorno del exec, no por la línea de comandos.
printf '%s\n' "$SQL" | docker exec -i -e MYSQL_PWD="$RAIZ" "$CONTENEDOR" mariadb -uroot
unset RAIZ
echo "Listo: base $BD y usuario $USUARIO para ${HOSTS[*]}."
