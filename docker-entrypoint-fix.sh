#!/bin/bash
set -e

# Force single MPM every time the container starts
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Railway assigns a dynamic port via $PORT -- rewrite Apache to listen there instead of 80
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-enabled/000-default.conf

exec "$@"