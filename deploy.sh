#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"

# Uso: dá `git pull` ANTES de chamar isso — não faça pull daqui de dentro.
# ./deploy.sh — local (SSH manual) ou chamado pela GH Action em .github/workflows/deploy.yml.
# Backend e frontend rodam com o código montado por volume (docker-compose.yml),
# então git pull já atualiza o que os processos leem; só falta reinstalar
# dependências e migrar o banco.

docker compose up -d
docker compose exec -T backend composer install --no-interaction --prefer-dist
docker compose exec -T backend php artisan migrate --force
docker compose exec -T frontend npm install

echo "Deploy ok"
