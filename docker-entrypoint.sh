#!/bin/bash
set -e

# Remove o arquivo apache2.pid se ele existir
if [ -f /var/run/apache2/apache2.pid ]; then
  rm /var/run/apache2/apache2.pid
fi

# Executa o comando original
exec "$@"
