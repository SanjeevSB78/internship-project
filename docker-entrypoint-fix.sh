#!/bin/bash
set -e

# Force single MPM every time the container starts, no matter what the image state is
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

exec "$@"